<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Appeals;

use App\Application\Appeals\AppealDraftAccess;
use App\Application\Appeals\AppealDraftService;
use App\Application\Auth\AuthService;
use App\Http\Requests\Api\Appeals\AppealSubmitRequest;
use Illuminate\Http\JsonResponse;

final readonly class AppealDraftSubmitController
{
    public function __construct(
        private AuthService $auth,
        private AppealDraftService $drafts,
    ) {}

    public function __invoke(AppealSubmitRequest $request, string $id): JsonResponse
    {
        $payload = $request->validated();

        return response()->json([
            'data' => $this->drafts->submit(
                access: new AppealDraftAccess(
                    user: $this->auth->userOrNull($request),
                    guestToken: $request->headers->get('X-Appeal-Draft-Token'),
                ),
                draftId: $id,
                captchaToken: (string) $payload['captcha_token'],
            ),
        ]);
    }
}
