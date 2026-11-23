<?php
/**
 * Bloque "Visítanos" del Inicio: dirección, horarios, teléfono, botones y mapa.
 *
 * @package msc-theme
 */

defined( 'ABSPATH' ) || exit;

$msc_direccion = msc_opcion( 'direccion' );
$msc_horarios  = msc_opcion( 'horarios' );
$msc_telefono  = msc_opcion( 'telefono' );
$msc_mapa      = msc_opcion( 'mapa_enlace' );
$msc_visitanos = msc_url_pagina( 'msc-visitanos', 'visitanos' );
?>
<section class="msc-seccion msc-contenedor msc-visita-resumen" aria-labelledby="visitanos-titulo">
	<div class="msc-visita-resumen__texto">
		<h2 id="visitanos-titulo"><?php esc_html_e( 'Visítanos', 'msc' ); ?></h2>
		<?php if ( $msc_direccion ) : ?>
			<p><?php echo esc_html( $msc_direccion ); ?></p>
		<?php endif; ?>
		<?php if ( $msc_horarios || current_user_can( 'edit_posts' ) ) : ?>
			<p><strong><?php esc_html_e( 'Horarios:', 'msc' ); ?></strong> <?php echo $msc_horarios ? esc_html( $msc_horarios ) : msc_por_confirmar(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
		<?php endif; ?>
		<?php if ( $msc_telefono ) : ?>
			<p><strong><?php esc_html_e( 'Teléfono:', 'msc' ); ?></strong> <a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $msc_telefono ) ); ?>"><?php echo esc_html( $msc_telefono ); ?></a></p>
		<?php endif; ?>
		<div class="msc-botones">
			<?php echo msc_tema_boton( __( 'Solicitar visita guiada', 'msc' ), $msc_visitanos . '#visita-guiada', 'primario' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php echo msc_tema_boton( __( 'Cómo llegar', 'msc' ), $msc_mapa ? $msc_mapa : $msc_visitanos . '#como-llegar', 'secundario', $msc_mapa ? ' rel="noopener"' : '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	</div>
	<?php get_template_part( 'parts/mapa' ); ?>
</section>
