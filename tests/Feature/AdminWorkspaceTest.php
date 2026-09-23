<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminAnalyticsController;
use App\Models\AnalyticsEvent;
use App\Models\SitePage;
use App\Models\SiteSection;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminWorkspaceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Explicitly isolated: these tests never migrate the project's configured database.
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
        Carbon::setTestNow('2026-09-23 12:00:00');
        $user = User::create(['name' => 'Equipo de prueba', 'email' => 'review@example.test', 'password' => bcrypt('test-password'), 'job_title' => 'Administrador', 'is_active' => true]);
        $this->withSession(['admin_user_id' => $user->id]);
        $this->withoutExceptionHandling();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_every_editor_and_history_render_with_one_save_action(): void
    {
        $this->get('/admin')->assertOk()->assertSee('Contenido del sitio');
        foreach (SitePage::all() as $page) {
            $response = $this->get('/admin/pages/'.$page->id.'/edit')->assertOk();
            $this->assertSame(1, substr_count($response->getContent(), 'data-editor-form'), $page->slug);
            $this->assertSame(1, substr_count($response->getContent(), 'name="change_summary"'), $page->slug);
            $this->get('/admin/pages/'.$page->id.'/edit?tab=history_overview')
                ->assertOk()->assertSee('Historial de cambios')->assertDontSee('data-editor-form', false);
        }
        $this->get('/admin/users')->assertOk();
        $this->get('/admin/users/create')->assertOk();
        $this->get('/admin/users/1/edit')->assertOk();
        $this->get('/admin/analytics')->assertOk()->assertSee('No hay actividad registrada');
    }

    public function test_brand_and_header_are_configured_once_on_home_and_shared_by_public_pages(): void
    {
        $home = SitePage::where('slug', 'home')->firstOrFail();
        $home->update(['theme' => ['primary_color' => '#123456', 'logo_url' => '/storage/logo.png']]);
        SiteSection::updateOrCreate(
            ['site_page_id' => $home->id, 'key' => 'header'],
            ['name' => 'Encabezado', 'type' => 'header', 'sort_order' => 1, 'is_active' => true, 'settings' => ['help_label' => 'Ayuda global']]
        );

        $page = SitePage::create(['slug' => 'pagina-prueba', 'name' => 'Página de prueba', 'meta_title' => 'SEO propio', 'theme' => ['primary_color' => '#abcdef'], 'is_active' => true]);
        $legacyHeader = SiteSection::create(['site_page_id' => $page->id, 'key' => 'header', 'name' => 'Encabezado antiguo', 'type' => 'header', 'sort_order' => 0, 'is_active' => true, 'settings' => ['help_label' => 'Texto local']]);
        $contentSection = SiteSection::create(['site_page_id' => $page->id, 'key' => 'custom_content', 'name' => 'Contenido existente', 'type' => 'generic', 'sort_order' => 2, 'is_active' => true, 'settings' => ['text' => 'Se conserva']]);

        $page->update(['meta_title' => 'SEO propio actualizado']);
        $response = $this->getJson('/api/site/pages/pagina-prueba')->assertOk();
        $response->assertJsonPath('theme.primary_color', '#123456');
        $response->assertJsonPath('theme.logo_url', asset('/storage/logo.png'));
        $response->assertJsonPath('meta_title', 'SEO propio actualizado');
        $response->assertJsonPath('section_map.header.settings.help_label', 'Ayuda global');

        $this->get('/admin/pages/'.$page->id.'/edit')->assertOk()
            ->assertDontSee('name="theme[primary_color]"', false)
            ->assertDontSee('name="header[help_label]"', false);
        $this->get('/admin/pages/'.$home->id.'/edit')->assertOk()
            ->assertDontSee('name="theme[primary_color]"', false)
            ->assertDontSee('name="header[help_label]"', false);
        $this->get('/admin/configuracion-global')->assertOk()
            ->assertSee('name="theme[primary_color]"', false)
            ->assertSee('name="header[help_label]"', false);

        $this->put('/admin/pages/'.$page->id, [
            'name' => 'Página editada', 'slug' => 'pagina-prueba', 'meta_title' => 'SEO nuevo',
            'meta_description' => '', 'is_active' => '1', 'change_summary' => 'Actualizar datos propios',
        ])->assertRedirect('/admin/pages/'.$page->id.'/edit');
        $this->assertSame('#abcdef', $page->fresh()->theme['primary_color']);
        $this->assertSame('Texto local', $legacyHeader->fresh()->settings['help_label']);
        $this->assertSame('Se conserva', $contentSection->fresh()->settings['text']);
    }

    public function test_ems_has_its_own_admin_page_while_home_payload_stays_compatible(): void
    {
        $home = SitePage::where('slug', 'home')->firstOrFail();
        $ems = SitePage::where('slug', 'ems')->firstOrFail();
        $emsKeys = ['ems_intro', 'ems_benefits', 'ems_national', 'ems_international'];

        $this->assertSame(0, $home->sections()->whereIn('key', $emsKeys)->count());
        $this->assertSame(4, $ems->sections()->whereIn('key', $emsKeys)->count());

        $this->getJson('/api/site/pages/home')->assertOk()
            ->assertJsonPath('section_map.ems_intro.key', 'ems_intro')
            ->assertJsonPath('section_map.ems_international.key', 'ems_international');
        $this->getJson('/api/site/pages/ems')->assertOk()
            ->assertJsonPath('section_map.ems_intro.key', 'ems_intro');

        $this->get('/admin')->assertOk()->assertSee('/ems')->assertDontSee('Incluye el contenido de EMS');
        $this->get('/admin/pages/'.$home->id.'/edit?tab=ems')
            ->assertRedirect(route('admin.pages.edit', $ems));
        $this->get('/admin/pages/'.$ems->id.'/edit')->assertOk()
            ->assertSee('Contenido de EMS')
            ->assertSee('buscadores')
            ->assertDontSee('Información de Home');
    }

    public function test_saving_ems_does_not_change_home_content(): void
    {
        $home = SitePage::where('slug', 'home')->firstOrFail();
        $ems = SitePage::where('slug', 'ems')->firstOrFail();
        $homeTitle = $home->sections()->where('key', 'hero')->firstOrFail()->settings['title'] ?? null;
        $emsSections = $ems->sections()->with('items')->get()->keyBy('key');
        $items = fn (string $key) => $emsSections[$key]->items
            ->map(fn ($item) => array_merge($item->data ?? [], ['id' => $item->id]))
            ->all();

        $this->put('/admin/pages/'.$ems->id, [
            'name' => 'EMS',
            'slug' => 'ems',
            'meta_title' => 'EMS actualizado',
            'meta_description' => 'Servicio EMS independiente.',
            'is_active' => '1',
            'ems_intro' => array_merge($emsSections['ems_intro']->settings, ['title' => 'Envíos urgentes EMS']),
            'ems_benefits' => array_merge($emsSections['ems_benefits']->settings, ['items' => $items('ems_benefits')]),
            'ems_national' => array_merge($emsSections['ems_national']->settings, ['items' => $items('ems_national')]),
            'ems_international' => array_merge($emsSections['ems_international']->settings, ['items' => $items('ems_international')]),
        ])->assertRedirect(route('admin.pages.edit', $ems))->assertSessionHasNoErrors();

        $this->assertSame($homeTitle, $home->fresh()->sections()->where('key', 'hero')->firstOrFail()->settings['title'] ?? null);
        $updatedIntro = $ems->fresh('sections')->sections->firstWhere('key', 'ems_intro');
        $this->assertSame('Envíos urgentes EMS', $updatedIntro->settings['title']);
        $this->getJson('/api/site/pages/home')->assertJsonPath('section_map.ems_intro.settings.title', 'Envíos urgentes EMS');
    }

    public function test_analytics_deduplicates_visitors_per_week_and_counts_all_interactions(): void
    {
        foreach (['2026-09-20', '2026-09-21'] as $day) {
            AnalyticsEvent::create(['visitor_token' => 'same-visitor', 'session_token' => 'session-a', 'event_name' => 'page_view', 'page_path' => '/', 'occurred_at' => $day.' 10:00:00']);
        }
        for ($i = 0; $i < 12; $i++) {
            AnalyticsEvent::create(['visitor_token' => 'same-visitor', 'session_token' => 'session-a', 'event_name' => 'cta_click', 'section_key' => 'section-'.$i, 'occurred_at' => '2026-09-21 10:00:00']);
        }
        $response = $this->get('/admin/analytics?start_date=2026-09-20&end_date=2026-09-23&granularity=week')->assertOk();
        $summary = $response->viewData('summary');
        $this->assertSame(1, $summary['visitors_in_range']);
        $this->assertSame(2, $summary['page_views_in_range']);
        $this->assertSame(12, $summary['interactions_in_range']);
        $this->assertSame(10, $response->viewData('topInteractions')->count());
        $this->assertSame(1, $response->viewData('trendSeries')->first()->total);
        $this->assertNull($summary['visitors_delta']['value']);
        $this->assertSame(4, $response->viewData('filters')['days']);
        $response->assertSee('Sin base de comparación');
    }

    public function test_ranges_remain_ordered_and_comparison_has_equal_calendar_days(): void
    {
        $controller = new class extends AdminAnalyticsController {
            public function ranges(array $query): array {
                [$start, $end] = $this->resolveDateRange(new Request($query));
                return [$start, $end, $this->resolveComparisonRange($start, $end, 'previous')];
            }
        };
        foreach ([['2030-01-01', '2030-02-01'], ['2020-01-01', '2020-02-01'], ['2026-09-23', '2026-09-20'], ['invalid', '2026-02-31'], [[], []]] as [$start, $end]) {
            [$actualStart, $actualEnd, [$previousStart, $previousEnd]] = $controller->ranges(['start_date' => $start, 'end_date' => $end]);
            $this->assertTrue($actualStart->lessThanOrEqualTo($actualEnd));
            $this->assertTrue($actualStart->greaterThanOrEqualTo(Carbon::today()->subDays(365)));
            $this->assertTrue($actualEnd->lessThanOrEqualTo(Carbon::today()->endOfDay()));
            $this->assertSame((int) $actualStart->diffInDays($actualEnd->copy()->startOfDay()), (int) $previousStart->diffInDays($previousEnd->copy()->startOfDay()));
            $this->assertSame($actualStart->copy()->subDay()->toDateString(), $previousEnd->toDateString());
        }
    }

    public function test_inactive_user_creation_is_preserved_by_the_shared_form(): void
    {
        $this->post('/admin/users', ['name' => 'Inactivo', 'email' => 'inactive@example.test', 'job_title' => 'Gestor', 'password' => 'test-password', 'is_active' => '0'])->assertRedirect('/admin/users');
        $this->assertFalse(User::where('email', 'inactive@example.test')->firstOrFail()->is_active);
    }

    public function test_history_renders_field_differences_and_a_single_restore_action(): void
    {
        $page = SitePage::where('slug', 'home')->firstOrFail();
        $versioning = app(\App\Services\SitePageVersioningService::class);
        $first = $versioning->createInitialVersion($page, User::first());
        $before = $page->fresh(['sections.items']);
        $page->update(['name' => 'Inicio actualizado']);
        $versioning->recordUpdate($before, $page->fresh(['sections.items']), User::first());
        $response = $this->get('/admin/pages/'.$page->id.'/edit?tab=history_overview')->assertOk();
        $response->assertSee('Inicio actualizado')->assertSee('DESPUÉS')->assertSee('ANTES');
        $this->assertSame(1, substr_count($response->getContent(), 'action="'.route('admin.pages.restore', [$page, $first]).'"'));
    }

    public function test_editor_role_cannot_manage_users(): void
    {
        $this->withExceptionHandling();
        User::first()->update(['job_title' => 'Gestor']);
        $this->get('/admin')->assertOk()->assertDontSee('href="'.route('admin.users.index').'"', false);
        $this->get('/admin/users')->assertForbidden();
    }

    public function test_tracking_codes_are_searchable_paginated_and_show_the_recorded_source(): void
    {
        for ($i = 0; $i < 12; $i++) {
            AnalyticsEvent::create(['visitor_token' => 'visitor', 'session_token' => 'session', 'event_name' => 'tracking_search', 'searched_term' => 'BO-'.$i.'-TEST', 'metadata' => ['source' => 'hero_tracking'], 'occurred_at' => now()]);
        }
        $response = $this->get('/admin/analytics')->assertOk()->assertSee('Buscador de la portada');
        $this->assertSame(12, $response->viewData('topTrackingSearches')->total());
        $this->assertSame(10, $response->viewData('topTrackingSearches')->count());
        $response = $this->get('/admin/analytics?tracking_page=2')->assertOk();
        $this->assertSame(2, $response->viewData('topTrackingSearches')->count());
        $response = $this->get('/admin/analytics?tracking_code=bo-11')->assertOk();
        $this->assertSame(1, $response->viewData('topTrackingSearches')->total());
        $this->assertSame('BO-11-TEST', $response->viewData('topTrackingSearches')->first()->searched_term);
        $response = $this->get('/admin/analytics?tracking_code=%25')->assertOk();
        $this->assertSame(0, $response->viewData('topTrackingSearches')->total());
    }

    public function test_legacy_tracking_codes_show_the_s10_service_without_saved_metadata(): void
    {
        foreach ([
            ['LX090198785NL', null],
            ['RR526016791NL', null],
            ['EE000741500BO', null],
            ['EN00005799CBB', null],
            ['SL000741500BO', null],
            ['MA123456785BO', 'Saco M'],
            ['HA123456785BO', 'ECOMPRO'],
            ['AV123456785BO', 'RFID'],
            ['XB123456785BO', 'TRACKING'],
        ] as $index => [$code, $service]) {
            AnalyticsEvent::create([
                'visitor_token' => 'legacy-visitor-'.$index,
                'session_token' => 'legacy-session-'.$index,
                'event_name' => 'tracking_search',
                'searched_term' => $code,
                'metadata' => $service ? ['service' => $service] : null,
                'occurred_at' => now(),
            ]);
        }

        $response = $this->get('/admin/analytics')->assertOk();
        $servicesByCode = $response->viewData('topTrackingSearches')->getCollection()->keyBy('searched_term');

        $this->assertSame('ORDI', $servicesByCode->get('LX090198785NL')->service);
        $this->assertSame('CERTI', $servicesByCode->get('RR526016791NL')->service);
        $this->assertSame('EMS Internacional', $servicesByCode->get('EE000741500BO')->service);
        $this->assertSame('EMS Nacional', $servicesByCode->get('EN00005799CBB')->service);
        $this->assertSame('Delivery Express', $servicesByCode->get('SL000741500BO')->service);
        $this->assertNull($servicesByCode->get('MA123456785BO')->service);
        $this->assertNull($servicesByCode->get('HA123456785BO')->service);
        $this->assertNull($servicesByCode->get('AV123456785BO')->service);
        $this->assertNull($servicesByCode->get('XB123456785BO')->service);

        $services = $response->viewData('trackingByService')->keyBy('service');
        $this->assertSame(1, (int) $services->get('ORDI')->searches);
        $this->assertSame(1, (int) $services->get('CERTI')->searches);
        $this->assertSame(1, (int) $services->get('EMS Internacional')->searches);
        $this->assertSame(1, (int) $services->get('EMS Nacional')->searches);
        $this->assertSame(1, (int) $services->get('Delivery Express')->searches);
        $this->assertSame(4, (int) $services->get(null)->searches);
        $this->assertSame(4, (int) $services->get(null)->codes);

        $unassigned = $this->get('/admin/analytics?tracking_service=__unassigned__')->assertOk();
        $this->assertSame(4, $unassigned->viewData('topTrackingSearches')->total());
        $this->assertEqualsCanonicalizing(
            ['MA123456785BO', 'HA123456785BO', 'AV123456785BO', 'XB123456785BO'],
            $unassigned->viewData('topTrackingSearches')->pluck('searched_term')->all()
        );
    }

    public function test_package_statistics_merge_code_variants_and_export_the_filtered_period(): void
    {
        foreach ([' bo123 ', 'BO123', '=FORMULA'] as $index => $code) {
            AnalyticsEvent::create(['visitor_token' => 'visitor-'.$index, 'session_token' => 'session-'.$index, 'event_name' => 'tracking_search', 'searched_term' => $code, 'occurred_at' => now()]);
        }
        $response = $this->get('/admin/analytics?chart_metric=tracking')->assertOk();
        $this->assertSame(2, $response->viewData('summary')['tracking_unique_codes']);
        $this->assertSame(1, $response->viewData('summary')['tracking_repeat_searches']);
        $this->assertSame(3, $response->viewData('summary')['tracking_visitors']);
        $this->assertSame(3, $response->viewData('trendSeries')->sum('total'));
        $this->assertSame(3, $response->viewData('hourlyTracking')->sum('total'));
        $row = $response->viewData('topTrackingSearches')->first();
        $this->assertSame('BO123', $row->searched_term);
        $this->assertSame(2, (int) $row->visitors);

        $download = $this->get('/admin/analytics/tracking/export?tracking_code=bo123')->assertOk();
        $csv = $download->streamedContent();
        $this->assertStringContainsString('BO123;;2;2;', $csv);
        $this->assertStringNotContainsString('FORMULA', $csv);
        $download = $this->get('/admin/analytics/tracking/export')->assertOk();
        $this->assertStringContainsString("'=FORMULA", $download->streamedContent());
    }

    public function test_tracking_searches_group_by_service_and_report_unsuccessful_results(): void
    {
        foreach ([['EMS Internacional', 'EE123'], ['EMS Internacional', 'EE124'], ['Encomienda', 'XB456']] as [$service, $code]) {
            AnalyticsEvent::create(['visitor_token' => 'visitor-'.$code, 'session_token' => 'session-'.$code, 'event_name' => 'tracking_search', 'searched_term' => $code, 'metadata' => ['service' => $service], 'occurred_at' => now()]);
        }

        $this->withExceptionHandling()->postJson('/api/analytics/collect', [
            'visitor_token' => 'visitor-result', 'session_token' => 'session-result',
            'event_name' => 'tracking_result', 'searched_term' => 'EE999',
            'metadata' => ['service' => 'EMS Internacional', 'tracking_status' => 'not_found'],
        ])->assertOk()->assertJsonPath('ok', true);
        $this->postJson('/api/analytics/collect', [
            'visitor_token' => 'visitor-found', 'session_token' => 'session-found',
            'event_name' => 'tracking_result', 'searched_term' => 'EE123',
            'metadata' => ['service' => 'EMS Internacional', 'tracking_status' => 'found'],
        ])->assertOk();
        $this->postJson('/api/analytics/collect', [
            'visitor_token' => 'visitor-error', 'session_token' => 'session-error',
            'event_name' => 'tracking_result', 'searched_term' => 'XB456',
            'metadata' => ['service' => 'Encomienda', 'tracking_status' => 'error'],
        ])->assertOk();

        $response = $this->get('/admin/analytics')->assertOk()->assertSee('Búsquedas por servicio')->assertSee('Consultas sin éxito');
        $services = $response->viewData('trackingByService')->keyBy('service');
        $this->assertSame(2, (int) $services->get('EMS Internacional')->searches);
        $this->assertSame(1, (int) $services->get('Encomienda')->searches);
        $this->assertSame(1, $response->viewData('trackingResults')['not_found']);
        $this->assertSame(1, $response->viewData('trackingResults')['found']);
        $this->assertSame(1, $response->viewData('trackingResults')['error']);
        $this->assertSame('EE999', $response->viewData('unsuccessfulTrackingSearches')->first()->searched_term);

        $filtered = $this->get('/admin/analytics?tracking_service=EMS%20Internacional')->assertOk();
        $this->assertSame(2, $filtered->viewData('topTrackingSearches')->total());
        $this->assertSame(1, $filtered->viewData('unsuccessfulTrackingSearches')->total());

        $this->postJson('/api/analytics/collect', [
            'visitor_token' => 'visitor-invalid-result', 'session_token' => 'session-invalid-result',
            'event_name' => 'tracking_result', 'searched_term' => 'EE000',
            'metadata' => ['service' => 'EMS', 'tracking_status' => 'maybe'],
        ])->assertUnprocessable();
    }
}
