<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Application\Admin\AdminContentService;
use App\Application\Auth\AuthService;
use App\Http\Requests\Api\Admin\AdminAppealRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final readonly class AdminAppealController
{
    public function __construct(
        private AdminContentService $content,
        private AuthService $auth,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->auth->requireAdmin($request);

        return response()->json([
            'data' => $this->content->appeals(),
        ]);
    }

    public function store(AdminAppealRequest $request): JsonResponse
    {
        $this->auth->requireAdmin($request);

        return response()->json([
            'data' => $this->content->createAppeal($request->validated()),
        ], 201);
    }

    public function update(AdminAppealRequest $request, string $id): JsonResponse
    {
        $this->auth->requireAdmin($request);

        return response()->json([
            'data' => $this->content->updateAppeal($id, $request->validated()),
        ]);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $this->auth->requireAdmin($request);
        $this->content->deleteAppeal($id);

        return response()->json(null, 204);
    }
}
