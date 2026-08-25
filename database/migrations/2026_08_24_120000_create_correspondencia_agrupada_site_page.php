<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! DB::table('site_pages')->where('slug', 'correspondencia-agrupada')->exists()) {
            $this->seedCorrespondenciaAgrupadaPage();
        }
    }

    public function down(): void
    {
        $pageId = DB::table('site_pages')->where('slug', 'correspondencia-agrupada')->value('id');

        if (! $pageId) {
            return;
        }

        DB::table('site_section_items')->whereIn('site_section_id', function ($query) use ($pageId) {
            $query->select('id')->from('site_sections')->where('site_page_id', $pageId);
        })->delete();

        DB::table('site_sections')->where('site_page_id', $pageId)->delete();
        DB::table('site_pages')->where('id', $pageId)->delete();
    }

    protected function seedCorrespondenciaAgrupadaPage(): void
    {
        $source = DB::table('site_pages')->where('slug', 'eca')->first();

        if ($source) {
            $this->cloneFromEca($source);
            return;
        }

        $this->seedFallbackContent();
    }

    protected function cloneFromEca(object $source): void
    {
        $now = now();

        $pageId = DB::table('site_pages')->insertGetId([
            'slug' => 'correspondencia-agrupada',
            'name' => 'Correspondencia Agrupada',
            'meta_title' => 'Correspondencia Agrupada | Correos de Bolivia',
            'meta_description' => 'Servicio de correspondencia agrupada para empresas e instituciones. Tarifas preferenciales, cobertura nacional e internacional y soluciones corporativas.',
            'theme' => $source->theme,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $sourceSections = DB::table('site_sections')
            ->where('site_page_id', $source->id)
            ->orderBy('sort_order')
            ->get();

        foreach ($sourceSections as $section) {
            $settings = json_decode($section->settings ?? '[]', true) ?: [];

            if ($section->key === 'eca_hero') {
                $settings['badge'] = 'Servicio de Correspondencia Agrupada';
            }

            $newSectionId = DB::table('site_sections')->insertGetId([
                'site_page_id' => $pageId,
                'key' => $section->key,
                'name' => $section->name,
                'type' => $section->type,
                'settings' => json_encode($settings, JSON_UNESCAPED_UNICODE),
                'sort_order' => $section->sort_order,
                'is_active' => (bool) $section->is_active,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $items = DB::table('site_section_items')
                ->where('site_section_id', $section->id)
                ->orderBy('sort_order')
                ->get();

            foreach ($items as $item) {
                DB::table('site_section_items')->insert([
                    'site_section_id' => $newSectionId,
                    'name' => $item->name,
                    'type' => $item->type,
                    'data' => $item->data,
                    'sort_order' => $item->sort_order,
                    'is_active' => (bool) $item->is_active,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    protected function seedFallbackContent(): void
    {
        $now = now();
        $home = DB::table('site_pages')->where('slug', 'home')->first();
        $homeHeader = $home ? DB::table('site_sections')->where('site_page_id', $home->id)->where('key', 'header')->first() : null;
        $homeFooter = $home ? DB::table('site_sections')->where('site_page_id', $home->id)->where('key', 'footer')->first() : null;

        $pageId = DB::table('site_pages')->insertGetId([
            'slug' => 'correspondencia-agrupada',
            'name' => 'Correspondencia Agrupada',
            'meta_title' => 'Correspondencia Agrupada | Correos de Bolivia',
            'meta_description' => 'Servicio de correspondencia agrupada para empresas e instituciones. Tarifas preferenciales, cobertura nacional e internacional y soluciones corporativas.',
            'theme' => $home->theme ?? json_encode([
                'logo_url' => 'https://correos.gob.bo/wp-content/uploads/2023/06/LOGO-19-2-26-B-scaled.png',
                'primary_color' => '#20539a',
                'secondary_color' => '#102542',
                'accent_color' => '#fecc36',
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
                'key' => 'eca_hero',
                'name' => 'ECA Hero',
                'type' => 'eca_hero',
                'settings' => json_encode([
                    'badge' => 'Servicio de Correspondencia Agrupada',
                    'title_line_one_blue' => 'Grandes Envíos.',
                    'title_line_one_yellow' => 'Grandes',
                    'title_line_two_yellow' => 'Ahorros.',
                    'title_line_three_blue' => 'Grandes Soluciones.',
                    'subtitle' => 'Descubre Correspondencia Agrupada de Correos de Bolivia: la logística inteligente que tu empresa necesita hoy.',
                    'primary_button_label' => 'Ver tarifas preferenciales',
                    'primary_button_url' => '#correspondencia-rates',
                    'secondary_button_label' => 'Cotizar ahora',
                    'secondary_button_url' => '#correspondencia-cta',
                    'visual_icon' => 'briefcase',
                    'visual_image' => '',
                ], JSON_UNESCAPED_UNICODE),
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
                    'eyebrow' => 'El problema y la solución',
                    'title' => 'Optimiza tu comunicación masiva con un aliado estratégico.',
                    'paragraph_one' => 'En la era digital, la correspondencia física sigue siendo vital para la formalidad y el alcance de tu organización. Sin embargo, sabemos que gestionar grandes volúmenes puede ser costoso y complejo.',
                    'paragraph_two' => 'Correspondencia Agrupada está diseñada específicamente para empresas e instituciones que buscan optimizar sus envíos masivos con costos reducidos y una operación eficiente. Evolucionamos para ofrecerte un servicio más ágil, confiable y económico.',
                ], JSON_UNESCAPED_UNICODE),
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
                    'title' => 'Tarifas Preferenciales: Premia tu volumen de envío.',
                    'subtitle' => 'Califica como Gran Impositor y desbloquea tarifas corporativas exclusivas diseñadas para el volumen de tu empresa.',
                    'note_title' => 'Tu eficiencia tiene recompensa.',
                    'note_text' => 'Si cumples los criterios de calificación, accedes a nuestras tarifas preferenciales. Si tu envío es menor al mínimo, se aplicará la tarifa convencional de ventanilla. ¡Planifica y maximiza tu ahorro!',
                    'primary_button_label' => 'Solicitar cotización corporativa',
                    'primary_button_url' => '#correspondencia-cta',
                ], JSON_UNESCAPED_UNICODE),
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
                    'note_text' => 'Si la longitud máxima de cualquiera de los lados del paquete excede los 1.50 metros, se aplicarán costos volumétricos adicionales. Consulta con nuestro equipo para optimizar tu embalaje y reducir costos.',
                ], JSON_UNESCAPED_UNICODE),
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
                    'title' => 'La solución ideal para tus necesidades corporativas.',
                    'subtitle' => 'Correspondencia Agrupada potencia la operación de los sectores más exigentes.',
                ], JSON_UNESCAPED_UNICODE),
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
                    'title' => '¿Listo para reducir costos y mejorar tu logística?',
                    'text' => 'Nuestros ejecutivos corporativos están listos para diseñarte un plan a medida. Contáctanos y descubre cómo Correspondencia Agrupada puede transformar la gestión de correspondencia de tu organización.',
                    'phone_label' => 'Teléfono corporativo',
                    'phone_value' => '+591 2 2356789 (Ext. Empresas)',
                    'email_label' => 'Email corporativo',
                    'email_value' => 'empresas@correos.gob.bo',
                    'address_label' => 'Oficina central',
                    'address_value' => 'Av. Mariscal Santa Cruz 1278, La Paz',
                    'footnote' => 'Correos de Bolivia: Evolucionamos con vos.',
                    'qr_title' => 'Cotización Corporativa Digital',
                    'qr_text' => 'Escanea para solicitar una cotización corporativa digital y conocer nuestras tarifas preferenciales',
                    'qr_image' => '',
                    'button_label' => 'Escribir al equipo B2B',
                    'button_url' => '#',
                ], JSON_UNESCAPED_UNICODE),
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
                'settings' => $homeFooter->settings ?? json_encode([], JSON_UNESCAPED_UNICODE),
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
                'data' => json_encode(['title' => $item[0], 'icon' => $item[1]], JSON_UNESCAPED_UNICODE),
                'sort_order' => $index,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach ([
            ['40', 'Unidades mínimas', 'por imposición (sobres o pequeños paquetes)'],
            ['100 gr', 'Peso máximo', 'por unidad para calificar a la tarifa preferencial'],
        ] as $index => $item) {
            DB::table('site_section_items')->insert([
                'site_section_id' => $ratesId,
                'name' => $item[1],
                'type' => 'eca_rate_stat',
                'data' => json_encode(['value' => $item[0], 'title' => $item[1], 'text' => $item[2]], JSON_UNESCAPED_UNICODE),
                'sort_order' => $index,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach ([
            ['BO Alcance', 'Nacional', 'truck', 'Tiempo de entrega', '24 a 72 horas', 'Cobertura', '9 departamentos del país', 'Peso por unidad', 'Desde 10 gr hasta 20 Kg'],
            ['Proyección', 'Internacional', 'plane', 'Tiempo de entrega', '7 a 14 días hábiles', 'Cobertura', '192 países (Red UPU)', 'Peso por unidad', 'Desde 10 gr hasta 20 Kg'],
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
                ], JSON_UNESCAPED_UNICODE),
                'sort_order' => $index,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach ([
            ['Facturación Masiva', 'file', 'Distribuye facturas físicas a toda tu cartera de clientes de forma eficiente, puntual y con respaldo oficial.'],
            ['Estados de Cuenta', 'chart', 'Envíos periódicos de extractos y estados financieros para bancos, cooperativas y entidades de crédito.'],
            ['Publicidad y Marketing', 'megaphone', 'Campañas de mailing directo con alto impacto para estrategias de marketing B2C a escala nacional.'],
            ['Notificaciones Institucionales', 'building', 'Comunicados formales, notificaciones legales y convocatorias para entidades estatales y privadas.'],
        ] as $index => $item) {
            DB::table('site_section_items')->insert([
                'site_section_id' => $solutionsId,
                'name' => $item[0],
                'type' => 'eca_solution_card',
                'data' => json_encode(['title' => $item[0], 'icon' => $item[1], 'text' => $item[2], 'badge' => ''], JSON_UNESCAPED_UNICODE),
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
