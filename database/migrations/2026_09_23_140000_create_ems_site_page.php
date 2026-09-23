<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const SECTION_KEYS = [
        'ems_intro',
        'ems_benefits',
        'ems_national',
        'ems_international',
    ];

    public function up(): void
    {
        $home = DB::table('site_pages')->where('slug', 'home')->first();

        if (! $home) {
            return;
        }

        $now = now();
        $emsPageId = DB::table('site_pages')->where('slug', 'ems')->value('id');

        if (! $emsPageId) {
            $emsPageId = DB::table('site_pages')->insertGetId([
                'slug' => 'ems',
                'name' => 'EMS',
                'meta_title' => 'EMS | Correos de Bolivia',
                'meta_description' => 'Conoce el servicio EMS nacional e internacional de Correos de Bolivia.',
                'theme' => null,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::table('site_sections')
            ->where('site_page_id', $home->id)
            ->whereIn('key', self::SECTION_KEYS)
            ->update([
                'site_page_id' => $emsPageId,
            ]);
    }

    public function down(): void
    {
        $homePageId = DB::table('site_pages')->where('slug', 'home')->value('id');
        $emsPage = DB::table('site_pages')->where('slug', 'ems')->first();

        if (! $homePageId || ! $emsPage) {
            return;
        }

        DB::table('site_sections')
            ->where('site_page_id', $emsPage->id)
            ->whereIn('key', self::SECTION_KEYS)
            ->update([
                'site_page_id' => $homePageId,
            ]);

        DB::table('site_pages')->where('id', $emsPage->id)->delete();
    }
};
