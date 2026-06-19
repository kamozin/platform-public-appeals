<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class AppealModerationEvent extends Model
{
    use HasUuids;

    protected $fillable = [
        'appeal_draft_id',
        'moderator_user_id',
        'action',
        'comment',
        'payload',
    ];

    /**
     * @return BelongsTo<AppealDraft, $this>
     */
    public function draft(): BelongsTo
    {
        return $this->belongsTo(AppealDraft::class, 'appeal_draft_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderator_user_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payload' => 'array',
        ];
    }
}
