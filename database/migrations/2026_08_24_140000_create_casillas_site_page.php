<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! DB::table('site_pages')->where('slug', 'casillas')->exists()) {
            $this->seedCasillasPage();
        }
    }

    public function down(): void
    {
        $pageId = DB::table('site_pages')->where('slug', 'casillas')->value('id');

        if (! $pageId) {
            return;
        }

        DB::table('site_section_items')->whereIn('site_section_id', function ($query) use ($pageId) {
            $query->select('id')->from('site_sections')->where('site_page_id', $pageId);
        })->delete();

        DB::table('site_sections')->where('site_page_id', $pageId)->delete();
        DB::table('site_pages')->where('id', $pageId)->delete();
    }

    protected function seedCasillasPage(): void
    {
        $now = now();
        $home = DB::table('site_pages')->where('slug', 'home')->first();
        $homeHeader = $home ? DB::table('site_sections')->where('site_page_id', $home->id)->where('key', 'header')->first() : null;
        $homeFooter = $home ? DB::table('site_sections')->where('site_page_id', $home->id)->where('key', 'footer')->first() : null;

        $pageId = DB::table('site_pages')->insertGetId([
            'slug' => 'casillas',
            'name' => 'Casillas',
            'meta_title' => 'Casillas | Correos de Bolivia',
            'meta_description' => 'Servicio de casillas postales de Correos de Bolivia. Seguridad, privacidad, tamanos disponibles y requisitos de apertura.',
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
                'key' => 'casillas_hero',
                'name' => 'Casillas Hero',
                'type' => 'casillas_hero',
                'settings' => json_encode([
                    'badge' => 'Servicios - Casillas Postales',
                    'title_line_one_white' => 'Tu direccion exclusiva en',
                    'title_line_one_yellow' => 'el corazon',
                    'title_line_two_white' => 'de la ciudad.',
                    'highlight_text' => 'Servicio de Casillas: tu punto de llegada preferencial.',
                    'subtitle' => 'Seguridad, privacidad y disponibilidad inmediata. Dale a tus envios el lugar que se merecen en la nueva era digital de Correos de Bolivia.',
                    'primary_button_label' => 'Obtener mi Casilla',
                    'primary_button_url' => '#casillas-requirements',
                    'secondary_button_label' => 'Ver tamanos disponibles',
                    'secondary_button_url' => '#casillas-sizes',
                    'background_image' => '',
                ], JSON_UNESCAPED_UNICODE),
                'sort_order' => 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'site_page_id' => $pageId,
                'key' => 'casillas_intro',
                'name' => 'Casillas Intro',
                'type' => 'casillas_intro',
                'settings' => json_encode([
                    'eyebrow' => 'Tu espacio seguro',
                    'title' => 'Mas que una casilla, tu hub personal de conexion.',
                    'paragraph_one' => 'En un mundo en constante movimiento, necesitas un lugar fijo y confiable. El Servicio de Casillas de Correos de Bolivia es la solucion ideal para profesionales, academicos, empresas y ciudadanos que buscan gestionar su correspondencia y paquetes con total autonomia.',
                    'paragraph_two' => 'Ya sea por razones laborales, estudios o tramites personales, tu casilla es tu puerta al mundo, garantizando que cada documento y cada paquete te espere en un entorno seguro y confidencial.',
                ], JSON_UNESCAPED_UNICODE),
                'sort_order' => 2,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'site_page_id' => $pageId,
                'key' => 'casillas_benefits',
                'name' => 'Casillas Beneficios',
                'type' => 'casillas_benefits',
                'settings' => json_encode([
                    'title' => 'Control total y entrega inmediata.',
                    'subtitle' => 'Por que esperar al cartero si tus envios pueden esperarte a ti?',
                ], JSON_UNESCAPED_UNICODE),
                'sort_order' => 3,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'site_page_id' => $pageId,
                'key' => 'casillas_sizes',
                'name' => 'Casillas Tamanos',
                'type' => 'casillas_sizes',
                'settings' => json_encode([
                    'title' => 'Una solucion a tu medida.',
                    'subtitle' => 'Ofrecemos opciones flexibles que se ajustan a tu volumen de recepcion y a tu presupuesto.',
                    'plan_label' => 'Plan de alquiler:',
                    'quarterly_label' => 'Trimestral',
                    'semiannual_label' => 'Semestral',
                    'annual_label' => 'Anual',
                    'annual_badge' => '-20%',
                    'panel_title' => 'Panel de casilleros',
                ], JSON_UNESCAPED_UNICODE),
                'sort_order' => 4,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'site_page_id' => $pageId,
                'key' => 'casillas_requirements',
                'name' => 'Casillas Requisitos',
                'type' => 'casillas_requirements',
                'settings' => json_encode([
                    'title' => 'Sencillo, rapido y oficial.',
                    'subtitle' => 'Abre tu casilla hoy mismo con estos requisitos minimos.',
                    'banner_title' => 'Ven a nuestra Oficina Central y sal con tu llave en mano el mismo dia!',
                    'banner_text' => 'Sin citas previas · Proceso en menos de 15 minutos · Todos los tamanos disponibles',
                    'banner_button_label' => 'Ir ahora',
                    'banner_button_url' => '/contacto',
                ], JSON_UNESCAPED_UNICODE),
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
                'settings' => $homeFooter->settings ?? json_encode([], JSON_UNESCAPED_UNICODE),
                'sort_order' => 6,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $introId = DB::table('site_sections')->where('site_page_id', $pageId)->where('key', 'casillas_intro')->value('id');
        $benefitsId = DB::table('site_sections')->where('site_page_id', $pageId)->where('key', 'casillas_benefits')->value('id');
        $sizesId = DB::table('site_sections')->where('site_page_id', $pageId)->where('key', 'casillas_sizes')->value('id');
        $requirementsId = DB::table('site_sections')->where('site_page_id', $pageId)->where('key', 'casillas_requirements')->value('id');

        foreach ([
            ['192', 'Paises conectados'],
            ['24h', 'Notificacion de llegada'],
            ['100%', 'Confidencialidad'],
        ] as $index => $item) {
            DB::table('site_section_items')->insert([
                'site_section_id' => $introId,
                'name' => $item[1],
                'type' => 'casillas_stat',
                'data' => json_encode(['value' => $item[0], 'label' => $item[1]], JSON_UNESCAPED_UNICODE),
                'sort_order' => $index,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach ([
            ['spark', 'Disponibilidad Acelerada', 'Tus envios se depositan en el instante mismo de su arribo. Sin demoras de distribucion domiciliaria.'],
            ['lock', 'Privacidad y Seguridad', 'Solo tu tienes acceso. Protegemos la confidencialidad con los mas altos estandares institucionales.'],
            ['calendar', 'Horarios que se adaptan a ti', 'Retira con comodidad: Lun-Vie 08:00 - 18:30 (continuo) Sabado: 08:30 - 13:00.'],
            ['globe', 'Conexion Global', 'Recibe correspondencia de Bolivia y del mundo con garantia institucional y respaldo de la UPU.'],
        ] as $index => $item) {
            DB::table('site_section_items')->insert([
                'site_section_id' => $benefitsId,
                'name' => $item[1],
                'type' => 'casillas_benefit_card',
                'data' => json_encode(['icon' => $item[0], 'title' => $item[1], 'text' => $item[2]], JSON_UNESCAPED_UNICODE),
                'sort_order' => $index,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach ([
            ['mail', 'Pequena', 'Cartas / Personal', 'Ideal para cartas, documentos personales y extracciones de bajo volumen.', '15 × 30 × 40 cm'],
            ['package', 'Mediana', 'Suscripciones frecuentes', 'Perfecta para revistas, libros y paquetes de e-commerce ligeros.', '25 × 30 × 50 cm'],
            ['briefcase', 'Gaveta', 'Profesionales', 'Para profesionales con flujo constante de muestras, sobres y correspondencia administrativa.', '35 × 40 × 60 cm'],
            ['building', 'Cajon', 'Empresas / Paquetes', 'Solucion robusta para empresas con alto volumen de recepcion y retiros frecuentes.', '50 × 50 × 80 cm'],
        ] as $index => $item) {
            DB::table('site_section_items')->insert([
                'site_section_id' => $sizesId,
                'name' => $item[1],
                'type' => 'casillas_size_card',
                'data' => json_encode([
                    'icon' => $item[0],
                    'title' => $item[1],
                    'badge' => $item[2],
                    'category' => $item[2],
                    'text' => $item[3],
                    'dimensions' => $item[4],
                ], JSON_UNESCAPED_UNICODE),
                'sort_order' => $index,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach ([
            ['person', 'Persona Natural', 'Fotocopia de Cedula de Identidad vigente', 'Fotocopia de factura de luz o agua', 'Numero de celular activo', 'Pago del plan seleccionado', ''],
            ['office', 'Persona Juridica', 'Fotocopia de NIT activo de la empresa', 'Poder notarial del representante legal', 'Cedula de Identidad del representante', 'Fotocopia de factura de servicios basicos', 'Correo institucional de contacto'],
        ] as $index => $item) {
            DB::table('site_section_items')->insert([
                'site_section_id' => $requirementsId,
                'name' => $item[1],
                'type' => 'casillas_requirement_card',
                'data' => json_encode([
                    'icon' => $item[0],
                    'title' => $item[1],
                    'row_one' => $item[2],
                    'row_two' => $item[3],
                    'row_three' => $item[4],
                    'row_four' => $item[5],
                    'row_five' => $item[6],
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
