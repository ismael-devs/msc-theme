<?php
/**
 * Plugin Name:       MSC Contenido
 * Plugin URI:        https://www.museosefardidecaracas.org.ve/
 * Description:       Modelo de contenido del Museo Sefardí de Caracas: colecciones, exposiciones, eventos y publicaciones, campos personalizados, URL, redirecciones, formularios y datos estructurados. El contenido no depende del tema.
 * Version:           1.0.0
 * Requires at least: 6.6
 * Requires PHP:      8.1
 * Author:            Konecta web
 * Text Domain:       msc
 * Domain Path:       /languages
 * License:           GPL-2.0-or-later
 *
 * @package msc-contenido
 */

defined( 'ABSPATH' ) || exit;

define( 'MSC_CONTENIDO_VERSION', '1.0.0' );
define( 'MSC_CONTENIDO_FILE', __FILE__ );
define( 'MSC_CONTENIDO_DIR', plugin_dir_path( __FILE__ ) );
define( 'MSC_CONTENIDO_URL', plugin_dir_url( __FILE__ ) );

$msc_archivos = array(
	'helpers',
	'idiomas',
	'cpt',
	'campos',
	'estados',
	'urls',
	'consultas',
	'busqueda',
	'esquema',
	'seo',
	'formularios',
	'redirecciones',
	'admin',
);
foreach ( $msc_archivos as $msc_archivo ) {
	require_once MSC_CONTENIDO_DIR . 'includes/' . $msc_archivo . '.php';
}
unset( $msc_archivos, $msc_archivo );

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once MSC_CONTENIDO_DIR . 'includes/cli.php';
}

/**
 * Carga las traducciones del plugin.
 */
function msc_cargar_traducciones() {
	load_plugin_textdomain( 'msc', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'msc_cargar_traducciones', 1 );

/**
 * Activación: registra tipos y reglas, programa el cron y vacía los enlaces permanentes.
 */
function msc_activar_plugin() {
	msc_registrar_tipos();
	msc_registrar_taxonomias();
	msc_registrar_tipo_envio();
	msc_registrar_reglas_url();
	msc_programar_cron();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'msc_activar_plugin' );

/**
 * Desactivación: elimina el cron y vacía los enlaces permanentes.
 */
function msc_desactivar_plugin() {
	wp_clear_scheduled_hook( 'msc_cron_diario' );
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'msc_desactivar_plugin' );
