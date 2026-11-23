<?php
/**
 * Publicaciones: calendario destacado, catálogo (8 por página) y boletín.
 *
 * @package msc-theme
 */

defined( 'ABSPATH' ) || exit;

global $wp_query;
get_header();
msc_tema_cabecera( __( 'Publicaciones', 'msc' ), (string) msc_opcion( 'intro_publicaciones' ) );
?>
<div class="msc-seccion msc-contenedor"><?php msc_tema_calendario( 'publicaciones' ); ?></div>

<section class="msc-seccion msc-contenedor" aria-labelledby="catalogo-titulo">
	<h2 id="catalogo-titulo"><?php esc_html_e( 'Catálogo', 'msc' ); ?></h2>
	<?php if ( have_posts() ) : ?>
		<?php echo msc_tema_rejilla( wp_list_pluck( $wp_query->posts, 'ID' ), 'c4', array( 'ratio' => 'portada' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php msc_tema_paginacion(); ?>
	<?php else : ?>
		<?php echo msc_tema_vacio( __( 'Todavía no hay publicaciones en el catálogo.', 'msc' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	<?php endif; ?>
</section>
<?php
msc_tema_banda_boletin();
get_footer();
