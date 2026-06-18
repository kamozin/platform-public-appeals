<?php

declare(strict_types=1);

namespace App\Application\Subscriptions;

use App\Models\NewsSubscription;
use App\Models\SupportVideo;
use App\Models\User;
use App\Support\Api\ApiProblemException;
use Illuminate\Http\UploadedFile;

final class SubscriptionService
{
    /**
     * @return array<string, mixed>
     */
    public function subscribe(string $email): array
    {
        $subscription = NewsSubscription::query()->updateOrCreate(
            ['email' => $email],
            ['status' => 'active'],
        );

        return [
            'email' => $subscription->email,
            'status' => $subscription->status,
        ];
    }

    public function unsubscribe(string $email): void
    {
        NewsSubscription::query()->updateOrCreate(
            ['email' => $email],
            ['status' => 'unsubscribed'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function storeSupportVideo(?User $user, ?string $authorEmail, UploadedFile $file): array
    {
        $mime = (string) $file->getMimeType();

        if (! in_array($mime, ['video/mp4', 'video/quicktime'], true)) {
            throw new ApiProblemException('VIDEO_TYPE_NOT_ALLOWED', 422);
        }

        if ((int) $file->getSize() > 100 * 1024 * 1024) {
            throw new ApiProblemException('VIDEO_TOO_LARGE', 422);
        }

        $video = SupportVideo::query()->create([
            'user_id' => $user?->id,
            'author_email' => $authorEmail,
            'original_name' => $file->getClientOriginalName(),
            'path' => $file->store('support-videos'),
            'mime_type' => $mime,
            'size' => (int) $file->getSize(),
            'status' => 'pending_moderation',
        ]);

        return [
            'id' => $video->id,
            'status' => $video->status,
        ];
    }
}
