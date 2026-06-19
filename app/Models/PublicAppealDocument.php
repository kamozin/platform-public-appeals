<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PublicAppealDocument extends Model
{
    use HasUuids;

    protected $fillable = [
        'public_appeal_id',
        'title',
        'url',
        'sort_order',
    ];

    /**
     * @return BelongsTo<PublicAppeal, $this>
     */
    public function appeal(): BelongsTo
    {
        return $this->belongsTo(PublicAppeal::class, 'public_appeal_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }
}
