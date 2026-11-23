<?php
/**
 * Recursos del front: CSS crítico en línea, un solo CSS diferido, un solo JS diferido y fuentes locales.
 *
 * @package msc-theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Versión de un archivo según su fecha de modificación (para invalidar caché).
 *
 * @param string $ruta Ruta relativa al tema.
 * @return string
 */
function msc_tema_version( $ruta ) {
	$archivo = MSC_TEMA_DIR . '/' . $ruta;
	return file_exists( $archivo ) ? (string) filemtime( $archivo ) : MSC_TEMA_VERSION;
}

/**
 * Declaraciones @font-face (autohospedadas, font-display: swap).
 *
 * @return string
 */
function msc_tema_fuentes_css() {
	$base    = MSC_TEMA_URL . '/assets/fonts/';
	$fuentes = array(
		array( 'EB Garamond', 500, 'eb-garamond-500.woff2' ),
		array( 'Jost', 400, 'jost-400.woff2' ),
		array( 'Jost', 600, 'jost-600.woff2' ),
		array( 'Jost', 700, 'jost-700.woff2' ),
	);
	$css     = '';
	foreach ( $fuentes as $f ) {
		$css .= sprintf(
			'@font-face{font-family:"%1$s";font-style:normal;font-weight:%2$d;font-display:swap;src:url(%3$s) format("woff2");unicode-range:U+0000-00FF,U+0131,U+0152-0153,U+02BB-02BC,U+02C6,U+02DA,U+02DC,U+0304,U+0308,U+0329,U+2000-206F,U+20AC,U+2122,U+2190-2199,U+2212,U+2215,U+FEFF,U+FFFD}',
			$f[0],
			$f[1],
			esc_url( $base . $f[2] )
		);
	}
	return $css;
}

/**
 * Imprime en el <head>: precarga de fuentes, CSS crítico y la hoja principal sin bloquear el render.
 */
function msc_tema_head_recursos() {
	$fuentes = MSC_TEMA_URL . '/assets/fonts/';
	printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( $fuentes . 'eb-garamond-500.woff2' ) );
	printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( $fuentes . 'jost-400.woff2' ) );

	$critico = get_transient( 'msc_css_critico_' . msc_tema_version( 'assets/css/critical.css' ) . msc_tema_version( 'assets/css/tokens.css' ) );
	if ( false === $critico ) {
		$critico  = (string) file_get_contents( MSC_TEMA_DIR . '/assets/css/tokens.css' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$critico .= (string) file_get_contents( MSC_TEMA_DIR . '/assets/css/critical.css' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$critico  = preg_replace( '#/\*.*?\*/#s', '', $critico );
		$critico  = preg_replace( '/\s+/', ' ', $critico );
		$critico  = str_replace( array( ' {', '{ ', ' }', '; ', ': ', ', ' ), array( '{', '{', '}', ';', ':', ',' ), $critico );
		set_transient( 'msc_css_critico_' . msc_tema_version( 'assets/css/critical.css' ) . msc_tema_version( 'assets/css/tokens.css' ), $critico, WEEK_IN_SECONDS );
	}
	echo '<style id="msc-critico">' . msc_tema_fuentes_css() . $critico . '</style>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	$css = MSC_TEMA_URL . '/assets/css/msc.css?ver=' . msc_tema_version( 'assets/css/msc.css' );
	printf( '<link rel="preload" href="%1$s" as="style" onload="this.onload=null;this.rel=\'stylesheet\'">' . "\n", esc_url( $css ) ); // CSS principal asíncrono (no bloquea el render).
	printf( '<noscript><link rel="stylesheet" href="%s"></noscript>' . "\n", esc_url( $css ) ); // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
}
add_action( 'wp_head', 'msc_tema_head_recursos', 2 );

/**
 * JS único, diferido, sin jQuery.
 */
function msc_tema_scripts() {
	wp_enqueue_script(
		'msc',
		MSC_TEMA_URL . '/assets/js/msc.js',
		array(),
		msc_tema_version( 'assets/js/msc.js' ),
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);
	wp_localize_script(
		'msc',
		'mscTextos',
		array(
			'copiado'   => __( 'Enlace copiado', 'msc' ),
			'requerido' => __( 'Completa este campo.', 'msc' ),
			'correo'    => __( 'Escribe un correo electrónico válido.', 'msc' ),
			'revisa'    => __( 'Revisa los campos marcados.', 'msc' ),
			'abrir'     => __( 'Abrir menú', 'msc' ),
			'cerrar'    => __( 'Cerrar menú', 'msc' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'msc_tema_scripts' );

/**
 * Elimina jQuery del front si algún plugin lo encola sin necesitarlo realmente
 * (se puede desactivar con el filtro msc_quitar_jquery).
 */
function msc_tema_sin_jquery() {
	if ( ! is_admin() && ! is_customize_preview() && apply_filters( 'msc_quitar_jquery', true ) && ! is_user_logged_in() ) {
		wp_deregister_script( 'jquery-migrate' );
	}
}
add_action( 'wp_enqueue_scripts', 'msc_tema_sin_jquery', 100 );
