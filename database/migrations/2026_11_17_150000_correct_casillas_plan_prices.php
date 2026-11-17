<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $prices = [
        [35, 126, 210],
        [49, 175, 294],
        [70, 250, 420],
        [84, 300, 504],
    ];

    public function up(): void
    {
        $this->updatePrices($this->prices);
    }

    public function down(): void
    {
        $this->updatePrices([
            [35, 210, 420],
            [49, 294, 588],
            [70, 420, 840],
            [84, 504, 1008],
        ]);
    }

    private function updatePrices(array $prices): void
    {
        $pageId = DB::table('site_pages')->where('slug', 'casillas')->value('id');
        if (! $pageId) {
            return;
        }

        $sectionId = DB::table('site_sections')
            ->where('site_page_id', $pageId)
            ->where('key', 'casillas_sizes')
            ->value('id');
        if (! $sectionId) {
            return;
        }

        DB::transaction(function () use ($sectionId, $prices): void {
            $items = DB::table('site_section_items')
                ->where('site_section_id', $sectionId)
                ->where('type', 'casillas_size_card')
                ->orderBy('sort_order')
                ->get();

            foreach ($prices as $index => [$monthly, $semiannual, $annual]) {
                $item = $items->get($index);
                if (! $item) {
                    continue;
                }

                $data = is_array($item->data) ? $item->data : json_decode((string) $item->data, true);
                $data = is_array($data) ? $data : [];
                $data['monthly_price'] = $monthly;
                $data['semiannual_price'] = $semiannual;
                $data['annual_price'] = $annual;

                DB::table('site_section_items')->where('id', $item->id)->update([
                    'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
                    'updated_at' => now(),
                ]);
            }
        });
    }
};
