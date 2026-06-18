<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Subscriptions;

use App\Application\Auth\AuthService;
use App\Application\Subscriptions\SubscriptionService;
use App\Http\Requests\Api\Subscriptions\SupportVideoRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;

final readonly class SupportVideoController
{
    public function __construct(
        private AuthService $auth,
        private SubscriptionService $subscriptions,
    ) {}

    public function __invoke(SupportVideoRequest $request): JsonResponse
    {
        $file = $request->file('video');

        if (! $file instanceof UploadedFile) {
            abort(422);
        }

        $user = null;

        if ($request->headers->has('Authorization')) {
            $user = $this->auth->requireUser($request);
        }

        $payload = $request->validated();

        return response()->json([
            'data' => $this->subscriptions->storeSupportVideo(
                user: $user instanceof User ? $user : null,
                authorEmail: isset($payload['email']) ? (string) $payload['email'] : null,
                file: $file,
            ),
        ], 201);
    }
}
