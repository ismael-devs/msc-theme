<?php
/**
 * Inicio.
 *
 * @package msc-theme
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'msc_campo' ) ) {
	get_template_part( 'index' );
	return;
}

get_header();
$msc_id = get_queried_object_id();
$msc_c  = static function ( $nombre ) use ( $msc_id ) {
	return msc_campo( $nombre, $msc_id );
};
?>

<section class="msc-hero" aria-labelledby="hero-titulo">
	<div class="msc-hero__media">
		<?php
		echo msc_img( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			msc_campo( 'hero_imagen', $msc_id, false ),
			array(
				'sizes'     => '(max-width: 820px) 100vw, 1312px',
				'tamano'    => 'msc-2400',
				'prioridad' => true,
			)
		);
		?>
	</div>
	<div class="msc-hero__texto">
		<h1 id="hero-titulo"><?php echo esc_html( $msc_c( 'hero_titulo' ) ? $msc_c( 'hero_titulo' ) : 'Museo Sefardí de Caracas Morris E. Curiel' ); ?></h1>
		<?php if ( $msc_c( 'hero_frase' ) ) : ?>
			<p class="msc-hero__frase"><?php echo esc_html( $msc_c( 'hero_frase' ) ); ?></p>
		<?php endif; ?>
		<div class="msc-botones">
			<?php
			echo msc_tema_boton( $msc_c( 'hero_boton1' ), $msc_c( 'hero_boton1_url' ) ? $msc_c( 'hero_boton1_url' ) : msc_url_pagina( 'msc-colecciones', 'colecciones' ), 'primario' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo msc_tema_boton( $msc_c( 'hero_boton2' ), $msc_c( 'hero_boton2_url' ) ? $msc_c( 'hero_boton2_url' ) : msc_url_pagina( 'msc-visitanos', 'visitanos' ), 'secundario' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			?>
		</div>
	</div>
</section>

<?php if ( $msc_c( 'memoria_texto' ) ) : ?>
	<section class="msc-seccion msc-contenedor msc-memoria" aria-labelledby="memoria-titulo">
		<h2 id="memoria-titulo"><?php echo esc_html( $msc_c( 'memoria_titulo' ) ); ?></h2>
		<div>
			<?php echo msc_tema_texto( $msc_c( 'memoria_texto' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<p><a class="msc-enlace-flecha" href="<?php echo esc_url( msc_url_pagina( 'msc-el-museo', 'el-museo' ) . '#historia' ); ?>"><?php esc_html_e( 'Conoce nuestra historia', 'msc' ); ?> <?php echo msc_icono( 'flecha', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a></p>
		</div>
	</section>
<?php endif; ?>

<?php
// Destacado automático: exposición actual (o próxima) y, si no hay, el próximo evento.
$msc_modo      = $msc_c( 'destacado_modo' ) ? $msc_c( 'destacado_modo' ) : 'auto';
$msc_destacado = 0;
$msc_sobre     = '';
if ( in_array( $msc_modo, array( 'auto', 'exposicion' ), true ) ) {
	$msc_destacado = msc_exposicion_actual();
	if ( $msc_destacado ) {
		$msc_sobre = 'proxima' === msc_estado_exposicion( $msc_destacado ) ? __( 'Próxima exposición', 'msc' ) : __( 'Exposición actual', 'msc' );
	}
}
if ( ! $msc_destacado && in_array( $msc_modo, array( 'auto', 'evento' ), true ) ) {
	$msc_prox = msc_eventos_proximos( 1 );
	if ( $msc_prox ) {
		$msc_destacado = $msc_prox[0];
		$msc_sobre     = __( 'Próximo evento', 'msc' );
	}
}
if ( $msc_destacado ) :
	$msc_es_expo = 'msc_exposicion' === get_post_type( $msc_destacado );
	$msc_fechas  = $msc_es_expo
		? msc_rango_fechas( get_post_meta( $msc_destacado, 'fecha_inicio', true ), get_post_meta( $msc_destacado, 'fecha_cierre', true ) )
		: msc_formatear_fecha( get_post_meta( $msc_destacado, 'fecha_hora', true ), 'j \d\e F \d\e Y · H:i' );
	$msc_enlace  = ( $msc_es_expo || msc_evento_tiene_ficha( $msc_destacado ) ) ? get_permalink( $msc_destacado ) : msc_url( 'eventos' );
	?>
	<section class="msc-seccion msc-contenedor" aria-labelledby="destacado-titulo">
		<div class="msc-destacado">
			<div class="msc-destacado__media">
				<?php
				echo msc_img( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					msc_imagen_principal( $msc_destacado ),
					array(
						'sizes'  => '(max-width: 820px) 100vw, 640px',
						'tamano' => 'msc-1200',
					)
				);
				?>
			</div>
			<div class="msc-destacado__texto">
				<p class="msc-sobretitulo"><?php echo esc_html( $msc_sobre ); ?></p>
				<h2 id="destacado-titulo" class="msc-destacado__titulo"><?php echo esc_html( get_the_title( $msc_destacado ) ); ?></h2>
				<?php if ( $msc_fechas ) : ?>
					<p class="msc-destacado__fechas"><?php echo esc_html( $msc_fechas ); ?></p>
				<?php endif; ?>
				<p><a class="msc-enlace-flecha" href="<?php echo esc_url( $msc_enlace ); ?>"><?php echo esc_html( $msc_es_expo ? __( 'Ver exposición', 'msc' ) : __( 'Ver evento', 'msc' ) ); ?><span class="screen-reader-text">: <?php echo esc_html( get_the_title( $msc_destacado ) ); ?></span> <?php echo msc_icono( 'flecha', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a></p>
			</div>
		</div>
	</section>
<?php endif; ?>

<?php $msc_cats = msc_categorias_coleccion(); ?>
<?php if ( $msc_cats ) : ?>
	<section class="msc-seccion msc-contenedor" aria-labelledby="colecciones-titulo">
		<div class="msc-seccion__cabecera">
			<h2 id="colecciones-titulo"><?php esc_html_e( 'Explora las colecciones', 'msc' ); ?></h2>
			<a class="msc-enlace-flecha" href="<?php echo esc_url( msc_url_pagina( 'msc-colecciones', 'colecciones' ) ); ?>"><?php esc_html_e( 'Ver todas', 'msc' ); ?><span class="screen-reader-text"> <?php esc_html_e( 'las colecciones', 'msc' ); ?></span> <?php echo msc_icono( 'flecha', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
		</div>
		<ul class="msc-rejilla msc-rejilla--c5 msc-colecciones-inicio" role="list">
			<?php foreach ( $msc_cats as $msc_cat ) : ?>
				<li class="msc-cat-inicio">
					<a href="<?php echo esc_url( get_term_link( $msc_cat ) ); ?>">
						<span class="msc-cat-inicio__media">
							<?php
							echo msc_img( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								get_term_meta( $msc_cat->term_id, 'imagen_portada', true ),
								array(
									'sizes'  => '(max-width: 820px) 50vw, 20vw',
									'tamano' => 'msc-800',
								)
							);
							?>
						</span>
						<span class="msc-punto-nombre"><span class="msc-punto" style="--punto: <?php echo esc_attr( msc_color_categoria( $msc_cat ) ); ?>" aria-hidden="true"></span><?php echo esc_html( $msc_cat->name ); ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</section>
<?php endif; ?>

<?php
$msc_espacios = $msc_c( 'espacios' );
if ( is_array( $msc_espacios ) && $msc_espacios ) :
	?>
	<section class="msc-seccion msc-contenedor" aria-labelledby="espacios-titulo">
		<h2 id="espacios-titulo"><?php echo esc_html( $msc_c( 'espacios_titulo' ) ); ?></h2>
		<ul class="msc-rejilla msc-rejilla--c3 msc-espacios" role="list">
			<?php foreach ( $msc_espacios as $msc_e ) : ?>
				<li class="msc-espacio">
					<div class="msc-espacio__media">
						<?php
						echo msc_img( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							$msc_e['imagen'] ?? 0,
							array(
								'sizes'  => '(max-width: 820px) 100vw, 33vw',
								'tamano' => 'msc-800',
							)
						);
						?>
					</div>
					<h3>
						<?php if ( ! empty( $msc_e['enlace'] ) ) : ?>
							<a href="<?php echo esc_url( $msc_e['enlace'] ); ?>"><?php echo esc_html( $msc_e['titulo'] ); ?></a>
						<?php else : ?>
							<?php echo esc_html( $msc_e['titulo'] ); ?>
						<?php endif; ?>
					</h3>
					<?php if ( ! empty( $msc_e['texto'] ) ) : ?>
						<p><?php echo esc_html( $msc_e['texto'] ); ?></p>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</section>
<?php endif; ?>

<div class="msc-seccion msc-contenedor"><?php msc_tema_calendario( 'inicio' ); ?></div>

<?php
$msc_colab = msc_url_pagina( 'msc-colaboraciones', 'colaboraciones' );
msc_tema_banda(
	$msc_c( 'legado_titulo' ) ? $msc_c( 'legado_titulo' ) : __( 'Confía al museo la custodia de tu legado', 'msc' ),
	(string) $msc_c( 'legado_texto' ),
	msc_tema_boton( __( 'Donaciones', 'msc' ), $msc_colab . '#donacion', 'dorado' )
	. msc_tema_boton( __( 'Adquisiciones', 'msc' ), $msc_colab . '#adquisicion', 'dorado' )
	. msc_tema_boton( __( 'Comodato', 'msc' ), $msc_colab . '#comodato', 'dorado' ),
	'legado-titulo'
);
?>

<?php get_template_part( 'parts/visitanos-resumen' ); ?>

<?php msc_tema_marquee(); ?>

<?php
get_footer();
