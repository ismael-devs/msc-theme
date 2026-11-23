# Museo Sefardí de Caracas — tema y plugin de WordPress

Rediseño de museosefardidecaracas.org.ve. Este repositorio contiene dos piezas:

| Carpeta | Qué es |
|---|---|
| `wp-content/plugins/msc-contenido/` | **Plugin de contenido.** Tipos de contenido (Colecciones, Exposición, Evento, Publicación), campos, URL y redirecciones 301, estados por fecha, formularios, JSON-LD, SEO técnico y datos de ejemplo. El contenido no depende del tema. |
| `wp-content/themes/msc-theme/` | **Tema clásico**, ligero y accesible, con las plantillas de la maqueta (`handoff/reference/html-paleta/`). |
| `tools/` | Empaquetado (`empaquetar.sh`) y scripts de QA con Playwright (`tools/qa/`). |
| `handoff/` | Documentación y maqueta de referencia entregadas por el equipo. |
| `phpcs.xml.dist` | Reglas de código (WordPress Coding Standards + compatibilidad PHP 8.1+). |

## Requisitos

- **WordPress 6.6 o superior** (probado en 7.1.5) y **PHP 8.1–8.4** (probado en 8.3; el código se revisa con PHPCompatibility para 8.1+).
- **Secure Custom Fields (SCF)**, gratuito, en WordPress.org. Incluye repetidores, galerías, relaciones y página de opciones. Si el museo compra ACF PRO, el código funciona igual.
- **Polylang**, gratuito. Español por defecto; inglés en `/en/` en una fase posterior.
- Opcional: Yoast SEO o Rank Math. Si están activos, ellos generan los metadatos y las migas en JSON-LD, y el plugin solo añade los tipos del museo, sin duplicar.

No se usa ningún otro plugin.

## Instalación

1. Genera los .zip con `./tools/empaquetar.sh` (crea `dist/msc-contenido.zip` y `dist/msc-theme.zip`) o copia las carpetas por SFTP.
2. En **Plugins → Añadir nuevo** instala y activa **Secure Custom Fields** y **Polylang**.
3. Sube y activa **MSC Contenido** (Plugins → Añadir nuevo → Subir plugin).
4. Sube y activa **MSC Theme** (Apariencia → Temas → Añadir nuevo → Subir tema).
5. **Ajustes → Enlaces permanentes**: elige "Nombre de la entrada" (`/%postname%/`) y guarda. Esto también vacía las reglas de URL.
6. **Ajustes → Generales**: zona horaria **Caracas** (los estados por fecha dependen de ella) e idioma del sitio **Español**.
7. **Idiomas (Polylang)**:
   - Añade **Español** primero; será el idioma por defecto.
   - Añade **English** solo cuando se publique la versión en inglés.
   - En *Ajustes → URL modifications* deja "El idioma se define por el nombre de directorio" y marca "Ocultar el código del idioma por defecto", para que el español quede sin `/es/` y el inglés en `/en/`.
   - En *Sincronización* marca "Campos personalizados" y "Plantilla de página", para que la traducción copie la estructura.
   - Asigna el español al contenido existente cuando Polylang lo pida.
8. Crea las páginas y asigna a cada una su **plantilla** (atributo "Plantilla" en el editor):

   | Página | Slug | Plantilla |
   |---|---|---|
   | Inicio | `inicio` | (por defecto; márcala en *Ajustes → Lectura* como página de inicio) |
   | El museo | `el-museo` | El museo |
   | Colecciones | `colecciones` | Colecciones (portada) |
   | Exposiciones | `exposiciones` | Exposiciones (portada) |
   | Colaboraciones | `colaboraciones` | Colaboraciones |
   | Visítanos | `visitanos` | Visítanos |
   | Política de privacidad | `politica-de-privacidad` | (por defecto; márcala en *Ajustes → Privacidad*) |

   Eventos, Publicaciones, Exposiciones pasadas y Calendarios anteriores **no son páginas**: las genera el plugin en `/eventos/`, `/publicaciones/`, `/exposiciones/pasadas/` y `/publicaciones/calendarios/`.
9. **Apariencia → Menús**: crea el menú "Principal" con las 7 secciones en este orden: El museo, Colecciones, Exposiciones, Eventos, Publicaciones, Colaboraciones, Visítanos. Asígnalo a "Menú principal". Si no hay menú, el tema muestra esas 7 secciones igualmente.
10. **Museo** (menú lateral): completa los datos globales (dirección, teléfonos, horarios, instituciones aliadas, correo de los formularios…).
11. Logo: súbelo en **Apariencia → Personalizar → Identidad del sitio**, o colócalo en `wp-content/themes/msc-theme/assets/img/logo.png`. Sin logo se muestra el nombre del museo en EB Garamond.

