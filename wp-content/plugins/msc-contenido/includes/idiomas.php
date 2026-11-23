<?php
/**
 * Preparación multiidioma (Polylang). Español por defecto; inglés en /en/ en una fase posterior.
 *
 * @package msc-contenido
 */

defined( 'ABSPATH' ) || exit;

/**
 * Idiomas secundarios previstos (además del español). Ampliable con el filtro msc_idiomas_secundarios.
 *
 * @return string[]
 */
function msc_idiomas_secundarios() {
	return apply_filters( 'msc_idiomas_secundarios', array( 'en' ) );
}

/**
 * Código del idioma actual ("es" si Polylang no está activo).
 *
 * @return string
 */
function msc_idioma_actual() {
	if ( function_exists( 'pll_current_language' ) ) {
		$idioma = pll_current_language( 'slug' );
		if ( $idioma ) {
			return $idioma;
		}
	}
	return 'es';
}

/**
 * Idioma por defecto del sitio.
 *
 * @return string
 */
function msc_idioma_defecto() {
	if ( function_exists( 'pll_default_language' ) ) {
		$idioma = pll_default_language( 'slug' );
		if ( $idioma ) {
			return $idioma;
		}
	}
	return 'es';
}

/**
 * Idioma de una entrada.
 *
 * @param int $post_id ID.
 * @return string
 */
function msc_idioma_entrada( $post_id ) {
	if ( function_exists( 'pll_get_post_language' ) ) {
		$idioma = pll_get_post_language( $post_id, 'slug' );
		if ( $idioma ) {
			return $idioma;
		}
	}
	return msc_idioma_defecto();
}

/**
 * URL base (con barra final) para un idioma: "/" para español, "/en/" para inglés.
 *
 * @param string|null $idioma Código del idioma (por defecto, el actual).
 * @return string
 */
function msc_url_base( $idioma = null ) {
	$idioma = $idioma ? $idioma : msc_idioma_actual();
	$base   = home_url( '/' );
	if ( msc_idioma_defecto() !== $idioma && in_array( $idioma, msc_idiomas_activos(), true ) ) {
		$base = trailingslashit( $base . $idioma );
	}
	return $base;
}

/**
 * Idiomas dados de alta en Polylang.
 *
 * @return string[]
 */
function msc_idiomas_activos() {
	if ( function_exists( 'pll_languages_list' ) ) {
		$lista = pll_languages_list( array( 'fields' => 'slug' ) );
		if ( $lista ) {
			return $lista;
		}
	}
	return array( 'es' );
}

/**
 * Construye una URL del sitio en el idioma indicado: msc_url( 'colecciones/libros' ).
 *
 * @param string      $ruta   Ruta relativa sin barra inicial.
 * @param string|null $idioma Idioma.
 * @return string
 */
function msc_url( $ruta = '', $idioma = null ) {
	$ruta = trim( $ruta, '/' );
	return msc_url_base( $idioma ) . ( '' === $ruta ? '' : user_trailingslashit( $ruta ) );
}

/**
 * Declara los tipos de contenido y taxonomías como traducibles en Polylang.
 *
 * @param array $tipos     Tipos.
 * @param bool  $es_ajuste Si se llama desde la pantalla de ajustes.
 * @return array
 */
function msc_pll_tipos( $tipos, $es_ajuste ) {
	$nuestros = array( 'msc_coleccion', 'msc_exposicion', 'msc_evento', 'msc_publicacion' );
	if ( $es_ajuste ) {
		foreach ( $nuestros as $t ) {
			unset( $tipos[ $t ] );
		}
		return $tipos;
	}
	return array_merge( $tipos, array_combine( $nuestros, $nuestros ) );
}
add_filter( 'pll_get_post_types', 'msc_pll_tipos', 10, 2 );

/**
 * Declara las taxonomías como traducibles en Polylang.
 *
 * @param array $taxonomias Taxonomías.
 * @param bool  $es_ajuste  Si se llama desde la pantalla de ajustes.
 * @return array
 */
