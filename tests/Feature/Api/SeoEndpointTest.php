<?php

declare(strict_types=1);

use function Pest\Laravel\getJson;

it('returns public sitemap urls for the frontend sitemap builder', function (): void {
    getJson('/api/v1/seo/sitemap-urls')
        ->assertOk()
        ->assertJsonPath('data.0.loc', config('app.url').'/')
        ->assertJsonFragment([
            'loc' => config('app.url').'/appeals/road-pits-after-repair',
        ]);
});

it('has a named sitemap urls route', function (): void {
    expect(route('api.seo.sitemap-urls', absolute: false))->toBe('/api/v1/seo/sitemap-urls');
});
