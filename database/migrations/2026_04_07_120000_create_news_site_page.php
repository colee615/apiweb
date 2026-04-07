<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! DB::table('site_pages')->where('slug', 'noticias')->exists()) {
            $this->seedNewsPage();
        }
    }

    public function down(): void
    {
        $pageId = DB::table('site_pages')->where('slug', 'noticias')->value('id');

        if (! $pageId) {
            return;
        }

        DB::table('site_section_items')->whereIn('site_section_id', function ($query) use ($pageId) {
            $query->select('id')->from('site_sections')->where('site_page_id', $pageId);
        })->delete();

        DB::table('site_sections')->where('site_page_id', $pageId)->delete();
        DB::table('site_pages')->where('id', $pageId)->delete();
    }

    protected function seedNewsPage(): void
    {
        $now = now();
        $home = DB::table('site_pages')->where('slug', 'home')->first();
        $homeHeader = $home ? DB::table('site_sections')->where('site_page_id', $home->id)->where('key', 'header')->first() : null;
        $homeFooter = $home ? DB::table('site_sections')->where('site_page_id', $home->id)->where('key', 'footer')->first() : null;

        $pageId = DB::table('site_pages')->insertGetId([
            'slug' => 'noticias',
            'name' => 'Noticias',
            'meta_title' => 'Noticias | Correos de Bolivia',
            'meta_description' => 'Noticias, comunicados y novedades institucionales de Correos de Bolivia.',
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
                'key' => 'featured_story',
                'name' => 'Noticia destacada',
                'type' => 'featured_story',
                'settings' => json_encode([
                    'button_label' => 'Leer noticia completa',
                ]),
                'sort_order' => 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'site_page_id' => $pageId,
                'key' => 'category_filters',
                'name' => 'Filtros de categoria',
                'type' => 'category_filters',
                'settings' => json_encode([
                    'search_placeholder' => 'Buscar noticias...',
                ]),
                'sort_order' => 2,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'site_page_id' => $pageId,
                'key' => 'news_grid',
                'name' => 'Grid de noticias',
                'type' => 'news_grid',
                'settings' => json_encode([
                    'title' => 'Noticias recientes',
                    'subtitle' => 'Actualidad institucional, filatelia, comunicados y prensa.',
                    'cta_label' => 'Leer mas',
                ]),
                'sort_order' => 3,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'site_page_id' => $pageId,
                'key' => 'newsletter',
                'name' => 'Boletin',
                'type' => 'newsletter',
                'settings' => json_encode([
                    'badge' => 'Boletin digital',
                    'title' => 'Mantente informado',
                    'text' => 'Suscribete a nuestro boletin digital y recibe las ultimas noticias, actualizaciones y promociones directamente en tu correo.',
                    'placeholder' => 'tu@email.com',
                    'button_label' => 'Unirse',
                    'legal_text' => 'Al suscribirte, aceptas recibir comunicaciones de Correos de Bolivia.',
                ]),
                'sort_order' => 4,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'site_page_id' => $pageId,
                'key' => 'pagination',
                'name' => 'Paginacion',
                'type' => 'pagination',
                'settings' => json_encode([
                    'load_more_label' => 'Cargar mas noticias',
                ]),
                'sort_order' => 5,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $featuredId = DB::table('site_sections')->where('site_page_id', $pageId)->where('key', 'featured_story')->value('id');
        $filtersId = DB::table('site_sections')->where('site_page_id', $pageId)->where('key', 'category_filters')->value('id');
        $gridId = DB::table('site_sections')->where('site_page_id', $pageId)->where('key', 'news_grid')->value('id');
        $paginationId = DB::table('site_sections')->where('site_page_id', $pageId)->where('key', 'pagination')->value('id');

        DB::table('site_section_items')->insert([
            'site_section_id' => $featuredId,
            'name' => 'Correos de Bolivia implementa nueva tecnologia de rastreo en tiempo real',
            'type' => 'featured_story_item',
            'data' => json_encode([
                'badge' => 'Destacado',
                'title' => 'Correos de Bolivia implementa nueva tecnologia de rastreo en tiempo real',
                'excerpt' => 'La Agencia Boliviana de Correos anuncia la modernizacion de su sistema de seguimiento, permitiendo a los usuarios rastrear sus envios con precision milimetrica y recibir notificaciones instantaneas.',
                'category' => 'Institucional',
                'image' => 'https://images.unsplash.com/photo-1516321165247-4aa89a48be28?auto=format&fit=crop&w=1400&q=80',
                'article_url' => '#',
            ]),
            'sort_order' => 0,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        foreach ([
            ['Institucional', '#', true],
            ['Filatelia', '#', false],
            ['Comunicados', '#', false],
            ['Prensa', '#', false],
        ] as $index => $filter) {
            DB::table('site_section_items')->insert([
                'site_section_id' => $filtersId,
                'name' => $filter[0],
                'type' => 'news_category',
                'data' => json_encode([
                    'label' => $filter[0],
                    'url' => $filter[1],
                    'is_active' => $filter[2],
                ]),
                'sort_order' => $index,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach ([
            ['15 Mar 2026', 'Institucional', 'Correos de Bolivia amplia su red de oficinas en el area rural', 'Nueva apertura de sucursales en zonas rurales para facilitar el acceso a servicios postales en comunidad...', 'https://images.unsplash.com/photo-1521791136064-7986c2920216?auto=format&fit=crop&w=900&q=80'],
            ['12 Mar 2026', 'Filatelia', 'Nueva coleccion filatelica conmemora la biodiversidad boliviana', 'Una serie especial de sellos celebra la rica flora y fauna del pais, destacando especies endemicas.', 'https://images.unsplash.com/photo-1516542076529-1ea3854896f2?auto=format&fit=crop&w=900&q=80'],
            ['08 Mar 2026', 'Comunicados', 'Acuerdo internacional fortalece servicios de paqueteria', 'Alianza estrategica con operadores postales de la region mejora tiempos de entrega internacional.', 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?auto=format&fit=crop&w=900&q=80'],
            ['03 Mar 2026', 'Prensa', 'Campana nacional promueve el uso de canales digitales de seguimiento', 'La institucion impulsa herramientas de autoservicio para consultas y rastreo en linea.', 'https://images.unsplash.com/photo-1520607162513-77705c0f0d4a?auto=format&fit=crop&w=900&q=80'],
            ['28 Feb 2026', 'Institucional', 'Centro logistica mejora capacidad operativa en temporada alta', 'La nueva infraestructura permite clasificar mas envios y reducir tiempos de procesamiento.', 'https://images.unsplash.com/photo-1566576721346-d4a3b4eaeb55?auto=format&fit=crop&w=900&q=80'],
            ['21 Feb 2026', 'Comunicados', 'Correos de Bolivia habilita nuevas ventanillas de atencion empresarial', 'El servicio especializado facilita tramites y despachos mas eficientes para clientes corporativos.', 'https://images.unsplash.com/photo-1497366754035-f200968a6e72?auto=format&fit=crop&w=900&q=80'],
        ] as $index => $item) {
            DB::table('site_section_items')->insert([
                'site_section_id' => $gridId,
                'name' => $item[2],
                'type' => 'news_card',
                'data' => json_encode([
                    'date' => $item[0],
                    'category' => $item[1],
                    'title' => $item[2],
                    'excerpt' => $item[3],
                    'image' => $item[4],
                    'article_url' => '#',
                ]),
                'sort_order' => $index,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach ([
            ['1', '#', true, false],
            ['2', '#', false, false],
            ['3', '#', false, false],
            ['...', '#', false, true],
            ['10', '#', false, false],
        ] as $index => $item) {
            DB::table('site_section_items')->insert([
                'site_section_id' => $paginationId,
                'name' => $item[0],
                'type' => 'news_page',
                'data' => json_encode([
                    'label' => $item[0],
                    'url' => $item[1],
                    'is_active' => $item[2],
                    'is_ellipsis' => $item[3],
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
            'sort_order' => 6,
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