function msc_pll_taxonomias( $taxonomias, $es_ajuste ) {
	$nuestras = array( 'categoria_coleccion', 'tipo_evento', 'tipo_publicacion' );
	if ( $es_ajuste ) {
		foreach ( $nuestras as $t ) {
			unset( $taxonomias[ $t ] );
		}
		return $taxonomias;
	}
	return array_merge( $taxonomias, array_combine( $nuestras, $nuestras ) );
}
add_filter( 'pll_get_taxonomies', 'msc_pll_taxonomias', 10, 2 );

/**
 * Polylang añade el prefijo de idioma a los enlaces de entradas. Como nuestros enlaces ya
 * se construyen con msc_url() (que incluye /en/), evitamos el doble prefijo.
 *
 * @param string  $url  Enlace.
 * @param WP_Post $post Entrada.
 * @return string
 */
function msc_pll_no_duplicar_prefijo( $url, $post ) {
	foreach ( msc_idiomas_secundarios() as $idioma ) {
		$doble = '/' . $idioma . '/' . $idioma . '/';
		if ( false !== strpos( $url, $doble ) ) {
			$url = str_replace( $doble, '/' . $idioma . '/', $url );
		}
	}
	return $url;
}
add_filter( 'post_type_link', 'msc_pll_no_duplicar_prefijo', 99, 2 );

/**
 * Enlaces al resto de idiomas de la página actual, para el selector ES | EN y hreflang.
 * Si no existe traducción, apunta a la portada de ese idioma (o null si el idioma aún no existe).
 *
 * @return array<string, array{url: ?string, actual: bool, traducido: bool}>
 */
function msc_enlaces_idioma() {
	$salida  = array();
	$actual  = msc_idioma_actual();
	$codigos = array_unique( array_merge( array( 'es' ), msc_idiomas_secundarios() ) );
	$activos = msc_idiomas_activos();
	foreach ( $codigos as $codigo ) {
		$url       = null;
		$traducido = false;
		if ( in_array( $codigo, $activos, true ) ) {
			if ( function_exists( 'pll_the_languages' ) ) {
				$lista = pll_the_languages(
					array(
						'raw'                    => 1,
						'hide_if_no_translation' => 0,
						'hide_if_empty'          => 0,
					)
				);
				if ( is_array( $lista ) && isset( $lista[ $codigo ] ) ) {
					$url       = $lista[ $codigo ]['url'];
					$traducido = empty( $lista[ $codigo ]['no_translation'] );
				}
			}
			// Vistas sin traducción propia (archivos de eventos, publicaciones, pasadas...): misma ruta en el otro idioma.
			if ( is_post_type_archive() || is_search() ) {
				$url = msc_ruta_en_idioma( $codigo );
			}
			if ( ! $url ) {
				$url = msc_url_base( $codigo );
			}
		}
		$salida[ $codigo ] = array(
			'url'       => $url,
			'actual'    => $codigo === $actual,
			'traducido' => $traducido || $codigo === $actual,
		);
	}
	return $salida;
}

/**
 * Ruta actual (sin prefijo de idioma) trasladada a otro idioma, con su consulta.
 *
 * @param string $codigo Idioma de destino.
 * @return string
 */
function msc_ruta_en_idioma( $codigo ) {
	$uri  = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/';
	$ruta = trim( (string) wp_parse_url( $uri, PHP_URL_PATH ), '/' );
	$base = trim( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ), '/' );
	if ( '' !== $base && 0 === strpos( $ruta, $base ) ) {
		$ruta = trim( substr( $ruta, strlen( $base ) ), '/' );
	}
	$ruta = preg_replace( '#^(' . implode( '|', array_map( 'preg_quote', msc_idiomas_secundarios() ) ) . ')(/|$)#', '', $ruta );
	$url  = msc_url( $ruta, $codigo );
	$qs   = (string) wp_parse_url( $uri, PHP_URL_QUERY );
	return $qs ? $url . '?' . $qs : $url;
}
