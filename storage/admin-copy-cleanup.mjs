import fs from 'node:fs';
let p='resources/views/admin/pages/edit.blade.php';let s=fs.readFileSync(p,'utf8');
s=s.replace("    $historySections = $historyData['history_sections'];\n",'');
const start=s.indexOf('        go(section) {'),end=s.indexOf('\n    }"',start);
s=s.slice(0,start)+`        go(section) {
            this.tab = section;
            const url = new URL(window.location.href);
            url.searchParams.set('tab', section);
            url.hash = '';
            window.history.replaceState({}, '', url);
        }`+s.slice(end);
s=s.replace(' x-show="!isHistoryTab()"','');
const button='<button type="button" class="editor-nav-button" :class="{ \'active\': tab === \'header\' }" @click="go(\'header\')"><strong>Encabezado</strong><span>Menú y enlaces globales</span></button>\n                        <button type="button" class="editor-nav-button" :class="{ \'active\': tab === \'tools\' }" @click="go(\'tools\')"><strong>Herramientas</strong><span>Rastreo y calculadora</span></button>\n                        ';
s=s.replace('<strong>Diseño</strong><span>Textos, logo y enlaces</span>','<strong>Configuración</strong><span>Nombre, publicación y marca</span>');
s=s.replace('<button type="button" class="editor-nav-button" :class="{ \'active\': tab === \'backgrounds\' }"',button+'<button type="button" class="editor-nav-button" :class="{ \'active\': tab === \'backgrounds\' }"');
let count=0;s=s.replace(/<section class="section-card" x-show="tab === 'design_text'">/g,match=>{count++;return count===2?match.replace('design_text','header'):count===3?match.replace('design_text','tools'):match;});
const replacements={'Startup announcement':'Aviso de inicio','EMS showcase':'Servicio EMS','Landing EMS administrable':'Contenido de EMS','Navigation system':'Navegación','Encabezado y menu':'Encabezado y menú','Hero media':'Multimedia','Service gallery':'Servicios','Utility area':'Herramientas','App promotion':'Aplicaciones','Commerce curation':'Filatelia','Market y productos':'Productos de filatelia','Closure and contact':'Contacto','<strong>Footer</strong>':'<strong>Pie de página</strong>','<strong>Landing EMS</strong>':'<strong>EMS</strong>'};
for(const [a,b] of Object.entries(replacements))s=s.split(a).join(b);
fs.writeFileSync(p,s);
for(const name of ['edit-news.blade.php','edit-about.blade.php']){
 const p='resources/views/admin/pages/'+name;let s=fs.readFileSync(p,'utf8');
 for(const [a,b] of Object.entries({'Diseno':'Diseño','Configuracion':'Configuración','Titulo':'Título','Descripcion':'Descripción','Duracion':'Duración','Mision':'Misión','vision':'visión','Categorias':'Categorías','busqueda':'búsqueda','Boletin':'Boletín','Suscripcion':'Suscripción','paginacion':'paginación','<strong>Grid</strong>':'<strong>Noticias</strong>','Hero principal':'Noticia principal','Esta vista centraliza el contenido editorial sin duplicar el layout global.':'Selecciona el bloque que quieres editar.'}))s=s.split(a).join(b);
 fs.writeFileSync(p,s);
}
