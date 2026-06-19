<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Application\Admin\AdminContentService;
use App\Application\Auth\AuthService;
use App\Http\Requests\Api\Admin\AdminAdvertisementRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final readonly class AdminAdvertisementController
{
    public function __construct(
        private AdminContentService $content,
        private AuthService $auth,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->auth->requireAdmin($request);

        return response()->json([
            'data' => $this->content->advertisements(),
        ]);
    }

    public function store(AdminAdvertisementRequest $request): JsonResponse
    {
        $this->auth->requireAdmin($request);

        return response()->json([
            'data' => $this->content->createAdvertisement($request->validated()),
        ], 201);
    }

    public function update(AdminAdvertisementRequest $request, string $id): JsonResponse
    {
        $this->auth->requireAdmin($request);

        return response()->json([
            'data' => $this->content->updateAdvertisement($id, $request->validated()),
        ]);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $this->auth->requireAdmin($request);
        $this->content->deleteAdvertisement($id);

        return response()->json(null, 204);
    }
}
