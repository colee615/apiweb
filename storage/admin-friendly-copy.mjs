import fs from 'node:fs';
const p='resources/views/admin/analytics/index.blade.php';let s=fs.readFileSync(p,'utf8');
const start=s.indexOf('    <section class="table-shell"><div class="table-toolbar"><div><strong>Búsquedas');
if(start<0)throw Error('Tracking table missing');
const end=s.indexOf('</section>',start)+10;
s=s.slice(0,start)+"    @include('admin.analytics.tracking')"+s.slice(end);
s=s.replace('Personas identificadas en el período','Visitantes diferentes en el período');
fs.writeFileSync(p,s);
const walk=p=>fs.readdirSync(p,{withFileTypes:true}).flatMap(e=>e.isDirectory()?walk(p+'/'+e.name):[p+'/'+e.name]);
const replacements={
 'Slug':'Dirección de la página', 'Título SEO':'Título en buscadores', 'Titulo SEO':'Título en buscadores', 'Descripción SEO':'Descripción en buscadores', 'Descripcion SEO':'Descripción en buscadores',
 'URL':'Enlace', 'Link de redirección':'Enlace de destino', 'Badge':'Etiqueta destacada', 'Badge opcional':'Etiqueta opcional', 'Texto del badge':'Texto de la etiqueta',
 'Estilo de preview':'Estilo de la vista previa', 'Etiqueta del preview':'Etiqueta de la vista previa', 'Título del preview':'Título de la vista previa',
 'Hero':'Portada', 'CTA':'Botones de acción', 'Header':'Encabezado', 'Footer':'Pie de página', 'Poster':'Portada del video', 'Subir poster':'Subir portada del video',
 'Icono':'Ícono', 'Icono visual':'Ícono del bloque', 'Icono central':'Ícono principal', 'Nombre accesible':'Descripción para lectores de pantalla',
};
for(const file of walk('resources/views/admin/pages').filter(p=>p.endsWith('.blade.php')&&!p.includes('history'))){
 let s=fs.readFileSync(file,'utf8');
 s=s.replace(/<label>([^<]*)<\/label>/g,(match,text)=>'<label>'+(replacements[text] || text.replace(/^URL /,'Enlace ').replace(/\bCTA\b/g,'botón'))+'</label>');
 s=s.replace("{{ $page->is_active ? 'checked' : '' }}","{{ old('is_active', $page->is_active) ? 'checked' : '' }}");
 fs.writeFileSync(file,s);
}
