<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $pageId = DB::table('site_pages')->where('slug', 'home')->value('id');

        if (! $pageId) {
            return;
        }

        $now = now();

        $sections = [
            [
                'key' => 'ems_intro',
                'name' => 'EMS Intro',
                'type' => 'ems_intro',
                'sort_order' => 5,
                'settings' => [
                    'eyebrow' => 'Express Mail Service',
                    'hero_title' => 'Velocidad que conecta',
                    'watermark_text' => 'EMS',
                    'title' => 'Conectamos tus sueños, aceleramos tu mundo.',
                    'highlight_text' => 'Descubre el nuevo EMS (Express Mail Service): la evolución de la mensajería urgente en Bolivia.',
                    'paragraph_one' => 'El Corazón de Bolivia vuelve a latir con fuerza. En Correos de Bolivia, hemos evolucionado. Estamos más conectados, más digitales y más rápidos que nunca.',
                    'paragraph_two' => 'Nuestro servicio EMS representa la nueva era de la mensajería urgente: una combinación perfecta entre la tradición de nuestro servicio postal y la innovación tecnológica que el mundo moderno exige.',
                    'paragraph_three' => 'Con cobertura nacional e internacional, garantizamos que tus envíos lleguen a tiempo, con seguridad y con la confiabilidad que solo décadas de experiencia pueden ofrecer.',
                    'primary_button_label' => 'Solicitar servicio EMS',
                    'primary_button_url' => '#',
                    'visual_icon' => 'plane',
                    'image' => '',
                ],
                'items' => [],
            ],
            [
                'key' => 'ems_benefits',
                'name' => 'EMS Beneficios',
                'type' => 'ems_card_grid',
                'sort_order' => 6,
                'settings' => [
                    'title' => 'No solo enviamos paquetes, entregamos tranquilidad.',
                ],
                'items' => [
                    ['name' => 'Rastreo en Tiempo Real', 'type' => 'ems_benefit', 'sort_order' => 0, 'data' => ['icon' => 'broadcast', 'title' => 'Rastreo en Tiempo Real', 'text' => 'Seguimiento GPS de tu envío en cada momento del trayecto', 'badge' => '']],
                    ['name' => 'Seguridad Blindada', 'type' => 'ems_benefit', 'sort_order' => 1, 'data' => ['icon' => 'shield', 'title' => 'Seguridad Blindada', 'text' => 'Protección total con tecnología de seguridad de última generación', 'badge' => '']],
                    ['name' => 'Prueba de Entrega', 'type' => 'ems_benefit', 'sort_order' => 2, 'data' => ['icon' => 'check-circle', 'title' => 'Prueba de Entrega', 'text' => 'Confirmación digital inmediata con firma electrónica del destinatario', 'badge' => '']],
                    ['name' => 'Versatilidad', 'type' => 'ems_benefit', 'sort_order' => 3, 'data' => ['icon' => 'package', 'title' => 'Versatilidad', 'text' => 'Documentos, paquetes y encomiendas de cualquier tamaño', 'badge' => '']],
                ],
            ],
            [
                'key' => 'ems_national',
                'name' => 'EMS Nacional',
                'type' => 'ems_card_grid',
                'sort_order' => 7,
                'settings' => [
                    'title' => 'Bolivia, más cerca que nunca.',
                    'subtitle' => 'Conectando el corazón de Sudamérica con eficiencia y compromiso',
                    'stat_label' => 'Tiempo de entrega nacional:',
                    'stat_value' => '24 a 48 horas',
                    'stat_caption' => 'En principales ciudades',
                ],
                'items' => [
                    ['name' => 'Cobertura Total', 'type' => 'ems_national_card', 'sort_order' => 0, 'data' => ['icon' => 'pin', 'title' => 'Cobertura Total', 'text' => 'Llegamos a todos los rincones del territorio nacional, desde las grandes ciudades hasta las comunidades más remotas.', 'badge' => '']],
                    ['name' => 'Tiempos Récord', 'type' => 'ems_national_card', 'sort_order' => 1, 'data' => ['icon' => 'clock', 'title' => 'Tiempos Récord', 'text' => 'Entregas en 24 a 48 horas en las principales ciudades del país. Velocidad garantizada.', 'badge' => '']],
                    ['name' => 'Capacidad a tu medida', 'type' => 'ems_national_card', 'sort_order' => 2, 'data' => ['icon' => 'layers', 'title' => 'Capacidad a tu medida', 'text' => 'Desde documentos hasta paquetes de hasta 30 kg. Adaptamos el servicio a tus necesidades.', 'badge' => '']],
                    ['name' => 'Servicio Puerta a Puerta', 'type' => 'ems_national_card', 'sort_order' => 3, 'data' => ['icon' => 'home', 'title' => 'Servicio Puerta a Puerta', 'text' => 'Recogemos en tu domicilio y entregamos directamente al destinatario. Comodidad total.', 'badge' => '']],
                ],
            ],
            [
                'key' => 'ems_international',
                'name' => 'EMS Internacional',
                'type' => 'ems_card_grid',
                'sort_order' => 8,
                'settings' => [
                    'title' => 'El mundo en la palma de tu mano.',
                    'subtitle' => 'Gracias al convenio con la Unión Postal Universal (UPU), tu envío puede llegar a cualquier rincón del planeta con la calidad y seguridad que nos caracteriza.',
                    'highlight_text' => 'UPU',
                    'cta_text' => '¿Listo para enviar tu paquete al mundo? Nuestro equipo está preparado para ayudarte en cada paso del proceso.',
                    'secondary_button_label' => 'Cotizar envío internacional',
                    'secondary_button_url' => '#',
                ],
                'items' => [
                    ['name' => 'Alcance Sin Límites', 'type' => 'ems_international_card', 'sort_order' => 0, 'data' => ['icon' => 'globe', 'title' => 'Alcance Sin Límites', 'text' => 'Conectamos con más de 175 países en todos los continentes del mundo.', 'badge' => '175+ países']],
                    ['name' => 'Calidad Certificada', 'type' => 'ems_international_card', 'sort_order' => 1, 'data' => ['icon' => 'award', 'title' => 'Calidad Certificada', 'text' => 'Servicio respaldado por la Unión Postal Universal (UPU) con estándares internacionales.', 'badge' => 'UPU Certified']],
                    ['name' => 'Tiempos Competitivos', 'type' => 'ems_international_card', 'sort_order' => 2, 'data' => ['icon' => 'bolt', 'title' => 'Tiempos Competitivos', 'text' => 'Entregas internacionales entre 5 a 8 días hábiles a los principales destinos.', 'badge' => '5-8 días']],
                    ['name' => 'Confianza Mundial', 'type' => 'ems_international_card', 'sort_order' => 3, 'data' => ['icon' => 'users', 'title' => 'Confianza Mundial', 'text' => 'Parte de la red postal más grande del planeta con millones de envíos exitosos.', 'badge' => 'Red Global']],
                ],
            ],
        ];

        DB::table('site_sections')
            ->where('site_page_id', $pageId)
            ->whereIn('key', ['tools', 'app_banner', 'market', 'footer'])
            ->increment('sort_order', 4);

        foreach ($sections as $section) {
            $sectionId = DB::table('site_sections')
                ->where('site_page_id', $pageId)
                ->where('key', $section['key'])
                ->value('id');

            if (! $sectionId) {
                $sectionId = DB::table('site_sections')->insertGetId([
                    'site_page_id' => $pageId,
                    'key' => $section['key'],
                    'name' => $section['name'],
                    'type' => $section['type'],
                    'settings' => json_encode($section['settings']),
                    'sort_order' => $section['sort_order'],
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            } else {
                DB::table('site_sections')
                    ->where('id', $sectionId)
                    ->update([
                        'name' => $section['name'],
                        'type' => $section['type'],
                        'settings' => json_encode($section['settings']),
                        'sort_order' => $section['sort_order'],
                        'updated_at' => $now,
                    ]);
            }

            $hasItems = DB::table('site_section_items')->where('site_section_id', $sectionId)->exists();

            if ($hasItems || empty($section['items'])) {
                continue;
            }

            foreach ($section['items'] as $item) {
                DB::table('site_section_items')->insert([
                    'site_section_id' => $sectionId,
                    'name' => $item['name'],
                    'type' => $item['type'],
                    'data' => json_encode($item['data']),
                    'sort_order' => $item['sort_order'],
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        $pageId = DB::table('site_pages')->where('slug', 'home')->value('id');

        if (! $pageId) {
            return;
        }

        DB::table('site_section_items')->whereIn('site_section_id', function ($query) use ($pageId) {
            $query->select('id')
                ->from('site_sections')
                ->where('site_page_id', $pageId)
                ->whereIn('key', ['ems_intro', 'ems_benefits', 'ems_national', 'ems_international']);
        })->delete();

        DB::table('site_sections')
            ->where('site_page_id', $pageId)
            ->whereIn('key', ['ems_intro', 'ems_benefits', 'ems_national', 'ems_international'])
            ->delete();

        DB::table('site_sections')
            ->where('site_page_id', $pageId)
            ->whereIn('key', ['tools', 'app_banner', 'market', 'footer'])
            ->decrement('sort_order', 4);
    }
};
