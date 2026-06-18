<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Appeals;

use App\Application\Appeals\AppealInteractionService;
use App\Application\Auth\AuthService;
use App\Http\Requests\Api\Appeals\StoreAppealCommentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class AppealCommentController
{
    public function __construct(
        private AppealInteractionService $interactions,
        private AuthService $auth,
    ) {}

    public function index(Request $request, string $appeal): JsonResponse
    {
        $filter = $request->query('filter');

        if ($filter !== null && ! in_array($filter, ['official', 'media'], true)) {
            throw new NotFoundHttpException;
        }

        return response()->json([
            'data' => $this->interactions->comments($appeal, is_string($filter) ? $filter : null),
        ]);
    }

    public function store(StoreAppealCommentRequest $request, string $appeal): JsonResponse
    {
        $payload = $request->validated();

        return response()->json([
            'data' => $this->interactions->storeComment(
                user: $this->auth->requireUser($request),
                slug: $appeal,
                comment: (string) $payload['comment'],
            ),
        ], 201);
    }
}
