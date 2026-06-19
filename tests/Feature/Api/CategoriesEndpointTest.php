<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\CategoryGroup;
use Database\Seeders\PublicContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\getJson;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    seed(PublicContentSeeder::class);
});

it('returns grouped public categories with seo metadata', function (): void {
    $response = getJson('/api/v1/categories')
        ->assertOk()
        ->assertJsonPath('data.seo.title', 'Все категории обращений')
        ->assertJsonPath('data.seo.robots', 'index,follow')
        ->assertJsonPath('data.groups.0.slug', 'city')
        ->assertJsonPath('data.groups.0.categories.0.slug', 'zhkh')
        ->assertJsonCount(4, 'data.groups');

    $groups = $response->json('data.groups');
    $categories = array_merge(...array_map(
        static fn (array $group): array => $group['categories'],
        $groups,
    ));
    $slugs = array_map(
        static fn (array $category): string => $category['slug'],
        $categories,
    );

    expect($categories)->toHaveCount(15)
        ->and($slugs)->toBe([
            'zhkh',
            'roads',
            'transport',
            'improvement',
            'healthcare',
            'education',
            'social-support',
            'ecology',
            'safety',
            'public-services',
            'land-real-estate',
            'authorities',
            'telecom',
            'commerce-services',
            'other',
        ])
        ->and(array_unique($slugs))->toHaveCount(15);
});

it('filters inactive database categories and applies database sort order', function (): void {
    $visibleGroup = CategoryGroup::query()->create([
        'slug' => 'database-backed',
        'title' => 'Справочник из БД',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    Category::query()->create([
        'category_group_id' => $visibleGroup->id,
        'slug' => 'database-second',
        'title' => 'Вторая активная',
        'description' => 'Проверяет сортировку активных категорий.',
        'icon' => 'file',
        'sort_order' => 20,
        'is_active' => true,
    ]);

    Category::query()->create([
        'category_group_id' => $visibleGroup->id,
        'slug' => 'database-first',
        'title' => 'Первая активная',
        'description' => 'Должна идти первой внутри группы.',
        'icon' => 'home',
        'sort_order' => 10,
        'is_active' => true,
    ]);

    Category::query()->create([
        'category_group_id' => $visibleGroup->id,
        'slug' => 'database-hidden-category',
        'title' => 'Скрытая категория',
        'description' => 'Не должна попасть в публичный ответ.',
        'icon' => 'shield',
        'sort_order' => 1,
        'is_active' => false,
    ]);

    $inactiveGroup = CategoryGroup::query()->create([
        'slug' => 'database-hidden-group',
        'title' => 'Скрытая группа',
        'sort_order' => 0,
        'is_active' => false,
    ]);

    Category::query()->create([
        'category_group_id' => $inactiveGroup->id,
        'slug' => 'database-category-in-hidden-group',
        'title' => 'Категория скрытой группы',
        'description' => 'Не должна попасть в публичный ответ.',
        'icon' => 'file',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $onlyInactiveGroup = CategoryGroup::query()->create([
        'slug' => 'database-only-inactive',
        'title' => 'Группа только с неактивными категориями',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    Category::query()->create([
        'category_group_id' => $onlyInactiveGroup->id,
        'slug' => 'database-only-inactive-category',
        'title' => 'Единственная неактивная',
        'description' => 'Группа не должна попасть в публичный ответ.',
        'icon' => 'file',
        'sort_order' => 1,
        'is_active' => false,
    ]);

    getJson('/api/v1/categories')
        ->assertOk()
        ->assertJsonPath('data.groups.0.slug', 'database-backed')
        ->assertJsonPath('data.groups.0.categories.0.slug', 'database-first')
        ->assertJsonPath('data.groups.0.categories.1.slug', 'database-second')
        ->assertJsonMissing(['slug' => 'database-hidden-category'])
        ->assertJsonMissing(['slug' => 'database-hidden-group'])
        ->assertJsonMissing(['slug' => 'database-category-in-hidden-group'])
        ->assertJsonMissing(['slug' => 'database-only-inactive'])
        ->assertJsonMissing(['slug' => 'database-only-inactive-category']);
});

it('has a named categories route', function (): void {
    expect(route('api.categories.index', absolute: false))->toBe('/api/v1/categories');
});
