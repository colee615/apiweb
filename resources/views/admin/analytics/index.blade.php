@extends('layouts.admin')

@section('content')
@php
    $maxDaily = max(1, (int) $dailyVisitors->max('total'));
    $maxTopPages = max(1, (int) $topPages->max('total'));
    $maxTopInteractions = max(1, (int) $topInteractions->max('total'));
    $maxTopTracking = max(1, (int) $topTrackingSearches->max('total'));
    $pageViewsRange = max(1, (int) $summary['page_views_in_range']);
    $trackingRate = $summary['tracking_searches_in_range'] > 0
        ? round(($summary['tracking_searches_in_range'] / $pageViewsRange) * 100, 1)
        : 0;
    $topPageLeader = $topPages->first();
    $topInteractionLeader = $topInteractions->first();
    $topTrackingLeader = $topTrackingSearches->first();
    $engagementTotal = (int) $topInteractions->sum('total');
    $activeUsersTotal = (int) $summary['online_now'] + (int) $summary['apiweb_online_now'];
    $maxTrendTotal = max(
        1,
        (int) max(
            (int) $trendSeries->max('total'),
            (int) $comparisonTrendSeries->max('total')
        )
    );
    $trendLabelStep = max(1, (int) ceil(max(1, $trendSeries->count()) / 8));
    $dailyChartPoints = $trendSeries->values()->map(function ($point, $index) use ($trendSeries, $maxTrendTotal) {
        $count = max(1, $trendSeries->count());
        $x = $count === 1 ? 50 : round(($index / ($count - 1)) * 100, 2);
        $height = min(100, round(($point->total / $maxTrendTotal) * 100, 2));
        $y = round(100 - $height, 2);

        return [
            'x' => $x,
            'y' => $y,
            'height' => $height,
            'total' => (int) $point->total,
            'label' => $point->label,
        ];
    });
    $comparisonChartPoints = $trendSeries->values()->map(function ($point, $index) use ($trendSeries, $comparisonTrendSeries, $maxTrendTotal) {
        $count = max(1, $trendSeries->count());
        $comparisonPoint = $comparisonTrendSeries->values()->get($index);
        $x = $count === 1 ? 50 : round(($index / ($count - 1)) * 100, 2);
        $comparisonTotal = (int) ($comparisonPoint->total ?? 0);
        $height = min(100, round(($comparisonTotal / $maxTrendTotal) * 100, 2));
        $y = round(100 - $height, 2);

        return [
            'x' => $x,
            'y' => $y,
            'height' => $height,
            'total' => $comparisonTotal,
        ];
    });
    $dailyLinePoints = $dailyChartPoints->map(fn ($point) => $point['x'] . ',' . $point['y'])->implode(' ');
    $comparisonLinePoints = $comparisonChartPoints->map(fn ($point) => $point['x'] . ',' . $point['y'])->implode(' ');
    $dailyAreaPoints = trim('0,100 ' . $dailyLinePoints . ' 100,100');

    $formatDelta = function (array $delta): string {
        $prefix = $delta['direction'] === 'up' ? '+' : ($delta['direction'] === 'down' ? '-' : '');
        return $prefix . number_format($delta['value'], 1) . '%';
    };

    $deltaClass = function (array $delta): string {
        return match ($delta['direction']) {
            'up' => 'ga-delta-up',
            'down' => 'ga-delta-down',
            default => 'ga-delta-neutral',
        };
    };
@endphp

