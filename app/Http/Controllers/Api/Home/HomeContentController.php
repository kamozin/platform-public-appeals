<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Home;

use App\Application\PublicContent\PublicContentService;
use Illuminate\Http\JsonResponse;

final readonly class HomeContentController
{
    public function __construct(private PublicContentService $content) {}

    public function __invoke(): JsonResponse
    {
        return response()->json([
            'data' => $this->content->home(),
        ]);
    }
}
