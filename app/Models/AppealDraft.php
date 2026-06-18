<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
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
    ];

    /**
     * @return HasMany<AppealAttachment, $this>
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(AppealAttachment::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
        ];
    }
}