### Instalación rápida con datos de ejemplo (sin WP-CLI)

En **Herramientas → Datos de ejemplo MSC**, el botón **Crear datos de ejemplo** hace lo mismo que `wp msc seed` (reemplaza los pasos 5 y 8–10). El botón **Borrar datos de ejemplo** equivale a `wp msc limpiar`.

### Instalación rápida con datos de ejemplo (WP-CLI)

Con WP-CLI hay un atajo que reemplaza los pasos 5 y 8–10. Crea las páginas con sus plantillas y los textos reales de `docs/04`, el menú, las opciones y contenido de prueba de todas las plantillas:

```bash
wp msc seed
```

Cuando el museo cargue su contenido real, borra solo el contenido de ejemplo (las entradas e imágenes marcadas como ejemplo):

```bash
wp msc limpiar
```

Para ejecutar en el momento la revisión diaria de estados (la misma que hace el cron):

```bash
wp msc estados
```

> ⚠️ `wp msc seed` sobrescribe los campos de las páginas editoriales y del Inicio con los textos de partida. No lo ejecutes en producción después de que el museo haya editado esas páginas.

## Guía rápida para el equipo del museo

Todo se edita desde el administrador, sin tocar código. El **Escritorio** tiene un recuadro "Museo Sefardí: atajos".

| Tarea | Dónde |
|---|---|
| Publicar una **pieza** | Colecciones → Añadir nueva pieza. Elige la categoría a la derecha. La primera imagen de la galería es la principal y **cada imagen necesita texto alternativo** (si falta, no deja guardar). "Pieza destacada" la muestra en la portada de Colecciones. |
| Publicar una **exposición** | Exposiciones → Añadir nueva. Con las **fechas de inicio y de cierre**, el sitio la marca sola como *Próxima*, *En cartelera* o *Pasada*. Al pasar la fecha de cierre, su dirección cambia sola a `/exposiciones/pasadas/…` y la antigua redirige (301). |
| Publicar un **evento** | Eventos → Añadir nuevo. Si escribes una **descripción**, el evento tiene página propia. Si no, aparece solo como una línea en la lista. Pasa solo de *Próximo* a *Pasado*. |
| Publicar una **publicación** | Publicaciones → Añadir nueva. Tipo "Libro / catálogo". No hay botones de compra ni de descarga. |
| Publicar un **calendario** nuevo | Publicaciones → Añadir nueva, tipo **Calendario**: año civil, año hebreo y PDF. |
| Cambiar el **calendario destacado** del Inicio | Marca "Calendario destacado" en el calendario nuevo. Se muestra el destacado con el año civil más reciente; no hace falta desmarcar el anterior. |
| Editar las **instituciones aliadas** (marquee) | Museo → pestaña *Instituciones aliadas*: logo, nombre y enlace. |
| Horarios, tarifas, teléfonos, mapa | Museo → pestañas *Contacto* y *Horarios y visita*. |
| Textos en inglés de los datos globales | Museo → pestaña *English*. |
| Ver los mensajes de los formularios | Museo → Envíos (también llegan por correo). |
| Redirecciones del sitio antiguo | Museo → Redirecciones. |

**Reglas editoriales que aplica el sitio:**

- Los campos vacíos no se muestran, así que no quedan títulos huérfanos.
- Si un párrafo supera las **80 palabras**, el administrador avisa al guardar (sin bloquear) para que lo dividas con Intro.
- Lo que está **[por confirmar]** aparece con un recuadro amarillo **solo para quien ha iniciado sesión**. El público no lo ve.

## URL, estados y redirecciones

| Contenido | URL |
|---|---|
| Pieza | `/colecciones/{categoría}/{pieza}/`. Si se pide con otra categoría, responde 301 a la correcta. |
| Categoría | `/colecciones/libros/` (`/page/2/` para paginar). Todas las piezas: `/colecciones/todas/` |
| Exposición vigente o próxima | `/exposiciones/{slug}/` |
| Exposición pasada | `/exposiciones/pasadas/{slug}/`, con 301 automático desde la URL anterior y viceversa |
| Archivo de pasadas | `/exposiciones/pasadas/` (filtros `?msc_q=` y `?msc_anio=`) |
| Eventos / ficha | `/eventos/` y `/eventos/{slug}/`. Sin descripción, 301 a `/eventos/`. |
| Publicaciones / ficha / calendarios | `/publicaciones/`, `/publicaciones/{slug}/`, `/publicaciones/calendarios/` |
| Inglés | Las mismas rutas con el prefijo `/en/` |

