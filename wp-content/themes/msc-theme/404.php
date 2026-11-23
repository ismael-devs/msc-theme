<?php
/**
 * Página no encontrada.
 *
 * @package msc-theme
 */

defined( 'ABSPATH' ) || exit;

get_header();
msc_tema_cabecera( __( 'Página no encontrada', 'msc' ), __( 'La página que buscas no existe o cambió de dirección. Prueba con el buscador o con los enlaces del menú.', 'msc' ) );
?>
<div class="msc-contenedor msc-seccion">
	<?php get_search_form(); ?>
	<div class="msc-botones">
		<?php echo msc_tema_boton( __( 'Ir al Inicio', 'msc' ), function_exists( 'msc_url_base' ) ? msc_url_base() : home_url( '/' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</div>
</div>
<?php
get_footer();
