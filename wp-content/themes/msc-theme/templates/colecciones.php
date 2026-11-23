<?php
/**
 * Colecciones · portada: buscador, 5 colecciones, piezas destacadas e invitación a colaborar.
 *
 * @package msc-theme
 */

defined( 'ABSPATH' ) || exit;

get_header();
$msc_id    = get_queried_object_id();
$msc_todas = msc_url( 'colecciones/todas' );

ob_start();
?>
<form class="msc-buscar msc-buscar--cabecera" role="search" method="get" action="<?php echo esc_url( $msc_todas ); ?>">
	<label for="buscar-colecciones"><?php esc_html_e( 'Buscar en las colecciones', 'msc' ); ?></label>
	<div class="msc-buscar__fila">
		<input type="search" id="buscar-colecciones" name="msc_q" placeholder="<?php esc_attr_e( 'Título, tema, autor…', 'msc' ); ?>">
		<button type="submit" class="msc-btn msc-btn--primario"><?php esc_html_e( 'Buscar', 'msc' ); ?></button>
	</div>
</form>
<?php
msc_tema_cabecera( get_the_title(), (string) msc_campo( 'intro', $msc_id ), '', (string) ob_get_clean() );

$msc_cats = msc_categorias_coleccion();
if ( $msc_cats ) :
	?>
	<section class="msc-seccion msc-contenedor" aria-labelledby="tipos-titulo">
		<h2 id="tipos-titulo"><?php esc_html_e( 'Explora por tipo de pieza', 'msc' ); ?></h2>
		<ul class="msc-rejilla msc-rejilla--c5" role="list">
			<?php foreach ( $msc_cats as $msc_cat ) : ?>
				<li>
					<?php
					echo msc_tema_tarjeta( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						array(
							'titulo' => $msc_cat->name,
							'url'    => get_term_link( $msc_cat ),
							'imagen' => (int) get_term_meta( $msc_cat->term_id, 'imagen_portada', true ),
							/* translators: %d: número de piezas. */
							'meta'   => esc_html( sprintf( _n( '%d pieza', '%d piezas', (int) $msc_cat->count, 'msc' ), (int) $msc_cat->count ) ),
							'sizes'  => '(max-width: 820px) 50vw, 20vw',
							'clase'  => 'msc-tarjeta--categoria',
						)
					);
					?>
				</li>
			<?php endforeach; ?>
		</ul>
	</section>
<?php endif; ?>

<?php $msc_dest = msc_piezas_destacadas( 4 ); ?>
<?php if ( $msc_dest ) : ?>
	<section class="msc-seccion msc-contenedor" aria-labelledby="destacadas-titulo">
		<h2 id="destacadas-titulo"><?php esc_html_e( 'Piezas destacadas', 'msc' ); ?></h2>
		<?php
		echo msc_tema_rejilla( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			$msc_dest,
			'c4',
			array(
				'con_categoria' => true,
				'clase'         => 'msc-tarjeta--grande',
			)
		);
		?>
		<div class="msc-botones"><?php echo msc_tema_boton( __( 'Ver todas las piezas', 'msc' ), $msc_todas, 'secundario' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
	</section>
<?php endif; ?>

<?php
msc_tema_banda_colaborar( __( '¿Tienes una pieza que aportar al patrimonio sefardí?', 'msc' ) );
get_footer();
