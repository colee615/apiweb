<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const SEED_MARKER = '2026_10_29_130000';

    public function up(): void
    {
        $pageId = DB::table('site_pages')->where('slug', 'postalshopper')->value('id');

        if (! $pageId) {
            return;
        }

        $section = DB::table('site_sections')
            ->where('site_page_id', $pageId)
            ->where('key', 'postalshopper_destinations')
            ->first();

        if (! $section) {
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
        } else {
            $sectionId = $section->id;
        }

        $destinations = [
            ['Estados Unidos', '🇺🇸', 'Disponible', '/contacto'],
            ['China', '🇨🇳', 'Muy pronto', ''],
            ['España', '🇪🇸', 'Muy pronto', ''],
            ['Brasil', '🇧🇷', 'Muy pronto', ''],
            ['Chile', '🇨🇱', 'Muy pronto', ''],
            ['Argentina', '🇦🇷', 'Muy pronto', ''],
            ['México', '🇲🇽', 'Muy pronto', ''],
        ];

        $now = now();
        foreach ($destinations as [$label, $flag, $status, $url]) {
            $exists = DB::table('site_section_items')
                ->where('site_section_id', $sectionId)
                ->where('name', $label)
                ->exists();

            if ($exists) {
                continue;
            }

            $sortOrder = ((int) DB::table('site_section_items')
                ->where('site_section_id', $sectionId)
                ->max('sort_order')) + 1;

            DB::table('site_section_items')->insert([
                'site_section_id' => $sectionId,
                'name' => $label,
                'type' => 'postalshopper_destination',
                'data' => json_encode([
                    'label' => $label,
                    'flag' => $flag,
                    'status' => $status,
                    'url' => $url,
                    '_seeded_by' => self::SEED_MARKER,
                ], JSON_UNESCAPED_UNICODE),
                'sort_order' => $sortOrder,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

    }

    public function down(): void
    {
        $sectionIds = DB::table('site_sections')
            ->where('key', 'postalshopper_destinations')
            ->whereIn('site_page_id', function ($query) {
                $query->select('id')->from('site_pages')->where('slug', 'postalshopper');
            })
            ->pluck('id');

        if ($sectionIds->isEmpty()) {
            return;
        }

        DB::table('site_section_items')
            ->whereIn('site_section_id', $sectionIds)
            ->get()
            ->each(function ($item) {
                $data = is_array($item->data) ? $item->data : json_decode((string) $item->data, true);

                if (($data['_seeded_by'] ?? null) === self::SEED_MARKER) {
                    DB::table('site_section_items')->where('id', $item->id)->delete();
                }
            });
    }
};
