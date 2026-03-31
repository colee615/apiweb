@extends('layouts.admin')

@php
    $announcement = $editorData['announcement_modal'];
    $header = $editorData['header'];
    $hero = $editorData['hero'];
    $services = $editorData['services'];
    $status = $editorData['status'];
    $tools = $editorData['tools'];
    $appBanner = $editorData['app_banner'];
    $market = $editorData['market'];
    $footer = $editorData['footer'];
    $historySections = $historyData['history_sections'];
    $address = explode('|', $footer['settings']['address'] ?? '|');
    $phone = explode('|', $footer['settings']['phone'] ?? '|');
    $currentVersionNumber = $page->latest_version ?? optional($versions->first())->version_number;
    $historySectionLabels = collect($historySections)->pluck('label', 'key')->all();
    $historySectionLabels['general'] = 'General';
    $historyActionLabels = [
        'created' => 'Creacion',
        'updated' => 'Actualizacion',
        'deleted' => 'Eliminacion',
        'restored' => 'Restauración',
    ];
    $historyFieldLabels = [
        'slug' => 'Slug',
        'name' => 'Nombre',
        'title' => 'Título',
        'subtitle' => 'Subtítulo',
        'text' => 'Texto',
        'description' => 'Descripción',
        'label' => 'Etiqueta',
        'placeholder' => 'Placeholder',
        'button_label' => 'Texto del botón',
        'tracking_title' => 'Título de rastreo',
        'tracking_text' => 'Texto de rastreo',
        'tracking_label' => 'Etiqueta de rastreo',
        'tracking_placeholder' => 'Placeholder de rastreo',
        'tracking_button' => 'Botón de rastreo',
        'view_all_label' => 'Texto de ver todo',
        'view_all_url' => 'Enlace de ver todo',
        'app_store_label' => 'Texto App Store',
        'play_store_label' => 'Texto Google Play',
        'app_store_url' => 'Enlace App Store',
        'play_store_url' => 'Enlace Google Play',
        'map_title' => 'Título del mapa',
        'map_text' => 'Texto del mapa',
        'map_button_label' => 'Botón del mapa',
        'maps_url' => 'Google Maps URL',
        'weekday_hours' => 'Horario de lunes a viernes',
        'saturday_hours' => 'Horario de sábado',
        'calculator_title' => 'Título de calculadora',
        'calculator_text' => 'Texto de calculadora',
        'origin_label' => 'Etiqueta de origen',
        'origin_placeholder' => 'Placeholder de origen',
        'destination_label' => 'Etiqueta de destino',
        'destination_placeholder' => 'Placeholder de destino',
        'weight_label' => 'Etiqueta de peso',
        'weight_placeholder' => 'Placeholder de peso',
        'calculate_button_label' => 'Botón de calcular',
        'help_label' => 'Ayuda / contacto',
        'login_label' => 'Inicio de sesión',
        'search_placeholder' => 'Placeholder de búsqueda',
        'language_primary' => 'Idioma principal',
        'language_secondary' => 'Idioma secundario',
        'accessibility_label' => 'Etiqueta de accesibilidad',
        'url' => 'Enlace',
        'src' => 'Imagen o archivo',
        'poster' => 'Portada',
        'poster_image' => 'Imagen principal',
        'poster_title' => 'Título del popup',
        'poster_caption' => 'Pie del popup',
        'icon' => 'Icono',
        'iconImage' => 'Imagen del icono',
        'image' => 'Imagen',
        'background_image' => 'Imagen de fondo',
        'price' => 'Precio',
        'year' => 'Año',
        'series' => 'Serie',
        'dept' => 'Código de departamento',
        'group' => 'Grupo',
        'media_type' => 'Tipo de medio',
        'phone' => 'Teléfono',
        'email' => 'Correo',
        'address' => 'Dirección',
        'copyright' => 'Copyright',
        'legal_text' => 'Texto legal',
        'help_title' => 'Título de ayuda',
        'company_title' => 'Título de empresa',
        'contact_title' => 'Título de contacto',
        'social_title' => 'Título de redes',
        'social_text' => 'Texto de redes',
        'logo_url' => 'Logo',
        'primary_color' => 'Color principal',
        'secondary_color' => 'Color secundario',
        'accent_color' => 'Color de acento',
        'enabled' => 'Visibilidad',
        'show_once' => 'Mostrar solo una vez',
        'storage_key' => 'Clave de control',
        'settings' => 'Configuración',
        'data' => 'Contenido',
        'theme' => 'Identidad visual',
        'page_meta' => 'Configuración general',
        'is_active' => 'Estado',
        'sort_order' => 'Orden',
        'type' => 'Tipo',
        'key' => 'Clave',
        'left' => 'Posición izquierda',
        'top' => 'Posición superior',
    ];
    $historyIgnoredKeys = ['id', 'page_id', 'section_id', 'item_id', 'created_at', 'updated_at'];
    $historyAssetFields = ['src', 'poster', 'poster_image', 'iconImage', 'image', 'background_image', 'logo_url'];
    $isAssocHistoryArray = function (array $value): bool {
        return array_keys($value) !== range(0, count($value) - 1);
    };
    $formatHistoryPath = function (array $segments) use ($historyFieldLabels) {
        $labels = collect($segments)
            ->filter(fn ($segment) => filled($segment))
            ->map(function ($segment) use ($historyFieldLabels) {
                if (is_int($segment)) {
                    return 'Elemento ' . ($segment + 1);
                }

                if (is_string($segment) && str_starts_with($segment, '#')) {
                    return 'Elemento ' . substr($segment, 1);
                }

                return $historyFieldLabels[$segment] ?? ucfirst(str_replace('_', ' ', (string) $segment));
            })
            ->values()
            ->all();

        return implode(' > ', $labels);
    };
    $describeHistoryValue = function ($value, array $segments = []) use ($historyAssetFields) {
        $field = end($segments) ?: null;

        if ($value === null || $value === '') {
            return 'Sin valor';
        }

        if (is_bool($value)) {
            return $value ? 'Activo' : 'Inactivo';
        }

        if (is_array($value)) {
            return count($value) . ' elemento(s)';
        }

        $text = trim((string) $value);

        if (in_array($field, $historyAssetFields, true) || filter_var($text, FILTER_VALIDATE_URL)) {
            $path = parse_url($text, PHP_URL_PATH) ?: $text;
            $filename = basename($path);
            return $filename ? 'Archivo: ' . $filename : 'Archivo o recurso vinculado';
        }

        return \Illuminate\Support\Str::limit($text, 160);
    };
    $buildHistoryDiff = function ($before, $after, array $segments = []) use (&$buildHistoryDiff, $historyIgnoredKeys, $isAssocHistoryArray, $formatHistoryPath, $describeHistoryValue, $historyAssetFields) {
        $changes = [];

        if (is_array($before) || is_array($after)) {
            $beforeArray = is_array($before) ? $before : [];
            $afterArray = is_array($after) ? $after : [];

            if ($isAssocHistoryArray($beforeArray ?: $afterArray)) {
                $keys = collect(array_keys($beforeArray))
                    ->merge(array_keys($afterArray))
                    ->unique()
                    ->reject(fn ($key) => in_array($key, $historyIgnoredKeys, true))
                    ->values();

                foreach ($keys as $key) {
                    $changes = array_merge(
                        $changes,
                        $buildHistoryDiff($beforeArray[$key] ?? null, $afterArray[$key] ?? null, [...$segments, $key])
                    );
                }

                return $changes;
            }

            $max = max(count($beforeArray), count($afterArray));

            for ($index = 0; $index < $max; $index++) {
                $changes = array_merge(
                    $changes,
                    $buildHistoryDiff($beforeArray[$index] ?? null, $afterArray[$index] ?? null, [...$segments, $index])
                );
            }

            return $changes;
        }

        if ($before === $after) {
            return [];
        }

        $field = end($segments) ?: null;
        $isAsset = in_array($field, $historyAssetFields, true);
        $changeType = 'Actualizado';

        if (($before === null || $before === '') && ($after !== null && $after !== '')) {
            $changeType = 'Agregado';
        } elseif (($after === null || $after === '') && ($before !== null && $before !== '')) {
            $changeType = 'Eliminado';
        } elseif ($isAsset) {
            $changeType = 'Imagen o archivo reemplazado';
        } elseif (is_string($before) || is_string($after)) {
            $changeType = 'Texto actualizado';
        }

        return [[
            'label' => $formatHistoryPath($segments),
            'type' => $changeType,
            'before' => $describeHistoryValue($before, $segments),
            'after' => $describeHistoryValue($after, $segments),
        ]];
    };
@endphp

