<?php
/**
 * Colaboraciones: modalidades, cómo funciona (pasos) y formulario.
 *
 * @package msc-theme
 */

defined( 'ABSPATH' ) || exit;

get_header();
$msc_id = get_queried_object_id();
msc_tema_cabecera( get_the_title(), (string) msc_campo( 'intro', $msc_id ) );

$msc_modalidades = array_filter(
	(array) msc_campo( 'modalidades', $msc_id ),
	static function ( $m ) {
		return ! empty( $m['titulo'] );
	}
);
$msc_pasos       = array_filter(
	(array) msc_campo( 'pasos', $msc_id ),
	static function ( $p ) {
		return ! empty( $p['titulo'] );
	}
);
?>

<?php if ( $msc_modalidades ) : ?>
	<section class="msc-seccion msc-contenedor" aria-labelledby="modalidades-titulo">
		<h2 id="modalidades-titulo"><?php esc_html_e( 'Modalidades', 'msc' ); ?></h2>
		<ul class="msc-rejilla msc-rejilla--c3" role="list">
			<?php foreach ( $msc_modalidades as $msc_m ) : ?>
				<li class="msc-recuadro msc-modalidad"<?php echo ! empty( $msc_m['ancla'] ) ? ' id="' . esc_attr( sanitize_title( $msc_m['ancla'] ) ) . '" tabindex="-1"' : ''; ?>>
					<h3><?php echo esc_html( $msc_m['titulo'] ); ?></h3>
					<?php if ( ! empty( $msc_m['texto'] ) ) : ?>
						<p><?php echo esc_html( $msc_m['texto'] ); ?></p>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</section>
<?php endif; ?>

<?php if ( $msc_pasos ) : ?>
	<section class="msc-seccion msc-contenedor" aria-labelledby="pasos-titulo">
		<h2 id="pasos-titulo"><?php esc_html_e( 'Cómo funciona', 'msc' ); ?></h2>
		<ol class="msc-pasos">
			<?php foreach ( $msc_pasos as $msc_p ) : ?>
				<li>
					<strong><?php echo esc_html( $msc_p['titulo'] ); ?></strong>
					<?php if ( ! empty( $msc_p['texto'] ) ) : ?>
						<span><?php echo esc_html( $msc_p['texto'] ); ?></span>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ol>
	</section>
<?php endif; ?>

<section id="propuesta" class="msc-seccion msc-contenedor" aria-labelledby="propuesta-titulo">
	<h2 id="propuesta-titulo"><?php esc_html_e( 'Cuéntanos tu propuesta', 'msc' ); ?></h2>
	<div class="msc-form-caja">
		<?php
		echo msc_formulario( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			'colaboracion',
			array( 'id_html' => 'form-colaboracion' )
		);
		?>
	</div>
</section>
<?php
get_footer();
