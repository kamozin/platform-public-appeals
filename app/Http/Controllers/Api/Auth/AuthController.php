<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Application\Auth\AuthService;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\RegisterRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final readonly class AuthController
{
    public function __construct(private AuthService $auth) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        return response()->json([
            'data' => $this->auth->register($request->validated()),
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $payload = $request->validated();

        return response()->json([
            'data' => $this->auth->login(
                login: (string) $payload['login'],
                password: (string) $payload['password'],
            ),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->auth->userPayload($this->auth->requireUser($request)),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->auth->logout($request);

        return response()->json(null, 204);
    }
}