- Los slugs `pasadas`, `calendarios`, `todas` y `page` están reservados y no se pueden usar como nombre de una entrada.
- Los estados usan la zona horaria de WordPress (America/Caracas). Una exposición sigue en cartelera hasta que termina su día de cierre.
- Un cron diario (00:05) detecta los cambios de estado y vacía las cachés. El hook `msc_cache_limpiada` sirve para purgar una caché de página externa.

**Redirecciones 301 del sitio antiguo**: `wp-content/plugins/msc-contenido/data/redirects.csv` sale de rastrear el sitio actual (92 URL, incluidas sus versiones en inglés y francés).

- Estados de la tabla: **11 `ok`**, **79 `revisar`** y **2 `pendiente`**.
  - `revisar`: casos dudosos para que los valides. Son sobre todo exposiciones y eventos antiguos cuyo slug depende de cómo se migren; mientras no existan, redirigen a su listado.
  - `pendiente`: el PDF de nacionalidad y un audio. Sin destino, no redirigen.
- Puedes añadir reglas en Museo → Redirecciones; esas tienen prioridad.

## Formularios

Colaboraciones, Visita guiada y Boletín son formularios propios del plugin.

- **Seguridad:** nonce, honeypot, tiempo mínimo de 3 s y límite por IP (8 envíos cada 10 min).
- **Validación** en servidor y en el navegador, con mensajes accesibles (`aria-live`). Funcionan también sin JavaScript.
- **Bloques condicionales** según el tipo de colaboración (Donación, Adquisición, Comodato).
- **Fotos adjuntas:** JPG, PNG o WebP. El límite de cantidad y de tamaño se ajusta en Museo → Formularios.
- **Cada envío:**
  - se guarda en **Museo → Envíos**;
  - se envía al correo configurado, con *Reply-To* de quien escribe;
  - se manda una copia de recepción a quien escribe (desactivable).
- **Campos:** los comunes (nombre, correo, teléfono, ciudad) y los condicionales son **de ejemplo, [por confirmar]**. Para cambiarlos, edita `msc_definicion_formulario()` en `includes/formularios.php` o usa el filtro `msc_formulario_campos`.
- **Boletín:** guarda las suscripciones en Envíos. Para conectarlo a un servicio de correo, usa la acción `msc_envio_guardado`.
- **Envío de correo:** se recomienda configurar SMTP en el hosting para que los correos de WordPress no acaben en spam.

## Multiidioma (preparado, no publicado)

- Todas las cadenas del tema y del plugin usan el dominio de texto `msc`.
- Los cuatro tipos de contenido y sus taxonomías están declarados como **traducibles en Polylang**. Cada contenido en inglés tiene **los mismos campos**.
- La página de opciones "Museo" tiene una **pestaña English** (campos `_en`). Polylang gratuito no traduce páginas de opciones; si un campo `_en` está vacío, en `/en/` se muestra el texto en español.
- El selector **ES | EN** se ve desde el lanzamiento:
  - mientras no exista el idioma inglés, "EN" aparece como *próximamente* (no es un enlace);
  - después, lleva a la traducción o, en listados y búsquedas, a la misma ruta en `/en/`.
- `hreflang`: lo genera Polylang cuando hay traducciones.
- **Probado** con ES + EN: `/en/colecciones/books/sample-piece-01/` responde bien y tiene `lang="en"`, `hreflang` y selector correcto.
- **Pendiente para la fase en inglés:** traducir las cadenas fijas del tema (por ejemplo, con Loco Translate o un archivo `msc-en_US.po`) y los slugs de las páginas.

## Rendimiento, SEO y accesibilidad

**Rendimiento:**
- CSS crítico en línea (tokens + base) y **un solo CSS** (`assets/css/msc.css`) cargado sin bloquear el render.
- **Un solo JS** (`assets/js/msc.js`), diferido, sin jQuery.
- Fuentes **autohospedadas** en WOFF2 con `font-display: swap`: EB Garamond 500 y Jost 400/600/700 (licencia OFL incluida). Se precargan las de títulos y texto.
- Imágenes con `srcset`/`sizes`, `loading="lazy"` y `fetchpriority="high"` en la imagen principal. Sin CLS.
- El mapa se carga solo cuando el visitante lo pide.

