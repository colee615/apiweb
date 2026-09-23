import fs from 'node:fs';
const p='resources/views/admin/analytics/index.blade.php';let s=fs.readFileSync(p,'utf8');
s=s.replace('@php\n',"@php\n    $chartLabel = ['visitors' => 'visitantes', 'views' => 'páginas vistas', 'tracking' => 'búsquedas de paquetes'][$filters['chart_metric']];\n");
s=s.replace('            <button type="submit" class="button button-primary">Aplicar filtros</button>',`            <div class="field"><label for="chart-metric">Dato del gráfico</label><select id="chart-metric" name="chart_metric">@foreach(['visitors' => 'Visitantes', 'views' => 'Páginas vistas', 'tracking' => 'Búsquedas de paquetes'] as $value => $label)<option value="{{ $value }}" @selected($filters['chart_metric'] === $value)>{{ $label }}</option>@endforeach</select></div>
            <button type="submit" class="button button-primary">Aplicar filtros</button>`);
s=s.replace('<section class="panel panel-body" x-data="{ chartView:', '<section id="activity-chart" class="panel panel-body" x-data="{ chartView:');
s=s.replace('Evolución de visitantes','Evolución de {{ $chartLabel }}');
s=s.replace('Cada visitante se cuenta una vez por',"{{ $filters['chart_metric'] === 'visitors' ? 'Cada visitante se cuenta una vez por' : 'Total de eventos por' }}");
s=s.replace('Puede aparecer en varios intervalos.',"{{ $filters['chart_metric'] === 'visitors' ? 'Puede aparecer en varios intervalos.' : 'Incluye las consultas repetidas.' }}");
s=s.replace("{{ $point->total }} visitantes",'{{ $point->total }} {{ $chartLabel }}').replace("{{ $previous->total }} visitantes",'{{ $previous->total }} {{ $chartLabel }}');
s=s.replace('<th>Visitantes</th>','<th>{{ ucfirst($chartLabel) }}</th>');
s=s.replace('No hay visitas registradas en estos períodos. Prueba otro rango de fechas.','No hay actividad registrada en estos períodos. Prueba otro rango de fechas.');
s=s.replace("    @include('admin.analytics.tracking')","    @include('admin.analytics.details')\n    @include('admin.analytics.tracking-summary')\n    @include('admin.analytics.tracking')");
fs.writeFileSync(p,s);
