<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Application\Auth\VerificationService;
use App\Http\Requests\Api\Auth\PasswordResetCompleteRequest;
use App\Http\Requests\Api\Auth\PasswordResetSendRequest;
use App\Http\Requests\Api\Auth\VerificationSendRequest;
use App\Http\Requests\Api\Auth\VerificationVerifyRequest;
use Illuminate\Http\JsonResponse;

final readonly class VerificationController
{
    public function __construct(private VerificationService $verification) {}

    public function send(VerificationSendRequest $request): JsonResponse
    {
        $payload = $request->validated();

        return response()->json([
            'data' => $this->verification->send(
                purpose: 'verification',
                channel: (string) $payload['channel'],
                target: (string) $payload['target'],
            ),
        ], 201);
    }

    public function verify(VerificationVerifyRequest $request): JsonResponse
    {
        $payload = $request->validated();

        return response()->json([
            'data' => $this->verification->verify(
                id: (string) $payload['challenge_id'],
                code: (string) $payload['code'],
                purpose: 'verification',
            ),
        ]);
    }

    public function sendPasswordReset(PasswordResetSendRequest $request): JsonResponse
    {
        $payload = $request->validated();

        return response()->json([
            'data' => $this->verification->send(
                purpose: 'password_reset',
                channel: 'email',
                target: (string) $payload['email'],
            ),
        ], 201);
    }

    public function verifyPasswordReset(VerificationVerifyRequest $request): JsonResponse
    {
        $payload = $request->validated();

        return response()->json([
            'data' => $this->verification->verify(
                id: (string) $payload['challenge_id'],
                code: (string) $payload['code'],
                purpose: 'password_reset',
            ),
        ]);
    }

    public function completePasswordReset(PasswordResetCompleteRequest $request): JsonResponse
    {
        $payload = $request->validated();

        return response()->json([
            'data' => $this->verification->completePasswordReset(
                id: (string) $payload['challenge_id'],
                code: (string) $payload['code'],
                email: (string) $payload['email'],
                password: (string) $payload['password'],
            ),
        ]);
    }
}
