// Capturas 1440/390 de todas las plantillas. Uso: MSC_URL=https://sitio node tools/qa/capturas.js
const { chromium } = require('playwright');
const B=(process.env.MSC_URL||'http://127.0.0.1:8080')+'';
const pages = {
 'index':'/', 'el-museo':'/el-museo/', 'colecciones':'/colecciones/', 'colecciones-categoria':'/colecciones/libros/',
 'colecciones-ficha':'/colecciones/libros/pieza-ejemplo-01/', 'exposiciones':'/exposiciones/', 'exposiciones-pasadas':'/exposiciones/pasadas/',
 'exposiciones-ficha':'/exposiciones/pasadas/los-pardo/', 'eventos':'/eventos/', 'eventos-ficha':'/eventos/evento-ejemplo-3/',
 'publicaciones':'/publicaciones/', 'publicaciones-ficha':'/publicaciones/publicacion-ejemplo-02/', 'publicaciones-calendarios':'/publicaciones/calendarios/',
 'colaboraciones':'/colaboraciones/', 'visitanos':'/visitanos/', 'busqueda':'/?s=pieza'
};
const only = process.argv[2] ? process.argv[2].split(',') : null;
(async () => {
  require('fs').mkdirSync('tools/qa/capturas',{recursive:true});
  const b = await chromium.launch();
  for (const w of [1440, 390]) {
    const ctx = await b.newContext({ viewport: { width: w, height: 900 }, reducedMotion: 'reduce' });
    const p = await ctx.newPage();
    p.on('console', m => { if (m.type()==='error') console.log('console', w, m.text()); });
    for (const [n,u] of Object.entries(pages)) {
      if (only && !only.includes(n)) continue;
      await p.goto(B+u, {waitUntil:'networkidle'});
      await p.evaluate(async()=>{for(let y=0;y<document.body.scrollHeight;y+=600){window.scrollTo(0,y);await new Promise(r=>setTimeout(r,40));}window.scrollTo(0,0);});
      await p.waitForLoadState('networkidle');
      await p.screenshot({ path: `tools/qa/capturas/${n}-${w}.png`, fullPage: true });
    }
    await ctx.close();
  }
  await b.close();
})();
