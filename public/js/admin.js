(() => {
    // Associate existing CMS labels without changing field names or submitted data.
    let nextId = 0;
    const labelFields = root => root.querySelectorAll('.field').forEach(field => {
        const label = field.querySelector('label');
        const input = field.querySelector('input:not([type=hidden]),textarea,select');
        if (!label || !input || label.contains(input)) return;
        if (!input.id) input.id = `admin-field-${++nextId}`;
        if (!label.htmlFor) label.htmlFor = input.id;
    });
    labelFields(document);
    document.querySelectorAll('.nav-link').forEach(link => {
        link.title = link.textContent.trim();
        if (link.classList.contains('active')) link.setAttribute('aria-current', 'page');
    });
    document.querySelectorAll('[data-table-search]').forEach(input => {
        const container = input.closest('.table-shell');
        const rows = [...container.querySelectorAll('tbody tr[data-search-row]')];
        const empty = container.querySelector('[data-search-empty]');
        const normalize = value => value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
        input.addEventListener('input', () => {
            const query = normalize(input.value.trim());
            rows.forEach(row => row.hidden = !normalize(row.textContent).includes(query));
            if (empty) empty.hidden = rows.some(row => !row.hidden);
        });
    });
    const form = document.querySelector('[data-editor-form]');
    if (!form) return;
    let dirty = false;
    const status = form.querySelector('[data-save-status]');
    const markDirty = () => {
        dirty = true;
        if (status) { status.textContent = 'Cambios sin guardar'; status.classList.add('is-dirty'); }
    };
    form.addEventListener('input', markDirty);
    form.addEventListener('change', markDirty);
    form.addEventListener('click', event => {
        if (event.target.closest('[data-add-row],[data-remove-row]')) markDirty();
    });
    form.querySelectorAll('[data-rows]').forEach(rows => rows.addEventListener('end', markDirty));
    new MutationObserver(() => labelFields(form)).observe(form, {childList:true,subtree:true});
    window.addEventListener('beforeunload', event => {
        if (!dirty) return;
        event.preventDefault(); event.returnValue = '';
    });
    form.addEventListener('submit', () => {
        dirty = false;
        form.querySelectorAll('button[type=submit]').forEach(button => {
            button.disabled = true; button.textContent = 'Guardando…';
        });
    });
    // Service editors share the same section navigation as the main CMS editors.
    const existingNav = form.querySelector('.editor-nav-list');
    if (existingNav) {
        const component = form.closest('[x-data]');
        const buttons = [...existingNav.querySelectorAll('[\\@click]')];
        const tabs = buttons.map(button => button.getAttribute('@click')?.match(/go\('([^']+)'\)/)?.[1]).filter(Boolean);
        if (window.Alpine && component && tabs.length) {
            const data = Alpine.$data(component);
            if (!tabs.includes(data.tab)) data.tab = tabs[0] === 'announcement' ? 'design_text' : tabs[0];
            form.addEventListener('invalid', event => {
                const section = event.target.closest('[x-show]');
                const tab = section?.getAttribute('x-show')?.match(/tab === '([^']+)'/)?.[1];
                if (tab) data.tab = tab;
            }, true);
        }
        return;
    }
    const sections = [...form.querySelectorAll(':scope > .section-card')];
    if (sections.length < 2) return;
    const layout = document.createElement('div'); layout.className = 'editor-layout';
    const aside = document.createElement('aside'); aside.className = 'editor-sidebar';
    const nav = document.createElement('nav'); nav.className = 'editor-nav'; nav.setAttribute('aria-label','Secciones de la página');
    const title = document.createElement('h3'); title.textContent = 'Secciones'; nav.append(title);
    const list = document.createElement('div'); list.className = 'editor-nav-list'; nav.append(list); aside.append(nav);
    const main = document.createElement('div'); main.className = 'editor-main';
    form.insertBefore(layout, sections[0]); layout.append(aside,main);
    const select = (index, updateUrl = true) => {
        sections.forEach((section,i) => section.hidden = i !== index);
        [...list.children].forEach((button,i) => { button.classList.toggle('active',i === index); button.setAttribute('aria-pressed',String(i === index)); });
        if (updateUrl) { const url = new URL(location.href); url.searchParams.set('section',String(index+1)); history.replaceState(null,'',url); }
    };
    sections.forEach((section,index) => {
        main.append(section); section.id ||= `editor-section-${index+1}`;
        const button = document.createElement('button'); button.type = 'button'; button.className = 'editor-nav-button';
        const text = document.createElement('strong'); text.textContent = section.querySelector('.section-eyebrow')?.textContent.trim() || section.querySelector('.section-title')?.textContent.trim() || `Sección ${index+1}`;
        button.append(text); button.setAttribute('aria-controls',section.id);
        button.addEventListener('click',() => select(index)); list.append(button);
    });
    const requested = Number(new URL(location.href).searchParams.get('section'))-1;
    select(Number.isInteger(requested) && requested >= 0 && requested < sections.length ? requested : 0,false);
    form.addEventListener('invalid',event => {
        const index = sections.findIndex(section => section.contains(event.target));
        if (index >= 0) select(index);
    },true);
})();
