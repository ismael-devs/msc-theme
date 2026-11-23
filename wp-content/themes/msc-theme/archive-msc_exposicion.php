<?php
/**
 * Exposiciones pasadas (/exposiciones/pasadas/): buscador, filtro por año, cuadrícula y paginación.
 *
 * @package msc-theme
 */

defined( 'ABSPATH' ) || exit;

global $wp_query;
get_header();
msc_tema_cabecera( __( 'Exposiciones pasadas', 'msc' ), (string) msc_opcion( 'intro_pasadas' ) );

$msc_anios = array( '' => __( 'Todos', 'msc' ) );
foreach ( msc_anios_disponibles( 'msc_exposicion', 'fecha_cierre' ) as $msc_a ) {
	$msc_anios[ $msc_a ] = (string) $msc_a;
}
?>
<div class="msc-contenedor">
	<?php
	msc_tema_filtros(
		__( 'Buscar y filtrar', 'msc' ),
		msc_url( 'exposiciones/pasadas' ),
		msc_tema_campo_filtro( 'text', 'msc_q', __( 'Buscar', 'msc' ), array(), __( 'Título o tema', 'msc' ) )
		. msc_tema_campo_filtro( 'select', 'msc_anio', __( 'Año', 'msc' ), $msc_anios )
	);
	?>
	<section class="msc-resultados" aria-labelledby="resultados-titulo">
		<div class="msc-resultados__cabecera">
			<h2 id="resultados-titulo" class="msc-resultados__total">
				<?php
				/* translators: %d: número de exposiciones. */
				echo esc_html( sprintf( _n( '%d exposición', '%d exposiciones', (int) $wp_query->found_posts, 'msc' ), (int) $wp_query->found_posts ) );
				?>
			</h2>
		</div>
		<?php if ( have_posts() ) : ?>
			<?php echo msc_tema_rejilla( wp_list_pluck( $wp_query->posts, 'ID' ), 'c4' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php msc_tema_paginacion(); ?>
		<?php else : ?>
			<?php echo msc_tema_vacio( __( 'No hay exposiciones que coincidan con tu búsqueda.', 'msc' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php endif; ?>
	</section>
</div>
<?php
get_footer();
