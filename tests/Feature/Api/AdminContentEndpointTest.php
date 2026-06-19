<?php

declare(strict_types=1);

use App\Application\Auth\AuthService;
use App\Models\User;
use Database\Seeders\PublicContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\patchJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    seed(PublicContentSeeder::class);
});

function adminHeaders(bool $isAdmin = true): array
{
    $email = $isAdmin ? 'content-admin@example.com' : 'content-user@example.com';

    $user = User::query()->create([
        'name' => $isAdmin ? 'Content Admin' : 'Content User',
        'email' => $email,
        'password' => Hash::make('password123'),
        'is_admin' => $isAdmin,
    ]);

    $token = app(AuthService::class)->login($user->email, 'password123')['token'];

    return ['Authorization' => "Bearer $token"];
}

it('protects admin content endpoints', function (): void {
    getJson('/api/v1/admin/news')
        ->assertUnauthorized()
        ->assertJsonPath('error.code', 'UNAUTHORIZED');

    getJson('/api/v1/admin/news', adminHeaders(false))
        ->assertForbidden()
        ->assertJsonPath('error.code', 'FORBIDDEN');
});

it('creates updates and deletes an admin category', function (): void {
    $headers = adminHeaders();

    $id = postJson('/api/v1/admin/categories', [
        'group_slug' => 'test-group',
        'group_title' => 'Тестовая группа',
        'slug' => 'test-category',
        'title' => 'Тестовая категория',
        'description' => 'Описание тестовой категории',
        'icon' => 'file',
        'is_active' => true,
    ], $headers)
        ->assertCreated()
        ->assertJsonPath('data.slug', 'test-category')
        ->json('data.id');

    patchJson("/api/v1/admin/categories/$id", [
        'title' => 'Обновленная категория',
    ], $headers)
        ->assertOk()
        ->assertJsonPath('data.title', 'Обновленная категория');

    getJson('/api/v1/categories')
        ->assertOk()
        ->assertJsonFragment(['slug' => 'test-category']);

    deleteJson("/api/v1/admin/categories/$id", [], $headers)
        ->assertNoContent();
});

it('publishes news through admin api and exposes it publicly', function (): void {
    $headers = adminHeaders();

    $id = postJson('/api/v1/admin/news', [
        'slug' => 'admin-published-news',
        'title' => 'Новость из админки',
        'excerpt' => 'Короткое описание новости.',
        'content' => 'Полный текст новости из админки.',
        'category' => 'Админка',
        'image_url' => '/assets/hero-hands-heart.png',
        'status' => 'published',
    ], $headers)
        ->assertCreated()
        ->assertJsonPath('data.slug', 'admin-published-news')
        ->json('data.id');

    getJson('/api/v1/news/admin-published-news')
        ->assertOk()
        ->assertJsonPath('data.title', 'Новость из админки');

    patchJson("/api/v1/admin/news/$id", [
        'status' => 'archived',
    ], $headers)
        ->assertOk()
        ->assertJsonPath('data.status', 'archived');

    getJson('/api/v1/news/admin-published-news')
        ->assertNotFound()
        ->assertJsonPath('error.code', 'NOT_FOUND');
});

it('publishes an appeal through admin api and exposes it publicly', function (): void {
    $headers = adminHeaders();

    $id = postJson('/api/v1/admin/appeals', [
        'slug' => 'admin-public-appeal',
        'title' => 'Публичное обращение из админки',
        'excerpt' => 'Короткое описание обращения.',
        'description' => 'Подробное описание обращения.',
        'status' => 'checking',
        'status_label' => 'На проверке',
        'city' => 'Казань',
        'district' => 'Центральный район',
        'category' => 'Дороги',
        'location' => 'Казань',
        'support_count' => 5,
        'views_count' => 10,
        'comments_count' => 0,
        'image_url' => '/assets/issue-road.png',
        'is_public' => true,
        'attachments' => [
            ['type' => 'image', 'url' => '/assets/issue-road.png', 'title' => 'Фото проблемы'],
        ],
        'timeline' => [
            ['status' => 'published', 'title' => 'Опубликовано', 'happened_at' => '2026-06-19T10:00:00+03:00', 'text' => 'Обращение опубликовано.'],
        ],
        'documents' => [
            ['title' => 'Документ', 'url' => '#'],
        ],
        'official_response' => [
            'title' => 'Ответ ожидается',
            'text' => 'Запрос направлен.',
        ],
    ], $headers)
        ->assertCreated()
        ->assertJsonPath('data.slug', 'admin-public-appeal')
        ->json('data.id');

    getJson('/api/v1/appeals/admin-public-appeal')
        ->assertOk()
        ->assertJsonPath('data.title', 'Публичное обращение из админки')
        ->assertJsonCount(1, 'data.attachments');

    patchJson("/api/v1/admin/appeals/$id", [
        'is_public' => false,
    ], $headers)->assertOk();

    getJson('/api/v1/appeals/admin-public-appeal')
        ->assertNotFound()
        ->assertJsonPath('error.code', 'NOT_FOUND');
});

