<?php
/**
 * Proveedor de sitemap para las vistas que no son entradas (archivos de eventos, publicaciones...).
 *
 * @package msc-contenido
 */

defined( 'ABSPATH' ) || exit;

/**
 * Sitemap de vistas del museo.
 */
class MSC_Sitemap_Vistas extends WP_Sitemaps_Provider {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->name        = 'msc';
		$this->object_type = 'msc';
	}

	/**
	 * URL de la lista.
	 *
	 * @param int    $page_num       Página.
	 * @param string $object_subtype Subtipo.
	 * @return array
	 */
	public function get_url_list( $page_num, $object_subtype = '' ) {
		$rutas = array( 'eventos', 'publicaciones', 'publicaciones/calendarios', 'exposiciones/pasadas', 'colecciones/todas' );
		return array_map(
			static function ( $ruta ) {
				return array( 'loc' => msc_url( $ruta, msc_idioma_defecto() ) );
			},
			$rutas
		);
	}

	/**
	 * Número de páginas.
	 *
	 * @param string $object_subtype Subtipo.
	 * @return int
	 */
	public function get_max_num_pages( $object_subtype = '' ) {
		return 1;
	}
}
