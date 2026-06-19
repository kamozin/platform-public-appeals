<?php

declare(strict_types=1);

namespace App\Application\Profile;

use App\Application\Auth\AuthService;
use App\Models\User;
use App\Support\Api\ApiProblemException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Throwable;

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

        $disk = Storage::disk('public');
        $oldPath = $this->avatarPathOrNull($user->avatar_path);
        $path = $file->store('avatars', 'public');

        if (! is_string($path) || $path === '') {
            throw new ApiProblemException('AVATAR_UPLOAD_FAILED', 500);
        }

        try {
            $user->forceFill(['avatar_path' => $path])->save();
        } catch (Throwable $exception) {
            $disk->delete($path);

            throw $exception;
        }

        if ($oldPath !== null && $oldPath !== $path) {
            $disk->delete($oldPath);
        }

        return $this->profile($user->refresh());
    }

    public function deleteAvatar(User $user): void
    {
        $oldPath = $this->avatarPathOrNull($user->avatar_path);
        $user->forceFill(['avatar_path' => null])->save();

        if ($oldPath !== null) {
            Storage::disk('public')->delete($oldPath);
        }
    }

    private function avatarPathOrNull(?string $path): ?string
    {
        if ($path === null || $path === '' || $path === '0') {
            return null;
        }

        return $path;
    }
}
