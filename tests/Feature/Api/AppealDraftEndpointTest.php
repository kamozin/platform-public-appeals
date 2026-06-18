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

it('creates updates reads submits and deletes own draft', function (): void {
    $user = User::query()->create([
        'name' => 'Ivan',
        'email' => 'ivan@example.com',
        'password' => Hash::make('password123'),
    ]);
    $token = app(AuthService::class)->login($user->email, 'password123')['token'];
    $headers = ['Authorization' => "Bearer $token"];

    $draftId = postJson('/api/v1/appeal-drafts', [
        'category' => 'roads',
        'submission_type' => 'complaint',
        'title' => 'Ямы во дворе',
        'description' => 'Описание проблемы',
        'urgency' => 'normal',
        'location' => 'Москва',
        'contact_visibility' => 'hidden',
    ], $headers)
        ->assertCreated()
        ->assertJsonPath('data.status', 'draft')
        ->json('data.id');

    patchJson("/api/v1/appeal-drafts/$draftId", [
        'title' => 'Ямы на дороге',
    ], $headers)
        ->assertOk()
        ->assertJsonPath('data.title', 'Ямы на дороге');

    getJson("/api/v1/appeal-drafts/$draftId", $headers)
        ->assertOk()
        ->assertJsonPath('data.id', $draftId);

    postJson("/api/v1/appeal-drafts/$draftId/submit", [
        'captcha_token' => 'test-captcha',
    ], $headers)
        ->assertOk()
        ->assertJsonPath('data.status', 'pending_moderation');

    patchJson("/api/v1/appeal-drafts/$draftId", [
        'title' => 'Нельзя менять',
    ], $headers)
        ->assertConflict()
        ->assertJsonPath('error.code', 'CONFLICT');
});

it('creates updates reads and submits a guest draft by token', function (): void {
    $response = postJson('/api/v1/appeal-drafts', [
        'category' => 'roads',
        'submission_type' => 'complaint',
        'title' => 'Гостевая жалоба',
        'description' => 'Описание проблемы',
        'urgency' => 'normal',
    ])
        ->assertCreated()
        ->assertJsonPath('data.status', 'draft');

    $draftId = $response->json('data.id');
    $guestToken = $response->json('data.guestToken');

    expect($guestToken)->toBeString();
    expect($guestToken !== '')->toBeTrue();

    $headers = ['X-Appeal-Draft-Token' => $guestToken];

    patchJson("/api/v1/appeal-drafts/$draftId", [
        'title' => 'Гостевая жалоба обновлена',
    ], $headers)
        ->assertOk()
        ->assertJsonPath('data.title', 'Гостевая жалоба обновлена')
        ->assertJsonPath('data.guestToken', null);

    getJson("/api/v1/appeal-drafts/$draftId", $headers)
        ->assertOk()
        ->assertJsonPath('data.id', $draftId);

    postJson("/api/v1/appeal-drafts/$draftId/submit", [
        'captcha_token' => 'test-captcha',
    ], $headers)
        ->assertOk()
        ->assertJsonPath('data.status', 'pending_moderation');
});

it('does not expose a guest draft without its token', function (): void {
    $response = postJson('/api/v1/appeal-drafts', ['title' => 'Гостевой черновик'])
        ->assertCreated();

    $draftId = $response->json('data.id');

    getJson("/api/v1/appeal-drafts/$draftId")
        ->assertUnauthorized()
        ->assertJsonPath('error.code', 'UNAUTHORIZED');

    getJson("/api/v1/appeal-drafts/$draftId", ['X-Appeal-Draft-Token' => 'wrong-token'])
        ->assertNotFound()
        ->assertJsonPath('error.code', 'NOT_FOUND');
});

it('uploads and deletes a draft attachment', function (): void {
    Storage::fake('local');

    $user = User::query()->create([
        'name' => 'Ivan',
        'email' => 'ivan@example.com',
        'password' => Hash::make('password123'),
    ]);
    $token = app(AuthService::class)->login($user->email, 'password123')['token'];
    $headers = ['Authorization' => "Bearer $token"];

    $draftId = postJson('/api/v1/appeal-drafts', ['title' => 'Черновик'], $headers)->json('data.id');

    $attachmentId = post("/api/v1/appeal-drafts/$draftId/attachments", [
        'file' => UploadedFile::fake()->create('photo.jpg', 10, 'image/jpeg'),
    ], [...$headers, 'Accept' => 'application/json'])
        ->assertCreated()
        ->assertJsonPath('data.kind', 'image')
        ->json('data.id');

    deleteJson("/api/v1/appeal-drafts/$draftId/attachments/$attachmentId", [], $headers)
        ->assertNoContent();
});

it('uploads and deletes a guest draft attachment', function (): void {
    Storage::fake('local');

    $response = postJson('/api/v1/appeal-drafts', ['title' => 'Гостевой черновик'])
        ->assertCreated();

    $draftId = $response->json('data.id');
    $guestToken = $response->json('data.guestToken');
    $headers = ['X-Appeal-Draft-Token' => $guestToken];

    $attachmentId = post("/api/v1/appeal-drafts/$draftId/attachments", [
        'file' => UploadedFile::fake()->create('photo.jpg', 10, 'image/jpeg'),
    ], [...$headers, 'Accept' => 'application/json'])
        ->assertCreated()
        ->assertJsonPath('data.kind', 'image')
        ->json('data.id');

    deleteJson("/api/v1/appeal-drafts/$draftId/attachments/$attachmentId", [], $headers)
        ->assertNoContent();
});

it('does not expose another users draft', function (): void {
    $owner = User::query()->create([
        'name' => 'Owner',
        'email' => 'owner@example.com',
        'password' => Hash::make('password123'),
    ]);
    $other = User::query()->create([
        'name' => 'Other',
        'email' => 'other@example.com',
        'password' => Hash::make('password123'),
    ]);

    $ownerToken = app(AuthService::class)->login($owner->email, 'password123')['token'];
    $otherToken = app(AuthService::class)->login($other->email, 'password123')['token'];

    $draftId = postJson('/api/v1/appeal-drafts', ['title' => 'Свой'], ['Authorization' => "Bearer $ownerToken"])
        ->json('data.id');

    getJson("/api/v1/appeal-drafts/$draftId", ['Authorization' => "Bearer $otherToken"])
        ->assertNotFound()
        ->assertJsonPath('error.code', 'NOT_FOUND');
});
