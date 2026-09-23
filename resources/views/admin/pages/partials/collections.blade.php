<template id="link-template">
        <div class="repeater-card" data-row>
            <div class="toolbar">
                <div class="actions">
                    <span class="drag-handle" data-drag>::</span>
                    <strong>Nuevo enlace</strong>
                </div>
                <button type="button" class="button button-danger" data-remove-row>Eliminar</button>
            </div>
            <div class="grid grid-2" style="margin-top:12px;">
                <div class="field"><label>Texto</label><input type="text" data-field="label"></div>
                <div class="field"><label>Enlace</label><input type="text" data-field="url" value="#"></div>
            </div>
            <input type="hidden" data-field="id">
        </div>
    </template>

    <template id="service-template">
        <div class="repeater-card" data-row>
            <div class="toolbar">
                <div class="actions">
                    <span class="drag-handle" data-drag>::</span>
                    <strong>Nuevo servicio</strong>
                </div>
                <button type="button" class="button button-danger" data-remove-row>Eliminar</button>
            </div>
            <div class="grid grid-2" style="margin-top:12px;">
                <div class="field"><label>Título</label><input type="text" data-field="title"></div>
                <div class="field"><label>Ícono</label><input type="text" data-field="icon" placeholder="plane, truck, mail"></div>
                <div class="field"><label>Imagen actual</label><input type="text" data-field="iconImage"></div>
                <div class="field"><label>Subir imagen</label><input type="file" data-field="iconImage_file" accept="image/*" data-preview-input></div>
                <div class="field" style="grid-column:1/-1;"><label>Enlace de destino</label><input type="text" data-field="url" placeholder="/ems, /casillas o https://..."></div>
                <div class="field" style="grid-column:1/-1;"><label>Descripción</label><input type="text" data-field="text"></div>
            </div>
            <div style="margin-top:12px;">
                <button type="button" class="button button-secondary" @click="openPreviewFromCard($event.currentTarget)">Ver imagen</button>
                <img class="thumb" data-preview-image data-no-inline-preview="1" style="display:none;" alt="Preview">
            </div>
            <input type="hidden" data-field="id">
        </div>
    </template>

    <template id="ems-card-template">
        <div class="repeater-card" data-row>
            <div class="toolbar">
                <div class="actions">
                    <span class="drag-handle" data-drag>::</span>
                    <strong>Nueva tarjeta EMS</strong>
                </div>
                <button type="button" class="button button-danger" data-remove-row>Eliminar</button>
            </div>
            <div class="grid grid-2" style="margin-top:12px;">
                <div class="field"><label>Título</label><input type="text" data-field="title"></div>
                <div class="field"><label>Ícono</label><input type="text" data-field="icon" placeholder="broadcast, shield, globe..."></div>
                <div class="field" style="grid-column:1/-1;"><label>Descripción</label><textarea class="field-small" data-field="text"></textarea></div>
                <div class="field"><label>Etiqueta opcional</label><input type="text" data-field="badge"></div>
            </div>
            <input type="hidden" data-field="id">
        </div>
    </template>

    <template id="hero-media-template">
        <div class="repeater-card" data-row>
            <div class="toolbar">
                <div class="actions">
                    <span class="drag-handle" data-drag>::</span>
                    <strong>Nuevo slide</strong>
                </div>
                <button type="button" class="button button-danger" data-remove-row>Eliminar</button>
            </div>
            <div class="grid grid-2" style="margin-top:12px;">
                <div class="field"><label>Nombre interno</label><input type="text" data-field="title"></div>
                <div class="field"><label>Tipo</label><select data-field="media_type" data-media-type><option value="image">Imagen</option><option value="video">Video</option></select></div>
                <div class="field"><label>Duración (segundos)</label><input type="number" min="1" max="300" step="1" data-field="duration_seconds" value="5"></div>
                <div class="field"><label>Archivo actual</label><input type="text" data-field="src"></div>
                <div class="field"><label>Subir archivo</label><input type="file" data-field="media_file" accept="image/*,video/*"></div>
                <div class="field" data-poster-field><label>Portada del video</label><input type="text" data-field="poster"></div>
                <div class="field" data-poster-field><label>Subir portada del video</label><input type="file" data-field="poster_file" accept="image/*"></div>
            </div>
            <input type="hidden" data-field="id">
        </div>
    </template>

    <template id="product-template">
        <div class="repeater-card" data-row>
            <div class="toolbar">
                <div class="actions">
                    <span class="drag-handle" data-drag>::</span>
                    <strong>Nuevo producto</strong>
                </div>
                <button type="button" class="button button-danger" data-remove-row>Eliminar</button>
            </div>
            <div class="grid grid-3" style="margin-top:12px;">
                <div class="field"><label>Título</label><input type="text" data-field="title"></div>
                <div class="field"><label>Precio</label><input type="text" data-field="price"></div>
                <div class="field"><label>Año o etiqueta</label><input type="text" data-field="year"></div>
                <div class="field"><label>Serie</label><input type="text" data-field="series"></div>
                <div class="field"><label>Imagen actual</label><input type="text" data-field="image"></div>
                <div class="field"><label>Subir imagen</label><input type="file" data-field="image_file" accept="image/*" data-preview-input></div>
            </div>
            <div class="field" style="margin-top:12px;"><label>Descripción</label><textarea class="field-small" data-field="description"></textarea></div>
            <img class="thumb" data-preview-image style="display:none; margin-top:14px;" alt="Preview">
            <input type="hidden" data-field="id">
        </div>
    </template>

    <template id="office-template">
        <div class="repeater-card" data-row>
            <div class="toolbar">
                <div class="actions">
                    <span class="drag-handle" data-drag>::</span>
                    <strong>Nueva oficina</strong>
                </div>
                <button type="button" class="button button-danger" data-remove-row>Eliminar</button>
            </div>
            <div class="grid grid-3" style="margin-top:12px;">
                <div class="field"><label>Nombre oficina</label><input type="text" data-field="title"></div>
                <div class="field"><label>Ciudad o etiqueta</label><input type="text" data-field="name"></div>
                <div class="field"><label>Código depto</label><input type="text" data-field="dept" placeholder="BOL, BOC, BOS..."></div>
                <div class="field"><label>Dirección</label><input type="text" data-field="address"></div>
                <div class="field"><label>Lun a Vie</label><input type="text" data-field="weekday_hours" placeholder="08:00 a 18:00"></div>
                <div class="field"><label>Sábado</label><input type="text" data-field="saturday_hours" placeholder="09:00 a 13:00"></div>
                <div class="field"><label>Teléfono</label><input type="text" data-field="phone"></div>
                <div class="field"><label>Posición izquierda</label><input type="text" data-field="left" placeholder="29.6%"></div>
                <div class="field"><label>Posición arriba</label><input type="text" data-field="top" placeholder="46%"></div>
                <div class="field"><label>Google Maps URL</label><input type="text" data-field="maps_url" value="#"></div>
                <div class="field" style="grid-column:1/-1;"><label>Horario general de respaldo</label><input type="text" data-field="hours" placeholder="Opcional para compatibilidad"></div>
            </div>
            <input type="hidden" data-field="id">
        </div>
    </template>

    <template id="announcement-template">
        <div class="repeater-card" data-row>
            <div class="toolbar">
                <div class="actions">
                    <span class="drag-handle" data-drag>::</span>
                    <strong>Nuevo popup</strong>
                </div>
                <button type="button" class="button button-danger" data-remove-row>Eliminar</button>
            </div>
            <div class="grid grid-2" style="margin-top:12px;">
                <div class="field"><label>Nombre interno</label><input type="text" data-field="title"></div>
                <div class="field"><label>Texto alternativo</label><input type="text" data-field="poster_alt" value="Comunicado institucional"></div>
                <div class="field"><label>Imagen actual</label><input type="text" data-field="poster_image"></div>
                <div class="field"><label>Subir imagen</label><input type="file" data-field="poster_file" accept="image/*" data-preview-input></div>
                <div class="field"><label>Título visible</label><input type="text" data-field="poster_title"></div>
                <div class="field"><label>Pie o detalle</label><input type="text" data-field="poster_caption"></div>
            </div>
            <img class="thumb" data-preview-image data-no-inline-preview="1" style="display:none; margin-top:14px; max-width: 320px; aspect-ratio: auto;" alt="Preview">
            <input type="hidden" data-field="id">
        </div>
    </template>

    <template id="app-banner-template">
        <div class="repeater-card" data-row>
            <div class="toolbar">
                <div class="actions">
                    <span class="drag-handle" data-drag>::</span>
                    <strong>Nuevo banner</strong>
                </div>
                <button type="button" class="button button-danger" data-remove-row>Eliminar</button>
            </div>
            <div class="grid grid-3" style="margin-top:12px;">
                <div class="field"><label>Nombre interno</label><input type="text" data-field="title"></div>
                <div class="field"><label>Duración (segundos)</label><input type="number" min="1" max="300" step="1" data-field="duration_seconds" value="5"></div>
                <div class="field"><label>Imagen actual</label><input type="text" data-field="image"></div>
                <div class="field"><label>Subir imagen</label><input type="file" data-field="image_file" accept="image/*" data-preview-input></div>
            </div>
            <div style="margin-top:12px;">
                <button type="button" class="button button-secondary" @click="openPreviewFromCard($event.currentTarget)">Ver imagen</button>
                <img class="thumb" data-preview-image data-no-inline-preview="1" style="display:none;" alt="Preview">
            </div>
            <input type="hidden" data-field="id">
        </div>
    </template>

    <template id="social-template">
        <div class="repeater-card" data-row>
            <div class="toolbar">
                <div class="actions">
                    <span class="drag-handle" data-drag>::</span>
                    <strong>Nueva red social</strong>
                </div>
                <button type="button" class="button button-danger" data-remove-row>Eliminar</button>
            </div>
            <div class="grid grid-3" style="margin-top:12px;">
                <div class="field"><label>Texto corto</label><input type="text" data-field="label"></div>
                <div class="field"><label>Descripción para lectores de pantalla</label><input type="text" data-field="aria_label"></div>
                <div class="field"><label>Enlace</label><input type="text" data-field="url" value="#"></div>
                <div class="field"><label>Imagen actual</label><input type="text" data-field="image"></div>
                <div class="field"><label>Subir icono</label><input type="file" data-field="image_file" accept="image/*" data-preview-input></div>
            </div>
            <div style="margin-top:12px;">
                <img class="thumb" data-preview-image style="display:none; max-width: 88px; aspect-ratio: 1 / 1; object-fit: contain;" alt="Icono social">
            </div>
            <input type="hidden" data-field="id">
        </div>
    </template>
