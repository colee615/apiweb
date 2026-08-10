<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auth_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('guard_name', 40)->index();
            $table->string('session_key', 128);
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('last_path', 255)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('logged_in_at')->nullable()->index();
            $table->timestamp('last_seen_at')->nullable()->index();
            $table->timestamp('logged_out_at')->nullable();
            $table->timestamps();

            $table->unique(['guard_name', 'session_key']);
            $table->index(['guard_name', 'is_active', 'last_seen_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auth_sessions');
    }
};
