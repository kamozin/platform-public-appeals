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
            $table->foreignId('user_id')->nullable()->change();
            $table->string('guest_token_hash')->nullable()->unique()->after('user_id');
        });

        Schema::table('appeal_attachments', function (Blueprint $table): void {
            $table->foreignId('user_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('appeal_attachments', function (Blueprint $table): void {
            $table->foreignId('user_id')->nullable(false)->change();
        });

        Schema::table('appeal_drafts', function (Blueprint $table): void {
            $table->dropColumn('guest_token_hash');
            $table->foreignId('user_id')->nullable(false)->change();
        });
    }
};
