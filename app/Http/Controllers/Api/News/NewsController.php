<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\News;

use App\Application\PublicContent\PublicContentService;
use App\Http\Requests\Api\News\NewsIndexRequest;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class NewsController
{
    public function __construct(private PublicContentService $content) {}

    public function index(NewsIndexRequest $request): JsonResponse
    {
        $payload = $request->validated();

        return response()->json([
            'data' => $this->content->newsIndex(
                page: max(1, (int) ($payload['page'] ?? 1)),
                perPage: max(1, min(24, (int) ($payload['per_page'] ?? 6))),
            ),
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $item = $this->content->newsShow($slug);

        if ($item === null) {
            throw new NotFoundHttpException;
        }

        return response()->json([
            'data' => $item,
        ]);
    }
}
