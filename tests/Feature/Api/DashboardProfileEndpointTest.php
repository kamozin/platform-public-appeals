<?php

declare(strict_types=1);

use App\Application\Auth\AuthService;
use App\Application\Auth\VerificationService;
use App\Models\User;
use App\Models\VerificationChallenge;
use App\Notifications\Auth\VerificationCodeNotification;
use Database\Seeders\PublicContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\patchJson;
use function Pest\Laravel\post;
use function Pest\Laravel\postJson;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    seed(PublicContentSeeder::class);
});

it('reads and updates profile including avatar', function (): void {
    Storage::fake('public');
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

    $avatarUrl = (string) $avatarResponse->json('data.avatarUrl');
    $avatarPath = str_replace('/storage/', '', $avatarUrl);

    expect($avatarUrl)
        ->not->toBe('/storage/0')
        ->and(str_starts_with($avatarUrl, '/storage/avatars/'))->toBeTrue();

    Storage::disk('public')->assertExists($avatarPath);
    expect($user->refresh()->avatar_path)->toBe($avatarPath);

    $secondAvatarResponse = post('/api/v1/profile/avatar', [
        'avatar' => UploadedFile::fake()->create('avatar-second.png', 10, 'image/png'),
    ], [...$headers, 'Accept' => 'application/json'])
        ->assertCreated();

    $secondAvatarUrl = (string) $secondAvatarResponse->json('data.avatarUrl');
    $secondAvatarPath = str_replace('/storage/', '', $secondAvatarUrl);

    expect($secondAvatarPath)->not->toBe($avatarPath);
    Storage::disk('public')->assertMissing($avatarPath);
    Storage::disk('public')->assertExists($secondAvatarPath);

    deleteJson('/api/v1/profile/avatar', [], $headers)
        ->assertNoContent();

    Storage::disk('public')->assertMissing($secondAvatarPath);
    expect($user->refresh()->avatar_path)->toBeNull();
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
    getJson('/api/v1/dashboard/notifications', $headers)
        ->assertOk()
        ->assertJsonPath('data.items.0.read', false)
        ->assertJsonPath('data.items.1.read', false);

    postJson('/api/v1/dashboard/notifications/mark-all-read', [], $headers)
        ->assertOk()
        ->assertJsonPath('data.marked', true)
        ->assertJsonPath('data.count', 2);

    getJson('/api/v1/dashboard/notifications', $headers)
        ->assertOk()
        ->assertJsonPath('data.items.0.read', true)
        ->assertJsonPath('data.items.1.read', true);

    patchJson('/api/v1/dashboard/notification-settings', ['notifications' => true], $headers)->assertOk();
    getJson('/api/v1/dashboard/status-history', $headers)->assertOk();
    getJson('/api/v1/dashboard/security/sessions', $headers)->assertOk();
    getJson('/api/v1/dashboard/achievements', $headers)->assertOk();
});

it('changes password and revokes other bearer tokens', function (): void {
    $user = User::query()->create([
        'name' => 'Ivan',
        'email' => 'ivan@example.com',
        'password' => Hash::make('password123'),
    ]);

    $currentToken = app(AuthService::class)->login($user->email, 'password123')['token'];
    $otherToken = app(AuthService::class)->login($user->email, 'password123')['token'];
    $headers = ['Authorization' => "Bearer $currentToken"];

    patchJson('/api/v1/profile/password', [
        'current_password' => 'wrong-password',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ], $headers)
        ->assertUnprocessable()
        ->assertJsonPath('error.code', 'CURRENT_PASSWORD_INVALID');

    patchJson('/api/v1/profile/password', [
        'current_password' => 'password123',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ], $headers)
        ->assertOk()
        ->assertJsonPath('data.changed', true);

    postJson('/api/v1/auth/login', [
        'login' => $user->email,
        'password' => 'password123',
    ])->assertUnauthorized();

    postJson('/api/v1/auth/login', [
        'login' => $user->email,
        'password' => 'new-password',
    ])->assertOk();

    getJson('/api/v1/auth/me', ['Authorization' => "Bearer $currentToken"])
        ->assertOk();

    getJson('/api/v1/auth/me', ['Authorization' => "Bearer $otherToken"])
        ->assertUnauthorized();
});

it('enables and disables email two factor from profile security', function (): void {
    Notification::fake();

    $user = User::query()->create([
        'name' => 'Ivan',
        'email' => 'ivan@example.com',
        'password' => Hash::make('password123'),
    ]);
    $token = app(AuthService::class)->login($user->email, 'password123')['token'];
    $headers = ['Authorization' => "Bearer $token"];

    postJson('/api/v1/profile/security/email-2fa/send', [
        'current_password' => 'wrong-password',
    ], $headers)
        ->assertUnprocessable()
        ->assertJsonPath('error.code', 'CURRENT_PASSWORD_INVALID');

    $challengeId = postJson('/api/v1/profile/security/email-2fa/send', [
        'current_password' => 'password123',
    ], $headers)
        ->assertCreated()
        ->assertJsonPath('data.maskedTarget', 'i***@example.com')
        ->json('data.id');

    Notification::assertSentOnDemand(
        VerificationCodeNotification::class,
        fn (VerificationCodeNotification $notification, array $channels): bool => $notification->purpose === 'email_two_factor_enable'
            && in_array('mail', $channels, true),
    );

    postJson('/api/v1/profile/security/email-2fa/enable', [
        'challenge_id' => $challengeId,
        'code' => '000000',
    ], $headers)
        ->assertUnprocessable()
        ->assertJsonPath('error.code', 'INVALID_VERIFICATION_CODE');

    postJson('/api/v1/profile/security/email-2fa/enable', [
        'challenge_id' => $challengeId,
        'code' => '123456',
    ], $headers)
        ->assertOk()
        ->assertJsonPath('data.emailTwoFactorEnabled', true);

    getJson('/api/v1/profile', $headers)
        ->assertOk()
        ->assertJsonPath('data.emailTwoFactorEnabled', true);

    deleteJson('/api/v1/profile/security/email-2fa', [
        'current_password' => 'password123',
    ], $headers)
        ->assertOk()
        ->assertJsonPath('data.emailTwoFactorEnabled', false);
});

it('does not consume another users email two factor challenge', function (): void {
    Notification::fake();

    $user = User::query()->create([
        'name' => 'Ivan',
        'email' => 'ivan@example.com',
        'password' => Hash::make('password123'),
    ]);
    $anotherUser = User::query()->create([
        'name' => 'Petr',
        'email' => 'petr@example.com',
        'password' => Hash::make('password123'),
    ]);

    $token = app(AuthService::class)->login($user->email, 'password123')['token'];
    $challenge = app(VerificationService::class)->sendEmailTwoFactorEnable($anotherUser);

    postJson('/api/v1/profile/security/email-2fa/enable', [
        'challenge_id' => $challenge['id'],
        'code' => '123456',
    ], ['Authorization' => "Bearer $token"])
        ->assertNotFound()
        ->assertJsonPath('error.code', 'CHALLENGE_NOT_FOUND');

    $storedChallenge = VerificationChallenge::query()->find($challenge['id']);

    expect($storedChallenge)
        ->toBeInstanceOf(VerificationChallenge::class)
        ->and($storedChallenge->consumed_at)->toBeNull()
        ->and($storedChallenge->attempts)->toBe(0);
});
