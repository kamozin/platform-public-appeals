<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Profile;

use App\Application\Auth\AuthService;
use App\Application\Profile\ProfileService;
use App\Http\Requests\Api\Profile\ProfileUpdateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final readonly class ProfileController
{
    public function __construct(
        private AuthService $auth,
        private ProfileService $profile,
    ) {}

    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->profile->profile($this->auth->requireUser($request)),
        ]);
    }

    public function update(ProfileUpdateRequest $request): JsonResponse
    {
        return response()->json([
            'data' => $this->profile->update($this->auth->requireUser($request), $request->validated()),
        ]);
    }
}
