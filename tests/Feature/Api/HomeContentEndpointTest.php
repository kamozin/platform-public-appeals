<?php

declare(strict_types=1);

use Database\Seeders\PublicContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\getJson;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    seed(PublicContentSeeder::class);
});

it('returns database backed home content', function (): void {
    getJson('/api/v1/home')
        ->assertOk()
        ->assertJsonPath('data.seo.title', 'Рука добра - платформа обращений и жалоб граждан')
        ->assertJsonPath('data.slides.0.slug', 'public-appeals-platform')
        ->assertJsonPath('data.advertisements.0.slug', 'contract-service-main')
        ->assertJsonPath('data.categoryGroups.0.slug', 'city')
        ->assertJsonCount(3, 'data.slides')
        ->assertJsonCount(3, 'data.advertisements')
        ->assertJsonCount(4, 'data.categoryGroups');
});

it('has a named home content route', function (): void {
    expect(route('api.home.show', absolute: false))->toBe('/api/v1/home');
});