**SEO:**
- `rel=canonical`, descripción y Open Graph.
- `noindex` en filtros y búsquedas.
- Sitemap de WordPress con las 4 CPT (solo los eventos con ficha) y las vistas de archivo.

**Accesibilidad:**
- Enlace "Saltar al contenido", landmarks, `aria-current`, foco visible (cian, 3 px).
- Objetivos táctiles de 44–48 px.
- `prefers-reduced-motion`: el marquee pasa a cuadrícula fija.
- **Marquee:** gira en ciclo continuo, sin botón. Se detiene al pasar el ratón, al enfocar con el teclado o con un toque (otro toque lo reanuda). Así cumple la WCAG 2.2.2 sin botón visible.
- El dorado `#cc942b` nunca se usa como texto sobre blanco.

### Resultados de QA (entorno local, WordPress 7.1.5 + PHP 8.3, datos de ejemplo)

| Prueba | Resultado |
|---|---|
| Lighthouse móvil, Inicio | Rendimiento **99** · Accesibilidad **100** · Buenas prácticas **100** · SEO **100** (LCP 1,9 s, CLS 0) |
| Lighthouse móvil, ficha de pieza | 99 · 100 · 100 · 100 (LCP 2,0 s, CLS 0) |
| Lighthouse móvil, Eventos | 99 · 100 · 100 · 100 |
| axe-core (WCAG 2.2 AA + buenas prácticas), 18 páginas × 1440/390 px | **0 incidencias** |
| PHPCS (WordPress + PHPCompatibility 8.1+) | **0 errores** (6 avisos revisados: falsos positivos o parámetros de interfaz) |
| `php -l` en todos los archivos | sin errores |
| Rutas, 301 y respaldos de redirección | todas correctas |
| Cambio de estado por fecha (exposición que cierra) | 301 a `/pasadas/`, aparece en el archivo de pasadas y el Inicio destaca la siguiente |
| Formularios: validación sin JS y con JS, condicionales, adjuntos, registro y correos | correctos |
| JSON-LD | Museum, ExhibitionEvent, Event, Book/CreativeWork, VisualArtwork/CreativeWork y BreadcrumbList |

Los resultados de Lighthouse en el servidor real dependerán del hosting: compresión, caché de página y HTTP/2.

**Recomendaciones para el hosting:**
- caché de página;
- Brotli/gzip;
- caducidad larga para `/wp-content/themes/msc-theme/assets/`.

### Repetir la QA

Desarrollo local, sin MySQL: WordPress con el drop-in SQLite y el servidor de PHP.

```bash
composer global require squizlabs/php_codesniffer wp-coding-standards/wpcs phpcompatibility/phpcompatibility-wp
```

```bash
phpcs
```

```bash
MSC_URL=http://127.0.0.1:8080 node tools/qa/capturas.js
```

```bash
MSC_URL=http://127.0.0.1:8080 node tools/qa/axe.js
```

```bash
MSC_URL=http://127.0.0.1:8080 node tools/qa/interaccion.js
```

```bash
MSC_URL=http://127.0.0.1:8080 node tools/qa/formularios.js
```

## Decisiones y diferencias con la maqueta

- **Pie de página:** según la maqueta, el Inicio lleva Boletín | Contacto | Explora y el resto de páginas lleva Nombre + Contacto | Explora. En las fichas de exposición y en Eventos y Publicaciones, el boletín está en la banda oscura "Recibe nuestras novedades".
- **Hero del Inicio:** el texto va sobre un panel claro semitransparente, para mantener el contraste AA con cualquier fotografía. En móvil va debajo de la imagen, como en la maqueta.
- **Imágenes:** las proporciones siguen la maqueta, pero son menos extremas en las fichas para no recortar demasiado las fotografías reales. Las portadas de publicaciones no se recortan.
- **Búsqueda del header:** en lugar de enlazar a Colecciones (como en la maqueta), abre un buscador general con resultados agrupados por tipo.
- **"Todas"** en las pestañas de Colecciones lleva a `/colecciones/todas/`.
- **Filtros:** en escritorio se muestran abiertos; en móvil quedan plegados (`<details>`) salvo que haya filtros activos.
- **Menú del admin:** "Entradas" y "Comentarios" están ocultos porque el sitio no tiene Noticias ni comentarios (filtro `msc_ocultar_entradas` para volver a mostrarlos).

