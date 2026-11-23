<?php
/**
 * Plantilla de respaldo.
 *
 * @package msc-theme
 */

defined( 'ABSPATH' ) || exit;

get_header();
msc_tema_cabecera( wp_strip_all_tags( get_the_archive_title() ? get_the_archive_title() : get_bloginfo( 'name' ) ) );
?>
<div class="msc-contenedor msc-seccion">
	<?php if ( have_posts() ) : ?>
		<ul class="msc-lista-simple">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
			<?php endwhile; ?>
		</ul>
		<?php msc_tema_paginacion(); ?>
	<?php else : ?>
		<?php echo msc_tema_vacio( __( 'No hay contenido para mostrar.', 'msc' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	<?php endif; ?>
</div>
<?php
get_footer();
