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

        $now = now();
        $section = DB::table('site_sections')
            ->where('site_page_id', $pageId)
            ->where('key', 'postalshopper_calculator')
            ->first();

        if (! $section) {
            $sectionId = DB::table('site_sections')->insertGetId([
                'site_page_id' => $pageId,
                'key' => 'postalshopper_calculator',
                'name' => 'Postal Shopper Calculadora',
                'type' => 'postalshopper_calculator',
                'settings' => json_encode([
                    'eyebrow' => 'COTIZADOR DE ENVÍOS',
                    'title' => 'Cotiza tu paquete',
                    'title_accent' => 'en segundos.',
                    'description' => 'Consulta una tarifa estimada para tu envío.',
                    'weight_title' => 'Elige el peso del paquete',
                    'weight_help' => 'Selecciona entre 1 y 5 libras',
                    'weight_unit_singular' => 'libra',
                    'weight_unit_plural' => 'libras',
                    'currency_label' => '$us',
                    'total_label' => 'Tarifa estimada',
                    'image' => '',
                    'image_alt' => 'Cartero con un paquete y una calculadora de envíos',
                    'caption_label' => 'POSTAL SHOPPER',
                    'caption_text' => 'Tus compras, de Miami a Bolivia',
                ], JSON_UNESCAPED_UNICODE),
                'sort_order' => 5,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } else {
            $sectionId = $section->id;
        }

        if (! DB::table('site_section_items')->where('site_section_id', $sectionId)->exists()) {
            foreach ([
                [1, 10.59],
                [2, 13.86],
                [3, 17.14],
                [4, 20.41],
                [5, 23.70],
            ] as $index => [$weight, $price]) {
                DB::table('site_section_items')->insert([
                    'site_section_id' => $sectionId,
                    'name' => $weight . ' lb',
                    'type' => 'postalshopper_rate',
                    'data' => json_encode([
                        'weight' => $weight,
                        'price' => $price,
                    ], JSON_UNESCAPED_UNICODE),
                    'sort_order' => $index,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $steps = DB::table('site_sections')
            ->where('site_page_id', $pageId)
            ->where('key', 'postalshopper_steps')
            ->first();

        if ($steps) {
            $settings = is_array($steps->settings) ? $steps->settings : json_decode((string) $steps->settings, true);
            $settings = is_array($settings) ? $settings : [];

            if (($settings['title'] ?? null) === 'Tu puente directo entre Miami y Bolivia.') {
                $settings['title'] = '¿Cómo funciona Postal Shopper?';
            }

            if (($settings['subtitle'] ?? null) === 'Hemos simplificado el proceso de importacion para que tu unica preocupacion sea decidir que comprar hoy.') {
                $settings['subtitle'] = 'Mira el tutorial y compra desde Estados Unidos en 3 pasos';
            }

            $settings += [
                'tutorial_url' => '',
                'tutorial_image' => '',
                'tutorial_label' => 'Ver tutorial completo',
                'tutorial_duration' => '1:30 min',
            ];

            DB::table('site_sections')->where('id', $steps->id)->update([
                'settings' => json_encode($settings, JSON_UNESCAPED_UNICODE),
                'updated_at' => $now,
            ]);

            $stepItems = DB::table('site_section_items')
                ->where('site_section_id', $steps->id)
                ->orderBy('sort_order')
                ->get();
            $legacyTitles = [
                'Tu Direccion Americana',
                'Compra sin Limites',
                'Consolidacion y Ahorro',
                'El Vuelo a Casa',
            ];
            $currentTitles = $stepItems->map(function ($item) {
                $data = is_array($item->data) ? $item->data : json_decode((string) $item->data, true);

                return is_array($data) ? ($data['title'] ?? null) : null;
            })->all();

            if ($currentTitles === $legacyTitles) {
                DB::table('site_section_items')->where('site_section_id', $steps->id)->delete();

                foreach ([
                    ['1', 'user-plus', 'Regístrate gratis'],
                    ['2', 'cart', 'Compra y envía a tu casilla'],
                    ['3', 'package', 'Recibe tu paquete en Bolivia'],
                ] as $index => [$number, $icon, $title]) {
                    DB::table('site_section_items')->insert([
                        'site_section_id' => $steps->id,
                        'name' => $title,
                        'type' => 'postalshopper_step_card',
                        'data' => json_encode([
                            'step' => $number,
                            'icon' => $icon,
                            'title' => $title,
                            'text' => '',
                        ], JSON_UNESCAPED_UNICODE),
                        'sort_order' => $index,
                        'is_active' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }

        $destinations = DB::table('site_sections')
            ->where('site_page_id', $pageId)
            ->where('key', 'postalshopper_destinations')
            ->first();

        if ($destinations) {
            $settings = is_array($destinations->settings) ? $destinations->settings : json_decode((string) $destinations->settings, true);
            $settings = is_array($settings) ? $settings : [];
            $settings += ['upcoming_enabled' => true];
            if (($settings['upcoming_label'] ?? null) === 'Nuevos destinos') {
                $settings['upcoming_label'] = 'Nuevos envíos';
            }

            DB::table('site_sections')->where('id', $destinations->id)->update([
                'settings' => json_encode($settings, JSON_UNESCAPED_UNICODE),
                'sort_order' => 2,
                'updated_at' => $now,
            ]);

            foreach (DB::table('site_section_items')->where('site_section_id', $destinations->id)->get() as $item) {
                $data = is_array($item->data) ? $item->data : json_decode((string) $item->data, true);
                $data = is_array($data) ? $data : [];

                if (($data['label'] ?? null) === 'Estados Unidos' && ($data['status'] ?? null) === 'Disponible') {
                    $data['status'] = 'Envío directo a Bolivia';
                    DB::table('site_section_items')->where('id', $item->id)->update([
                        'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
                        'updated_at' => $now,
                    ]);
                }
            }
        }

        foreach ([
            'postalshopper_steps' => 3,
            'postalshopper_calculator' => 4,
            'postalshopper_intro' => 5,
            'postalshopper_benefits' => 6,
            'footer' => 7,
        ] as $key => $sortOrder) {
            DB::table('site_sections')
                ->where('site_page_id', $pageId)
                ->where('key', $key)
                ->update(['sort_order' => $sortOrder, 'updated_at' => $now]);
        }

        $hero = DB::table('site_sections')
            ->where('site_page_id', $pageId)
            ->where('key', 'postalshopper_hero')
            ->first();

        $registrationUrl = 'https://delivery.correos.gob.bo/';

        if ($hero) {
            $settings = is_array($hero->settings) ? $hero->settings : json_decode((string) $hero->settings, true);
            $settings = is_array($settings) ? $settings : [];

            if (in_array($settings['primary_button_label'] ?? null, ['Como funciona', 'Ver tutorial'], true)) {
                $settings['primary_button_label'] = 'Cómo funciona';
            }

            $settings['secondary_button_url'] = $registrationUrl;

            DB::table('site_sections')->where('id', $hero->id)->update([
                'settings' => json_encode($settings, JSON_UNESCAPED_UNICODE),
                'updated_at' => $now,
            ]);
        }
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

        if ($sectionId) {
            DB::table('site_section_items')->where('site_section_id', $sectionId)->delete();
            DB::table('site_sections')->where('id', $sectionId)->delete();
        }
    }
};
