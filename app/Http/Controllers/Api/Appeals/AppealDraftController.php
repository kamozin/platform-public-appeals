<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Appeals;

use App\Application\Appeals\AppealDraftAccess;
use App\Application\Appeals\AppealDraftService;
use App\Application\Auth\AuthService;
use App\Http\Requests\Api\Appeals\AppealDraftRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final readonly class AppealDraftController
{
    public function __construct(
        private AuthService $auth,
        private AppealDraftService $drafts,
    ) {}

    public function store(AppealDraftRequest $request): JsonResponse
    {
        return response()->json([
            'data' => $this->drafts->create($this->draftAccess($request), $request->validated()),
        ], 201);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        return response()->json([
            'data' => $this->drafts->show($this->draftAccess($request), $id),
        ]);
    }

    public function update(AppealDraftRequest $request, string $id): JsonResponse
    {
        return response()->json([
            'data' => $this->drafts->update($this->draftAccess($request), $id, $request->validated()),
        ]);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $this->drafts->delete($this->draftAccess($request), $id);

        return response()->json(null, 204);
    }

    private function draftAccess(Request $request): AppealDraftAccess
    {
        return new AppealDraftAccess(
            user: $this->auth->userOrNull($request),
            guestToken: $request->headers->get('X-Appeal-Draft-Token'),
        );
    }
}
