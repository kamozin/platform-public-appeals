<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Application\Admin\AdminContentService;
use App\Application\Auth\AuthService;
use App\Http\Requests\Api\Admin\AdminCategoryRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final readonly class AdminCategoryController
{
    public function __construct(
        private AdminContentService $content,
        private AuthService $auth,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->auth->requireAdmin($request);

        return response()->json([
            'data' => $this->content->categories(),
        ]);
    }

    public function store(AdminCategoryRequest $request): JsonResponse
    {
        $this->auth->requireAdmin($request);

        return response()->json([
            'data' => $this->content->createCategory($request->validated()),
        ], 201);
    }

    public function update(AdminCategoryRequest $request, string $id): JsonResponse
    {
        $this->auth->requireAdmin($request);

        return response()->json([
            'data' => $this->content->updateCategory($id, $request->validated()),
        ]);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $this->auth->requireAdmin($request);
        $this->content->deleteCategory($id);

        return response()->json(null, 204);
    }
}
