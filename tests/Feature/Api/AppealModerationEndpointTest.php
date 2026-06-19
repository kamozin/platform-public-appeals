<?php

declare(strict_types=1);

use App\Application\Auth\AuthService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use function Pest\Laravel\getJson;
use function Pest\Laravel\patchJson;
use function Pest\Laravel\post;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

function moderationUser(bool $isAdmin = false): User
{
    $prefix = $isAdmin ? 'moderation-admin-' : 'moderation-user-';

    return User::query()->create([
        'name' => $isAdmin ? 'Moderation Admin' : 'Moderation User',
        'email' => $prefix.Str::uuid().'@example.com',
        'password' => Hash::make('password123'),
        'is_admin' => $isAdmin,
    ]);
}

function moderationHeadersFor(User $user): array
{
    $token = app(AuthService::class)->login($user->email, 'password123')['token'];

    return ['Authorization' => "Bearer $token"];
}

function createSubmittedModerationDraft(array $headers): string
{
    $draftId = postJson('/api/v1/appeal-drafts', [
        'category' => 'roads',
        'submission_type' => 'complaint',
        'title' => 'Ямы во дворе',
        'description' => 'Описание проблемы с дорогой',
        'urgency' => 'normal',
        'location' => 'Москва, улица Тестовая',
        'contact_visibility' => 'hidden',
        'contact_name' => 'Иван Иванов',
        'contact_email' => 'ivan@example.com',
        'contact_phone' => '+79990000000',
    ], $headers)
        ->assertCreated()
        ->json('data.id');

    postJson("/api/v1/appeal-drafts/$draftId/submit", [
        'captcha_token' => 'test-captcha',
    ], $headers)
        ->assertOk()
        ->assertJsonPath('data.status', 'pending_moderation');

    return (string) $draftId;
}

function moderationApprovePayload(string $slug): array
{
    return [
        'slug' => $slug,
        'title' => 'Публичное обращение после модерации',
        'excerpt' => 'Короткое описание обращения после проверки.',
        'description' => 'Публичное описание обращения без персональных контактов.',
        'status' => 'checking',
        'status_label' => 'На проверке',
        'city' => 'Москва',
        'district' => 'Центральный район',
        'category' => 'Дороги',
        'location' => 'Москва, улица Тестовая',
        'support_count' => 0,
        'views_count' => 0,
        'comments_count' => 0,
        'image_url' => '/assets/issue-road.png',
        'is_public' => true,
        'attachments' => [
            [
                'type' => 'image',
                'url' => '/assets/issue-road.png',
                'title' => 'Фото проблемы',
            ],
        ],
        'timeline' => [
            [
                'status' => 'published',
                'title' => 'Опубликовано',
                'happened_at' => '2026-06-19T10:00:00+03:00',
                'text' => 'Обращение опубликовано после модерации.',
            ],
        ],
    ];
}

it('protects admin appeal moderation endpoints', function (): void {
    getJson('/api/v1/admin/appeal-moderation')
        ->assertUnauthorized()
        ->assertJsonPath('error.code', 'UNAUTHORIZED');

    getJson('/api/v1/admin/appeal-moderation', moderationHeadersFor(moderationUser()))
        ->assertForbidden()
        ->assertJsonPath('error.code', 'FORBIDDEN');
});

it('lists and shows submitted drafts with attachment metadata', function (): void {
    Storage::fake('local');

    $owner = moderationUser();
    $admin = moderationUser(true);
    $ownerHeaders = moderationHeadersFor($owner);
    $adminHeaders = moderationHeadersFor($admin);
    $draftId = createSubmittedModerationDraft($ownerHeaders);

    post("/api/v1/appeal-drafts/$draftId/attachments", [
        'file' => UploadedFile::fake()->create('photo.jpg', 10, 'image/jpeg'),
    ], [...$ownerHeaders, 'Accept' => 'application/json'])
        ->assertCreated();

    getJson('/api/v1/admin/appeal-moderation', $adminHeaders)
        ->assertOk()
        ->assertJsonPath('data.items.0.id', $draftId)
        ->assertJsonPath('data.items.0.status', 'pending_moderation');

    getJson("/api/v1/admin/appeal-moderation/$draftId", $adminHeaders)
        ->assertOk()
        ->assertJsonPath('data.id', $draftId)
        ->assertJsonPath('data.contactEmail', 'ivan@example.com')
        ->assertJsonPath('data.attachments.0.kind', 'image')
        ->assertJsonPath('data.attachments.0.originalName', 'photo.jpg');
});

