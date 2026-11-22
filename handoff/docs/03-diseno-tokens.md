# 03 · Diseño: paleta, tipografía y reglas

## Paleta (medida desde el logo)
| Token | Valor | Uso |
|---|---|---|
| `--msc-navy` | #24335c | Color principal: títulos, botones, enlaces, fondo ES activo |
| `--msc-navy-900` | #141c33 | Bandas oscuras (invitaciones, boletín) |
| `--msc-navy-100` | #e6e9f2 | Fondos suaves, pie de página |
| `--msc-gold` | #cc942b | Acento: botones sobre fondo oscuro, filetes. **Nunca texto sobre blanco** |
| `--msc-gold-text` | #8a5f0f | Dorado apto para texto |
| `--msc-gold-100` | #f6efe0 | Fondo del calendario destacado (con borde dorado) |
| `--msc-paper` | #f6f7fa | Fondo de página |
| `--msc-ink` | #1b2236 | Texto |
| `--msc-ink-muted` | #4a5368 | Texto secundario |
| `--msc-line` | #c9cedc | Bordes |
| `--msc-placeholder` | #dfe4f0 / borde #8f99b8 | Marcadores de imagen |
| Estrellas del logo | rojo #da2027 · verde #1d9c48 · petróleo #1b5f7a · cian #20b9d4 · magenta #c01f62 · teal #1a8ca0 | Acentos; textos: verde #15703a, teal #12687a, rojo #c4161c |
| Foco | #20b9d4 (outline 3px) | |
Colores de colección: Libros rojo, Documentos petróleo, Fotografías teal, Judaica dorado, Arte magenta (punto de 10px junto al nombre).
Estados: *En cartelera* verde (fondo #e3f2e9, texto #15703a); *Próximo* teal (fondo #dff0f3, texto #12687a); *Pasada/Pasado* gris (fondo #e6e9f2, texto #4a5368).

## Tipografía
Títulos **EB Garamond 500** (color navy); texto **Jost** 400/600/700. Autohospedar en WOFF2 con `font-display:swap`.
Escala orientativa: h1 52–64px escritorio / 32–36px móvil; h2 40/28; h3 20–22; texto 16–18; etiquetas 11–13 versalitas con `letter-spacing:.06em`.

## Componentes (ver la maqueta)
Botón primario (navy, texto blanco, alto 48px), secundario (borde 2px navy, fondo blanco), botón dorado (solo sobre navy-900, texto #141c33), tarjetas blancas con borde `--msc-line` y miniatura, píldora de estado, chips de pestañas (desplazables en móvil), paginación (44px), acordeones `<details>` en móvil para filtros/información práctica, migas de pan, banda oscura con h2 blanco.
Layout: contenedor 1440px de maqueta con márgenes laterales de 64px en escritorio y 16px en móvil; breakpoint principal 820px.
Logo: `assets/img/logo.png` (pendiente de recibir el archivo vectorial; si no existe, mostrar el nombre en EB Garamond).
