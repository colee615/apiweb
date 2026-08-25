<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $pageId = DB::table('site_pages')->where('slug', 'correspondencia-agrupada')->value('id');

        if (! $pageId) {
            return;
        }

        $map = [
            'eca_hero' => ['correspondencia_hero', 'Correspondencia Hero', 'correspondencia_hero'],
            'eca_intro' => ['correspondencia_intro', 'Correspondencia Intro', 'correspondencia_intro'],
            'eca_rates' => ['correspondencia_rates', 'Correspondencia Tarifas', 'correspondencia_rates'],
            'eca_coverage' => ['correspondencia_coverage', 'Correspondencia Cobertura', 'correspondencia_coverage'],
            'eca_solutions' => ['correspondencia_solutions', 'Correspondencia Soluciones', 'correspondencia_solutions'],
            'eca_cta' => ['correspondencia_cta', 'Correspondencia CTA', 'correspondencia_cta'],
        ];

        foreach ($map as $oldKey => [$newKey, $newName, $newType]) {
            $section = DB::table('site_sections')
                ->where('site_page_id', $pageId)
                ->where('key', $oldKey)
                ->first();

            if (! $section) {
                continue;
            }

            $settings = json_decode($section->settings ?? '[]', true) ?: [];

            foreach (['primary_button_url', 'secondary_button_url', 'button_url'] as $urlKey) {
                if (! empty($settings[$urlKey]) && is_string($settings[$urlKey])) {
                    $settings[$urlKey] = str_replace('#eca-', '#correspondencia-', $settings[$urlKey]);
                }
            }

            DB::table('site_sections')
                ->where('id', $section->id)
                ->update([
                    'key' => $newKey,
                    'name' => $newName,
                    'type' => $newType,
                    'settings' => json_encode($settings, JSON_UNESCAPED_UNICODE),
                    'updated_at' => now(),
                ]);

            $items = DB::table('site_section_items')->where('site_section_id', $section->id)->get();
            foreach ($items as $item) {
                DB::table('site_section_items')
                    ->where('id', $item->id)
                    ->update([
                        'type' => str_replace('eca_', 'correspondencia_', (string) $item->type),
                        'updated_at' => now(),
                    ]);
            }
        }
    }

    public function down(): void
    {
        $pageId = DB::table('site_pages')->where('slug', 'correspondencia-agrupada')->value('id');

        if (! $pageId) {
            return;
        }

        $map = [
            'correspondencia_hero' => ['eca_hero', 'ECA Hero', 'eca_hero'],
            'correspondencia_intro' => ['eca_intro', 'ECA Intro', 'eca_intro'],
            'correspondencia_rates' => ['eca_rates', 'ECA Tarifas', 'eca_rates'],
            'correspondencia_coverage' => ['eca_coverage', 'ECA Cobertura', 'eca_coverage'],
            'correspondencia_solutions' => ['eca_solutions', 'ECA Soluciones', 'eca_solutions'],
            'correspondencia_cta' => ['eca_cta', 'ECA CTA', 'eca_cta'],
        ];

        foreach ($map as $oldKey => [$newKey, $newName, $newType]) {
            $section = DB::table('site_sections')
                ->where('site_page_id', $pageId)
                ->where('key', $oldKey)
                ->first();

            if (! $section) {
                continue;
            }

            $settings = json_decode($section->settings ?? '[]', true) ?: [];

            foreach (['primary_button_url', 'secondary_button_url', 'button_url'] as $urlKey) {
                if (! empty($settings[$urlKey]) && is_string($settings[$urlKey])) {
                    $settings[$urlKey] = str_replace('#correspondencia-', '#eca-', $settings[$urlKey]);
                }
            }

            DB::table('site_sections')
                ->where('id', $section->id)
                ->update([
                    'key' => $newKey,
                    'name' => $newName,
                    'type' => $newType,
                    'settings' => json_encode($settings, JSON_UNESCAPED_UNICODE),
                    'updated_at' => now(),
                ]);

            $items = DB::table('site_section_items')->where('site_section_id', $section->id)->get();
            foreach ($items as $item) {
                DB::table('site_section_items')
                    ->where('id', $item->id)
                    ->update([
                        'type' => str_replace('correspondencia_', 'eca_', (string) $item->type),
                        'updated_at' => now(),
                    ]);
            }
        }
    }
};
