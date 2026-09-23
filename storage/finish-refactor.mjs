import fs from 'node:fs';
const read=p=>fs.readFileSync(p,'utf8'), write=(p,s)=>fs.writeFileSync(p,s);
let p='resources/views/admin/pages/history.blade.php';
write(p,read(p).replace('{{-- HISTORY_FORMAT --}}',read('resources/views/admin/pages/partials/history-format.blade.php')).replace('$log->before_data','$log->before_state').replace('$log->after_data','$log->after_state'));
p='app/Http/Controllers/AdminAnalyticsController.php';let s=read(p);
const a=s.indexOf('        $dailyVisitorsRaw ='), b=s.indexOf('        $topPages =',a);
s=s.slice(0,a)+`        $trendSeries = $this->buildVisitorTrend($startDate, $endDate, $granularity);
        $comparisonTrendSeries = $this->buildVisitorTrend($previousStartDate, $previousEndDate, $granularity);

`+s.slice(b);
s=s.replace("            'dailyVisitors',\n",'');
s=s.slice(0,s.indexOf('    protected function fillDailySeries'))+`    /** Count each visitor once per displayed bucket, rather than adding daily uniques. */
    protected function buildVisitorTrend(Carbon $startDate, Carbon $endDate, string $granularity): Collection
    {
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
        $cases = []; $bindings = [];
        foreach ($buckets as $index => $bucket) {
            $cases[] = 'WHEN occurred_at >= ? AND occurred_at <= ? THEN ' . $index;
            $bindings[] = $bucket->start;
            $bindings[] = $bucket->end;
        }
        $totals = AnalyticsEvent::query()
            ->selectRaw('CASE ' . implode(' ', $cases) . ' END as bucket, COUNT(DISTINCT visitor_token) as total', $bindings)
            ->where('event_name', 'page_view')
            ->whereBetween('occurred_at', [$startDate, $endDate])
            ->groupBy('bucket')->get()->pluck('total', 'bucket');

        return $buckets->map(function ($bucket, $index) use ($totals) {
            $bucket->total = (int) $totals->get($index, 0);
            return $bucket;
        });
    }
}
`;
write(p,s);
// One canonical user form; list actions navigate to it instead of duplicating every form in modals.
p='resources/views/admin/users/index.blade.php';s=read(p);
s=s.replace(/ x-data="[^\n]+"/,'');
s=s.replace('<button type="button" class="button button-primary" @click="openCreate = true">Nuevo usuario</button>','<a class="button button-primary" href="{{ route(\'admin.users.create\') }}">Nuevo usuario</a>');
s=s.replace('<button type="button" class="button button-secondary" @click="openEdit = {{ $user->id }}">Editar perfil</button>','<a class="button button-secondary" href="{{ route(\'admin.users.edit\', $user) }}">Editar perfil</a>');
s=s.slice(0,s.indexOf('    <div class="admin-modal-backdrop"'))+'</div>\n@endsection\n';
s=s.replace('Usuarios Administradores','Usuarios y accesos').replace('equipo creativo y de contenido','equipo editorial');
s=s.replace(/<h2>/g,'<h1>').replace(/<\/h2>/g,'</h1>');
s=s.replace('<span class="table-note">{{ $users->where(\'is_active\', true)->count() }} activos</span>','<div class="field search-field"><label for="user-search" class="sr-only">Buscar usuarios</label><input id="user-search" type="search" data-table-search placeholder="Buscar nombre, correo o rol…"></div>');
s=s.replace('@foreach ($users as $user)\n                        <tr>','@foreach ($users as $user)\n                        <tr data-search-row>');
s=s.replace('</tbody>','<tr data-search-empty hidden><td colspan="5" class="empty-note">No se encontraron usuarios.</td></tr>\n                </tbody>');
write(p,s);
p='resources/views/admin/users/form.blade.php';s=read(p).replace('panel premium de administracion','panel de administración').replace('Contrasena','Contraseña').replace(/<h2>/g,'<h1>').replace(/<\/h2>/g,'</h1>');
s=s.replace('<label><input type="checkbox" name="is_active"','<input type="hidden" name="is_active" value="0"><label><input type="checkbox" name="is_active"');
s=s.replace('name="password"','name="password" autocomplete="new-password" minlength="8"');
s=s.replace('<button type="submit" class="button button-primary">','<a href="{{ route(\'admin.users.index\') }}" class="button button-ghost">Cancelar</a>\n                <button type="submit" class="button button-primary">');
write(p,s);
p='resources/views/admin/auth/login.blade.php';s=read(p).replace('Que bueno verte de nuevo','Administración del sitio').replace('Inicia sesion para gestionar contenidos.','Correos de Bolivia · Acceso al equipo editorial').replaceAll('Contrasena','Contraseña').replaceAll('contrasena','contraseña').replace('ejemplo@tuempresa.com','tu.correo@correos.gob.bo').replace('name="email"','name="email" autocomplete="username" autofocus').replace('name="password"','name="password" autocomplete="current-password"');
write(p,s);
