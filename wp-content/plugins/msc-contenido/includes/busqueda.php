<?php
/**
 * Búsqueda: incluye campos personalizados (autor, descripción, ficha técnica, ponentes...).
 *
 * @package msc-contenido
 */

defined( 'ABSPATH' ) || exit;

/**
 * Claves de campos que participan en la búsqueda.
 *
 * @return string[]
 */
function msc_campos_buscables() {
	return apply_filters(
		'msc_campos_buscables',
		array(
			'descripcion',
			'texto',
			'resena',
			'subtitulo',
			'autor_procedencia',
			'material_tecnica',
			'epoca',
			'numero_inventario',
			'credito_donante',
			'lugar',
			'anio_civil',
			'anio_hebreo',
		)
	);
}

/**
 * Amplía la cláusula de búsqueda con un EXISTS sobre los campos personalizados.
 *
 * @param string   $busqueda SQL de búsqueda.
 * @param WP_Query $q        Consulta.
 * @return string
 */
function msc_buscar_en_campos( $busqueda, $q ) {
	if ( '' === $busqueda || ! $q->get( 'msc_buscar_meta' ) ) {
		return $busqueda;
	}
	global $wpdb;
	$texto = trim( (string) $q->get( 's' ) );
	if ( '' === $texto ) {
		return $busqueda;
	}
	$like     = '%' . $wpdb->esc_like( $texto ) . '%';
	$claves   = msc_campos_buscables();
	$huecos   = implode( ',', array_fill( 0, count( $claves ), '%s' ) );
	$interior = preg_replace( '/^\s*AND\s*/i', '', $busqueda );
	// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$exists = $wpdb->prepare( "EXISTS (SELECT 1 FROM {$wpdb->postmeta} msc_pm WHERE msc_pm.post_id = {$wpdb->posts}.ID AND ( msc_pm.meta_key IN ($huecos) OR msc_pm.meta_key LIKE %s ) AND msc_pm.meta_value LIKE %s)", array_merge( $claves, array( 'ponentes\_%\_nombre', $like ) ) );
	return ' AND ( ' . $interior . ' OR ' . $exists . ' ) ';
}
add_filter( 'posts_search', 'msc_buscar_en_campos', 10, 2 );

/**
 * Agrupa resultados de búsqueda por tipo (para la plantilla de búsqueda).
 *
 * @param WP_Post[] $entradas Entradas.
 * @return array<string, array{titulo: string, entradas: WP_Post[]}>
 */
function msc_agrupar_resultados( $entradas ) {
	$grupos = array(
		'msc_coleccion'   => array(
			'titulo'   => __( 'Piezas de las colecciones', 'msc' ),
			'entradas' => array(),
		),
		'msc_exposicion'  => array(
			'titulo'   => __( 'Exposiciones', 'msc' ),
			'entradas' => array(),
		),
		'msc_evento'      => array(
			'titulo'   => __( 'Eventos', 'msc' ),
			'entradas' => array(),
		),
		'msc_publicacion' => array(
			'titulo'   => __( 'Publicaciones', 'msc' ),
			'entradas' => array(),
		),
		'page'            => array(
			'titulo'   => __( 'Páginas', 'msc' ),
			'entradas' => array(),
		),
	);
	foreach ( $entradas as $entrada ) {
		if ( isset( $grupos[ $entrada->post_type ] ) ) {
			$grupos[ $entrada->post_type ]['entradas'][] = $entrada;
		}
	}
	return array_filter(
		$grupos,
		static function ( $g ) {
			return ! empty( $g['entradas'] );
		}
	);
}
