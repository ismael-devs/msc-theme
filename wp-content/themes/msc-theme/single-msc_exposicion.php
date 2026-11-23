<?php
/**
 * Ficha de exposición.
 *
 * @package msc-theme
 */

defined( 'ABSPATH' ) || exit;

get_header();
$msc_id     = get_queried_object_id();
$msc_estado = msc_estado_exposicion( $msc_id );

msc_tema_cabecera( get_the_title( $msc_id ), '', msc_tema_pildora( $msc_estado ) );

$msc_sedes = array();
foreach ( (array) msc_campo( 'sede', $msc_id ) as $msc_s ) {
	if ( ! empty( $msc_s['nombre'] ) ) {
		$msc_sedes[] = esc_html( $msc_s['nombre'] );
	}
}
?>
<div class="msc-contenedor msc-ficha">
	<?php if ( msc_campo( 'imagen_principal', $msc_id ) ) : ?>
		<div class="msc-banner msc-banner--ficha">
			<?php
			echo msc_img( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				msc_campo( 'imagen_principal', $msc_id, false ),
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
			__( 'Fechas', 'msc' )  => esc_html( msc_rango_fechas( get_post_meta( $msc_id, 'fecha_inicio', true ), get_post_meta( $msc_id, 'fecha_cierre', true ) ) ),
			__( 'Sede', 'msc' )    => implode( '<br>', $msc_sedes ),
			__( 'Horario', 'msc' ) => esc_html( (string) msc_campo( 'horario', $msc_id ) ),
		),
		'msc-datos--compacto'
	);
	?>

	<?php echo msc_tema_texto( (string) msc_campo( 'texto', $msc_id ), 'msc-texto--legible' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

	<?php
	$msc_secciones = array_filter(
		(array) msc_campo( 'secciones', $msc_id ),
		static function ( $s ) {
			return ! empty( $s['titulo'] );
		}
	);
	if ( $msc_secciones ) :
		?>
		<section class="msc-seccion" aria-labelledby="secciones-titulo">
			<h2 id="secciones-titulo"><?php esc_html_e( 'Secciones temáticas', 'msc' ); ?></h2>
			<ul class="msc-rejilla msc-rejilla--c4" role="list">
				<?php foreach ( $msc_secciones as $msc_s ) : ?>
					<li>
						<?php
						echo msc_tema_tarjeta( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							array(
								'titulo' => $msc_s['titulo'],
								'url'    => $msc_s['enlace'] ?? '',
								'imagen' => $msc_s['imagen'] ?? 0,
								'meta'   => esc_html( $msc_s['texto'] ?? '' ),
							)
						);
						?>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>
	<?php endif; ?>

	<?php $msc_creditos = msc_tema_creditos( msc_campo( 'creditos', $msc_id ) ); ?>
	<?php if ( $msc_creditos ) : ?>
		<section class="msc-seccion" aria-labelledby="creditos-titulo">
			<h2 id="creditos-titulo"><?php esc_html_e( 'Créditos', 'msc' ); ?></h2>
			<?php echo $msc_creditos; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</section>
	<?php endif; ?>

	<?php
	$msc_video   = msc_tema_video( (string) msc_campo( 'video', $msc_id, false ), get_the_title( $msc_id ) );
	$msc_galeria = msc_tema_galeria( msc_campo( 'galeria', $msc_id, false ) );
	if ( $msc_video || $msc_galeria ) :
		?>
		<section class="msc-seccion" aria-labelledby="multimedia-titulo">
			<h2 id="multimedia-titulo"><?php esc_html_e( 'Multimedia', 'msc' ); ?></h2>
			<?php echo $msc_video . $msc_galeria; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</section>
	<?php endif; ?>

	<?php
	$msc_rel = array_merge( (array) msc_campo( 'publicacion_relacionada', $msc_id, false ), (array) msc_campo( 'piezas_relacionadas', $msc_id, false ) );
	$msc_rel = array_values(
		array_filter(
			array_map( 'intval', $msc_rel ),
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

	<?php $msc_otras = msc_otras_exposiciones( $msc_id, 4 ); ?>
	<?php if ( $msc_otras ) : ?>
		<section class="msc-seccion" aria-labelledby="otras-titulo">
			<h2 id="otras-titulo"><?php esc_html_e( 'Otras exposiciones', 'msc' ); ?></h2>
			<?php echo msc_tema_rejilla( $msc_otras, 'c4' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</section>
	<?php endif; ?>
</div>
<?php
msc_tema_banda_boletin();
get_footer();
