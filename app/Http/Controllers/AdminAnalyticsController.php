<?php

namespace App\Http\Controllers;

use App\Models\AuthSession;
use App\Models\AnalyticsEvent;
use App\Models\AnalyticsVisitorSession;
use App\Models\SitePageChangeLog;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AdminAnalyticsController extends Controller
{
    public function index(Request $request): View
    {
        [$startDate, $endDate] = $this->resolveDateRange($request);
        $compareMode = $this->resolveCompareMode($request);
        $granularity = $this->resolveGranularity($request, $startDate, $endDate);
        $today = Carbon::today();
        $onlineThreshold = now()->subMinutes(5);
        $rangeDays = $startDate->diffInDays($endDate) + 1;
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

        $dailyVisitorsRaw = AnalyticsEvent::query()
            ->selectRaw('DATE(occurred_at) as day, COUNT(DISTINCT visitor_token) as total')
            ->where('event_name', 'page_view')
            ->whereBetween('occurred_at', [$startDate, $endDate])
            ->groupBy(DB::raw('DATE(occurred_at)'))
            ->orderBy('day')
            ->get();

        $dailyVisitors = $this->fillDailySeries($dailyVisitorsRaw, $startDate, $endDate);
        $trendSeries = $this->buildTrendSeries($dailyVisitors, $startDate, $endDate, $granularity);

        $comparisonVisitorsRaw = AnalyticsEvent::query()
            ->selectRaw('DATE(occurred_at) as day, COUNT(DISTINCT visitor_token) as total')
            ->where('event_name', 'page_view')
            ->whereBetween('occurred_at', [$previousStartDate, $previousEndDate])
            ->groupBy(DB::raw('DATE(occurred_at)'))
            ->orderBy('day')
            ->get();

        $comparisonDailyVisitors = $this->fillDailySeries($comparisonVisitorsRaw, $previousStartDate, $previousEndDate);
        $comparisonTrendSeries = $this->buildTrendSeries($comparisonDailyVisitors, $previousStartDate, $previousEndDate, $granularity);

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

        $topTrackingSearches = AnalyticsEvent::query()
            ->selectRaw('searched_term, COUNT(*) as total, MAX(occurred_at) as last_seen_at')
            ->where('event_name', 'tracking_search')
            ->whereNotNull('searched_term')
            ->where('searched_term', '!=', '')
            ->whereBetween('occurred_at', [$startDate, $endDate])
            ->groupBy('searched_term')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $activeVisitors = AnalyticsVisitorSession::query()
            ->where('is_online', true)
            ->where('last_seen_at', '>=', $onlineThreshold)
            ->latest('last_seen_at')
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
            'days' => $rangeDays,
        ];

        return view('admin.analytics.index', compact(
            'summary',
            'apiwebSessions',
            'dailyVisitors',
            'trendSeries',
            'comparisonTrendSeries',
            'topPages',
            'topInteractions',
            'topTrackingSearches',
            'activeVisitors',
            'recentActivity',
            'filters'
        ));
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

        $startInput = (string) $request->query('start_date', '');
        $endInput = (string) $request->query('end_date', '');

        try {
            $startDate = $startInput !== ''
                ? Carbon::parse($startInput)->startOfDay()
                : $defaultStart;
        } catch (\Throwable) {
            $startDate = $defaultStart;
        }

        try {
            $endDate = $endInput !== ''
                ? Carbon::parse($endInput)->endOfDay()
                : $defaultEnd;
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

        return [$startDate, $endDate];
    }

    protected function resolveCompareMode(Request $request): string
    {
        $mode = (string) $request->query('compare_mode', 'previous');

        return in_array($mode, ['previous', 'month', 'year'], true)
            ? $mode
            : 'previous';
    }

    protected function resolveGranularity(Request $request, Carbon $startDate, Carbon $endDate): string
    {
        $granularity = (string) $request->query('granularity', 'auto');
        $allowed = ['auto', 'day', 'week', 'month'];

        if (!in_array($granularity, $allowed, true)) {
            $granularity = 'auto';
        }

        if ($granularity !== 'auto') {
            return $granularity;
        }

        $rangeDays = $startDate->diffInDays($endDate) + 1;

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
                $startDate->copy()->subYear()->startOfDay(),
                $endDate->copy()->subYear()->endOfDay(),
                $startDate->copy()->subYear()->format('d/m/Y') . ' al ' . $endDate->copy()->subYear()->format('d/m/Y'),
                'Ano vs ano',
            ],
            default => [
                $startDate->copy()->subDay()->subDays($startDate->diffInDays($endDate))->startOfDay(),
                $startDate->copy()->subDay()->endOfDay(),
                $startDate->copy()->subDay()->subDays($startDate->diffInDays($endDate))->format('d/m/Y') . ' al ' . $startDate->copy()->subDay()->format('d/m/Y'),
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
            return ['value' => 100.0, 'direction' => 'up'];
        }

        $delta = round((($current - $previous) / $previous) * 100, 1);

        return [
            'value' => abs($delta),
            'direction' => $delta > 0 ? 'up' : ($delta < 0 ? 'down' : 'neutral'),
        ];
    }

    protected function fillDailySeries(Collection $rows, Carbon $startDate, Carbon $endDate): Collection
    {
        $grouped = $rows->keyBy(fn ($row) => Carbon::parse($row->day)->toDateString());
        $days = collect();

        $cursor = $startDate->copy()->startOfDay();

        while ($cursor->lessThanOrEqualTo($endDate)) {
            $key = $cursor->toDateString();
            $existing = $grouped->get($key);

            $days->push((object) [
                'day' => $key,
                'total' => (int) ($existing->total ?? 0),
            ]);

            $cursor->addDay();
        }

        return $days;
    }

    protected function buildTrendSeries(Collection $dailySeries, Carbon $startDate, Carbon $endDate, string $granularity): Collection
    {
        if ($granularity === 'month') {
            return $dailySeries
                ->groupBy(fn ($row) => Carbon::parse($row->day)->format('Y-m'))
                ->map(function (Collection $rows, string $monthKey) {
                    return (object) [
                        'label' => Carbon::createFromFormat('Y-m', $monthKey)->format('m/Y'),
                        'total' => (int) $rows->sum('total'),
                    ];
                })
                ->values();
        }

        if ($granularity === 'week') {
            return $dailySeries
                ->chunk(7)
                ->map(function (Collection $rows) {
                    $first = Carbon::parse($rows->first()->day);
                    $last = Carbon::parse($rows->last()->day);

                    return (object) [
                        'label' => $first->format('d/m') . ' - ' . $last->format('d/m'),
                        'total' => (int) $rows->sum('total'),
                    ];
                })
                ->values();
        }

        return $dailySeries
            ->map(fn ($row) => (object) [
                'label' => Carbon::parse($row->day)->format('d/m'),
                'total' => (int) $row->total,
            ])
            ->values();
    }
}
