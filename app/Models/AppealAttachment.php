<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

final class AppealAttachment extends Model
{
    use HasUuids;

    protected $fillable = [
        'appeal_draft_id',
        'user_id',
        'kind',
        'original_name',
        'path',
        'mime_type',
        'size',
    ];
}
