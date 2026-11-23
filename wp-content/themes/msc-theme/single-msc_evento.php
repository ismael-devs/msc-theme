<?php
/**
 * Ficha de evento (solo existe si el evento tiene descripción).
 * Sin botones "Añadir al calendario", "Registrarme" ni "Compartir".
 *
 * @package msc-theme
 */

defined( 'ABSPATH' ) || exit;

get_header();
$msc_id    = get_queried_object_id();
$msc_tipos = get_the_terms( $msc_id, 'tipo_evento' );
$msc_tipo  = $msc_tipos && ! is_wp_error( $msc_tipos ) ? $msc_tipos[0]->name : '';

msc_tema_cabecera( get_the_title( $msc_id ), '', msc_tema_etiqueta( $msc_tipo ) . msc_tema_pildora( msc_estado_evento( $msc_id ) ) );

$msc_ponentes = array();
foreach ( (array) msc_campo( 'ponentes', $msc_id ) as $msc_p ) {
	if ( ! empty( $msc_p['nombre'] ) ) {
		$msc_ponentes[] = esc_html( $msc_p['nombre'] );
	}
}
?>
<div class="msc-contenedor msc-ficha">
	<?php if ( msc_campo( 'imagen', $msc_id ) ) : ?>
		<div class="msc-banner msc-banner--ficha">
			<?php
			echo msc_img( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				msc_campo( 'imagen', $msc_id, false ),
				array(
					'sizes'     => '(max-width: 820px) 100vw, 1312px',
					'tamano'    => 'msc-2400',
					'prioridad' => true,
				)
			);
			?>
		</div>
	<?php endif; ?>

	<?php
	echo msc_tema_datos( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		array(
			__( 'Fecha y hora', 'msc' ) => esc_html( msc_formatear_fecha( get_post_meta( $msc_id, 'fecha_hora', true ), 'j \d\e F \d\e Y · H:i' ) ),
			__( 'Lugar', 'msc' )        => esc_html( (string) msc_campo( 'lugar', $msc_id ) ),
			__( 'Ponentes', 'msc' )     => implode( '<br>', $msc_ponentes ),
		),
		'msc-datos--compacto'
	);
	?>

	<?php echo msc_tema_texto( (string) msc_campo( 'descripcion', $msc_id ), 'msc-texto--legible' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

	<?php
	$msc_video   = msc_tema_video( (string) msc_campo( 'video', $msc_id, false ), get_the_title( $msc_id ) );
	$msc_galeria = msc_tema_galeria( msc_campo( 'galeria', $msc_id, false ) );
	if ( $msc_video || $msc_galeria ) :
		?>
		<section class="msc-seccion" aria-labelledby="grabacion-titulo">
			<h2 id="grabacion-titulo"><?php echo esc_html( $msc_video ? __( 'Grabación y fotos', 'msc' ) : __( 'Fotos', 'msc' ) ); ?></h2>
			<?php echo $msc_video . $msc_galeria; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</section>
	<?php endif; ?>

	<?php
	$msc_rel = array_values(
		array_filter(
			array_map( 'intval', (array) msc_campo( 'relacionados', $msc_id, false ) ),
			static function ( $r ) {
				return $r && 'publish' === get_post_status( $r );
			}
		)
	);
	if ( $msc_rel ) :
		?>
		<section class="msc-seccion" aria-labelledby="relacionados-titulo">
			<h2 id="relacionados-titulo"><?php esc_html_e( 'Relacionados', 'msc' ); ?></h2>
			<?php echo msc_tema_rejilla( $msc_rel, 'c4' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</section>
	<?php endif; ?>

	<?php $msc_otros = msc_otros_eventos( $msc_id, 3 ); ?>
	<?php if ( $msc_otros ) : ?>
		<section class="msc-seccion" aria-labelledby="otros-titulo">
			<h2 id="otros-titulo"><?php esc_html_e( 'Otros eventos', 'msc' ); ?></h2>
			<ul class="msc-lista-eventos" role="list">
				<?php
				foreach ( $msc_otros as $msc_o ) {
					echo msc_tema_fila_evento( $msc_o, true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
				?>
			</ul>
		</section>
	<?php endif; ?>
</div>
<?php
get_footer();
