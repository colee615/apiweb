import fs from 'node:fs';
const file='resources/views/layouts/admin.blade.php';let s=fs.readFileSync(file,'utf8');
const templatesStart=s.indexOf('    <template id="link-template">');
const scriptStart=s.indexOf('    <script>\n',templatesStart)>=0?s.indexOf('    <script>\n',templatesStart):s.indexOf('    <script>\r\n',templatesStart);
const scriptEnd=s.indexOf('    </script>',scriptStart)+'    </script>'.length;
if(templatesStart<0 || scriptStart<0 || scriptEnd<0)throw Error('Assets not found');
fs.writeFileSync('resources/views/admin/pages/partials/collections.blade.php',s.slice(templatesStart,scriptStart).trim()+'\n');
fs.writeFileSync('public/js/admin-collections.js',s.slice(scriptStart,scriptEnd).replace(/^    <script>\r?\n/,'').replace(/    <\/script>$/,'').replace(/^        /gm,'').trim()+'\n');
s=s.slice(0,templatesStart)+`    @if (request()->routeIs('admin.pages.edit') && !$isHistoryMode)
        @include('admin.pages.partials.collections')
        <script src="{{ asset('js/admin-collections.js') }}?v=2" defer></script>
    @endif
`+s.slice(scriptEnd);
s=s.replace('    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>',`    @if (request()->routeIs('admin.pages.edit') && !str_starts_with((string) request('tab', ''), 'history'))
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    @endif`);
s=s.replace('<a class="skip-link" href="#main-content">','<a class="skip-link" href="{{ request()->routeIs(\'admin.login*\') ? \'#login-content\' : \'#main-content\' }}">');
fs.writeFileSync(file,s);
const login='resources/views/admin/auth/login.blade.php';fs.writeFileSync(login,fs.readFileSync(login,'utf8').replace('<div class="login-card">','<div class="login-card" id="login-content" tabindex="-1">'));
for(const name of fs.readdirSync('resources/views/admin/pages').filter(n=>n.startsWith('edit')&&n.endsWith('.blade.php'))){
 const p='resources/views/admin/pages/'+name;let s=fs.readFileSync(p,'utf8');
 s=s.replace(/<input type="checkbox" name="is_active"/g,'<input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active"');
 s=s.replaceAll('<div class="section-eyebrow">Header</div>','<div class="section-eyebrow">Encabezado</div>').replaceAll('<div class="section-eyebrow">Footer</div>','<div class="section-eyebrow">Pie de página</div>').replaceAll('<div class="section-eyebrow">Hero</div>','<div class="section-eyebrow">Portada</div>').replaceAll('<div class="section-eyebrow">CTA</div>','<div class="section-eyebrow">Contacto y botones</div>');
 fs.writeFileSync(p,s);
}
