import puppeteer from 'puppeteer';
import fs from 'node:fs';
const browser=await puppeteer.launch({headless:true,executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe'});
const page=await browser.newPage(); const errors=[],report=[];
page.on('pageerror',e=>errors.push(e.message));page.on('dialog',d=>d.accept());
await page.setRequestInterception(true);
page.on('request',r=>r.resourceType()==='image'&&!r.url().startsWith('blob:')?r.abort():r.continue());
await page.setViewport({width:1440,height:1000});
await page.goto('http://127.0.0.1:8777/admin/login',{waitUntil:'networkidle2'});
await page.type('[name=email]','review@example.test');await page.type('[name=password]','Review-local-2026');
await Promise.all([page.waitForNavigation({waitUntil:'networkidle2'}),page.click('button[type=submit]')]);
const links=await page.$$eval('a.button[href*="/pages/"]',els=>els.map(e=>e.href));
for(const url of links){
 await page.goto(url,{waitUntil:'networkidle2'});
 const audit=await page.evaluate(()=>{
  const files=[...document.querySelectorAll('[data-editor-form] input[type=file]')];
  const colors=[...document.querySelectorAll('[data-editor-form] input[type=text]')].filter(e=>/(?:color|colour)(?:\]|$)/i.test(e.dataset.field||e.name));
  return {files:files.length,enhanced:files.filter(e=>e.dataset.mediaReady).length,unmapped:files.filter(e=>!e.dataset.previewSources).map(e=>e.name),colors:colors.length,colorPickers:document.querySelectorAll('.visual-color-picker').length};
 });
 if(audit.files!==audit.enhanced||audit.unmapped.length||audit.colors!==audit.colorPickers)errors.push({url,audit});
 // Every individual upload gets a new preview, including files inside hidden tabs.
 const previews=await page.evaluate(()=>{
  let checked=0;const bad=[];
  for(const input of document.querySelectorAll('[data-editor-form] input[type=file]')){
   const data=new DataTransfer();data.items.add(new File(['review'],'review.png',{type:'image/png'}));input.files=data.files;input.dispatchEvent(new Event('change',{bubbles:true}));
   if(input.closest('.field').querySelector('.asset-selected img'))checked++;else bad.push(input.name);
   input.closest('.field').querySelector('.asset-clear').click();
   if(input.files.length||input.closest('.field').querySelectorAll('.asset-selected .asset-card').length)bad.push('Cancel '+input.name);
  }
  return {checked,bad};
 });
 if(previews.bad.length)errors.push({url,previews});
 await page.setViewport({width:390,height:844});
 await page.waitForFunction(()=>document.querySelector('.sidebar').getBoundingClientRect().right<=0);
 const nav=await page.$$('.editor-nav-button');
 for(const button of nav){await button.click();if(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth))errors.push('Mobile overflow '+url);}
 await page.setViewport({width:1440,height:1000});
 report.push({url,...audit,checked:previews.checked});console.log('Preview audit',url,audit.files);
}
await page.goto('http://127.0.0.1:8777/admin/pages/4/edit',{waitUntil:'networkidle2'});
const picker=await page.$('.visual-color-picker');
await picker.evaluate(e=>{e.value='#ffcc22';e.dispatchEvent(new Event('input',{bubbles:true}));});
const color=await page.$eval('[name="theme[primary_color]"]',e=>e.value);
if(color!=='#ffcc22')errors.push('Color not synced');
await page.screenshot({path:'storage/admin-visual-colors.png',fullPage:true});
await page.goto('http://127.0.0.1:8777/admin/pages/11/edit',{waitUntil:'networkidle2'});
await page.click('.editor-nav-button:last-child');
const add=await page.$('[data-add-row]');await add.click();
await page.waitForFunction(()=>[...document.querySelectorAll('input[type=file]')].every(e=>e.dataset.mediaReady));
const result=await page.evaluate(()=>{
 const input=[...document.querySelectorAll('input[type=file][multiple]')].at(-1);const data=new DataTransfer();
 data.items.add(new File(['%PDF-1.4'],'documento.pdf',{type:'application/pdf'}));data.items.add(new File(['a,b\n1,2'],'tabla.csv',{type:'text/csv'}));data.items.add(new File(['video'],'video.webm',{type:'video/webm'}));
 input.files=data.files;input.dispatchEvent(new Event('change',{bubbles:true}));
 const field=input.closest('.field');return {cards:field.querySelectorAll('.asset-card').length,pdf:!!field.querySelector('.asset-document'),video:!!field.querySelector('video'),name:input.name};
});
if(result.cards!==3||!result.pdf||!result.video||!result.name.endsWith('[files][]'))errors.push(result);
await page.screenshot({path:'storage/admin-file-previews.png',fullPage:true});
await browser.close();console.log(JSON.stringify({report,errors},null,2));fs.writeFileSync('storage/admin-media-report.json',JSON.stringify({report,errors},null,2));if(errors.length)process.exitCode=1;
