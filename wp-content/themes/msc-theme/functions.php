<?php
/**
 * Funciones del tema MSC.
 *
 * @package msc-theme
 */

defined( 'ABSPATH' ) || exit;

define( 'MSC_TEMA_VERSION', '1.0.0' );
define( 'MSC_TEMA_DIR', get_template_directory() );
define( 'MSC_TEMA_URL', get_template_directory_uri() );

require_once MSC_TEMA_DIR . '/inc/configuracion.php';
require_once MSC_TEMA_DIR . '/inc/recursos.php';
require_once MSC_TEMA_DIR . '/inc/plantillas.php';
require_once MSC_TEMA_DIR . '/inc/iconos.php';
require_once MSC_TEMA_DIR . '/inc/componentes.php';
