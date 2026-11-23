<?php
/**
 * El museo: una sola página con menú de anclas (Historia, Misión y visión, Espacios, Consejos directivos).
 *
 * @package msc-theme
 */

defined( 'ABSPATH' ) || exit;

get_header();
$msc_id = get_queried_object_id();
$msc_c  = static function ( $n ) use ( $msc_id ) {
	return msc_campo( $n, $msc_id );
};

$msc_secciones = array();
if ( ! msc_vacio( $msc_c( 'historia_texto' ) ) || $msc_c( 'fundador_texto' ) ) {
	$msc_secciones['historia'] = __( 'Historia', 'msc' );
}
if ( $msc_c( 'mision' ) || $msc_c( 'vision' ) ) {
	$msc_secciones['mision'] = __( 'Misión y visión', 'msc' );
}
if ( $msc_c( 'espacios' ) ) {
	$msc_secciones['espacios'] = __( 'Espacios', 'msc' );
}
if ( $msc_c( 'consejo_museo' ) || $msc_c( 'consejo_aiv' ) ) {
	$msc_secciones['consejos'] = __( 'Consejos directivos', 'msc' );
}

$msc_anclas = '';
if ( count( $msc_secciones ) > 1 ) {
	$msc_anclas = '<nav class="msc-anclas" aria-label="' . esc_attr__( 'En esta página', 'msc' ) . '" data-msc-anclas><ul>';
	foreach ( $msc_secciones as $msc_ancla => $msc_texto ) {
		$msc_anclas .= '<li><a href="#' . esc_attr( $msc_ancla ) . '">' . esc_html( $msc_texto ) . '</a></li>';
	}
	$msc_anclas .= '</ul></nav>';
}
msc_tema_cabecera( get_the_title(), (string) $msc_c( 'intro' ), '', $msc_anclas );
?>

