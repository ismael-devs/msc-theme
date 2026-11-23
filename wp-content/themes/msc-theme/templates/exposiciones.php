<?php
/**
 * Exposiciones · portada: sala permanente, exposición actual (o próxima, o mensaje) y 4 pasadas.
 *
 * @package msc-theme
 */

defined( 'ABSPATH' ) || exit;

get_header();
$msc_id         = get_queried_object_id();
$msc_actual     = msc_exposicion_actual();
$msc_pasadas    = msc_exposiciones_pasadas( 4 );
$msc_tiene_sala = ! msc_vacio( (string) msc_campo( 'sala_texto', $msc_id ) ) || msc_campo( 'sala_imagen', $msc_id );

$msc_anclas = '<nav class="msc-anclas msc-anclas--lista" aria-label="' . esc_attr__( 'En esta página', 'msc' ) . '" data-msc-anclas><ul>';
if ( $msc_tiene_sala ) {
	$msc_anclas .= '<li><a href="#sala-permanente">' . esc_html__( 'Sala permanente', 'msc' ) . msc_icono( 'abajo', 16 ) . '</a></li>';
}
$msc_anclas .= '<li><a href="#exposicion-actual">' . esc_html( $msc_actual && 'proxima' === msc_estado_exposicion( $msc_actual ) ? __( 'Próxima exposición', 'msc' ) : __( 'Exposición actual', 'msc' ) ) . msc_icono( 'abajo', 16 ) . '</a></li>';
if ( $msc_pasadas ) {
	$msc_anclas .= '<li><a href="#exposiciones-pasadas">' . esc_html__( 'Exposiciones pasadas', 'msc' ) . msc_icono( 'abajo', 16 ) . '</a></li>';
}
$msc_anclas .= '</ul></nav>';

msc_tema_cabecera( get_the_title(), (string) msc_campo( 'intro', $msc_id ), '', $msc_anclas );
?>

<?php if ( $msc_tiene_sala ) : ?>
	<section id="sala-permanente" class="msc-seccion msc-contenedor" aria-labelledby="sala-titulo">
		<h2 id="sala-titulo"><?php esc_html_e( 'Sala permanente', 'msc' ); ?></h2>
		<?php if ( msc_campo( 'sala_imagen', $msc_id ) ) : ?>
			<div class="msc-banner"><?php echo msc_img( msc_campo( 'sala_imagen', $msc_id, false ), array( 'sizes' => '(max-width: 820px) 100vw, 1312px' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
		<?php endif; ?>
		<?php echo msc_tema_texto( (string) msc_campo( 'sala_texto', $msc_id ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<div class="msc-botones">
			<?php
			$msc_b1 = msc_campo( 'sala_boton1_url', $msc_id ) ? msc_campo( 'sala_boton1_url', $msc_id ) : msc_url_pagina( 'msc-el-museo', 'el-museo' ) . '#espacios';
			$msc_b2 = msc_campo( 'sala_boton2_url', $msc_id ) ? msc_campo( 'sala_boton2_url', $msc_id ) : msc_url_pagina( 'msc-visitanos', 'visitanos' );
			echo msc_tema_boton( (string) msc_campo( 'sala_boton1', $msc_id ), $msc_b1, 'primario' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo msc_tema_boton( (string) msc_campo( 'sala_boton2', $msc_id ), $msc_b2, 'secundario' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			?>
		</div>
	</section>
<?php endif; ?>

<section id="exposicion-actual" class="msc-seccion msc-contenedor" aria-labelledby="actual-titulo">
	<?php if ( $msc_actual ) : ?>
		<?php $msc_estado = msc_estado_exposicion( $msc_actual ); ?>
		<h2 id="actual-titulo"><?php echo esc_html( 'proxima' === $msc_estado ? __( 'Próxima exposición', 'msc' ) : __( 'Exposición actual', 'msc' ) ); ?></h2>
		<article class="msc-expo-actual">
			<div class="msc-expo-actual__media"><?php echo msc_img( msc_imagen_principal( $msc_actual ), array( 'sizes' => '(max-width: 820px) 100vw, 1312px' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
			<div class="msc-expo-actual__cuerpo">
				<?php echo msc_tema_pildora( $msc_estado ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<h3 class="msc-expo-actual__titulo"><?php echo esc_html( get_the_title( $msc_actual ) ); ?></h3>
				<?php
				$msc_sedes = array();
				foreach ( (array) msc_campo( 'sede', $msc_actual ) as $msc_s ) {
					if ( ! empty( $msc_s['nombre'] ) ) {
						$msc_sedes[] = $msc_s['nombre'];
					}
				}
				$msc_meta = array_filter( array( msc_rango_fechas( get_post_meta( $msc_actual, 'fecha_inicio', true ), get_post_meta( $msc_actual, 'fecha_cierre', true ) ), implode( ', ', $msc_sedes ) ) );
				if ( $msc_meta ) :
					?>
					<p class="msc-expo-actual__meta"><?php echo esc_html( implode( ' · ', $msc_meta ) ); ?></p>
				<?php endif; ?>
				<a class="msc-btn msc-btn--primario" href="<?php echo esc_url( get_permalink( $msc_actual ) ); ?>"><?php esc_html_e( 'Ver exposición', 'msc' ); ?><span class="screen-reader-text">: <?php echo esc_html( get_the_title( $msc_actual ) ); ?></span></a>
			</div>
		</article>
	<?php else : ?>
		<h2 id="actual-titulo"><?php esc_html_e( 'Exposición actual', 'msc' ); ?></h2>
		<div class="msc-recuadro">
			<p><?php esc_html_e( 'En este momento no hay una exposición temporal en cartelera. Te invitamos a visitar la sala permanente y a recorrer nuestras exposiciones pasadas.', 'msc' ); ?></p>
			<?php echo msc_tema_boton( __( 'Ver exposiciones pasadas', 'msc' ), msc_url( 'exposiciones/pasadas' ), 'secundario' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	<?php endif; ?>
</section>

<?php if ( $msc_pasadas ) : ?>
	<section id="exposiciones-pasadas" class="msc-seccion msc-contenedor" aria-labelledby="pasadas-titulo">
		<h2 id="pasadas-titulo"><?php esc_html_e( 'Exposiciones pasadas', 'msc' ); ?></h2>
		<?php echo msc_tema_rejilla( $msc_pasadas, 'c4' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<div class="msc-botones"><?php echo msc_tema_boton( __( 'Ver todas las pasadas', 'msc' ), msc_url( 'exposiciones/pasadas' ), 'secundario' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
	</section>
<?php endif; ?>

<?php
msc_tema_banda_colaborar( __( '¿Quieres colaborar con una exposición?', 'msc' ) );
get_footer();
