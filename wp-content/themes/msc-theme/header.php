<?php
/**
 * Cabecera: saltar al contenido, logo, menú, selector ES | EN, buscador y menú móvil.
 *
 * @package msc-theme
 */

defined( 'ABSPATH' ) || exit;

$msc_enlaces = msc_tema_enlaces_menu();
$msc_idiomas = function_exists( 'msc_enlaces_idioma' ) ? msc_enlaces_idioma() : array(
	'es' => array(
		'url'    => home_url( '/' ),
		'actual' => true,
	),
	'en' => array(
		'url'    => null,
		'actual' => false,
	),
);
$msc_inicio  = function_exists( 'msc_url_base' ) ? msc_url_base() : home_url( '/' );
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#24335c">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="msc-saltar" href="#contenido"><?php esc_html_e( 'Saltar al contenido', 'msc' ); ?></a>
<header class="msc-header">
	<div class="msc-header__interior">
		<div class="msc-header__marca">
			<a class="msc-logo" href="<?php echo esc_url( $msc_inicio ); ?>" rel="home"<?php echo is_front_page() ? ' aria-current="page"' : ''; ?>>
				<?php
				$msc_logo = get_theme_mod( 'custom_logo' );
				if ( $msc_logo ) {
					echo wp_get_attachment_image(
						$msc_logo,
						'full',
						false,
						array(
							'class'         => 'msc-logo__img',
							'alt'           => 'Museo Sefardí de Caracas Morris E. Curiel',
							'loading'       => 'eager',
							'fetchpriority' => 'low',
						)
					);
				} elseif ( file_exists( MSC_TEMA_DIR . '/assets/img/logo.png' ) ) {
					printf( '<img class="msc-logo__img" src="%s" alt="%s" height="52" width="130">', esc_url( MSC_TEMA_URL . '/assets/img/logo.png' ), esc_attr( 'Museo Sefardí de Caracas Morris E. Curiel' ) );
				} else {
					echo '<span class="msc-logo__texto">Museo Sefardí<br>de Caracas</span>';
				}
				?>
			</a>
			<nav class="msc-nav" aria-label="<?php esc_attr_e( 'Principal', 'msc' ); ?>">
				<ul>
					<?php foreach ( $msc_enlaces as $msc_e ) : ?>
						<li><a href="<?php echo esc_url( $msc_e['url'] ); ?>"<?php echo $msc_e['actual'] ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $msc_e['titulo'] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</nav>
		</div>
		<div class="msc-header__acciones">
			<div class="msc-idiomas" role="group" aria-label="<?php esc_attr_e( 'Idioma', 'msc' ); ?>">
				<?php
				foreach ( $msc_idiomas as $msc_codigo => $msc_i ) :
					$msc_nombre = 'es' === $msc_codigo ? 'Español' : 'English';
					if ( $msc_i['actual'] ) :
						?>
						<a class="is-actual" href="<?php echo esc_url( (string) $msc_i['url'] ); ?>" lang="<?php echo esc_attr( $msc_codigo ); ?>" aria-current="true"><?php echo esc_html( strtoupper( $msc_codigo ) ); ?><span class="screen-reader-text"> — <?php echo esc_html( $msc_nombre ); ?></span></a>
					<?php elseif ( $msc_i['url'] ) : ?>
						<a href="<?php echo esc_url( $msc_i['url'] ); ?>" lang="<?php echo esc_attr( $msc_codigo ); ?>" hreflang="<?php echo esc_attr( $msc_codigo ); ?>"><?php echo esc_html( strtoupper( $msc_codigo ) ); ?><span class="screen-reader-text"> — <?php echo esc_html( $msc_nombre ); ?></span></a>
					<?php else : ?>
						<span class="is-pronto" lang="<?php echo esc_attr( $msc_codigo ); ?>" title="<?php echo esc_attr( 'en' === $msc_codigo ? 'Coming soon' : 'Próximamente' ); ?>"><?php echo esc_html( strtoupper( $msc_codigo ) ); ?><span class="screen-reader-text"> — <?php echo esc_html( 'en' === $msc_codigo ? 'English version coming soon' : 'Próximamente' ); ?></span></span>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
			<a class="msc-icono-btn" href="<?php echo esc_url( add_query_arg( 's', '', $msc_inicio ) ); ?>" data-msc-buscar aria-controls="msc-buscador" aria-expanded="false">
				<?php echo msc_icono( 'buscar' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span class="screen-reader-text"><?php esc_html_e( 'Buscar', 'msc' ); ?></span>
			</a>
			<button type="button" class="msc-icono-btn msc-menu-btn" aria-controls="msc-menu-movil" aria-expanded="false" data-msc-menu>
				<span class="msc-menu-btn__abrir"><?php echo msc_icono( 'menu' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span class="msc-menu-btn__cerrar"><?php echo msc_icono( 'cerrar' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span class="screen-reader-text" data-msc-menu-texto><?php esc_html_e( 'Abrir menú', 'msc' ); ?></span>
			</button>
		</div>
	</div>
	<div class="msc-buscador" id="msc-buscador" hidden>
		<form class="msc-contenedor msc-buscador__form" role="search" method="get" action="<?php echo esc_url( $msc_inicio ); ?>">
			<label for="msc-buscador-campo" class="screen-reader-text"><?php esc_html_e( 'Buscar en el sitio', 'msc' ); ?></label>
			<input type="search" id="msc-buscador-campo" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Buscar piezas, exposiciones, eventos…', 'msc' ); ?>">
			<button type="submit" class="msc-btn msc-btn--primario"><?php esc_html_e( 'Buscar', 'msc' ); ?></button>
		</form>
	</div>
	<nav class="msc-menu-movil" id="msc-menu-movil" aria-label="<?php esc_attr_e( 'Principal', 'msc' ); ?>" hidden>
		<ul>
			<?php foreach ( $msc_enlaces as $msc_e ) : ?>
				<li><a href="<?php echo esc_url( $msc_e['url'] ); ?>"<?php echo $msc_e['actual'] ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $msc_e['titulo'] ); ?></a></li>
			<?php endforeach; ?>
		</ul>
	</nav>
</header>
<main id="contenido" class="msc-main" tabindex="-1">
