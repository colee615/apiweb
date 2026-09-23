import fs from 'node:fs';
const walk=p=>fs.readdirSync(p,{withFileTypes:true}).flatMap(e=>e.isDirectory()?walk(p+'/'+e.name):[p+'/'+e.name]);
console.log([...new Set(walk('resources/views/admin/pages').filter(p=>p.endsWith('.php')).flatMap(p=>[...fs.readFileSync(p,'utf8').matchAll(/<input[^>]+type="file"[^>]*>/g)].map(m=>m[0])))].join('\n'));
