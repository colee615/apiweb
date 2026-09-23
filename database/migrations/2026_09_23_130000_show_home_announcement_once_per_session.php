<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $homePageId = DB::table('site_pages')->where('slug', 'home')->value('id');

        if (! $homePageId) {
            return;
        }

        $section = DB::table('site_sections')
            ->where('site_page_id', $homePageId)
            ->where('key', 'announcement_modal')
            ->first();

        if (! $section) {
            return;
        }

        $settings = json_decode($section->settings ?: '{}', true);
        $settings = is_array($settings) ? $settings : [];
        $settings['show_once'] = true;

        DB::table('site_sections')
            ->where('id', $section->id)
            ->update([
                'settings' => json_encode($settings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        $homePageId = DB::table('site_pages')->where('slug', 'home')->value('id');

        if (! $homePageId) {
            return;
        }

        $section = DB::table('site_sections')
            ->where('site_page_id', $homePageId)
            ->where('key', 'announcement_modal')
            ->first();

        if (! $section) {
            return;
        }

        $settings = json_decode($section->settings ?: '{}', true);
        $settings = is_array($settings) ? $settings : [];
        $settings['show_once'] = false;

        DB::table('site_sections')
            ->where('id', $section->id)
            ->update([
                'settings' => json_encode($settings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'updated_at' => now(),
            ]);
    }
};
