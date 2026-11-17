<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $sizeData = [
        [
            'name' => 'Pequeña',
            'dimensions' => '11 × 11 × 27 cm',
            'monthly_price' => 35,
            'semiannual_price' => 210,
            'annual_price' => 420,
        ],
        [
            'name' => 'Mediana',
            'dimensions' => '12 × 23 × 27 cm',
            'monthly_price' => 49,
            'semiannual_price' => 294,
            'annual_price' => 588,
        ],
        [
            'name' => 'Gaveta',
            'dimensions' => '24 × 23 × 27 cm',
            'monthly_price' => 70,
            'semiannual_price' => 420,
            'annual_price' => 840,
        ],
        [
            'name' => 'Cajón',
            'dimensions' => '33 × 23 × 27 cm',
            'monthly_price' => 84,
            'semiannual_price' => 504,
            'annual_price' => 1008,
        ],
    ];

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

        DB::transaction(function () use ($section): void {
            $settings = is_array($section->settings)
                ? $section->settings
                : json_decode((string) $section->settings, true);
            $settings = is_array($settings) ? $settings : [];
            $settings['plan_label'] = 'Periodo de alquiler';
            $settings['monthly_label'] = 'Mensual';
            $settings['semiannual_label'] = 'Semestral';
            $settings['annual_label'] = 'Anual';
            unset($settings['quarterly_label'], $settings['annual_badge']);

            DB::table('site_sections')->where('id', $section->id)->update([
                'settings' => json_encode($settings, JSON_UNESCAPED_UNICODE),
                'updated_at' => now(),
            ]);

            $items = DB::table('site_section_items')
                ->where('site_section_id', $section->id)
                ->where('type', 'casillas_size_card')
                ->orderBy('sort_order')
                ->get();

            foreach ($this->sizeData as $index => $size) {
                $item = $items->get($index);
                if (! $item) {
                    DB::table('site_section_items')->insert([
                        'site_section_id' => $section->id,
                        'name' => $size['name'],
                        'type' => 'casillas_size_card',
                        'data' => json_encode([
                            'title' => $size['name'],
                            'dimensions' => $size['dimensions'],
                            'monthly_price' => $size['monthly_price'],
                            'semiannual_price' => $size['semiannual_price'],
                            'annual_price' => $size['annual_price'],
                        ], JSON_UNESCAPED_UNICODE),
                        'sort_order' => $index,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    continue;
                }

                $data = is_array($item->data) ? $item->data : json_decode((string) $item->data, true);
                $data = is_array($data) ? $data : [];
                $data['title'] = $size['name'];
                $data['dimensions'] = $size['dimensions'];
                $data['monthly_price'] = $size['monthly_price'];
                $data['semiannual_price'] = $size['semiannual_price'];
                $data['annual_price'] = $size['annual_price'];

                DB::table('site_section_items')->where('id', $item->id)->update([
                    'name' => $size['name'],
                    'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
                    'sort_order' => $index,
                    'updated_at' => now(),
                ]);
            }
        });
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

        DB::transaction(function () use ($section): void {
            $settings = is_array($section->settings)
                ? $section->settings
                : json_decode((string) $section->settings, true);
            $settings = is_array($settings) ? $settings : [];
            unset($settings['monthly_label']);
            $settings['quarterly_label'] = 'Trimestral';
            $settings['annual_badge'] = '-20%';

            DB::table('site_sections')->where('id', $section->id)->update([
                'settings' => json_encode($settings, JSON_UNESCAPED_UNICODE),
                'updated_at' => now(),
            ]);

            $oldDimensions = [
                '15 × 30 × 40 cm',
                '25 × 30 × 50 cm',
                '35 × 40 × 60 cm',
                '50 × 50 × 80 cm',
            ];

            $items = DB::table('site_section_items')
                ->where('site_section_id', $section->id)
                ->where('type', 'casillas_size_card')
                ->orderBy('sort_order')
                ->get();

            foreach ($items as $index => $item) {
                $data = is_array($item->data) ? $item->data : json_decode((string) $item->data, true);
                $data = is_array($data) ? $data : [];
                unset($data['monthly_price'], $data['semiannual_price'], $data['annual_price']);
                if (isset($oldDimensions[$index])) {
                    $data['dimensions'] = $oldDimensions[$index];
                }

                DB::table('site_section_items')->where('id', $item->id)->update([
                    'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
                    'updated_at' => now(),
                ]);
            }
        });
    }
};
