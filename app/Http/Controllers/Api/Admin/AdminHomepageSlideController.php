<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Application\Admin\AdminContentService;
use App\Application\Auth\AuthService;
use App\Http\Requests\Api\Admin\AdminHomepageSlideRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final readonly class AdminHomepageSlideController
{
    public function __construct(
        private AdminContentService $content,
        private AuthService $auth,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->auth->requireAdmin($request);

        return response()->json([
            'data' => $this->content->homepageSlides(),
        ]);
    }

    public function store(AdminHomepageSlideRequest $request): JsonResponse
    {
        $this->auth->requireAdmin($request);

        return response()->json([
            'data' => $this->content->createHomepageSlide($request->validated()),
        ], 201);
    }

    public function update(AdminHomepageSlideRequest $request, string $id): JsonResponse
    {
        $this->auth->requireAdmin($request);

        return response()->json([
            'data' => $this->content->updateHomepageSlide($id, $request->validated()),
        ]);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $this->auth->requireAdmin($request);
        $this->content->deleteHomepageSlide($id);

        return response()->json(null, 204);
    }
}
