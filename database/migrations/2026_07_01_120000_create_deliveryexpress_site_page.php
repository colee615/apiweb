<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! DB::table('site_pages')->where('slug', 'deliveryexpress')->exists()) {
            $this->seedDeliveryExpressPage();
        }
    }

    public function down(): void
    {
        $pageId = DB::table('site_pages')->where('slug', 'deliveryexpress')->value('id');

        if (! $pageId) {
            return;
        }

        DB::table('site_section_items')->whereIn('site_section_id', function ($query) use ($pageId) {
            $query->select('id')->from('site_sections')->where('site_page_id', $pageId);
        })->delete();

        DB::table('site_sections')->where('site_page_id', $pageId)->delete();
        DB::table('site_pages')->where('id', $pageId)->delete();
    }

    protected function seedDeliveryExpressPage(): void
    {
        $now = now();
        $home = DB::table('site_pages')->where('slug', 'home')->first();
        $homeHeader = $home ? DB::table('site_sections')->where('site_page_id', $home->id)->where('key', 'header')->first() : null;
        $homeFooter = $home ? DB::table('site_sections')->where('site_page_id', $home->id)->where('key', 'footer')->first() : null;

        $pageId = DB::table('site_pages')->insertGetId([
            'slug' => 'deliveryexpress',
            'name' => 'Delivery Express',
            'meta_title' => 'Delivery Express | Correos de Bolivia',
            'meta_description' => 'Delivery Express by Correos. Entrega a domicilio, trazabilidad y soluciones para empresas en Bolivia.',
            'theme' => $home->theme ?? json_encode([
                'logo_url' => 'https://correos.gob.bo/wp-content/uploads/2023/06/LOGO-19-2-26-B-scaled.png',
                'primary_color' => '#20539a',
                'secondary_color' => '#102542',
                'accent_color' => '#f3b53f',
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
            'settings' => $homeHeader->settings ?? json_encode([
                'help_label' => 'Ayuda / Contacto',
                'login_label' => 'Iniciar sesion',
                'search_placeholder' => 'Buscar...',
                'language_primary' => 'Espanol',
                'language_secondary' => 'English',
                'accessibility_label' => 'Accesibilidad',
            ]),
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
                'key' => 'delivery_hero',
                'name' => 'Delivery Hero',
                'type' => 'delivery_hero',
                'settings' => json_encode([
                    'title_primary' => 'DELIVERY',
                    'title_secondary' => 'EXPRESS',
                    'eyebrow' => 'By CORREOS',
                    'subtitle' => 'La evolucion de la entrega a domicilio en Bolivia',
                    'primary_button_label' => 'Solicitar servicio',
                    'primary_button_url' => '#',
                    'secondary_button_label' => 'Descargar app',
                    'secondary_button_url' => '#deliveryexpress-cta',
                    'visual_icon' => 'smartphone',
                    'floating_icon' => 'smartphone',
                    'visual_image' => '',
                ]),
                'sort_order' => 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'site_page_id' => $pageId,
                'key' => 'delivery_intro',
                'name' => 'Delivery Intro',
                'type' => 'delivery_intro',
                'settings' => json_encode([
                    'title' => 'Tu negocio no se detiene. Nosotros lo hacemos volar.',
                    'highlight_text' => 'Presentamos Delivery Express by Correos: La evolucion de la entrega a domicilio que conecta tu talento con la puerta de tu cliente.',
                    'paragraph_one' => 'En un mundo donde el tiempo es oro y la eficiencia es la clave del exito, hemos disenado un servicio que entiende las necesidades de los emprendedores, comerciantes y empresas bolivianas.',
                    'paragraph_two' => '',
                    'closing_text' => 'Correos de Bolivia: Mas digital, mas cerca, mas tuyo.',
                    'chip_one' => 'Rapido',
                    'chip_two' => 'Confiable',
                    'chip_three' => 'Tecnologico',
                    'visual_icon' => 'truck',
                    'visual_badge' => 'Entrega urbana',
                    'visual_image' => '',
                ]),
                'sort_order' => 2,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'site_page_id' => $pageId,
                'key' => 'delivery_advantages',
                'name' => 'Delivery Ventajas',
                'type' => 'delivery_card_grid',
                'settings' => json_encode([
                    'title' => 'El aliado estrategico que tu crecimiento necesitaba.',
                    'subtitle' => 'En un mercado dinamico y competitivo, tu exito depende de contar con socios que entiendan tus desafios. Delivery Express by Correos no es solo un servicio de entrega: es tu ventaja competitiva.',
                ]),
                'sort_order' => 3,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'site_page_id' => $pageId,
                'key' => 'delivery_process',
                'name' => 'Delivery Proceso',
                'type' => 'delivery_process',
                'settings' => json_encode([
                    'title' => 'Logistica inteligente en la palma de tu mano.',
                    'subtitle' => 'Un proceso simple en 3 pasos que revoluciona tu forma de hacer negocios',
                    'feature_title' => 'Trazabilidad en tiempo real',
                    'feature_text' => 'Sigue cada paso de tu envio desde tu smartphone. Recibe notificaciones instantaneas y manten a tus clientes informados en todo momento.',
                    'bullet_one' => 'Mapa interactivo con ubicacion en vivo',
                    'bullet_two' => 'Linea de tiempo detallada del recorrido',
                    'bullet_three' => 'Confirmacion digital con firma electronica',
                    'tracker_title' => 'Seguimiento de envio',
                    'tracker_status' => '3',
                    'tracker_stage' => 'En transito',
                    'timeline_one_title' => 'Recogida confirmada',
                    'timeline_one_time' => 'Hoy, 10:30 AM',
                    'timeline_two_title' => 'En camino',
                    'timeline_two_time' => 'Hoy, 11:15 AM',
                    'timeline_three_title' => 'Entrega programada',
                    'timeline_three_time' => 'Hoy, 14:00 PM',
                ]),
                'sort_order' => 4,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'site_page_id' => $pageId,
                'key' => 'delivery_info',
                'name' => 'Delivery Informacion',
                'type' => 'delivery_card_grid',
                'settings' => json_encode([
                    'title' => 'Informacion importante sobre el servicio',
                    'subtitle' => 'Transparencia y claridad para que tomes la mejor decision',
                    'footnote' => 'Necesitas enviar paquetes de mayor tamano? Contactanos para soluciones personalizadas.',
                ]),
                'sort_order' => 5,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'site_page_id' => $pageId,
                'key' => 'delivery_cta',
                'name' => 'Delivery CTA',
                'type' => 'delivery_cta',
                'settings' => json_encode([
                    'title' => 'Listo para expandir tus ventas?',
                    'subtitle' => 'Unete a cientos de empresas y emprendedores que ya confian en Delivery Express by Correos',
                    'trust_text' => 'Mas de 500 empresas ya confian en nosotros. Que esperas para unirte?',
                    'app_title' => 'Descarga la App',
                    'app_note' => 'Disponible para iOS y Android',
                    'app_store_label' => 'Descarga en App Store',
                    'app_store_url' => '#',
                    'app_store_badge' => '',
                    'play_store_label' => 'Disponible en Google Play',
                    'play_store_url' => '#',
                    'play_store_badge' => '',
                    'register_title' => 'Registrate como Empresa',
                    'register_text' => 'Escanea para acceder al formulario de registro empresarial',
                    'register_qr_image' => '',
                    'contact_title' => 'Contactanos',
                    'contact_whatsapp_label' => 'Escribenos por WhatsApp',
                    'contact_whatsapp_url' => '#',
                    'contact_web_label' => 'Visita nuestra Pagina Web',
                    'contact_web_url' => 'https://correos.gob.bo',
                    'contact_phone_label' => 'Llamanos al 800-12-3456',
                    'contact_phone_url' => 'tel:800123456',
                ]),
                'sort_order' => 6,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $advantagesId = DB::table('site_sections')->where('site_page_id', $pageId)->where('key', 'delivery_advantages')->value('id');
        $processId = DB::table('site_sections')->where('site_page_id', $pageId)->where('key', 'delivery_process')->value('id');
        $infoId = DB::table('site_sections')->where('site_page_id', $pageId)->where('key', 'delivery_info')->value('id');

        foreach ([
            ['Optimizacion Total', 'bolt', 'Reducimos tus tiempos de espera y maximizamos la eficiencia de tus entregas. Cada minuto cuenta y nosotros lo sabemos. Con rutas inteligentes y tecnologia de punta, tu producto llega mas rapido.', ''],
            ['Confianza Institucional', 'shield', 'Tu marca viaja protegida por la experiencia de decadas en el sector postal. Somos mas que una empresa de envios: somos una institucion que respalda tu reputacion con cada entrega.', ''],
            ['Escalabilidad', 'trend-up', 'No importa si envias un producto al dia o cien. Nuestro sistema se adapta a tu ritmo de crecimiento sin complicaciones, garantizando la misma calidad en cada envio.', ''],
        ] as $index => $item) {
            DB::table('site_section_items')->insert([
                'site_section_id' => $advantagesId,
                'name' => $item[0],
                'type' => 'delivery_advantage',
                'data' => json_encode([
                    'title' => $item[0],
                    'icon' => $item[1],
                    'text' => $item[2],
                    'badge' => $item[3],
                ]),
                'sort_order' => $index,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach ([
            ['Solicita', 'smartphone', 'Agenda tu recogida desde nuestra app o web con solo unos clics. Facil, rapido y sin complicaciones.', '01'],
            ['Prepara', 'package', 'Empaqueta tu producto de forma segura. Nosotros nos encargamos del resto con el mismo cuidado que tu.', '02'],
            ['Entrega', 'check-circle', 'Tu cliente recibe su pedido y tu recibes la confirmacion digital al instante. Todo bajo control.', '03'],
        ] as $index => $item) {
            DB::table('site_section_items')->insert([
                'site_section_id' => $processId,
                'name' => $item[0],
                'type' => 'delivery_step',
                'data' => json_encode([
                    'title' => $item[0],
                    'icon' => $item[1],
                    'text' => $item[2],
                    'badge' => $item[3],
                ]),
                'sort_order' => $index,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach ([
            ['Dimensiones', 'package', 'Paquetes de hasta 50cm x 40cm x 30cm y peso maximo de 20kg. Ideal para la mayoria de productos comerciales.', ''],
            ['Cobertura', 'pin', 'Servicio disponible en todas las principales ciudades de Bolivia y en expansion constante a nuevas zonas.', ''],
            ['Seguridad', 'shield', 'Paquetes asegurados durante todo el trayecto. Compensacion disponible segun terminos y condiciones.', ''],
        ] as $index => $item) {
            DB::table('site_section_items')->insert([
                'site_section_id' => $infoId,
                'name' => $item[0],
                'type' => 'delivery_info_card',
                'data' => json_encode([
                    'title' => $item[0],
                    'icon' => $item[1],
                    'text' => $item[2],
                    'badge' => $item[3],
                ]),
                'sort_order' => $index,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $footerId = DB::table('site_sections')->insertGetId([
            'site_page_id' => $pageId,
            'key' => 'footer',
            'name' => 'Pie de pagina',
            'type' => 'footer',
            'settings' => $homeFooter->settings ?? json_encode([
                'help_title' => 'Ayuda',
                'company_title' => 'Empresa',
                'contact_title' => 'Contacto',
                'social_title' => 'Siguenos',
                'social_text' => 'Mantente conectado con nosotros en redes sociales',
                'address' => 'Av. Mariscal Santa Cruz 1278|La Paz, Bolivia',
                'phone' => '+591 2 2356789|0800-10-5050 (Gratis)',
                'email' => 'info@correos.bo',
                'copyright' => '© 2026 Correos de Bolivia. Todos los derechos reservados.',
                'legal_text' => 'Empresa Publica Nacional Estrategica',
            ]),
            'sort_order' => 7,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        if ($homeFooter) {
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
