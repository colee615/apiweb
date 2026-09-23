function reindexCollection(collection) {
    if (!collection) return;
    const baseName = collection.getAttribute('data-base');
    collection.querySelectorAll('[data-row]').forEach(function (row, index) {
        row.querySelectorAll('[data-field]').forEach(function (field) {
            const fieldKey = field.getAttribute('data-field');
            const multipleSuffix = fieldKey === 'files' ? '[]' : '';
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
        };

        typeSelect.dataset.applicationTypeBound = '1';
        typeSelect.addEventListener('change', updateVisibility);
        updateVisibility();
    });
}

document.addEventListener('click', function (event) {
    if (event.target.matches('[data-remove-row]')) {
        const collection = event.target.closest('[data-collection]');
        event.target.closest('[data-row]').remove();
        reindexCollection(collection);
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
    }
});

document.querySelectorAll('[data-collection]').forEach(reindexCollection);
initSortable();
bindPreviewInput(document);
syncHeroMediaFields(document);
syncApplicationFields(document);
document.querySelectorAll('[data-preview-image][src]').forEach(function (img) {
    if (img.dataset.noInlinePreview === '1') return;
    if (img.getAttribute('src')) img.style.display = 'block';
    img.addEventListener('error', function () {
        img.style.display = 'none';
    });
});
