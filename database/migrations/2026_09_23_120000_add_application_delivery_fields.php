<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $sectionIds = DB::table('site_sections')
            ->where('key', 'applications')
            ->pluck('id');

        if ($sectionIds->isEmpty()) {
            return;
        }

        DB::table('site_section_items')
            ->whereIn('site_section_id', $sectionIds)
            ->where('type', 'application')
            ->orderBy('id')
            ->eachById(function ($item) {
                $data = json_decode($item->data ?? '{}', true);

                if (! is_array($data)) {
                    $data = [];
                }

                if (! isset($data['resource_type'])) {
                    $legacyType = mb_strtolower((string) ($data['type'] ?? ''));
                    $isApplication = str_contains($legacyType, 'aplic') || str_contains($legacyType, 'appl');
                    $data['resource_type'] = $isApplication ? 'app' : 'web';
                }

                $data['play_store_url'] = $data['play_store_url'] ?? '';
                $data['download_url'] = $data['download_url'] ?? '';
                $data['download_name'] = $data['download_name'] ?? '';

                DB::table('site_section_items')
                    ->where('id', $item->id)
                    ->update([
                        'data' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        'updated_at' => now(),
                    ]);
            });
    }

    public function down(): void
    {
        $sectionIds = DB::table('site_sections')
            ->where('key', 'applications')
            ->pluck('id');

        if ($sectionIds->isEmpty()) {
            return;
        }

        DB::table('site_section_items')
            ->whereIn('site_section_id', $sectionIds)
            ->where('type', 'application')
            ->orderBy('id')
            ->eachById(function ($item) {
                $data = json_decode($item->data ?? '{}', true);

                if (! is_array($data)) {
                    return;
                }

                unset(
                    $data['resource_type'],
                    $data['play_store_url'],
                    $data['download_url'],
                    $data['download_name']
                );

                DB::table('site_section_items')
                    ->where('id', $item->id)
                    ->update([
                        'data' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        'updated_at' => now(),
                    ]);
            });
    }
};
