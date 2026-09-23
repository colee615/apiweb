import fs from 'node:fs';
const read = p => fs.readFileSync(p, 'utf8');
const write = (p,s) => fs.writeFileSync(p,s);
const root = 'resources/views/admin/pages/';
let home = read(root+'edit.blade.php');
const helper = home.slice(home.indexOf('    $currentVersionNumber'), home.indexOf('@endphp'));
write(root+'partials/history-format.blade.php', '@php\n    $historySections = $historyData[\'history_sections\'];\n'+helper+'@endphp\n');
home = home.slice(0,home.indexOf('    $currentVersionNumber')) + home.slice(home.indexOf('@endphp'));
let start = home.indexOf('                <div class="editor-nav" style="margin-top:18px;" x-show="isHistoryTab()">');
home = home.slice(0,start)+home.slice(home.indexOf('            </aside>',start));
start = home.indexOf('                <section id="history-root"');
const end = home.indexOf('\n            </div>\n\n        </div>',start);
home = home.slice(0,start)+home.slice(end);
home = home.replace(/<script>[\s\S]*?<\/script>\s*@endsection/, '@endsection');
write(root+'edit.blade.php',home);
for(const name of fs.readdirSync(root).filter(n=>n.startsWith('edit')&&n.endsWith('.blade.php'))){
 let s=read(root+name);
 const start=s.indexOf('<div class="save-dock">');
 if(start<0) throw Error(name+' save missing');
 let cursor=start,depth=0,end;
 const tags=/<\/?div\b[^>]*>/g; tags.lastIndex=start;
 for(let match;(match=tags.exec(s));){depth+=match[0].startsWith('</')?-1:1;if(!depth){end=tags.lastIndex;break;}}
 s=s.slice(0,start)+"@include('admin.pages.partials.save')"+s.slice(end);
 const form=s.indexOf('<form');
 s=s.slice(0,form)+"@include('admin.pages.partials.header')\n\n    "+s.slice(form);
 if(!s.includes('id="page-edit-form"')) s=s.replace('<form ', '<form id="page-edit-form" ');
 s=s.replace('class="stack" enctype="multipart/form-data"','class="stack" data-editor-form enctype="multipart/form-data"');
 s=s.replace(/tab: @js\(request\('tab', 'design_text'\)\)/g,"tab: @js(request('tab', 'design_text'))");
 s=s.replace('En este modo solo ves herramientas de edición. El historial queda separado en su propio submenú.','Selecciona el bloque que quieres editar.');
 s=s.replace('Esta pagina solo administra su contenido propio. Header y footer quedan fuera para evitar duplicados.','Selecciona el bloque que quieres editar.');
 s=s.replace(/Secciones de dise[nñ]o/g,'Secciones');
 // Repair only known mojibake sequences without altering content values.
 for(const [a,b] of Object.entries({'TÃ­tulo':'Título','DescripciÃ³n':'Descripción','DuraciÃ³n':'Duración','AÃ±o':'Año','CÃ³digo':'Código','DirecciÃ³n':'Dirección','SÃ¡bado':'Sábado','TelÃ©fono':'Teléfono','PosiciÃ³n':'Posición'})) s=s.split(a).join(b);
 write(root+name,s);
}
let layout=read('resources/views/layouts/admin.blade.php');
const css=layout.match(/    <style>([\s\S]*?)    <\/style>/)[1];
// Retain the specialized visual editor classes; the shell and forms use one stylesheet.
fs.mkdirSync('public/css',{recursive:true});
write('public/css/admin-editor.css',css.slice(css.indexOf('        .repeater-card {'),css.indexOf('        @media (max-width: 1500px)')));
layout=layout.replace(/    <style>[\s\S]*?    <\/style>/,'    <link rel="stylesheet" href="{{ asset(\'css/admin-editor.css\') }}?v=2">\n    <link rel="stylesheet" href="{{ asset(\'css/admin.css\') }}?v=2">');
layout=layout.replace("{{ $title ?? 'Studio Admin' }}","{{ $title ?? ($page->name ?? 'Administración') }} · Correos de Bolivia");
layout=layout.replace('<body>','<body>\n<a class="skip-link" href="#main-content">Saltar al contenido</a>');
layout=layout.replace('sidebarCollapsed: false }','sidebarCollapsed: false }');
layout=layout.replace('<aside class="sidebar">','<button x-cloak x-show="sidebarOpen" class="sidebar-backdrop" @click="sidebarOpen = false" aria-label="Cerrar menú"></button>\n        <aside id="admin-navigation" class="sidebar" @keydown.escape.window="sidebarOpen = false">');
layout=layout.replace('<h1>Correos de Bolivia</h1>','<a class="brand-home" href="{{ route(\'admin.dashboard\') }}"><span class="brand-mark">C<span>↗</span></span><strong>Correos de Bolivia</strong></a>');
layout=layout.replace('<nav class="nav-group">','<div class="nav-caption">ADMINISTRACIÓN</div>\n            <nav class="nav-group" aria-label="Navegación principal">');
layout=layout.replace('<span>Diseno</span>','<span>Contenido del sitio</span>').replace('<span>Estadisticas</span>','<span>Estadísticas</span>');
layout=layout.replace('🎨','▤').replace('📈','↗').replace('🕘','◷').replace('👤','♙');
layout=layout.replace('aria-label="Menu"','aria-label="Alternar menú" aria-controls="admin-navigation" :aria-expanded="window.innerWidth <= 1080 ? sidebarOpen : !sidebarCollapsed"');
layout=layout.replace('<div class="topbar-actions">','<div class="topbar-actions">\n                    <span class="account-name">{{ $adminUser->name ?? \'Equipo editorial\' }}<small>{{ $adminUser->role ?? \'\' }}</small></span>');
layout=layout.replace('Cerrar sesion','Cerrar sesión').replace('<div class="content-inner">','<div id="main-content" class="content-inner" tabindex="-1">');
layout=layout.replace('        function initSortable() {','        function initSortable() {\n            if (typeof Sortable === \'undefined\') return;');
layout=layout.replace('</body>','    <script src="{{ asset(\'js/admin.js\') }}?v=2" defer></script>\n</body>');
for(const [a,b] of Object.entries({'TÃ­tulo':'Título','DescripciÃ³n':'Descripción','DuraciÃ³n':'Duración','AÃ±o':'Año','CÃ³digo':'Código','DirecciÃ³n':'Dirección','SÃ¡bado':'Sábado','TelÃ©fono':'Teléfono','PosiciÃ³n':'Posición'})) layout=layout.split(a).join(b);
write('resources/views/layouts/admin.blade.php',layout);
