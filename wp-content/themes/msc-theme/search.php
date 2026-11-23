<?php
/**
 * Resultados de búsqueda agrupados por tipo.
 *
 * @package msc-theme
 */

defined( 'ABSPATH' ) || exit;

get_header();
global $wp_query;
$msc_q = get_search_query();
msc_tema_cabecera(
	__( 'Resultados de búsqueda', 'msc' ),
	$msc_q ? sprintf(
		/* translators: 1: número de resultados, 2: texto buscado. */
		_n( '%1$d resultado para "%2$s"', '%1$d resultados para "%2$s"', (int) $wp_query->found_posts, 'msc' ),
		(int) $wp_query->found_posts,
		$msc_q
	) : ''
);
?>
<div class="msc-contenedor msc-seccion">
	<?php get_search_form(); ?>
	<?php
	if ( $msc_q && have_posts() && function_exists( 'msc_agrupar_resultados' ) ) :
		foreach ( msc_agrupar_resultados( $wp_query->posts ) as $msc_tipo => $msc_grupo ) :
			$msc_ids = wp_list_pluck( $msc_grupo['entradas'], 'ID' );
			?>
			<section class="msc-seccion" aria-labelledby="res-<?php echo esc_attr( $msc_tipo ); ?>">
				<h2 id="res-<?php echo esc_attr( $msc_tipo ); ?>"><?php echo esc_html( $msc_grupo['titulo'] ); ?> <span class="msc-cuenta">(<?php echo (int) count( $msc_ids ); ?>)</span></h2>
				<?php if ( 'page' === $msc_tipo ) : ?>
					<ul class="msc-lista-simple">
						<?php foreach ( $msc_ids as $msc_id ) : ?>
							<li><a href="<?php echo esc_url( get_permalink( $msc_id ) ); ?>"><?php echo esc_html( get_the_title( $msc_id ) ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				<?php elseif ( 'msc_evento' === $msc_tipo ) : ?>
					<ul class="msc-lista-eventos" role="list">
						<?php
						foreach ( $msc_ids as $msc_id ) {
							echo msc_tema_fila_evento( $msc_id, true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						}
						?>
					</ul>
				<?php else : ?>
					<?php echo msc_tema_rejilla( $msc_ids, 'c4', array( 'con_categoria' => 'msc_coleccion' === $msc_tipo ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php endif; ?>
			</section>
			<?php
		endforeach;
	elseif ( $msc_q ) :
		echo msc_tema_vacio( __( 'No encontramos resultados. Prueba con otras palabras.', 'msc' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	endif;
	?>
</div>
<?php
get_footer();