<?php if ( isset( $msc_secciones['historia'] ) ) : ?>
	<section id="historia" class="msc-seccion msc-contenedor" aria-labelledby="historia-titulo">
		<h2 id="historia-titulo"><?php esc_html_e( 'Historia', 'msc' ); ?></h2>
		<?php if ( $msc_c( 'historia_imagen' ) ) : ?>
			<div class="msc-banner"><?php echo msc_img( msc_campo( 'historia_imagen', $msc_id, false ), array( 'sizes' => '(max-width: 820px) 100vw, 1312px' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
		<?php endif; ?>
		<?php echo msc_tema_texto( $msc_c( 'historia_texto' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php if ( $msc_c( 'fundador_texto' ) ) : ?>
			<aside class="msc-recuadro msc-fundador" aria-labelledby="fundador-titulo">
				<?php if ( $msc_c( 'fundador_imagen' ) ) : ?>
					<div class="msc-banner"><?php echo msc_img( msc_campo( 'fundador_imagen', $msc_id, false ), array( 'sizes' => '(max-width: 820px) 100vw, 1280px' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
				<?php endif; ?>
				<p class="msc-sobretitulo"><?php esc_html_e( 'Nuestro fundador', 'msc' ); ?></p>
				<h3 id="fundador-titulo"><?php echo esc_html( $msc_c( 'fundador_nombre' ) ); ?></h3>
				<p><?php echo esc_html( $msc_c( 'fundador_texto' ) ); ?></p>
			</aside>
		<?php endif; ?>
	</section>
<?php endif; ?>

<?php if ( isset( $msc_secciones['mision'] ) ) : ?>
	<section id="mision" class="msc-seccion msc-contenedor" aria-labelledby="mision-titulo">
		<h2 id="mision-titulo"><?php esc_html_e( 'Misión y visión', 'msc' ); ?></h2>
		<div class="msc-rejilla msc-rejilla--c2">
			<?php
			foreach ( array(
				'mision' => __( 'Misión', 'msc' ),
				'vision' => __( 'Visión', 'msc' ),
			) as $msc_campo_mv => $msc_titulo_mv ) :
				?>
									<?php if ( $msc_c( $msc_campo_mv ) ) : ?>
					<div class="msc-recuadro">
						<h3><?php echo esc_html( $msc_titulo_mv ); ?></h3>
						<p><?php echo esc_html( $msc_c( $msc_campo_mv ) ); ?></p>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
	</section>
<?php endif; ?>

<?php if ( isset( $msc_secciones['espacios'] ) ) : ?>
	<section id="espacios" class="msc-seccion msc-contenedor" aria-labelledby="espacios-titulo">
		<h2 id="espacios-titulo"><?php esc_html_e( 'Espacios', 'msc' ); ?></h2>
		<?php if ( $msc_c( 'espacios_intro' ) ) : ?>
			<p class="msc-lead"><?php echo esc_html( $msc_c( 'espacios_intro' ) ); ?></p>
		<?php endif; ?>
		<?php foreach ( (array) $msc_c( 'espacios' ) as $msc_e ) : ?>
			<div class="msc-espacio-largo">
				<?php if ( ! empty( $msc_e['imagen'] ) ) : ?>
					<div class="msc-banner msc-banner--bajo"><?php echo msc_img( $msc_e['imagen'], array( 'sizes' => '(max-width: 820px) 100vw, 1312px' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
				<?php endif; ?>
				<h3><?php echo esc_html( $msc_e['titulo'] ); ?></h3>
				<?php if ( ! empty( $msc_e['texto'] ) ) : ?>
					<p><?php echo esc_html( $msc_e['texto'] ); ?></p>
				<?php endif; ?>
				<?php if ( ! empty( $msc_e['enlace_url'] ) && ! empty( $msc_e['enlace_texto'] ) ) : ?>
					<p><a class="msc-enlace-flecha" href="<?php echo esc_url( $msc_e['enlace_url'] ); ?>"><?php echo esc_html( $msc_e['enlace_texto'] ); ?> <?php echo msc_icono( 'flecha', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a></p>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</section>
<?php endif; ?>

<?php if ( isset( $msc_secciones['consejos'] ) ) : ?>
	<section id="consejos" class="msc-seccion msc-contenedor" aria-labelledby="consejos-titulo">
		<h2 id="consejos-titulo"><?php esc_html_e( 'Consejos directivos', 'msc' ); ?></h2>
		<div class="msc-rejilla msc-rejilla--c2">
			<?php foreach ( array( 'consejo_museo', 'consejo_aiv' ) as $msc_consejo ) : ?>
				<?php
				$msc_miembros = $msc_c( $msc_consejo );
				if ( ! is_array( $msc_miembros ) || ! $msc_miembros ) {
					continue;
				}
				?>
				<div class="msc-recuadro">
					<h3><?php echo esc_html( $msc_c( $msc_consejo . '_titulo' ) ); ?></h3>
					<ul class="msc-consejo" role="list">
						<?php foreach ( $msc_miembros as $msc_m ) : ?>
							<?php
							if ( empty( $msc_m['nombre'] ) ) {
								continue;
							}
							?>
							<li><strong><?php echo esc_html( $msc_m['nombre'] ); ?></strong><?php echo ! empty( $msc_m['cargo'] ) ? '<span>' . esc_html( $msc_m['cargo'] ) . '</span>' : ''; ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endforeach; ?>
		</div>
	</section>
<?php endif; ?>

<?php
$msc_visitanos = msc_url_pagina( 'msc-visitanos', 'visitanos' );
msc_tema_banda(
	__( 'Conoce el museo en persona', 'msc' ),
	'',
	msc_tema_boton( __( 'Planifica tu visita', 'msc' ), $msc_visitanos, 'primario msc-btn--sobre-oscuro' ) . msc_tema_boton( __( 'Solicitar visita guiada', 'msc' ), $msc_visitanos . '#visita-guiada', 'dorado' ),
	'conoce-titulo'
);
get_footer();
