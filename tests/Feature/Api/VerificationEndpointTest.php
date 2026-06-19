<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\VerificationChallenge;
use App\Notifications\Auth\VerificationCodeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

it('sends and verifies a verification challenge', function (): void {
    Notification::fake();

    $challengeId = postJson('/api/v1/auth/verification/send', [
        'channel' => 'email',
        'target' => 'ivan@example.com',
    ])
        ->assertCreated()
        ->assertJsonPath('data.devCode', '123456')
        ->json('data.id');

    Notification::assertSentOnDemand(
        VerificationCodeNotification::class,
        fn (VerificationCodeNotification $notification, array $channels): bool => $notification->code === '123456'
            && $notification->purpose === 'verification'
            && in_array('mail', $channels, true),
    );

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

it('does not consume password reset challenge for another email', function (): void {
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
        'email' => 'petr@example.com',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ])
        ->assertNotFound()
        ->assertJsonPath('error.code', 'CHALLENGE_NOT_FOUND');

    $storedChallenge = VerificationChallenge::query()->find($challengeId);

    expect($storedChallenge)
        ->toBeInstanceOf(VerificationChallenge::class)
        ->and($storedChallenge->consumed_at)->toBeNull()
        ->and($storedChallenge->attempts)->toBe(0);
});

it('builds a branded verification email message', function (): void {
    $mail = (new VerificationCodeNotification('123456', 'two_factor_login'))
        ->toMail(new AnonymousNotifiable);

    expect($mail->subject)->toBe('Код входа в личный кабинет')
        ->and($mail->view)->toBe('emails.auth.verification-code')
        ->and($mail->viewData['code'])->toBe('123456')
        ->and($mail->viewData['heading'])->toBe('Подтверждение входа')
        ->and($mail->viewData['expiresInMinutes'])->toBe(15);

    $html = view($mail->view, $mail->viewData)->render();

    expect($html)
        ->toContain('123456')
        ->toContain('Подтверждение входа')
        ->toContain('Код действует 15 минут');
});
