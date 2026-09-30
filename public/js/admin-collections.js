function reindexCollection(collection) {
    if (!collection) return;
    const baseName = collection.getAttribute('data-base');
    collection.querySelectorAll('[data-row]').forEach(function (row, index) {
        row.querySelectorAll('[data-field]').forEach(function (field) {
            const fieldKey = field.getAttribute('data-field');
            const multipleSuffix = field.multiple || fieldKey === 'files' ? '[]' : '';
            field.name = `${baseName}[${index}][${fieldKey}]${multipleSuffix}`;
        });
    });
}

function initSortable() {
    if (typeof Sortable === 'undefined') return;
    document.querySelectorAll('[data-rows]').forEach(function (rows) {
        if (rows.dataset.sortableReady) return;
        rows.dataset.sortableReady = '1';
        Sortable.create(rows, {
            animation: 180,
            handle: '[data-drag]',
            onEnd: function () {
                reindexCollection(rows.closest('[data-collection]'));
                rows.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });
    });
}

function bindPreviewInput(root = document) {
    root.querySelectorAll('[data-preview-input]').forEach(function (input) {
        if (input.dataset.previewBound) return;
        input.dataset.previewBound = '1';
        input.addEventListener('change', function (event) {
            const file = event.target.files && event.target.files[0];
            const card = event.target.closest('[data-row]');
            const preview = card ? card.querySelector('[data-preview-image]') : null;
            if (!file || !preview) return;
            const reader = new FileReader();
            reader.onload = function (e) {
                preview.src = e.target.result;
                if (preview.dataset.noInlinePreview !== '1') preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        });
    });
}

function syncHeroMediaFields(root = document) {
    root.querySelectorAll('[data-row]').forEach(function (row) {
        const mediaType = row.querySelector('[data-media-type]');
        if (!mediaType || mediaType.dataset.mediaTypeBound) {
            if (!mediaType) return;
        }

        const updatePosterVisibility = function () {
            const isVideo = mediaType.value === 'video';
            row.querySelectorAll('[data-poster-field]').forEach(function (field) {
                field.style.display = isVideo ? '' : 'none';
            });
        };

        if (!mediaType.dataset.mediaTypeBound) {
            mediaType.dataset.mediaTypeBound = '1';
            mediaType.addEventListener('change', updatePosterVisibility);
        }

        updatePosterVisibility();
    });
}

function syncApplicationFields(root = document) {
    root.querySelectorAll('[data-application-type]').forEach(function (typeSelect) {
        if (typeSelect.dataset.applicationTypeBound) return;

        const row = typeSelect.closest('[data-row]');
        if (!row) return;

        const updateVisibility = function () {
            const isApplication = typeSelect.value === 'app';

            row.querySelectorAll('[data-application-web-field]').forEach(function (field) {
                field.hidden = isApplication;
            });

            row.querySelectorAll('[data-application-app-field]').forEach(function (field) {
                field.hidden = !isApplication;
            });

            syncApplicationApkFields(row);
        };

        typeSelect.dataset.applicationTypeBound = '1';
        typeSelect.addEventListener('change', updateVisibility);
        updateVisibility();
    });
}

function syncApplicationCategoryOptions() {
    const collection = document.querySelector('[data-collection][data-base="applications[items]"]');
    const list = document.getElementById('application-category-options');
    if (!collection || !list) return;

    const seen = new Set();
    const categories = [];
    collection.querySelectorAll('[data-field="category"]').forEach(function (field) {
        const label = field.value.trim();
        const key = label.toLocaleLowerCase();
        if (!label || seen.has(key)) return;
        seen.add(key);
        categories.push(label);
    });

    list.replaceChildren(...categories.map(function (label) {
        const option = document.createElement('option');
        option.value = label;
        return option;
    }));
}

function syncApplicationApkFields(root = document) {
    root.querySelectorAll('[data-application-apk-field]').forEach(function (field) {
        const row = field.closest('[data-row]');
        if (!row) return;
        const file = row.querySelector('[data-field="download_file"]');
        const chosenFile = file && file.files && file.files[0];
        const fileName = chosenFile ? chosenFile.name : '';
        const name = row.querySelector('[data-field="download_name"]');
        const link = row.querySelector('[data-field="download_url"]');
        const isApk = [fileName, name && name.value, link && link.value].some(function (value) {
            return typeof value === 'string' && /[.]apk(?:[?#].*)?$/i.test(value.trim());
        });
        field.hidden = !isApk;
    });

    root.querySelectorAll('[data-field="download_file"], [data-field="download_name"], [data-field="download_url"]').forEach(function (field) {
        if (field.dataset.apkVisibilityBound) return;
        field.dataset.apkVisibilityBound = '1';
        field.addEventListener(field.matches('input[type="file"]') ? 'change' : 'input', function () {
            syncApplicationApkFields(field.closest('[data-row]'));
        });
    });
}

function makeScreenshotAction(label, action, className = '') {
    const button = document.createElement('button');
    button.type = 'button';
    button.dataset.screenshotAction = action;
    button.setAttribute('aria-label', label);
    button.title = label;
    if (className) button.className = className;
    return button;
}

function createScreenshotEntry(manager, kind, value, position) {
    const entry = document.createElement('figure');
    entry.className = 'cb-screenshot-item';
    entry.dataset.screenshotEntry = '1';
    entry.dataset.screenshotKind = kind;

    const thumb = document.createElement('div');
    thumb.className = 'cb-screenshot-thumb';
    const image = document.createElement('img');
    image.alt = `Captura ${position}`;
    image.loading = 'lazy';
    thumb.appendChild(image);
    entry.appendChild(thumb);

    const caption = document.createElement('figcaption');
    caption.className = 'cb-screenshot-caption';
    const name = document.createElement('span');
    name.className = 'cb-screenshot-name';
    const actions = document.createElement('div');
    actions.className = 'cb-screenshot-actions';
    const drag = makeScreenshotAction(`Arrastrar captura ${position} para ordenar`, 'drag');
    drag.dataset.screenshotDrag = '1';
    delete drag.dataset.screenshotAction;
    drag.textContent = '⠿';
    const up = makeScreenshotAction(`Mover captura ${position} arriba`, 'up');
    up.textContent = '↑';
    const down = makeScreenshotAction(`Mover captura ${position} abajo`, 'down');
    down.textContent = '↓';
    const remove = makeScreenshotAction(`Quitar captura ${position}`, 'remove', 'is-remove');
    remove.textContent = '×';
    actions.append(drag, up, down, remove);
    caption.append(name, actions);
    entry.appendChild(caption);

    if (kind === 'file') {
        entry._screenshotFile = value;
        entry._screenshotObjectUrl = URL.createObjectURL(value);
        image.src = entry._screenshotObjectUrl;
        name.textContent = value.name;
    } else {
        entry.dataset.screenshotUrl = value;
        image.src = value;
        let filename = '';
        try {
            filename = decodeURIComponent(new URL(value, window.location.href).pathname.split('/').pop() || '');
        } catch (error) {
            filename = '';
        }
        name.textContent = filename || `Captura ${position}`;
    }

    image.addEventListener('error', function () {
        entry.classList.add('is-unavailable');
    });
    return entry;
}

function syncScreenshotManager(manager, markDirty = false) {
    const list = manager.querySelector('[data-screenshot-list]');
    const urlField = manager.querySelector('[data-field="screenshots_text"]');
    const orderField = manager.querySelector('[data-field="screenshots_order"]');
    const fileInput = manager.querySelector('[data-field="screenshots_files"]');
    if (!list || !urlField || !orderField) return;

    const entries = [...list.querySelectorAll('[data-screenshot-entry]')];
    const urlEntries = entries.filter(entry => entry.dataset.screenshotKind === 'url');
    const fileEntries = entries.filter(entry => entry.dataset.screenshotKind === 'file');
    const urls = urlEntries.map(entry => entry.dataset.screenshotUrl || '').filter(Boolean);
    urlField.value = urls.join('\n');

    if (fileInput && typeof DataTransfer !== 'undefined') {
        const transfer = new DataTransfer();
        fileEntries.forEach(entry => {
            if (entry._screenshotFile) transfer.items.add(entry._screenshotFile);
        });
        fileInput.files = transfer.files;
    }

    orderField.value = JSON.stringify(entries.map(entry => {
        if (entry.dataset.screenshotKind === 'file') return `file:${fileEntries.indexOf(entry)}`;
        return `url:${urlEntries.indexOf(entry)}`;
    }));

    entries.forEach((entry, index) => {
        const up = entry.querySelector('[data-screenshot-action="up"]');
        const down = entry.querySelector('[data-screenshot-action="down"]');
        const remove = entry.querySelector('[data-screenshot-action="remove"]');
        const drag = entry.querySelector('[data-screenshot-drag]');
        if (up) { up.disabled = index === 0; up.setAttribute('aria-label', `Mover captura ${index + 1} arriba`); }
        if (down) { down.disabled = index === entries.length - 1; down.setAttribute('aria-label', `Mover captura ${index + 1} abajo`); }
        if (remove) remove.setAttribute('aria-label', `Quitar captura ${index + 1}`);
        if (drag) drag.setAttribute('aria-label', `Arrastrar captura ${index + 1} para ordenar`);
    });

    const empty = manager.querySelector('[data-screenshot-empty]');
    if (empty) empty.hidden = entries.length > 0;
    if (markDirty) manager.dispatchEvent(new Event('input', { bubbles: true }));
}

function bindScreenshotManagers(root = document) {
    root.querySelectorAll('[data-screenshot-manager]').forEach(function (manager) {
        if (manager.dataset.screenshotManagerBound) return;
        manager.dataset.screenshotManagerBound = '1';
        const list = manager.querySelector('[data-screenshot-list]');
        const fileInput = manager.querySelector('[data-field="screenshots_files"]');
        if (!list || !fileInput) return;

        fileInput.addEventListener('change', function () {
            [...fileInput.files].forEach(function (file) {
                const position = list.children.length + 1;
                list.appendChild(createScreenshotEntry(manager, 'file', file, position));
            });
            syncScreenshotManager(manager, true);
        });

        if (typeof Sortable !== 'undefined') {
            Sortable.create(list, {
                animation: 160,
                handle: '[data-screenshot-drag]',
                onEnd: function () { syncScreenshotManager(manager, true); }
            });
        }

        syncScreenshotManager(manager);
    });
}

function updateNewsCollectionCounts() {
    const featuredCount = document.querySelector('[data-base="featured_story[items]"] [data-rows]')?.children.length || 0;
    const recentCount = document.querySelector('[data-base="news_grid[items]"] [data-rows]')?.children.length || 0;
    document.querySelectorAll('[data-news-count="featured"]').forEach(function (count) {
        count.textContent = `${featuredCount} destacado(s)`;
    });
    document.querySelectorAll('[data-news-count="recent"]').forEach(function (count) {
        count.textContent = `${recentCount} noticia(s)`;
    });
}

function syncNewsCategoryOptions() {
    const filterCollection = document.querySelector('[data-collection][data-base="category_filters[items]"]');
    if (!filterCollection) return;

    const choices = [];
    const seen = new Set();
    filterCollection.querySelectorAll('[data-row]').forEach(function (row) {
        const labelField = row.querySelector('[data-field="label"]');
        const label = labelField && labelField.value.trim();
        const categoryKey = label && label.toLocaleLowerCase();
        if (!label || seen.has(categoryKey)) return;
        seen.add(categoryKey);

        const activeField = row.querySelector('[data-field="is_active"]');
        choices.push({
            value: label,
            label: activeField && !activeField.checked ? `${label} (inactivo)` : label
        });
    });

    document.querySelectorAll('[data-news-category]').forEach(function (select) {
        const selectedValue = select.value;
        const matchingChoice = choices.find(choice => choice.value.toLocaleLowerCase() === selectedValue.toLocaleLowerCase());
        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = 'Selecciona una categoria';
        select.replaceChildren(placeholder);

        choices.forEach(function (choice) {
            const option = document.createElement('option');
            option.value = choice.value;
            option.textContent = choice.label;
            select.appendChild(option);
        });

        if (selectedValue && !matchingChoice) {
            const legacyOption = document.createElement('option');
            legacyOption.value = selectedValue;
            legacyOption.textContent = `${selectedValue} (sin filtro)`;
            select.appendChild(legacyOption);
        }

        const valueToSelect = matchingChoice ? matchingChoice.value : selectedValue;
        select.value = valueToSelect && [...select.options].some(option => option.value === valueToSelect)
            ? valueToSelect
            : '';
    });
}

document.addEventListener('input', function (event) {
    if (event.target.closest('[data-collection][data-base="category_filters[items]"]')) {
        syncNewsCategoryOptions();
    }
    if (event.target.matches('[data-field="category"]') && event.target.closest('[data-collection][data-base="applications[items]"]')) {
        syncApplicationCategoryOptions();
    }
});

document.addEventListener('change', function (event) {
    if (event.target.closest('[data-collection][data-base="category_filters[items]"]')) {
        syncNewsCategoryOptions();
    }
    if (event.target.matches('[data-field="category"]') && event.target.closest('[data-collection][data-base="applications[items]"]')) {
        syncApplicationCategoryOptions();
    }
});

document.addEventListener('click', function (event) {
    const screenshotAction = event.target.closest('[data-screenshot-action]');
    if (screenshotAction) {
        const manager = screenshotAction.closest('[data-screenshot-manager]');
        const list = manager && manager.querySelector('[data-screenshot-list]');
        const entry = screenshotAction.closest('[data-screenshot-entry]');
        if (!manager || !list || !entry) return;

        const entries = [...list.querySelectorAll('[data-screenshot-entry]')];
        const index = entries.indexOf(entry);
        if (screenshotAction.dataset.screenshotAction === 'remove') {
            if (entry._screenshotObjectUrl) URL.revokeObjectURL(entry._screenshotObjectUrl);
            entry.remove();
        } else if (screenshotAction.dataset.screenshotAction === 'up' && index > 0) {
            list.insertBefore(entry, entries[index - 1]);
        } else if (screenshotAction.dataset.screenshotAction === 'down' && index < entries.length - 1) {
            list.insertBefore(entries[index + 1], entry);
        }
        syncScreenshotManager(manager, true);
        return;
    }

    if (event.target.matches('[data-remove-row]')) {
        const collection = event.target.closest('[data-collection]');
        event.target.closest('[data-row]')?.querySelectorAll('[data-screenshot-entry]').forEach(function (entry) {
            if (entry._screenshotObjectUrl) URL.revokeObjectURL(entry._screenshotObjectUrl);
        });
        event.target.closest('[data-row]').remove();
        reindexCollection(collection);
        updateNewsCollectionCounts();
        syncNewsCategoryOptions();
        syncApplicationCategoryOptions();
    }

    if (event.target.matches('[data-add-row]')) {
        const subpanel = event.target.closest('.subpanel');
        let collection = event.target.closest('[data-collection]');

        if (!collection && subpanel) {
            collection = subpanel.querySelector('[data-collection]');
        }

        if (!collection) return;
        const templateId = collection.getAttribute('data-template');
        const host = collection.querySelector('[data-rows]');
        const fragment = document.querySelector(`#${templateId}`).content.cloneNode(true);
        host.appendChild(fragment);
        reindexCollection(collection);
        initSortable();
        bindPreviewInput(collection);
        bindScreenshotManagers(collection);
        syncHeroMediaFields(collection);
        syncApplicationFields(collection);
        if (typeof window.initAdminIconPicker === 'function') window.initAdminIconPicker(collection);
        updateNewsCollectionCounts();
        syncNewsCategoryOptions();
        syncApplicationCategoryOptions();
    }
});

document.addEventListener('click', function (event) {
    const button = event.target.closest('[data-news-move]');
    if (!button) return;

    const row = button.closest('[data-row]');
    const sourceCollection = row && row.closest('[data-collection]');
    const direction = button.getAttribute('data-news-move');
    const isMovingToRecent = direction === 'recent';
    const targetBase = isMovingToRecent ? 'news_grid[items]' : 'featured_story[items]';
    const targetCollection = document.querySelector(`[data-collection][data-base="${targetBase}"]`);
    const targetRows = targetCollection && targetCollection.querySelector('[data-rows]');

    if (!row || !sourceCollection || !targetCollection || !targetRows || sourceCollection === targetCollection) return;

    if (isMovingToRecent) {
        const badgeInput = row.querySelector('[data-field="badge"]');
        const badgeField = badgeInput && badgeInput.closest('.field');
        if (badgeInput) row.dataset.featuredBadge = badgeInput.value;
        if (badgeField) badgeField.remove();
        button.setAttribute('data-news-move', 'featured');
        button.textContent = 'Destacar noticia';
        button.title = 'Mover esta noticia al bloque destacado';
    } else {
        const fields = row.querySelector('.grid.grid-3');
        const badgeField = document.createElement('div');
        const badgeLabel = document.createElement('label');
        const badgeInput = document.createElement('input');

        badgeField.className = 'field';
        badgeLabel.textContent = 'Etiqueta destacada';
        badgeInput.type = 'text';
        badgeInput.setAttribute('data-field', 'badge');
        badgeInput.value = row.dataset.featuredBadge || 'Destacado';
        badgeField.append(badgeLabel, badgeInput);
        if (fields) fields.prepend(badgeField);

        button.setAttribute('data-news-move', 'recent');
        button.textContent = 'Pasar a recientes';
        button.title = 'Quitar esta noticia del bloque destacado';
    }

    targetRows.appendChild(row);
    reindexCollection(sourceCollection);
    reindexCollection(targetCollection);

    targetRows.dispatchEvent(new Event('change', { bubbles: true }));
    updateNewsCollectionCounts();

    const editor = row.closest('[x-data]');
    const editorData = editor && window.Alpine ? window.Alpine.$data(editor) : null;
    if (editorData && typeof editorData.go === 'function') {
        editorData.go(isMovingToRecent ? 'grid' : 'featured');
    }
});

document.querySelectorAll('[data-collection]').forEach(reindexCollection);
initSortable();
bindPreviewInput(document);
bindScreenshotManagers(document);
syncHeroMediaFields(document);
syncApplicationFields(document);
syncApplicationApkFields(document);
syncNewsCategoryOptions();
syncApplicationCategoryOptions();
document.querySelectorAll('[data-preview-image][src]').forEach(function (img) {
    if (img.dataset.noInlinePreview === '1') return;
    if (img.getAttribute('src')) img.style.display = 'block';
    img.addEventListener('error', function () {
        img.style.display = 'none';
    });
});
