<?php

namespace App\Http\Controllers;

use App\Models\AuthSession;
use App\Models\AnalyticsEvent;
use App\Models\AnalyticsVisitorSession;
use App\Models\SitePageChangeLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminAnalyticsController extends Controller
{
    public function index(Request $request): View
    {
        [$startDate, $endDate] = $this->resolveDateRange($request);
        $compareMode = $this->resolveCompareMode($request);
        $granularity = $this->resolveGranularity($request, $startDate, $endDate);
        $chartMetric = in_array($request->query('chart_metric'), ['views', 'tracking'], true)
            ? $request->query('chart_metric') : 'visitors';
        $today = Carbon::today();
        $onlineThreshold = now()->subMinutes(5);
        $rangeDays = (int) $startDate->diffInDays($endDate->copy()->startOfDay()) + 1;
        [$previousStartDate, $previousEndDate, $compareLabel, $compareModeLabel] = $this->resolveComparisonRange(
            $startDate,
            $endDate,
            $compareMode
        );

        app(\App\Services\AuthSessionTracker::class)->expireInactive();

        AnalyticsVisitorSession::query()
            ->where('last_seen_at', '<', $onlineThreshold)
            ->update(['is_online' => false]);

        $pageViewsInRange = AnalyticsEvent::query()
            ->where('event_name', 'page_view')
            ->whereBetween('occurred_at', [$startDate, $endDate])
            ->count();

        $trackingSearchesInRange = AnalyticsEvent::query()
            ->where('event_name', 'tracking_search')
            ->whereBetween('occurred_at', [$startDate, $endDate])
            ->count();

        $visitorsInRange = AnalyticsEvent::query()
            ->where('event_name', 'page_view')
            ->whereBetween('occurred_at', [$startDate, $endDate])
            ->distinct('visitor_token')
            ->count('visitor_token');

        $pageViewsPrevious = AnalyticsEvent::query()
            ->where('event_name', 'page_view')
            ->whereBetween('occurred_at', [$previousStartDate, $previousEndDate])
            ->count();

        $trackingSearchesPrevious = AnalyticsEvent::query()
            ->where('event_name', 'tracking_search')
            ->whereBetween('occurred_at', [$previousStartDate, $previousEndDate])
            ->count();

        $visitorsPrevious = AnalyticsEvent::query()
            ->where('event_name', 'page_view')
            ->whereBetween('occurred_at', [$previousStartDate, $previousEndDate])
            ->distinct('visitor_token')
            ->count('visitor_token');

        $summary = [
            'apiweb_online_now' => AuthSession::query()
                ->where('is_active', true)
                ->where('last_seen_at', '>=', $onlineThreshold)
                ->count(),
            'apiweb_admin_online_now' => AuthSession::query()
                ->where('guard_name', 'admin_web')
                ->where('is_active', true)
                ->where('last_seen_at', '>=', $onlineThreshold)
                ->count(),
            'apiweb_api_online_now' => AuthSession::query()
                ->where('guard_name', 'api_users')
                ->where('is_active', true)
                ->where('last_seen_at', '>=', $onlineThreshold)
                ->count(),
            'online_now' => AnalyticsVisitorSession::query()
                ->where('is_online', true)
                ->where('last_seen_at', '>=', $onlineThreshold)
                ->count(),
            'visitors_in_range' => $visitorsInRange,
            'page_views_in_range' => $pageViewsInRange,
            'tracking_searches_in_range' => $trackingSearchesInRange,
            'visitors_today' => AnalyticsEvent::query()
                ->where('event_name', 'page_view')
                ->whereDate('occurred_at', $today)
                ->distinct('visitor_token')
                ->count('visitor_token'),
            'page_views_today' => AnalyticsEvent::query()
                ->where('event_name', 'page_view')
                ->whereDate('occurred_at', $today)
                ->count(),
            'tracking_searches_today' => AnalyticsEvent::query()
                ->where('event_name', 'tracking_search')
                ->whereDate('occurred_at', $today)
                ->count(),
            'visitors_delta' => $this->calculateDelta($visitorsInRange, $visitorsPrevious),
            'page_views_delta' => $this->calculateDelta($pageViewsInRange, $pageViewsPrevious),
            'tracking_delta' => $this->calculateDelta($trackingSearchesInRange, $trackingSearchesPrevious),
        ];

        $eventTotals = AnalyticsEvent::query()
            ->selectRaw('event_name, COUNT(*) as total')
            ->whereBetween('occurred_at', [$startDate, $endDate])
            ->whereIn('event_name', ['cta_click', 'tracking_search', 'calculator_quote'])
            ->groupBy('event_name')->pluck('total', 'event_name');
        $summary['cta_clicks_in_range'] = (int) $eventTotals->get('cta_click', 0);
        $summary['calculator_quotes_in_range'] = (int) $eventTotals->get('calculator_quote', 0);
        $summary['interactions_in_range'] = (int) $eventTotals->sum();
        $summary['sessions_in_range'] = AnalyticsEvent::query()->where('event_name', 'page_view')
            ->whereBetween('occurred_at', [$startDate, $endDate])->distinct()->count('session_token');
        $summary['views_per_session'] = $summary['sessions_in_range'] > 0
            ? round($pageViewsInRange / $summary['sessions_in_range'], 2) : null;
        $trackingTotals = AnalyticsEvent::query()->where('event_name', 'tracking_search')
            ->whereBetween('occurred_at', [$startDate, $endDate])
            ->selectRaw("COUNT(DISTINCT NULLIF(UPPER(TRIM(searched_term)), '')) as codes, COUNT(DISTINCT visitor_token) as visitors, COUNT(NULLIF(TRIM(searched_term), '')) as with_code")
            ->first();
        $summary['tracking_unique_codes'] = (int) $trackingTotals->codes;
        $summary['tracking_visitors'] = (int) $trackingTotals->visitors;
        $summary['tracking_repeat_searches'] = (int) $trackingTotals->with_code - (int) $trackingTotals->codes;
        $summary['tracking_without_code'] = $trackingSearchesInRange - (int) $trackingTotals->with_code;

        $deviceBreakdown = AnalyticsVisitorSession::query()
            ->selectRaw("COALESCE(device_type, 'unknown') as device, COUNT(*) as total")
            ->whereIn('session_token', AnalyticsEvent::query()
                ->select('session_token')->where('event_name', 'page_view')
                ->whereBetween('occurred_at', [$startDate, $endDate]))
            ->groupBy('device_type')->orderByDesc('total')->get();

        $trendSeries = $this->buildVisitorTrend($startDate, $endDate, $granularity, $chartMetric);
        $comparisonTrendSeries = $this->buildVisitorTrend($previousStartDate, $previousEndDate, $granularity, $chartMetric);

        $sessionsInRange = AnalyticsVisitorSession::query()->whereIn('session_token', AnalyticsEvent::query()
            ->select('session_token')->where('event_name', 'page_view')->whereBetween('occurred_at', [$startDate, $endDate]));
        $browserBreakdown = (clone $sessionsInRange)->selectRaw("COALESCE(NULLIF(browser, ''), 'Sin identificar') as name, COUNT(*) as total")
            ->groupBy('browser')->orderByDesc('total')->limit(8)->get();
        $referrers = (clone $sessionsInRange)->selectRaw('referrer, COUNT(*) as total')->groupBy('referrer')->get()
            ->groupBy(fn ($row) => parse_url((string) $row->referrer, PHP_URL_HOST) ?: 'Sin referencia registrada')
            ->map(fn ($rows, $host) => (object) ['name' => $host, 'total' => (int) $rows->sum('total')])
            ->sortByDesc('total')->take(8)->values();
        $hourExpression = DB::connection()->getDriverName() === 'sqlite'
            ? "CAST(strftime('%H', occurred_at) AS INTEGER)" : 'EXTRACT(HOUR FROM occurred_at)';
        $hourTotals = AnalyticsEvent::query()->selectRaw($hourExpression . ' as hour, COUNT(*) as total')
            ->where('event_name', 'tracking_search')->whereBetween('occurred_at', [$startDate, $endDate])
            ->groupBy(DB::raw($hourExpression))->get()->keyBy('hour');
        $hourlyTracking = collect(range(0, 23))->map(fn ($hour) => (object) [
            'label' => str_pad((string) $hour, 2, '0', STR_PAD_LEFT) . ':00',
            'total' => (int) ($hourTotals->get($hour)?->total ?? 0),
        ]);
        $topButtons = AnalyticsEvent::query()->selectRaw("COALESCE(NULLIF(label, ''), 'Botón sin nombre') as name, page_path, COUNT(*) as total")
            ->where('event_name', 'cta_click')->whereBetween('occurred_at', [$startDate, $endDate])
            ->groupBy('label', 'page_path')->orderByDesc('total')->limit(8)->get();
        $quoteBreakdown = collect(['destination' => 'Destinos cotizados', 'service' => 'Servicios cotizados'])
            ->map(function ($label, $field) use ($startDate, $endDate) {
                return [
                    'label' => $label,
                    'rows' => AnalyticsEvent::query()->select('metadata->'.$field.' as name')->selectRaw('COUNT(*) as total')
                        ->where('event_name', 'calculator_quote')->whereBetween('occurred_at', [$startDate, $endDate])
                        ->groupBy('metadata->'.$field)->orderByDesc('total')->limit(8)->get(),
                ];
            });

        $topPages = AnalyticsEvent::query()
            ->selectRaw('page_path, MAX(page_name) as page_name, COUNT(*) as total')
            ->where('event_name', 'page_view')
            ->whereBetween('occurred_at', [$startDate, $endDate])
            ->whereNotNull('page_path')
            ->groupBy('page_path')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $topInteractions = AnalyticsEvent::query()
            ->selectRaw("COALESCE(section_key, page_path, 'sin-seccion') as section_name, COUNT(*) as total")
            ->whereIn('event_name', ['cta_click', 'tracking_search', 'calculator_quote'])
            ->whereBetween('occurred_at', [$startDate, $endDate])
            ->groupBy(DB::raw("COALESCE(section_key, page_path, 'sin-seccion')"))
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $trackingCode = $this->trackingCodeFilter($request);
        $trackingService = $this->trackingServiceFilter($request);
        $topTrackingSearches = $this->trackingCodesQuery($startDate, $endDate, $trackingCode, $trackingService)
            ->paginate(10, ['*'], 'tracking_page')
            ->withQueryString();

        $trackingSources = AnalyticsEvent::query()
            ->select('metadata->source as source')->selectRaw('COUNT(*) as total')
            ->where('event_name', 'tracking_search')
            ->whereBetween('occurred_at', [$startDate, $endDate])
            ->groupBy('metadata->source')->orderByDesc('total')->get();

        $trackingByService = AnalyticsEvent::query()
            ->selectRaw($this->trackingServiceSql().' as service')
            ->selectRaw("COUNT(*) as searches, COUNT(DISTINCT NULLIF(UPPER(TRIM(searched_term)), '')) as codes")
            ->where('event_name', 'tracking_search')->whereBetween('occurred_at', [$startDate, $endDate])
            ->groupByRaw($this->trackingServiceSql())->orderByDesc('searches')->get();

        $trackingResultTotals = AnalyticsEvent::query()
            ->select('metadata->tracking_status as status')->selectRaw('COUNT(*) as total')
            ->where('event_name', 'tracking_result')->whereBetween('occurred_at', [$startDate, $endDate])
            ->groupBy('metadata->tracking_status')->pluck('total', 'status');
        $trackingResults = [
            'found' => (int) $trackingResultTotals->get('found', 0),
            'not_found' => (int) $trackingResultTotals->get('not_found', 0),
            'error' => (int) $trackingResultTotals->get('error', 0),
        ];
        $unsuccessfulTrackingSearches = AnalyticsEvent::query()
            ->select(['searched_term', 'page_path', 'occurred_at'])
            ->selectRaw($this->trackingServiceSql().' as service')->addSelect('metadata->tracking_status as tracking_status')
            ->where('event_name', 'tracking_result')->whereBetween('occurred_at', [$startDate, $endDate])
            ->whereIn('metadata->tracking_status', ['not_found', 'error'])
            ->when($trackingService === '__unassigned__', fn ($query) => $query->whereRaw($this->trackingServiceSql().' IS NULL'))
            ->when($trackingService !== '' && $trackingService !== '__unassigned__', fn ($query) => $query->whereRaw($this->trackingServiceSql().' = ?', [$trackingService]))
            ->when($trackingCode !== '', fn ($query) => $query->whereRaw("LOWER(searched_term) LIKE ? ESCAPE '!'", [
                '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], mb_strtolower($trackingCode)) . '%',
            ]))
            ->latest('occurred_at')->paginate(10, ['*'], 'tracking_result_page')->withQueryString();

        $activeVisitors = AnalyticsVisitorSession::query()
            ->where('is_online', true)
            ->where('last_seen_at', '>=', $onlineThreshold)
            ->latest('last_seen_at')
            ->limit(30)
            ->get();

        $apiwebSessions = AuthSession::query()
            ->with('user')
            ->where('is_active', true)
            ->where('last_seen_at', '>=', $onlineThreshold)
            ->latest('last_seen_at')
            ->limit(12)
            ->get();

        $recentActivity = $this->buildRecentActivity($startDate, $endDate);
        $filters = [
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'label' => $startDate->isSameDay($endDate)
                ? $startDate->format('d/m/Y')
                : $startDate->format('d/m/Y') . ' al ' . $endDate->format('d/m/Y'),
            'previous_label' => $compareLabel,
            'compare_mode' => $compareMode,
            'compare_mode_label' => $compareModeLabel,
            'granularity' => $granularity,
            'chart_metric' => $chartMetric,
            'days' => $rangeDays,
        ];

        return view('admin.analytics.index', compact(
            'summary',
            'deviceBreakdown',
            'browserBreakdown',
            'referrers',
            'hourlyTracking',
            'topButtons',
            'quoteBreakdown',
            'apiwebSessions',
            'trendSeries',
            'comparisonTrendSeries',
            'topPages',
            'topInteractions',
            'topTrackingSearches',
            'trackingCode',
            'trackingSources',
            'trackingByService',
            'trackingResults',
            'unsuccessfulTrackingSearches',
            'trackingService',
            'activeVisitors',
            'recentActivity',
            'filters'
        ));
    }

    public function exportTracking(Request $request): StreamedResponse
    {
        [$startDate, $endDate] = $this->resolveDateRange($request);
        $query = $this->trackingCodesQuery($startDate, $endDate, $this->trackingCodeFilter($request), $this->trackingServiceFilter($request));

        return response()->streamDownload(function () use ($query) {
            $stream = fopen('php://output', 'w');
            fwrite($stream, "\xEF\xBB\xBF");
            fputcsv($stream, ['Código de paquete', 'Servicio', 'Consultas', 'Visitantes', 'Primera consulta', 'Última consulta'], ';', '"', '');
            foreach ($query->cursor() as $row) {
                // Codes are user input; prevent spreadsheet formula execution on export.
                $code = preg_match('/^[=+@\-\t\r\n]/', $row->searched_term) ? "'".$row->searched_term : $row->searched_term;
                fputcsv($stream, [$code, $row->service, $row->total, $row->visitors, $row->first_seen_at, $row->last_seen_at], ';', '"', '');
            }
            fclose($stream);
        }, 'codigos-consultados-'.$startDate->format('Ymd').'-'.$endDate->format('Ymd').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    protected function trackingCodeFilter(Request $request): string
    {
        $value = $request->query('tracking_code', '');

        return is_string($value) ? mb_substr(trim($value), 0, 160) : '';
    }

    protected function trackingServiceFilter(Request $request): string
    {
        $value = $request->query('tracking_service', '');

        return is_string($value) ? mb_substr(trim($value), 0, 120) : '';
    }

    protected function trackingCodesQuery(Carbon $startDate, Carbon $endDate, string $code, string $service = ''): Builder
    {
        $serviceSql = $this->trackingServiceSql();

        return AnalyticsEvent::query()
            ->selectRaw("UPPER(TRIM(searched_term)) as searched_term, COUNT(*) as total, COUNT(DISTINCT visitor_token) as visitors, MIN(occurred_at) as first_seen_at, MAX(occurred_at) as last_seen_at, {$serviceSql} as service")
            ->where('event_name', 'tracking_search')->whereNotNull('searched_term')->whereRaw("TRIM(searched_term) != ''")
            ->whereBetween('occurred_at', [$startDate, $endDate])
            ->when($service === '__unassigned__', fn ($query) => $query->whereRaw($serviceSql.' IS NULL'))
            ->when($service !== '' && $service !== '__unassigned__', fn ($query) => $query->whereRaw($serviceSql.' = ?', [$service]))
            ->when($code !== '', fn ($query) => $query->whereRaw("LOWER(searched_term) LIKE ? ESCAPE '!'", [
                '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], mb_strtolower($code)) . '%',
            ]))
            ->groupBy(DB::raw('UPPER(TRIM(searched_term))'), DB::raw($serviceSql))
            ->orderByDesc('total')->orderByDesc('last_seen_at')->orderByRaw('UPPER(TRIM(searched_term))');
    }

    protected function trackingServiceSql(): string
    {
        $grammar = DB::connection()->getQueryGrammar();
        $storedService = $grammar->wrap('metadata->service');
        $code = 'UPPER(TRIM(searched_term))';
        $indicator = "SUBSTR({$code}, 1, 1)";
        $serviceIndicator = "SUBSTR({$code}, 1, 2)";
        $inferredService = "CASE"
            ." WHEN {$serviceIndicator} = 'EN' THEN 'EMS Nacional'"
            ." WHEN {$serviceIndicator} = 'SL' THEN 'Delivery Express'"
            ." WHEN {$indicator} = 'E' THEN 'EMS Internacional'"
            ." WHEN {$indicator} = 'R' THEN 'CERTI'"
            ." WHEN {$indicator} IN ('C', 'L', 'U') THEN 'ORDI'"
            ." WHEN {$indicator} = 'V' THEN 'CARTA ASEGURADA'"
            ." WHEN {$indicator} = 'Q' THEN 'IBRS'"
            .' END';
        $storedServiceFallback = "CASE"
            ." WHEN UPPER(TRIM({$storedService})) IN ('', 'TRACKING', 'NO IDENTIFICADO', 'SERVICIO NO IDENTIFICADO', 'SACO M', 'ECOMPRO', 'RFID') THEN NULL"
            ." ELSE {$storedService}"
            .' END';

        return "COALESCE({$inferredService}, {$storedServiceFallback})";
    }

    protected function buildRecentActivity(Carbon $startDate, Carbon $endDate): Collection
    {
        $pageChanges = SitePageChangeLog::query()
            ->with('page')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->latest('created_at')
            ->limit(12)
            ->get()
            ->map(function (SitePageChangeLog $log) {
                return [
                    'source' => 'cms',
                    'title' => $log->summary ?: 'Cambio registrado',
                    'subtitle' => $log->page?->name ?: ($log->section_key ?: 'Sitio'),
                    'actor' => $log->created_by_name ?: $log->created_by_email ?: 'Sistema',
                    'detail' => $log->action === 'restored'
                        ? 'Restauracion aplicada'
                        : 'Cambio en ' . ($log->section_key ?: 'contenido general'),
                    'occurred_at' => $log->created_at,
                ];
            });

        $authLogs = collect();

        if (Schema::hasTable('activity_logs')) {
            $authLogs = DB::table('activity_logs')
                ->select(['activity_type', 'description', 'user_email', 'created_at'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->latest('created_at')
                ->limit(12)
                ->get()
                ->map(function ($log) {
                    return [
                        'source' => 'auth',
                        'title' => $log->activity_type ?: 'Actividad',
                        'subtitle' => $log->description ?: 'Movimiento del sistema',
                        'actor' => $log->user_email ?: 'No registrado',
                        'detail' => 'Bitacora de autenticacion/API',
                        'occurred_at' => Carbon::parse($log->created_at),
                    ];
                });
        }

        return $pageChanges
            ->concat($authLogs)
            ->sortByDesc('occurred_at')
            ->take(14)
            ->values();
    }

    protected function resolveDateRange(Request $request): array
    {
        $defaultEnd = Carbon::today()->endOfDay();
        $defaultStart = $defaultEnd->copy()->subDays(13)->startOfDay();

        $startInput = $request->query('start_date', '');
        $endInput = $request->query('end_date', '');

        try {
            $startDate = is_string($startInput) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $startInput)
                ? Carbon::createFromFormat('!Y-m-d', $startInput)->startOfDay()
                : $defaultStart;
            if ($startDate->toDateString() !== $startInput) $startDate = $defaultStart;
        } catch (\Throwable) {
            $startDate = $defaultStart;
        }

        try {
            $endDate = is_string($endInput) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $endInput)
                ? Carbon::createFromFormat('!Y-m-d', $endInput)->endOfDay()
                : $defaultEnd;
            if ($endDate->toDateString() !== $endInput) $endDate = $defaultEnd;
        } catch (\Throwable) {
            $endDate = $defaultEnd;
        }

        if ($startDate->greaterThan($endDate)) {
            [$startDate, $endDate] = [$endDate->copy()->startOfDay(), $startDate->copy()->endOfDay()];
        }

        $maxPast = Carbon::today()->subDays(365)->startOfDay();
        $maxEnd = Carbon::today()->endOfDay();

        if ($startDate->lessThan($maxPast)) {
            $startDate = $maxPast;
        }

        if ($endDate->greaterThan($maxEnd)) {
            $endDate = $maxEnd;
        }

        // Clamp both ends, including ranges entirely in the future or past.
        if ($startDate->greaterThan($maxEnd)) $startDate = $maxEnd->copy()->startOfDay();
        if ($endDate->lessThan($maxPast)) $endDate = $maxPast->copy()->endOfDay();

        return [$startDate, $endDate];
    }

    protected function resolveCompareMode(Request $request): string
    {
        $mode = $request->query('compare_mode', 'previous');

        return in_array($mode, ['previous', 'month', 'year'], true)
            ? $mode
            : 'previous';
    }

    protected function resolveGranularity(Request $request, Carbon $startDate, Carbon $endDate): string
    {
        $granularity = $request->query('granularity', 'auto');
        $allowed = ['auto', 'day', 'week', 'month'];

        if (!in_array($granularity, $allowed, true)) {
            $granularity = 'auto';
        }

        if ($granularity !== 'auto') {
            return $granularity;
        }

        $rangeDays = (int) $startDate->diffInDays($endDate->copy()->startOfDay()) + 1;

        if ($rangeDays <= 31) {
            return 'day';
        }

        if ($rangeDays <= 180) {
            return 'week';
        }

        return 'month';
    }

    protected function resolveComparisonRange(Carbon $startDate, Carbon $endDate, string $compareMode): array
    {
        return match ($compareMode) {
            'month' => [
                $startDate->copy()->subMonthNoOverflow()->startOfDay(),
                $endDate->copy()->subMonthNoOverflow()->endOfDay(),
                $startDate->copy()->subMonthNoOverflow()->format('d/m/Y') . ' al ' . $endDate->copy()->subMonthNoOverflow()->format('d/m/Y'),
                'Mes vs mes',
            ],
            'year' => [
                $startDate->copy()->subYearNoOverflow()->startOfDay(),
                $endDate->copy()->subYearNoOverflow()->endOfDay(),
                $startDate->copy()->subYearNoOverflow()->format('d/m/Y') . ' al ' . $endDate->copy()->subYearNoOverflow()->format('d/m/Y'),
                'Año anterior',
            ],
            default => [
                $startDate->copy()->subDays((int) $startDate->diffInDays($endDate->copy()->startOfDay()) + 1)->startOfDay(),
                $startDate->copy()->subDay()->endOfDay(),
                $startDate->copy()->subDays((int) $startDate->diffInDays($endDate->copy()->startOfDay()) + 1)->format('d/m/Y') . ' al ' . $startDate->copy()->subDay()->format('d/m/Y'),
                'Periodo anterior',
            ],
        };
    }

    protected function calculateDelta(int $current, int $previous): array
    {
        if ($previous === 0 && $current === 0) {
            return ['value' => 0.0, 'direction' => 'neutral'];
        }

        if ($previous === 0) {
            return ['value' => null, 'direction' => 'new'];
        }

        $delta = round((($current - $previous) / $previous) * 100, 1);

        return [
            'value' => abs($delta),
            'direction' => $delta > 0 ? 'up' : ($delta < 0 ? 'down' : 'neutral'),
        ];
    }

    /** Count each visitor once per displayed bucket, rather than adding daily uniques. */
    protected function buildVisitorTrend(Carbon $startDate, Carbon $endDate, string $granularity, string $metric = 'visitors'): Collection
    {
        $event = $metric === 'tracking' ? 'tracking_search' : 'page_view';
        $count = $metric === 'visitors' ? 'COUNT(DISTINCT visitor_token)' : 'COUNT(*)';
        $buckets = collect();
        $cursor = $startDate->copy()->startOfDay();
        while ($cursor->lessThanOrEqualTo($endDate)) {
            $until = match ($granularity) {
                'month' => $cursor->copy()->endOfMonth(),
                'week' => $cursor->copy()->addDays(6)->endOfDay(),
                default => $cursor->copy()->endOfDay(),
            };
            if ($until->greaterThan($endDate)) $until = $endDate->copy();
            $buckets->push((object) [
                'start' => $cursor->copy(),
                'end' => $until,
                'label' => $granularity === 'day' ? $cursor->format('d/m') : $cursor->format('d/m') . '–' . $until->format('d/m'),
                'total' => 0,
            ]);
            $cursor = $until->copy()->addDay()->startOfDay();
        }

        if ($buckets->isEmpty()) return $buckets;
        if ($granularity === 'day') {
            $totals = AnalyticsEvent::query()
                ->selectRaw('DATE(occurred_at) as day, ' . $count . ' as total')
                ->where('event_name', $event)
                ->whereBetween('occurred_at', [$startDate, $endDate])
                ->groupBy(DB::raw('DATE(occurred_at)'))->get()->pluck('total', 'day');

            return $buckets->map(function ($bucket) use ($totals) {
                $bucket->total = (int) $totals->get($bucket->start->toDateString(), 0);
                return $bucket;
            });
        }
        $cases = []; $bindings = [];
        foreach ($buckets as $index => $bucket) {
            $cases[] = 'WHEN occurred_at >= ? AND occurred_at <= ? THEN ' . $index;
            $bindings[] = $bucket->start;
            $bindings[] = $bucket->end;
        }
        $totals = AnalyticsEvent::query()
            ->selectRaw('CASE ' . implode(' ', $cases) . ' END as bucket, ' . $count . ' as total', $bindings)
            ->where('event_name', $event)
            ->whereBetween('occurred_at', [$startDate, $endDate])
            ->groupBy('bucket')->get()->pluck('total', 'bucket');

        return $buckets->map(function ($bucket, $index) use ($totals) {
            $bucket->total = (int) $totals->get($index, 0);
            return $bucket;
        });
    }
}
