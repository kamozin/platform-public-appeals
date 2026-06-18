<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\deleteJson;
use function Pest\Laravel\post;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

it('subscribes and unsubscribes news email idempotently', function (): void {
    postJson('/api/v1/subscriptions/news', ['email' => 'reader@example.com'])
        ->assertCreated()
        ->assertJsonPath('data.status', 'active');

    postJson('/api/v1/subscriptions/news', ['email' => 'reader@example.com'])
        ->assertCreated()
        ->assertJsonPath('data.status', 'active');

    deleteJson('/api/v1/subscriptions/news', ['email' => 'reader@example.com'])
        ->assertNoContent();
});

it('accepts support video for moderation', function (): void {
    Storage::fake('local');

    post('/api/v1/support-videos', [
        'email' => 'reader@example.com',
        'video' => UploadedFile::fake()->create('support.mp4', 100, 'video/mp4'),
    ], ['Accept' => 'application/json'])
        ->assertCreated()
        ->assertJsonPath('data.status', 'pending_moderation');
});

it('rejects unsupported support video types', function (): void {
    Storage::fake('local');

    post('/api/v1/support-videos', [
        'email' => 'reader@example.com',
        'video' => UploadedFile::fake()->create('support.txt', 1, 'text/plain'),
    ], ['Accept' => 'application/json'])
        ->assertUnprocessable()
        ->assertJsonPath('error.code', 'VIDEO_TYPE_NOT_ALLOWED');
});
