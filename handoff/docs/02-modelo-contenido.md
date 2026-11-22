# 02 · Modelo de contenido (CPT + campos)

Prefijo de slugs `msc_`. Campos con ACF registrados por código. Estado: **C** = confirmado en wireframes, **P** = propuesto (se oculta si está vacío), **D** = por definir con el museo (dejar editable).

## CPT `msc_coleccion` — "Colecciones" (piezas)
Taxonomía `categoria_coleccion` (jerárquica no necesaria): Libros, Documentos, Fotografías, Judaica, Arte. Cada término lleva: descripción breve, imagen de portada y color (campo `color_categoria`: Libros #da2027, Documentos #1b5f7a, Fotografías #1a8ca0, Judaica #cc942b, Arte #c01f62).
| Campo | Tipo | Estado |
|---|---|---|
| Galería de imágenes (la primera es la principal) | galería | C |
| Descripción (párrafos ≤80 palabras) | editor | C |
| Dimensiones (alto × ancho × profundidad) | texto | C |
| Forma de adquisición (Donación / Compra / Comodato) | selección (lista editable) | C (lista final D) |
| Destacada | sí/no (alimenta "Piezas destacadas") | C |
| Nº de inventario | texto | P |
| Época o fecha | texto | P |
| Autor o procedencia | texto | P |
| Material o técnica | texto | P |
| Crédito del donante | texto | P |
Listado: filtro por búsqueda (título, autor, tema), forma de adquisición y orden; 12 por página; paginación. Ficha: galería con miniaturas, ficha técnica (solo filas con valor), "Más piezas de [categoría]", invitación a colaborar.

## CPT `msc_exposicion` — "Exposición"
Estado **automático** por fechas: *En cartelera*, *Próxima*, *Pasada*.
| Campo | Tipo | Estado |
|---|---|---|
| Imagen principal / afiche | imagen | C |
| Fecha de inicio y de cierre | fecha ×2 (definen estado y URL) | C |
| Sede | repetidor de texto | C |
| Horario | texto | C |
| Texto de la exposición (≤80 palabras/párrafo) | editor | C |
| Secciones temáticas | repetidor (título, texto breve, imagen, enlace) | C |
| Créditos | repetidor (rol + nombre) | C |
| Video (YouTube/Vimeo) y galería | oEmbed + galería | C |
| Publicación relacionada | relación → `msc_publicacion` | C |
| Piezas relacionadas | relación → `msc_coleccion` | C |
Además: **Sala permanente** vive como campos de la página Exposiciones (foto, texto, botones), no como CPT.
Portada de Exposiciones: sala permanente, exposición actual (si no hay vigente, mostrar la próxima o un mensaje + enlace a pasadas), 4 pasadas recientes. Archivo de pasadas: buscador + filtro por año, cuadrícula, paginación.

## CPT `msc_evento` — "Evento"
Taxonomía `tipo_evento` (lista por definir con el museo; alimenta el filtro). Estado automático *Próximo / Pasado*.
| Campo | Tipo | Estado |
|---|---|---|
| Fecha y hora | fecha-hora | C |
| Lugar | texto | C |
| Ponentes | repetidor de texto | C |
| Descripción (si existe se crea la ficha) | editor | C |
| Imagen | imagen | C |
| Grabación (video) y galería | oEmbed + galería | C |
| Relacionados (exposición/publicación) | relación | C |
Portada: próximos eventos (tarjetas) con estado vacío ("Por ahora no hay eventos programados"), pasados en lista por año con filtros (buscar, tipo, año) y flecha → solo si hay ficha. **Sin** botones "Añadir al calendario", "Registrarme" ni "Compartir".

## CPT `msc_publicacion` — "Publicación"
Taxonomía `tipo_publicacion`: *Libro/catálogo* y *Calendario*. Las publicaciones se **consultan como en la web actual** (sin descarga ni compra) salvo el calendario.
| Campo | Tipo | Estado |
|---|---|---|
| Portada | imagen | C |
| Subtítulo | texto | C |
| Año, páginas, formato | texto/número | C |
| Tirada | número | C (opcional) |
| Créditos | repetidor (rol + nombre) | C |
| Reseña | editor (se oculta si vacía) | C |
| Enlace o archivo de consulta (como en la web actual) | url/archivo | D |
| Exposición relacionada | relación | C |
| **Calendario:** año civil + año hebreo (p. ej. 2026 · 5787) | texto ×2 | C |
| **Calendario:** PDF | archivo (descargable) | C |
El **calendario vigente** (el de año civil más reciente marcado "destacado") alimenta el bloque del Inicio y el de la portada de Publicaciones. `/publicaciones/calendarios/`: vigente + unos 4 anteriores (cantidad por confirmar) con "Calendario [Año civil] / Año hebreo: [5###]" y "Descargar PDF".

## Opciones globales (página de opciones ACF "Museo")
Dirección, teléfonos, WhatsApp, Instagram, correo (por confirmar), horarios y tarifas (por confirmar), coordenadas/enlace del mapa, textos del pie, correo de destino de formularios, **Instituciones aliadas** (repetidor: logo, nombre, enlace). Estos datos se reutilizan en pie, Visítanos, Inicio y JSON-LD.

## Páginas con campos propios
- **Inicio**: hero (foto, frase, 2 botones), texto "Memoria sefardí…", destacado (lee exposición actual o próximo evento), 5 colecciones, 3 espacios, calendario (lee el calendario vigente), banda "Confía al museo la custodia de tu legado" (Donaciones / Adquisiciones / Comodato), Visítanos, marquee de instituciones aliadas.
- **El museo**: Historia (+ semblanza del fundador Morris E. Curiel 1920–2007), Misión y Visión, Espacios (Sala de exposición permanente, Sala de lectura Isaac Chocrón, Biblioteca — sin página propia), Consejos directivos (del Museo y de la AIV; repetidores nombre + cargo).
- **Colaboraciones**: Modalidades (Donación, Adquisición, Comodato), Cómo funciona (3 pasos), formulario.
- **Visítanos**: ubicación + mapa, horarios y entrada, solicitud de visita guiada (formulario), información práctica (accesibilidad, estacionamiento, normas), "Qué encontrarás".
