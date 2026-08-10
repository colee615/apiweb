<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_visitor_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('visitor_token', 80)->index();
            $table->string('session_token', 80)->unique();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device_type', 30)->nullable();
            $table->string('browser', 80)->nullable();
            $table->string('platform', 80)->nullable();
            $table->string('country', 120)->nullable();
            $table->string('city', 120)->nullable();
            $table->string('current_path', 255)->nullable()->index();
            $table->string('current_title', 255)->nullable();
            $table->text('referrer')->nullable();
            $table->boolean('is_online')->default(true)->index();
            $table->timestamp('first_seen_at')->nullable()->index();
            $table->timestamp('last_seen_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();
            $table->string('visitor_token', 80)->index();
            $table->string('session_token', 80)->index();
            $table->string('event_name', 60)->index();
            $table->string('page_path', 255)->nullable()->index();
            $table->string('page_name', 255)->nullable();
            $table->string('section_key', 120)->nullable()->index();
            $table->string('label', 255)->nullable();
            $table->string('searched_term', 160)->nullable()->index();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_events');
        Schema::dropIfExists('analytics_visitor_sessions');
    }
};
