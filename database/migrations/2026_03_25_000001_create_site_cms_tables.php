<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->json('theme')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('site_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_page_id')->constrained('site_pages')->cascadeOnDelete();
            $table->string('key');
            $table->string('name');
            $table->string('type')->default('generic');
            $table->json('settings')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['site_page_id', 'key']);
        });

        Schema::create('site_section_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_section_id')->constrained('site_sections')->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('type')->default('item');
            $table->json('data')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $this->seedHomepage();
    }

    public function down(): void
    {
        Schema::dropIfExists('site_section_items');
        Schema::dropIfExists('site_sections');
        Schema::dropIfExists('site_pages');
    }

    protected function seedHomepage(): void
    {
        $now = now();

        $pageId = DB::table('site_pages')->insertGetId([
            'slug' => 'home',
            'name' => 'Home',
            'meta_title' => 'Correos de Bolivia',
            'meta_description' => 'Portal principal administrable de Correos de Bolivia.',
            'theme' => json_encode([
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
            'settings' => json_encode([
                'help_label' => 'Ayuda / Contacto',
                'login_label' => 'Iniciar Sesion',
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

        DB::table('site_section_items')->insert([
            [
                'site_section_id' => $headerId,
                'name' => 'Quienes somos',
                'type' => 'nav_link',
                'data' => json_encode(['label' => 'Quienes somos', 'url' => '#']),
                'sort_order' => 0,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'site_section_id' => $headerId,
                'name' => 'Noticias',
                'type' => 'nav_link',
                'data' => json_encode(['label' => 'Noticias', 'url' => '#']),
                'sort_order' => 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'site_section_id' => $headerId,
                'name' => 'Institucional',
                'type' => 'nav_link',
                'data' => json_encode(['label' => 'Institucional', 'url' => '#']),
                'sort_order' => 2,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $heroId = DB::table('site_sections')->insertGetId([
            'site_page_id' => $pageId,
            'key' => 'hero',
            'name' => 'Hero',
            'type' => 'hero',
            'settings' => json_encode([
                'title' => 'Conectando Bolivia|con el Mundo',
                'subtitle' => 'Servicio postal confiable, rapido y seguro',
                'tracking_title' => 'Rastrea tu Envio',
                'tracking_text' => 'Ingresa tu codigo de seguimiento para conocer el estado de tu paquete',
                'tracking_label' => 'Codigo de seguimiento',
                'tracking_placeholder' => 'Ej: PE123456789',
                'tracking_button' => 'Buscar',
            ]),
            'sort_order' => 1,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $servicesId = DB::table('site_sections')->insertGetId([
            'site_page_id' => $pageId,
            'key' => 'services',
            'name' => 'Servicios',
            'type' => 'service_grid',
            'settings' => json_encode([
                'title' => 'Servicios Destacados',
                'subtitle' => 'Soluciones integrales para todas tus necesidades de envio',
                'kicker' => 'Servicio destacado',
            ]),
            'sort_order' => 2,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('site_section_items')->insert([
            ['site_section_id' => $servicesId, 'name' => 'EMS', 'type' => 'service', 'data' => json_encode(['icon' => 'plane', 'iconImage' => '/IconosWEB-normalized/Icono EMS Bolivia.png', 'title' => 'EMS', 'text' => 'Envio expreso internacional']), 'sort_order' => 0, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['site_section_id' => $servicesId, 'name' => 'Delivery Express', 'type' => 'service', 'data' => json_encode(['icon' => 'truck', 'iconImage' => '/IconosWEB-normalized/Icono Delivery.png', 'title' => 'Delivery Express', 'text' => 'Entregas rapidas nacionales']), 'sort_order' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['site_section_id' => $servicesId, 'name' => 'Correspondencia Agrupada', 'type' => 'service', 'data' => json_encode(['icon' => 'mail', 'title' => 'Correspondencia Agrupada', 'text' => 'Envios de correspondencia masiva']), 'sort_order' => 2, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['site_section_id' => $servicesId, 'name' => 'Encomienda Postal', 'type' => 'service', 'data' => json_encode(['icon' => 'box', 'iconImage' => '/IconosWEB-normalized/Icono Encomienda Postal.png', 'title' => 'Encomienda Postal', 'text' => 'Paquetes y encomiendas']), 'sort_order' => 3, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['site_section_id' => $servicesId, 'name' => 'Casillas', 'type' => 'service', 'data' => json_encode(['icon' => 'grid', 'iconImage' => '/IconosWEB-normalized/ICONOS NUEVOS casillas postales.png', 'title' => 'Casillas', 'text' => 'Casillas postales de alquiler']), 'sort_order' => 4, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['site_section_id' => $servicesId, 'name' => 'Mi Encomienda', 'type' => 'service', 'data' => json_encode(['icon' => 'cube', 'iconImage' => '/IconosWEB-normalized/Icono Mi Encomienda.png', 'title' => 'Mi Encomienda', 'text' => 'Seguimiento personalizado']), 'sort_order' => 5, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['site_section_id' => $servicesId, 'name' => 'Servicio Prioritario', 'type' => 'service', 'data' => json_encode(['icon' => 'clock', 'iconImage' => '/IconosWEB-normalized/Icono Servicio Prioritario.png', 'title' => 'Servicio Prioritario', 'text' => 'Entregas con prioridad']), 'sort_order' => 6, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['site_section_id' => $servicesId, 'name' => 'Filatelia', 'type' => 'service', 'data' => json_encode(['icon' => 'stamp', 'iconImage' => '/IconosWEB-normalized/Icono Filatelia.png', 'title' => 'Filatelia', 'text' => 'Sellos y colecciones']), 'sort_order' => 7, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['site_section_id' => $servicesId, 'name' => 'Red Nacional', 'type' => 'service', 'data' => json_encode(['icon' => 'pin', 'title' => 'Red Nacional', 'text' => 'Oficinas en todo el pais']), 'sort_order' => 8, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('site_sections')->insert([
            [
                'site_page_id' => $pageId,
                'key' => 'status',
                'name' => 'Estado de envio',
                'type' => 'tracking_form',
                'settings' => json_encode([
                    'title' => 'Estado de tu Envio',
                    'subtitle' => 'Ingresa tu numero de seguimiento para conocer el estado de tu paquete',
                    'placeholder' => 'Ej: PE123456789',
                    'button_label' => 'Rastrear',
                ]),
                'sort_order' => 3,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'site_page_id' => $pageId,
                'key' => 'tools',
                'name' => 'Herramientas',
                'type' => 'tools',
                'settings' => json_encode([
                    'map_title' => 'Mapa de Oficinas',
                    'map_text' => 'Encuentra la oficina de Correos mas cercana a tu ubicacion',
                    'map_button_label' => 'Ver Todas las Oficinas',
                    'calculator_title' => 'Calculadora de Tarifas',
                    'calculator_text' => 'Calcula el costo de tu envio de manera rapida',
                    'origin_label' => 'Ciudad de Origen',
                    'origin_placeholder' => 'Selecciona una ciudad',
                    'destination_label' => 'Ciudad de Destino',
                    'destination_placeholder' => 'Selecciona una ciudad',
                    'weight_label' => 'Peso (kg)',
                    'weight_placeholder' => 'Ej: 2.5',
                    'calculate_button_label' => 'Calcular Tarifa',
                ]),
                'sort_order' => 4,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'site_page_id' => $pageId,
                'key' => 'app_banner',
                'name' => 'Banner App',
                'type' => 'app_banner',
                'settings' => json_encode([
                    'title' => 'Nueva App de Correos de Bolivia',
                    'text' => 'Descargala ahora y gestiona tus envios desde tu movil con total comodidad',
                    'app_store_label' => 'Disponible en|App Store',
                    'play_store_label' => 'Disponible en|Google Play',
                    'app_store_url' => '#',
                    'play_store_url' => '#',
                ]),
                'sort_order' => 5,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'site_page_id' => $pageId,
                'key' => 'market',
                'name' => 'Market',
                'type' => 'product_grid',
                'settings' => json_encode([
                    'title' => 'Correos Market / Filatelia',
                    'subtitle' => 'Descubre nuestra coleccion exclusiva de sellos y souvenirs',
                    'view_all_label' => 'Ver todos los productos ->',
                    'view_all_url' => '#',
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
                'settings' => json_encode([
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
            ],
        ]);

        $marketId = DB::table('site_sections')->where('site_page_id', $pageId)->where('key', 'market')->value('id');
        $footerId = DB::table('site_sections')->where('site_page_id', $pageId)->where('key', 'footer')->value('id');

        DB::table('site_section_items')->insert([
            ['site_section_id' => $marketId, 'name' => 'Coleccion Bolivia 2026', 'type' => 'product', 'data' => json_encode(['title' => 'Coleccion Bolivia 2026', 'price' => 'Bs. 85.00', 'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/9/97/Bolivia_stamp_1988.jpg/320px-Bolivia_stamp_1988.jpg', 'year' => '2026', 'series' => 'Coleccion Oficial', 'description' => 'Una pieza conmemorativa pensada para coleccionistas que valoran la identidad postal boliviana.']), 'sort_order' => 0, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['site_section_id' => $marketId, 'name' => 'Serie Especial Patrimonio', 'type' => 'product', 'data' => json_encode(['title' => 'Serie Especial Patrimonio', 'price' => 'Bs. 120.00', 'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/5/5d/Bolivia_stamp_1951.jpg/320px-Bolivia_stamp_1951.jpg', 'year' => 'Edicion Especial', 'series' => 'Patrimonio Cultural', 'description' => 'Serie dedicada al legado historico y arquitectonico, con una presentacion elegante y coleccionable.']), 'sort_order' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['site_section_id' => $marketId, 'name' => 'Sellos Conmemorativos', 'type' => 'product', 'data' => json_encode(['title' => 'Sellos Conmemorativos', 'price' => 'Bs. 65.00', 'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/1/1f/Bolivia_1868_5c_stamp.jpg/320px-Bolivia_1868_5c_stamp.jpg', 'year' => 'Serie Clasica', 'series' => 'Memoria Postal', 'description' => 'Una seleccion de emisiones historicas con valor documental y un acabado visual refinado.']), 'sort_order' => 2, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['site_section_id' => $marketId, 'name' => 'Album Filatelico Premium', 'type' => 'product', 'data' => json_encode(['title' => 'Album Filatelico Premium', 'price' => 'Bs. 200.00', 'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/a0/Bolivia_stamp_1938.jpg/320px-Bolivia_stamp_1938.jpg', 'year' => 'Premium', 'series' => 'Edicion Institucional', 'description' => 'Un formato distinguido para preservar piezas filatelicas con mejor presentacion y cuidado editorial.']), 'sort_order' => 3, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['site_section_id' => $footerId, 'name' => 'Preguntas Frecuentes', 'type' => 'help_link', 'data' => json_encode(['group' => 'help', 'label' => 'Preguntas Frecuentes', 'url' => '#']), 'sort_order' => 0, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['site_section_id' => $footerId, 'name' => 'Como Rastrear un Envio', 'type' => 'help_link', 'data' => json_encode(['group' => 'help', 'label' => 'Como Rastrear un Envio', 'url' => '#']), 'sort_order' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['site_section_id' => $footerId, 'name' => 'Tarifas y Servicios', 'type' => 'help_link', 'data' => json_encode(['group' => 'help', 'label' => 'Tarifas y Servicios', 'url' => '#']), 'sort_order' => 2, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['site_section_id' => $footerId, 'name' => 'Reclamos', 'type' => 'help_link', 'data' => json_encode(['group' => 'help', 'label' => 'Reclamos', 'url' => '#']), 'sort_order' => 3, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['site_section_id' => $footerId, 'name' => 'Terminos y Condiciones', 'type' => 'help_link', 'data' => json_encode(['group' => 'help', 'label' => 'Terminos y Condiciones', 'url' => '#']), 'sort_order' => 4, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['site_section_id' => $footerId, 'name' => 'Sobre Nosotros', 'type' => 'company_link', 'data' => json_encode(['group' => 'company', 'label' => 'Sobre Nosotros', 'url' => '#']), 'sort_order' => 5, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['site_section_id' => $footerId, 'name' => 'Nuestra Historia', 'type' => 'company_link', 'data' => json_encode(['group' => 'company', 'label' => 'Nuestra Historia', 'url' => '#']), 'sort_order' => 6, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['site_section_id' => $footerId, 'name' => 'Trabaja con Nosotros', 'type' => 'company_link', 'data' => json_encode(['group' => 'company', 'label' => 'Trabaja con Nosotros', 'url' => '#']), 'sort_order' => 7, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['site_section_id' => $footerId, 'name' => 'Noticias', 'type' => 'company_link', 'data' => json_encode(['group' => 'company', 'label' => 'Noticias', 'url' => '#']), 'sort_order' => 8, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['site_section_id' => $footerId, 'name' => 'Responsabilidad Social', 'type' => 'company_link', 'data' => json_encode(['group' => 'company', 'label' => 'Responsabilidad Social', 'url' => '#']), 'sort_order' => 9, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['site_section_id' => $footerId, 'name' => 'Facebook', 'type' => 'social_link', 'data' => json_encode(['group' => 'social', 'label' => 'f', 'aria_label' => 'Facebook', 'url' => '#']), 'sort_order' => 10, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['site_section_id' => $footerId, 'name' => 'Twitter', 'type' => 'social_link', 'data' => json_encode(['group' => 'social', 'label' => 'x', 'aria_label' => 'Twitter', 'url' => '#']), 'sort_order' => 11, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['site_section_id' => $footerId, 'name' => 'Instagram', 'type' => 'social_link', 'data' => json_encode(['group' => 'social', 'label' => 'ig', 'aria_label' => 'Instagram', 'url' => '#']), 'sort_order' => 12, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['site_section_id' => $footerId, 'name' => 'LinkedIn', 'type' => 'social_link', 'data' => json_encode(['group' => 'social', 'label' => 'in', 'aria_label' => 'LinkedIn', 'url' => '#']), 'sort_order' => 13, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
};
