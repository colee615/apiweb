<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('site_pages')->where('slug', 'tramites')->exists()) {
            return;
        }

        $now = now();
        $home = DB::table('site_pages')->where('slug', 'home')->first();
        $homeHeader = $home ? DB::table('site_sections')->where('site_page_id', $home->id)->where('key', 'header')->first() : null;
        $homeFooter = $home ? DB::table('site_sections')->where('site_page_id', $home->id)->where('key', 'footer')->first() : null;

        $pageId = DB::table('site_pages')->insertGetId([
            'slug' => 'tramites',
            'name' => 'Trámites',
            'meta_title' => 'Trámites | Correos de Bolivia',
            'meta_description' => 'Consulta los requisitos, formularios y documentos de los trámites de Correos de Bolivia.',
            'theme' => $home?->theme ?? json_encode([
                'logo_url' => '',
                'primary_color' => '#20539a',
                'secondary_color' => '#2f3f5c',
                'accent_color' => '#fecc36',
            ], JSON_UNESCAPED_UNICODE),
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $headerId = $this->section($pageId, 'header', 'Encabezado', 'header', $homeHeader?->settings ?? json_encode([], JSON_UNESCAPED_UNICODE), 0, $now);
        if ($homeHeader) {
            $this->copyItems($homeHeader->id, $headerId, $now);
        }

        $this->section(
            $pageId,
            'tramites',
            'Trámites',
            'tramites_grid',
            json_encode([
                'eyebrow' => 'SERVICIOS EN LÍNEA',
                'title' => 'Trámites',
                'description' => 'Encuentra formularios, requisitos y documentos útiles para realizar tus gestiones de forma rápida y sencilla.',
                'search_placeholder' => 'Buscar un trámite...',
                'empty_text' => 'No encontramos trámites con esos criterios.',
            ], JSON_UNESCAPED_UNICODE),
            1,
            $now
        );

        $footerId = $this->section($pageId, 'footer', 'Pie de página', 'footer', $homeFooter?->settings ?? json_encode([], JSON_UNESCAPED_UNICODE), 2, $now);
        if ($homeFooter) {
            $this->copyItems($homeFooter->id, $footerId, $now);
        }
    }

    public function down(): void
    {
        $pageId = DB::table('site_pages')->where('slug', 'tramites')->value('id');
        if (! $pageId) {
            return;
        }

        DB::table('site_section_items')->whereIn('site_section_id', function ($query) use ($pageId) {
            $query->select('id')->from('site_sections')->where('site_page_id', $pageId);
        })->delete();
        DB::table('site_sections')->where('site_page_id', $pageId)->delete();
        DB::table('site_pages')->where('id', $pageId)->delete();
    }

    protected function section(int $pageId, string $key, string $name, string $type, string $settings, int $sortOrder, $now): int
    {
        return DB::table('site_sections')->insertGetId([
            'site_page_id' => $pageId,
            'key' => $key,
            'name' => $name,
            'type' => $type,
            'settings' => $settings,
            'sort_order' => $sortOrder,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    protected function copyItems(int $sourceSectionId, int $targetSectionId, $now): void
    {
        $items = DB::table('site_section_items')->where('site_section_id', $sourceSectionId)->orderBy('sort_order')->get();
        foreach ($items as $index => $item) {
            DB::table('site_section_items')->insert([
                'site_section_id' => $targetSectionId,
                'name' => $item->name,
                'type' => $item->type,
                'data' => $item->data,
                'sort_order' => $index,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
};
