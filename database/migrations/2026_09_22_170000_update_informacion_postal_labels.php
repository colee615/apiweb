<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $pageIds = DB::table('site_pages')
            ->whereIn('slug', ['informacion-postal', 'tramites'])
            ->pluck('id');

        foreach ($pageIds as $pageId) {
            $section = DB::table('site_sections')
                ->where('site_page_id', $pageId)
                ->where('key', 'tramites')
                ->first();

            if (! $section) {
                continue;
            }

            $settings = json_decode($section->settings ?: '{}', true) ?: [];
            $settings['title'] = $settings['title'] ?? 'Información Postal';
            $settings['search_placeholder'] = 'Buscar información postal...';
            $settings['empty_text'] = 'No encontramos información con esos criterios.';

            DB::table('site_sections')->where('id', $section->id)->update([
                'settings' => json_encode($settings, JSON_UNESCAPED_UNICODE),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        $pageIds = DB::table('site_pages')
            ->whereIn('slug', ['informacion-postal', 'tramites'])
            ->pluck('id');

        foreach ($pageIds as $pageId) {
            $section = DB::table('site_sections')
                ->where('site_page_id', $pageId)
                ->where('key', 'tramites')
                ->first();

            if (! $section) {
                continue;
            }

            $settings = json_decode($section->settings ?: '{}', true) ?: [];
            $settings['search_placeholder'] = 'Buscar un trámite...';
            $settings['empty_text'] = 'No encontramos trámites con esos criterios.';

            DB::table('site_sections')->where('id', $section->id)->update([
                'settings' => json_encode($settings, JSON_UNESCAPED_UNICODE),
                'updated_at' => now(),
            ]);
        }
    }
};
