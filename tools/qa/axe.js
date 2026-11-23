// Accesibilidad con axe-core (WCAG 2.2 AA). Requiere: npm i axe-core. Uso: MSC_URL=https://sitio node tools/qa/axe.js
const { chromium } = require('playwright'); const fs=require('fs');
const axe = fs.readFileSync(require.resolve('axe-core/axe.min.js',{paths:[process.cwd()]}),'utf8');
const B=process.env.MSC_URL||'http://127.0.0.1:8080';
const urls=['/','/el-museo/','/colecciones/','/colecciones/libros/','/colecciones/libros/pieza-ejemplo-01/','/exposiciones/','/exposiciones/pasadas/','/exposiciones/exposicion-actual-ejemplo/','/eventos/','/eventos/evento-ejemplo-1/','/publicaciones/','/publicaciones/calendarios/','/publicaciones/publicacion-ejemplo-01/','/colaboraciones/','/visitanos/','/?s=pieza','/no-existe/','/colaboraciones/?msc_form=x&msc_id=form-colaboracion'];
(async()=>{const b=await chromium.launch(); let total=0;
for (const w of [1440,390]) { const p=await (await b.newContext({viewport:{width:w,height:900}})).newPage();
 for (const u of urls){ await p.goto(B+u); await p.addScriptTag({content:axe});
  const r=await p.evaluate(async()=>await axe.run(document,{runOnly:['wcag2a','wcag2aa','wcag21a','wcag21aa','wcag22aa','best-practice']}));
  for (const v of r.violations){ total++; console.log(w,u,v.impact,v.id,v.nodes.length, v.nodes[0].target.join(' '));} } }
console.log('violaciones:',total); await b.close();})();
