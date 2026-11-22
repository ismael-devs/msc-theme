# CLAUDE.md — Museo Sefardí de Caracas (tema WordPress)

## Proyecto
Rediseño de museosefardidecaracas.org.ve. Cliente: Museo Sefardí de Caracas "Morris E. Curiel" (contacto: Anny Cudisevich). Desarrollo: Konecta web (Ismael). Idioma de trabajo: español.

## Stack y convenciones
- WordPress 6.6+, PHP 8.1+, tema clásico `msc-theme` + plugin `msc-contenido`. Prefijo `msc_`.
- ACF registrado por código (no desde el admin). CPT y taxonomías en el plugin.
- Sin jQuery en el front. Un CSS, un JS. Tokens en `assets/css/tokens.css`.
- Escapar siempre (`esc_html`, `esc_url`, `esc_attr`, `wp_kses_post`), sanear entradas, nonces en formularios.
- Cadenas traducibles con `__()`/`_e()` y text-domain `msc`.
- Accesibilidad AA obligatoria: landmarks, `aria-current="page"`, foco visible, botones ≥48px, enlaces ≥44px.
- Campos vacíos → no renderizar su bloque.
- Párrafos de texto largo ≤80 palabras (el editor los divide; no mostrar muros de texto).

## Datos del museo (reales)
- Dirección: Paseo Colón con Av. Principal de Maripérez, complejo Gran Sinagoga Tiferet Israel / AIV, Caracas.
- Teléfonos: +58 212 5781489 · +58 424 2787933 (WhatsApp). Instagram: @museosefardiccs.
- Correo, horarios, tarifas, accesibilidad, estacionamiento: **por confirmar**.

## No hacer
- No usar dorado como color de texto sobre blanco.
- No crear sección de Noticias.
- No poner botones de descarga/compra en Publicaciones (se consultan como en la web actual), salvo el calendario anual que sí se descarga en PDF.
- No inventar contenido ni cifras.
