import puppeteer from 'puppeteer';
import fs from 'node:fs';
const browser=await puppeteer.launch({headless:true,executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe'});
const page=await browser.newPage(); const errors=[]; const report=[];
page.on('pageerror',e=>errors.push(e.message));page.on('dialog',d=>d.accept());
await page.setRequestInterception(true);
page.on('request', request => request.resourceType() === 'image' ? request.abort() : request.continue());
await page.setViewport({width:1440,height:1000});
await page.goto('http://127.0.0.1:8777/admin/login',{waitUntil:'networkidle2'});
await page.type('[name=email]','review@example.test');await page.type('[name=password]','Review-local-2026');
await Promise.all([page.waitForNavigation({waitUntil:'networkidle2'}),page.click('button[type=submit]')]);
const links=await page.$$eval('a.button[href*="/pages/"]',els=>els.map(e=>e.href));
for(const url of links){
 await page.goto(url,{waitUntil:'networkidle2'});
 const before=await page.$eval('#page-edit-form',f=>[...new FormData(f)].filter(([k,v])=>typeof v==='string' && !['_token','_method','change_summary'].includes(k)));
 await page.type('[name=change_summary]','Verificación del editor en base aislada');
 const valid=await page.$eval('#page-edit-form',f=>f.checkValidity());
 if(!valid){errors.push({url,invalid:await page.$$eval(':invalid',els=>els.map(e=>({name:e.name,message:e.validationMessage})))});continue;}
 await Promise.all([page.waitForNavigation({waitUntil:'networkidle2'}),page.click('.save-dock button[type=submit]')]);
 const result=await page.evaluate(()=>({url:location.href,success:document.querySelector('.notice-success')?.textContent.trim(),error:document.querySelector('.notice-error,.admin-modal-list')?.textContent.trim()}));
 if(!result.success || result.error)errors.push({url,...result});
 const after=await page.$eval('#page-edit-form',f=>[...new FormData(f)].filter(([k,v])=>typeof v==='string' && !['_token','_method','change_summary'].includes(k)));
 const missing=before.filter(([key,value])=>value!=='' && !after.some(([k,v])=>k===key && v===value));
 report.push({url,...result,changedFields:missing.map(([name])=>name)});
 console.log('Saved',url,result.success || result.error);
 await page.goto(url+'?tab=history_overview',{waitUntil:'networkidle2'});
 const history=await page.evaluate(()=>({versions:document.querySelectorAll('.history-version').length,changes:document.querySelectorAll('.history-change').length}));
 if(!history.versions || !history.changes)errors.push({url,history});
}
// Check restoring content on the isolated Home page after a visible edit.
await page.goto('http://127.0.0.1:8777/admin/pages/1/edit',{waitUntil:'networkidle2'});
const original=await page.$eval('[name=name]',e=>e.value);
await page.$eval('[name=name]',e=>{e.value='Inicio de prueba';e.dispatchEvent(new Event('input',{bubbles:true}));});
await Promise.all([page.waitForNavigation({waitUntil:'networkidle2'}),page.click('.save-dock button[type=submit]')]);
await page.goto('http://127.0.0.1:8777/admin/pages/1/edit?tab=history_overview',{waitUntil:'networkidle2'});
await page.screenshot({path:'storage/admin-history.png',fullPage:true});
const restore=await page.$('.history-version form button');
if(restore){await Promise.all([page.waitForNavigation({waitUntil:'networkidle2'}),restore.click()]);await page.goto('http://127.0.0.1:8777/admin/pages/1/edit',{waitUntil:'networkidle2'});if(await page.$eval('[name=name]',e=>e.value)!==original)errors.push('Restore did not recover original name');}else errors.push('No restore action');
await page.setViewport({width:390,height:844});
await page.goto('http://127.0.0.1:8777/admin/analytics',{waitUntil:'networkidle2'});
await page.screenshot({path:'storage/admin-analytics-mobile.png',fullPage:true});
const sidebar=await page.$eval('.sidebar',e=>e.getBoundingClientRect().right);
if(sidebar>1)errors.push('Mobile menu visible while closed: '+sidebar);
await page.click('.menu-toggle');await page.waitForFunction(()=>document.querySelector('.sidebar').getBoundingClientRect().left>=0);
await page.mouse.click(370,400);
await page.waitForFunction(()=>document.querySelector('.sidebar').getBoundingClientRect().right<=0);
await browser.close();
console.log(JSON.stringify({report,errors},null,2));fs.writeFileSync('storage/admin-save-report.json',JSON.stringify({report,errors},null,2));
if(errors.length)process.exitCode=1;
