<?php
/**
 * Formulario de búsqueda.
 *
 * @package msc-theme
 */

defined( 'ABSPATH' ) || exit;

$msc_id = 'buscar-' . wp_unique_id();
?>
<form class="msc-buscar" role="search" method="get" action="<?php echo esc_url( function_exists( 'msc_url_base' ) ? msc_url_base() : home_url( '/' ) ); ?>">
	<label for="<?php echo esc_attr( $msc_id ); ?>"><?php esc_html_e( 'Buscar en el sitio', 'msc' ); ?></label>
	<div class="msc-buscar__fila">
		<input type="search" id="<?php echo esc_attr( $msc_id ); ?>" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Título, tema, autor…', 'msc' ); ?>">
		<button type="submit" class="msc-btn msc-btn--primario"><?php esc_html_e( 'Buscar', 'msc' ); ?></button>
	</div>
</form>
