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

document.addEventListener('click', function (event) {
    if (event.target.matches('[data-remove-row]')) {
        const collection = event.target.closest('[data-collection]');
        event.target.closest('[data-row]').remove();
        reindexCollection(collection);
        updateNewsCollectionCounts();
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
        syncHeroMediaFields(collection);
        syncApplicationFields(collection);
        if (typeof window.initAdminIconPicker === 'function') window.initAdminIconPicker(collection);
        updateNewsCollectionCounts();
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
syncHeroMediaFields(document);
syncApplicationFields(document);
syncApplicationApkFields(document);
document.querySelectorAll('[data-preview-image][src]').forEach(function (img) {
    if (img.dataset.noInlinePreview === '1') return;
    if (img.getAttribute('src')) img.style.display = 'block';
    img.addEventListener('error', function () {
        img.style.display = 'none';
    });
});
