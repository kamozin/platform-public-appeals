<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class NewsSubscription extends Model
{
    protected $fillable = [
        'email',
        'status',
    ];
}
