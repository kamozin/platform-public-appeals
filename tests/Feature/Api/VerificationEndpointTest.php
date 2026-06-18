<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

it('sends and verifies a verification challenge', function (): void {
    $challengeId = postJson('/api/v1/auth/verification/send', [
        'channel' => 'email',
        'target' => 'ivan@example.com',
    ])
        ->assertCreated()
        ->assertJsonPath('data.devCode', '123456')
        ->json('data.id');

    postJson('/api/v1/auth/verification/verify', [
        'challenge_id' => $challengeId,
        'code' => '123456',
    ])
        ->assertOk()
        ->assertJsonPath('data.verified', true);
});

it('returns stable error for invalid verification code', function (): void {
    $challengeId = postJson('/api/v1/auth/verification/send', [
        'channel' => 'email',
        'target' => 'ivan@example.com',
    ])->json('data.id');

    postJson('/api/v1/auth/verification/verify', [
        'challenge_id' => $challengeId,
        'code' => '000000',
    ])
        ->assertUnprocessable()
        ->assertJsonPath('error.code', 'INVALID_VERIFICATION_CODE');
});

it('completes password reset after valid challenge', function (): void {
    User::query()->create([
        'name' => 'Ivan',
        'email' => 'ivan@example.com',
        'password' => Hash::make('old-password'),
    ]);

    $challengeId = postJson('/api/v1/auth/password-reset/send', [
        'email' => 'ivan@example.com',
    ])->json('data.id');

    postJson('/api/v1/auth/password-reset/complete', [
        'challenge_id' => $challengeId,
        'code' => '123456',
        'email' => 'ivan@example.com',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ])
        ->assertOk()
        ->assertJsonPath('data.changed', true);

    postJson('/api/v1/auth/login', [
        'login' => 'ivan@example.com',
        'password' => 'new-password',
    ])->assertOk();
});
