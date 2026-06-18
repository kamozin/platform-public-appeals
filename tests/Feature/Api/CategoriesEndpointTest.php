<?php

declare(strict_types=1);

use function Pest\Laravel\getJson;

it('returns grouped public categories with seo metadata', function (): void {
    getJson('/api/v1/categories')
        ->assertOk()
        ->assertJsonPath('data.seo.title', 'Все категории обращений')
        ->assertJsonPath('data.seo.robots', 'index,follow')
        ->assertJsonPath('data.groups.0.slug', 'city')
        ->assertJsonPath('data.groups.0.categories.0.slug', 'zhkh')
        ->assertJsonCount(3, 'data.groups');
});

it('has a named categories route', function (): void {
    expect(route('api.categories.index', absolute: false))->toBe('/api/v1/categories');
});