it('creates updates and deletes a homepage slide through admin api', function (): void {
    $headers = adminHeaders();

    $id = postJson('/api/v1/admin/homepage-slides', [
        'slug' => 'admin-home-slide',
        'label' => 'Слайд из админки',
        'title' => 'Заголовок слайда',
        'lead' => 'Описание слайда для главной страницы.',
        'note' => 'Дополнительная заметка.',
        'image_url' => '/assets/hero-civic-flag.png',
        'primary_cta_label' => 'Подать обращение',
        'primary_cta_url' => '/appeal/new',
        'secondary_cta_label' => 'Смотреть обращения',
        'secondary_cta_url' => '/appeals',
        'is_active' => true,
    ], $headers)
        ->assertCreated()
        ->assertJsonPath('data.slug', 'admin-home-slide')
        ->json('data.id');

    getJson('/api/v1/home')
        ->assertOk()
        ->assertJsonFragment(['slug' => 'admin-home-slide']);

    patchJson("/api/v1/admin/homepage-slides/$id", [
        'is_active' => false,
    ], $headers)
        ->assertOk()
        ->assertJsonPath('data.isActive', false);

    getJson('/api/v1/home')
        ->assertOk()
        ->assertJsonMissing(['slug' => 'admin-home-slide']);

    deleteJson("/api/v1/admin/homepage-slides/$id", [], $headers)
        ->assertNoContent();
});

it('creates updates and deletes an advertisement through admin api', function (): void {
    $headers = adminHeaders();

    $id = postJson('/api/v1/admin/advertisements', [
        'slug' => 'admin-home-ad',
        'placement' => 'home_promo',
        'title' => 'Реклама из админки',
        'label' => 'Партнерский блок',
        'description' => 'Описание рекламного баннера.',
        'image_url' => '/assets/114a584a-17bb-4ecb-95fd-c338df16704e.png',
        'alt' => 'Рекламный баннер',
        'target_url' => 'https://example.com/',
        'is_active' => true,
    ], $headers)
        ->assertCreated()
        ->assertJsonPath('data.slug', 'admin-home-ad')
        ->json('data.id');

    getJson('/api/v1/home')
        ->assertOk()
        ->assertJsonFragment(['slug' => 'admin-home-ad']);

    patchJson("/api/v1/admin/advertisements/$id", [
        'is_active' => false,
    ], $headers)
        ->assertOk()
        ->assertJsonPath('data.isActive', false);

    getJson('/api/v1/home')
        ->assertOk()
        ->assertJsonMissing(['slug' => 'admin-home-ad']);

    deleteJson("/api/v1/admin/advertisements/$id", [], $headers)
        ->assertNoContent();
});

it('has named admin content routes', function (): void {
    expect(route('api.admin.categories.index', absolute: false))->toBe('/api/v1/admin/categories');
    expect(route('api.admin.news.index', absolute: false))->toBe('/api/v1/admin/news');
    expect(route('api.admin.appeals.index', absolute: false))->toBe('/api/v1/admin/appeals');
    expect(route('api.admin.homepage-slides.index', absolute: false))->toBe('/api/v1/admin/homepage-slides');
    expect(route('api.admin.advertisements.index', absolute: false))->toBe('/api/v1/admin/advertisements');
});
