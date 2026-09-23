<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $page = DB::table('site_pages')->where('slug', 'tramites')->first();

        if (! $page || DB::table('site_pages')->where('slug', 'informacion-postal')->exists()) {
            return;
        }

        DB::table('site_pages')->where('id', $page->id)->update([
            'slug' => 'informacion-postal',
            'name' => 'Información Postal',
            'meta_title' => 'Información Postal | Correos de Bolivia',
            'meta_description' => 'Consulta requisitos, formularios y documentos útiles de Correos de Bolivia.',
            'updated_at' => now(),
        ]);

        $section = DB::table('site_sections')->where('site_page_id', $page->id)->where('key', 'tramites')->first();
        if (! $section) {
            return;
        }

        $settings = json_decode($section->settings ?: '{}', true) ?: [];
        $settings['eyebrow'] = 'SERVICIOS POSTALES';
        $settings['title'] = 'Información Postal';
        $settings['description'] = 'Encuentra requisitos, formularios y documentos útiles para realizar tus gestiones postales de forma rápida y sencilla.';

        DB::table('site_sections')->where('id', $section->id)->update([
            'name' => 'Información Postal',
            'settings' => json_encode($settings, JSON_UNESCAPED_UNICODE),
            'updated_at' => now(),
        ]);

        if (! DB::table('site_section_items')->where('site_section_id', $section->id)->exists()) {
            DB::table('site_section_items')->insert([
                'site_section_id' => $section->id,
                'name' => 'Mercancías prohibidas',
                'type' => 'tramite',
                'data' => json_encode([
                    'title' => 'Mercancías prohibidas',
                    'description' => 'Consulta las mercancías que no pueden ser transportadas por los servicios postales y descarga la información oficial.',
                    'category' => 'Información Postal',
                    'url' => '',
                    'src' => '',
                    'file_name' => '',
                    'file_mime' => '',
                    'file_extension' => '',
                    'attachments' => [],
                ], JSON_UNESCAPED_UNICODE),
                'sort_order' => 0,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        $page = DB::table('site_pages')->where('slug', 'informacion-postal')->first();
        if (! $page) {
            return;
        }

        DB::table('site_pages')->where('id', $page->id)->update([
            'slug' => 'tramites',
            'name' => 'Trámites',
            'meta_title' => 'Trámites | Correos de Bolivia',
            'updated_at' => now(),
        ]);
    }
};
