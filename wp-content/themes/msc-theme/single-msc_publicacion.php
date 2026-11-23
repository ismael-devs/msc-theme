<?php
/**
 * Ficha de publicación. Se consulta como en la web actual: sin botones de compra ni descarga
 * (salvo los calendarios, que sí se descargan en PDF).
 *
 * @package msc-theme
 */

defined( 'ABSPATH' ) || exit;

get_header();
$msc_id  = get_queried_object_id();
$msc_cal = 'calendario' === get_post_meta( $msc_id, 'tipo', true );
$msc_sub = (string) msc_campo( 'subtitulo', $msc_id );

msc_tema_cabecera( $msc_cal ? msc_titulo_calendario( $msc_id ) : get_the_title( $msc_id ), $msc_sub );
?>
<div class="msc-contenedor msc-ficha">
	<?php if ( msc_campo( 'portada', $msc_id ) ) : ?>
		<div class="msc-banner msc-banner--portada">
			<?php
			echo msc_img( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				msc_campo( 'portada', $msc_id, false ),
				array(
					'sizes'     => '(max-width: 820px) 100vw, 640px',
					'tamano'    => 'msc-1600',
					'prioridad' => true,
				)
			);
			?>
		</div>
	<?php endif; ?>

	<?php
	$msc_tirada = (string) msc_campo( 'tirada', $msc_id );
	echo msc_tema_datos( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$msc_cal ? array(
			__( 'Año civil', 'msc' )  => esc_html( (string) msc_campo( 'anio_civil', $msc_id ) ),
			__( 'Año hebreo', 'msc' ) => esc_html( (string) msc_campo( 'anio_hebreo', $msc_id ) ),
		) : array(
			__( 'Año', 'msc' )     => esc_html( (string) msc_campo( 'anio', $msc_id ) ),
			__( 'Páginas', 'msc' ) => esc_html( (string) msc_campo( 'paginas', $msc_id ) ),
			__( 'Formato', 'msc' ) => esc_html( (string) msc_campo( 'formato', $msc_id ) ),
			/* translators: %s: número de ejemplares. */
			__( 'Tirada', 'msc' )  => $msc_tirada ? esc_html( sprintf( __( '%s ejemplares', 'msc' ), number_format_i18n( (int) $msc_tirada ) ) ) : '',
		),
		'msc-datos--ficha'
	);
	?>

	<?php $msc_creditos = msc_tema_creditos( msc_campo( 'creditos', $msc_id ) ); ?>
	<?php if ( $msc_creditos ) : ?>
		<section class="msc-seccion" aria-labelledby="creditos-titulo">
			<h2 id="creditos-titulo"><?php esc_html_e( 'Créditos', 'msc' ); ?></h2>
			<?php echo $msc_creditos; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</section>
	<?php endif; ?>

	<?php echo msc_tema_texto( (string) msc_campo( 'resena', $msc_id ), 'msc-texto--legible msc-seccion' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

	<?php
	// Consulta (como en la web actual) o descarga del calendario.
	$msc_pdf      = $msc_cal ? msc_id_imagen( msc_campo( 'pdf', $msc_id, false ) ) : 0;
	$msc_consulta = (string) msc_campo( 'consulta_url', $msc_id );
	$msc_archivo  = msc_id_imagen( msc_campo( 'consulta_archivo', $msc_id, false ) );
	if ( ! $msc_consulta && $msc_archivo ) {
		$msc_consulta = (string) wp_get_attachment_url( $msc_archivo );
	}
	?>
	<?php if ( $msc_pdf || $msc_consulta ) : ?>
		<div class="msc-botones">
			<?php if ( $msc_pdf ) : ?>
				<a class="msc-btn msc-btn--primario" href="<?php echo esc_url( (string) wp_get_attachment_url( $msc_pdf ) ); ?>" download><?php echo msc_icono( 'descargar', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php esc_html_e( 'Descargar PDF', 'msc' ); ?></a>
			<?php elseif ( $msc_consulta ) : ?>
				<a class="msc-btn msc-btn--secundario" href="<?php echo esc_url( $msc_consulta ); ?>" rel="noopener"><?php esc_html_e( 'Consultar la publicación', 'msc' ); ?></a>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php
	$msc_expo = array_values(
		array_filter(
			array_map( 'intval', (array) msc_campo( 'exposicion_relacionada', $msc_id, false ) ),
			static function ( $r ) {
				return $r && 'publish' === get_post_status( $r );
			}
		)
	);
	if ( $msc_expo ) :
		?>
		<section class="msc-seccion" aria-labelledby="expo-titulo">
			<h2 id="expo-titulo"><?php echo esc_html( _n( 'Exposición relacionada', 'Exposiciones relacionadas', count( $msc_expo ), 'msc' ) ); ?></h2>
			<?php echo msc_tema_rejilla( $msc_expo, 'c4' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</section>
	<?php endif; ?>

	<?php $msc_otras = $msc_cal ? msc_calendarios_anteriores( 4 ) : msc_otras_publicaciones( $msc_id, 4 ); ?>
	<?php $msc_otras = array_values( array_diff( $msc_otras, array( $msc_id ) ) ); ?>
	<?php if ( $msc_otras ) : ?>
		<section class="msc-seccion" aria-labelledby="otras-titulo">
			<h2 id="otras-titulo"><?php echo esc_html( $msc_cal ? __( 'Otros calendarios', 'msc' ) : __( 'Otras publicaciones', 'msc' ) ); ?></h2>
			<?php echo msc_tema_rejilla( $msc_otras, 'c4', array( 'ratio' => 'portada' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</section>
	<?php endif; ?>
</div>
<?php
get_footer();
