<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Application\Admin\AdminContentService;
use App\Application\Auth\AuthService;
use App\Http\Requests\Api\Admin\AdminNewsRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final readonly class AdminNewsController
{
    public function __construct(
        private AdminContentService $content,
        private AuthService $auth,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->auth->requireAdmin($request);

        return response()->json([
            'data' => $this->content->news(),
        ]);
    }

    public function store(AdminNewsRequest $request): JsonResponse
    {
        $this->auth->requireAdmin($request);

        return response()->json([
            'data' => $this->content->createNews($request->validated()),
        ], 201);
    }

    public function update(AdminNewsRequest $request, string $id): JsonResponse
    {
        $this->auth->requireAdmin($request);

        return response()->json([
            'data' => $this->content->updateNews($id, $request->validated()),
        ]);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $this->auth->requireAdmin($request);
        $this->content->deleteNews($id);

        return response()->json(null, 204);
    }
}
