<?php
/**
 * SEO técnico: título, descripción, canonical, Open Graph, robots y sitemap.
 * Si Yoast o Rank Math están activos, se encargan ellos de metadatos y canonical.
 *
 * @package msc-contenido
 */

defined( 'ABSPATH' ) || exit;

/**
 * Indica si la vista actual tiene filtros (búsqueda interna, año, tipo...).
 *
 * @return bool
 */
function msc_vista_filtrada() {
	foreach ( array( 'msc_q', 'msc_forma', 'msc_orden', 'msc_anio', 'msc_tipo' ) as $var ) {
		if ( '' !== (string) get_query_var( $var ) ) {
			return true;
		}
	}
	return false;
}

/**
 * Títulos de las vistas virtuales.
 *
 * @return string
 */
function msc_titulo_vista() {
	if ( is_post_type_archive( 'msc_exposicion' ) ) {
		return __( 'Exposiciones pasadas', 'msc' );
	}
	if ( is_post_type_archive( 'msc_coleccion' ) ) {
		return __( 'Todas las piezas', 'msc' );
	}
	if ( is_post_type_archive( 'msc_evento' ) ) {
		return __( 'Eventos', 'msc' );
	}
	if ( is_post_type_archive( 'msc_publicacion' ) ) {
		return 'calendarios' === get_query_var( 'msc_archivo' ) ? __( 'Calendarios anteriores', 'msc' ) : __( 'Publicaciones', 'msc' );
	}
	return '';
}

/**
 * Ajusta el título del documento en las vistas virtuales.
 *
 * @param array $partes Partes.
 * @return array
 */
function msc_titulo_documento( $partes ) {
	$titulo = msc_titulo_vista();
	if ( $titulo ) {
		$partes['title'] = $titulo;
	}
	return $partes;
}
add_filter( 'document_title_parts', 'msc_titulo_documento' );

/**
 * Descripción de la vista actual.
 *
 * @return string
 */
function msc_descripcion_vista() {
	if ( is_singular() ) {
		$id = get_queried_object_id();
		if ( is_front_page() ) {
			$texto = msc_campo( 'memoria_texto', $id );
			return $texto ? wp_trim_words( $texto, 30 ) : get_bloginfo( 'description' );
		}
		$intro = msc_campo( 'intro', $id );
		if ( $intro ) {
			return wp_trim_words( $intro, 30 );
		}
		return msc_resumen( $id, 30 );
	}
	if ( is_tax( 'categoria_coleccion' ) ) {
		return wp_trim_words( term_description(), 30 );
	}
	if ( is_post_type_archive( 'msc_evento' ) ) {
		return (string) msc_opcion( 'intro_eventos' );
	}
	if ( is_post_type_archive( 'msc_exposicion' ) ) {
		return (string) msc_opcion( 'intro_pasadas' );
	}
	if ( is_post_type_archive( 'msc_publicacion' ) ) {
		return (string) msc_opcion( 'calendarios' === get_query_var( 'msc_archivo' ) ? 'intro_calendarios' : 'intro_publicaciones' );
	}
	return get_bloginfo( 'description' );
}

/**
 * Imprime descripción, canonical (fuera de entradas) y Open Graph.
 */
