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

it('returns paginated news ordered by publication date', function (): void {
    getJson('/api/v1/news?per_page=2')
        ->assertOk()
        ->assertJsonPath('data.items.0.slug', 'legal-support-consultations')
        ->assertJsonPath('data.pagination.perPage', 2)
        ->assertJsonPath('data.pagination.total', 3)
        ->assertJsonPath('data.seo.title', 'Новости')
        ->assertJsonCount(2, 'data.items');
});

it('returns a public news detail page dto', function (): void {
    getJson('/api/v1/news/legal-support-consultations')
        ->assertOk()
        ->assertJsonPath('data.slug', 'legal-support-consultations')
        ->assertJsonPath('data.seo.canonicalUrl', config('app.url').'/news/legal-support-consultations')
        ->assertJsonStructure([
            'data' => [
                'id',
                'slug',
                'title',
                'excerpt',
                'content',
                'publishedAt',
                'category',
                'imageUrl',
                'seo',
            ],
        ]);
});

it('returns api error for a missing news item', function (): void {
    getJson('/api/v1/news/missing-news-item')
        ->assertNotFound()
        ->assertJsonPath('error.code', 'NOT_FOUND');
});

it('validates news pagination query', function (): void {
    getJson('/api/v1/news?per_page=99')
        ->assertUnprocessable()
        ->assertJsonPath('error.code', 'VALIDATION_FAILED');
});

it('has named news routes', function (): void {
    expect(route('api.news.index', absolute: false))->toBe('/api/v1/news');
    expect(route('api.news.show', ['slug' => 'legal-support-consultations'], false))
        ->toBe('/api/v1/news/legal-support-consultations');
});
