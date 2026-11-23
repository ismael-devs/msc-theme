<?php
/**
 * Enlace entre las plantillas declaradas por el plugin y los archivos del tema.
 *
 * @package msc-theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Carga templates/{plantilla}.php para las páginas con plantilla del museo
 * y la plantilla de calendarios para /publicaciones/calendarios/.
 *
 * @param string $plantilla Ruta elegida por WordPress.
 * @return string
 */
function msc_tema_elegir_plantilla( $plantilla ) {
	if ( is_page() ) {
		$clave = get_page_template_slug( get_queried_object_id() );
		if ( $clave && 0 === strpos( $clave, 'msc-' ) ) {
			$archivo = MSC_TEMA_DIR . '/templates/' . substr( $clave, 4 ) . '.php';
			if ( file_exists( $archivo ) ) {
				return $archivo;
			}
		}
	}
	if ( is_post_type_archive( 'msc_publicacion' ) && 'calendarios' === get_query_var( 'msc_archivo' ) ) {
		return MSC_TEMA_DIR . '/templates/calendarios.php';
	}
	return $plantilla;
}
add_filter( 'template_include', 'msc_tema_elegir_plantilla', 20 );

/**
 * Clases del <body>.
 *
 * @param string[] $clases Clases.
 * @return string[]
 */
function msc_tema_clases_body( $clases ) {
	$clases[] = 'msc';
	return $clases;
}
add_filter( 'body_class', 'msc_tema_clases_body' );

/**
 * Sección activa del menú (para aria-current y subrayado) según la vista.
 *
 * @return string Ruta base de la sección (el-museo, colecciones...).
 */
function msc_tema_seccion_actual() {
	if ( is_singular( 'msc_coleccion' ) || is_tax( 'categoria_coleccion' ) || is_post_type_archive( 'msc_coleccion' ) || is_page_template( 'msc-colecciones' ) ) {
		return 'colecciones';
	}
	if ( is_singular( 'msc_exposicion' ) || is_post_type_archive( 'msc_exposicion' ) || is_page_template( 'msc-exposiciones' ) ) {
		return 'exposiciones';
	}
	if ( is_singular( 'msc_evento' ) || is_post_type_archive( 'msc_evento' ) ) {
		return 'eventos';
	}
	if ( is_singular( 'msc_publicacion' ) || is_post_type_archive( 'msc_publicacion' ) ) {
		return 'publicaciones';
	}
	if ( is_page_template( 'msc-el-museo' ) ) {
		return 'el-museo';
	}
	if ( is_page_template( 'msc-colaboraciones' ) ) {
		return 'colaboraciones';
	}
	if ( is_page_template( 'msc-visitanos' ) ) {
		return 'visitanos';
	}
	return '';
}

/**
 * Enlaces del menú principal: los del menú de WordPress si existe; si no, los 7 fijos.
 *
 * @return array<int, array{titulo: string, url: string, actual: bool}>
 */
function msc_tema_enlaces_menu() {
	$seccion = msc_tema_seccion_actual();
	$enlaces = array();
	$ubic    = get_nav_menu_locations();
	if ( ! empty( $ubic['principal'] ) ) {
		$items = wp_get_nav_menu_items( $ubic['principal'] );
		foreach ( (array) $items as $item ) {
			if ( $item->menu_item_parent ) {
				continue;
			}
			$ruta      = trim( (string) wp_parse_url( $item->url, PHP_URL_PATH ), '/' );
			$ruta      = preg_replace( '#^(' . implode( '|', function_exists( 'msc_idiomas_secundarios' ) ? msc_idiomas_secundarios() : array( 'en' ) ) . ')/#', '', $ruta );
			$enlaces[] = array(
				'titulo' => $item->title,
				'url'    => $item->url,
				'actual' => '' !== $seccion && 0 === strpos( $ruta, $seccion ),
			);
		}
	}
	if ( ! $enlaces ) {
		$fijos = array(
			'el-museo'       => __( 'El museo', 'msc' ),
			'colecciones'    => __( 'Colecciones', 'msc' ),
			'exposiciones'   => __( 'Exposiciones', 'msc' ),
			'eventos'        => __( 'Eventos', 'msc' ),
			'publicaciones'  => __( 'Publicaciones', 'msc' ),
			'colaboraciones' => __( 'Colaboraciones', 'msc' ),
			'visitanos'      => __( 'Visítanos', 'msc' ),
		);
		foreach ( $fijos as $ruta => $titulo ) {
			$enlaces[] = array(
				'titulo' => $titulo,
				'url'    => function_exists( 'msc_url' ) ? msc_url( $ruta ) : home_url( '/' . $ruta . '/' ),
				'actual' => $ruta === $seccion,
			);
		}
	}
	return $enlaces;
}
