<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_page_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_page_id')->constrained('site_pages')->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->string('action', 40)->default('updated');
            $table->text('change_summary')->nullable();
            $table->json('snapshot');
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('created_by_name')->nullable();
            $table->string('created_by_email')->nullable();
            $table->foreignId('restored_from_version_id')->nullable()->constrained('site_page_versions')->nullOnDelete();
            $table->timestamps();

            $table->unique(['site_page_id', 'version_number']);
            $table->index(['site_page_id', 'created_at']);
        });

        Schema::create('site_page_change_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_page_id')->constrained('site_pages')->cascadeOnDelete();
            $table->foreignId('site_page_version_id')->constrained('site_page_versions')->cascadeOnDelete();
            $table->foreignId('site_section_id')->nullable()->constrained('site_sections')->nullOnDelete();
            $table->foreignId('site_section_item_id')->nullable()->constrained('site_section_items')->nullOnDelete();
            $table->string('section_key')->nullable();
            $table->string('item_name')->nullable();
            $table->string('entity_type', 40);
            $table->string('action', 40);
            $table->string('field_name')->nullable();
            $table->text('summary')->nullable();
            $table->json('before_state')->nullable();
            $table->json('after_state')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('created_by_name')->nullable();
            $table->string('created_by_email')->nullable();
            $table->timestamps();

            $table->index(['site_page_id', 'created_at']);
            $table->index(['section_key', 'entity_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_page_change_logs');
        Schema::dropIfExists('site_page_versions');
    }
};
