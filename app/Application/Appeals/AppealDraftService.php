<?php

declare(strict_types=1);

namespace App\Application\Appeals;

use App\Models\AppealAttachment;
use App\Models\AppealDraft;
use App\Models\User;
use App\Support\Api\ApiProblemException;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

final class AppealDraftService
{
    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function create(AppealDraftAccess $access, array $payload): array
    {
        $guestToken = $access->user instanceof User ? null : Str::random(80);
        $draft = AppealDraft::query()->create($this->draftAttributes(
            payload: $payload,
            user: $access->user,
            guestToken: $guestToken,
        ));

        return $this->draftPayload($draft, $guestToken);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function update(AppealDraftAccess $access, string $id, array $payload): array
    {
        $draft = $this->ownedDraft($access, $id);
        $this->ensureContentEditable($draft);
        $draft->forceFill($this->draftAttributes($payload, includeOwner: false))->save();

        return $this->draftPayload($draft->refresh());
    }

    /**
     * @return array<string, mixed>
     */
    public function show(AppealDraftAccess $access, string $id): array
    {
        return $this->draftPayload($this->ownedDraft($access, $id));
    }

    public function delete(AppealDraftAccess $access, string $id): void
    {
        $draft = $this->ownedDraft($access, $id);
        $this->ensureDeletable($draft);
        $draft->delete();
    }

    /**
     * @return array<string, mixed>
     */
    public function addAttachment(AppealDraftAccess $access, string $draftId, UploadedFile $file): array
    {
        $draft = $this->ownedDraft($access, $draftId);
        $this->ensureContentEditable($draft);

        if ($draft->attachments()->count() >= 10) {
            throw new ApiProblemException('ATTACHMENT_LIMIT_EXCEEDED', 409);
        }

        $mime = (string) $file->getMimeType();
        $kind = $this->attachmentKind($mime);
        $size = (int) $file->getSize();

        $this->ensureAttachmentSize($kind, $size);

        $path = $file->store('appeal-attachments');

        $attachment = AppealAttachment::query()->create([
            'appeal_draft_id' => $draft->id,
            'user_id' => $access->user?->id,
            'kind' => $kind,
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $mime,
            'size' => $size,
        ]);

        return $this->attachmentPayload($attachment);
    }

    public function deleteAttachment(AppealDraftAccess $access, string $draftId, string $attachmentId): void
    {
        $draft = $this->ownedDraft($access, $draftId);
        $this->ensureContentEditable($draft);

        $attachment = $draft->attachments()->whereKey($attachmentId)->first();

        if (! $attachment instanceof AppealAttachment) {
            throw new ApiProblemException('NOT_FOUND', 404);
        }

        $attachment->delete();
    }

    /**
     * @return array<string, mixed>
     */
    public function submit(AppealDraftAccess $access, string $draftId, string $captchaToken): array
    {
        $draft = $this->ownedDraft($access, $draftId);
        $this->ensureSubmittable($draft);

        if (! in_array($captchaToken, ['test-captcha', 'dev-captcha'], true)) {
            throw new ApiProblemException('CAPTCHA_FAILED', 422);
        }

        $draft->forceFill([
            'status' => 'pending_moderation',
            'submitted_at' => now(),
            'moderated_by_user_id' => null,
            'moderated_at' => null,
            'moderation_note' => null,
            'rejection_reason' => null,
        ])->save();

        return $this->draftPayload($draft->refresh());
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function userDrafts(User $user): array
    {
        return AppealDraft::query()
            ->where('user_id', $user->id)
            ->latest()
            ->get()
            ->map(fn (AppealDraft $draft): array => $this->draftPayload($draft))
            ->values()
            ->all();
    }

    private function ownedDraft(AppealDraftAccess $access, string $id): AppealDraft
    {
        if (! $access->hasAnyCredential()) {
            throw new ApiProblemException('UNAUTHORIZED', 401);
        }

        $guestTokenHash = $access->guestTokenHash();

        $draft = AppealDraft::query()
            ->whereKey($id)
            ->where(function (Builder $query) use ($access, $guestTokenHash): void {
                if ($access->user instanceof User) {
                    $query->where('user_id', $access->user->id);
                }

                if ($guestTokenHash === null) {
                    return;
                }

                if ($access->user instanceof User) {
                    $query->orWhere('guest_token_hash', $guestTokenHash);

                    return;
                }

                $query->where('guest_token_hash', $guestTokenHash);
            })
            ->first();

        if (! $draft instanceof AppealDraft) {
            throw new ApiProblemException('NOT_FOUND', 404);
        }

        return $draft;
    }

    private function ensureContentEditable(AppealDraft $draft): void
    {
        if (in_array($draft->status, ['draft', 'pending_moderation', 'needs_changes'], true)) {
            return;
        }

        throw new ApiProblemException('CONFLICT', 409);
    }

    private function ensureSubmittable(AppealDraft $draft): void
    {
        if (in_array($draft->status, ['draft', 'needs_changes'], true)) {
            return;
        }

        throw new ApiProblemException('CONFLICT', 409);
    }

    private function ensureDeletable(AppealDraft $draft): void
    {
        if (in_array($draft->status, ['draft', 'needs_changes'], true)) {
            return;
        }

        throw new ApiProblemException('CONFLICT', 409);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function draftAttributes(
        array $payload,
        ?User $user = null,
        ?string $guestToken = null,
        bool $includeOwner = true,
    ): array {
        $attributes = [
            'category' => $payload['category'] ?? null,
            'submission_type' => $payload['submission_type'] ?? null,
            'title' => $payload['title'] ?? null,
            'description' => $payload['description'] ?? null,
            'urgency' => $payload['urgency'] ?? null,
            'location' => $payload['location'] ?? null,
            'contact_visibility' => $payload['contact_visibility'] ?? null,
            'contact_name' => $payload['contact_name'] ?? null,
            'contact_email' => $payload['contact_email'] ?? null,
            'contact_phone' => $payload['contact_phone'] ?? null,
        ];

        if (! $includeOwner) {
            return $attributes;
        }

        $attributes['status'] = 'draft';

        if ($user instanceof User) {
            $attributes['user_id'] = $user->id;

            return $attributes;
        }

        if ($guestToken !== null && $guestToken !== '') {
            $attributes['guest_token_hash'] = hash('sha256', $guestToken);
        }

        return $attributes;
    }

    private function attachmentKind(string $mime): string
    {
        return match ($mime) {
            'image/jpeg', 'image/png', 'image/webp' => 'image',
            'video/mp4', 'video/quicktime' => 'video',
            'application/pdf' => 'document',
            default => throw new ApiProblemException('ATTACHMENT_TYPE_NOT_ALLOWED', 422),
        };
    }

    private function ensureAttachmentSize(string $kind, int $size): void
    {
        $limit = match ($kind) {
            'image' => 10 * 1024 * 1024,
            'video' => 100 * 1024 * 1024,
            default => 20 * 1024 * 1024,
        };

        if ($size > $limit) {
            throw new ApiProblemException('VALIDATION_FAILED', 422, [
                'fields' => [
                    'file' => ['The file is too large.'],
                ],
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function draftPayload(AppealDraft $draft, ?string $guestToken = null): array
    {
        return [
            'id' => $draft->id,
            'guestToken' => $guestToken,
            'status' => $draft->status,
            'category' => $draft->category,
            'submissionType' => $draft->submission_type,
            'title' => $draft->title,
            'description' => $draft->description,
            'urgency' => $draft->urgency,
            'location' => $draft->location,
            'contactVisibility' => $draft->contact_visibility,
            'contactName' => $draft->contact_name,
            'contactEmail' => $draft->contact_email,
            'contactPhone' => $draft->contact_phone,
            'submittedAt' => $this->dateString($draft->submitted_at),
            'moderatedAt' => $this->dateString($draft->moderated_at),
            'moderationNote' => $draft->moderation_note,
            'rejectionReason' => $draft->rejection_reason,
            'publicAppealId' => $draft->public_appeal_id,
            'attachments' => $draft->attachments()
                ->latest()
                ->get()
                ->map(fn (AppealAttachment $attachment): array => $this->attachmentPayload($attachment))
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function attachmentPayload(AppealAttachment $attachment): array
    {
        return [
            'id' => $attachment->id,
            'kind' => $attachment->kind,
            'originalName' => $attachment->original_name,
            'mimeType' => $attachment->mime_type,
            'size' => $attachment->size,
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
