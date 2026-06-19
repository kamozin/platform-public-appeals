<?php

declare(strict_types=1);

namespace App\Application\Dashboard;

use App\Application\Appeals\AppealDraftService;
use App\Application\Appeals\AppealInteractionService;
use App\Application\PublicContent\PublicContentService;
use App\Models\ApiToken;
use App\Models\User;
use App\Support\Api\ApiProblemException;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;

final class DashboardService
{
    public function __construct(
        private readonly AppealDraftService $drafts,
        private readonly AppealInteractionService $interactions,
        private readonly PublicContentService $content,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function appeals(User $user): array
    {
        $drafts = $this->drafts->userDrafts($user);

        return [
            'items' => array_values(array_filter($drafts, fn (array $draft): bool => $draft['status'] !== 'draft')),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function drafts(User $user): array
    {
        return [
            'items' => array_values(array_filter(
                $this->drafts->userDrafts($user),
                fn (array $draft): bool => $draft['status'] === 'draft',
            )),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function savedAppeals(User $user): array
    {
        $slugs = DB::table('saved_appeals')
            ->where('user_id', $user->id)
            ->orderByDesc('updated_at')
            ->pluck('appeal_slug')
            ->all();

        $items = [];

        foreach ($slugs as $slug) {
            $appeal = $this->content->appealShow((string) $slug);

            if ($appeal !== null) {
                $items[] = $appeal;
            }
        }

        return [
            'items' => $items,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function comments(User $user): array
    {
        return [
            'items' => $this->interactions->userComments($user),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function notifications(User $user): array
    {
        $readIds = DB::table('user_notification_reads')
            ->where('user_id', $user->id)
            ->pluck('notification_id')
            ->map(fn (mixed $id): string => (string) $id)
            ->all();

        return [
            'items' => array_map(
                fn (array $notification): array => [
                    ...$notification,
                    'read' => in_array($notification['id'], $readIds, true),
                ],
                $this->systemNotifications(),
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function markAllNotificationsRead(User $user): array
    {
        $now = now();
        $rows = array_map(
            fn (array $notification): array => [
                'user_id' => $user->id,
                'notification_id' => $notification['id'],
                'read_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            $this->systemNotifications(),
        );

        DB::table('user_notification_reads')->upsert(
            $rows,
            ['user_id', 'notification_id'],
            ['read_at', 'updated_at'],
        );

        return [
            'marked' => true,
            'count' => count($rows),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function updateNotificationSettings(User $user, bool $enabled): array
    {
        $user->forceFill(['notifications_enabled' => $enabled])->save();

        return [
            'notificationsEnabled' => (bool) $user->refresh()->notifications_enabled,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function statusHistory(User $user): array
    {
        return [
            'items' => array_map(
                fn (array $draft): array => [
                    'id' => $draft['id'],
                    'title' => $draft['title'] ?? 'Черновик обращения',
                    'status' => $draft['status'],
                    'createdAt' => null,
                ],
                $this->drafts->userDrafts($user),
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function sessions(User $user): array
    {
        $items = $user->apiTokens()
            ->latest()
            ->get()
            ->map(fn ($token): array => [
                'id' => $token->id,
                'name' => $token->name,
                'lastUsedAt' => $this->dateString($token->last_used_at),
                'createdAt' => $this->dateString($token->created_at),
            ])
            ->values()
            ->all();

        return [
            'items' => $items,
        ];
    }

    public function terminateSession(User $user, ?ApiToken $currentToken, string $id): void
    {
        if ($currentToken instanceof ApiToken && $currentToken->id === $id) {
            throw new ApiProblemException('CONFLICT', 409);
        }

        $deleted = $user->apiTokens()->whereKey($id)->delete();

        if ($deleted === 0) {
            throw new ApiProblemException('NOT_FOUND', 404);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function achievements(): array
    {
        return [
            'items' => [
                ['id' => 'first-draft', 'title' => 'Первый черновик', 'description' => 'Создайте первое обращение.', 'earned' => false],
                ['id' => 'public-support', 'title' => 'Общественная поддержка', 'description' => 'Поддержите публичное обращение.', 'earned' => false],
            ],
        ];
    }

    /**
     * @return list<array{id: string, title: string, text: string}>
     */
    private function systemNotifications(): array
    {
        return [
            ['id' => 'welcome', 'title' => 'Добро пожаловать', 'text' => 'Личный кабинет готов к работе.'],
            ['id' => 'drafts', 'title' => 'Черновики', 'text' => 'Сохраняйте обращения перед отправкой.'],
        ];
    }

    private function dateString(mixed $value): ?string
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format(DateTimeInterface::ATOM);
        }

        if (is_string($value) && $value !== '') {
            return $value;
        }

        return null;
    }
}
