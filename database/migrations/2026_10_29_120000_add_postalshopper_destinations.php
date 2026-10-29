<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $pageId = DB::table('site_pages')->where('slug', 'postalshopper')->value('id');

        if (! $pageId || DB::table('site_sections')
            ->where('site_page_id', $pageId)
            ->where('key', 'postalshopper_destinations')
            ->exists()) {
            return;
        }

        $now = now();
        $sectionId = DB::table('site_sections')->insertGetId([
            'site_page_id' => $pageId,
            'key' => 'postalshopper_destinations',
            'name' => 'Postal Shopper Destinos',
            'type' => 'postalshopper_destinations',
            'settings' => json_encode([], JSON_UNESCAPED_UNICODE),
            'sort_order' => 2,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $destinations = [
            ['Estados Unidos', '🇺🇸', 'Disponible', '/contacto'],
            ['China', '🇨🇳', 'Muy pronto', ''],
            ['España', '🇪🇸', 'Muy pronto', ''],
            ['Brasil', '🇧🇷', 'Muy pronto', ''],
            ['Chile', '🇨🇱', 'Muy pronto', ''],
            ['Argentina', '🇦🇷', 'Muy pronto', ''],
            ['México', '🇲🇽', 'Muy pronto', ''],
        ];

        foreach ($destinations as $index => [$label, $flag, $status, $url]) {
            DB::table('site_section_items')->insert([
                'site_section_id' => $sectionId,
                'name' => $label,
                'type' => 'postalshopper_destination',
                'data' => json_encode([
                    'label' => $label,
                    'flag' => $flag,
                    'status' => $status,
                    'url' => $url,
                ], JSON_UNESCAPED_UNICODE),
                'sort_order' => $index,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        $sectionId = DB::table('site_sections')
            ->where('key', 'postalshopper_destinations')
            ->whereIn('site_page_id', function ($query) {
                $query->select('id')->from('site_pages')->where('slug', 'postalshopper');
            })
            ->value('id');

        if (! $sectionId) {
            return;
        }

        DB::table('site_section_items')->where('site_section_id', $sectionId)->delete();
        DB::table('site_sections')->where('id', $sectionId)->delete();
    }
};
