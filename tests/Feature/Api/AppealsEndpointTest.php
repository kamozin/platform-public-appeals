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

it('returns public appeals feed with summary and seo', function (): void {
    getJson('/api/v1/appeals')
        ->assertOk()
        ->assertJsonPath('data.items.0.slug', 'road-pits-after-repair')
        ->assertJsonPath('data.summary.publishedCount', 3)
        ->assertJsonPath('data.summary.supportCount', 223)
        ->assertJsonPath('data.seo.robots', 'index,follow')
        ->assertJsonCount(3, 'data.items');
});

it('filters public appeals and marks filtered pages noindex', function (): void {
    getJson('/api/v1/appeals?city=Москва&category=Дороги&sort=popular')
        ->assertOk()
        ->assertJsonPath('data.items.0.slug', 'road-pits-after-repair')
        ->assertJsonPath('data.pagination.total', 1)
        ->assertJsonPath('data.seo.robots', 'noindex,follow');
});

it('returns a public appeal detail dto', function (): void {
    getJson('/api/v1/appeals/road-pits-after-repair')
        ->assertOk()
        ->assertJsonPath('data.slug', 'road-pits-after-repair')
        ->assertJsonPath('data.status', 'active')
        ->assertJsonPath('data.seo.canonicalUrl', config('app.url').'/appeals/road-pits-after-repair')
        ->assertJsonStructure([
            'data' => [
                'id',
                'slug',
                'title',
                'description',
                'status',
                'timeline',
                'attachments',
                'officialResponse',
                'commentsPreview',
                'seo',
            ],
        ]);
});

it('does not expose private appeal detail pages', function (): void {
    getJson('/api/v1/appeals/private-draft-example')
        ->assertNotFound()
        ->assertJsonPath('error.code', 'NOT_FOUND');
});

it('returns public appeal comments preview list', function (): void {
    getJson('/api/v1/appeals/road-pits-after-repair/comments')
        ->assertOk()
        ->assertJsonFragment(['type' => 'public'])
        ->assertJsonCount(2, 'data.items');
});

it('validates appeal filters', function (): void {
    getJson('/api/v1/appeals?status=draft')
        ->assertUnprocessable()
        ->assertJsonPath('error.code', 'VALIDATION_FAILED');
});

it('has named appeal routes', function (): void {
    expect(route('api.appeals.index', absolute: false))->toBe('/api/v1/appeals');
    expect(route('api.appeals.show', ['slug' => 'road-pits-after-repair'], false))
        ->toBe('/api/v1/appeals/road-pits-after-repair');
    expect(route('api.appeals.comments.index', ['appeal' => 'road-pits-after-repair'], false))
        ->toBe('/api/v1/appeals/road-pits-after-repair/comments');
});
