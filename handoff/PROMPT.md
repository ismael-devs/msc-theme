# PROMPT PARA CLAUDE CODE — Tema WordPress del Museo Sefardí de Caracas

Copia y pega todo este archivo como primer mensaje en Claude Code, con la carpeta `handoff/` dentro del repositorio (o en la raíz del proyecto).

---

## Rol y objetivo
Eres un desarrollador senior de WordPress (rendimiento, SEO técnico y accesibilidad). Vas a construir **el tema de WordPress y el plugin de contenido** para el rediseño de https://www.museosefardidecaracas.org.ve/ (Museo Sefardí de Caracas "Morris E. Curiel"). Hoy el sitio es HTML estático de 2016 sin CMS; el equipo del museo no es técnico, así que **todo el contenido debe poder gestionarse desde el administrador de WordPress**.

Lee primero, en este orden: `CLAUDE.md`, `docs/01-sitemap-urls.md`, `docs/02-modelo-contenido.md`, `docs/03-diseno-tokens.md`, `docs/04-paginas.md`, `docs/05-funcionalidades.md`, `docs/06-pendientes.md`. La carpeta `reference/html-paleta/` contiene la maqueta navegable con la paleta aplicada (HTML estático): es la **referencia visual** de cada página (escritorio y móvil). Ábrela (`index.html`) antes de escribir plantillas.

## Entregables
1. **Plugin `msc-contenido`** (o mu-plugin): CPT, taxonomías, campos personalizados, reglas de URL/redirecciones, esquema JSON-LD. El contenido NO debe depender del tema.
2. **Tema `msc-theme`** (tema clásico PHP, sin page builder obligatorio, ligero): plantillas, parciales, CSS con tokens, JS mínimo.
3. **Datos de ejemplo** (script WP-CLI o importador) para ver todas las plantillas con contenido de prueba.
4. **README de instalación** y lista de verificación de cada página.

## Decisiones ya tomadas (respétalas; si ves un problema serio, dímelo antes de cambiarlas)
- Páginas del menú: Inicio, El museo, Colecciones, Exposiciones, Eventos, Publicaciones, Colaboraciones, Visítanos. Sin sección de Noticias.
- 4 CPT: **Colecciones**, **Exposición**, **Evento**, **Publicación**. Campos con ACF (los registras por código con `acf_add_local_field_group`, para que viajen con el plugin). Si ACF no está disponible, avísame y propón alternativa.
- Páginas editoriales (El museo, Colaboraciones, Visítanos, Política de privacidad) son páginas normales con plantilla propia; los textos se editan en el editor/ACF.
- URLs anidadas para SEO (ver `docs/01`). Exposiciones: la URL cambia sola de vigente a pasada con 301.
- Idioma: lanzamiento solo en español, pero la estructura debe estar lista para traducir (Polylang, ES por defecto, EN después en `/en/`). El selector **ES | EN** se muestra desde el lanzamiento.
- Textos largos: párrafos de máximo 80 palabras. Campos vacíos se ocultan (no dejar títulos huérfanos).
- Accesibilidad AA, objetivos táctiles ≥44px, `prefers-reduced-motion`, foco visible, enlace "saltar al contenido".
- Rendimiento (WPO): CSS crítico en línea, un solo archivo CSS y uno JS, imágenes con `srcset`, `loading="lazy"`, `fetchpriority` en el hero, fuentes con `font-display:swap` y autohospedadas (EB Garamond 500 y Jost 400/600/700, WOFF2), sin jQuery en el front, sin librerías de iconos (SVG en línea).
- Paleta y tipografías: `docs/03-diseno-tokens.md` y `assets/css/tokens.css` (cópialo tal cual). El dorado `#cc942b` **nunca** se usa como color de texto sobre blanco (contraste 2.7:1).

## Forma de trabajar
1. Antes de escribir código, haz un **plan por fases** y muéstramelo: (a) plugin con CPT/CF/URLs, (b) tema base (header, footer, tokens, parciales), (c) plantillas por página, (d) funcionalidades (marquee, calendario, formularios, esquema), (e) rendimiento/SEO/accesibilidad, (f) datos de ejemplo y QA.
2. Avanza fase por fase; al terminar cada una ejecuta pruebas (WP-CLI, `php -l`, PHPCS con WordPress-Coding-Standards si está disponible) y revisa visualmente con Playwright contra `reference/html-paleta/` (capturas de escritorio 1440px y móvil 390px).
3. Si algo depende de una decisión del museo (`docs/06-pendientes.md`), deja un campo editable o un marcador `[por confirmar]` visible solo en el admin; no inventes datos.
4. Usa los textos reales de `docs/04-paginas.md`; el resto son marcadores `[...]`.
5. Todo el código y los comentarios de funciones en español neutro; nombres de funciones/CPT/slugs con prefijo `msc_`.
6. No instales plugins de terceros salvo los listados en `docs/05`. Dime qué versiones de WordPress/PHP asumes (por defecto WP 6.6+, PHP 8.1+).

## Criterios de aceptación
- Cada plantilla coincide con la maqueta (estructura, jerarquía, estados) en escritorio y móvil.
- Un editor sin conocimientos técnicos puede: publicar una pieza, una exposición, un evento, una publicación y un calendario nuevo; cambiar el calendario destacado del Inicio; editar los logos aliados; sin tocar código.
- Estados automáticos por fecha (exposición vigente/pasada, evento próximo/pasado) funcionan sin intervención.
- Las URLs, 301 y breadcrumbs cumplen `docs/01`.
- JSON-LD válido para cada tipo (Museum, ExhibitionEvent, Event, Book, VisualArtwork, BreadcrumbList).
- Lighthouse móvil: Rendimiento ≥90, Accesibilidad ≥95, SEO ≥95 en Inicio y en una ficha.

Empieza leyendo los documentos y respóndeme con: (1) un resumen de lo que entendiste en 10 líneas, (2) dudas bloqueantes (máximo 5; si no hay, di "ninguna"), (3) el plan por fases.
