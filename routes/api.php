<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Appeals\AppealCommentController;
use App\Http\Controllers\Api\Appeals\AppealController;
use App\Http\Controllers\Api\Appeals\AppealDraftAttachmentController;
use App\Http\Controllers\Api\Appeals\AppealDraftController;
use App\Http\Controllers\Api\Appeals\AppealDraftSubmitController;
use App\Http\Controllers\Api\Appeals\AppealSaveController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\VerificationController;
use App\Http\Controllers\Api\Categories\CategoryController;
use App\Http\Controllers\Api\Dashboard\DashboardController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\News\NewsController;
use App\Http\Controllers\Api\Profile\ProfileAvatarController;
use App\Http\Controllers\Api\Profile\ProfileController;
use App\Http\Controllers\Api\Seo\SitemapUrlsController;
use App\Http\Controllers\Api\Subscriptions\NewsSubscriptionController;
use App\Http\Controllers\Api\Subscriptions\SupportVideoController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->name('api.')
    ->group(function (): void {
        Route::get('/health', HealthController::class)->name('health');

        Route::get('/categories', CategoryController::class)
            ->name('categories.index');

        Route::prefix('auth')->name('auth.')->group(function (): void {
            Route::post('/register', [AuthController::class, 'register'])
                ->name('register');
            Route::post('/login', [AuthController::class, 'login'])
                ->name('login');
            Route::get('/me', [AuthController::class, 'me'])
                ->name('me');
            Route::post('/logout', [AuthController::class, 'logout'])
                ->name('logout');
            Route::post('/verification/send', [VerificationController::class, 'send'])
                ->name('verification.send');
            Route::post('/verification/verify', [VerificationController::class, 'verify'])
                ->name('verification.verify');
            Route::post('/password-reset/send', [VerificationController::class, 'sendPasswordReset'])
                ->name('password-reset.send');
            Route::post('/password-reset/verify', [VerificationController::class, 'verifyPasswordReset'])
                ->name('password-reset.verify');
            Route::post('/password-reset/complete', [VerificationController::class, 'completePasswordReset'])
                ->name('password-reset.complete');
        });

        Route::prefix('news')->name('news.')->group(function (): void {
            Route::get('/', [NewsController::class, 'index'])
                ->name('index');

            Route::get('/{slug}', [NewsController::class, 'show'])
                ->where('slug', '[a-z0-9-]+')
                ->name('show');
        });

        Route::prefix('appeals')->name('appeals.')->group(function (): void {
            Route::get('/', [AppealController::class, 'index'])
                ->name('index');

            Route::get('/{appeal}/comments', [AppealCommentController::class, 'index'])
                ->where('appeal', '[a-z0-9-]+')
                ->name('comments.index');
            Route::post('/{appeal}/comments', [AppealCommentController::class, 'store'])
                ->where('appeal', '[a-z0-9-]+')
                ->name('comments.store');
            Route::post('/{appeal}/save', [AppealSaveController::class, 'store'])
                ->where('appeal', '[a-z0-9-]+')
                ->name('save.store');
            Route::delete('/{appeal}/save', [AppealSaveController::class, 'destroy'])
                ->where('appeal', '[a-z0-9-]+')
                ->name('save.destroy');

            Route::get('/{slug}', [AppealController::class, 'show'])
                ->where('slug', '[a-z0-9-]+')
                ->name('show');
        });

        Route::prefix('appeal-drafts')->name('appeal-drafts.')->group(function (): void {
            Route::post('/', [AppealDraftController::class, 'store'])
                ->name('store');
            Route::get('/{id}', [AppealDraftController::class, 'show'])
                ->whereUuid('id')
                ->name('show');
            Route::patch('/{id}', [AppealDraftController::class, 'update'])
                ->whereUuid('id')
                ->name('update');
            Route::delete('/{id}', [AppealDraftController::class, 'destroy'])
                ->whereUuid('id')
                ->name('destroy');
            Route::post('/{id}/attachments', [AppealDraftAttachmentController::class, 'store'])
                ->whereUuid('id')
                ->name('attachments.store');
            Route::delete('/{id}/attachments/{attachment}', [AppealDraftAttachmentController::class, 'destroy'])
                ->whereUuid('id')
                ->whereUuid('attachment')
                ->name('attachments.destroy');
            Route::post('/{id}/submit', AppealDraftSubmitController::class)
                ->whereUuid('id')
                ->name('submit');
        });

        Route::prefix('profile')->name('profile.')->group(function (): void {
            Route::get('/', [ProfileController::class, 'show'])
                ->name('show');
            Route::patch('/', [ProfileController::class, 'update'])
                ->name('update');
            Route::post('/avatar', [ProfileAvatarController::class, 'store'])
                ->name('avatar.store');
            Route::delete('/avatar', [ProfileAvatarController::class, 'destroy'])
                ->name('avatar.destroy');
        });

        Route::prefix('dashboard')->name('dashboard.')->group(function (): void {
            Route::get('/appeals', [DashboardController::class, 'appeals'])
                ->name('appeals');
            Route::get('/drafts', [DashboardController::class, 'drafts'])
                ->name('drafts');
            Route::get('/saved-appeals', [DashboardController::class, 'savedAppeals'])
                ->name('saved-appeals');
            Route::get('/comments', [DashboardController::class, 'comments'])
                ->name('comments');
            Route::get('/notifications', [DashboardController::class, 'notifications'])
                ->name('notifications');
            Route::post('/notifications/mark-all-read', [DashboardController::class, 'markAllNotificationsRead'])
                ->name('notifications.mark-all-read');
            Route::patch('/notification-settings', [DashboardController::class, 'updateNotificationSettings'])
                ->name('notification-settings.update');
            Route::get('/status-history', [DashboardController::class, 'statusHistory'])
                ->name('status-history');
            Route::get('/security/sessions', [DashboardController::class, 'sessions'])
                ->name('security.sessions');
            Route::delete('/security/sessions/{id}', [DashboardController::class, 'terminateSession'])
                ->whereUuid('id')
                ->name('security.sessions.destroy');
            Route::get('/achievements', [DashboardController::class, 'achievements'])
                ->name('achievements');
        });

        Route::post('/subscriptions/news', [NewsSubscriptionController::class, 'store'])
            ->name('subscriptions.news.store');
        Route::delete('/subscriptions/news', [NewsSubscriptionController::class, 'destroy'])
            ->name('subscriptions.news.destroy');

        Route::post('/support-videos', SupportVideoController::class)
            ->name('support-videos.store');

        Route::get('/seo/sitemap-urls', SitemapUrlsController::class)
            ->name('seo.sitemap-urls');
    });
