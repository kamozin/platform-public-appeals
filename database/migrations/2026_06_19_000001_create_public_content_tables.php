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
            $table->boolean('is_admin')->default(false)->after('notifications_enabled');
        });

        Schema::create('category_groups', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('slug')->unique();
            $table->string('title');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('category_group_id')->constrained('category_groups')->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('description');
            $table->string('icon')->default('file');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('news_posts', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('excerpt');
            $table->longText('content');
            $table->string('category');
            $table->string('image_url')->nullable();
            $table->string('status')->default('draft')->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('public_appeals', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('excerpt');
            $table->longText('description');
            $table->string('status')->index();
            $table->string('status_label');
            $table->string('city')->index();
            $table->string('district')->nullable();
            $table->string('category')->index();
            $table->string('location')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->unsignedInteger('support_count')->default(0);
            $table->unsignedInteger('views_count')->default(0);
            $table->unsignedInteger('comments_count')->default(0);
            $table->string('image_url')->nullable();
            $table->boolean('is_public')->default(false)->index();
            $table->timestamps();
        });

        Schema::create('public_appeal_attachments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('public_appeal_id')->constrained('public_appeals')->cascadeOnDelete();
            $table->string('type')->default('image');
            $table->string('url');
            $table->string('title');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('public_appeal_timeline_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('public_appeal_id')->constrained('public_appeals')->cascadeOnDelete();
            $table->string('status');
            $table->string('title');
            $table->timestamp('happened_at');
            $table->text('text');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('public_appeal_documents', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('public_appeal_id')->constrained('public_appeals')->cascadeOnDelete();
            $table->string('title');
            $table->string('url');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('public_appeal_official_responses', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('public_appeal_id')->unique()->constrained('public_appeals')->cascadeOnDelete();
            $table->string('title');
            $table->text('text');
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('public_appeal_official_responses');
        Schema::dropIfExists('public_appeal_documents');
        Schema::dropIfExists('public_appeal_timeline_items');
        Schema::dropIfExists('public_appeal_attachments');
        Schema::dropIfExists('public_appeals');
        Schema::dropIfExists('news_posts');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('category_groups');

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('is_admin');
        });
    }
};
