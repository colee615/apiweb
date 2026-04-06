<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! DB::table('site_pages')->where('slug', 'quienes-somos')->exists()) {
            $this->seedAboutPage();
        }
    }

    public function down(): void
    {
        $pageId = DB::table('site_pages')->where('slug', 'quienes-somos')->value('id');

        if (! $pageId) {
            return;
        }

        DB::table('site_section_items')->whereIn('site_section_id', function ($query) use ($pageId) {
            $query->select('id')->from('site_sections')->where('site_page_id', $pageId);
        })->delete();

        DB::table('site_sections')->where('site_page_id', $pageId)->delete();
        DB::table('site_pages')->where('id', $pageId)->delete();
    }

    protected function seedAboutPage(): void
    {
        $now = now();
        $home = DB::table('site_pages')->where('slug', 'home')->first();
        $homeHeader = $home ? DB::table('site_sections')->where('site_page_id', $home->id)->where('key', 'header')->first() : null;
        $homeFooter = $home ? DB::table('site_sections')->where('site_page_id', $home->id)->where('key', 'footer')->first() : null;

        $pageId = DB::table('site_pages')->insertGetId([
            'slug' => 'quienes-somos',
            'name' => 'Quienes Somos',
            'meta_title' => 'Quienes Somos | Correos de Bolivia',
            'meta_description' => 'Historia, principios, organigrama y objetivos institucionales de Correos de Bolivia.',
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
                'login_label' => 'Iniciar sesión',
                'search_placeholder' => 'Buscar...',
                'language_primary' => 'Español',
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

        $heroId = DB::table('site_sections')->insertGetId([
            'site_page_id' => $pageId,
            'key' => 'hero_gallery',
            'name' => 'Carrusel superior',
            'type' => 'hero_gallery',
            'settings' => json_encode([
                'title' => 'Carrusel de fotografias',
                'subtitle' => 'Instalaciones de Correos de Bolivia',
            ]),
            'sort_order' => 1,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        foreach ([
            ['Institucional', 'Carrusel de fotografias', 'Instalaciones de Correos de Bolivia', 'https://images.unsplash.com/photo-1520607162513-77705c0f0d4a?auto=format&fit=crop&w=1600&q=80'],
            ['Nuestra gente', 'Compromiso con el pais', 'Atencion cercana, cobertura nacional y servicio publico con identidad boliviana.', 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=1600&q=80'],
            ['Modernizacion', 'Transformacion institucional', 'Infraestructura, procesos y tecnologia para conectar Bolivia con el mundo.', 'https://images.unsplash.com/photo-1497366754035-f200968a6e72?auto=format&fit=crop&w=1600&q=80'],
        ] as $index => $slide) {
            DB::table('site_section_items')->insert([
                'site_section_id' => $heroId,
                'name' => $slide[1],
                'type' => 'hero_gallery_slide',
                'data' => json_encode(['eyebrow' => $slide[0], 'title' => $slide[1], 'text' => $slide[2], 'image' => $slide[3]]),
                'sort_order' => $index,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::table('site_sections')->insert([
            [
                'site_page_id' => $pageId,
                'key' => 'mission_vision',
                'name' => 'Mision y vision',
                'type' => 'mission_vision',
                'settings' => json_encode([
                    'mission_title' => 'Mision',
                    'mission_text' => 'Brindar servicios postales y logisticos accesibles, confiables y oportunos, fortaleciendo la integracion territorial y la comunicacion de la poblacion boliviana con un enfoque de servicio publico.',
                    'vision_title' => 'Vision',
                    'vision_text' => 'Consolidarnos como una empresa postal moderna, eficiente e innovadora, reconocida por su cobertura nacional, calidad de atencion y aporte al desarrollo economico y social del pais.',
                ]),
                'sort_order' => 2,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'site_page_id' => $pageId,
                'key' => 'history',
                'name' => 'Historia',
                'type' => 'history',
                'settings' => json_encode([
                    'kicker' => 'Trayectoria institucional',
                    'title' => 'Nuestra Historia',
                    'text' => 'La Agencia Boliviana de Correos ha dejado una huella imborrable en la historia de Bolivia. Su transformacion constante y su capacidad de adaptarse a los desafios del mundo moderno la han convertido en un actor clave en la comunicacion y el comercio del pais.',
                    'carousel_title' => 'Historia de Correos 1',
                    'carousel_text' => 'Una mirada visual a la evolucion institucional.',
                ]),
                'sort_order' => 3,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'site_page_id' => $pageId,
                'key' => 'principles',
                'name' => 'Principios',
                'type' => 'principles',
                'settings' => json_encode([
                    'title' => 'Principios',
                    'subtitle' => 'Valores institucionales que guian la gestion publica de Correos de Bolivia.',
                ]),
                'sort_order' => 4,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'site_page_id' => $pageId,
                'key' => 'organigram',
                'name' => 'Organigrama',
                'type' => 'organigram',
                'settings' => json_encode([
                    'title' => 'Organigrama Correos de Bolivia',
                    'subtitle' => '',
                    'card_title' => 'Organigrama Institucional',
                    'card_text' => 'Estructura organizacional de la Agencia Boliviana de Correos',
                    'image' => 'https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=1400&q=80',
                ]),
                'sort_order' => 5,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'site_page_id' => $pageId,
                'key' => 'objectives',
                'name' => 'Objetivos institucionales',
                'type' => 'objectives',
                'settings' => json_encode([
                    'title' => 'Objetivos Estrategicos Institucionales',
                    'subtitle' => '',
                ]),
                'sort_order' => 6,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $historySectionId = DB::table('site_sections')->where('site_page_id', $pageId)->where('key', 'history')->value('id');
        $principlesSectionId = DB::table('site_sections')->where('site_page_id', $pageId)->where('key', 'principles')->value('id');
        $objectivesSectionId = DB::table('site_sections')->where('site_page_id', $pageId)->where('key', 'objectives')->value('id');

        foreach ([
            ['Historia de Correos 1', 'Oficinas y espacios que reflejan la presencia institucional en todo el territorio nacional.', 'https://images.unsplash.com/photo-1497366412874-3415097a27e7?auto=format&fit=crop&w=1400&q=80'],
            ['Historia de Correos 2', 'Procesos de modernizacion para responder a las necesidades actuales del servicio postal.', 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1400&q=80'],
            ['Historia de Correos 3', 'Un servicio que combina tradicion, cercania ciudadana y vision de futuro.', 'https://images.unsplash.com/photo-1516321497487-e288fb19713f?auto=format&fit=crop&w=1400&q=80'],
        ] as $index => $slide) {
            DB::table('site_section_items')->insert([
                'site_section_id' => $historySectionId,
                'name' => $slide[0],
                'type' => 'history_slide',
                'data' => json_encode(['title' => $slide[0], 'text' => $slide[1], 'image' => $slide[2]]),
                'sort_order' => $index,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach ([
            ['heart', 'Vivir Bien', 'Buscamos promover la satisfaccion integral de las necesidades humanas, materiales, espirituales y afectivas en armonia con la naturaleza, para alcanzar el desarrollo sostenible.'],
            ['sun', 'Calidez', 'Nos comprometemos a garantizar un trato amable, cortés y respetuoso hacia todos los usuarios de nuestros servicios, creando un ambiente acogedor y profesional.'],
            ['scale', 'Etica', 'Promovemos un compromiso efectivo por parte de nuestros servidores publicos con los principios morales y valores institucionales en el desempeño de sus funciones.'],
            ['map', 'Descolonizacion', 'Nos comprometemos a diseñar las politicas publicas en base a los valores culturales propios de nuestra diversidad, promoviendo la identidad boliviana.'],
            ['thumbs', 'Legitimidad', 'Promovemos un reconocimiento pleno por parte del soberano a los actos del Estado, asegurando que nuestras acciones reflejen la voluntad y necesidades del pueblo.'],
            ['scale', 'Legalidad', 'Nos comprometemos a actuar en estricto cumplimiento de las disposiciones legales vigentes, respetando el marco normativo que rige nuestras actividades.'],
            ['equal', 'Igualdad', 'Promovemos y garantizamos el pleno reconocimiento del derecho de ejercer los servicios sin discriminacion alguna, asegurando acceso equitativo para todos.'],
            ['searchPlus', 'Transparencia', 'Promovemos y valoramos la practica y el manejo transparente de los recursos publicos, permitiendo el acceso a la informacion de manera clara y oportuna.'],
            ['building', 'Competencia', 'Valoramos y reconocemos la atribucion legitima conferida a las autoridades para el ejercicio de sus funciones dentro del ambito de su jurisdiccion.'],
            ['trend', 'Eficacia', 'Nuestro objetivo es alcanzar los resultados programados con el proposito de cumplir las metas establecidas, maximizando el impacto de nuestras acciones.'],
            ['handshake', 'Honestidad', 'Nos comprometemos a actuar de manera correcta en el desempeño de nuestras funciones, con integridad, rectitud y sinceridad en todas nuestras acciones.'],
            ['anchor', 'Responsabilidad', 'Nos comprometemos a asumir plenamente las consecuencias de nuestros actos y decisiones, garantizando el cumplimiento de nuestras obligaciones.'],
            ['bolt', 'Eficiencia', 'Nos comprometemos a cumplir los objetivos y metas establecidas optimizando el uso de recursos disponibles, logrando mas con menos.'],
            ['checkCircle', 'Calidad', 'Nos basamos en una serie de atributos fundamentales en nuestro desempeño que garantizan la excelencia en la prestacion de servicios postales.'],
            ['trophy', 'Resultados', 'Nos enorgullece presentar los productos obtenidos a traves del desempeño eficiente de nuestras funciones, demostrando nuestro compromiso con la excelencia.'],
        ] as $index => $principle) {
            DB::table('site_section_items')->insert([
                'site_section_id' => $principlesSectionId,
                'name' => $principle[1],
                'type' => 'principle',
                'data' => json_encode(['icon' => $principle[0], 'title' => $principle[1], 'text' => $principle[2]]),
                'sort_order' => $index,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach ([
            'Diversificar la oferta a traves de la implementacion de nuevos servicios y mejora en la calidad de 9 a 17 municipios hasta el 2025.',
            'Ampliar la cobertura de los servicios postales de 9 a 17 municipios hasta el 2025.',
            'Generar ingresos por concepto de venta de servicios por un valor de Bs 14.000.000 en la gestion 2025.',
            'Ampliar la cobertura de los servicios postales en el sector publico de 1 Ministerio en la gestion 2021 a 17 Ministerios en la gestion 2025.',
            'Modernizar la estructura normativa institucional, con la finalidad de fortalecer al Operador Designado y al sector postal.',
        ] as $index => $objective) {
            DB::table('site_section_items')->insert([
                'site_section_id' => $objectivesSectionId,
                'name' => 'Objetivo ' . ($index + 1),
                'type' => 'objective',
                'data' => json_encode(['icon' => 'target', 'text' => $objective]),
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
