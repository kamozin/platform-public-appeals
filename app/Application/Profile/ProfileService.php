<?php

declare(strict_types=1);

namespace App\Application\Profile;

use App\Application\Auth\AuthService;
use App\Models\User;
use App\Support\Api\ApiProblemException;
use Illuminate\Http\UploadedFile;

final class ProfileService
{
    public function __construct(private readonly AuthService $auth) {}

    /**
     * @return array<string, mixed>
     */
    public function profile(User $user): array
    {
        return $this->auth->userPayload($user);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function update(User $user, array $payload): array
    {
        $user->forceFill([
            'name' => $payload['name'] ?? $user->name,
            'phone' => $payload['phone'] ?? $user->phone,
            'notifications_enabled' => $payload['notifications'] ?? $user->notifications_enabled,
        ])->save();

        return $this->profile($user->refresh());
    }

    /**
     * @return array<string, mixed>
     */
    public function uploadAvatar(User $user, UploadedFile $file): array
    {
        $mime = (string) $file->getMimeType();

        if (! in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            throw new ApiProblemException('AVATAR_TYPE_NOT_ALLOWED', 422);
        }

        if ((int) $file->getSize() > 2 * 1024 * 1024) {
            throw new ApiProblemException('AVATAR_TOO_LARGE', 422);
        }

        $path = $file->store('avatars');
        $user->forceFill(['avatar_path' => $path])->save();

        return $this->profile($user->refresh());
    }

    public function deleteAvatar(User $user): void
    {
        $user->forceFill(['avatar_path' => null])->save();
    }
}
