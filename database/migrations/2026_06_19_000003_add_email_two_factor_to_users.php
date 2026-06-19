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
            $table->boolean('email_two_factor_enabled')->default(false);
            $table->timestamp('email_two_factor_confirmed_at')->nullable();
        });

        Schema::table('verification_challenges', function (Blueprint $table): void {
            $table->foreignId('user_id')->nullable()->index();
            $table->timestamp('consumed_at')->nullable();
            $table->index(['purpose', 'target', 'expires_at'], 'verification_challenges_lookup_index');
        });
    }

    public function down(): void
    {
        Schema::table('verification_challenges', function (Blueprint $table): void {
            $table->dropIndex('verification_challenges_lookup_index');
            $table->dropIndex(['user_id']);
            $table->dropColumn(['user_id', 'consumed_at']);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['email_two_factor_enabled', 'email_two_factor_confirmed_at']);
        });
    }
};
