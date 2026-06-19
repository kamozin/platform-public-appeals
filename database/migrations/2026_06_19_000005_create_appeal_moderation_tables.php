<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appeal_drafts', function (Blueprint $table): void {
            $table->foreignId('moderated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('moderated_at')->nullable();
            $table->text('moderation_note')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignUuid('public_appeal_id')->nullable()->constrained('public_appeals')->nullOnDelete();

            $table->index(['status', 'submitted_at']);
        });

        Schema::create('appeal_moderation_events', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('appeal_draft_id')->constrained('appeal_drafts')->cascadeOnDelete();
            $table->foreignId('moderator_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action')->index();
            $table->text('comment')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index(['appeal_draft_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appeal_moderation_events');

        Schema::table('appeal_drafts', function (Blueprint $table): void {
            $table->dropIndex(['status', 'submitted_at']);
            $table->dropConstrainedForeignId('moderated_by_user_id');
            $table->dropConstrainedForeignId('public_appeal_id');
            $table->dropColumn([
                'moderated_at',
                'moderation_note',
                'rejection_reason',
            ]);
        });
    }
};
