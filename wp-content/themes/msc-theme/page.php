<?php
/**
 * Página genérica (por ejemplo, Política de privacidad).
 *
 * @package msc-theme
 */

defined( 'ABSPATH' ) || exit;

get_header();
while ( have_posts() ) :
	the_post();
	msc_tema_cabecera( get_the_title() );
	?>
	<div class="msc-contenedor msc-seccion">
		<div class="msc-texto msc-texto--legible">
			<?php the_content(); ?>
		</div>
	</div>
	<?php
endwhile;
get_footer();
