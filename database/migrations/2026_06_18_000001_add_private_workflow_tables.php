<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('phone')->nullable()->unique()->after('email');
            $table->boolean('notifications_enabled')->default(true)->after('password');
            $table->string('avatar_path')->nullable()->after('notifications_enabled');
        });

        Schema::create('api_tokens', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('token_hash')->unique();
            $table->string('name')->default('api');
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('verification_challenges', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('purpose');
            $table->string('channel');
            $table->string('target')->index();
            $table->string('code_hash');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('expires_at');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('appeal_drafts', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('draft')->index();
            $table->string('category')->nullable();
            $table->string('submission_type')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('urgency')->nullable();
            $table->string('location')->nullable();
            $table->string('contact_visibility')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('appeal_attachments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('appeal_draft_id')->constrained('appeal_drafts')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('kind');
            $table->string('original_name');
            $table->string('path');
            $table->string('mime_type');
            $table->unsignedBigInteger('size');
            $table->timestamps();
        });

        Schema::create('appeal_comments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('appeal_slug')->index();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('pending')->index();
            $table->string('type')->default('public');
            $table->text('comment');
            $table->boolean('has_media')->default(false);
            $table->timestamps();
        });

        Schema::create('saved_appeals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('appeal_slug');
            $table->timestamps();
            $table->unique(['user_id', 'appeal_slug']);
        });

        Schema::create('news_subscriptions', function (Blueprint $table): void {
            $table->id();
            $table->string('email')->unique();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('support_videos', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('author_email')->nullable();
            $table->string('original_name');
            $table->string('path');
            $table->string('mime_type');
            $table->unsignedBigInteger('size');
            $table->string('status')->default('pending_moderation');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_videos');
        Schema::dropIfExists('news_subscriptions');
        Schema::dropIfExists('saved_appeals');
        Schema::dropIfExists('appeal_comments');
        Schema::dropIfExists('appeal_attachments');
        Schema::dropIfExists('appeal_drafts');
        Schema::dropIfExists('verification_challenges');
        Schema::dropIfExists('api_tokens');

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['phone', 'notifications_enabled', 'avatar_path']);
        });
    }
};
