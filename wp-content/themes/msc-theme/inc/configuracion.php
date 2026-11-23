<?php
/**
 * Configuración del tema: soportes, menús, tamaños de imagen y limpieza del <head>.
 *
 * @package msc-theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Soportes del tema.
 */
function msc_tema_configurar() {
	load_theme_textdomain( 'msc', MSC_TEMA_DIR . '/languages' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 104,
			'width'       => 260,
			'flex-width'  => true,
			'flex-height' => true,
		)
	);
	register_nav_menus(
		array(
			'principal' => __( 'Menú principal', 'msc' ),
		)
	);
	// Anchos para srcset (sin recorte: el encuadre se hace con CSS object-fit).
	add_image_size( 'msc-480', 480, 0 );
	add_image_size( 'msc-800', 800, 0 );
	add_image_size( 'msc-1200', 1200, 0 );
	add_image_size( 'msc-1600', 1600, 0 );
	add_image_size( 'msc-2400', 2400, 0 );
}
add_action( 'after_setup_theme', 'msc_tema_configurar' );

/**
 * Ancho máximo de contenido (para incrustados).
 */
function msc_tema_ancho_contenido() {
	$GLOBALS['content_width'] = 1312;
}
add_action( 'after_setup_theme', 'msc_tema_ancho_contenido', 0 );

/**
 * Limpia el <head>: emojis, generador, RSD, enlaces cortos, estilos de bloques no usados.
 */
function msc_tema_limpiar_head() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'feed_links_extra', 3 );
	add_filter( 'emoji_svg_url', '__return_false' );
}
add_action( 'init', 'msc_tema_limpiar_head' );

/**
 * No carga los estilos de bloques ni estilos globales en el front (el tema no los usa,
 * salvo en la política de privacidad, que se formatea con los estilos propios).
 */
function msc_tema_quitar_estilos_bloques() {
	if ( is_admin() ) {
		return;
	}
	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'wp-block-library-theme' );
	wp_dequeue_style( 'global-styles' );
	wp_dequeue_style( 'classic-theme-styles' );
	wp_dequeue_style( 'core-block-supports' );
}
add_action( 'wp_enqueue_scripts', 'msc_tema_quitar_estilos_bloques', 100 );
add_filter( 'should_load_separate_core_block_assets', '__return_true' );
add_filter( 'wp_img_tag_add_auto_sizes', '__return_false' );

/**
 * Plantilla del aviso "[por confirmar]" disponible también si el plugin no está activo.
 */
if ( ! function_exists( 'msc_por_confirmar' ) ) {
	/**
	 * Marcador visible solo para editores.
	 *
	 * @param string $texto Texto.
	 * @return string
	 */
	function msc_por_confirmar( $texto = '' ) {
		return current_user_can( 'edit_posts' ) ? '<span class="msc-por-confirmar">' . esc_html( $texto ? $texto : '[por confirmar]' ) . '</span>' : '';
	}
}

/**
 * Aviso en el admin si falta el plugin de contenido.
 */
function msc_tema_aviso_plugin() {
	if ( function_exists( 'msc_campo' ) || ! current_user_can( 'activate_plugins' ) ) {
		return;
	}
	echo '<div class="notice notice-error"><p>' . esc_html__( 'El tema MSC necesita el plugin "MSC Contenido". Actívalo en Plugins.', 'msc' ) . '</p></div>';
}
add_action( 'admin_notices', 'msc_tema_aviso_plugin' );