## Pendientes del museo (dejados editables o como [por confirmar])

Según `handoff/docs/06-pendientes.md`:

- Fotos y frase breve del Inicio.
- Textos de Modalidades y de "Cómo funciona".
- Consejos directivos completos.
- Horarios, tarifas, reservas, correo de contacto, accesibilidad, estacionamiento, seguridad y normas de visita.
- Lista final de formas de adquisición (editable en Museo → Colecciones).
- Tipos de evento: taxonomía editable; los de los datos de ejemplo son de prueba.
- Cantidad de calendarios anteriores (Museo → Textos de portadas; por defecto 4) y los PDF de cada calendario.
- Campos definitivos de los formularios, correo de destino y límites de fotos.
- Enlace o archivo de consulta de las publicaciones (cómo se consultan hoy).
- Dónde vive el PDF de nacionalidad (está pendiente en `redirects.csv`).
- Logo en vector.
- Validar las redirecciones marcadas como `revisar`.

## Lista de verificación por página

- [ ] **Header:** logo → Inicio; 7 secciones con la activa subrayada (`aria-current`); ES | EN; buscador; menú móvil (Esc lo cierra); "Saltar al contenido" con el primer Tab.
- [ ] **Inicio:**
  - hero con imagen prioritaria y 2 botones;
  - "Memoria sefardí" con su texto real;
  - destacado automático (exposición actual → próxima → próximo evento);
  - 5 colecciones con punto de color;
  - 3 espacios;
  - calendario vigente con PDF descargable y enlace a anteriores;
  - banda de legado con 3 botones a Colaboraciones;
  - Visítanos con mapa bajo demanda;
  - marquee de aliados;
  - pie con boletín.
- [ ] **El museo:** anclas (fijas en móvil); Historia + fundador; Misión y visión; Espacios; Consejos directivos (solo los nombres cargados); banda final.
- [ ] **Colecciones:** buscador → `/colecciones/todas/?msc_q=`; 5 categorías con el número de piezas; 4 piezas destacadas; banda de colaborar.
- [ ] **Categoría:** pestañas (Todas + 5), filtros (buscar, forma de adquisición, orden), contador y rango, 12 por página, paginación que conserva los filtros.
- [ ] **Ficha de pieza:** migas Inicio / Colecciones / Categoría / Pieza; visor con miniaturas; ficha técnica (solo filas con valor); Compartir; Volver a la categoría; Más piezas.
- [ ] **Exposiciones:** anclas; sala permanente; exposición actual (o próxima, o mensaje con enlace a pasadas); 4 pasadas; banda.
- [ ] **Exposiciones pasadas:** buscador, filtro por año, 8 por página.
- [ ] **Ficha de exposición:**
  - píldora de estado y fechas, sede y horario;
  - texto y secciones temáticas;
  - créditos y multimedia;
  - relacionados y otras exposiciones;
  - banda del boletín;
  - URL correcta según su estado.
- [ ] **Eventos:** próximos en tarjetas (o "Por ahora no hay eventos programados"); pasados por año con filtros; flecha solo si tiene ficha. Sin botones de calendario, registro ni compartir.
- [ ] **Ficha de evento:** tipo, estado, fecha y hora, lugar, ponentes, descripción, grabación y fotos, relacionados, otros eventos.
- [ ] **Publicaciones:** calendario destacado; catálogo de 8 por página (sin calendarios); banda del boletín.
- [ ] **Ficha de publicación:** año, páginas, formato, tirada, créditos, reseña (se oculta si está vacía), exposición relacionada, otras. Sin compra ni descarga (salvo calendarios).
- [ ] **Calendarios anteriores:** vigente y los últimos N, con "Año hebreo" y "Descargar PDF".
- [ ] **Colaboraciones:** modalidades (anclas `#donacion`, `#adquisicion`, `#comodato`), 3 pasos, formulario con bloques condicionales.
- [ ] **Visítanos:**
  - dirección y mapa;
  - botones Abrir en el mapa, Llamar y WhatsApp;
  - Instagram;
  - horarios y entrada;
  - formulario de visita guiada (`#visita-guiada`);
  - información práctica en acordeón;
  - "Qué encontrarás".
- [ ] **Política de privacidad**, **búsqueda** y **404**.
- [ ] **Todas:** campos vacíos ocultos, JSON-LD presente, ningún texto dorado sobre blanco, foco visible, sin desplazamiento horizontal a 390 px.
