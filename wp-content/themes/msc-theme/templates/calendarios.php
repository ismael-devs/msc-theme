<?php
/**
 * Calendarios anteriores (/publicaciones/calendarios/): vigente + los últimos N.
 *
 * @package msc-theme
 */

defined( 'ABSPATH' ) || exit;

get_header();
msc_tema_cabecera( __( 'Calendarios anteriores', 'msc' ), (string) msc_opcion( 'intro_calendarios' ) );
$msc_anteriores = msc_calendarios_anteriores();
?>
<div class="msc-seccion msc-contenedor"><?php msc_tema_calendario( 'calendarios' ); ?></div>

<?php if ( $msc_anteriores ) : ?>
	<section class="msc-seccion msc-contenedor" aria-labelledby="anteriores-titulo">
		<h2 id="anteriores-titulo" class="screen-reader-text"><?php esc_html_e( 'Ediciones anteriores', 'msc' ); ?></h2>
		<ul class="msc-calendarios" role="list">
			<?php foreach ( $msc_anteriores as $msc_cal ) : ?>
				<?php
				$msc_pdf   = msc_id_imagen( msc_campo( 'pdf', $msc_cal, false ) );
				$msc_civil = (string) msc_campo( 'anio_civil', $msc_cal );
				$msc_heb   = (string) msc_campo( 'anio_hebreo', $msc_cal );
				/* translators: %s: año civil. */
				$msc_titulo = sprintf( __( 'Calendario %s', 'msc' ), $msc_civil );
				?>
				<li class="msc-cal-item">
					<div class="msc-cal-item__portada">
					<?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo msc_img(
						msc_campo( 'portada', $msc_cal, false ),
						array(
							'sizes'  => '84px',
							'tamano' => 'msc-480',
						)
					);
					?>
					</div>
					<div class="msc-cal-item__texto">
						<h3><?php echo esc_html( $msc_titulo ); ?></h3>
						<?php if ( $msc_heb ) : ?>
							<p><?php /* translators: %s: año hebreo. */ echo esc_html( sprintf( __( 'Año hebreo: %s', 'msc' ), $msc_heb ) ); ?></p>
						<?php endif; ?>
						<?php if ( $msc_pdf ) : ?>
							<a class="msc-btn msc-btn--secundario msc-btn--bloque" href="<?php echo esc_url( (string) wp_get_attachment_url( $msc_pdf ) ); ?>" download><?php esc_html_e( 'Descargar PDF', 'msc' ); ?><span class="screen-reader-text">: <?php echo esc_html( $msc_titulo ); ?></span></a>
						<?php endif; ?>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
	</section>
<?php endif; ?>
<?php
get_footer();
