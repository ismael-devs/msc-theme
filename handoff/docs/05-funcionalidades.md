# 05 · Funcionalidades transversales

## Plugins permitidos
ACF PRO (o ACF gratuito si no se necesita repetidor; confirmar licencia), Polylang (idioma), un plugin de formularios con antispam (Fluent Forms o el formulario de Elementor PRO si se usa Elementor; por defecto **formulario propio en el tema** con nonce, honeypot y `wp_mail`, y opción de integrarlo), un plugin SEO (Rank Math o Yoast; el JSON-LD de las CPT lo genera el plugin `msc-contenido` y debe convivir sin duplicar). Nada más sin consultarme.

## Marquee de instituciones aliadas (Inicio)
Pista CSS con animación infinita (`translateX`, 40 s), grupo duplicado con `aria-hidden` y `tabindex=-1`, **botón visible "Pausar / Reanudar"**, pausa al hover/foco, y con `prefers-reduced-motion` se convierte en una cuadrícula estática. Datos desde la opción global "Instituciones aliadas". Ejemplos: Asociación Israelita de Venezuela, Hebraica, CAIV, Nuevo Mundo Israelita, Revista Maguén-Escudo.

## Estados por fecha
Funciones `msc_estado_exposicion($id)` y `msc_estado_evento($id)` (en cartelera/próxima/pasada) usadas por listados, píldoras, JSON-LD y URLs. Considerar la zona horaria de WordPress (America/Caracas). Cron diario que invalida caché y revalida permalinks de exposiciones que cambiaron de estado.

## Calendario anual
Bloque en Inicio y Publicaciones que lee el calendario "destacado" más reciente; PDF descargable con `download`; título "Calendario [año civil] · [año hebreo]". Página de anteriores con los últimos N (ajustable en opciones).

## Formularios (Colaboraciones y Visita guiada)
Validación en servidor y cliente, mensajes accesibles (`aria-live`), campos de ejemplo editables desde un array/ACF para que sea fácil añadir los definitivos, bloques condicionales según el tipo (JS mínimo, con fallback sin JS), correo de destino configurable, copia de recepción, política de privacidad, límite de adjuntos (por definir: fotos de la pieza). Guardar envíos como registros en el admin (opcional pero recomendado).

## Búsqueda
Buscador del header y buscadores de colecciones/exposiciones/eventos con `pre_get_posts` (título, extracto, campos de autor/tema). Resultados agrupados por tipo.

## SEO técnico y esquema
- Metadatos y Open Graph por plantilla; `rel=canonical`; sitemap con las CPT; `robots` correcto en filtros/paginación.
- JSON-LD: `Museum` (Inicio/Visítanos con dirección, teléfonos, horarios), `ExhibitionEvent`, `Event`, `Book`/`CreativeWork` (publicaciones), `VisualArtwork`/`CreativeWork` (piezas), `BreadcrumbList`.
- Imágenes con `alt` obligatorio en el editor de piezas.
- Redirecciones 301 desde el sitio antiguo (ver `docs/01`).

## Multiidioma (preparación)
Todos los textos del tema en text-domain `msc`; Polylang con ES por defecto; selector ES|EN en header; `hreflang` listo; campos ACF traducibles. El inglés se completa en una fase posterior.

## Rendimiento y accesibilidad
CSS crítico en línea, un CSS y un JS diferidos, WOFF2 autohospedado, `srcset`/`sizes`, lazy-load salvo el hero, `fetchpriority="high"` en el LCP, sin render bloqueante, sin CLS (reservar tamaños), caché de consultas en Inicio con transients. Foco visible, saltar al contenido, contraste AA, navegación por teclado completa, `prefers-reduced-motion`.

## Ideas de interés (fuera de alcance por ahora)
Galería virtual 360° (depende de presupuesto), mapa con hotspots y QR por exposición. Deja ganchos (campos opcionales) pero no los implementes.
