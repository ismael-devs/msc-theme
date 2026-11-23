// QA de formularios. Uso: MSC_URL=https://sitio MSC_FOTO=/ruta/foto.jpg node tools/qa/formularios.js
const { chromium } = require('playwright');
(async () => {
  const b = await chromium.launch(); const p = await b.newPage();
  // 1) Envío vacío: validación en cliente
  await p.goto((process.env.MSC_URL||'http://127.0.0.1:8080')+'/colaboraciones/');
  await p.click('#form-colaboracion button[type=submit]');
  console.log('cliente errores:', await p.locator('#form-colaboracion .msc-form__error').count(), '| foco en:', await p.evaluate(()=>document.activeElement.name));
  // 2) Campos condicionales
  console.log('procedencia visible antes:', await p.isVisible('#form-colaboracion-procedencia'));
  await p.check('#form-colaboracion-tipo-donacion');
  console.log('procedencia visible tras Donación:', await p.isVisible('#form-colaboracion-procedencia'), '| plazo:', await p.isVisible('#form-colaboracion-plazo'));
  // 3) Envío válido (esperando > 3 s por la trampa de tiempo)
  await p.fill('#form-colaboracion-nombre','Prueba QA'); await p.fill('#form-colaboracion-correo','qa@example.test');
  await p.fill('#form-colaboracion-procedencia','Herencia familiar'); await p.fill('#form-colaboracion-mensaje','Mensaje de prueba');
  if (process.env.MSC_FOTO) await p.setInputFiles('#form-colaboracion-fotos', process.env.MSC_FOTO);
  await p.check('#form-colaboracion-privacidad');
  await p.waitForTimeout(3200);
  await Promise.all([p.waitForNavigation(), p.click('#form-colaboracion button[type=submit]')]);
  console.log('URL:', p.url()); console.log('aviso:', await p.locator('#form-colaboracion .msc-aviso').innerText());
  // 4) Servidor sin JS: envío vacío
  const c2 = await b.newContext({ javaScriptEnabled:false }); const p2 = await c2.newPage();
  await p2.goto((process.env.MSC_URL||'http://127.0.0.1:8080')+'/visitanos/'); await p2.waitForTimeout(3200);
  await Promise.all([p2.waitForNavigation(), p2.click('#form-visita button[type=submit]')]);
  console.log('sin JS:', (await p2.locator('#form-visita .msc-aviso--error').innerText()).replace(/\n/g,' | '));
  await b.close();
})();
