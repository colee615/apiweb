<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $legacyPage = DB::table('site_pages')->where('slug', 'misaplicaicones')->first();
        $currentPage = DB::table('site_pages')->where('slug', 'misaplicaciones')->first();

        if ($legacyPage && ! $currentPage) {
            DB::table('site_pages')
                ->where('id', $legacyPage->id)
                ->update([
                    'slug' => 'misaplicaciones',
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        $currentPage = DB::table('site_pages')->where('slug', 'misaplicaciones')->first();
        $legacyPage = DB::table('site_pages')->where('slug', 'misaplicaicones')->first();

        if ($currentPage && ! $legacyPage) {
            DB::table('site_pages')
                ->where('id', $currentPage->id)
                ->update([
                    'slug' => 'misaplicaicones',
                    'updated_at' => now(),
                ]);
        }
    }
};
