<?php

declare(strict_types=1);

namespace App\Application\Admin;

use App\Models\AppealAttachment;
use App\Models\AppealDraft;
use App\Models\AppealModerationEvent;
use App\Models\User;
use App\Support\Api\ApiProblemException;
use DateTimeInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final readonly class AppealModerationService
{
    private const STATUS_PENDING = 'pending_moderation';

    private const STATUS_NEEDS_CHANGES = 'needs_changes';

    private const STATUS_REJECTED = 'rejected';

    private const STATUS_APPROVED = 'approved';

    /**
     * @var list<string>
     */
    private const MODERATION_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_NEEDS_CHANGES,
        self::STATUS_REJECTED,
        self::STATUS_APPROVED,
    ];

    public function __construct(private AdminContentService $content) {}

    /**
     * @return array{items: list<array<string, mixed>>, pagination: array<string, int>}
     */
    public function list(?string $status = null, int $page = 1, int $perPage = 15): array
    {
        $normalizedStatus = $this->normalizedStatus($status);
        $query = AppealDraft::query()
            ->whereNotNull('submitted_at')
            ->withCount('attachments')
            ->latest('submitted_at')
            ->latest('created_at');

        if ($normalizedStatus !== 'all') {
            $query->where('status', $normalizedStatus);
        }

        $paginator = $query->paginate(
            perPage: max(1, min(50, $perPage)),
            page: max(1, $page),
        );

        return [
            'items' => $paginator->getCollection()
                ->map(fn (AppealDraft $draft): array => $this->listItemPayload($draft))
                ->values()
                ->all(),
            'pagination' => $this->pagination($paginator),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function show(string $id): array
    {
        return $this->draftPayload($this->findSubmittedDraft($id));
    }

    /**
     * @return array<string, mixed>
     */
    public function requestChanges(string $id, User $moderator, string $message): array
    {
        $draft = $this->findSubmittedDraft($id);
        $this->ensurePending($draft);

        $draft->forceFill([
            'status' => self::STATUS_NEEDS_CHANGES,
            'moderated_by_user_id' => $moderator->id,
            'moderated_at' => now(),
            'moderation_note' => $message,
            'rejection_reason' => null,
        ])->save();

        $this->recordEvent($draft, $moderator, 'changes_requested', $message);

        return $this->draftPayload($draft->refresh());
    }

    /**
     * @return array<string, mixed>
     */
    public function reject(string $id, User $moderator, string $reason): array
    {
        $draft = $this->findSubmittedDraft($id);
        $this->ensureRejectable($draft);

        $draft->forceFill([
            'status' => self::STATUS_REJECTED,
            'moderated_by_user_id' => $moderator->id,
            'moderated_at' => now(),
            'moderation_note' => null,
            'rejection_reason' => $reason,
        ])->save();

        $this->recordEvent($draft, $moderator, 'rejected', $reason);

        return $this->draftPayload($draft->refresh());
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function approve(string $id, User $moderator, array $payload): array
    {
        $draft = $this->findSubmittedDraft($id);
        $this->ensurePending($draft);

        return DB::transaction(function () use ($draft, $moderator, $payload): array {
            $appeal = $this->content->createAppeal($payload);

            $draft->forceFill([
                'status' => self::STATUS_APPROVED,
                'moderated_by_user_id' => $moderator->id,
                'moderated_at' => now(),
                'moderation_note' => null,
                'rejection_reason' => null,
                'public_appeal_id' => $appeal['id'],
            ])->save();

            $this->recordEvent($draft, $moderator, 'approved', null, [
                'publicAppealId' => $appeal['id'],
                'slug' => $appeal['slug'] ?? null,
            ]);

            return $this->draftPayload($draft->refresh());
        });
    }

    private function findSubmittedDraft(string $id): AppealDraft
    {
        $draft = AppealDraft::query()
            ->with(['attachments', 'moderationEvents.moderator'])
            ->whereKey($id)
            ->whereNotNull('submitted_at')
            ->first();

        if (! $draft instanceof AppealDraft) {
            throw new ApiProblemException('NOT_FOUND', 404);
        }

        return $draft;
    }

    private function ensurePending(AppealDraft $draft): void
    {
        if ($draft->status === self::STATUS_PENDING) {
            return;
        }

        throw new ApiProblemException('CONFLICT', 409);
    }

    private function ensureRejectable(AppealDraft $draft): void
    {
        if (in_array($draft->status, [self::STATUS_PENDING, self::STATUS_NEEDS_CHANGES], true)) {
            return;
        }

        throw new ApiProblemException('CONFLICT', 409);
    }

    private function normalizedStatus(?string $status): string
    {
        if ($status === null || $status === '') {
            return self::STATUS_PENDING;
        }

        if ($status === 'all') {
            return 'all';
        }

        if (in_array($status, self::MODERATION_STATUSES, true)) {
            return $status;
        }

        throw new ApiProblemException('VALIDATION_FAILED', 422, [
            'fields' => [
                'status' => ['The selected status is invalid.'],
            ],
        ]);
    }

    /**
     * @param  array<string, mixed>|null  $payload
     */
    private function recordEvent(
        AppealDraft $draft,
        User $moderator,
        string $action,
        ?string $comment = null,
        ?array $payload = null,
    ): void {
        AppealModerationEvent::query()->create([
            'appeal_draft_id' => $draft->id,
            'moderator_user_id' => $moderator->id,
            'action' => $action,
            'comment' => $comment,
            'payload' => $payload,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function listItemPayload(AppealDraft $draft): array
    {
        return [
            'id' => $draft->id,
            'status' => $draft->status,
            'category' => $draft->category,
            'submissionType' => $draft->submission_type,
            'title' => $draft->title,
            'description' => $draft->description,
            'urgency' => $draft->urgency,
            'location' => $draft->location,
            'submittedAt' => $this->dateString($draft->submitted_at),
            'moderatedAt' => $this->dateString($draft->moderated_at),
            'moderationNote' => $draft->moderation_note,
            'rejectionReason' => $draft->rejection_reason,
            'publicAppealId' => $draft->public_appeal_id,
            'attachmentsCount' => $this->attachmentsCount($draft),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function draftPayload(AppealDraft $draft): array
    {
        $draft->loadMissing(['attachments', 'moderationEvents.moderator']);

        return [
            ...$this->listItemPayload($draft),
            'contactVisibility' => $draft->contact_visibility,
            'contactName' => $draft->contact_name,
            'contactEmail' => $draft->contact_email,
            'contactPhone' => $draft->contact_phone,
            'createdAt' => $this->dateString($draft->created_at),
            'updatedAt' => $this->dateString($draft->updated_at),
            'attachments' => $draft->attachments
                ->map(fn (AppealAttachment $attachment): array => [
                    'id' => $attachment->id,
                    'kind' => $attachment->kind,
                    'originalName' => $attachment->original_name,
                    'mimeType' => $attachment->mime_type,
                    'size' => $attachment->size,
                ])
                ->values()
                ->all(),
            'events' => $draft->moderationEvents
                ->map(fn (AppealModerationEvent $event): array => $this->eventPayload($event))
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function eventPayload(AppealModerationEvent $event): array
    {
        $moderator = $event->moderator;

        return [
            'id' => $event->id,
            'action' => $event->action,
            'comment' => $event->comment,
            'payload' => $event->payload ?? [],
            'moderator' => $moderator instanceof User ? [
                'id' => $moderator->id,
                'name' => $moderator->name,
                'email' => $moderator->email,
            ] : null,
            'createdAt' => $this->dateString($event->created_at),
        ];
    }

    /**
     * @param  LengthAwarePaginator<int, mixed>  $paginator
     * @return array{currentPage: int, perPage: int, total: int, lastPage: int}
     */
    private function pagination(LengthAwarePaginator $paginator): array
    {
        return [
            'currentPage' => $paginator->currentPage(),
            'perPage' => $paginator->perPage(),
            'total' => $paginator->total(),
            'lastPage' => $paginator->lastPage(),
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

    private function attachmentsCount(AppealDraft $draft): int
    {
        $count = $draft->getAttribute('attachments_count');

        if (is_numeric($count)) {
            return (int) $count;
        }

        if ($draft->relationLoaded('attachments')) {
            return $draft->attachments->count();
        }

        return 0;
    }
}
