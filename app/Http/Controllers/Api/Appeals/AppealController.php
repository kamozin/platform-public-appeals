<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Appeals;

use App\Application\PublicContent\PublicContentService;
use App\Http\Requests\Api\Appeals\AppealIndexRequest;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class AppealController
{
    public function __construct(private PublicContentService $content) {}

    public function index(AppealIndexRequest $request): JsonResponse
    {
        $payload = $request->validated();

        return response()->json([
            'data' => $this->content->appealsIndex([
                'search' => $payload['search'] ?? null,
                'status' => $payload['status'] ?? null,
                'city' => $payload['city'] ?? null,
                'category' => $payload['category'] ?? null,
                'sort' => $payload['sort'] ?? null,
                'page' => max(1, (int) ($payload['page'] ?? 1)),
                'perPage' => max(1, min(24, (int) ($payload['per_page'] ?? 6))),
            ]),
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $item = $this->content->appealShow($slug);

        if ($item === null) {
            throw new NotFoundHttpException;
        }

        return response()->json([
            'data' => $item,
        ]);
    }
}
