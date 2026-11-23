<?php
/**
 * Estados automáticos por fecha (zona horaria de WordPress: America/Caracas).
 *
 * Exposición: proxima | en_cartelera | pasada.  Evento: proximo | pasado.
 *
 * @package msc-contenido
 */

defined( 'ABSPATH' ) || exit;

/**
 * Fecha de hoy (Ymd) en la zona horaria del sitio. Filtrable para pruebas.
 *
 * @return string
 */
function msc_hoy() {
	return apply_filters( 'msc_hoy', msc_ahora()->format( 'Ymd' ) );
}

/**
 * Estado de una exposición según sus fechas.
 *
 * @param int $id ID de la exposición.
 * @return string proxima|en_cartelera|pasada
 */
function msc_estado_exposicion( $id ) {
	$hoy    = msc_hoy();
	$inicio = (string) get_post_meta( $id, 'fecha_inicio', true );
	$cierre = (string) get_post_meta( $id, 'fecha_cierre', true );
	if ( $cierre && $hoy > $cierre ) {
		return 'pasada';
	}
	if ( $inicio && $hoy < $inicio ) {
		return 'proxima';
	}
	return 'en_cartelera';
}

/**
 * Estado de un evento. Un evento de hoy sigue siendo "próximo" hasta que termina el día.
 *
 * @param int $id ID del evento.
 * @return string proximo|pasado
 */
function msc_estado_evento( $id ) {
	$fecha = (string) get_post_meta( $id, 'fecha_hora', true );
	if ( ! $fecha ) {
		return 'proximo';
	}
	return substr( str_replace( '-', '', $fecha ), 0, 8 ) < msc_hoy() ? 'pasado' : 'proximo';
}

/**
 * Etiqueta visible de un estado.
 *
 * @param string $estado Estado.
 * @return string
 */
function msc_etiqueta_estado( $estado ) {
	$etiquetas = array(
		'en_cartelera' => __( 'En cartelera', 'msc' ),
		'proxima'      => __( 'Próxima', 'msc' ),
		'pasada'       => __( 'Pasada', 'msc' ),
		'proximo'      => __( 'Próximo', 'msc' ),
		'pasado'       => __( 'Pasado', 'msc' ),
	);
	return $etiquetas[ $estado ] ?? '';
}

/**
 * Clase CSS (color) de un estado: verde, teal o gris.
 *
 * @param string $estado Estado.
 * @return string
 */
function msc_clase_estado( $estado ) {
	$clases = array(
		'en_cartelera' => 'is-vigente',
		'proxima'      => 'is-proximo',
		'proximo'      => 'is-proximo',
		'pasada'       => 'is-pasado',
		'pasado'       => 'is-pasado',
	);
	return $clases[ $estado ] ?? 'is-pasado';
}

/**
 * Indica si un evento tiene ficha propia (solo si tiene descripción).
 *
 * @param int $id ID del evento.
 * @return bool
 */
function msc_evento_tiene_ficha( $id ) {
	return ! msc_vacio( (string) get_post_meta( $id, 'descripcion', true ) );
}

/**
 * Programa el cron diario (00:05 hora de Caracas).
 */
function msc_programar_cron() {
	if ( wp_next_scheduled( 'msc_cron_diario' ) ) {
		return;
	}
	$manana = msc_ahora()->setTime( 0, 5 )->modify( '+1 day' );
	wp_schedule_event( $manana->getTimestamp(), 'daily', 'msc_cron_diario' );
}
add_action( 'init', 'msc_programar_cron', 20 );

/**
 * Cron diario: detecta exposiciones y eventos que cambiaron de estado, guarda el estado,
 * invalida cachés y transients (las URL de exposiciones pasadas cambian solas).
 */
function msc_revisar_estados() {
	$cambios = 0;
	$ids     = get_posts(
		array(
			'post_type'        => array( 'msc_exposicion', 'msc_evento' ),
			'post_status'      => 'publish',
			'posts_per_page'   => -1,
			'fields'           => 'ids',
			'suppress_filters' => true,
			'lang'             => '',
		)
	);
	foreach ( $ids as $id ) {
		$estado = 'msc_exposicion' === get_post_type( $id ) ? msc_estado_exposicion( $id ) : msc_estado_evento( $id );
		if ( get_post_meta( $id, '_msc_estado', true ) !== $estado ) {
			update_post_meta( $id, '_msc_estado', $estado );
			clean_post_cache( $id );
			++$cambios;
			/**
			 * Se dispara cuando una exposición o evento cambia de estado.
			 *
			 * @param int    $id     ID.
			 * @param string $estado Nuevo estado.
			 */
			do_action( 'msc_cambio_estado', $id, $estado );
		}
	}
	if ( $cambios ) {
		msc_limpiar_cache();
	}
	return $cambios;
}
add_action( 'msc_cron_diario', 'msc_revisar_estados' );

/**
 * Guarda el estado al publicar o actualizar (así el cron solo actúa en cambios reales).
 *
 * @param int|string $post_id ID.
 */
function msc_guardar_estado( $post_id ) {
	if ( ! is_numeric( $post_id ) ) {
		return;
	}
	$tipo = get_post_type( (int) $post_id );
	if ( 'msc_exposicion' === $tipo ) {
		update_post_meta( (int) $post_id, '_msc_estado', msc_estado_exposicion( (int) $post_id ) );
	} elseif ( 'msc_evento' === $tipo ) {
		update_post_meta( (int) $post_id, '_msc_estado', msc_estado_evento( (int) $post_id ) );
	}
	if ( in_array( $tipo, array( 'msc_coleccion', 'msc_exposicion', 'msc_evento', 'msc_publicacion', 'page' ), true ) ) {
		msc_limpiar_cache();
	}
}
add_action( 'acf/save_post', 'msc_guardar_estado', 30 );

/**
 * Borra los transients del plugin (consultas cacheadas del Inicio y portadas).
 */
function msc_limpiar_cache() {
	$version = (int) get_option( 'msc_cache_version', 1 );
	update_option( 'msc_cache_version', $version + 1, true );
	if ( function_exists( 'wp_cache_flush_group' ) ) {
		wp_cache_flush_group( 'msc' );
	}
	/**
	 * Permite purgar cachés de página externas (LiteSpeed, WP Rocket…).
	 */
	do_action( 'msc_cache_limpiada' );
}
add_action( 'deleted_post', 'msc_limpiar_cache' );
add_action( 'trashed_post', 'msc_limpiar_cache' );
add_action( 'update_option_options_aliados', 'msc_limpiar_cache' );

/**
 * Clave de transient versionada (para invalidar todo de una vez).
 *
 * @param string $nombre Nombre lógico.
 * @return string
 */
function msc_clave_cache( $nombre ) {
	return 'msc_' . (int) get_option( 'msc_cache_version', 1 ) . '_' . msc_idioma_actual() . '_' . msc_hoy() . '_' . $nombre;
}
