<?php

declare(strict_types=1);

namespace App\Application\Appeals;

use App\Application\PublicContent\PublicContentService;
use App\Models\AppealComment;
use App\Models\User;
use App\Support\Api\ApiProblemException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class AppealInteractionService
{
    public function __construct(private readonly PublicContentService $content) {}

    /**
     * @return array{items: list<array<string, mixed>>}
     */
    public function comments(string $slug, ?string $filter = null): array
    {
        $staticComments = $this->content->appealComments($slug);

        if ($staticComments === null) {
            throw new ApiProblemException('NOT_FOUND', 404);
        }

        $storedComments = [];

        if (Schema::hasTable('appeal_comments')) {
            $storedComments = AppealComment::query()
                ->with('user')
                ->where('appeal_slug', $slug)
                ->latest()
                ->get()
                ->map(fn (AppealComment $comment): array => $this->commentPayload($comment))
                ->all();
        }

        $items = [...$storedComments, ...$staticComments];

        if ($filter === 'official') {
            $items = array_values(array_filter($items, fn (array $item): bool => $item['type'] === 'official'));
        }

        if ($filter === 'media') {
            $items = array_values(array_filter($items, fn (array $item): bool => $item['hasMedia'] === true));
        }

        return [
            'items' => $items,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function storeComment(User $user, string $slug, string $comment): array
    {
        if ($this->content->appealShow($slug) === null) {
            throw new ApiProblemException('NOT_FOUND', 404);
        }

        $stored = AppealComment::query()->create([
            'appeal_slug' => $slug,
            'user_id' => $user->id,
            'status' => 'pending',
            'type' => 'public',
            'comment' => $comment,
            'has_media' => false,
        ]);

        return $this->commentPayload($stored->refresh());
    }

    /**
     * @return array<string, mixed>
     */
    public function save(User $user, string $slug): array
    {
        if ($this->content->appealShow($slug) === null) {
            throw new ApiProblemException('NOT_FOUND', 404);
        }

        DB::table('saved_appeals')->updateOrInsert(
            ['user_id' => $user->id, 'appeal_slug' => $slug],
            ['updated_at' => now(), 'created_at' => now()],
        );

        return [
            'saved' => true,
        ];
    }

    public function unsave(User $user, string $slug): void
    {
        DB::table('saved_appeals')
            ->where('user_id', $user->id)
            ->where('appeal_slug', $slug)
            ->delete();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function userComments(User $user): array
    {
        return AppealComment::query()
            ->where('user_id', $user->id)
            ->latest()
            ->get()
            ->map(fn (AppealComment $comment): array => $this->commentPayload($comment))
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function commentPayload(AppealComment $comment): array
    {
        $user = $comment->user;

        return [
            'id' => $comment->id,
            'appealSlug' => $comment->appeal_slug,
            'authorName' => $user instanceof User ? $user->name : 'Пользователь',
            'status' => $comment->status,
            'type' => $comment->type,
            'comment' => $comment->comment,
            'createdAt' => $comment->created_at?->toAtomString(),
            'hasMedia' => (bool) $comment->has_media,
        ];
    }
}
