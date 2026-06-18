<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Appeals;

use App\Application\Appeals\AppealInteractionService;
use App\Application\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final readonly class AppealSaveController
{
    public function __construct(
        private AuthService $auth,
        private AppealInteractionService $interactions,
    ) {}

    public function store(Request $request, string $appeal): JsonResponse
    {
        return response()->json([
            'data' => $this->interactions->save($this->auth->requireUser($request), $appeal),
        ], 201);
    }

    public function destroy(Request $request, string $appeal): JsonResponse
    {
        $this->interactions->unsave($this->auth->requireUser($request), $appeal);

        return response()->json(null, 204);
    }
}
