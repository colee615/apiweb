<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $pageId = DB::table('site_pages')->where('slug', 'postalshopper')->value('id');
        if (! $pageId) {
            return;
        }

        $section = DB::table('site_sections')
            ->where('site_page_id', $pageId)
            ->where('key', 'postalshopper_calculator')
            ->first();

        if (! $section) {
            return;
        }

        DB::transaction(function () use ($section): void {
            $rates = [
                1 => 10.59,
                2 => 13.86,
                3 => 17.14,
                4 => 20.41,
                5 => 23.70,
            ];

            for ($weight = 6; $weight <= 44; $weight++) {
                $rates[$weight] = round(23.70 + (($weight - 5) * 4), 2);
            }

            $existingItems = DB::table('site_section_items')
                ->where('site_section_id', $section->id)
                ->where('type', 'postalshopper_rate')
                ->orderBy('id')
                ->get();

            $itemsByWeight = [];
            foreach ($existingItems as $item) {
                $data = is_array($item->data) ? $item->data : json_decode((string) $item->data, true);
                $weight = (int) ($data['weight'] ?? 0);
                if ($weight >= 1 && $weight <= 44 && ! isset($itemsByWeight[$weight])) {
                    $itemsByWeight[$weight] = $item;
                }
            }

            $now = now();
            $keptIds = [];
            foreach ($rates as $weight => $price) {
                $data = json_encode(['weight' => $weight, 'price' => $price], JSON_UNESCAPED_UNICODE);
                $existing = $itemsByWeight[$weight] ?? null;

                if ($existing) {
                    DB::table('site_section_items')->where('id', $existing->id)->update([
                        'name' => $weight . ' lb',
                        'type' => 'postalshopper_rate',
                        'data' => $data,
                        'sort_order' => $weight - 1,
                        'is_active' => true,
                        'updated_at' => $now,
                    ]);
                    $keptIds[] = $existing->id;
                    continue;
                }

                $keptIds[] = DB::table('site_section_items')->insertGetId([
                    'site_section_id' => $section->id,
                    'name' => $weight . ' lb',
                    'type' => 'postalshopper_rate',
                    'data' => $data,
                    'sort_order' => $weight - 1,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            DB::table('site_section_items')
                ->where('site_section_id', $section->id)
                ->where('type', 'postalshopper_rate')
                ->whereNotIn('id', $keptIds)
                ->delete();

            $settings = is_array($section->settings)
                ? $section->settings
                : json_decode((string) $section->settings, true);
            $settings = is_array($settings) ? $settings : [];
            $settings['excess_weight_price_per_lb'] = 4;

            DB::table('site_sections')->where('id', $section->id)->update([
                'settings' => json_encode($settings, JSON_UNESCAPED_UNICODE),
                'updated_at' => $now,
            ]);
        });
    }

    public function down(): void
    {
        $pageId = DB::table('site_pages')->where('slug', 'postalshopper')->value('id');
        if (! $pageId) {
            return;
        }

        $sectionId = DB::table('site_sections')
            ->where('site_page_id', $pageId)
            ->where('key', 'postalshopper_calculator')
            ->value('id');

        if (! $sectionId) {
            return;
        }

        $excessIds = DB::table('site_section_items')
            ->where('site_section_id', $sectionId)
            ->where('type', 'postalshopper_rate')
            ->get()
            ->filter(function ($item): bool {
                $data = is_array($item->data) ? $item->data : json_decode((string) $item->data, true);
                return (int) ($data['weight'] ?? 0) > 20;
            })
            ->pluck('id');

        if ($excessIds->isNotEmpty()) {
            DB::table('site_section_items')->whereIn('id', $excessIds)->delete();
        }
    }
};
