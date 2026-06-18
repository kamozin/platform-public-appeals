<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Categories;

use App\Application\PublicContent\PublicContentService;
use Illuminate\Http\JsonResponse;

final readonly class CategoryController
{
    public function __construct(private PublicContentService $content) {}

    public function __invoke(): JsonResponse
    {
        return response()->json([
            'data' => $this->content->categories(),
        ]);
    }
}