it('requests changes and allows the owner to resubmit a draft', function (): void {
    $owner = moderationUser();
    $admin = moderationUser(true);
    $ownerHeaders = moderationHeadersFor($owner);
    $adminHeaders = moderationHeadersFor($admin);
    $draftId = createSubmittedModerationDraft($ownerHeaders);

    postJson("/api/v1/admin/appeal-moderation/$draftId/request-changes", [
        'message' => 'Уточните адрес и приложите фото.',
    ], $adminHeaders)
        ->assertOk()
        ->assertJsonPath('data.status', 'needs_changes')
        ->assertJsonPath('data.moderationNote', 'Уточните адрес и приложите фото.')
        ->assertJsonPath('data.events.0.action', 'changes_requested');

    patchJson("/api/v1/appeal-drafts/$draftId", [
        'location' => 'Москва, улица Тестовая, дом 10',
    ], $ownerHeaders)
        ->assertOk()
        ->assertJsonPath('data.status', 'needs_changes');

    postJson("/api/v1/appeal-drafts/$draftId/submit", [
        'captcha_token' => 'test-captcha',
    ], $ownerHeaders)
        ->assertOk()
        ->assertJsonPath('data.status', 'pending_moderation')
        ->assertJsonPath('data.moderationNote', null);
});

it('rejects a draft and blocks further moderation actions on the final status', function (): void {
    $owner = moderationUser();
    $admin = moderationUser(true);
    $ownerHeaders = moderationHeadersFor($owner);
    $adminHeaders = moderationHeadersFor($admin);
    $draftId = createSubmittedModerationDraft($ownerHeaders);

    postJson("/api/v1/admin/appeal-moderation/$draftId/reject", [
        'reason' => 'Недостаточно сведений для проверки.',
    ], $adminHeaders)
        ->assertOk()
        ->assertJsonPath('data.status', 'rejected')
        ->assertJsonPath('data.rejectionReason', 'Недостаточно сведений для проверки.')
        ->assertJsonPath('data.events.0.action', 'rejected');

    postJson("/api/v1/admin/appeal-moderation/$draftId/request-changes", [
        'message' => 'Повторное действие.',
    ], $adminHeaders)
        ->assertConflict()
        ->assertJsonPath('error.code', 'CONFLICT');
});

it('approves a draft and exposes the created public appeal', function (): void {
    $owner = moderationUser();
    $admin = moderationUser(true);
    $ownerHeaders = moderationHeadersFor($owner);
    $adminHeaders = moderationHeadersFor($admin);
    $draftId = createSubmittedModerationDraft($ownerHeaders);

    getJson('/api/v1/appeals/moderated-public-appeal')
        ->assertNotFound();

    $publicAppealId = postJson(
        "/api/v1/admin/appeal-moderation/$draftId/approve",
        moderationApprovePayload('moderated-public-appeal'),
        $adminHeaders,
    )
        ->assertOk()
        ->assertJsonPath('data.status', 'approved')
        ->json('data.publicAppealId');

    expect($publicAppealId)->toBeString();

    getJson('/api/v1/appeals/moderated-public-appeal')
        ->assertOk()
        ->assertJsonPath('data.title', 'Публичное обращение после модерации');

    postJson(
        "/api/v1/admin/appeal-moderation/$draftId/approve",
        moderationApprovePayload('moderated-public-appeal-duplicate'),
        $adminHeaders,
    )
        ->assertConflict()
        ->assertJsonPath('error.code', 'CONFLICT');
});

it('has named appeal moderation routes', function (): void {
    $id = '00000000-0000-4000-8000-000000000999';

    expect(route('api.admin.appeal-moderation.index', absolute: false))->toBe('/api/v1/admin/appeal-moderation');
    expect(route('api.admin.appeal-moderation.show', ['id' => $id], false))->toBe("/api/v1/admin/appeal-moderation/$id");
    expect(route('api.admin.appeal-moderation.request-changes', ['id' => $id], false))->toBe("/api/v1/admin/appeal-moderation/$id/request-changes");
    expect(route('api.admin.appeal-moderation.reject', ['id' => $id], false))->toBe("/api/v1/admin/appeal-moderation/$id/reject");
    expect(route('api.admin.appeal-moderation.approve', ['id' => $id], false))->toBe("/api/v1/admin/appeal-moderation/$id/approve");
});
