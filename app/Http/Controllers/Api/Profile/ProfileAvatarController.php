<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Profile;

use App\Application\Auth\AuthService;
use App\Application\Profile\ProfileService;
use App\Http\Requests\Api\Profile\AvatarRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

final readonly class ProfileAvatarController
{
    public function __construct(
        private AuthService $auth,
        private ProfileService $profile,
    ) {}

    public function store(AvatarRequest $request): JsonResponse
    {
        $file = $request->file('avatar');

        if (! $file instanceof UploadedFile) {
            abort(422);
        }

        return response()->json([
            'data' => $this->profile->uploadAvatar($this->auth->requireUser($request), $file),
        ], 201);
    }

    public function destroy(Request $request): JsonResponse
    {
        $this->profile->deleteAvatar($this->auth->requireUser($request));

        return response()->json(null, 204);
    }
}
