<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Profile;

use App\Application\Auth\AuthService;
use App\Application\Profile\ProfileSecurityService;
use App\Http\Requests\Api\Profile\EmailTwoFactorDisableRequest;
use App\Http\Requests\Api\Profile\EmailTwoFactorEnableRequest;
use App\Http\Requests\Api\Profile\EmailTwoFactorSendRequest;
use App\Http\Requests\Api\Profile\PasswordUpdateRequest;
use Illuminate\Http\JsonResponse;

final readonly class ProfileSecurityController
{
    public function __construct(
        private AuthService $auth,
        private ProfileSecurityService $security,
    ) {}

    public function updatePassword(PasswordUpdateRequest $request): JsonResponse
    {
        $payload = $request->validated();

        return response()->json([
            'data' => $this->security->changePassword(
                user: $this->auth->requireUser($request),
                currentToken: $this->auth->currentToken($request),
                currentPassword: (string) $payload['current_password'],
                password: (string) $payload['password'],
            ),
        ]);
    }

    public function sendEmailTwoFactor(EmailTwoFactorSendRequest $request): JsonResponse
    {
        $payload = $request->validated();

        return response()->json([
            'data' => $this->security->sendEmailTwoFactorEnableCode(
                user: $this->auth->requireUser($request),
                currentPassword: (string) $payload['current_password'],
            ),
        ], 201);
    }

    public function enableEmailTwoFactor(EmailTwoFactorEnableRequest $request): JsonResponse
    {
        $payload = $request->validated();

        return response()->json([
            'data' => $this->security->enableEmailTwoFactor(
                user: $this->auth->requireUser($request),
                challengeId: (string) $payload['challenge_id'],
                code: (string) $payload['code'],
            ),
        ]);
    }

    public function disableEmailTwoFactor(EmailTwoFactorDisableRequest $request): JsonResponse
    {
        $payload = $request->validated();

        return response()->json([
            'data' => $this->security->disableEmailTwoFactor(
                user: $this->auth->requireUser($request),
                currentPassword: (string) $payload['current_password'],
            ),
        ]);
    }
}
