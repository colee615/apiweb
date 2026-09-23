<section class="table-shell" id="tracking-searches">
    <div class="table-toolbar">
        <div><strong>Códigos de paquetes consultados</strong><p>{{ number_format($summary['tracking_searches_in_range']) }} consultas en el período · {{ number_format($topTrackingSearches->total()) }} códigos {{ $trackingCode !== '' ? 'encontrados' : 'diferentes' }}.</p></div>
        <form method="GET" action="{{ route('admin.analytics') }}#tracking-searches" class="actions">
            @foreach(['start_date', 'end_date', 'compare_mode', 'granularity', 'chart_metric'] as $filter)<input type="hidden" name="{{ $filter }}" value="{{ $filters[$filter] }}">@endforeach
            <div class="field"><label for="tracking-service" class="sr-only">Filtrar por servicio</label><select id="tracking-service" name="tracking_service"><option value="">Todos los servicios</option>@foreach($trackingByService as $service)<option value="{{ $service->service ?: '__unassigned__' }}" @selected($trackingService === ($service->service ?: '__unassigned__'))>{{ $service->service ?: 'Servicio no identificado' }}</option>@endforeach</select></div>
            <div class="field"><label for="tracking-code" class="sr-only">Buscar código de envío</label><input id="tracking-code" type="search" name="tracking_code" value="{{ $trackingCode }}" placeholder="Buscar un código de envío…" maxlength="160"></div>
            <button class="button button-secondary" type="submit">Buscar</button>
            <a class="button button-ghost" href="{{ route('admin.analytics.tracking.export', ['start_date' => $filters['start_date'], 'end_date' => $filters['end_date'], 'tracking_code' => $trackingCode, 'tracking_service' => $trackingService]) }}">Descargar CSV</a>
            @if($trackingCode !== '')<a class="button button-ghost" href="{{ route('admin.analytics', \Illuminate\Support\Arr::only($filters, ['start_date', 'end_date', 'compare_mode', 'granularity'])) }}#tracking-searches">Limpiar</a>@endif
        </form>
    </div>
    <div class="panel-body">
        <table class="page-table"><thead><tr><th>Código consultado</th><th>Servicio</th><th>Consultas</th><th>Visitantes</th><th>Primera consulta</th><th>Última consulta</th></tr></thead><tbody>
        @forelse($topTrackingSearches as $search)
            <tr><td><strong>{{ $search->searched_term }}</strong></td><td>{{ $search->service ?: 'Servicio no identificado' }}</td><td>{{ number_format($search->total) }}</td><td>{{ number_format($search->visitors) }}</td><td>{{ \Illuminate\Support\Carbon::parse($search->first_seen_at)->format('d/m/Y H:i') }}</td><td>{{ \Illuminate\Support\Carbon::parse($search->last_seen_at)->format('d/m/Y H:i') }}</td></tr>
        @empty<tr><td colspan="6" class="empty-note">{{ $trackingCode !== '' || $trackingService !== '' ? 'No hay búsquedas que coincidan con esos filtros en el período elegido.' : 'No hay búsquedas de seguimiento registradas en este período.' }}</td></tr>@endforelse
        </tbody></table>
    </div>
    @if($topTrackingSearches->hasPages())
    <nav class="table-toolbar" aria-label="Páginas de códigos buscados"><span class="section-copy">Página {{ $topTrackingSearches->currentPage() }} de {{ $topTrackingSearches->lastPage() }}</span><div class="actions">
        @if($topTrackingSearches->previousPageUrl())<a class="button button-ghost" rel="prev" href="{{ $topTrackingSearches->previousPageUrl() }}#tracking-searches">← Anterior</a>@endif
        @if($topTrackingSearches->nextPageUrl())<a class="button button-ghost" rel="next" href="{{ $topTrackingSearches->nextPageUrl() }}#tracking-searches">Siguiente →</a>@endif
    </div></nav>
    @endif
    <div class="panel-body" style="padding:18px 24px">
        <p class="section-copy">Se muestran los códigos que las personas escribieron en el buscador de paquetes. Las fechas corresponden al período elegido. Se agrupan mayúsculas, minúsculas y espacios al inicio o al final. Una consulta no confirma que el paquete exista ni indica su estado de entrega.</p>
        @if($trackingSources->isNotEmpty())<div class="actions" style="margin-top:10px">@foreach($trackingSources as $source)<span class="pill pill-off">{{ $source->source === 'hero_tracking' ? 'Buscador de la portada' : ($source->source ? 'Otro buscador registrado' : 'Origen no indicado') }}: {{ number_format($source->total) }}</span>@endforeach</div>@endif
    </div>
</section>
