import puppeteer from 'puppeteer';
import fs from 'node:fs';
const browser = await puppeteer.launch({headless:true,executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe'});
const page = await browser.newPage();
const errors = [];
page.on('pageerror', error => errors.push(error.message));
page.on('dialog', dialog => dialog.accept());
await page.setViewport({width:1440,height:1000});
await page.goto('http://127.0.0.1:8777/admin/login',{waitUntil:'networkidle2'});
await page.screenshot({path:'storage/admin-login.png'});
await page.type('[name=email]','review@example.test');
await page.type('[name=password]','Review-local-2026');
await Promise.all([page.waitForNavigation({waitUntil:'networkidle2'}),page.click('button[type=submit]')]);
if(!page.url().endsWith('/admin')) throw Error('Login failed: '+page.url());
await page.screenshot({path:'storage/admin-dashboard.png'});
const links = await page.$$eval('a.button[href*="/pages/"]', links=>links.map(a=>a.href));
const report=[];
for(const url of links){
    await page.goto(url,{waitUntil:'networkidle2'});
    const checks = await page.evaluate(()=>({title:document.querySelector('h1')?.textContent.trim(),form:!!document.querySelector('[data-editor-form]'),sections:document.querySelectorAll('.editor-nav-button').length,visible:[...document.querySelectorAll('.section-card')].filter(e=>e.getClientRects().length).length,overflow:document.documentElement.scrollWidth>innerWidth}));
    if(!checks.form || !checks.sections || checks.visible < 1) errors.push('Editor invalid '+url+JSON.stringify(checks));
    // Exercise every section without changing content.
    const buttons=await page.$$('.editor-nav-button');
    for(const button of buttons){await button.click();}
    await page.setViewport({width:390,height:844});
    const mobileOverflow=await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth);
    if(mobileOverflow) errors.push('Mobile overflow '+url);
    await page.setViewport({width:1440,height:1000});
    report.push({...checks,url,mobileOverflow});
}
await page.goto(links[0],{waitUntil:'networkidle2'});
await page.screenshot({path:'storage/admin-editor.png'});
await page.goto('http://127.0.0.1:8777/admin/analytics',{waitUntil:'networkidle2'});
await page.screenshot({path:'storage/admin-analytics.png',fullPage:true});
await page.setViewport({width:390,height:844});
await page.screenshot({path:'storage/admin-analytics-mobile.png',fullPage:true});
if(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth)) errors.push('Analytics mobile overflow');
await page.goto('http://127.0.0.1:8777/admin/users',{waitUntil:'networkidle2'});
await page.goto('http://127.0.0.1:8777/admin/users/create',{waitUntil:'networkidle2'});
await page.screenshot({path:'storage/admin-user-mobile.png'});
console.log(JSON.stringify({report,errors},null,2));
fs.writeFileSync('storage/admin-browser-report.json',JSON.stringify({report,errors},null,2));
await browser.close();
if(errors.length) process.exitCode=1;
