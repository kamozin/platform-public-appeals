<?php

declare(strict_types=1);

namespace App\Support\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use stdClass;

final class ApiErrorResponseFactory
{
    /**
     * @param  array<string, mixed>  $details
     */
    public function make(Request $request, string $code, int $status, array $details = []): JsonResponse
    {
        $traceId = $request->headers->get('X-Request-Id');

        if ($traceId === null || trim($traceId) === '') {
            $traceId = (string) Str::uuid();
        }

        $normalizedDetails = new stdClass;

        if ($details !== []) {
            $normalizedDetails = (object) $details;
        }

        return response()
            ->json([
                'error' => [
                    'code' => $code,
                    'message' => trans("api_errors.$code"),
                    'details' => $normalizedDetails,
                    'trace_id' => $traceId,
                ],
            ], $status)
            ->header('X-Request-Id', $traceId);
    }
}
