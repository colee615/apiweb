import fs from 'node:fs';
import postcss from 'postcss';
const combined=postcss.parse(fs.readFileSync('public/css/admin-editor.css','utf8')+'\n'+fs.readFileSync('public/css/admin.css','utf8'));
const seen=new Map();
const rules=[];combined.walkRules(rule=>rules.push(rule));
for(const rule of rules.reverse()){
 let scope='';for(let parent=rule.parent;parent&&parent.type!=='root';parent=parent.parent)scope=parent.name+parent.params+'/'+scope;
 const key=scope+'|'+rule.selector;const props=seen.get(key)||new Map();
 for(const decl of [...rule.nodes].reverse()){
  if(decl.type!=='decl')continue;
  if(props.has(decl.prop) && (props.get(decl.prop) || !decl.important))decl.remove();
  else props.set(decl.prop,!!decl.important);
 }
 seen.set(key,props);if(!rule.nodes.length)rule.remove();
}
combined.walk(node=>{
 let depth=0;for(let p=node.parent;p&&p.type!=='root';p=p.parent)depth++;
 node.raws.before='\n'+'    '.repeat(depth);
 node.raws.between=node.type==='decl'?': ':' ';
 if(node.nodes){node.raws.after='\n'+'    '.repeat(depth);node.raws.semicolon=true;}
});
fs.writeFileSync('public/css/admin.css',combined.toString().trim()+'\n');
const p='resources/views/layouts/admin.blade.php';let s=fs.readFileSync(p,'utf8');s=s.replace(/    <link rel="stylesheet" href="\{\{ asset\('css\/admin-editor.css'\) \}\}\?v=2">\r?\n/,'');fs.writeFileSync(p,s);
