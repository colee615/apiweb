(() => {
    const form = document.querySelector('[data-editor-form]');
    if (!form) return;
    form.classList.add('media-enhanced');
    const urls = new Map();
    const make = (tag, className, text) => {
        const element = document.createElement(tag);
        if (className) element.className = className;
        if (text) element.textContent = text;
        return element;
    };
    const safeUrl = value => {
        if (!value || value === '#') return null;
        try {
            const url = new URL(value, location.href);
            return ['http:', 'https:', 'blob:'].includes(url.protocol) ? url.href : null;
        } catch { return null; }
    };
    const filename = value => {
        try { return decodeURIComponent(new URL(value, location.href).pathname.split('/').pop()) || 'Archivo actual'; }
        catch { return 'Archivo actual'; }
    };
    const release = input => {
        (urls.get(input) || []).forEach(url => URL.revokeObjectURL(url));
        urls.delete(input);
    };
    const formatSize = bytes => bytes >= 1048576 ? `${(bytes / 1048576).toFixed(1)} MB` : `${Math.max(1, Math.round(bytes / 1024))} KB`;

    function previewCard(src, name, mime = '', size = null, file = null) {
        const card = make('article', 'asset-card');
        const url = safeUrl(src);
        const extension = name.split('.').pop().toLowerCase();
        const isImage = mime.startsWith('image/') || /^(png|jpe?g|gif|webp|avif|svg|bmp|ico)$/.test(extension);
        const isVideo = mime.startsWith('video/') || /^(mp4|webm|mov|m4v|ogv)$/.test(extension);
        const isAudio = mime.startsWith('audio/') || /^(mp3|wav|ogg|m4a)$/.test(extension);
        const isPdf = mime === 'application/pdf' || extension === 'pdf';
        const visual = make('div', 'asset-visual');
        const status = make('p', 'asset-message');
        if (url && (isImage || isVideo || isAudio)) {
            const media = make(isImage ? 'img' : (isVideo ? 'video' : 'audio'));
            if (isImage) { media.alt = `Vista previa de ${name}`; media.loading = 'lazy'; }
            else { media.controls = true; media.preload = 'metadata'; }
            media.addEventListener('error', () => {
                media.hidden = true;
                status.textContent = 'No se pudo mostrar este archivo. Puedes abrirlo para revisarlo o elegir otro.';
            });
            media.src = url;
            visual.append(media);
        } else if (url && isPdf) {
            const details = make('details', 'asset-document');
            details.append(make('summary', '', 'Ver documento PDF'));
            details.addEventListener('toggle', () => {
                if (!details.open || details.querySelector('iframe')) return;
                const frame = make('iframe'); frame.title = `Vista previa de ${name}`;
                frame.src = url; frame.loading = 'lazy';
                details.append(frame);
            });
            visual.append(make('span', 'asset-file-icon', 'PDF'), details);
            status.textContent = 'Si tu navegador no muestra el documento, utiliza «Abrir archivo».';
        } else {
            visual.append(make('span', 'asset-file-icon', extension.length <= 6 ? extension.toUpperCase() : 'ARCHIVO'));
            status.textContent = 'Este formato se revisa abriéndolo con su aplicación. El archivo se conserva en su formato original.';
            if (file && (mime.startsWith('text/') || /^(txt|csv|json)$/.test(extension))) {
                file.slice(0, 12000).text().then(text => {
                    const excerpt = make('pre', 'asset-text', text);
                    visual.append(excerpt);
                    status.textContent = file.size > 12000 ? 'Vista previa de los primeros 12 KB.' : 'Vista previa del contenido.';
                });
            }
        }
        const info = make('div', 'asset-info');
        info.append(make('strong', '', name));
        if (size !== null) info.append(make('small', '', formatSize(size)));
        if (url) {
            const link = make('a', 'asset-open', 'Abrir archivo ↗');
            link.href = url; link.target = '_blank'; link.rel = 'noopener noreferrer';
            info.append(link);
        }
        card.append(visual, info, status);
        return card;
    }

    const sourceFields = input => {
        const key = input.dataset.field || input.name.match(/\[([^\]]+)\]$/)?.[1] || '';
        const aliases = {
            logo_file: ['logo_url', 'logo'], media_file: ['media_url', 'src'],
            poster_file: ['poster_image', 'poster'], background_file: ['background_image', 'background'],
            register_qr_file: ['register_qr_image'], files: ['attachments_json', 'src'], file: ['src'],
        };
        const candidates = aliases[key] || [key.replace(/_file$/, '')];
        const row = input.closest('[data-row]');
        const prefix = input.name.replace(/\[[^\]]+\]$/, '');
        const fields = [...(row || form).querySelectorAll('input:not([type=file])')];
        return candidates.map(candidate => fields.find(field => row
            ? field.dataset.field === candidate
            : field.name === `${prefix}[${candidate}]`)).filter(Boolean);
    };

    function enhanceUpload(input) {
        if (input.dataset.mediaReady) return;
        input.dataset.mediaReady = '1';
        const field = input.closest('.field') || input.parentElement;
        const sources = sourceFields(input);
        input.dataset.previewSources = sources.map(source => source.dataset.field || source.name).join(',');
        const existing = make('div', 'asset-grid asset-existing');
        const selected = make('div', 'asset-grid asset-selected');
        selected.setAttribute('aria-live', 'polite');
        const help = make('p', 'field-help', input.multiple
            ? 'Puedes seleccionar varios archivos. Verás cada uno aquí antes de guardar.'
            : 'Elige un archivo y revísalo aquí. Se publicará cuando guardes los cambios.');
        const clear = make('button', 'asset-clear', 'Cancelar selección'); clear.type = 'button'; clear.hidden = true;
        field.append(help, existing, selected, clear);
        const renderCurrent = () => {
            existing.replaceChildren();
            const attachments = sources.find(source => source.dataset.field === 'attachments_json');
            let files = [];
            if (attachments) {
                try { files = JSON.parse(attachments.value || '[]'); } catch { files = []; }
                if (!Array.isArray(files)) files = [];
            }
            if (!files.length) {
                const source = sources.find(source => source.dataset.field !== 'attachments_json' && source.value.trim());
                if (source) {
                    let mime = '';
                    if (input.accept === 'image/*' || input.accept === '.jpg,.jpeg,.png,.webp,.svg') mime = 'image/*';
                    const prefix = input.name.replace(/\[[^\]]+\]$/, '');
                    const mediaType = input.closest('[data-row]')?.querySelector('[data-field=media_type]')
                        || [...form.querySelectorAll('select')].find(select => select.name === `${prefix}[media_type]`);
                    if (mediaType?.value === 'video') mime = 'video/*';
                    if (mediaType?.value === 'image') mime = 'image/*';
                    files = [{src:source.value.trim(), file_name:filename(source.value), file_mime:mime}];
                }
            }
            if (!files.length) {
                existing.append(make('p','asset-empty','Sin archivo guardado. Puedes agregar uno desde este campo.'));
                return;
            }
            existing.append(make('span','asset-heading', input.multiple ? `Archivos guardados (${files.length})` : 'Archivo guardado'));
            files.forEach(file => {
                if (file?.src) existing.append(previewCard(file.src, file.file_name || filename(file.src), file.file_mime || ''));
            });
        };
        const updateSelection = () => {
            release(input); selected.replaceChildren();
            const files = [...input.files]; clear.hidden = !files.length;
            if (files.length) selected.append(make('span','asset-heading', input.multiple ? `Nuevos archivos (${files.length})` : 'Nuevo archivo · pendiente de guardar'));
            files.forEach(file => {
                const url = URL.createObjectURL(file);
                urls.set(input, [...(urls.get(input) || []), url]);
                selected.append(previewCard(url, file.name, file.type, file.size, file));
            });
            existing.classList.toggle('has-replacement', !input.multiple && !!files.length);
        };
        clear.addEventListener('click', () => { input.value = ''; input.dispatchEvent(new Event('change', {bubbles:true})); });
        input.addEventListener('change', updateSelection);
        sources.forEach(source => {
            source.addEventListener('input', renderCurrent);
            if (source.dataset.field === 'attachments_json' && source.closest('.field')) {
                source.closest('.field').classList.add('asset-legacy-summary');
            }
            if (source.type === 'hidden' || source.closest('details')) return;
            const sourceField = source.closest('.field');
            if (!sourceField || sourceField === field) return;
            const details = make('details', 'asset-source');
            details.append(make('summary', '', 'Usar un enlace en lugar de subir un archivo'));
            sourceField.insertBefore(details,source); details.append(source);
            const label = sourceField.querySelector('label');
            if (label) label.textContent = 'Enlace del archivo';
            details.append(make('p','field-help','Opcional. Pega la dirección de una imagen, video o documento que ya esté publicado.'));
            // Keep the current link and upload together; retain the original input and its name.
            field.append(details);
            if (!sourceField.querySelector('input,textarea,select')) sourceField.remove();
        });
        const clearExisting = input.closest('[data-row]')?.querySelector('[data-field=clear_attachments]');
        clearExisting?.addEventListener('change', () => {
            existing.classList.toggle('will-remove', clearExisting.checked);
            existing.setAttribute('aria-label',clearExisting.checked ? 'Estos archivos se quitarán al guardar' : 'Archivos guardados');
        });
        renderCurrent();
    }

    function enhanceColor(input) {
        if (input.dataset.colorReady) return;
        input.dataset.colorReady = '1';
        const field = input.closest('.field') || input.parentElement;
        const control = make('div','color-control');
        const picker = make('input','visual-color-picker'); picker.type = 'color';
        picker.setAttribute('aria-label', `Elegir ${(field.querySelector('label')?.textContent || 'color').toLowerCase()}`);
        input.setAttribute('aria-label', `Código de ${(field.querySelector('label')?.textContent || 'color').toLowerCase()}`);
        input.before(control); control.append(picker,input);
        const sample = make('button','color-sample'); sample.type = 'button';
        sample.setAttribute('aria-label', picker.getAttribute('aria-label'));
        sample.addEventListener('click', () => { if (picker.showPicker) picker.showPicker(); else picker.click(); });
        sample.append(make('span','','Elegir este color'),make('strong','','Aa'));
        const help = make('p','field-help','Haz clic en la muestra para elegir el color. El código se actualiza automáticamente.');
        field.append(sample,help);
        const sync = () => {
            const value = input.value.trim();
            const expanded = /^#[a-f\d]{3}$/i.test(value) ? '#'+[...value.slice(1)].map(c=>c+c).join('') : value;
            if (!/^#[a-f\d]{6}$/i.test(expanded)) {
                sample.hidden = true; help.textContent = 'Usa el selector o un código como #245BB2.'; return;
            }
            picker.value = expanded; sample.hidden = false; sample.style.backgroundColor = expanded;
            const rgb = expanded.slice(1).match(/../g).map(v=>parseInt(v,16));
            sample.style.color = (rgb[0]*299+rgb[1]*587+rgb[2]*114)/1000 > 150 ? '#18263b' : '#fff';
            help.textContent = 'Haz clic en la muestra para elegir el color. El código se actualiza automáticamente.';
        };
        picker.addEventListener('input', () => { input.value = picker.value; input.dispatchEvent(new Event('input',{bubbles:true})); });
        input.addEventListener('input',sync); sync();
    }

    const enhance = () => {
        form.querySelectorAll('input[type=file]').forEach(enhanceUpload);
        form.querySelectorAll('input[type=text]').forEach(input => {
            const key = input.dataset.field || input.name;
            if (/(?:color|colour)(?:\]|$)/i.test(key)) enhanceColor(input);
            const field = input.closest('.field');
            if (!field || field.dataset.helpReady || field.querySelector('.field-help')) return;
            const help = {
                slug: 'Es la parte final del enlace de esta página. Por ejemplo: casillas. Cambiarla modifica su dirección.',
                meta_title: 'Nombre que aparece en la pestaña del navegador y en buscadores como Google.',
                meta_description: 'Describe brevemente la página. Este texto ayuda a las personas a reconocerla en los buscadores.',
            }[key];
            if (help) {field.append(make('p', 'field-help', help));field.dataset.helpReady='1';}
        });
        form.querySelectorAll('button').forEach(button => {
            if (button.getAttribute('@click')?.startsWith('openPreviewFromCard')) button.hidden = true;
        });
        // Release object URLs when a repeater item is removed.
        for (const input of urls.keys()) if (!input.isConnected) release(input);
    };
    enhance();
    let scheduled = false;
    new MutationObserver(records => {
        if (scheduled || !records.some(record => [...record.addedNodes].some(node => node.nodeType === 1 && (node.matches?.('[data-row]') || node.querySelector?.('input[type=file]:not([data-media-ready])')))
            || [...record.removedNodes].some(node => node.nodeType === 1 && node.matches?.('[data-row]')))) return;
        scheduled = true; queueMicrotask(() => {scheduled=false;enhance();});
    }).observe(form,{childList:true,subtree:true});
    window.addEventListener('pagehide', () => {for(const input of urls.keys())release(input);});
})();
