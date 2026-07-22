<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! DB::table('site_pages')->where('slug', 'encomienda')->exists()) {
            $this->seedEncomiendaPage();
        }
    }

    public function down(): void
    {
        $pageId = DB::table('site_pages')->where('slug', 'encomienda')->value('id');

        if (! $pageId) {
            return;
        }

        DB::table('site_section_items')->whereIn('site_section_id', function ($query) use ($pageId) {
            $query->select('id')->from('site_sections')->where('site_page_id', $pageId);
        })->delete();

        DB::table('site_sections')->where('site_page_id', $pageId)->delete();
        DB::table('site_pages')->where('id', $pageId)->delete();
    }

    protected function seedEncomiendaPage(): void
    {
        $now = now();
        $home = DB::table('site_pages')->where('slug', 'home')->first();
        $homeHeader = $home ? DB::table('site_sections')->where('site_page_id', $home->id)->where('key', 'header')->first() : null;
        $homeFooter = $home ? DB::table('site_sections')->where('site_page_id', $home->id)->where('key', 'footer')->first() : null;

        $pageId = DB::table('site_pages')->insertGetId([
            'slug' => 'encomienda',
            'name' => 'Encomienda Postal',
            'meta_title' => 'Encomienda Postal | Correos de Bolivia',
            'meta_description' => 'Servicio de encomienda postal internacional con cobertura global, rastreo en tiempo real y guias claras para tu envio.',
            'theme' => $home->theme ?? json_encode([
                'logo_url' => 'https://correos.gob.bo/wp-content/uploads/2023/06/LOGO-19-2-26-B-scaled.png',
                'primary_color' => '#0d47b5',
                'secondary_color' => '#2a4268',
                'accent_color' => '#ffc61a',
            ]),
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $headerId = DB::table('site_sections')->insertGetId([
            'site_page_id' => $pageId,
            'key' => 'header',
            'name' => 'Encabezado',
            'type' => 'header',
            'settings' => $homeHeader->settings ?? json_encode([]),
            'sort_order' => 0,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        if ($homeHeader) {
            $headerItems = DB::table('site_section_items')->where('site_section_id', $homeHeader->id)->orderBy('sort_order')->get();
            foreach ($headerItems as $index => $item) {
                DB::table('site_section_items')->insert([
                    'site_section_id' => $headerId,
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

        DB::table('site_sections')->insert([
            [
                'site_page_id' => $pageId,
                'key' => 'encomienda_hero',
                'name' => 'Encomienda Hero',
                'type' => 'encomienda_hero',
                'settings' => json_encode([
                    'badge' => 'Correos de Bolivia · Encomienda Postal',
                    'title_line_one_white' => 'Tu mundo,',
                    'title_line_one_yellow' => 'en un paquete.',
                    'title_line_two_white' => 'Tu confianza,',
                    'title_line_two_yellow' => 'en nuestras manos.',
                    'subtitle' => 'Descubre la nueva Era de la Encomienda Postal de Correos de Bolivia: Segura, Global y 100% Rastreable.',
                    'primary_button_label' => 'Enviar ahora',
                    'primary_button_url' => '#encomienda-cta',
                    'secondary_button_label' => 'Rastrear envio',
                    'secondary_button_url' => '#encomienda-faq',
                    'visual_icon' => 'package',
                    'visual_image' => '',
                ]),
                'sort_order' => 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'site_page_id' => $pageId,
                'key' => 'encomienda_intro',
                'name' => 'Encomienda Intro',
                'type' => 'encomienda_intro',
                'settings' => json_encode([
                    'title' => 'Nos renovamos para llevar tus suenos mas lejos.',
                    'paragraph_one' => 'En Correos de Bolivia entendemos que una encomienda es mucho mas que mercancia; es un regalo para un ser querido, el impulso para tu negocio o el inicio de una nueva oportunidad.',
                    'paragraph_two' => 'Por eso, en este relanzamiento, hemos digitalizado y optimizado nuestro Servicio de Encomienda Postal. Combinamos nuestra esencia historica con tecnologia de vanguardia para ofrecerte un tratamiento oportuno, seguro y adaptado a los tiempos que corren.',
                    'quote' => '"Tu encomienda, nuestro compromiso seguro y confiable."',
                    'image' => '',
                    'visual_icon' => 'package',
                    'badge_label' => 'Destinos',
                    'badge_value' => '192',
                    'badge_suffix' => 'paises',
                ]),
                'sort_order' => 2,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'site_page_id' => $pageId,
                'key' => 'encomienda_features',
                'name' => 'Encomienda Features',
                'type' => 'encomienda_features',
                'settings' => json_encode([
                    'title' => '¡Transformamos el mundo en tu vecindario!',
                    'subtitle' => 'Envia regalos o mercancias a cualquier rincon del planeta con total tranquilidad.',
                ]),
                'sort_order' => 3,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'site_page_id' => $pageId,
                'key' => 'encomienda_faq',
                'name' => 'Encomienda FAQ',
                'type' => 'encomienda_faq',
                'settings' => json_encode([
                    'title' => 'Todo lo que necesitas saber para tu envio perfecto.',
                    'subtitle' => 'Prepara tu Encomienda Internacional con estas guias simples:',
                    'tip_text' => 'Tip: Nuestros expertos en embalaje pueden asesorarte en agencias para asegurar que tu encomienda cumpla todos los requisitos internacionales y llegue en perfectas condiciones.',
                ]),
                'sort_order' => 4,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'site_page_id' => $pageId,
                'key' => 'encomienda_cta',
                'name' => 'Encomienda CTA',
                'type' => 'encomienda_cta',
                'settings' => json_encode([
                    'title' => '¿Listo para enviar? El mundo te espera.',
                    'subtitle' => 'Unete a la nueva experiencia logistica de Bolivia. Mas rapida, mas digital, mas tuya.',
                    'button_one_label' => 'Visitanos en Agencias',
                    'button_one_url' => '#site-footer',
                    'button_two_label' => 'Rastrea YA',
                    'button_two_url' => '#encomienda-faq',
                    'button_three_label' => 'Contactanos',
                    'button_three_url' => '#site-footer',
                    'footnote' => 'Correos de Bolivia: Evolucionamos para conectarte.',
                    'watermark_text' => 'CORREOS',
                ]),
                'sort_order' => 5,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'site_page_id' => $pageId,
                'key' => 'footer',
                'name' => 'Pie de pagina',
                'type' => 'footer',
                'settings' => $homeFooter->settings ?? json_encode([]),
                'sort_order' => 6,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $introId = DB::table('site_sections')->where('site_page_id', $pageId)->where('key', 'encomienda_intro')->value('id');
        $featuresId = DB::table('site_sections')->where('site_page_id', $pageId)->where('key', 'encomienda_features')->value('id');
        $faqId = DB::table('site_sections')->where('site_page_id', $pageId)->where('key', 'encomienda_faq')->value('id');
        $ctaId = DB::table('site_sections')->where('site_page_id', $pageId)->where('key', 'encomienda_cta')->value('id');

        foreach ([
            ['8-14', 'Dias estimados'],
            ['20 kg', 'Peso maximo'],
            ['100%', 'Rastreable'],
        ] as $index => $item) {
            DB::table('site_section_items')->insert([
                'site_section_id' => $introId,
                'name' => $item[1],
                'type' => 'encomienda_stat',
                'data' => json_encode(['value' => $item[0], 'label' => $item[1]]),
                'sort_order' => $index,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach ([
            ['globe', 'Cobertura global sin rival', 'Llegamos a donde otros no llegan. Alianza estrategica con la UPU (Union Postal Universal), presencia en 192 destinos asociados a la ONU.'],
            ['clock', 'Tiempos que compiten', 'Eficiencia en cada kilometro. Llegada a destino estimada en 8 a 14 dias (desde salida de Bolivia hacia el exterior).'],
            ['pin', 'Rastreo digital en tiempo real', 'El control lo tienes tu. Sistema de rastreo inteligente desde el origen hasta la entrega al destinatario final.'],
            ['shield', 'Seguridad garantizada', 'Tratamiento oportuno. Embalaje seguro y manejo eficiente para que todo llegue en perfectas condiciones.'],
        ] as $index => $item) {
            DB::table('site_section_items')->insert([
                'site_section_id' => $featuresId,
                'name' => $item[1],
                'type' => 'encomienda_feature',
                'data' => json_encode(['icon' => $item[0], 'title' => $item[1], 'text' => $item[2]]),
                'sort_order' => $index,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach ([
            ['package', '¿Que puedes enviar?', 'Puedes enviar regalos, documentos, textiles, productos comerciales y articulos permitidos por normativa internacional.'],
            ['bag', '¿Cuanto puede pesar?', 'Cada encomienda puede alcanzar hasta 20 kg, respetando la politica del destino y el tipo de contenido.'],
            ['ruler', '¿Que tamano puede tener?', 'El paquete debe ajustarse a las dimensiones aceptadas por el servicio y a las reglas del pais receptor.'],
        ] as $index => $item) {
            DB::table('site_section_items')->insert([
                'site_section_id' => $faqId,
                'name' => $item[1],
                'type' => 'encomienda_faq_item',
                'data' => json_encode(['icon' => $item[0], 'title' => $item[1], 'text' => $item[2]]),
                'sort_order' => $index,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach ([
            ['phone', 'Linea Gratuita: 0800-10-5050'],
            ['message', 'WhatsApp disponible'],
            ['pin', 'Av. Mariscal Santa Cruz 1278, La Paz'],
        ] as $index => $item) {
            DB::table('site_section_items')->insert([
                'site_section_id' => $ctaId,
                'name' => $item[1],
                'type' => 'encomienda_contact_chip',
                'data' => json_encode(['icon' => $item[0], 'text' => $item[1]]),
                'sort_order' => $index,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        if ($homeFooter) {
            $footerId = DB::table('site_sections')->where('site_page_id', $pageId)->where('key', 'footer')->value('id');
            $footerItems = DB::table('site_section_items')->where('site_section_id', $homeFooter->id)->orderBy('sort_order')->get();
            foreach ($footerItems as $index => $item) {
                DB::table('site_section_items')->insert([
                    'site_section_id' => $footerId,
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
    }
};