function msc_imprimir_metadatos() {
	if ( msc_hay_plugin_seo() || is_404() ) {
		return;
	}
	$descripcion = trim( wp_strip_all_tags( msc_descripcion_vista() ) );
	if ( '' === $descripcion && is_singular() ) {
		$descripcion = (string) get_post_meta( get_queried_object_id(), 'subtitulo', true );
	}
	if ( '' === $descripcion ) {
		$descripcion = get_bloginfo( 'description' );
	}
	if ( $descripcion ) {
		printf( '<meta name="description" content="%s">' . "\n", esc_attr( $descripcion ) );
	}

	$url = msc_url_actual();
	$pag = max( 1, (int) get_query_var( 'paged' ) );
	if ( $pag > 1 ) {
		$url = trailingslashit( $url ) . user_trailingslashit( 'page/' . $pag, 'paged' );
	}
	// En entradas y páginas, WordPress ya imprime rel=canonical.
	if ( ! is_singular() && ! is_search() ) {
		printf( '<link rel="canonical" href="%s">' . "\n", esc_url( $url ) );
	}

	$titulo = wp_get_document_title();
	$imagen = null;
	if ( is_singular() ) {
		$id     = get_queried_object_id();
		$imagen = msc_url_imagen( msc_imagen_principal( $id ) );
		if ( ! $imagen && is_front_page() ) {
			$imagen = msc_url_imagen( msc_id_imagen( msc_campo( 'hero_imagen', $id, false ) ) );
		}
	}
	$og = array(
		'og:type'        => is_singular( array( 'msc_exposicion', 'msc_evento', 'msc_publicacion', 'msc_coleccion' ) ) ? 'article' : 'website',
		'og:site_name'   => get_bloginfo( 'name' ),
		'og:title'       => $titulo,
		'og:description' => $descripcion,
		'og:url'         => $url,
		'og:locale'      => 'en' === msc_idioma_actual() ? 'en_US' : 'es_VE',
		'og:image'       => $imagen,
	);
	foreach ( $og as $propiedad => $valor ) {
		if ( $valor ) {
			printf( '<meta property="%s" content="%s">' . "\n", esc_attr( $propiedad ), esc_attr( $valor ) );
		}
	}
	printf( '<meta name="twitter:card" content="%s">' . "\n", $imagen ? 'summary_large_image' : 'summary' );
}
add_action( 'wp_head', 'msc_imprimir_metadatos', 5 );

/**
 * Robots: noindex en filtros, búsquedas y vistas sin contenido propio.
 *
 * @param array $robots Directivas.
 * @return array
 */
function msc_robots( $robots ) {
	if ( is_search() || msc_vista_filtrada() || is_404() ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
	}
	return $robots;
}
add_filter( 'wp_robots', 'msc_robots' );

/**
 * Sitemap: solo eventos con ficha.
 *
 * @param array  $args Argumentos.
 * @param string $tipo Tipo.
 * @return array
 */
function msc_sitemap_eventos( $args, $tipo ) {
	if ( 'msc_evento' === $tipo ) {
		$args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			array(
				'key'     => 'descripcion',
				'value'   => '',
				'compare' => '!=',
			),
		);
	}
	return $args;
}
add_filter( 'wp_sitemaps_posts_query_args', 'msc_sitemap_eventos', 10, 2 );

/**
 * Sitemap: quita usuarios y taxonomías internas.
 *
 * @param WP_Sitemaps_Provider $proveedor Proveedor.
 * @param string               $nombre    Nombre.
 * @return WP_Sitemaps_Provider|false
 */
function msc_sitemap_proveedores( $proveedor, $nombre ) {
	return 'users' === $nombre ? false : $proveedor;
}
add_filter( 'wp_sitemaps_add_provider', 'msc_sitemap_proveedores', 10, 2 );

/**
 * Sitemap: taxonomías incluidas (solo categorías de colección).
 *
 * @param array $taxonomias Taxonomías.
 * @return array
 */
function msc_sitemap_taxonomias( $taxonomias ) {
	return array_intersect_key( $taxonomias, array( 'categoria_coleccion' => true ) );
}
add_filter( 'wp_sitemaps_taxonomies', 'msc_sitemap_taxonomias' );

/**
 * Sitemap: añade las vistas virtuales (exposiciones pasadas y calendarios).
 * Se exponen como un proveedor sencillo.
 */
function msc_sitemap_vistas() {
	if ( ! class_exists( 'WP_Sitemaps_Provider' ) ) {
		return;
	}
	require_once MSC_CONTENIDO_DIR . 'includes/class-msc-sitemap-vistas.php';
	wp_register_sitemap_provider( 'msc', new MSC_Sitemap_Vistas() );
}
add_action( 'init', 'msc_sitemap_vistas', 20 );
