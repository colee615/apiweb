<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('site_pages')->where('slug', 'misaplicaicones')->exists()) {
            return;
        }

        $this->seedPage();
    }

    public function down(): void
    {
        $pageId = DB::table('site_pages')->where('slug', 'misaplicaicones')->value('id');

        if (! $pageId) {
            return;
        }

        DB::table('site_section_items')->whereIn('site_section_id', function ($query) use ($pageId) {
            $query->select('id')->from('site_sections')->where('site_page_id', $pageId);
        })->delete();

        DB::table('site_sections')->where('site_page_id', $pageId)->delete();
        DB::table('site_pages')->where('id', $pageId)->delete();
    }

    protected function seedPage(): void
    {
        $now = now();
        $home = DB::table('site_pages')->where('slug', 'home')->first();
        $homeHeader = $home ? DB::table('site_sections')->where('site_page_id', $home->id)->where('key', 'header')->first() : null;
        $homeFooter = $home ? DB::table('site_sections')->where('site_page_id', $home->id)->where('key', 'footer')->first() : null;

        $pageId = DB::table('site_pages')->insertGetId([
            'slug' => 'misaplicaicones',
            'name' => 'Aplicaciones y Sistemas',
            'meta_title' => 'Aplicaciones y Sistemas | Correos de Bolivia',
            'meta_description' => 'Accede a las aplicaciones, plataformas y sistemas digitales de Correos de Bolivia.',
            'theme' => $home->theme ?? json_encode([
                'logo_url' => '',
                'primary_color' => '#20539a',
                'secondary_color' => '#2f3f5c',
                'accent_color' => '#fecc36',
            ], JSON_UNESCAPED_UNICODE),
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $headerId = $this->insertSection(
            $pageId,
            'header',
            'Encabezado',
            'header',
            $homeHeader->settings ?? json_encode([], JSON_UNESCAPED_UNICODE),
            0,
            $now
        );

        if ($homeHeader) {
            $this->copyItems($homeHeader->id, $headerId, $now);
        }

        $applicationsId = $this->insertSection(
            $pageId,
            'applications',
            'Aplicaciones y Sistemas',
            'applications_grid',
            json_encode([
                'hero_eyebrow' => 'HERRAMIENTAS DIGITALES',
                'hero_title' => 'Aplicaciones y',
                'hero_title_accent' => 'Sistemas',
                'hero_text' => 'Accede a nuestras plataformas digitales y realiza tus trámites, consultas y gestiones de forma rápida, segura y desde cualquier lugar.',
                'catalog_eyebrow' => 'TODO EN UN SOLO LUGAR',
                'catalog_title' => 'Tus herramientas digitales',
                'search_placeholder' => 'Buscar aplicaciones o sistemas...',
                'support_eyebrow' => '¿NECESITAS AYUDA?',
                'support_title' => 'Estamos para orientarte',
                'support_text' => 'Si tienes dudas sobre una plataforma o necesitas acceder a otro servicio, nuestro equipo puede ayudarte.',
                'support_button_label' => 'Contáctanos',
                'support_button_url' => '/contacto',
                'background_image' => '/servicios_fondo.png',
            ], JSON_UNESCAPED_UNICODE),
            1,
            $now
        );

        foreach ($this->applications() as $index => $application) {
            DB::table('site_section_items')->insert([
                'site_section_id' => $applicationsId,
                'name' => $application['name'],
                'type' => 'application',
                'data' => json_encode($application, JSON_UNESCAPED_UNICODE),
                'sort_order' => $index,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $highlightsId = $this->insertSection(
            $pageId,
            'applications_highlights',
            'Beneficios digitales',
            'highlight_grid',
            json_encode([], JSON_UNESCAPED_UNICODE),
            2,
            $now
        );

        foreach ($this->highlights() as $index => $highlight) {
            DB::table('site_section_items')->insert([
                'site_section_id' => $highlightsId,
                'name' => $highlight['title'],
                'type' => 'application_highlight',
                'data' => json_encode($highlight, JSON_UNESCAPED_UNICODE),
                'sort_order' => $index,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $footerId = $this->insertSection(
            $pageId,
            'footer',
            'Pie de página',
            'footer',
            $homeFooter->settings ?? json_encode([], JSON_UNESCAPED_UNICODE),
            3,
            $now
        );

        if ($homeFooter) {
            $this->copyItems($homeFooter->id, $footerId, $now);
        }
    }

    protected function insertSection(int $pageId, string $key, string $name, string $type, string $settings, int $sortOrder, $now): int
    {
        return DB::table('site_sections')->insertGetId([
            'site_page_id' => $pageId,
            'key' => $key,
            'name' => $name,
            'type' => $type,
            'settings' => $settings,
            'sort_order' => $sortOrder,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    protected function copyItems(int $sourceSectionId, int $targetSectionId, $now): void
    {
        $items = DB::table('site_section_items')->where('site_section_id', $sourceSectionId)->orderBy('sort_order')->get();

        foreach ($items as $index => $item) {
            DB::table('site_section_items')->insert([
                'site_section_id' => $targetSectionId,
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

    protected function applications(): array
    {
        return [
            ['id' => 'trackingbo', 'name' => 'TrackingBO', 'type' => 'Aplicación', 'category' => 'Operativos', 'description' => 'Consulta el estado de tus envíos en todo el país.', 'icon' => 'package', 'preview' => 'tracking', 'preview_label' => 'TrackingBO', 'preview_title' => 'Rastrea tu envío', 'color' => '#1769d1', 'action' => 'Ingresar', 'url' => 'https://trackingbo.correos.gob.bo:8100', 'image' => ''],
            ['id' => 'bolipost', 'name' => 'Bolipost', 'type' => 'Aplicación', 'category' => 'Comerciales', 'description' => 'Realiza tus envíos de forma rápida y segura.', 'icon' => 'package', 'preview' => 'bolipost', 'preview_label' => 'Bolipost', 'preview_title' => 'Envíos fáciles', 'color' => '#f2b900', 'action' => 'Ver más', 'url' => '', 'image' => ''],
            ['id' => 'sitra', 'name' => 'SITRA', 'type' => 'Sistema', 'category' => 'Administrativos', 'description' => 'Sistema de trámites y correspondencia institucional.', 'icon' => 'document', 'preview' => 'sitra', 'preview_label' => 'SITRA', 'preview_title' => 'Mis trámites', 'color' => '#10a768', 'action' => 'Ingresar', 'url' => '', 'image' => ''],
            ['id' => 'filatelia', 'name' => 'Filatelia', 'type' => 'Sitio web', 'category' => 'Comerciales', 'description' => 'Descubre y colecciona nuestra historia postal.', 'icon' => 'stamp', 'preview' => 'filatelia', 'preview_label' => 'Filatelia', 'preview_title' => 'Colección postal', 'color' => '#6d4bdd', 'action' => 'Ver más', 'url' => '/postalshopper', 'image' => ''],
            ['id' => 'rrhh-web', 'name' => 'RRHH Web', 'type' => 'Sistema', 'category' => 'Administrativos', 'description' => 'Gestión de talento humano de Correos de Bolivia.', 'icon' => 'users', 'preview' => 'rrhh', 'preview_label' => 'RRHH Web', 'preview_title' => 'Gestión humana', 'color' => '#e84145', 'action' => 'Ingresar', 'url' => '', 'image' => ''],
            ['id' => 'facturacion-electronica', 'name' => 'Facturación Electrónica', 'type' => 'Aplicación', 'category' => 'Administrativos', 'description' => 'Emite y consulta tus facturas de manera fácil y segura.', 'icon' => 'receipt', 'preview' => 'billing', 'preview_label' => 'Facturación', 'preview_title' => 'Nueva factura', 'color' => '#297be7', 'action' => 'Ingresar', 'url' => '', 'image' => ''],
            ['id' => 'ems-bolivia', 'name' => 'EMS Bolivia', 'type' => 'Sitio web', 'category' => 'Operativos', 'description' => 'Envíos exprés a nivel nacional e internacional.', 'icon' => 'plane', 'preview' => 'ems', 'preview_label' => 'EMS Bolivia', 'preview_title' => 'Rápido y seguro', 'color' => '#f47b20', 'action' => 'Ver más', 'url' => '/ems', 'image' => ''],
            ['id' => 'web-institucional', 'name' => 'Web Institucional', 'type' => 'Sitio web', 'category' => 'Institucionales', 'description' => 'Conoce más sobre nuestra institución y servicios.', 'icon' => 'building', 'preview' => 'institutional', 'preview_label' => 'Correos de Bolivia', 'preview_title' => 'Conectamos Bolivia', 'color' => '#174c91', 'action' => 'Visitar sitio', 'url' => '/', 'image' => ''],
        ];
    }

    protected function highlights(): array
    {
        return [
            ['icon' => 'shield', 'title' => 'Más servicios para ti'],
            ['icon' => 'users', 'title' => 'Gestión pública más eficiente'],
            ['icon' => 'spark', 'title' => 'Tecnología al servicio de Bolivia'],
        ];
    }
};
