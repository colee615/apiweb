<div class="analytics-grid">
    @foreach([
        ['Navegadores', 'Sesiones con visitas, agrupadas por navegador.', $browserBreakdown],
        ['Procedencia de las visitas', 'Dominio del enlace de origen registrado en la sesión.', $referrers],
        ['Botones más utilizados', 'Los 8 botones con más clics en el período.', $topButtons],
    ] as [$heading, $description, $rows])
        <section class="panel panel-body"><div class="section-header"><div><h2 class="section-title">{{ $heading }}</h2><p class="section-copy">{{ $description }}</p></div></div><ol class="ranking">
            @forelse($rows as $row)<li><div class="rank-head"><span>{{ $row->name }}@if(isset($row->page_path))<small>{{ $row->page_path }}</small>@endif</span><strong>{{ number_format($row->total) }}</strong></div><div class="rank-track"><i style="width:{{ $row->total / max(1, $rows->max('total')) * 100 }}%"></i></div></li>@empty<li class="empty-note">No hay datos registrados en este período.</li>@endforelse
        </ol></section>
    @endforeach
    <section class="panel panel-body"><div class="section-header"><div><h2 class="section-title">Lectura del período</h2><p class="section-copy">Promedios calculados con las visitas registradas.</p></div></div>
        <div class="grid grid-2">
            <div class="stat-card"><span>Sesiones del sitio</span><strong>{{ number_format($summary['sessions_in_range']) }}</strong><p>Sesiones con al menos una página vista.</p></div>
            <div class="stat-card"><span>Vistas por sesión</span><strong>{{ $summary['views_per_session'] === null ? '—' : number_format($summary['views_per_session'], 2) }}</strong><p>Promedio de páginas vistas por sesión.</p></div>
            <div class="stat-card"><span>Vistas por día</span><strong>{{ number_format($summary['page_views_in_range'] / max(1, $filters['days']), 1) }}</strong><p>Incluye los días sin visitas.</p></div>
            <div class="stat-card"><span>Consultas por día</span><strong>{{ number_format($summary['tracking_searches_in_range'] / max(1, $filters['days']), 1) }}</strong><p>Búsquedas de códigos de paquetes.</p></div>
        </div>
    </section>
</div>
@if($summary['calculator_quotes_in_range'])
<div class="analytics-grid">
    @foreach($quoteBreakdown as $group)
        <section class="panel panel-body"><div class="section-header"><div><h2 class="section-title">{{ $group['label'] }}</h2><p class="section-copy">Solicitudes en la calculadora del sitio durante el período.</p></div></div><ol class="ranking">
            @foreach($group['rows'] as $row)<li><div class="rank-head"><span>{{ $row->name ?: 'Sin especificar' }}</span><strong>{{ number_format($row->total) }}</strong></div><div class="rank-track"><i style="width:{{ $row->total / max(1, $group['rows']->max('total')) * 100 }}%"></i></div></li>@endforeach
        </ol></section>
    @endforeach
</div>
@endif
