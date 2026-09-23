<section class="panel panel-body" id="tracking-services">
    <div class="section-header"><div><h2 class="section-title">Búsquedas por servicio</h2><p class="section-copy">Cada fila cuenta consultas y códigos distintos del servicio informado por el rastreador.</p></div></div>
    <div class="panel-body">
        <table class="page-table"><thead><tr><th>Servicio</th><th>Consultas</th><th>Códigos distintos</th></tr></thead><tbody>
        @forelse($trackingByService as $service)
            <tr><td><strong>{{ $service->service ?: 'Servicio no identificado' }}</strong></td><td>{{ number_format($service->searches) }}</td><td>{{ number_format($service->codes) }}</td></tr>
        @empty<tr><td colspan="3" class="empty-note">No hay búsquedas por servicio en este período.</td>@endforelse
        </tbody></table>
    </div>
</section>

<section class="panel panel-body" id="tracking-results">
    <div class="section-header"><div><h2 class="section-title">Resultado de las consultas</h2><p class="section-copy">El resultado se cuenta cuando el rastreador lo envía; escribir un código, por sí solo, no confirma que exista o que haya sido entregado.</p></div></div>
    <div class="stat-grid">
        <article class="stat-card"><span>Encontrado por el servicio</span><strong>{{ number_format($trackingResults['found']) }}</strong><p>El proveedor devolvió información del paquete.</p></article>
        <article class="stat-card"><span>Sin coincidencias</span><strong>{{ number_format($trackingResults['not_found']) }}</strong><p>El proveedor indicó que no encontró ese código.</p></article>
        <article class="stat-card"><span>Error al consultar</span><strong>{{ number_format($trackingResults['error']) }}</strong><p>La consulta terminó con un error reportado.</p></article>
    </div>
    <div class="section-header" style="margin-top:20px"><div><h3 class="section-title">Consultas sin éxito</h3><p class="section-copy">Incluye códigos no encontrados y errores reportados por el servicio.</p></div></div>
    <div class="panel-body">
        <table class="page-table"><thead><tr><th>Código</th><th>Servicio</th><th>Resultado</th><th>Fecha y hora</th></tr></thead><tbody>
        @forelse($unsuccessfulTrackingSearches as $search)
            <tr><td><strong>{{ $search->searched_term }}</strong></td><td>{{ $search->service ?: 'Servicio no identificado' }}</td><td>{{ $search->tracking_status === 'not_found' ? 'Sin coincidencias' : 'Error al consultar' }}</td><td>{{ \Illuminate\Support\Carbon::parse($search->occurred_at)->format('d/m/Y H:i') }}</td></tr>
        @empty<tr><td colspan="4" class="empty-note">No hay resultados sin éxito registrados en este período. Las búsquedas antiguas no guardan el servicio ni el resultado.</td>@endforelse
        </tbody></table>
    </div>
    @if($unsuccessfulTrackingSearches->hasPages())
        <nav class="table-toolbar" aria-label="Páginas de consultas sin éxito"><span class="section-copy">Página {{ $unsuccessfulTrackingSearches->currentPage() }} de {{ $unsuccessfulTrackingSearches->lastPage() }}</span><div class="actions">
            @if($unsuccessfulTrackingSearches->previousPageUrl())<a class="button button-ghost" rel="prev" href="{{ $unsuccessfulTrackingSearches->previousPageUrl() }}#tracking-results">← Anterior</a>@endif
            @if($unsuccessfulTrackingSearches->nextPageUrl())<a class="button button-ghost" rel="next" href="{{ $unsuccessfulTrackingSearches->nextPageUrl() }}#tracking-results">Siguiente →</a>@endif
        </div></nav>
    @endif
    <p class="field-help" style="margin-top:14px">Para registrar resultados nuevos, el sitio debe enviar un evento <code>tracking_result</code> al terminar cada consulta, con el servicio y el estado devuelto: <code>found</code>, <code>not_found</code> o <code>error</code>.</p>
</section>
