<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Dashboard;

use App\Application\Auth\AuthService;
use App\Application\Dashboard\DashboardService;
use App\Http\Requests\Api\Dashboard\NotificationSettingsRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final readonly class DashboardController
{
    public function __construct(
        private AuthService $auth,
        private DashboardService $dashboard,
    ) {}

    public function appeals(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->dashboard->appeals($this->auth->requireUser($request))]);
    }

    public function drafts(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->dashboard->drafts($this->auth->requireUser($request))]);
    }

    public function savedAppeals(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->dashboard->savedAppeals($this->auth->requireUser($request))]);
    }

    public function comments(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->dashboard->comments($this->auth->requireUser($request))]);
    }

    public function notifications(Request $request): JsonResponse
    {
        $this->auth->requireUser($request);

        return response()->json(['data' => $this->dashboard->notifications()]);
    }

    public function markAllNotificationsRead(Request $request): JsonResponse
    {
        $this->auth->requireUser($request);

        return response()->json(['data' => $this->dashboard->markAllNotificationsRead()]);
    }

    public function updateNotificationSettings(NotificationSettingsRequest $request): JsonResponse
    {
        $payload = $request->validated();

        return response()->json([
            'data' => $this->dashboard->updateNotificationSettings(
                user: $this->auth->requireUser($request),
                enabled: (bool) $payload['notifications'],
            ),
        ]);
    }

    public function statusHistory(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->dashboard->statusHistory($this->auth->requireUser($request))]);
    }

    public function sessions(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->dashboard->sessions($this->auth->requireUser($request))]);
    }

    public function terminateSession(Request $request, string $id): JsonResponse
    {
        $this->dashboard->terminateSession(
            user: $this->auth->requireUser($request),
            currentToken: $this->auth->currentToken($request),
            id: $id,
        );

        return response()->json(null, 204);
    }

    public function achievements(Request $request): JsonResponse
    {
        $this->auth->requireUser($request);

        return response()->json(['data' => $this->dashboard->achievements()]);
    }
}
