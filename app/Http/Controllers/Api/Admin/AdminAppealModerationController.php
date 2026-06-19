<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Application\Admin\AppealModerationService;
use App\Application\Auth\AuthService;
use App\Http\Requests\Api\Admin\AdminAppealModerationApproveRequest;
use App\Http\Requests\Api\Admin\AdminAppealModerationIndexRequest;
use App\Http\Requests\Api\Admin\AdminAppealModerationRejectRequest;
use App\Http\Requests\Api\Admin\AdminAppealModerationRequestChangesRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final readonly class AdminAppealModerationController
{
    public function __construct(
        private AppealModerationService $moderation,
        private AuthService $auth,
    ) {}

    public function index(AdminAppealModerationIndexRequest $request): JsonResponse
    {
        $this->auth->requireAdmin($request);
        $payload = $request->validated();

        return response()->json([
            'data' => $this->moderation->list(
                status: $payload['status'] ?? null,
                page: (int) ($payload['page'] ?? 1),
                perPage: (int) ($payload['per_page'] ?? 15),
            ),
        ]);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $this->auth->requireAdmin($request);

        return response()->json([
            'data' => $this->moderation->show($id),
        ]);
    }

    public function requestChanges(AdminAppealModerationRequestChangesRequest $request, string $id): JsonResponse
    {
        $admin = $this->auth->requireAdmin($request);
        $payload = $request->validated();

        return response()->json([
            'data' => $this->moderation->requestChanges(
                id: $id,
                moderator: $admin,
                message: (string) $payload['message'],
            ),
        ]);
    }

    public function reject(AdminAppealModerationRejectRequest $request, string $id): JsonResponse
    {
        $admin = $this->auth->requireAdmin($request);
        $payload = $request->validated();

        return response()->json([
            'data' => $this->moderation->reject(
                id: $id,
                moderator: $admin,
                reason: (string) $payload['reason'],
            ),
        ]);
    }

    public function approve(AdminAppealModerationApproveRequest $request, string $id): JsonResponse
    {
        $admin = $this->auth->requireAdmin($request);

        return response()->json([
            'data' => $this->moderation->approve(
                id: $id,
                moderator: $admin,
                payload: $request->validated(),
            ),
        ]);
    }
}
