import fs from 'node:fs';
const walk=p=>fs.readdirSync(p,{withFileTypes:true}).flatMap(e=>e.isDirectory()?walk(p+'/'+e.name):[p+'/'+e.name]);
const words = [[/\bhero\b/gi,'portada'],[/\bCTA\b/g,'botón de acción'],[/\blanding\b/gi,'página'],[/\bfrontend\b/gi,'sitio web'],[/\bheader\b/gi,'encabezado'],[/\bfooter\b/gi,'pie de página'],[/\bbadge\b/gi,'etiqueta'],[/\bchips\b/gi,'etiquetas cortas'],[/\bmock\b/gi,'vista de ejemplo'],[/\bslider\b/gi,'carrusel'],[/\bslides\b/gi,'imágenes o videos'],[/\bslide\b/gi,'imagen o video'],[/\bbullets\b/gi,'puntos de la lista'],[/\bSEO\b/g,'información para buscadores'],[/\blayout global\b/gi,'diseño compartido'],[/\bpreview\b/gi,'vista previa'],[/\bwatermark\b/gi,'marca de agua']];
for(const file of walk('resources/views/admin/pages').filter(p=>p.endsWith('.blade.php')&&!p.includes('history'))){
 let s=fs.readFileSync(file,'utf8');
 s=s.replace(/(<(?:label|p|h[34]|span)[^>]*>)([^<>{]+)(<\/(?:label|p|h[34]|span)>)/g,(match,start,text,end)=>{
  for(const [from,to] of words)text=text.replace(from,to);
  return start+text+end;
 });
 fs.writeFileSync(file,s);
}
