<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Subscriptions;

use App\Application\Subscriptions\SubscriptionService;
use App\Http\Requests\Api\Subscriptions\NewsSubscriptionRequest;
use Illuminate\Http\JsonResponse;

final readonly class NewsSubscriptionController
{
    public function __construct(private SubscriptionService $subscriptions) {}

    public function store(NewsSubscriptionRequest $request): JsonResponse
    {
        $payload = $request->validated();

        return response()->json([
            'data' => $this->subscriptions->subscribe((string) $payload['email']),
        ], 201);
    }

    public function destroy(NewsSubscriptionRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $this->subscriptions->unsubscribe((string) $payload['email']);

        return response()->json(null, 204);
    }
}
