<?php

declare(strict_types=1);

use App\Application\Auth\AuthService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\patchJson;
use function Pest\Laravel\post;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

it('reads and updates profile including avatar', function (): void {
    Storage::fake('local');
    $user = User::query()->create([
        'name' => 'Ivan',
        'email' => 'ivan@example.com',
        'password' => Hash::make('password123'),
    ]);
    $token = app(AuthService::class)->login($user->email, 'password123')['token'];
    $headers = ['Authorization' => "Bearer $token"];

    getJson('/api/v1/profile', $headers)
        ->assertOk()
        ->assertJsonPath('data.email', 'ivan@example.com');

    patchJson('/api/v1/profile', [
        'name' => 'Ivan Updated',
        'notifications' => false,
    ], $headers)
        ->assertOk()
        ->assertJsonPath('data.name', 'Ivan Updated')
        ->assertJsonPath('data.notificationsEnabled', false);

    $avatarResponse = post('/api/v1/profile/avatar', [
        'avatar' => UploadedFile::fake()->create('avatar.png', 10, 'image/png'),
    ], [...$headers, 'Accept' => 'application/json'])
        ->assertCreated()
        ->assertJsonStructure(['data' => ['avatarUrl']]);

    expect($avatarResponse->json('data.avatarUrl'))->not->toBeNull();

    deleteJson('/api/v1/profile/avatar', [], $headers)
        ->assertNoContent();
});

it('returns dashboard activity and settings endpoints', function (): void {
    $user = User::query()->create([
        'name' => 'Ivan',
        'email' => 'ivan@example.com',
        'password' => Hash::make('password123'),
    ]);
    $token = app(AuthService::class)->login($user->email, 'password123')['token'];
    $headers = ['Authorization' => "Bearer $token"];

    postJson('/api/v1/appeal-drafts', ['title' => 'Черновик'], $headers)->assertCreated();
    postJson('/api/v1/appeals/road-pits-after-repair/save', [], $headers)->assertCreated();

    getJson('/api/v1/dashboard/drafts', $headers)->assertOk()->assertJsonCount(1, 'data.items');
    getJson('/api/v1/dashboard/saved-appeals', $headers)->assertOk()->assertJsonCount(1, 'data.items');
    getJson('/api/v1/dashboard/notifications', $headers)->assertOk();
    postJson('/api/v1/dashboard/notifications/mark-all-read', [], $headers)->assertOk();
    patchJson('/api/v1/dashboard/notification-settings', ['notifications' => true], $headers)->assertOk();
    getJson('/api/v1/dashboard/status-history', $headers)->assertOk();
    getJson('/api/v1/dashboard/security/sessions', $headers)->assertOk();
    getJson('/api/v1/dashboard/achievements', $headers)->assertOk();
});
