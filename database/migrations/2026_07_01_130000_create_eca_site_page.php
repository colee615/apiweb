<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! DB::table('site_pages')->where('slug', 'eca')->exists()) {
            $this->seedEcaPage();
        }
    }

    public function down(): void
    {
        $pageId = DB::table('site_pages')->where('slug', 'eca')->value('id');

        if (! $pageId) {
            return;
        }

        DB::table('site_section_items')->whereIn('site_section_id', function ($query) use ($pageId) {
            $query->select('id')->from('site_sections')->where('site_page_id', $pageId);
        })->delete();

        DB::table('site_sections')->where('site_page_id', $pageId)->delete();
        DB::table('site_pages')->where('id', $pageId)->delete();
    }

    protected function seedEcaPage(): void
    {
        $now = now();
        $home = DB::table('site_pages')->where('slug', 'home')->first();
        $homeHeader = $home ? DB::table('site_sections')->where('site_page_id', $home->id)->where('key', 'header')->first() : null;
        $homeFooter = $home ? DB::table('site_sections')->where('site_page_id', $home->id)->where('key', 'footer')->first() : null;

        $pageId = DB::table('site_pages')->insertGetId([
            'slug' => 'eca',
            'name' => 'Servicio ECA',
            'meta_title' => 'Servicio ECA | Correos de Bolivia',
            'meta_description' => 'Envios de correspondencia agrupada para empresas e instituciones. Tarifas preferenciales, cobertura y soluciones corporativas.',
            'theme' => $home->theme ?? json_encode([
                'logo_url' => 'https://correos.gob.bo/wp-content/uploads/2023/06/LOGO-19-2-26-B-scaled.png',
                'primary_color' => '#20539a',
                'secondary_color' => '#102542',
                'accent_color' => '#fecc36',
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
                'key' => 'eca_hero',
                'name' => 'ECA Hero',
                'type' => 'eca_hero',
                'settings' => json_encode([
                    'badge' => 'Servicio ECA · Envios de Correspondencia Agrupada',
                    'title_line_one_blue' => 'Grandes Envios.',
                    'title_line_one_yellow' => 'Grandes',
                    'title_line_two_yellow' => 'Ahorros.',
                    'title_line_three_blue' => 'Grandes Soluciones.',
                    'subtitle' => 'Descubre el Servicio ECA (Correspondencia Agrupada) de Correos de Bolivia: La logistica inteligente que tu empresa necesita hoy.',
                    'primary_button_label' => 'Ver tarifas preferenciales',
                    'primary_button_url' => '#eca-rates',
                    'secondary_button_label' => 'Cotizar ahora',
                    'secondary_button_url' => '#eca-cta',
                    'visual_icon' => 'briefcase',
                    'visual_image' => '',
                ]),
                'sort_order' => 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'site_page_id' => $pageId,
                'key' => 'eca_intro',
                'name' => 'ECA Intro',
                'type' => 'eca_intro',
                'settings' => json_encode([
                    'eyebrow' => 'El problema y la solucion',
                    'title' => 'Optimiza tu comunicacion masiva con un aliado estrategico.',
                    'paragraph_one' => 'En la era digital, la correspondencia fisica sigue siendo vital para la formalidad y el alcance de tu organizacion. Sin embargo, sabemos que gestionar grandes volumenes puede ser costoso y complejo.',
                    'paragraph_two' => 'El Servicio ECA esta disenado especificamente para empresas e instituciones que buscan optimizar sus envios masivos con costos reducidos y una operacion eficiente. Evolucionamos para ofrecerte un servicio mas agil, confiable y economico.',
                ]),
                'sort_order' => 2,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'site_page_id' => $pageId,
                'key' => 'eca_rates',
                'name' => 'ECA Tarifas',
                'type' => 'eca_rates',
                'settings' => json_encode([
                    'title' => 'Tarifas Preferenciales: Premia tu volumen de envio.',
                    'subtitle' => 'Califica como Gran Impositor y desbloquea tarifas corporativas exclusivas disenadas para el volumen de tu empresa.',
                    'note_title' => 'Tu eficiencia tiene recompensa.',
                    'note_text' => 'Si cumples los criterios de calificacion, accedes a nuestras tarifas preferenciales ECA. Si tu envio es menor al minimo, se aplicara la tarifa convencional de ventanilla. ¡Planifica y maximiza tu ahorro!',
                    'primary_button_label' => 'Solicitar cotizacion corporativa',
                    'primary_button_url' => '#eca-cta',
                ]),
                'sort_order' => 3,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'site_page_id' => $pageId,
                'key' => 'eca_coverage',
                'name' => 'ECA Cobertura',
                'type' => 'eca_coverage',
                'settings' => json_encode([
                    'title' => 'Cobertura total y tiempos competitivos.',
                    'subtitle' => 'Alcance nacional e internacional con la misma calidad de servicio.',
                    'note_title' => 'Nota sobre Dimensiones',
                    'note_text' => 'Si la longitud maxima de cualquiera de los lados del paquete excede los 1.50 metros, se aplicaran costos volumetricos adicionales. Consulta con nuestro equipo para optimizar tu embalaje y reducir costos.',
                ]),
                'sort_order' => 4,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'site_page_id' => $pageId,
                'key' => 'eca_solutions',
                'name' => 'ECA Soluciones',
                'type' => 'eca_solutions',
                'settings' => json_encode([
                    'title' => 'La solucion ideal para tus necesidades corporativas.',
                    'subtitle' => 'El Servicio ECA potencia la operacion de los sectores mas exigentes.',
                ]),
                'sort_order' => 5,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'site_page_id' => $pageId,
                'key' => 'eca_cta',
                'name' => 'ECA CTA',
                'type' => 'eca_cta',
                'settings' => json_encode([
                    'title' => '¿Listo para reducir costos y mejorar tu logistica?',
                    'text' => 'Nuestros ejecutivos corporativos estan listos para disenarte un plan a medida. Contactanos y descubre como el Servicio ECA puede transformar la gestion de correspondencia de tu organizacion.',
                    'phone_label' => 'Telefono corporativo',
                    'phone_value' => '+591 2 2356789 (Ext. Empresas)',
                    'email_label' => 'Email corporativo',
                    'email_value' => 'empresas@correos.gob.bo',
                    'address_label' => 'Oficina central',
                    'address_value' => 'Av. Mariscal Santa Cruz 1278, La Paz',
                    'footnote' => 'Correos de Bolivia: Evolucionamos con vos.',
                    'qr_title' => 'Cotizacion Corporativa Digital',
                    'qr_text' => 'Escanea para solicitar una cotizacion corporativa digital y conocer tarifas ECA',
                    'qr_image' => '',
                    'button_label' => 'Escribir al equipo B2B',
                    'button_url' => '#',
                ]),
                'sort_order' => 6,
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
                'sort_order' => 7,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $introId = DB::table('site_sections')->where('site_page_id', $pageId)->where('key', 'eca_intro')->value('id');
        $ratesId = DB::table('site_sections')->where('site_page_id', $pageId)->where('key', 'eca_rates')->value('id');
        $coverageId = DB::table('site_sections')->where('site_page_id', $pageId)->where('key', 'eca_coverage')->value('id');
        $solutionsId = DB::table('site_sections')->where('site_page_id', $pageId)->where('key', 'eca_solutions')->value('id');

        foreach ([
            ['Empresas', 'building'],
            ['Instituciones', 'file'],
            ['Finanzas', 'chart'],
            ['Marketing', 'megaphone'],
        ] as $index => $item) {
            DB::table('site_section_items')->insert([
                'site_section_id' => $introId,
                'name' => $item[0],
                'type' => 'eca_segment',
                'data' => json_encode(['title' => $item[0], 'icon' => $item[1]]),
                'sort_order' => $index,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach ([
            ['40', 'Unidades minimas', 'por imposicion (sobres o pequenos paquetes)'],
            ['100 gr', 'Peso maximo', 'por unidad para calificar a la tarifa preferencial'],
        ] as $index => $item) {
            DB::table('site_section_items')->insert([
                'site_section_id' => $ratesId,
                'name' => $item[1],
                'type' => 'eca_rate_stat',
                'data' => json_encode(['value' => $item[0], 'title' => $item[1], 'text' => $item[2]]),
                'sort_order' => $index,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach ([
            ['BO Alcance', 'Nacional', 'truck', 'Tiempo de entrega', '24 a 72 horas', 'Cobertura', '9 departamentos del pais', 'Peso por unidad', 'Desde 10 gr hasta 20 Kg'],
            ['Proyeccion', 'Internacional', 'plane', 'Tiempo de entrega', '7 a 14 dias habiles', 'Cobertura', '192 paises (Red UPU)', 'Peso por unidad', 'Desde 10 gr hasta 20 Kg'],
        ] as $index => $item) {
            DB::table('site_section_items')->insert([
                'site_section_id' => $coverageId,
                'name' => $item[1],
                'type' => 'eca_coverage_card',
                'data' => json_encode([
                    'eyebrow' => $item[0],
                    'title' => $item[1],
                    'icon' => $item[2],
                    'row_one_label' => $item[3],
                    'row_one_value' => $item[4],
                    'row_two_label' => $item[5],
                    'row_two_value' => $item[6],
                    'row_three_label' => $item[7],
                    'row_three_value' => $item[8],
                ]),
                'sort_order' => $index,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach ([
            ['Facturacion Masiva', 'file', 'Distribuye facturas fisicas a toda tu cartera de clientes de forma eficiente, puntual y con respaldo oficial.'],
            ['Estados de Cuenta', 'chart', 'Envios periodicos de extractos y estados financieros para bancos, cooperativas y entidades de credito.'],
            ['Publicidad y Marketing', 'megaphone', 'Campanas de mailing directo con alto impacto para estrategias de marketing B2C a escala nacional.'],
            ['Notificaciones Institucionales', 'building', 'Comunicados formales, notificaciones legales y convocatorias para entidades estatales y privadas.'],
        ] as $index => $item) {
            DB::table('site_section_items')->insert([
                'site_section_id' => $solutionsId,
                'name' => $item[0],
                'type' => 'eca_solution_card',
                'data' => json_encode(['title' => $item[0], 'icon' => $item[1], 'text' => $item[2], 'badge' => '']),
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
