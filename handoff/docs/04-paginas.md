# 04 · Especificación por página (bloques y comportamiento)

Referencia visual: `reference/html-paleta/*.html` (escritorio ≥821px, móvil ≤820px; `index.html` e `index-movil.html` tienen diseño propio).

## Inicio (`index.html`)
1 Hero (foto, h1 "Museo Sefardí de Caracas Morris E. Curiel", frase, botones **Explora las colecciones** y **Planifica tu visita**) · 2 "Memoria sefardí, de Sefarad a Venezuela" (texto real: *El Museo Sefardí de Caracas "Morris E. Curiel" fue fundado en 1999 para preservar la memoria de la comunidad judía sefardí, desde Sefarad hasta su arraigo en Venezuela. Está en el complejo de Maripérez, junto a la Gran Sinagoga Tiferet Israel, en Caracas.*) + "Conoce nuestra historia →" · 3 Destacado: exposición actual o próximo evento (automático) · 4 Explora las colecciones (5 categorías con punto de color) · 5 Tres espacios (Sala de exposición permanente: "Seis módulos sobre la huella y el arraigo de la comunidad sefardí en Venezuela."; Sala de lectura Isaac Chocrón: "Un homenaje al dramaturgo venezolano, para la lectura y la tertulia literaria."; Biblioteca: "Fondo documental especializado en judaísmo, historia sefardí y cultura universal.") · 6 Calendario anual (portada, título "Calendario [año civil] · [año hebreo]", PDF, enlace a anteriores) · 7 Banda "Confía al museo la custodia de tu legado" con botones Donaciones/Adquisiciones/Comodato (→ Colaboraciones) · 8 Visítanos (dirección, horarios, teléfono, mapa, botones) · 9 **Marquee de instituciones aliadas** · 10 Pie.

## El museo
Una sola página con menú de anclas (`#historia`, `#mision`, `#espacios`, `#consejos`; en móvil las pestañas se desplazan y el menú queda fijo). Textos reales:
- Historia: *El Museo Sefardí de Caracas "Morris E. Curiel" fue fundado en 1999 como una asociación civil sin fines de lucro adscrita a la Asociación Israelita de Venezuela, institución matriz de la comunidad judía sefardí fundada en 1930.* / *Ubicado en el complejo de Maripérez en Caracas, junto a la Gran Sinagoga Tiferet Israel, el museo se sitúa dentro del eje cultural capitalino que reúne a importantes instituciones artísticas y culturales del país.* Fundador: Morris E. Curiel (1920–2007), descendiente de una familia sefardí originaria de Curazao, empresario, banquero y filántropo.
- Misión: *Investigar, recolectar, conservar, interpretar y exhibir el patrimonio material e inmaterial de la comunidad judía sefardí.* Visión: *Consolidarse como una institución museística de referencia nacional e internacional en la preservación y divulgación de la memoria sefardí.*
- Espacios: Sala de exposición permanente (6 módulos), Sala de lectura Isaac Chocrón (homenaje al dramaturgo), Biblioteca.
- Consejo directivo del Museo: Dr. Abraham Levy Benshimol (Presidente), Priscila Abecasis de Bigio (Vicepresidente), Anny Cudisevich Echerman (Directora Ejecutiva); el resto por completar. Consejo de la AIV: Dr. Isaac Benjamin Fhima (Presidente), Samuel Chocrón Nahon (Primer vicepresidente); el resto por completar.
- Cierre: banda "Conoce el museo en persona" (Planifica tu visita / Solicitar visita guiada).

## Colecciones
Portada: cabecera + buscador, 5 colecciones, piezas destacadas (4), invitación a colaborar. Categoría: cabecera, pestañas (Todas + 5), filtros (buscar, forma de adquisición, ordenar), cuadrícula de 12, paginación. Ficha: galería + miniaturas, categoría, título, descripción, ficha técnica, "Compartir" y "← Volver a [categoría]", más piezas.

## Exposiciones
Portada (anclas Sala permanente / Exposición actual / Exposiciones pasadas), archivo de pasadas, ficha (cabecera con estado/fechas/sede/horario, texto, secciones temáticas 3×2, créditos, multimedia, relacionados, otras exposiciones, boletín). Pasadas de ejemplo (títulos reales): Janucá (2024), Calendario 5784 (2023), Sefardíes de Curazao a Coro (2022), Los Pardo (2019).

## Eventos
Portada y ficha según `docs/02`. Ficha: tipo, estado, fecha/hora, lugar, ponentes, descripción, grabación y galería, relacionados, otros eventos.

## Publicaciones
Portada: cabecera, calendario destacado, catálogo 4×2 (8 por página en escritorio), boletín. Ficha: portada, título, subtítulo, año/páginas/formato/tirada, créditos, reseña, exposición relacionada, otras publicaciones. Calendarios anteriores.

## Colaboraciones
Cabecera, **Modalidades** (Donación, Adquisición, Comodato — textos del Word del museo, a completar), **Cómo funciona** (3 pasos), **Formulario genérico**: tipo de colaboración (radios), 4 campos comunes por definir, mensaje, 3 bloques condicionales por tipo (por definir), aceptación de privacidad, enviar. Debe quedar fácil de ampliar cuando el museo defina los campos.

## Visítanos
Ubicación + mapa (Paseo Colón con Av. Principal de Maripérez, complejo Gran Sinagoga Tiferet Israel / AIV; botones Abrir en el mapa, Llamar, WhatsApp, @museosefardiccs), horarios y entrada (por confirmar), solicitud de visita guiada (formulario genérico), información práctica (por confirmar: accesibilidad, estacionamiento, normas), "Qué encontrarás" (4 tarjetas).
