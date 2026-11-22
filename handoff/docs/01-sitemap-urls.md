# 01 · Mapa del sitio y URLs

## Menú principal (orden fijo)
El museo · Colecciones · Exposiciones · Eventos · Publicaciones · Colaboraciones · Visítanos
Header: logo (→ Inicio), menú, selector **ES | EN**, buscar. Móvil: botones ES|EN, buscar y menú (panel desplegable).
Footer: boletín, contacto, enlaces "Explora" (los 7 + Política de privacidad), franja superior dorada.

## URLs
| Contenido | URL | Tipo |
|---|---|---|
| Inicio | `/` | Página (plantilla Inicio) |
| El museo | `/el-museo/` | Página (una sola página con menú de anclas: Historia, Misión y visión, Espacios, Consejos directivos) |
| Colecciones portada | `/colecciones/` | Página/archivo CPT |
| Categoría | `/colecciones/libros/` (también documentos, fotografias, judaica, arte) | Taxonomía `categoria_coleccion` |
| Ficha de pieza | `/colecciones/libros/[pieza]/` | CPT `msc_coleccion` (la URL incluye la categoría) |
| Exposiciones portada | `/exposiciones/` | Página/archivo |
| Exposición vigente o próxima | `/exposiciones/[nombre]/` | CPT `msc_exposicion` |
| Exposiciones pasadas (archivo) | `/exposiciones/pasadas/` | Archivo filtrado |
| Exposición pasada | `/exposiciones/pasadas/[nombre]/` | Mismo CPT; al pasar la fecha de cierre, la URL canónica cambia y la antigua responde **301** a la nueva |
| Eventos | `/eventos/` | Archivo CPT `msc_evento` |
| Evento con ficha | `/eventos/[nombre]/` | Solo existe si el evento tiene descripción; si no, es una línea de la lista (la URL redirige 301 a `/eventos/`) |
| Publicaciones | `/publicaciones/` | Archivo CPT `msc_publicacion` |
| Ficha de publicación | `/publicaciones/[nombre]/` | CPT |
| Calendarios anteriores | `/publicaciones/calendarios/` | Archivo filtrado por tipo "Calendario" |
| Colaboraciones | `/colaboraciones/` | Página con formulario |
| Visítanos | `/visitanos/` | Página con formulario de visita guiada |
| Política de privacidad | `/politica-de-privacidad/` | Página |
| Inglés (futuro) | `/en/...` | Polylang, misma estructura |

## Migas de pan
Inicio / Colecciones / Libros / [Pieza] · Inicio / Exposiciones / Exposiciones pasadas / [Título] · Inicio / Eventos / [Título] · Inicio / Publicaciones / [Título] · etc. Con `BreadcrumbList` JSON-LD.

## Redirecciones 301 desde el sitio antiguo
El sitio actual usa páginas `.html` (por ejemplo `/exposiciones.html`). **Tarea para ti:** rastrea el sitio actual (`WebFetch`/sitemap/enlaces del menú), genera la tabla antigua→nueva y entrégala como `redirects.csv` + implementación en el plugin (opción de administración o `template_redirect`). Marca los casos dudosos para que yo los revise.

## Notas de implementación
- Colecciones: registrar CPT con `rewrite => ['slug'=>'colecciones/%categoria_coleccion%']` y filtro `post_type_link` para reemplazar el término; la taxonomía con `rewrite => ['slug'=>'colecciones']`. Vaciar permalinks al activar.
- Exposiciones: estado calculado por `fecha_inicio` y `fecha_cierre` (meta); `post_type_link` devuelve `/exposiciones/pasadas/[slug]/` si cerró; `template_redirect` fuerza la canónica con 301.
- Evento: estado próximo/pasado por fecha; la ficha solo se enlaza si el campo `descripcion` no está vacío.
- `rel=canonical`, `hreflang` listo para `/en/`, sitemap XML con las 4 CPT.
