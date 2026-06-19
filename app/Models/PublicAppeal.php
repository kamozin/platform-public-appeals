<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class PublicAppeal extends Model
{
    use HasUuids;

    protected $fillable = [
        'slug',
        'title',
        'excerpt',
        'description',
        'status',
        'status_label',
        'city',
        'district',
        'category',
        'location',
        'published_at',
        'support_count',
        'views_count',
        'comments_count',
        'image_url',
        'is_public',
    ];

    /**
     * @return HasMany<PublicAppealAttachment, $this>
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(PublicAppealAttachment::class)->orderBy('sort_order');
    }

    /**
     * @return HasMany<PublicAppealTimelineItem, $this>
     */
    public function timelineItems(): HasMany
    {
        return $this->hasMany(PublicAppealTimelineItem::class)->orderBy('sort_order');
    }

    /**
     * @return HasMany<PublicAppealDocument, $this>
     */
    public function documents(): HasMany
    {
        return $this->hasMany(PublicAppealDocument::class)->orderBy('sort_order');
    }

    /**
     * @return HasOne<PublicAppealOfficialResponse, $this>
     */
    public function officialResponse(): HasOne
    {
        return $this->hasOne(PublicAppealOfficialResponse::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'support_count' => 'integer',
            'views_count' => 'integer',
            'comments_count' => 'integer',
            'is_public' => 'boolean',
        ];
    }
}
