<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $pageId = DB::table('site_pages')->where('slug', 'casillas')->value('id');
        if (! $pageId) {
            return;
        }

        $section = DB::table('site_sections')
            ->where('site_page_id', $pageId)
            ->where('key', 'casillas_sizes')
            ->first();
        if (! $section) {
            return;
        }

        $settings = is_array($section->settings)
            ? $section->settings
            : json_decode((string) $section->settings, true);
        $settings = is_array($settings) ? $settings : [];
        $settings['plan_label'] = 'Plan de alquiler:';

        DB::table('site_sections')->where('id', $section->id)->update([
            'settings' => json_encode($settings, JSON_UNESCAPED_UNICODE),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        $pageId = DB::table('site_pages')->where('slug', 'casillas')->value('id');
        if (! $pageId) {
            return;
        }

        $section = DB::table('site_sections')
            ->where('site_page_id', $pageId)
            ->where('key', 'casillas_sizes')
            ->first();
        if (! $section) {
            return;
        }

        $settings = is_array($section->settings)
            ? $section->settings
            : json_decode((string) $section->settings, true);
        $settings = is_array($settings) ? $settings : [];
        $settings['plan_label'] = 'Periodo de alquiler';

        DB::table('site_sections')->where('id', $section->id)->update([
            'settings' => json_encode($settings, JSON_UNESCAPED_UNICODE),
            'updated_at' => now(),
        ]);
    }
};
