<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $section = DB::table('site_sections')
            ->where('key', 'tramites')
            ->whereIn('site_page_id', function ($query) {
                $query->select('id')->from('site_pages')->whereIn('slug', ['informacion-postal', 'tramites']);
            })
            ->first();

        if (! $section || DB::table('site_section_items')->where('site_section_id', $section->id)->exists()) {
            return;
        }

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

    public function down(): void
    {
        $sectionId = DB::table('site_sections')
            ->where('key', 'tramites')
            ->whereIn('site_page_id', function ($query) {
                $query->select('id')->from('site_pages')->whereIn('slug', ['informacion-postal', 'tramites']);
            })
            ->value('id');

        if ($sectionId) {
            DB::table('site_section_items')
                ->where('site_section_id', $sectionId)
                ->where('name', 'Mercancías prohibidas')
                ->delete();
        }
    }
};
