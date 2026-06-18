<?php

declare(strict_types=1);

use function Pest\Laravel\getJson;

it('returns api error contract for missing routes', function (): void {
    getJson('/api/v1/missing', ['X-Request-Id' => 'trace-test'])
        ->assertNotFound()
        ->assertHeader('X-Request-Id', 'trace-test')
        ->assertJsonPath('error.code', 'NOT_FOUND')
        ->assertJsonPath('error.message', 'Resource not found.')
        ->assertJsonPath('error.trace_id', 'trace-test')
        ->assertJsonStructure([
            'error' => [
                'code',
                'message',
                'details',
                'trace_id',
            ],
        ]);
});
