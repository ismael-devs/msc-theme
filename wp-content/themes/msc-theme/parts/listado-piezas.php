<?php
/**
 * Listado de piezas: pestañas (Todas + categorías), filtros, cuadrícula de 12 y paginación.
 *
 * @package msc-theme
 */

defined( 'ABSPATH' ) || exit;

global $wp_query;
get_header();

$msc_termino = is_tax( 'categoria_coleccion' ) ? get_queried_object() : null;
$msc_titulo  = $msc_termino ? $msc_termino->name : __( 'Todas las piezas', 'msc' );
$msc_intro   = $msc_termino ? wp_strip_all_tags( term_description( $msc_termino ) ) : '';
$msc_accion  = $msc_termino ? get_term_link( $msc_termino ) : msc_url( 'colecciones/todas' );

// Pestañas.
$msc_pestanas  = '<nav class="msc-pestanas" aria-label="' . esc_attr__( 'Colecciones', 'msc' ) . '"><ul>';
$msc_pestanas .= '<li><a href="' . esc_url( msc_url( 'colecciones/todas' ) ) . '"' . ( $msc_termino ? '' : ' aria-current="page"' ) . '>' . esc_html__( 'Todas', 'msc' ) . '</a></li>';
foreach ( msc_categorias_coleccion() as $msc_cat ) {
	$msc_actual    = $msc_termino && $msc_termino->term_id === $msc_cat->term_id;
	$msc_pestanas .= '<li><a href="' . esc_url( get_term_link( $msc_cat ) ) . '"' . ( $msc_actual ? ' aria-current="page"' : '' ) . '>' . esc_html( $msc_cat->name ) . '</a></li>';
}
$msc_pestanas .= '</ul></nav>';

msc_tema_cabecera( $msc_titulo, $msc_intro, '', $msc_pestanas );

$msc_formas = array( '' => __( 'Todas', 'msc' ) ) + msc_formas_adquisicion();
/* translators: %s: nombre de la categoría. */
$msc_buscar_en = $msc_termino ? sprintf( __( 'Buscar en %s', 'msc' ), $msc_termino->name ) : __( 'Buscar en las colecciones', 'msc' );
?>
<div class="msc-contenedor">
	<?php
	msc_tema_filtros(
		__( 'Filtrar y ordenar', 'msc' ),
		$msc_accion,
		msc_tema_campo_filtro( 'text', 'msc_q', $msc_buscar_en, array(), __( 'Título, autor, tema…', 'msc' ) )
		. msc_tema_campo_filtro( 'select', 'msc_forma', __( 'Forma de adquisición', 'msc' ), $msc_formas )
		. msc_tema_campo_filtro(
			'select',
			'msc_orden',
			__( 'Ordenar por', 'msc' ),
			array(
				''         => __( 'Más recientes', 'msc' ),
				'antiguas' => __( 'Más antiguas', 'msc' ),
				'titulo'   => __( 'Título (A–Z)', 'msc' ),
			)
		)
	);
	?>
	<section class="msc-resultados" aria-labelledby="resultados-titulo">
		<div class="msc-resultados__cabecera">
			<h2 id="resultados-titulo" class="msc-resultados__total">
				<?php
				/* translators: %d: número de piezas. */
				echo esc_html( sprintf( _n( '%d pieza', '%d piezas', (int) $wp_query->found_posts, 'msc' ), (int) $wp_query->found_posts ) );
				?>
			</h2>
			<?php if ( $wp_query->found_posts ) : ?>
				<?php
				$msc_pp    = (int) $wp_query->get( 'posts_per_page' );
				$msc_pag   = max( 1, (int) get_query_var( 'paged' ) );
				$msc_desde = ( $msc_pag - 1 ) * $msc_pp + 1;
				$msc_hasta = min( $msc_pag * $msc_pp, (int) $wp_query->found_posts );
				?>
				<p class="msc-resultados__rango"><?php echo esc_html( $msc_desde . '–' . $msc_hasta ); ?></p>
			<?php endif; ?>
		</div>
		<?php if ( have_posts() ) : ?>
			<?php echo msc_tema_rejilla( wp_list_pluck( $wp_query->posts, 'ID' ), 'c4', array( 'con_categoria' => ! $msc_termino ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php msc_tema_paginacion(); ?>
		<?php else : ?>
			<?php echo msc_tema_vacio( __( 'No hay piezas que coincidan con tu búsqueda.', 'msc' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php endif; ?>
	</section>
</div>
<?php
msc_tema_banda_colaborar( __( '¿Tienes una pieza que aportar al patrimonio sefardí?', 'msc' ) );
get_footer();
