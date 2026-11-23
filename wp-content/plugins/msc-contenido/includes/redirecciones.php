<?php
/**
 * Redirecciones 301 desde el sitio antiguo (.html).
 *
 * Fuente: data/redirects.csv (origen, destino, respaldo, estado, nota) + reglas adicionales
 * editables en Museo → Redirecciones. Si el destino es una ficha que aún no existe
 * (por ejemplo, una exposición no migrada), se usa el respaldo.
 *
 * @package msc-contenido
 */

defined( 'ABSPATH' ) || exit;

/**
 * Normaliza una ruta para compararla.
 *
 * @param string $ruta Ruta.
 * @return string
 */
function msc_normalizar_ruta( $ruta ) {
	$ruta = rawurldecode( (string) wp_parse_url( $ruta, PHP_URL_PATH ) );
	$ruta = '/' . ltrim( $ruta, '/' );
	return strtolower( untrailingslashit( $ruta ) );
}

/**
 * Tabla de redirecciones (cacheada).
 *
 * @return array<string, array{destino: string, respaldo: string}>
 */
function msc_tabla_redirecciones() {
	$version = (int) get_option( 'msc_cache_version', 1 );
	$clave   = 'msc_redirecciones_' . $version . '_' . MSC_CONTENIDO_VERSION;
	$tabla   = get_transient( $clave );
	if ( is_array( $tabla ) ) {
		return $tabla;
	}
	$tabla   = array();
	$archivo = MSC_CONTENIDO_DIR . 'data/redirects.csv';
	if ( is_readable( $archivo ) ) {
		$manejador = fopen( $archivo, 'r' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		$cabecera  = fgetcsv( $manejador, 0, ',', '"', '\\' );
		while ( $manejador && ( $fila = fgetcsv( $manejador, 0, ',', '"', '\\' ) ) !== false ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
			$datos = array_combine( $cabecera, array_pad( $fila, count( $cabecera ), '' ) );
			if ( empty( $datos['destino'] ) || 'pendiente' === $datos['estado'] ) {
				continue;
			}
			$tabla[ msc_normalizar_ruta( $datos['origen'] ) ] = array(
				'destino'  => $datos['destino'],
				'respaldo' => $datos['respaldo'],
			);
		}
		if ( $manejador ) {
			fclose( $manejador ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		}
	}
	// Reglas manuales: "origen destino" por línea (tienen prioridad).
	$manuales = (string) get_option( 'msc_redirecciones_manuales', '' );
	foreach ( preg_split( '/\r\n|\r|\n/', $manuales ) as $linea ) {
		$partes = preg_split( '/\s+/', trim( $linea ) );
		if ( count( $partes ) >= 2 && '#' !== $partes[0][0] ) {
			$tabla[ msc_normalizar_ruta( $partes[0] ) ] = array(
				'destino'  => $partes[1],
				'respaldo' => $partes[2] ?? '',
			);
		}
	}
	set_transient( $clave, $tabla, DAY_IN_SECONDS );
	return $tabla;
}

/**
 * Comprueba si una ruta de destino apunta a una ficha existente.
 *
 * @param string $destino Ruta.
 * @return bool
 */
function msc_destino_existe( $destino ) {
	$ruta = trim( (string) wp_parse_url( $destino, PHP_URL_PATH ), '/' );
	$mapa = array(
		'#^exposiciones/(?:pasadas/)?([^/]+)$#' => 'msc_exposicion',
		'#^eventos/([^/]+)$#'                   => 'msc_evento',
		'#^publicaciones/([^/]+)$#'             => 'msc_publicacion',
		'#^colecciones/[^/]+/([^/]+)$#'         => 'msc_coleccion',
	);
	foreach ( $mapa as $patron => $tipo ) {
		if ( preg_match( $patron, $ruta, $m ) ) {
			if ( in_array( $m[1], array( 'pasadas', 'calendarios', 'todas' ), true ) ) {
				return true;
			}
			$entrada = get_page_by_path( $m[1], OBJECT, $tipo );
			if ( ! $entrada || 'publish' !== $entrada->post_status ) {
				return false;
			}
			return 'msc_evento' !== $tipo || msc_evento_tiene_ficha( $entrada->ID );
		}
	}
	return true;
}

/**
 * Aplica la redirección si la ruta pedida está en la tabla.
 */
function msc_aplicar_redirecciones() {
	if ( ! isset( $_SERVER['REQUEST_URI'] ) ) {
		return;
	}
	$ruta  = msc_normalizar_ruta( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
	$tabla = msc_tabla_redirecciones();
	if ( ! isset( $tabla[ $ruta ] ) ) {
		return;
	}
	$regla   = $tabla[ $ruta ];
	$destino = $regla['destino'];
	if ( $regla['respaldo'] && ! msc_destino_existe( $destino ) ) {
		$destino = $regla['respaldo'];
	}
	if ( ! preg_match( '#^https?://#', $destino ) ) {
		$destino = home_url( $destino );
	}
	wp_safe_redirect( $destino, 301, 'MSC' );
	exit;
}
add_action( 'template_redirect', 'msc_aplicar_redirecciones', 0 );
