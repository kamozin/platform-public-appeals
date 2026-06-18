<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

final class SupportVideo extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'author_email',
        'original_name',
        'path',
        'mime_type',
        'size',
        'status',
    ];
}
