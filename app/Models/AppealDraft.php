<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class AppealDraft extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'guest_token_hash',
        'status',
        'category',
        'submission_type',
        'title',
        'description',
        'urgency',
        'location',
        'contact_visibility',
        'contact_name',
        'contact_email',
        'contact_phone',
        'submitted_at',
        'moderated_by_user_id',
        'moderated_at',
        'moderation_note',
        'rejection_reason',
        'public_appeal_id',
    ];

    /**
     * @return HasMany<AppealAttachment, $this>
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(AppealAttachment::class);
    }

    /**
     * @return HasMany<AppealModerationEvent, $this>
     */
    public function moderationEvents(): HasMany
    {
        return $this->hasMany(AppealModerationEvent::class)->orderBy('created_at');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by_user_id');
    }

    /**
     * @return BelongsTo<PublicAppeal, $this>
     */
    public function publicAppeal(): BelongsTo
    {
        return $this->belongsTo(PublicAppeal::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'moderated_at' => 'datetime',
        ];
    }
}
