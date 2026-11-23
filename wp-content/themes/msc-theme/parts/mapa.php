<?php
/**
 * Mapa: se carga solo cuando el visitante lo pide (privacidad y velocidad).
 * Sin URL de mapa incrustado, solo el editor ve el marcador.
 *
 * @package msc-theme
 */

defined( 'ABSPATH' ) || exit;

$msc_embed = msc_opcion( 'mapa_embed' );
if ( ! $msc_embed ) {
	$msc_marcador = msc_por_confirmar( __( '[Mapa por confirmar: añade la URL del mapa en Museo → Contacto]', 'msc' ) );
	if ( $msc_marcador ) {
		echo '<div class="msc-mapa msc-mapa--vacio">' . $msc_marcador . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	return;
}
?>
<div class="msc-mapa" data-msc-mapa="<?php echo esc_url( $msc_embed ); ?>">
	<button type="button" class="msc-btn msc-btn--secundario msc-mapa__boton"><?php echo msc_icono( 'mapa' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php esc_html_e( 'Mostrar el mapa interactivo', 'msc' ); ?></button>
	<noscript><a href="<?php echo esc_url( $msc_embed ); ?>"><?php esc_html_e( 'Abrir el mapa', 'msc' ); ?></a></noscript>
</div>
