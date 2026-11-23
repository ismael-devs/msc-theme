<?php
/**
 * Iconos SVG en línea (sin librerías de iconos).
 *
 * @package msc-theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Devuelve un icono SVG decorativo (aria-hidden).
 *
 * @param string $nombre Nombre del icono.
 * @param int    $tam    Tamaño en px.
 * @return string
 */
function msc_icono( $nombre, $tam = 18 ) {
	$trazos = array(
		'buscar'    => '<circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/>',
		'menu'      => '<path d="M4 7h16M4 12h16M4 17h16"/>',
		'cerrar'    => '<path d="M6 6l12 12M18 6L6 18"/>',
		'flecha'    => '<path d="M5 12h14M13 6l6 6-6 6"/>',
		'atras'     => '<path d="M19 12H5M11 6l-6 6 6 6"/>',
		'abajo'     => '<path d="M12 5v14M6 13l6 6 6-6"/>',
		'izquierda' => '<path d="M15 6l-6 6 6 6"/>',
		'derecha'   => '<path d="M9 6l6 6-6 6"/>',
		'compartir' => '<circle cx="18" cy="5" r="2.5"/><circle cx="6" cy="12" r="2.5"/><circle cx="18" cy="19" r="2.5"/><path d="M8.2 10.9l7.6-4.4M8.2 13.1l7.6 4.4"/>',
		'descargar' => '<path d="M12 4v11M7 10l5 5 5-5M5 20h14"/>',
		'mapa'      => '<path d="M12 21s-7-6.1-7-11a7 7 0 0 1 14 0c0 4.9-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/>',
		'telefono'  => '<path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z"/>',
	);
	if ( ! isset( $trazos[ $nombre ] ) ) {
		return '';
	}
	return sprintf(
		'<svg class="msc-icono msc-icono--%1$s" width="%2$d" height="%2$d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%3$s</svg>',
		esc_attr( $nombre ),
		(int) $tam,
		$trazos[ $nombre ]
	);
}
