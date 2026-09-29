(() => {
    const catalog = window.CB_ADMIN_ICON_CATALOG || {};
    const pageGroups = {
        home: 'home',
        'quienes-somos': 'about',
        noticias: 'noticias',
        deliveryexpress: 'delivery',
        eca: 'eca',
        'correspondencia-agrupada': 'correspondencia',
        casillas: 'casillas',
        postalshopper: 'postalshopper',
        misaplicaciones: 'applications',
        misaplicaicones: 'applications',
        tramites: 'tramites',
        'informacion-postal': 'tramites',
        encomienda: 'encomienda',
        ems: 'ems',
        contacto: 'contacto',
        'politica-privacidad-trackingbo-app': 'trackingPrivacy'
    };
    const labels = {
        accessibility: 'Accesibilidad', alert: 'Alerta', anchor: 'Ancla', arrow: 'Flecha', award: 'Reconocimiento',
        bag: 'Bolsa', bolt: 'Rayo', box: 'Caja', briefcase: 'Maletín', broadcast: 'Señal', building: 'Edificio',
        calculator: 'Calculadora', calendar: 'Calendario', cart: 'Carrito de compras', chart: 'Gráfico',
        'check-circle': 'Verificado', checkCircle: 'Verificado', chevronDown: 'Flecha abajo', chevronLeft: 'Flecha izquierda',
        chevronRight: 'Flecha derecha', clock: 'Reloj', cube: 'Cubo', document: 'Documento', download: 'Descarga',
        equal: 'Igualdad', eye: 'Ver', facebook: 'Facebook', file: 'Archivo', globe: 'Mundo', grid: 'Cuadrícula',
        handshake: 'Acuerdo', heart: 'Corazón', help: 'Ayuda', home: 'Inicio', image: 'Imagen', infoCircle: 'Información',
        key: 'Llave', layers: 'Capas', link: 'Enlace', lock: 'Candado', login: 'Acceso', mail: 'Correo', mailbox: 'Buzón',
        map: 'Mapa', megaphone: 'Anuncio', message: 'Mensaje', 'message-circle': 'Conversación', news: 'Noticias',
        office: 'Oficina', package: 'Paquete', person: 'Persona', phone: 'Celular', 'phone-call': 'Llamada telefónica',
        pin: 'Ubicación', plane: 'Avión', qr: 'Código QR', receipt: 'Recibo', ruler: 'Regla', scale: 'Balanza',
        search: 'Buscar', searchPlus: 'Ampliar búsqueda', settings: 'Configuración', shield: 'Seguridad', smartphone: 'Teléfono móvil',
        spark: 'Destello', stamp: 'Sello postal', star: 'Estrella', sun: 'Sol', tag: 'Etiqueta', target: 'Objetivo',
        thumbs: 'Manos', trend: 'Tendencia', 'trend-up': 'Tendencia al alza', trophy: 'Trofeo', truck: 'Camión',
        users: 'Personas', whatsapp: 'WhatsApp', x: 'Red social X'
    };
    const normalize = (value) => String(value || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
    const pageSlug = (document.body.dataset.adminPage || '').trim().toLowerCase();
    const groupName = pageGroups[pageSlug];
    const icons = catalog[groupName] || Object.assign({}, ...Object.values(catalog));
    const iconEntries = Object.entries(icons).sort(([left], [right]) => {
        return (labels[left] || left).localeCompare(labels[right] || right, 'es');
    });
    const fieldsSelector = 'input[data-field="icon"], input[name$="[visual_icon]"], input[name$="[floating_icon]"]';
    let activeInput = null;
    let activeTrigger = null;
    let overlay = null;
    let searchInput = null;
    let iconGrid = null;
    let countLabel = null;

    function iconLabel(key) {
        return labels[key] || key.replace(/[-_]/g, ' ').replace(/\b\w/g, (letter) => letter.toUpperCase());
    }

    function setIconPreview(button, key) {
        const preview = button.querySelector('[data-icon-preview]');
        const name = button.querySelector('[data-icon-preview-name]');
        const svg = icons[key];
        preview.innerHTML = svg || '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="12" cy="12" r="9"></circle><path d="M12 8v4m0 4h.01"></path></svg>';
        name.textContent = key ? (svg ? iconLabel(key) : 'Personalizado') : 'Elegir icono';
        button.setAttribute('aria-label', key ? `Cambiar icono: ${iconLabel(key)}` : 'Elegir un icono');
        button.title = key ? `Icono actual: ${key}. Haz clic para cambiarlo` : 'Elegir un icono';
    }

    function createOverlay() {
        if (overlay) return;
        overlay = document.createElement('div');
        overlay.className = 'admin-icon-overlay';
        overlay.hidden = true;
        overlay.innerHTML = `
            <section class="admin-icon-dialog" role="dialog" aria-modal="true" aria-labelledby="admin-icon-title" tabindex="-1">
                <header class="admin-icon-dialog-header">
                    <div><span class="admin-icon-eyebrow">Biblioteca visual</span><h2 id="admin-icon-title">Elige un icono</h2><p>Selecciona el símbolo que mejor represente este contenido.</p></div>
                    <button class="admin-icon-close" type="button" aria-label="Cerrar selector">×</button>
                </header>
                <label class="admin-icon-search"><span aria-hidden="true">⌕</span><input type="search" placeholder="Buscar por nombre o uso..." autocomplete="off" aria-label="Buscar iconos"></label>
                <div class="admin-icon-list-heading"><strong>Iconos disponibles</strong><span data-icon-count></span></div>
                <div class="admin-icon-grid" data-icon-grid></div>
                <p class="admin-icon-footnote">El nombre técnico se conserva en el campo para facilitar la edición avanzada.</p>
            </section>`;
        document.body.appendChild(overlay);
        searchInput = overlay.querySelector('input[type="search"]');
        iconGrid = overlay.querySelector('[data-icon-grid]');
        countLabel = overlay.querySelector('[data-icon-count]');
        overlay.querySelector('.admin-icon-close').addEventListener('click', closeOverlay);
        overlay.addEventListener('click', (event) => {
            if (event.target === overlay) closeOverlay();
        });
        searchInput.addEventListener('input', renderIcons);
        overlay.addEventListener('click', (event) => {
            const choice = event.target.closest('[data-icon-choice]');
            if (!choice || !activeInput) return;
            const key = choice.dataset.iconChoice;
            activeInput.value = key;
            activeInput.dispatchEvent(new Event('input', { bubbles: true }));
            activeInput.dispatchEvent(new Event('change', { bubbles: true }));
            setIconPreview(activeTrigger, key);
            closeOverlay();
        });
        overlay.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                event.preventDefault();
                closeOverlay();
                return;
            }
            if (event.key !== 'Tab') return;
            const focusable = [...overlay.querySelectorAll('button:not([disabled]),input:not([disabled]),[tabindex="0"]')];
            if (!focusable.length) return;
            const first = focusable[0];
            const last = focusable[focusable.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        });
    }

    function renderIcons() {
        if (!iconGrid) return;
        const query = normalize(searchInput.value.trim());
        const matches = iconEntries.filter(([key]) => normalize(`${iconLabel(key)} ${key}`).includes(query));
        iconGrid.replaceChildren();

        const emptyChoice = document.createElement('button');
        emptyChoice.type = 'button';
        emptyChoice.className = 'admin-icon-option admin-icon-option-empty';
        emptyChoice.dataset.iconChoice = '';
        emptyChoice.setAttribute('aria-label', 'Quitar icono');
        emptyChoice.innerHTML = '<span class="admin-icon-option-art" aria-hidden="true"><span class="admin-icon-none">×</span></span><span class="admin-icon-option-name">Sin icono</span><span class="admin-icon-option-key">Quitar selección</span>';
        iconGrid.appendChild(emptyChoice);

        matches.forEach(([key, svg]) => {
            const option = document.createElement('button');
            option.type = 'button';
            option.className = 'admin-icon-option';
            option.dataset.iconChoice = key;
            option.setAttribute('aria-label', `${iconLabel(key)}, identificador ${key}`);
            const art = document.createElement('span');
            art.className = 'admin-icon-option-art';
            art.setAttribute('aria-hidden', 'true');
            art.innerHTML = svg;
            const name = document.createElement('span');
            name.className = 'admin-icon-option-name';
            name.textContent = iconLabel(key);
            const technicalName = document.createElement('span');
            technicalName.className = 'admin-icon-option-key';
            technicalName.textContent = key;
            option.append(art, name, technicalName);
            iconGrid.appendChild(option);
        });

        countLabel.textContent = `${matches.length} ${matches.length === 1 ? 'icono' : 'iconos'}`;
        if (!matches.length) {
            const empty = document.createElement('p');
            empty.className = 'admin-icon-no-results';
            empty.textContent = 'No encontramos iconos con esa búsqueda.';
            iconGrid.appendChild(empty);
        }
    }

    function openOverlay(input, trigger) {
        createOverlay();
        activeInput = input;
        activeTrigger = trigger;
        searchInput.value = '';
        renderIcons();
        overlay.hidden = false;
        document.documentElement.classList.add('admin-icon-modal-open');
        searchInput.focus();
    }

    function closeOverlay() {
        if (!overlay || overlay.hidden) return;
        overlay.hidden = true;
        document.documentElement.classList.remove('admin-icon-modal-open');
        activeTrigger?.focus();
        activeInput = null;
        activeTrigger = null;
    }

    function initialize(root = document) {
        if (!iconEntries.length || !root.querySelectorAll) return;
        root.querySelectorAll(fieldsSelector).forEach((input) => {
            if (input.type !== 'text' || input.dataset.iconPickerReady) return;
            input.dataset.iconPickerReady = '1';
            const control = document.createElement('div');
            control.className = 'admin-icon-control';
            input.parentNode.insertBefore(control, input);
            control.appendChild(input);

            const trigger = document.createElement('button');
            trigger.type = 'button';
            trigger.className = 'admin-icon-trigger';
            trigger.innerHTML = '<span class="admin-icon-trigger-art" data-icon-preview aria-hidden="true"></span><span data-icon-preview-name></span>';
            trigger.addEventListener('click', () => openOverlay(input, trigger));
            control.appendChild(trigger);
            setIconPreview(trigger, input.value.trim());
            input.addEventListener('input', () => setIconPreview(trigger, input.value.trim()));
            input.addEventListener('change', () => setIconPreview(trigger, input.value.trim()));
        });
    }

    window.initAdminIconPicker = initialize;
    initialize(document);
})();
