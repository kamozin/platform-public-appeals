<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

it('registers a user and returns bearer token', function (): void {
    postJson('/api/v1/auth/register', [
        'name' => 'Ivan Ivanov',
        'phone' => '+79001234567',
        'email' => 'ivan@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'privacy' => true,
        'notifications' => true,
    ])
        ->assertCreated()
        ->assertJsonPath('data.tokenType', 'Bearer')
        ->assertJsonPath('data.user.email', 'ivan@example.com');
});

it('logs in, returns me and logs out', function (): void {
    User::query()->create([
        'name' => 'Ivan Ivanov',
        'email' => 'ivan@example.com',
        'password' => Hash::make('password123'),
    ]);

    $token = postJson('/api/v1/auth/login', [
        'login' => 'ivan@example.com',
        'password' => 'password123',
    ])
        ->assertOk()
        ->json('data.token');

    getJson('/api/v1/auth/me', ['Authorization' => "Bearer $token"])
        ->assertOk()
        ->assertJsonPath('data.email', 'ivan@example.com');

    postJson('/api/v1/auth/logout', [], ['Authorization' => "Bearer $token"])
        ->assertNoContent();

    getJson('/api/v1/auth/me', ['Authorization' => "Bearer $token"])
        ->assertUnauthorized()
        ->assertJsonPath('error.code', 'UNAUTHORIZED');
});

it('rejects invalid credentials', function (): void {
    postJson('/api/v1/auth/login', [
        'login' => 'missing@example.com',
        'password' => 'password123',
    ])
        ->assertUnauthorized()
        ->assertJsonPath('error.code', 'INVALID_CREDENTIALS');
});