<style>
    .ga-shell {
        display: grid;
        gap: 20px;
        min-width: 0;
    }
    .ga-toolbar {
        display: flex;
        justify-content: stretch;
        gap: 18px;
        align-items: stretch;
    }
    .ga-filter-card {
        padding: 20px;
        border-radius: 24px;
        background: linear-gradient(180deg, rgba(255,255,255,.98), rgba(247,249,253,.95));
        border: 1px solid rgba(255,255,255,.9);
        box-shadow: var(--shadow);
        min-width: 0;
        width: 100%;
        max-width: 100%;
    }
    .ga-filter-card form {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 12px;
        align-items: end;
    }
    .ga-filter-head {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 12px;
        margin-bottom: 14px;
    }
    .ga-filter-title {
        margin: 0;
        font-size: 16px;
        font-weight: 800;
        color: #16335f;
    }
    .ga-filter-card .field { margin: 0; }
    .ga-filter-card .button { width: 100%; }
    .ga-range-note {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 12px;
        color: var(--muted);
        font-size: 13px;
    }
    .ga-top {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 18px;
        min-width: 0;
    }
    .ga-stats-strip {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 12px;
        padding: 14px;
        border-radius: 26px;
        background: linear-gradient(180deg, rgba(255,255,255,.98), rgba(247,249,253,.95));
        border: 1px solid rgba(255,255,255,.92);
        box-shadow: var(--shadow);
    }
    .ga-strip-stat,
    .ga-strip-spark {
        min-width: 0;
        border-radius: 20px;
        background: #fff;
        border: 1px solid #e5ecf7;
        padding: 16px 18px;
    }
    .ga-strip-stat span {
        display: block;
        color: var(--muted);
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .07em;
    }
    .ga-strip-stat strong {
        display: block;
        margin-top: 10px;
        color: #10264a;
        font-size: 34px;
        line-height: 1;
    }
    .ga-strip-stat small {
        display: block;
        margin-top: 8px;
        color: var(--muted);
        font-size: 12px;
        line-height: 1.45;
    }
    .ga-strip-spark {
        display: grid;
        align-items: center;
        gap: 10px;
        background: linear-gradient(180deg, #f7faff, #edf3fb);
    }
    .ga-strip-spark-head {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: center;
    }
    .ga-strip-spark-head span {
        color: var(--muted);
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .07em;
    }
    .ga-strip-sparkline {
        display: grid;
        grid-template-columns: repeat(14, minmax(0, 1fr));
        gap: 8px;
        align-items: end;
        min-height: 52px;
    }
    .ga-strip-point {
        height: 100%;
        display: flex;
        align-items: end;
        justify-content: center;
    }
    .ga-strip-point span {
        display: block;
        width: 100%;
        min-height: 8px;
        border-radius: 999px;
        background: linear-gradient(180deg, #68a6ff, #2558a4);
        box-shadow: 0 6px 14px rgba(37, 88, 164, .18);
    }
    .ga-card {
        padding: 24px;
        border-radius: 26px;
        background: linear-gradient(180deg, rgba(255,255,255,.98), rgba(247,249,253,.94));
        border: 1px solid rgba(255,255,255,.92);
        box-shadow: var(--shadow);
        min-width: 0;
        overflow: hidden;
    }
    .ga-card-dark {
        background:
            radial-gradient(circle at top right, rgba(254,204,54,.16), transparent 24%),
            linear-gradient(180deg, #0f2748, #173968 56%, #1d4a84);
        color: #fff;
        box-shadow: 0 20px 42px rgba(15,39,72,.24);
    }
    .ga-card-dark p,
    .ga-card-dark .ga-subtle,
    .ga-card-dark .ga-list-subtle,
    .ga-card-dark .ga-hero-meta { color: rgba(255,255,255,.78); }
    .ga-dark-head {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 18px;
        align-items: flex-start;
        margin-bottom: 22px;
    }
    .ga-dark-copy {
        min-width: 0;
    }
    .ga-dark-title {
        margin: 12px 0 10px;
        font-size: clamp(26px, 3vw, 34px);
        line-height: 1.06;
        letter-spacing: -.03em;
        max-width: 14ch;
    }
    .ga-dark-intro {
        margin: 0;
        max-width: 620px;
        line-height: 1.6;
        color: rgba(255,255,255,.82);
    }
    .ga-dark-meta {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 14px;
    }
    .ga-dark-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 12px;
        border-radius: 999px;
        background: rgba(255,255,255,.09);
        color: #fff;
        font-size: 12px;
        font-weight: 700;
        border: 1px solid rgba(255,255,255,.08);
    }
    .ga-dark-pill strong {
        font-size: inherit;
        margin: 0;
    }
    .ga-dark-side {
        display: grid;
        gap: 10px;
        justify-items: end;
    }
    .ga-dark-focus {
        min-width: 220px;
        padding: 16px 18px;
        border-radius: 20px;
        background: linear-gradient(180deg, rgba(255,255,255,.13), rgba(255,255,255,.08));
        border: 1px solid rgba(255,255,255,.08);
        box-shadow: inset 0 1px 0 rgba(255,255,255,.06);
    }
    .ga-dark-focus span {
        display: block;
        color: rgba(255,255,255,.74);
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .06em;
    }
    .ga-dark-focus strong {
        display: block;
        margin-top: 8px;
        font-size: 28px;
        line-height: 1;
    }
    .ga-dark-focus small {
        display: block;
        margin-top: 8px;
        color: rgba(255,255,255,.72);
        font-size: 12px;
        line-height: 1.45;
    }
    .ga-dark-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }
    .ga-dark-stat {
        padding: 16px 18px;
        border-radius: 18px;
        background: rgba(255,255,255,.07);
        border: 1px solid rgba(255,255,255,.08);
    }
    .ga-dark-stat span {
        display: block;
        color: rgba(255,255,255,.72);
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .06em;
    }
    .ga-dark-stat strong {
        display: block;
        margin-top: 8px;
        font-size: 28px;
        line-height: 1;
    }
    .ga-dark-stat small {
        display: block;
        margin-top: 8px;
        color: rgba(255,255,255,.72);
        line-height: 1.45;
        font-size: 12px;
    }
    .ga-kicker {
        display: inline-flex;
        padding: 8px 12px;
        border-radius: 999px;
        background: #edf4ff;
        color: #1e63c6;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .05em;
    }
    .ga-card-dark .ga-kicker {
        background: rgba(255,255,255,.12);
        color: #fff;
    }
    .ga-title {
        margin: 14px 0 10px;
        font-size: clamp(30px, 3.7vw, 44px);
        line-height: 1;
    }
    .ga-copy {
        margin: 0;
        color: var(--muted);
        line-height: 1.6;
        max-width: 760px;
    }
    .ga-hero-meta {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 12px;
        color: var(--muted);
        font-size: 13px;
    }
    .ga-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-top: 20px;
    }
    .ga-kpi {
        padding: 16px 18px;
        border-radius: 18px;
        background: #fff;
        border: 1px solid #e4ecf8;
    }
    .ga-card-dark .ga-kpi {
        background: rgba(255,255,255,.08);
        border-color: rgba(255,255,255,.1);
    }
    .ga-kpi span {
        display: block;
        color: var(--muted);
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .05em;
    }
    .ga-card-dark .ga-kpi span { color: rgba(255,255,255,.72); }
    .ga-kpi strong {
        display: block;
        margin-top: 8px;
        font-size: 30px;
        line-height: 1;
    }
    .ga-kpi-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-top: 10px;
    }
    .ga-kpi small {
        display: block;
        color: var(--muted);
        font-size: 12px;
    }
    .ga-card-dark .ga-kpi small { color: rgba(255,255,255,.72); }
    .ga-delta {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
    }
    .ga-delta-up { background: #eaf9f2; color: #0c7a58; }
    .ga-delta-down { background: #fff1ec; color: #b54708; }
    .ga-delta-neutral { background: #eef3fb; color: #47607d; }
    .ga-side-stack {
        display: grid;
        gap: 14px;
        min-width: 0;
    }
    .ga-mini-insight {
        padding: 18px;
        border-radius: 20px;
        background: linear-gradient(180deg, #fff, #f8fbff);
        border: 1px solid var(--line);
    }
    .ga-scroll-insight {
        display: grid;
        gap: 14px;
    }
    .ga-scroll-list {
        display: grid;
        gap: 10px;
        max-height: 520px;
        overflow-y: auto;
        padding-right: 4px;
    }
    .ga-scroll-list::-webkit-scrollbar {
        width: 8px;
    }
    .ga-scroll-list::-webkit-scrollbar-thumb {
        background: #d7e3f6;
        border-radius: 999px;
    }
    .ga-scroll-item {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr) auto;
        gap: 12px;
        align-items: center;
        padding: 12px 14px;
        border-radius: 16px;
        background: #fff;
        border: 1px solid #e4ecf8;
    }
    .ga-scroll-index {
        width: 30px;
        height: 30px;
        border-radius: 10px;
        background: #eef4ff;
        color: #1d4fa3;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 800;
    }
    .ga-scroll-copy {
        min-width: 0;
    }
    .ga-scroll-copy strong {
        display: block;
        font-size: 16px;
        line-height: 1.2;
        word-break: break-word;
    }
    .ga-scroll-copy span {
        display: block;
        margin-top: 4px;
        color: var(--muted);
        font-size: 12px;
        line-height: 1.45;
        word-break: break-word;
    }
    .ga-scroll-value {
        color: #173968;
        font-size: 13px;
        font-weight: 800;
        white-space: nowrap;
    }
    .ga-side-stat {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: flex-start;
    }
    .ga-side-stat strong {
        display: block;
        font-size: 28px;
        line-height: 1;
        margin-bottom: 6px;
    }
    .ga-subtle {
        color: var(--muted);
        line-height: 1.55;
        font-size: 14px;
    }
    .ga-chip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 8px 12px;
        border-radius: 999px;
        background: #eef3fb;
        color: var(--accent);
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
    }
    .ga-section-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.35fr) minmax(0, .65fr);
        gap: 18px;
        min-width: 0;
    }
    .ga-section-head {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: flex-start;
        margin-bottom: 18px;
    }
    .ga-section-head p {
        margin: 8px 0 0;
        color: var(--muted);
        line-height: 1.55;
    }
    .ga-chart-area {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(42px, 1fr));
        gap: 10px;
        align-items: end;
        min-height: 250px;
        min-width: 0;
    }
    .ga-chart-toolbar {
        display: inline-flex;
        gap: 8px;
        padding: 4px;
        border-radius: 999px;
        background: #eef3fb;
    }
    .ga-chart-switch {
        border: 0;
        background: transparent;
        color: #5e7291;
        font: inherit;
        font-size: 12px;
        font-weight: 800;
        padding: 8px 12px;
        border-radius: 999px;
        cursor: pointer;
        transition: .2s ease;
    }
    .ga-chart-switch.is-active {
        background: #fff;
        color: #16335f;
        box-shadow: 0 8px 18px rgba(22, 51, 95, .08);
    }
    .ga-chart-panel {
        border-radius: 24px;
        background: linear-gradient(180deg, #fbfdff, #f3f7fd);
        border: 1px solid #e5ecf7;
        padding: 18px;
    }
    .ga-chart-col {
        display: grid;
        gap: 8px;
        align-items: end;
        justify-items: center;
    }
    .ga-chart-track {
        width: 100%;
        height: 180px;
        display: flex;
        align-items: end;
        justify-content: center;
        border-radius: 16px;
        background: linear-gradient(180deg, #f5f8fd, #ebf1fa);
        padding: 8px;
    }
    .ga-chart-fill {
        width: 100%;
        min-height: 10px;
        border-radius: 12px;
        background: linear-gradient(180deg, #67a2f6, #2a6dd1 55%, #173968);
    }
    .ga-chart-val {
        font-size: 12px;
        font-weight: 800;
        color: #173968;
        background: #eef3fb;
        width: 30px;
        height: 30px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .ga-chart-label {
        font-size: 11px;
        font-weight: 700;
        color: var(--muted);
        text-align: center;
        line-height: 1.35;
    }
    .ga-line-chart {
        display: grid;
        gap: 14px;
    }
    .ga-line-stage {
        position: relative;
        height: 280px;
        border-radius: 22px;
        background:
            linear-gradient(180deg, rgba(255,255,255,.92), rgba(236,242,251,.9)),
            repeating-linear-gradient(
                to top,
                transparent 0,
                transparent calc(25% - 1px),
                rgba(27, 78, 161, .06) calc(25% - 1px),
                rgba(27, 78, 161, .06) 25%
            );
        overflow: hidden;
        border: 1px solid #e2eaf7;
    }
    .ga-line-stage svg {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
    }
    .ga-area-fill {
        fill: rgba(58, 120, 217, .16);
    }
    .ga-line-stroke {
        fill: none;
        stroke: #2d6fd0;
        stroke-width: 3;
        stroke-linecap: round;
        stroke-linejoin: round;
    }
    .ga-line-stroke-compare {
        fill: none;
        stroke: rgba(22, 51, 95, .42);
        stroke-width: 2.4;
        stroke-linecap: round;
        stroke-linejoin: round;
        stroke-dasharray: 4 4;
    }
    .ga-line-dot {
        fill: #fff;
        stroke: #1d4fa3;
        stroke-width: 2;
    }
    .ga-line-chart-labels {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(42px, 1fr));
        gap: 10px;
    }
    .ga-line-chart-labels span {
        text-align: center;
        color: var(--muted);
        font-size: 11px;
        font-weight: 700;
    }
    .ga-compare-legend {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 14px;
    }
    .ga-compare-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        border-radius: 999px;
        background: #eef3fb;
        color: #173968;
        font-size: 12px;
        font-weight: 700;
    }
    .ga-compare-pill::before {
        content: "";
        width: 12px;
        height: 3px;
        border-radius: 999px;
        background: #2d6fd0;
        display: inline-block;
    }
    .ga-compare-pill.is-ghost::before {
        background: rgba(22, 51, 95, .42);
    }
    .ga-realtime-list,
    .ga-activity-list,
    .ga-session-list,
    .ga-rank-list { display: grid; gap: 12px; }
    .ga-realtime-list--scroll {
        max-height: 980px;
        overflow-y: auto;
        padding-right: 4px;
    }
    .ga-realtime-list--scroll::-webkit-scrollbar {
        width: 8px;
    }
    .ga-realtime-list--scroll::-webkit-scrollbar-thumb {
        background: #d7e3f6;
        border-radius: 999px;
    }
    .ga-activity-list--scroll {
        max-height: 1320px;
        overflow-y: auto;
        padding-right: 4px;
    }
    .ga-activity-list--scroll::-webkit-scrollbar {
        width: 8px;
    }
    .ga-activity-list--scroll::-webkit-scrollbar-thumb {
        background: #d7e3f6;
        border-radius: 999px;
    }
    .ga-realtime-item,
    .ga-activity-item,
    .ga-session-item {
        padding: 16px 18px;
        border-radius: 18px;
        background: linear-gradient(180deg, #fff, #f8fbff);
        border: 1px solid var(--line);
    }
    .ga-realtime-item strong,
    .ga-activity-item strong,
    .ga-session-item strong { display: block; margin-bottom: 6px; }
    .ga-list-subtle {
        color: var(--muted);
        font-size: 14px;
        line-height: 1.5;
    }
    .ga-badge {
        display: inline-flex;
        margin-top: 10px;
        padding: 6px 10px;
        border-radius: 999px;
        background: #edf4ff;
        color: #1e63c6;
        font-size: 12px;
        font-weight: 800;
    }
    .ga-rank-item {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr) auto;
        gap: 12px;
        align-items: center;
    }
    .ga-rank-index {
        width: 34px;
        height: 34px;
        border-radius: 12px;
        background: #eff4fb;
        color: #173968;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 13px;
    }
    .ga-rank-copy {
        min-width: 0;
    }
    .ga-rank-copy strong {
        display: block;
        margin-bottom: 4px;
        font-size: 15px;
        word-break: break-word;
    }
    .ga-rank-copy span {
        display: block;
        color: var(--muted);
        font-size: 13px;
        line-height: 1.45;
        word-break: break-word;
    }
    .ga-rank-value {
        min-width: 46px;
        text-align: right;
        color: #173968;
        font-weight: 800;
    }
    .ga-rank-bar {
        grid-column: 2 / 4;
        height: 10px;
        border-radius: 999px;
        background: #edf2f8;
        overflow: hidden;
    }
    .ga-rank-bar > div {
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, #2f6fd1, #173968);
    }
    .ga-rank-list--clean {
        gap: 14px;
    }
    .ga-rank-list--clean .ga-rank-item {
        padding-bottom: 12px;
        border-bottom: 1px solid #edf2f8;
    }
    .ga-rank-list--clean .ga-rank-item:last-child {
        padding-bottom: 0;
        border-bottom: 0;
    }
    .ga-rank-list--clean .ga-rank-bar {
        margin-top: 6px;
    }
    .ga-clean-head {
        display: grid;
        gap: 14px;
        margin-bottom: 18px;
    }
    .ga-clean-focus {
        display: grid;
        gap: 6px;
    }
    .ga-clean-focus span {
        color: var(--muted);
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .06em;
    }
    .ga-clean-focus strong {
        color: #10264a;
        font-size: 28px;
        line-height: 1.08;
        word-break: break-word;
    }
    .ga-clean-focus small {
        color: var(--muted);
        font-size: 13px;
        line-height: 1.5;
    }
    .ga-clean-meta {
        display: flex;
        gap: 18px;
        flex-wrap: wrap;
        padding-top: 12px;
        border-top: 1px solid #e8eef8;
    }
    .ga-clean-meta-item {
        display: grid;
        gap: 4px;
        min-width: 110px;
    }
    .ga-clean-meta-item span {
        color: var(--muted);
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .06em;
    }
    .ga-clean-meta-item strong {
        color: #10264a;
        font-size: 22px;
        line-height: 1;
    }
    .ga-summary-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 16px;
    }
    .ga-summary-card {
        padding: 16px;
        border-radius: 18px;
        background: linear-gradient(180deg, #fff, #f8fbff);
        border: 1px solid var(--line);
    }
    .ga-summary-card span {
        display: block;
        color: var(--muted);
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .05em;
    }
    .ga-summary-card strong {
        display: block;
        margin-top: 8px;
        font-size: 20px;
        line-height: 1.2;
    }
    .ga-empty {
        padding: 24px;
        border-radius: 22px;
        border: 1px dashed var(--line);
        background: #fbfcfe;
        color: var(--muted);
        text-align: center;
    }
    @media (max-width: 1400px) {
        .ga-top,
        .ga-section-grid { grid-template-columns: 1fr; }
        .ga-kpi-grid,
        .ga-summary-grid,
        .ga-filter-card form { grid-template-columns: 1fr 1fr; }
    }
    @media (max-width: 1180px) {
        .grid-2,
        .grid-3,
        .ga-kpi-grid,
        .ga-summary-grid,
        .ga-filter-card form { grid-template-columns: 1fr !important; }
    }
    @media (max-width: 900px) {
        .ga-toolbar {
            gap: 14px;
            justify-content: stretch;
        }
        .ga-filter-card {
            width: 100%;
            padding: 18px;
            border-radius: 22px;
        }
        .ga-filter-card {
            padding: 16px;
            border-radius: 20px;
        }
        .ga-card {
            padding: 18px;
            border-radius: 22px;
        }
        .ga-title {
            font-size: clamp(28px, 9vw, 38px);
            line-height: 1.04;
        }
        .ga-dark-head,
        .ga-side-stat,
        .ga-section-head,
        .ga-kpi-row {
            flex-direction: column;
            align-items: flex-start;
        }
        .ga-stats-strip {
            grid-template-columns: 1fr 1fr;
        }
        .ga-strip-spark {
            grid-column: 1 / -1;
        }
        .ga-dark-head {
            grid-template-columns: 1fr;
        }
        .ga-dark-side {
            width: 100%;
            justify-items: stretch;
        }
        .ga-dark-focus {
            min-width: 0;
            width: 100%;
        }
        .ga-dark-grid {
            grid-template-columns: 1fr;
        }
        .ga-chart-area {
            gap: 8px;
            min-height: 210px;
        }
        .ga-chart-toolbar {
            width: 100%;
            justify-content: space-between;
        }
        .ga-chart-track {
            height: 150px;
            border-radius: 14px;
            padding: 6px;
        }
        .ga-line-stage {
            height: 220px;
        }
        .ga-chart-val {
            width: 28px;
            height: 28px;
            font-size: 11px;
        }
        .ga-chart-label {
            font-size: 10px;
        }
        .ga-rank-item {
            grid-template-columns: auto minmax(0, 1fr);
            align-items: start;
        }
        .ga-scroll-item {
            grid-template-columns: auto minmax(0, 1fr);
            align-items: start;
        }
        .ga-scroll-value {
            grid-column: 2;
        }
        .ga-rank-value {
            grid-column: 2;
            text-align: left;
            min-width: 0;
        }
        .ga-rank-bar {
            grid-column: 1 / -1;
        }
    }
    @media (max-width: 640px) {
        .ga-shell {
            gap: 16px;
        }
        .ga-dark-title {
            font-size: 30px;
            line-height: 1.04;
            max-width: none;
        }
        .ga-stats-strip {
            grid-template-columns: 1fr;
            padding: 12px;
        }
        .ga-strip-sparkline {
            gap: 6px;
        }
        .ga-dark-meta {
            gap: 8px;
        }
        .ga-filter-card form,
        .ga-kpi-grid,
        .ga-summary-grid {
            grid-template-columns: 1fr !important;
        }
        .ga-hero-meta,
        .ga-range-note {
            font-size: 12px;
        }
        .ga-copy,
        .ga-section-head p,
        .ga-subtle,
        .ga-list-subtle {
            font-size: 13px;
        }
        .ga-chart-area {
            grid-template-columns: repeat(auto-fit, minmax(34px, 1fr));
            gap: 6px;
        }
        .ga-chart-track {
            height: 128px;
        }
        .ga-line-stage {
            height: 180px;
        }
        .ga-chip,
        .ga-badge,
        .ga-delta {
            white-space: normal;
            text-align: center;
        }
    }
</style>

<div class="admin-shell ga-shell">
    <div class="ga-toolbar">
        <div class="ga-filter-card">
            <div class="ga-filter-head">
                <h3 class="ga-filter-title">Rango de analisis</h3>
            </div>
            <form method="GET" action="{{ route('admin.analytics') }}">
                <div class="field">
                    <label>Fecha inicio</label>
                    <input type="date" name="start_date" value="{{ $filters['start_date'] }}">
                </div>
                <div class="field">
                    <label>Fecha fin</label>
                    <input type="date" name="end_date" value="{{ $filters['end_date'] }}">
                </div>
                <div class="field">
                    <label>Comparacion</label>
                    <select name="compare_mode">
                        <option value="previous" @selected($filters['compare_mode'] === 'previous')>Periodo anterior</option>
                        <option value="month" @selected($filters['compare_mode'] === 'month')>Mes vs mes</option>
                        <option value="year" @selected($filters['compare_mode'] === 'year')>Ano vs ano</option>
                    </select>
                </div>
                <div class="field">
                    <label>Vista temporal</label>
                    <select name="granularity">
                        <option value="day" @selected($filters['granularity'] === 'day')>Diario</option>
                        <option value="week" @selected($filters['granularity'] === 'week')>Semanal</option>
                        <option value="month" @selected($filters['granularity'] === 'month')>Mensual</option>
                    </select>
                </div>
                <button type="submit" class="button button-primary">Aplicar filtro</button>
                <a href="{{ route('admin.analytics') }}" class="button button-secondary">Ultimos 14 dias</a>
            </form>
            <div class="ga-range-note">
                <span><strong>Rango actual:</strong> {{ $filters['label'] }}</span>
                <span><strong>{{ $filters['compare_mode_label'] }}:</strong> {{ $filters['previous_label'] }}</span>
            </div>
        </div>
    </div>

    <div class="stat-grid">
        <div class="stat-card">
            <span>Logueados en apiweb</span>
            <strong>{{ number_format($summary['apiweb_online_now']) }}</strong>
        </div>
        <div class="stat-card">
            <span>Frontweb en linea</span>
            <strong>{{ number_format($summary['online_now']) }}</strong>
        </div>
        <div class="stat-card">
            <span>Visitantes hoy</span>
            <strong>{{ number_format($summary['visitors_today']) }}</strong>
        </div>
        <div class="stat-card">
            <span>Tasa tracking</span>
            <strong>{{ number_format($trackingRate, 1) }}%</strong>
        </div>
    </div>

    <section class="ga-section-grid">
        <div class="ga-card" x-data="{ chartView: 'bars' }">
            <div class="ga-section-head">
                <div>
                    <h3 class="section-title">Tendencia de visitantes</h3>
                    <p>Lee el rango actual con la granularidad elegida y compara contra {{ strtolower($filters['compare_mode_label']) }}.</p>
                </div>
                <div class="ga-chart-toolbar">
                    <button type="button" class="ga-chart-switch" :class="{ 'is-active': chartView === 'bars' }" @click="chartView = 'bars'">Barras</button>
                    <button type="button" class="ga-chart-switch" :class="{ 'is-active': chartView === 'line' }" @click="chartView = 'line'">Linea</button>
                    <button type="button" class="ga-chart-switch" :class="{ 'is-active': chartView === 'area' }" @click="chartView = 'area'">Area</button>
                </div>
            </div>

            @if ($dailyVisitors->isNotEmpty())
                <div class="ga-chart-panel" x-show="chartView === 'bars'">
                    <div class="ga-section-head" style="margin-bottom: 16px;">
                        <span class="ga-chip">Pico del rango {{ number_format($maxTrendTotal) }}</span>
                        <span class="ga-chip">{{ ucfirst($filters['granularity']) }}</span>
                    </div>
                    <div class="ga-chart-area">
                        @foreach ($dailyChartPoints as $point)
                            <div class="ga-chart-col">
                                <span class="ga-chart-val">{{ $point['total'] }}</span>
                                <div class="ga-chart-track">
                                    <div class="ga-chart-fill" style="height: {{ max(8, $point['height']) }}%;"></div>
                                </div>
                                <span class="ga-chart-label">{{ $point['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="ga-chart-panel" x-show="chartView === 'line'">
                    <div class="ga-section-head" style="margin-bottom: 16px;">
                        <span class="ga-chip">Pico del rango {{ number_format($maxTrendTotal) }}</span>
                        <span class="ga-chip">{{ ucfirst($filters['granularity']) }}</span>
                    </div>
                    <div class="ga-line-chart">
                        <div class="ga-line-stage">
                            <svg viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true">
                                <polyline class="ga-line-stroke-compare" points="{{ $comparisonLinePoints }}"></polyline>
                                <polyline class="ga-line-stroke" points="{{ $dailyLinePoints }}"></polyline>
                                @foreach ($dailyChartPoints as $point)
                                    <circle class="ga-line-dot" cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="1.8"></circle>
                                @endforeach
                            </svg>
                        </div>
                        <div class="ga-line-chart-labels">
                            @foreach ($dailyChartPoints as $index => $point)
                                <span>{{ $index % $trendLabelStep === 0 || $loop->last ? $point['label'] : '' }}</span>
                            @endforeach
                        </div>
                        <div class="ga-compare-legend">
                            <span class="ga-compare-pill">Actual: {{ $filters['label'] }}</span>
                            <span class="ga-compare-pill is-ghost">{{ $filters['compare_mode_label'] }}: {{ $filters['previous_label'] }}</span>
                        </div>
                    </div>
                </div>

                <div class="ga-chart-panel" x-show="chartView === 'area'">
                    <div class="ga-section-head" style="margin-bottom: 16px;">
                        <span class="ga-chip">Pico del rango {{ number_format($maxTrendTotal) }}</span>
                        <span class="ga-chip">{{ ucfirst($filters['granularity']) }}</span>
                    </div>
                    <div class="ga-line-chart">
                        <div class="ga-line-stage">
                            <svg viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true">
                                <polyline class="ga-line-stroke-compare" points="{{ $comparisonLinePoints }}"></polyline>
                                <polygon class="ga-area-fill" points="{{ $dailyAreaPoints }}"></polygon>
                                <polyline class="ga-line-stroke" points="{{ $dailyLinePoints }}"></polyline>
                                @foreach ($dailyChartPoints as $point)
                                    <circle class="ga-line-dot" cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="1.8"></circle>
                                @endforeach
                            </svg>
                        </div>
                        <div class="ga-line-chart-labels">
                            @foreach ($dailyChartPoints as $index => $point)
                                <span>{{ $index % $trendLabelStep === 0 || $loop->last ? $point['label'] : '' }}</span>
                            @endforeach
                        </div>
                        <div class="ga-compare-legend">
                            <span class="ga-compare-pill">Actual: {{ $filters['label'] }}</span>
                            <span class="ga-compare-pill is-ghost">{{ $filters['compare_mode_label'] }}: {{ $filters['previous_label'] }}</span>
                        </div>
                    </div>
                </div>
            @else
                <div class="ga-empty">Aun no hay visitas registradas para mostrar una tendencia.</div>
            @endif
        </div>

        <div class="ga-card">
            <div class="ga-section-head">
                <div>
                    <h3 class="section-title">Usuarios activos en frontweb</h3>
                    <p>Sesiones detectadas en los ultimos 5 minutos.</p>
                </div>
                <span class="ga-chip">{{ number_format($summary['online_now']) }} activos</span>
            </div>

            <div class="ga-realtime-list ga-realtime-list--scroll">
                @forelse ($activeVisitors as $visitor)
                    <article class="ga-realtime-item">
                        <strong>{{ $visitor->current_title ?: $visitor->current_path ?: 'Ruta sin identificar' }}</strong>
                        <div class="ga-list-subtle">{{ $visitor->current_path ?: '/' }}</div>
                        <div class="ga-list-subtle">{{ $visitor->device_type ?: 'dispositivo' }} · {{ $visitor->browser ?: 'navegador' }} · ultima actividad {{ optional($visitor->last_seen_at)->format('H:i:s') }}</div>
                        <span class="ga-badge">{{ $visitor->platform ?: 'sistema no detectado' }}</span>
                    </article>
                @empty
                    <div class="ga-empty">No hay usuarios activos en frontweb en este momento.</div>
                @endforelse
            </div>
        </div>
    </section>

    <div class="grid grid-2">
        <section class="ga-card">
            <div class="ga-section-head">
                <div>
                    <h3 class="section-title">Paginas mas visitadas</h3>
                    <p>Top del rango actual para saber donde se concentra el trafico.</p>
                </div>
                <span class="ga-chip">{{ number_format($topPages->sum('total')) }} vistas</span>
            </div>

            <div class="ga-clean-head">
                <div class="ga-clean-focus">
                    <span>Pagina lider</span>
                    <strong>{{ $topPageLeader?->page_name ?: 'Sin datos aun' }}</strong>
                    <small>{{ $topPageLeader?->page_path ?: 'Sin ruta identificada' }}</small>
                </div>
                <div class="ga-clean-meta">
                    <div class="ga-clean-meta-item">
                        <span>Volumen</span>
                        <strong>{{ number_format($topPageLeader?->total ?? 0) }}</strong>
                    </div>
                    <div class="ga-clean-meta-item">
                        <span>Total vistas</span>
                        <strong>{{ number_format($topPages->sum('total')) }}</strong>
                    </div>
                </div>
            </div>

            @if ($topPages->isNotEmpty())
                <div class="ga-rank-list ga-rank-list--clean">
                    @foreach ($topPages as $index => $page)
                        @php($pagePercent = min(100, round(($page->total / $maxTopPages) * 100)))
                        <div class="ga-rank-item">
                            <span class="ga-rank-index">#{{ $index + 1 }}</span>
                            <div class="ga-rank-copy">
                                <strong>{{ $page->page_name ?: $page->page_path }}</strong>
                                <span>{{ $page->page_path }}</span>
                            </div>
                            <span class="ga-rank-value">{{ number_format($page->total) }}</span>
                            <div class="ga-rank-bar"><div style="width: {{ max(8, $pagePercent) }}%;"></div></div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="ga-empty">Sin datos aun.</div>
            @endif
        </section>

        <section class="ga-card">
            <div class="ga-section-head">
                <div>
                    <h3 class="section-title">Partes mas usadas</h3>
                    <p>Interacciones por seccion para ver donde hay mayor interes dentro del sitio.</p>
                </div>
                <span class="ga-chip">{{ number_format($engagementTotal) }} acciones</span>
            </div>

            <div class="ga-clean-head">
                <div class="ga-clean-focus">
                    <span>Seccion lider</span>
                    <strong>{{ $topInteractionLeader?->section_name ?: 'Sin datos aun' }}</strong>
                    <small>{{ $topInteractionLeader ? 'Zona con mejor respuesta del usuario' : 'Esperando actividad en el rango' }}</small>
                </div>
                <div class="ga-clean-meta">
                    <div class="ga-clean-meta-item">
                        <span>Acciones lider</span>
                        <strong>{{ number_format($topInteractionLeader?->total ?? 0) }}</strong>
                    </div>
                    <div class="ga-clean-meta-item">
                        <span>Lectura</span>
                        <strong>{{ $topInteractionLeader ? 'Alta' : 'Esperando' }}</strong>
                    </div>
                </div>
            </div>

            @if ($topInteractions->isNotEmpty())
                <div class="ga-rank-list ga-rank-list--clean">
                    @foreach ($topInteractions as $index => $interaction)
                        @php($interactionPercent = min(100, round(($interaction->total / $maxTopInteractions) * 100)))
                        <div class="ga-rank-item">
                            <span class="ga-rank-index">#{{ $index + 1 }}</span>
                            <div class="ga-rank-copy">
                                <strong>{{ $interaction->section_name }}</strong>
                                <span>Zona con clics, tracking o cotizaciones</span>
                            </div>
                            <span class="ga-rank-value">{{ number_format($interaction->total) }}</span>
                            <div class="ga-rank-bar"><div style="width: {{ max(8, $interactionPercent) }}%;"></div></div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="ga-empty">Sin interacciones aun.</div>
            @endif
        </section>

    </div>

    <section class="ga-section-grid">
        <div class="ga-card">
            <div class="ga-section-head">
                <div>
                    <h3 class="section-title">Actividad interna de apiweb</h3>
                    <p>Sesiones del panel y usuarios autenticados consumiendo la API en tiempo reciente.</p>
                </div>
                <span class="ga-chip">{{ number_format($summary['apiweb_online_now']) }} conectados</span>
            </div>

            <div class="ga-summary-grid">
                <div class="ga-summary-card">
                    <span>Admin web</span>
                    <strong>{{ number_format($summary['apiweb_admin_online_now']) }}</strong>
                </div>
                <div class="ga-summary-card">
                    <span>API JWT</span>
                    <strong>{{ number_format($summary['apiweb_api_online_now']) }}</strong>
                </div>
                <div class="ga-summary-card">
                    <span>Total apiweb</span>
                    <strong>{{ number_format($summary['apiweb_online_now']) }}</strong>
                </div>
            </div>

            <div class="ga-session-list">
                @forelse ($apiwebSessions as $session)
                    <article class="ga-session-item">
                        <strong>{{ $session->user?->name ?: $session->user?->email ?: 'Usuario sin nombre' }}</strong>
                        <div class="ga-list-subtle">{{ $session->guard_name === 'admin_web' ? 'Panel admin' : 'API JWT' }} · {{ $session->last_path ?: 'ruta no disponible' }}</div>
                        <div class="ga-list-subtle">{{ $session->ip_address ?: 'IP no disponible' }} · ultima actividad {{ optional($session->last_seen_at)->format('H:i:s') }}</div>
                    </article>
                @empty
                    <div class="ga-empty">No hay usuarios logueados en apiweb en este momento.</div>
                @endforelse
            </div>
        </div>

        <div class="ga-card">
            <div class="ga-section-head">
                <div>
                    <h3 class="section-title">Cambios y movimientos recientes</h3>
                    <p>Bitacora breve de cambios del CMS y actividad relevante en apiweb para el periodo filtrado.</p>
                </div>
                <span class="ga-chip">{{ number_format($recentActivity->count()) }} eventos</span>
            </div>

            <div class="ga-activity-list ga-activity-list--scroll">
                @forelse ($recentActivity as $activity)
                    <article class="ga-activity-item">
                        <strong>{{ $activity['title'] }}</strong>
                        <div class="ga-list-subtle">{{ $activity['subtitle'] }}</div>
                        <div class="ga-list-subtle">{{ $activity['actor'] }} · {{ optional($activity['occurred_at'])->format('d/m/Y H:i') }}</div>
                        <span class="ga-badge">{{ $activity['detail'] }}</span>
                    </article>
                @empty
                    <div class="ga-empty">Todavia no hay movimientos registrados en este rango.</div>
                @endforelse
            </div>
        </div>
    </section>
</div>
@endsection
