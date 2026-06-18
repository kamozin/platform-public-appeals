<?php

declare(strict_types=1);

use function Pest\Laravel\getJson;

it('returns health status', function (): void {
    getJson('/api/v1/health')
        ->assertOk()
        ->assertExactJson([
            'data' => [
                'status' => 'ok',
            ],
        ]);
});

it('has a named health route', function (): void {
    expect(route('api.health', absolute: false))->toBe('/api/v1/health');
});
