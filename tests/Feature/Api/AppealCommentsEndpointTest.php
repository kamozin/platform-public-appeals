<?php

declare(strict_types=1);

use App\Application\Auth\AuthService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

it('requires authentication to store an appeal comment', function (): void {
    postJson('/api/v1/appeals/road-pits-after-repair/comments', [
        'comment' => 'Проблема подтверждается.',
    ])
        ->assertUnauthorized()
        ->assertJsonPath('error.code', 'UNAUTHORIZED');
});

it('stores a pending comment for an authenticated user', function (): void {
    $user = User::query()->create([
        'name' => 'Anna',
        'email' => 'anna@example.com',
        'password' => Hash::make('password123'),
    ]);

    $token = app(AuthService::class)->login('anna@example.com', 'password123')['token'];

    postJson('/api/v1/appeals/road-pits-after-repair/comments', [
        'comment' => 'Проблема подтверждается.',
    ], ['Authorization' => "Bearer $token"])
        ->assertCreated()
        ->assertJsonPath('data.status', 'pending')
        ->assertJsonPath('data.authorName', $user->name);

    getJson('/api/v1/appeals/road-pits-after-repair/comments')
        ->assertOk()
        ->assertJsonPath('data.items.0.status', 'pending');
});

it('validates empty comments', function (): void {
    $user = User::query()->create([
        'name' => 'Anna',
        'email' => 'anna@example.com',
        'password' => Hash::make('password123'),
    ]);

    $token = app(AuthService::class)->login($user->email, 'password123')['token'];

    postJson('/api/v1/appeals/road-pits-after-repair/comments', [
        'comment' => '',
    ], ['Authorization' => "Bearer $token"])
        ->assertUnprocessable()
        ->assertJsonPath('error.code', 'VALIDATION_FAILED');
});