@section('content')
    <div
        class="admin-shell stack"
        x-data="{
        tab: @js(request('tab', 'design_text')),
        errorModalOpen: @js($errors->any()),
        go(section) {
            this.tab = section;
            if (window.history && window.history.replaceState) {
                const url = new URL(window.location.href);
                url.searchParams.set('tab', section);
                if (section.startsWith('history')) {
                    url.hash = 'history-root';
                } else {
                    url.hash = '';
                }
                window.history.replaceState({}, '', url.toString());
            }

            if (section.startsWith('history')) {
                this.$nextTick(() => {
                    const target = document.getElementById('history-root');
                    if (target) {
                        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                });
                return;
            }

            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
        isHistoryTab() {
            return this.tab.startsWith('history');
        }
    }"
>
    <div class="admin-topbar">
        <div class="admin-brand">
            <h2>Editor Studio de {{ $page->name }}</h2>
            <p>Una experiencia visual más clara para diseño, contenido, medios e historial sin tocar código.</p>
        </div>
        <div class="actions">
            <a href="{{ route('admin.dashboard') }}" class="button button-secondary">Volver al panel</a>
            <form method="POST" action="{{ route('admin.logout') }}">@csrf<button type="submit" class="button button-ghost">Cerrar sesión</button></form>
        </div>
    </div>

    <div class="panel hero-panel">
        <div class="split-header">
            <div style="max-width: 760px;">
                <div class="section-eyebrow">Creative workspace</div>
                <h1 style="margin:14px 0 10px; font-size:40px; line-height:1;">Control visual total de la página</h1>
                <p class="section-copy">Edita encabezado, portada, servicios, productos, pie de página y estilos desde una mesa de trabajo más ordenada, elegante y preparada para gestión editorial real.</p>
            </div>
            <div class="section-metrics">
                <span class="pill {{ $page->is_active ? 'pill-ok' : 'pill-off' }}">{{ $page->is_active ? 'Publicada' : 'Oculta' }}</span>
                <span class="pill pill-off">{{ count($header['links']) }} enlaces</span>
                <span class="pill pill-off">{{ count($services['items']) }} servicios</span>
                <span class="pill pill-off">{{ count($market['items']) }} productos</span>
            </div>
        </div>
    </div>

    <div class="card-grid">
        <div class="spot-card">
            <span>Enlaces</span>
            <strong>{{ count($header['links']) }}</strong>
            <p>Navegación principal configurada para esta página.</p>
        </div>
        <div class="spot-card">
            <span>Servicios</span>
            <strong>{{ count($services['items']) }}</strong>
            <p>Bloques de servicios listos para edición visual.</p>
        </div>
        <div class="spot-card">
            <span>Historial</span>
            <strong>{{ $historyData['total_changes'] }}</strong>
            <p>Cambios acumulados con trazabilidad y restauración.</p>
        </div>
    </div>

    @if (session('status'))<div class="notice notice-success">{{ session('status') }}</div>@endif
    @if ($errors->any())
        <div
            class="admin-modal-backdrop"
            x-cloak
            x-show="errorModalOpen"
            x-transition.opacity
            @keydown.escape.window="errorModalOpen = false"
        >
            <div class="admin-modal-card" @click.away="errorModalOpen = false">
                <button type="button" class="admin-modal-close" @click="errorModalOpen = false" aria-label="Cerrar">x</button>
                <div class="admin-modal-head">
                    <div class="admin-modal-icon">!</div>
                    <div class="admin-modal-copy">
                        <span class="admin-modal-kicker">Revisa estos puntos</span>
                        <h3>No pudimos guardar los cambios</h3>
                        <p>Encontramos datos incompletos o archivos que no cumplen con el formato permitido. Corrige estos puntos y vuelve a guardar.</p>
                    </div>
                </div>
                <div class="admin-modal-list">
                    @foreach ($errors->all() as $error)
                        <div class="admin-modal-item">{{ $error }}</div>
                    @endforeach
                </div>
                <div class="admin-modal-actions">
                    <button type="button" class="button button-primary" @click="errorModalOpen = false">Entendido</button>
                </div>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.pages.update', $page) }}" class="stack" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="editor-layout">
            <aside class="editor-sidebar">
                <div class="editor-nav" x-show="!isHistoryTab()">
                    <h3>Secciones de diseño</h3>
                    <p>En este modo solo ves herramientas de edición. El historial queda separado en su propio submenú.</p>

                    <div class="editor-nav-list">
                        <button type="button" class="editor-nav-button" :class="{ 'active': tab === 'announcement' }" @click="go('announcement')"><strong>Popup de inicio</strong><span>Imagen institucional al abrir</span></button>
                        <button type="button" class="editor-nav-button" :class="{ 'active': tab === 'design_text' }" @click="go('design_text')"><strong>Diseño</strong><span>Textos, logo y enlaces</span></button>
                        <button type="button" class="editor-nav-button" :class="{ 'active': tab === 'backgrounds' }" @click="go('backgrounds')"><strong>Fondos</strong><span>Carrusel de imágenes o videos</span></button>
                        <button type="button" class="editor-nav-button" :class="{ 'active': tab === 'services' }" @click="go('services')"><strong>Servicios</strong><span>Agregar, quitar y ordenar</span></button>
                        <button type="button" class="editor-nav-button" :class="{ 'active': tab === 'banner' }" @click="go('banner')"><strong>Banner</strong><span>Imagen directa del bloque app</span></button>
                        <button type="button" class="editor-nav-button" :class="{ 'active': tab === 'market' }" @click="go('market')"><strong>Filatelia</strong><span>Productos y colecciones</span></button>
                        <button type="button" class="editor-nav-button" :class="{ 'active': tab === 'footer' }" @click="go('footer')"><strong>Footer</strong><span>Textos, URL y logo</span></button>
                    </div>
                </div>

                <div class="editor-nav" style="margin-top:18px;" x-show="isHistoryTab()">
                    <h3>Historial</h3>
                    <p>Cada guardado genera una versión con usuario, fecha y resumen del cambio.</p>

                    <div class="stack" style="gap:12px;">
                        @forelse ($versions as $version)
                            @php($isCurrentVersion = (int) $version->version_number === (int) $currentVersionNumber)
                            <div class="repeater-card" style="padding:14px;">
                                <div style="display:flex; justify-content:space-between; gap:10px; align-items:flex-start;">
                                    <div>
                                        <strong>Versión {{ $version->version_number }}</strong>
                                        <div style="font-size:12px; color:#6b7280; margin-top:4px;">
                                            {{ $historyActionLabels[$version->action] ?? ucfirst($version->action) }} · {{ optional($version->created_at)->format('d/m/Y H:i') }}
                                        </div>
                                    </div>
                                    <span class="pill {{ $version->action === 'restored' ? 'pill-ok' : 'pill-off' }}">
                                        {{ $historyActionLabels[$version->action] ?? ucfirst($version->action) }}
                                    </span>
                                </div>
                                @if ($isCurrentVersion)
                                    <div style="margin-top:10px;">
                                        <span class="pill pill-ok">Versión actual</span>
                                    </div>
                                @endif

                                <div style="margin-top:10px; font-size:13px; color:#4b5563;">
                                    <div><strong>Responsable:</strong> {{ $version->created_by_name ?: 'Sistema' }}</div>
                                    @if ($version->change_summary)
                                        <div style="margin-top:6px;"><strong>Resumen editorial:</strong> {{ $version->change_summary }}</div>
                                    @endif
                                    <div style="margin-top:6px;"><strong>Cambios incluidos:</strong> {{ $version->changeLogs->count() }}</div>
                                </div>

                                @if ($isCurrentVersion)
                                    <div class="button button-secondary" style="width:100%; margin-top:12px; opacity:.78; cursor:default;">Versión actual publicada</div>
                                @else
                                    <form method="POST" action="{{ route('admin.pages.restore', [$page, $version]) }}" style="margin-top:12px;">
                                        @csrf
                                        <input type="hidden" name="change_summary" value="Restauración desde la versión {{ $version->version_number }}">
                                        <button type="submit" class="button button-secondary" style="width:100%;">Volver a esta versión</button>
                                    </form>
                                @endif
                            </div>
                        @empty
                            <div class="empty-note">Todavía no hay versiones registradas.</div>
                        @endforelse
                    </div>
                </div>
                <div class="editor-nav" style="margin-top:18px;" x-show="isHistoryTab()">
                    <h3>Historial por sección</h3>
                    <p>Submenús separados para navegar el historial completo de cada parte del sitio.</p>

                    <div class="editor-nav-list">
                        <button type="button" class="editor-nav-button" :class="{ 'active': tab === 'history_overview' }" @click="go('history_overview')">
                            <strong>Resumen general</strong>
                            <span>{{ $historyData['total_changes'] }} cambios registrados</span>
                        </button>
                        @foreach ($historySections as $historySection)
                            @continue($historySection['key'] === 'general')
                            <button type="button" class="editor-nav-button" :class="{ 'active': tab === 'history_{{ $historySection['key'] }}' }" @click="go('history_{{ $historySection['key'] }}')">
                                <strong>{{ $historySection['label'] }}</strong>
                                <span>{{ $historySection['count'] }} eventos</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            </aside>

            <div class="editor-main">
                <section class="section-card" x-show="tab === 'announcement'">
                    <div class="section-header">
                        <div>
                            <div class="section-eyebrow">Startup announcement</div>
                            <h3 class="section-title">Popup de inicio</h3>
                            <p class="section-copy">Carga uno o varios afiches institucionales. Si solo existe uno, se mostrará como pieza única; si agregas varios, el frontend los mostrará como una secuencia elegante.</p>
                        </div>
                        <div class="section-metrics">
                            <span class="pill {{ count($announcement['items'] ?? []) ? 'pill-ok' : 'pill-off' }}">{{ count($announcement['items'] ?? []) ? count($announcement['items']) . ' popup(s)' : 'Sin popups' }}</span>
                            <span class="pill {{ !empty($announcement['settings']['enabled']) ? 'pill-ok' : 'pill-off' }}">{{ !empty($announcement['settings']['enabled']) ? 'Activo' : 'Inactivo' }}</span>
                        </div>
                    </div>

                    <div class="design-grid">
                        <div class="subpanel span-4">
                            <h4>Visibilidad</h4>
                            <p>Activa o desactiva el popup sin tocar código.</p>
                            <div class="stack" style="gap: 12px;">
                                <label style="display:flex; gap:10px; align-items:center; font-weight:700;">
                                    <input type="checkbox" name="announcement_modal[enabled]" value="1" {{ !empty($announcement['settings']['enabled']) ? 'checked' : '' }}>
                                    Mostrar popup al cargar
                                </label>
                                <label style="display:flex; gap:10px; align-items:center; font-weight:700;">
                                    <input type="checkbox" name="announcement_modal[show_once]" value="1" {{ !empty($announcement['settings']['show_once']) ? 'checked' : '' }}>
                                    Mostrar solo una vez por navegador
                                </label>
                                <div class="field">
                                    <label>Clave de control</label>
                                    <input type="text" name="announcement_modal[storage_key]" value="{{ old('announcement_modal.storage_key', $announcement['settings']['storage_key'] ?? 'cb-home-announcement') }}">
                                </div>
                            </div>
                        </div>

                        <div class="subpanel span-8">
                            <div class="toolbar">
                                <div>
                                    <h4>Secuencia de popups</h4>
                                    <p>Ordena varias piezas como si fueran una baraja. La primera será la portada inicial y el usuario podrá recorrer las demás.</p>
                                </div>
                                <button type="button" class="button button-secondary" data-add-row>Agregar popup</button>
                            </div>
                            <div class="stack" data-collection data-base="announcement_modal[items]" data-template="announcement-template">
                                <div data-rows>
                                    @forelse (($announcement['items'] ?? []) as $item)
                                        <div class="repeater-card" data-row>
                                            <div class="toolbar">
                                                <div class="actions">
                                                    <span class="drag-handle" data-drag>::</span>
                                                    <strong>{{ $item['title'] ?? 'Popup institucional' }}</strong>
                                                </div>
                                                <button type="button" class="button button-danger" data-remove-row>Eliminar</button>
                                            </div>
                                            <div class="grid grid-2" style="margin-top:12px;">
                                                <div class="field"><label>Nombre interno</label><input type="text" data-field="title" value="{{ $item['title'] ?? '' }}"></div>
                                                <div class="field"><label>Texto alternativo</label><input type="text" data-field="poster_alt" value="{{ $item['poster_alt'] ?? 'Comunicado institucional' }}"></div>
                                                <div class="field"><label>Imagen actual</label><input type="text" data-field="poster_image" value="{{ $item['poster_image'] ?? '' }}"></div>
                                                <div class="field"><label>Subir imagen</label><input type="file" data-field="poster_file" accept="image/*" data-preview-input></div>
                                                <div class="field"><label>Título visible</label><input type="text" data-field="poster_title" value="{{ $item['poster_title'] ?? '' }}"></div>
                                                <div class="field"><label>Pie o detalle</label><input type="text" data-field="poster_caption" value="{{ $item['poster_caption'] ?? '' }}"></div>
                                            </div>
                                            @if (!empty($item['poster_image']))
                                                <img class="thumb" data-preview-image src="{{ $item['poster_image'] }}" style="display:block; margin-top:14px; max-width: 320px; aspect-ratio: auto;" alt="Preview">
                                            @else
                                                <img class="thumb" data-preview-image style="display:none; margin-top:14px; max-width: 320px; aspect-ratio: auto;" alt="Preview">
                                            @endif
                                            <input type="hidden" data-field="id" value="{{ $item['id'] ?? '' }}">
                                        </div>
                                    @empty
                                        <div class="empty-note">No hay popups cargados todavía. Agrega el primero para mostrarlo al abrir la página.</div>
                                    @endforelse
                                </div>
                            </div>

                            <div class="image-frame" style="margin-top:16px;">
                                <strong>Compatibilidad con la versión anterior</strong>
                                <p style="margin:0; color:#667085;">Los campos antiguos se conservan para no perder contenido guardado previamente. Si ya estás usando varios popups, puedes dejar esta base vacía.</p>
                                <div class="grid grid-2">
                                    <div class="field">
                                        <label>URL imagen base</label>
                                        <input type="text" name="announcement_modal[poster_image]" value="{{ old('announcement_modal.poster_image', $announcement['settings']['poster_image'] ?? '') }}">
                                    </div>
                                    <div class="field">
                                        <label>Subir imagen base</label>
                                        <input type="file" name="announcement_modal[poster_file]" accept="image/*">
                                    </div>
                                    <div class="field">
                                        <label>Texto alternativo base</label>
                                        <input type="text" name="announcement_modal[poster_alt]" value="{{ old('announcement_modal.poster_alt', $announcement['settings']['poster_alt'] ?? 'Comunicado institucional') }}">
                                    </div>
                                    <div class="field">
                                        <label>Título base</label>
                                        <input type="text" name="announcement_modal[poster_title]" value="{{ old('announcement_modal.poster_title', $announcement['settings']['poster_title'] ?? '') }}">
                                    </div>
                                </div>
                                <div class="field">
                                    <label>Pie base</label>
                                    <input type="text" name="announcement_modal[poster_caption]" value="{{ old('announcement_modal.poster_caption', $announcement['settings']['poster_caption'] ?? '') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="section-card" x-show="tab === 'design_text'">
                    <div class="section-header">
                        <div>
                            <div class="section-eyebrow">Base de marca</div>
                            <h3 class="section-title">Configuración general</h3>
                            <p class="section-copy">Gestiona identidad visual, SEO y estado de publicación con criterios más claros para edición ejecutiva.</p>
                        </div>
                        <div class="section-metrics">
                            <span class="pill pill-off">Logo</span>
                            <span class="pill pill-off">Colores</span>
                            <span class="pill pill-off">SEO</span>
                        </div>
                    </div>

                    <div class="spec-grid" style="margin-bottom:16px;">
                        <div class="spec-card">
                            <strong>Título interno</strong>
                            <span>Recomendado: hasta 60 caracteres. Debe ser corto, reconocible y operativo para el equipo.</span>
                        </div>
                        <div class="spec-card">
                            <strong>SEO</strong>
                            <span>Título sugerido de hasta 60 caracteres. Descripción sugerida entre 120 y 160 caracteres para buscadores.</span>
                        </div>
                        <div class="spec-card">
                            <strong>Logo e identidad</strong>
                            <span>Usa logo horizontal legible y colores institucionales en formato hexadecimal, por ejemplo <code>#20539A</code>.</span>
                        </div>
                    </div>

                    <div class="design-grid">
                        <div class="subpanel span-8">
                            <h4>Información principal</h4>
                            <p>Estos datos organizan la página dentro del panel y mejoran su presentación pública.</p>
                            <div class="grid grid-2">
                                <div class="field">
                                    <label>Nombre interno</label>
                                    <input type="text" name="name" value="{{ old('name', $page->name) }}" maxlength="160" required>
                                    <div class="field-help"><strong>Límite:</strong> 160 caracteres. Usa un nombre claro para gestión interna.</div>
                                </div>
                                <div class="field">
                                    <label>Slug</label>
                                    <input type="text" name="slug" value="{{ old('slug', $page->slug) }}" maxlength="120" required>
                                    <div class="field-help"><strong>Limite:</strong> 120 caracteres. Solo identificador corto y estable.</div>
                                </div>
                                <div class="field">
                                    <label>Título SEO</label>
                                    <input type="text" name="meta_title" value="{{ old('meta_title', $page->meta_title) }}" maxlength="255">
                                    <div class="field-help"><strong>Recomendado:</strong> 50 a 60 caracteres para mejor lectura en buscadores.</div>
                                </div>
                                <div class="field">
                                    <label>Descripción SEO</label>
                                    <input type="text" name="meta_description" value="{{ old('meta_description', $page->meta_description) }}" maxlength="255">
                                    <div class="field-help"><strong>Recomendado:</strong> entre 120 y 160 caracteres con enfoque informativo.</div>
                                </div>
                            </div>
                        </div>

                        <div class="subpanel span-4">
                            <h4>Estado del sitio</h4>
                            <p>Control ejecutivo de publicación para esta vista.</p>
                            <label style="display:flex; gap:10px; align-items:center; font-weight:700;">
                                <input type="checkbox" name="is_active" value="1" {{ $page->is_active ? 'checked' : '' }}>
                                Página activa
                            </label>
                            <div class="field-help"><strong>Uso:</strong> si está inactiva, la vista no se mostrará públicamente.</div>
                        </div>

                        <div
                            class="subpanel span-6"
                            x-data="{
                                primaryColor: @js(old('theme.primary_color', $editorData['theme']['primary_color'])),
                                secondaryColor: @js(old('theme.secondary_color', $editorData['theme']['secondary_color'])),
                                accentColor: @js(old('theme.accent_color', $editorData['theme']['accent_color']))
                            }"
                        >
                            <h4>Paleta</h4>
                            <p>Colores base para una identidad consistente y controlada.</p>
                            <div class="palette-grid">
                                <div class="color-token">
                                    <div class="field">
                                        <label>Color principal</label>
                                        <input type="text" name="theme[primary_color]" x-model="primaryColor" maxlength="7">
                                        <div class="field-help"><strong>Formato:</strong> hexadecimal. Ejemplo: <code>#20539A</code>.</div>
                                    </div>
                                    <div class="color-swatch-card">
                                        <div class="color-swatch" :style="{ backgroundColor: primaryColor || '#20539a' }"></div>
                                        <span class="color-swatch-value" x-text="primaryColor || '#20539a'"></span>
                                    </div>
                                </div>
                                <div class="color-token">
                                    <div class="field">
                                        <label>Color secundario</label>
                                        <input type="text" name="theme[secondary_color]" x-model="secondaryColor" maxlength="7">
                                        <div class="field-help"><strong>Formato:</strong> hexadecimal. Mantén buen contraste visual.</div>
                                    </div>
                                    <div class="color-swatch-card">
                                        <div class="color-swatch" :style="{ backgroundColor: secondaryColor || '#102542' }"></div>
                                        <span class="color-swatch-value" x-text="secondaryColor || '#102542'"></span>
                                    </div>
                                </div>
                                <div class="color-token">
                                    <div class="field">
                                        <label>Color acento</label>
                                        <input type="text" name="theme[accent_color]" x-model="accentColor" maxlength="7">
                                        <div class="field-help"><strong>Formato:</strong> hexadecimal. Ideal para llamados visuales o resaltados.</div>
                                    </div>
                                    <div class="color-swatch-card">
                                        <div class="color-swatch" :style="{ backgroundColor: accentColor || '#f3b53f' }"></div>
                                        <span class="color-swatch-value" x-text="accentColor || '#f3b53f'"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="subpanel span-6">
                            <h4>Logo</h4>
                            <p>Sube un archivo o usa una URL directa si el recurso ya está alojado.</p>
                            <div class="image-frame">
                                @if ($editorData['theme']['logo_url'])
                                    <img src="{{ $editorData['theme']['logo_url'] }}" alt="Logo actual" class="thumb" style="max-width: 280px;">
                                @endif
                                <div class="field">
                                    <label>URL del logo</label>
                                    <input type="text" name="theme[logo_url]" value="{{ old('theme.logo_url', $editorData['theme']['logo_url']) }}">
                                    <div class="field-help"><strong>Recomendado:</strong> logo horizontal en PNG o SVG con fondo limpio.</div>
                                </div>
                                <div class="field">
                                    <label>Subir nuevo logo</label>
                                    <input type="file" name="theme[logo_file]" accept="image/*">
                                    <div class="field-help"><strong>Sugerido:</strong> ancho mínimo de 240 px y peso optimizado para web.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="section-card" x-show="tab === 'design_text'">
                    <div class="section-header">
                        <div>
                            <div class="section-eyebrow">Navigation system</div>
                            <h3 class="section-title">Encabezado y menu</h3>
                            <p class="section-copy">Define idiomas, accesos rapidos y enlaces principales del sitio.</p>
                        </div>
                        <span class="pill pill-off">{{ count($header['links']) }} enlaces activos</span>
                    </div>

                    <div class="design-grid">
                        <div class="subpanel span-12">
                            <h4>Textos del header</h4>
                            <div class="field-help" style="margin-bottom:14px;"><strong>Guía:</strong> mantén textos breves. Cada etiqueta debería quedar idealmente entre 12 y 30 caracteres para no saturar el encabezado.</div>
                            <div class="grid grid-3">
                                <div class="field"><label>Idioma principal</label><input type="text" name="header[language_primary]" value="{{ old('header.language_primary', $header['settings']['language_primary']) }}" maxlength="30"><div class="field-help">Máximo 30 caracteres.</div></div>
                                <div class="field"><label>Idioma secundario</label><input type="text" name="header[language_secondary]" value="{{ old('header.language_secondary', $header['settings']['language_secondary']) }}" maxlength="30"><div class="field-help">Máximo 30 caracteres.</div></div>
                                <div class="field"><label>Accesibilidad</label><input type="text" name="header[accessibility_label]" value="{{ old('header.accessibility_label', $header['settings']['accessibility_label']) }}" maxlength="40"><div class="field-help">Máximo 40 caracteres.</div></div>
                                <div class="field"><label>Ayuda / contacto</label><input type="text" name="header[help_label]" value="{{ old('header.help_label', $header['settings']['help_label']) }}" maxlength="40"><div class="field-help">Máximo 40 caracteres.</div></div>
                                <div class="field"><label>Botón de inicio de sesión</label><input type="text" name="header[login_label]" value="{{ old('header.login_label', $header['settings']['login_label']) }}" maxlength="40"><div class="field-help">Máximo 40 caracteres.</div></div>
                                <div class="field"><label>Texto del buscador</label><input type="text" name="header[search_placeholder]" value="{{ old('header.search_placeholder', $header['settings']['search_placeholder']) }}" maxlength="60"><div class="field-help">Máximo 60 caracteres.</div></div>
                            </div>
                        </div>

                        <div class="subpanel span-12">
                            <div class="toolbar">
                                <div>
                                    <h4>Enlaces del menu</h4>
                                    <p>Agrega, elimina y reordena. Arrastra cada tarjeta para cambiar el orden.</p>
                                </div>
                                <button type="button" class="button button-secondary" data-add-row>Agregar enlace</button>
                            </div>
                            <div class="stack" data-collection data-base="header[links]" data-template="link-template">
                                <div data-rows>
                                    @forelse ($header['links'] as $link)
                                        <div class="repeater-card" data-row>
                                            <div class="toolbar">
                                                <div class="actions">
                                                    <span class="drag-handle" data-drag>::</span>
                                                    <strong>{{ $link['label'] ?? 'Enlace' }}</strong>
                                                </div>
                                                <button type="button" class="button button-danger" data-remove-row>Eliminar</button>
                                            </div>
                                            <div class="grid grid-2" style="margin-top:12px;">
                                                <div class="field"><label>Texto</label><input type="text" data-field="label" value="{{ $link['label'] ?? '' }}"></div>
                                                <div class="field"><label>URL</label><input type="text" data-field="url" value="{{ $link['url'] ?? '#' }}"></div>
                                            </div>
                                            <input type="hidden" data-field="id" value="{{ $link['id'] ?? '' }}">
                                        </div>
                                    @empty
                                        <div class="empty-note">Todavía no hay enlaces cargados.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="section-card" x-show="tab === 'backgrounds'">
                    <div class="section-header">
                        <div>
                            <div class="section-eyebrow">Hero media</div>
                            <h3 class="section-title">Fondos y carrusel</h3>
                            <p class="section-copy">Administra los fondos de la portada sin cambiar el diseño del frontend. Puedes cargar imágenes o videos.</p>
                        </div>
                        <span class="pill pill-off">{{ count($hero['media'] ?? []) }} slides</span>
                    </div>

                    <div class="subpanel">
                        <h4>Textos principales de portada</h4>
                        <div class="grid grid-2">
                            <div class="field"><label>Título principal</label><input type="text" name="hero[title]" value="{{ old('hero.title', $hero['settings']['title']) }}"></div>
                            <div class="field"><label>Subtítulo</label><input type="text" name="hero[subtitle]" value="{{ old('hero.subtitle', $hero['settings']['subtitle']) }}"></div>
                            <div class="field"><label>Título de rastreo</label><input type="text" name="hero[tracking_title]" value="{{ old('hero.tracking_title', $hero['settings']['tracking_title']) }}"></div>
                            <div class="field"><label>Texto rastreo</label><input type="text" name="hero[tracking_text]" value="{{ old('hero.tracking_text', $hero['settings']['tracking_text']) }}"></div>
                            <div class="field"><label>Etiqueta campo</label><input type="text" name="hero[tracking_label]" value="{{ old('hero.tracking_label', $hero['settings']['tracking_label']) }}"></div>
                            <div class="field"><label>Placeholder campo</label><input type="text" name="hero[tracking_placeholder]" value="{{ old('hero.tracking_placeholder', $hero['settings']['tracking_placeholder']) }}"></div>
                            <div class="field"><label>Texto del botón</label><input type="text" name="hero[tracking_button]" value="{{ old('hero.tracking_button', $hero['settings']['tracking_button']) }}"></div>
                        </div>
                    </div>

                    <div class="subpanel">
                        <div class="toolbar">
                            <div>
                                <h4>Carrusel de fondos</h4>
                                <p>Sube imágenes o videos. El frontend mantendrá el mismo formato visual con este contenido.</p>
                            </div>
                            <button type="button" class="button button-secondary" data-add-row>Agregar slide</button>
                        </div>
                        <div class="stack" data-collection data-base="hero[media]" data-template="hero-media-template">
                            <div data-rows>
                                @forelse (($hero['media'] ?? []) as $item)
                                    <div class="repeater-card" data-row>
                                        <div class="toolbar">
                                            <div class="actions">
                                                <span class="drag-handle" data-drag>::</span>
                                                <strong>{{ $item['title'] ?? 'Slide' }}</strong>
                                            </div>
                                            <button type="button" class="button button-danger" data-remove-row>Eliminar</button>
                                        </div>
                                        <div class="grid grid-2" style="margin-top:12px;">
                                            <div class="field"><label>Nombre interno</label><input type="text" data-field="title" value="{{ $item['title'] ?? '' }}"></div>
                                            <div class="field"><label>Tipo</label><select data-field="media_type" data-media-type><option value="image" {{ ($item['media_type'] ?? 'image') === 'image' ? 'selected' : '' }}>Imagen</option><option value="video" {{ ($item['media_type'] ?? '') === 'video' ? 'selected' : '' }}>Video</option></select></div>
                                            <div class="field"><label>Duración (segundos)</label><input type="number" min="1" max="300" step="1" data-field="duration_seconds" value="{{ $item['duration_seconds'] ?? 5 }}"></div>
                                            <div class="field"><label>Archivo actual</label><input type="text" data-field="src" value="{{ $item['src'] ?? '' }}"></div>
                                            <div class="field"><label>Subir archivo</label><input type="file" data-field="media_file" accept="image/*,video/*"></div>
                                            <div class="field" data-poster-field><label>Poster o imagen previa</label><input type="text" data-field="poster" value="{{ $item['poster'] ?? '' }}"></div>
                                            <div class="field" data-poster-field><label>Subir poster</label><input type="file" data-field="poster_file" accept="image/*"></div>
                                        </div>
                                        <input type="hidden" data-field="id" value="{{ $item['id'] ?? '' }}">
                                    </div>
                                @empty
                                    <div class="empty-note">No hay fondos configurados todavía.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </section>

                <section class="section-card" x-show="tab === 'services'">
                    <div class="section-header">
                        <div>
                            <div class="section-eyebrow">Service gallery</div>
                            <h3 class="section-title">Servicios destacados</h3>
                            <p class="section-copy">Cada tarjeta puede llevar texto, icono y una imagen propia.</p>
                        </div>
                        <span class="pill pill-off">{{ count($services['items']) }} bloques</span>
                    </div>

                    <div class="subpanel">
                        <h4>Cabecera de la sección</h4>
                        <div class="grid grid-3">
                            <div class="field"><label>Título de la sección</label><input type="text" name="services[title]" value="{{ old('services.title', $services['settings']['title']) }}"></div>
                            <div class="field"><label>Subtítulo</label><input type="text" name="services[subtitle]" value="{{ old('services.subtitle', $services['settings']['subtitle']) }}"></div>
                            <div class="field"><label>Texto superior</label><input type="text" name="services[kicker]" value="{{ old('services.kicker', $services['settings']['kicker']) }}"></div>
                        </div>
                    </div>

                    <div class="subpanel">
                        <div class="toolbar">
                            <div>
                                <h4>Tarjetas de servicio</h4>
                                <p>Sube imágenes y reorganiza el orden arrastrando cada tarjeta.</p>
                            </div>
                            <button type="button" class="button button-secondary" data-add-row>Agregar servicio</button>
                        </div>
                        <div class="stack" data-collection data-base="services[items]" data-template="service-template">
                            <div data-rows>
                                @forelse ($services['items'] as $item)
                                    <div class="repeater-card" data-row>
                                        <div class="toolbar">
                                            <div class="actions">
                                                <span class="drag-handle" data-drag>::</span>
                                                <strong>{{ $item['title'] ?? 'Servicio' }}</strong>
                                            </div>
                                            <button type="button" class="button button-danger" data-remove-row>Eliminar</button>
                                        </div>
                                        <div class="grid grid-2" style="margin-top:12px;">
                                            <div class="field"><label>Título</label><input type="text" data-field="title" value="{{ $item['title'] ?? '' }}"></div>
                                            <div class="field"><label>Icono</label><input type="text" data-field="icon" value="{{ $item['icon'] ?? '' }}"></div>
                                            <div class="field"><label>Imagen actual</label><input type="text" data-field="iconImage" value="{{ $item['iconImage'] ?? '' }}"></div>
                                            <div class="field"><label>Subir imagen</label><input type="file" data-field="iconImage_file" accept="image/*" data-preview-input></div>
                                            <div class="field" style="grid-column:1/-1;"><label>Descripción</label><input type="text" data-field="text" value="{{ $item['text'] ?? '' }}"></div>
                                        </div>
                                        <img src="{{ $item['iconImage'] ?? '' }}" alt="Imagen servicio" class="thumb" data-preview-image style="{{ empty($item['iconImage']) ? 'display:none; margin-top:14px;' : 'margin-top:14px;' }}">
                                        <input type="hidden" data-field="id" value="{{ $item['id'] ?? '' }}">
                                    </div>
                                @empty
                                    <div class="empty-note">No hay servicios todavía. Crea el primero desde este mismo panel.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </section>

                <section class="section-card" x-show="tab === 'design_text'">
                    <div class="section-header">
                        <div>
                            <div class="section-eyebrow">Utility area</div>
                            <h3 class="section-title">Herramientas</h3>
                            <p class="section-copy">Ajusta el bloque del mapa y la calculadora de envíos.</p>
                        </div>
                    </div>
                    <div class="subpanel">
                        <div class="grid grid-3">
                            <div class="field"><label>Título del mapa</label><input type="text" name="tools[map_title]" value="{{ old('tools.map_title', $tools['settings']['map_title'] ?? '') }}"></div>
                            <div class="field"><label>Texto mapa</label><input type="text" name="tools[map_text]" value="{{ old('tools.map_text', $tools['settings']['map_text'] ?? '') }}"></div>
                            <div class="field"><label>Botón del mapa</label><input type="text" name="tools[map_button_label]" value="{{ old('tools.map_button_label', $tools['settings']['map_button_label'] ?? '') }}"></div>
                            <div class="field"><label>Título de la calculadora</label><input type="text" name="tools[calculator_title]" value="{{ old('tools.calculator_title', $tools['settings']['calculator_title'] ?? '') }}"></div>
                            <div class="field"><label>Texto calculadora</label><input type="text" name="tools[calculator_text]" value="{{ old('tools.calculator_text', $tools['settings']['calculator_text'] ?? '') }}"></div>
                            <div class="field"><label>Etiqueta origen</label><input type="text" name="tools[origin_label]" value="{{ old('tools.origin_label', $tools['settings']['origin_label'] ?? '') }}"></div>
                            <div class="field"><label>Placeholder origen</label><input type="text" name="tools[origin_placeholder]" value="{{ old('tools.origin_placeholder', $tools['settings']['origin_placeholder'] ?? '') }}"></div>
                            <div class="field"><label>Etiqueta destino</label><input type="text" name="tools[destination_label]" value="{{ old('tools.destination_label', $tools['settings']['destination_label'] ?? '') }}"></div>
                            <div class="field"><label>Placeholder destino</label><input type="text" name="tools[destination_placeholder]" value="{{ old('tools.destination_placeholder', $tools['settings']['destination_placeholder'] ?? '') }}"></div>
                            <div class="field"><label>Etiqueta peso</label><input type="text" name="tools[weight_label]" value="{{ old('tools.weight_label', $tools['settings']['weight_label'] ?? '') }}"></div>
                            <div class="field"><label>Placeholder peso</label><input type="text" name="tools[weight_placeholder]" value="{{ old('tools.weight_placeholder', $tools['settings']['weight_placeholder'] ?? '') }}"></div>
                            <div class="field"><label>Botón de calcular</label><input type="text" name="tools[calculate_button_label]" value="{{ old('tools.calculate_button_label', $tools['settings']['calculate_button_label'] ?? '') }}"></div>
                        </div>
                    </div>
                    <div class="subpanel">
                        <div class="empty-note" style="margin-bottom:16px;">
                            Códigos del mapa:
                            `BON` Pando, `BOL` La Paz, `BOB` Beni, `BOO` Oruro, `BOC` Cochabamba, `BOS` Santa Cruz, `BOH` Chuquisaca, `BOP` Potosí, `BOT` Tarija.
                            Usa posiciones como `29.6%` y `46%` para mover cada pin sobre el mapa.
                        </div>
                        <div class="toolbar">
                            <div>
                                <h4>Oficinas del mapa</h4>
                                <p>Administra la información, el departamento y la posición visual de cada pin.</p>
                            </div>
                            <button type="button" class="button button-secondary" data-add-row>Agregar oficina</button>
                        </div>
                        <div class="stack" data-collection data-base="tools[items]" data-template="office-template">
                            <div data-rows>
                                @forelse ($tools['items'] ?? [] as $item)
                                    <div class="repeater-card" data-row>
                                        <div class="toolbar">
                                            <div class="actions">
                                                <span class="drag-handle" data-drag>::</span>
                                                <strong>{{ $item['title'] ?? 'Oficina' }}</strong>
                                            </div>
                                            <button type="button" class="button button-danger" data-remove-row>Eliminar</button>
                                        </div>
                                        <div class="grid grid-3" style="margin-top:12px;">
                                            <div class="field"><label>Nombre oficina</label><input type="text" data-field="title" value="{{ $item['title'] ?? '' }}"></div>
                                            <div class="field"><label>Ciudad o etiqueta</label><input type="text" data-field="name" value="{{ $item['name'] ?? '' }}"></div>
                                            <div class="field"><label>Código depto</label><input type="text" data-field="dept" value="{{ $item['dept'] ?? '' }}" placeholder="BOL, BOC, BOS..."></div>
                                            <div class="field"><label>Dirección</label><input type="text" data-field="address" value="{{ $item['address'] ?? '' }}"></div>
                                            <div class="field"><label>Lun a Vie</label><input type="text" data-field="weekday_hours" value="{{ $item['weekday_hours'] ?? '' }}" placeholder="08:00 a 18:00"></div>
                                            <div class="field"><label>Sábado</label><input type="text" data-field="saturday_hours" value="{{ $item['saturday_hours'] ?? '' }}" placeholder="09:00 a 13:00"></div>
                                            <div class="field"><label>Teléfono</label><input type="text" data-field="phone" value="{{ $item['phone'] ?? '' }}"></div>
                                            <div class="field"><label>Posición izquierda</label><input type="text" data-field="left" value="{{ $item['left'] ?? '' }}" placeholder="29.6%"></div>
                                            <div class="field"><label>Posición arriba</label><input type="text" data-field="top" value="{{ $item['top'] ?? '' }}" placeholder="46%"></div>
                                            <div class="field"><label>Google Maps URL</label><input type="text" data-field="maps_url" value="{{ $item['maps_url'] ?? '' }}"></div>
                                            <div class="field" style="grid-column:1/-1;"><label>Horario general de respaldo</label><input type="text" data-field="hours" value="{{ $item['hours'] ?? '' }}" placeholder="Opcional, útil para compatibilidad con datos antiguos"></div>
                                        </div>
                                        <input type="hidden" data-field="id" value="{{ $item['id'] ?? '' }}">
                                    </div>
                                @empty
                                    <div class="empty-note">No hay oficinas todavía. Crea la primera desde este panel.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </section>

                <section class="section-card" x-show="tab === 'banner'">
                    <div class="section-header">
                        <div>
                            <div class="section-eyebrow">App promotion</div>
                            <h3 class="section-title">Banner frontal visual</h3>
                            <p class="section-copy">Este bloque ahora se maneja como carrusel de imágenes puras, sin textos ni botones superpuestos en el frontend. Solo importa la pieza gráfica y su duración.</p>
                        </div>
                        <span class="pill {{ count($appBanner['items'] ?? []) ? 'pill-ok' : 'pill-off' }}">{{ count($appBanner['items'] ?? []) ?: 0 }} banner(s)</span>
                    </div>
                    <div class="subpanel">
                        <div class="toolbar">
                            <div>
                                <h4>Slides del banner</h4>
                                <p>Usa imágenes horizontales, idealmente 16:9 o panorámicas, para cubrir bien desktop y móvil. Cada slide puede tener su propio tiempo.</p>
                            </div>
                            <button type="button" class="button button-secondary" data-add-row>Agregar banner</button>
                        </div>
                        <div class="stack" data-collection data-base="app_banner[items]" data-template="app-banner-template">
                            <div data-rows>
                                @forelse (($appBanner['items'] ?? []) as $item)
                                    <div class="repeater-card" data-row>
                                        <div class="toolbar">
                                            <div class="actions">
                                                <span class="drag-handle" data-drag>::</span>
                                                <strong>{{ $item['title'] ?? 'Banner visual' }}</strong>
                                            </div>
                                            <button type="button" class="button button-danger" data-remove-row>Eliminar</button>
                                        </div>
                                        <div class="grid grid-3" style="margin-top:12px;">
                                            <div class="field"><label>Nombre interno</label><input type="text" data-field="title" value="{{ $item['title'] ?? '' }}"></div>
                                            <div class="field"><label>Duración (segundos)</label><input type="number" min="1" max="300" step="1" data-field="duration_seconds" value="{{ $item['duration_seconds'] ?? 5 }}"></div>
                                            <div class="field"><label>Imagen actual</label><input type="text" data-field="image" value="{{ $item['image'] ?? '' }}"></div>
                                            <div class="field"><label>Subir imagen</label><input type="file" data-field="image_file" accept="image/*" data-preview-input></div>
                                        </div>
                                        @if (!empty($item['image']))
                                            <img class="thumb" data-preview-image src="{{ $item['image'] }}" style="display:block; margin-top:14px; max-width: 320px; aspect-ratio: 16 / 9;" alt="Preview">
                                        @else
                                            <img class="thumb" data-preview-image style="display:none; margin-top:14px; max-width: 320px; aspect-ratio: 16 / 9;" alt="Preview">
                                        @endif
                                        <input type="hidden" data-field="id" value="{{ $item['id'] ?? '' }}">
                                    </div>
                                @empty
                                    <div class="empty-note">No hay banners cargados todavía. Agrega el primero para activar el carrusel visual.</div>
                                @endforelse
                            </div>
                        </div>

                        <div class="image-frame" style="margin-top:16px;">
                            <strong>Compatibilidad con la imagen antigua</strong>
                            <p style="margin:0; color:#667085;">Si ya tenías una imagen base guardada, este campo se conserva como respaldo. En el frontend se usará solo si no hay slides nuevos.</p>
                            @if (!empty($appBanner['settings']['background_image']))
                                <img src="{{ $appBanner['settings']['background_image'] }}" alt="Banner actual" class="thumb" style="max-width: 320px;">
                            @endif
                            <div class="field"><label>URL imagen base</label><input type="text" name="app_banner[background_image]" value="{{ old('app_banner.background_image', $appBanner['settings']['background_image'] ?? '') }}"></div>
                            <div class="field"><label>Subir imagen base</label><input type="file" name="app_banner[background_file]" accept="image/*"></div>
                        </div>
                    </div>
                </section>

                <section class="section-card" x-show="tab === 'market'">
                    <div class="section-header">
                        <div>
                            <div class="section-eyebrow">Commerce curation</div>
                            <h3 class="section-title">Market y productos</h3>
                            <p class="section-copy">Carga piezas destacadas, imágenes, precios y descripciones.</p>
                        </div>
                        <span class="pill pill-off">{{ count($market['items']) }} productos</span>
                    </div>

                    <div class="subpanel">
                        <h4>Cabecera de market</h4>
                        <div class="grid grid-2">
                            <div class="field"><label>Título</label><input type="text" name="market[title]" value="{{ old('market.title', $market['settings']['title'] ?? '') }}"></div>
                            <div class="field"><label>Subtítulo</label><input type="text" name="market[subtitle]" value="{{ old('market.subtitle', $market['settings']['subtitle'] ?? '') }}"></div>
                            <div class="field"><label>Texto del botón final</label><input type="text" name="market[view_all_label]" value="{{ old('market.view_all_label', $market['settings']['view_all_label'] ?? '') }}"></div>
                            <div class="field"><label>URL del botón final</label><input type="text" name="market[view_all_url]" value="{{ old('market.view_all_url', $market['settings']['view_all_url'] ?? '') }}"></div>
                        </div>
                    </div>

                    <div class="subpanel">
                        <div class="toolbar">
                            <div>
                                <h4>Productos destacados</h4>
                                <p>Sube fotos y organiza el orden de aparición con arrastrar y soltar.</p>
                            </div>
                            <button type="button" class="button button-secondary" data-add-row>Agregar producto</button>
                        </div>
                        <div class="stack" data-collection data-base="market[items]" data-template="product-template">
                            <div data-rows>
                                @forelse ($market['items'] as $item)
                                    <div class="repeater-card" data-row>
                                        <div class="toolbar">
                                            <div class="actions">
                                                <span class="drag-handle" data-drag>::</span>
                                                <strong>{{ $item['title'] ?? 'Producto' }}</strong>
                                            </div>
                                            <button type="button" class="button button-danger" data-remove-row>Eliminar</button>
                                        </div>
                                        <div class="grid grid-3" style="margin-top:12px;">
                                            <div class="field"><label>Título</label><input type="text" data-field="title" value="{{ $item['title'] ?? '' }}"></div>
                                            <div class="field"><label>Precio</label><input type="text" data-field="price" value="{{ $item['price'] ?? '' }}"></div>
                                            <div class="field"><label>Anio o etiqueta</label><input type="text" data-field="year" value="{{ $item['year'] ?? '' }}"></div>
                                            <div class="field"><label>Serie</label><input type="text" data-field="series" value="{{ $item['series'] ?? '' }}"></div>
                                            <div class="field"><label>Imagen actual</label><input type="text" data-field="image" value="{{ $item['image'] ?? '' }}"></div>
                                            <div class="field"><label>Subir imagen</label><input type="file" data-field="image_file" accept="image/*" data-preview-input></div>
                                        </div>
                                        <div class="field" style="margin-top:12px;"><label>Descripción</label><textarea class="field-small" data-field="description">{{ $item['description'] ?? '' }}</textarea></div>
                                        <img src="{{ $item['image'] ?? '' }}" alt="Imagen producto" class="thumb" data-preview-image style="{{ empty($item['image']) ? 'display:none; margin-top:14px;' : 'margin-top:14px;' }}">
                                        <input type="hidden" data-field="id" value="{{ $item['id'] ?? '' }}">
                                    </div>
                                @empty
                                    <div class="empty-note">No hay productos cargados en este momento.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </section>

                <section class="section-card" x-show="tab === 'footer'">
                    <div class="section-header">
                        <div>
                            <div class="section-eyebrow">Closure and contact</div>
                            <h3 class="section-title">Pie de página</h3>
                            <p class="section-copy">Cierra la experiencia con enlaces, datos de contacto y redes sociales.</p>
                        </div>
                    </div>

                    <div class="subpanel">
                        <h4>Textos base</h4>
                        <div class="grid grid-3">
                            <div class="field"><label>Título de ayuda</label><input type="text" name="footer[help_title]" value="{{ old('footer.help_title', $footer['settings']['help_title'] ?? '') }}"></div>
                            <div class="field"><label>Título de empresa</label><input type="text" name="footer[company_title]" value="{{ old('footer.company_title', $footer['settings']['company_title'] ?? '') }}"></div>
                            <div class="field"><label>Título de contacto</label><input type="text" name="footer[contact_title]" value="{{ old('footer.contact_title', $footer['settings']['contact_title'] ?? '') }}"></div>
                            <div class="field"><label>Título de redes</label><input type="text" name="footer[social_title]" value="{{ old('footer.social_title', $footer['settings']['social_title'] ?? '') }}"></div>
                            <div class="field"><label>Texto redes</label><input type="text" name="footer[social_text]" value="{{ old('footer.social_text', $footer['settings']['social_text'] ?? '') }}"></div>
                            <div class="field"><label>Email</label><input type="text" name="footer[email]" value="{{ old('footer.email', $footer['settings']['email'] ?? '') }}"></div>
                            <div class="field"><label>Dirección línea 1</label><input type="text" name="footer[address_line_1]" value="{{ old('footer.address_line_1', $address[0] ?? '') }}"></div>
                            <div class="field"><label>Dirección línea 2</label><input type="text" name="footer[address_line_2]" value="{{ old('footer.address_line_2', $address[1] ?? '') }}"></div>
                            <div class="field"><label>Teléfono línea 1</label><input type="text" name="footer[phone_line_1]" value="{{ old('footer.phone_line_1', $phone[0] ?? '') }}"></div>
                            <div class="field"><label>Teléfono línea 2</label><input type="text" name="footer[phone_line_2]" value="{{ old('footer.phone_line_2', $phone[1] ?? '') }}"></div>
                            <div class="field"><label>Copyright</label><input type="text" name="footer[copyright]" value="{{ old('footer.copyright', $footer['settings']['copyright'] ?? '') }}"></div>
                            <div class="field"><label>Texto legal</label><input type="text" name="footer[legal_text]" value="{{ old('footer.legal_text', $footer['settings']['legal_text'] ?? '') }}"></div>
                        </div>
                    </div>

                    <div class="subpanel">
                        <div class="toolbar">
                            <div>
                                <h4>Enlaces de ayuda</h4>
                                <p>Preguntas frecuentes, contacto o soporte.</p>
                            </div>
                            <button type="button" class="button button-secondary" data-add-row>Agregar enlace</button>
                        </div>
                        <div class="stack" data-collection data-base="footer[help_links]" data-template="link-template">
                            <div data-rows>
                                @forelse ($footer['help_links'] as $link)
                                    <div class="repeater-card" data-row>
                                        <div class="toolbar">
                                            <div class="actions">
                                                <span class="drag-handle" data-drag>::</span>
                                                <strong>{{ $link['label'] ?? 'Enlace' }}</strong>
                                            </div>
                                            <button type="button" class="button button-danger" data-remove-row>Eliminar</button>
                                        </div>
                                        <div class="grid grid-2" style="margin-top:12px;">
                                            <div class="field"><label>Texto</label><input type="text" data-field="label" value="{{ $link['label'] ?? '' }}"></div>
                                            <div class="field"><label>URL</label><input type="text" data-field="url" value="{{ $link['url'] ?? '#' }}"></div>
                                        </div>
                                        <input type="hidden" data-field="id" value="{{ $link['id'] ?? '' }}">
                                    </div>
                                @empty
                                    <div class="empty-note">No hay enlaces de ayuda todavía.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="subpanel">
                        <div class="toolbar">
                            <div>
                                <h4>Enlaces de empresa</h4>
                                <p>Sección institucional del footer.</p>
                            </div>
                            <button type="button" class="button button-secondary" data-add-row>Agregar enlace</button>
                        </div>
                        <div class="stack" data-collection data-base="footer[company_links]" data-template="link-template">
                            <div data-rows>
                                @forelse ($footer['company_links'] as $link)
                                    <div class="repeater-card" data-row>
                                        <div class="toolbar">
                                            <div class="actions">
                                                <span class="drag-handle" data-drag>::</span>
                                                <strong>{{ $link['label'] ?? 'Enlace' }}</strong>
                                            </div>
                                            <button type="button" class="button button-danger" data-remove-row>Eliminar</button>
                                        </div>
                                        <div class="grid grid-2" style="margin-top:12px;">
                                            <div class="field"><label>Texto</label><input type="text" data-field="label" value="{{ $link['label'] ?? '' }}"></div>
                                            <div class="field"><label>URL</label><input type="text" data-field="url" value="{{ $link['url'] ?? '#' }}"></div>
                                        </div>
                                        <input type="hidden" data-field="id" value="{{ $link['id'] ?? '' }}">
                                    </div>
                                @empty
                                    <div class="empty-note">No hay enlaces de empresa todavía.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="subpanel">
                        <div class="toolbar">
                            <div>
                                <h4>Redes sociales</h4>
                                <p>Nombre corto, nombre accesible y enlace final.</p>
                            </div>
                            <button type="button" class="button button-secondary" data-add-row>Agregar red</button>
                        </div>
                        <div class="stack" data-collection data-base="footer[social_links]" data-template="social-template">
                            <div data-rows>
                                @forelse ($footer['social_links'] as $link)
                                    <div class="repeater-card" data-row>
                                        <div class="toolbar">
                                            <div class="actions">
                                                <span class="drag-handle" data-drag>::</span>
                                                <strong>{{ $link['aria_label'] ?? 'Red social' }}</strong>
                                            </div>
                                            <button type="button" class="button button-danger" data-remove-row>Eliminar</button>
                                        </div>
                                        <div class="grid grid-3" style="margin-top:12px;">
                                            <div class="field"><label>Texto corto</label><input type="text" data-field="label" value="{{ $link['label'] ?? '' }}"></div>
                                            <div class="field"><label>Nombre accesible</label><input type="text" data-field="aria_label" value="{{ $link['aria_label'] ?? '' }}"></div>
                                            <div class="field"><label>URL</label><input type="text" data-field="url" value="{{ $link['url'] ?? '#' }}"></div>
                                        </div>
                                        <input type="hidden" data-field="id" value="{{ $link['id'] ?? '' }}">
                                    </div>
                                @empty
                                    <div class="empty-note">No hay redes sociales configuradas.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </section>

                <section id="history-root" class="section-card" x-show="tab === 'history_overview'">
                    <div class="section-header">
                        <div>
                            <div class="section-eyebrow">Timeline</div>
                            <h3 class="section-title">Historial general de la página</h3>
                            <p class="section-copy">Resumen editorial de cambios y restauraciones aplicadas en esta página.</p>
                        </div>
                        <div class="section-metrics">
                            <span class="pill pill-off">{{ $historyData['total_changes'] }} cambios</span>
                            <span class="pill pill-off">{{ $versions->count() }} versiones recientes</span>
                        </div>
                    </div>

                    <div class="design-grid">
                        <div class="subpanel span-6">
                            <h4>Versiones recientes</h4>
                            <p>Cada guardado crea una versión completa que puedes restaurar.</p>
                            <div class="stack" style="gap:12px;">
                                @forelse ($versions as $version)
                                    @php($isCurrentVersion = (int) $version->version_number === (int) $currentVersionNumber)
                                    <div class="repeater-card" style="padding:14px;">
                                        <div style="display:flex; justify-content:space-between; gap:10px; align-items:flex-start;">
                                            <div>
                                                <strong>Versión {{ $version->version_number }}</strong>
                                                <div style="font-size:12px; color:#6b7280; margin-top:4px;">
                                                    {{ $historyActionLabels[$version->action] ?? ucfirst($version->action) }} · {{ optional($version->created_at)->format('d/m/Y H:i') }}
                                                </div>
                                            </div>
                                            <span class="pill {{ $version->action === 'restored' ? 'pill-ok' : 'pill-off' }}">{{ $historyActionLabels[$version->action] ?? ucfirst($version->action) }}</span>
                                        </div>
                                        @if ($isCurrentVersion)
                                            <div style="margin-top:10px;">
                                                <span class="pill pill-ok">Versión actual</span>
                                            </div>
                                        @endif
                                        <div style="margin-top:10px; font-size:13px; color:#4b5563;">
                                            <div><strong>Responsable:</strong> {{ $version->created_by_name ?: 'Sistema' }}</div>
                                            @if ($version->change_summary)
                                                <div style="margin-top:6px;"><strong>Resumen editorial:</strong> {{ $version->change_summary }}</div>
                                            @endif
                                            <div style="margin-top:6px;"><strong>Cambios incluidos:</strong> {{ $version->changeLogs->count() }}</div>
                                        </div>
                                        @if ($isCurrentVersion)
                                            <div class="button button-secondary" style="width:100%; margin-top:12px; opacity:.78; cursor:default;">Versión actual publicada</div>
                                        @else
                                            <form method="POST" action="{{ route('admin.pages.restore', [$page, $version]) }}" style="margin-top:12px;">
                                                @csrf
                                                <input type="hidden" name="change_summary" value="Restauración desde la versión {{ $version->version_number }}">
                                                <button type="submit" class="button button-secondary" style="width:100%;">Volver a esta versión</button>
                                            </form>
                                        @endif
                                    </div>
                                @empty
                                    <div class="empty-note">Todavía no hay versiones registradas.</div>
                                @endforelse
                            </div>
                        </div>

                        <div class="subpanel span-6">
                            <h4>Cambios recientes</h4>
                            <p>Lectura simple de las últimas acciones realizadas en contenido y estructura.</p>
                            <div class="stack" style="gap:12px;">
                                @forelse ($historyData['latest_changes'] as $log)
                                    <div class="repeater-card" style="padding:14px;">
                                        <strong>{{ $log->summary ?: 'Cambio registrado' }}</strong>
                                        <div style="font-size:12px; color:#6b7280; margin-top:4px;">
                                            {{ optional($log->created_at)->format('d/m/Y H:i') }}
                                        </div>
                                        <div style="margin-top:10px; font-size:13px; color:#4b5563;">
                                            <div><strong>Sección:</strong> {{ $historySectionLabels[$log->section_key ?: 'general'] ?? ucfirst($log->section_key ?: 'general') }}</div>
                                            <div style="margin-top:6px;"><strong>Usuario:</strong> {{ $log->created_by_name ?: 'Sistema' }}</div>
                                            <div style="margin-top:6px;"><strong>Versión:</strong> {{ $log->version?->version_number ?: 'N/D' }}</div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="empty-note">Todavía no hay cambios registrados.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </section>

                @foreach ($historySections as $historySection)
                    <section class="section-card" x-show="tab === 'history_{{ $historySection['key'] }}'">
                        <div class="section-header">
                            <div>
                                <div class="section-eyebrow">Section history</div>
                                <h3 class="section-title">Historial de {{ $historySection['label'] }}</h3>
                                <p class="section-copy">Revisión editorial clara de cambios, responsables y restauraciones de esta sección.</p>
                            </div>
                            <div class="section-metrics">
                                <span class="pill pill-off">{{ $historySection['count'] }} eventos</span>
                                <span class="pill pill-off">{{ $historySection['versions']->count() }} versiones</span>
                            </div>
                        </div>

                        <div class="design-grid">
                            <div class="subpanel span-3">
                                <h4>Versiones relacionadas</h4>
                                <p>Guardados donde esta sección recibió algún cambio.</p>
                                <div class="stack" style="gap:12px;">
                                    @forelse ($historySection['versions'] as $version)
                                        @php($isCurrentVersion = (int) $version->version_number === (int) $currentVersionNumber)
                                        <div class="repeater-card" style="padding:14px;">
                                            <strong>Versión {{ $version->version_number }}</strong>
                                            <div style="font-size:12px; color:#6b7280; margin-top:4px;">
                                                {{ $historyActionLabels[$version->action] ?? ucfirst($version->action) }} · {{ optional($version->created_at)->format('d/m/Y H:i') }}
                                            </div>
                                            @if ($isCurrentVersion)
                                                <div style="margin-top:10px;">
                                                    <span class="pill pill-ok">Versión actual</span>
                                                </div>
                                            @endif
                                            <div style="margin-top:8px; font-size:13px; color:#4b5563;">
                                                <div><strong>Responsable:</strong> {{ $version->created_by_name ?: 'Sistema' }}</div>
                                                @if ($version->change_summary)
                                                    <div style="margin-top:6px;"><strong>Resumen editorial:</strong> {{ $version->change_summary }}</div>
                                                @endif
                                            </div>
                                            @if ($isCurrentVersion)
                                                <div class="button button-secondary" style="width:100%; margin-top:12px; opacity:.78; cursor:default;">Versión actual publicada</div>
                                            @else
                                                <form method="POST" action="{{ route('admin.pages.restore', [$page, $version]) }}" style="margin-top:12px;">
                                                    @csrf
                                                    <input type="hidden" name="change_summary" value="Restauración desde la versión {{ $version->version_number }} para {{ $historySection['label'] }}">
                                                    <button type="submit" class="button button-secondary" style="width:100%;">Volver a esta versión</button>
                                                </form>
                                            @endif
                                        </div>
                                    @empty
                                        <div class="empty-note">Esta sección aún no tiene versiones relacionadas.</div>
                                    @endforelse
                                </div>
                            </div>

                            <div class="subpanel span-9">
                                <h4>Línea de tiempo de la sección</h4>
                                <p>Lectura ejecutiva de lo que se modificó, quién lo hizo y cómo quedó el contenido.</p>
                                <div class="stack" style="gap:12px;">
                                    @forelse ($historySection['logs'] as $log)
                                        <div class="repeater-card" style="padding:14px;">
                                            <div style="display:flex; justify-content:space-between; gap:10px; align-items:flex-start;">
                                                <div>
                                                    <strong>{{ $log->summary ?: 'Cambio registrado' }}</strong>
                                                    <div style="font-size:12px; color:#6b7280; margin-top:4px;">
                                                        {{ optional($log->created_at)->format('d/m/Y H:i') }}
                                                    </div>
                                                </div>
                                                <span class="pill {{ $log->action === 'restored' ? 'pill-ok' : 'pill-off' }}">{{ $historyActionLabels[$log->action] ?? ucfirst($log->action) }}</span>
                                            </div>
                                            <div class="grid grid-3" style="margin-top:12px;">
                                                <div><strong>Usuario:</strong><br>{{ $log->created_by_name ?: 'Sistema' }}</div>
                                                <div><strong>Versión:</strong><br>{{ $log->version?->version_number ?: 'N/D' }}</div>
                                                <div><strong>Elemento:</strong><br>{{ $log->item_name ?: ($historyFieldLabels[$log->field_name] ?? ($log->field_name ?: 'Sección completa')) }}</div>
                                            </div>
                                            @php($changeDetails = $buildHistoryDiff($log->before_state, $log->after_state))
                                            @if (!empty($changeDetails))
                                                <div class="image-frame" style="margin-top:12px;">
                                                    <strong>Cambios detectados</strong>
                                                    <div class="stack" style="gap:10px; margin-top:12px;">
                                                        @foreach ($changeDetails as $change)
                                                            <div style="padding:12px 14px; border:1px solid #dbe5f3; border-radius:16px; background:#fff;">
                                                                <div style="display:flex; justify-content:space-between; gap:12px; align-items:flex-start;">
                                                                    <strong style="font-size:13px; color:#123047;">{{ $change['label'] ?: 'Campo actualizado' }}</strong>
                                                                    <span class="pill pill-off">{{ $change['type'] }}</span>
                                                                </div>
                                                                <div class="grid grid-2" style="margin-top:10px; gap:10px;">
                                                                    <div>
                                                                        <div style="font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.05em; color:#667085;">Antes</div>
                                                                        <div style="margin-top:4px; font-size:13px; color:#344054; line-height:1.55;">{{ $change['before'] }}</div>
                                                                    </div>
                                                                    <div>
                                                                        <div style="font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.05em; color:#667085;">Después</div>
                                                                        <div style="margin-top:4px; font-size:13px; color:#344054; line-height:1.55;">{{ $change['after'] }}</div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @empty
                                        <div class="empty-note">Todavía no hay cambios registrados para esta sección.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </section>
                @endforeach

                <div class="save-dock">
                    <div style="flex:1;">
                        <strong style="display:block; margin-bottom:4px;">Todo listo para guardar</strong>
                        <p>El frontend publico no cambia de estructura, solo actualiza lo que el administrador controla aqui.</p>
                        <div class="field" style="margin-top:12px;">
                            <label>Resumen del cambio</label>
                            <input type="text" name="change_summary" value="{{ old('change_summary') }}" placeholder="Ej: Actualice hero, servicios y footer">
                        </div>
                    </div>
                    <button type="submit" class="button button-primary">Guardar cambios del diseño</button>
                </div>
            </div>

        </div>
    </form>
</div>
@endsection
