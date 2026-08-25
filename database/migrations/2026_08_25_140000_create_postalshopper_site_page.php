<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! DB::table('site_pages')->where('slug', 'postalshopper')->exists()) {
            $this->seedPostalshopperPage();
        }
    }

    public function down(): void
    {
        $pageId = DB::table('site_pages')->where('slug', 'postalshopper')->value('id');

        if (! $pageId) {
            return;
        }

        DB::table('site_section_items')->whereIn('site_section_id', function ($query) use ($pageId) {
            $query->select('id')->from('site_sections')->where('site_page_id', $pageId);
        })->delete();

        DB::table('site_sections')->where('site_page_id', $pageId)->delete();
        DB::table('site_pages')->where('id', $pageId)->delete();
    }

    protected function seedPostalshopperPage(): void
    {
        $now = now();
        $home = DB::table('site_pages')->where('slug', 'home')->first();
        $homeHeader = $home ? DB::table('site_sections')->where('site_page_id', $home->id)->where('key', 'header')->first() : null;
        $homeFooter = $home ? DB::table('site_sections')->where('site_page_id', $home->id)->where('key', 'footer')->first() : null;

        $pageId = DB::table('site_pages')->insertGetId([
            'slug' => 'postalshopper',
            'name' => 'Postal Shopper',
            'meta_title' => 'Postal Shopper | Correos de Bolivia',
            'meta_description' => 'Compra en Estados Unidos y recibe en Bolivia con Postal Shopper de Correos de Bolivia.',
            'theme' => $home->theme ?? json_encode([
                'logo_url' => 'https://correos.gob.bo/wp-content/uploads/2023/06/LOGO-19-2-26-B-scaled.png',
                'primary_color' => '#0d47b5',
                'secondary_color' => '#2a4268',
                'accent_color' => '#ffcc18',
            ], JSON_UNESCAPED_UNICODE),
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $headerId = DB::table('site_sections')->insertGetId([
            'site_page_id' => $pageId,
            'key' => 'header',
            'name' => 'Encabezado',
            'type' => 'header',
            'settings' => $homeHeader->settings ?? json_encode([], JSON_UNESCAPED_UNICODE),
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
                'key' => 'postalshopper_hero',
                'name' => 'Postal Shopper Hero',
                'type' => 'postalshopper_hero',
                'settings' => json_encode([
                    'badge' => 'Correos de Bolivia - Postal Shopper',
                    'title_line_one_white' => 'Tus tiendas favoritas',
                    'title_line_one_yellow' => 'en EE.UU.,',
                    'title_line_two_white' => 'ahora a un clic de tu',
                    'title_line_two_yellow' => 'casa.',
                    'title_line_three_yellow' => '',
                    'lead_text' => 'Descubre Postal Shopper: Tu casilla virtual en Estados Unidos.',
                    'subtitle' => 'Compra en las mejores tiendas del mundo y recibelo en Bolivia con la garantia y respaldo de Correos.',
                    'primary_button_label' => 'Como funciona',
                    'primary_button_url' => '#postalshopper-steps',
                    'secondary_button_label' => 'Registrarme gratis',
                    'secondary_button_url' => '/contacto',
                    'background_image' => '',
                    'map_origin_country' => 'ESTADOS UNIDOS',
                    'map_origin_city' => 'Miami',
                    'map_destination_country' => 'BOLIVIA',
                    'map_destination_city' => 'Bolivia',
                    'map_caption' => 'Red de envios Miami - Bolivia',
                ], JSON_UNESCAPED_UNICODE),
                'sort_order' => 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'site_page_id' => $pageId,
                'key' => 'postalshopper_intro',
                'name' => 'Postal Shopper Intro',
                'type' => 'postalshopper_intro',
                'settings' => json_encode([
                    'eyebrow' => 'El mundo en tu carrito',
                    'title' => 'Rompemos las fronteras para tus compras online.',
                    'paragraph_one' => 'Viste una oferta increible, tecnologia de punta o ropa exclusiva, pero la tienda no hace envios internacionales? Con el nuevo servicio Postal Shopper, eso es cosa del pasado.',
                    'paragraph_two' => 'Al registrarte, te otorgamos una direccion fisica unica en Estados Unidos, funcionando exactamente como si vivieras alla. Tu solo dedicate a cazar las mejores ofertas en miles de tiendas en linea. Nosotros nos encargamos de recibir tus paquetes, resguardarlos y hacer que crucen el continente hasta llegar de manera segura a tus manos.',
                ], JSON_UNESCAPED_UNICODE),
                'sort_order' => 2,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'site_page_id' => $pageId,
                'key' => 'postalshopper_steps',
                'name' => 'Postal Shopper Pasos',
                'type' => 'postalshopper_steps',
                'settings' => json_encode([
                    'title' => 'Tu puente directo entre Miami y Bolivia.',
                    'subtitle' => 'Hemos simplificado el proceso de importacion para que tu unica preocupacion sea decidir que comprar hoy.',
                ], JSON_UNESCAPED_UNICODE),
                'sort_order' => 3,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'site_page_id' => $pageId,
                'key' => 'postalshopper_benefits',
                'name' => 'Postal Shopper Beneficios',
                'type' => 'postalshopper_benefits',
                'settings' => json_encode([
                    'title' => 'Compras inteligentes, entregas de vanguardia.',
                    'subtitle' => 'Tu experiencia de compra internacional respaldada por la red logistica de Correos.',
                    'banner_title' => 'Novedad: Correos Smart Lockers',
                    'banner_text' => 'Retira tu paquete en cualquier momento del dia con tecnologia de casilleros inteligentes disponibles en puntos estrategicos de las principales ciudades de Bolivia.',
                    'banner_button_label' => 'Saber mas',
                    'banner_button_url' => '/casillas',
                ], JSON_UNESCAPED_UNICODE),
                'sort_order' => 4,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'site_page_id' => $pageId,
                'key' => 'footer',
                'name' => 'Pie de pagina',
                'type' => 'footer',
                'settings' => $homeFooter->settings ?? json_encode([], JSON_UNESCAPED_UNICODE),
                'sort_order' => 5,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $heroId = DB::table('site_sections')->where('site_page_id', $pageId)->where('key', 'postalshopper_hero')->value('id');
        $introId = DB::table('site_sections')->where('site_page_id', $pageId)->where('key', 'postalshopper_intro')->value('id');
        $stepsId = DB::table('site_sections')->where('site_page_id', $pageId)->where('key', 'postalshopper_steps')->value('id');
        $benefitsId = DB::table('site_sections')->where('site_page_id', $pageId)->where('key', 'postalshopper_benefits')->value('id');

        foreach ([
            ['Gratis', 'Registro'],
            ['24/7', 'Acceso'],
            ['100%', 'Seguro'],
        ] as $index => $item) {
            DB::table('site_section_items')->insert([
                'site_section_id' => $heroId,
                'name' => $item[0],
                'type' => 'postalshopper_hero_stat',
                'data' => json_encode([
                    'value' => $item[0],
                    'label' => $item[1],
                ], JSON_UNESCAPED_UNICODE),
                'sort_order' => $index,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach (['Amazon', 'eBay', 'Walmart', 'Shein', 'Nike', 'Apple', '+ miles mas'] as $index => $item) {
            DB::table('site_section_items')->insert([
                'site_section_id' => $introId,
                'name' => $item,
                'type' => 'postalshopper_market_chip',
                'data' => json_encode(['label' => $item], JSON_UNESCAPED_UNICODE),
                'sort_order' => $index,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach ([
            ['01', 'pin', 'Tu Direccion Americana', 'Registrate y obten de inmediato tu propia casilla virtual en EE.UU. Una direccion real en Miami lista para recibir tus compras.'],
            ['02', 'cart', 'Compra sin Limites', 'Ingresa a Amazon, eBay, Walmart o cualquier tienda online y utiliza tu nueva direccion como destino de envio (Shipping Address).'],
            ['03', 'package', 'Consolidacion y Ahorro', 'Tus pedidos llegaran a tu casilla y permaneceran almacenados de forma segura. Agrupa todas tus compras en un solo envio y optimiza costos.'],
            ['04', 'plane', 'El Vuelo a Casa', 'Una vez que solicitas el envio, Correos de Bolivia se encarga de todo el traslado internacional y los procesos aduaneros correspondientes.'],
        ] as $index => $item) {
            DB::table('site_section_items')->insert([
                'site_section_id' => $stepsId,
                'name' => $item[2],
                'type' => 'postalshopper_step_card',
                'data' => json_encode([
                    'step' => $item[0],
                    'icon' => $item[1],
                    'title' => $item[2],
                    'text' => $item[3],
                ], JSON_UNESCAPED_UNICODE),
                'sort_order' => $index,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach ([
            ['globe', 'Acceso Global y Mejores Precios', 'Catalogos infinitos y descuentos de temporada exclusivos de EE.UU.: Black Friday, Cyber Monday y Prime Day.'],
            ['settings', 'Gestion a tu Medida', 'Tu decides cuando y como enviar tus paquetes a Bolivia. Almacenamiento gratuito por 30 dias en tu casilla.'],
            ['shield', 'Seguridad Garantizada', 'Recepcion, almacenaje y traslado bajo estrictos controles de calidad e integridad en cada etapa.'],
            ['spark', 'Innovacion en la Entrega Final', 'Recibelo en casa via Delivery Express, o retiralo de forma autonoma en nuestros Smart Lockers Correos Smart.'],
        ] as $index => $item) {
            DB::table('site_section_items')->insert([
                'site_section_id' => $benefitsId,
                'name' => $item[1],
                'type' => 'postalshopper_benefit_card',
                'data' => json_encode([
                    'icon' => $item[0],
                    'title' => $item[1],
                    'text' => $item[2],
                ], JSON_UNESCAPED_UNICODE),
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
