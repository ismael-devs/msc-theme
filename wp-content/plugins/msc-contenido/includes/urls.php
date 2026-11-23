<?php
/**
 * URL anidadas, enlaces permanentes, redirecciones 301 canónicas y migas de pan.
 *
 * /colecciones/{categoria}/{pieza}/
 * /exposiciones/{slug}/  →  /exposiciones/pasadas/{slug}/ al pasar la fecha de cierre (301).
 * /eventos/{slug}/ (solo si el evento tiene descripción; si no, 301 a /eventos/).
 * /publicaciones/{slug}/ · /publicaciones/calendarios/
 *
 * @package msc-contenido
 */

defined( 'ABSPATH' ) || exit;

/**
 * Slugs reservados por tipo: no se pueden usar como nombre de una entrada.
 *
 * @return array<string, string[]>
 */
function msc_slugs_reservados() {
	return array(
		'msc_exposicion'  => array( 'pasadas', 'page' ),
		'msc_publicacion' => array( 'calendarios', 'page' ),
		'msc_evento'      => array( 'page' ),
		'msc_coleccion'   => array( 'page' ),
	);
}

/**
 * Añade una regla con su variante con prefijo de idioma (/en/...).
 *
 * @param string $regex Expresión regular (sin ^).
 * @param string $query Consulta con $1, $2...
 */
function msc_regla( $regex, $query ) {
	add_rewrite_rule( '^' . $regex, 'index.php?' . $query, 'top' );
	$idiomas = implode( '|', array_map( 'preg_quote', msc_idiomas_secundarios() ) );
	if ( '' === $idiomas ) {
		return;
	}
	$desplazada = preg_replace_callback(
		'/\$matches\[(\d+)\]|\$(\d+)/',
		static function ( $m ) {
			$n = (int) ( '' !== $m[1] ? $m[1] : $m[2] );
			return '$matches[' . ( $n + 1 ) . ']';
		},
		$query
	);
	add_rewrite_rule( '^(' . $idiomas . ')/' . $regex, 'index.php?lang=$matches[1]&' . $desplazada, 'top' );
}

/**
 * Registra las reglas de URL.
 */
function msc_registrar_reglas_url() {
	$p = '/?$';
	msc_regla( 'colecciones/todas/page/?([0-9]{1,})' . $p, 'post_type=msc_coleccion&msc_archivo=todas&paged=$matches[1]' );
	msc_regla( 'colecciones/todas' . $p, 'post_type=msc_coleccion&msc_archivo=todas' );
	msc_regla( 'colecciones/([^/]+)/page/?([0-9]{1,})' . $p, 'categoria_coleccion=$matches[1]&paged=$matches[2]' );
	msc_regla( 'colecciones/([^/]+)/([^/]+)' . $p, 'post_type=msc_coleccion&msc_coleccion=$matches[2]&name=$matches[2]&msc_cat=$matches[1]' );
	msc_regla( 'colecciones/([^/]+)' . $p, 'categoria_coleccion=$matches[1]' );

	msc_regla( 'exposiciones/pasadas/page/?([0-9]{1,})' . $p, 'post_type=msc_exposicion&msc_archivo=pasadas&paged=$matches[1]' );
	msc_regla( 'exposiciones/pasadas' . $p, 'post_type=msc_exposicion&msc_archivo=pasadas' );
	msc_regla( 'exposiciones/pasadas/([^/]+)' . $p, 'post_type=msc_exposicion&msc_exposicion=$matches[1]&name=$matches[1]' );
	msc_regla( 'exposiciones/([^/]+)' . $p, 'post_type=msc_exposicion&msc_exposicion=$matches[1]&name=$matches[1]' );

	msc_regla( 'eventos/page/?([0-9]{1,})' . $p, 'post_type=msc_evento&paged=$matches[1]' );
	msc_regla( 'eventos' . $p, 'post_type=msc_evento' );
	msc_regla( 'eventos/([^/]+)' . $p, 'post_type=msc_evento&msc_evento=$matches[1]&name=$matches[1]' );

	msc_regla( 'publicaciones/calendarios' . $p, 'post_type=msc_publicacion&msc_archivo=calendarios' );
	msc_regla( 'publicaciones/page/?([0-9]{1,})' . $p, 'post_type=msc_publicacion&paged=$matches[1]' );
	msc_regla( 'publicaciones' . $p, 'post_type=msc_publicacion' );
	msc_regla( 'publicaciones/([^/]+)' . $p, 'post_type=msc_publicacion&msc_publicacion=$matches[1]&name=$matches[1]' );
}
add_action( 'init', 'msc_registrar_reglas_url', 10 );

/**
 * Archivos de tipo de contenido (necesarios para que WordPress reconozca /eventos/, etc.).
 *
 * @param array  $args Argumentos.
 * @param string $tipo Tipo.
 * @return array
 */
function msc_activar_archivos( $args, $tipo ) {
	$archivos = array(
		'msc_coleccion'   => 'colecciones',
		'msc_exposicion'  => 'exposiciones',
		'msc_evento'      => 'eventos',
		'msc_publicacion' => 'publicaciones',
	);
	if ( isset( $archivos[ $tipo ] ) ) {
		$args['has_archive'] = $archivos[ $tipo ];
	}
	return $args;
}
add_filter( 'register_post_type_args', 'msc_activar_archivos', 10, 2 );

/**
 * Variables de consulta propias.
 *
 * @param string[] $vars Variables.
 * @return string[]
 */
function msc_variables_consulta( $vars ) {
	return array_merge( $vars, array( 'msc_archivo', 'msc_cat', 'msc_q', 'msc_forma', 'msc_orden', 'msc_anio', 'msc_tipo' ) );
}
add_filter( 'query_vars', 'msc_variables_consulta' );

/**
 * Categoría principal de una pieza (la de menor "orden" o la primera).
 *
 * @param int $post_id ID.
 * @return WP_Term|null
 */
function msc_categoria_principal( $post_id ) {
	$terminos = get_the_terms( $post_id, 'categoria_coleccion' );
	if ( ! $terminos || is_wp_error( $terminos ) ) {
		return null;
	}
	$primaria = (int) get_post_meta( $post_id, '_yoast_wpseo_primary_categoria_coleccion', true );
	if ( ! $primaria ) {
		$primaria = (int) get_post_meta( $post_id, 'rank_math_primary_categoria_coleccion', true );
	}
	foreach ( $terminos as $t ) {
		if ( $t->term_id === $primaria ) {
			return $t;
		}
	}
	usort(
		$terminos,
		static function ( $a, $b ) {
			return $a->term_id <=> $b->term_id;
		}
	);
	return $terminos[0];
}

/**
 * Enlace permanente de los cuatro tipos.
 *
 * @param string  $url  Enlace original.
 * @param WP_Post $post Entrada.
 * @return string
 */
function msc_enlace_permanente( $url, $post ) {
	$tipos = array( 'msc_coleccion', 'msc_exposicion', 'msc_evento', 'msc_publicacion' );
	if ( ! in_array( $post->post_type, $tipos, true ) || '' === $post->post_name || in_array( $post->post_status, array( 'draft', 'pending', 'auto-draft', 'future' ), true ) ) {
		return $url;
	}
	if ( ! get_option( 'permalink_structure' ) ) {
		return $url;
	}
	$idioma = msc_idioma_entrada( $post->ID );
	$slug   = $post->post_name;
	switch ( $post->post_type ) {
		case 'msc_coleccion':
			$cat = msc_categoria_principal( $post->ID );
			return msc_url( 'colecciones/' . ( $cat ? $cat->slug : 'sin-categoria' ) . '/' . $slug, $idioma );
		case 'msc_exposicion':
			$base = 'pasada' === msc_estado_exposicion( $post->ID ) ? 'exposiciones/pasadas/' : 'exposiciones/';
			return msc_url( $base . $slug, $idioma );
		case 'msc_evento':
			return msc_url( 'eventos/' . $slug, $idioma );
		case 'msc_publicacion':
			return msc_url( 'publicaciones/' . $slug, $idioma );
	}
	return $url;
}
add_filter( 'post_type_link', 'msc_enlace_permanente', 10, 2 );

/**
 * Enlace de las categorías de colección: /colecciones/{slug}/.
 *
 * @param string  $url      Enlace.
 * @param WP_Term $termino  Término.
 * @param string  $taxonomia Taxonomía.
 * @return string
 */
function msc_enlace_termino( $url, $termino, $taxonomia ) {
	if ( 'categoria_coleccion' !== $taxonomia || ! get_option( 'permalink_structure' ) ) {
		return $url;
	}
	$idioma = function_exists( 'pll_get_term_language' ) ? pll_get_term_language( $termino->term_id ) : null;
	return msc_url( 'colecciones/' . $termino->slug, $idioma ? $idioma : null );
}
add_filter( 'term_link', 'msc_enlace_termino', 10, 3 );

/**
 * Enlaces de archivo de cada tipo.
 *
 * @param string $url  Enlace.
 * @param string $tipo Tipo.
 * @return string
 */
function msc_enlace_archivo( $url, $tipo ) {
	if ( ! get_option( 'permalink_structure' ) ) {
		return $url;
	}
	switch ( $tipo ) {
		case 'msc_coleccion':
			return msc_url_pagina( 'msc-colecciones', 'colecciones' );
		case 'msc_exposicion':
			return msc_url_pagina( 'msc-exposiciones', 'exposiciones' );
		case 'msc_evento':
			return msc_url( 'eventos' );
		case 'msc_publicacion':
			return msc_url( 'publicaciones' );
	}
	return $url;
}
add_filter( 'post_type_archive_link', 'msc_enlace_archivo', 10, 2 );

/**
 * URL de una página editorial por su plantilla (en el idioma actual), con ruta por defecto.
 *
 * @param string $plantilla Clave de plantilla (msc-el-museo...).
 * @param string $ruta      Ruta de respaldo.
 * @return string
 */
function msc_url_pagina( $plantilla, $ruta ) {
	$id = msc_id_pagina( $plantilla );
	return $id ? get_permalink( $id ) : msc_url( $ruta );
}

/**
 * ID de la página que usa una plantilla del museo (traducida si existe).
 *
 * @param string $plantilla Plantilla.
 * @return int
 */
function msc_id_pagina( $plantilla ) {
	static $cache = array();
	if ( ! isset( $cache[ $plantilla ] ) ) {
		$ids                 = get_posts(
			array(
				'post_type'        => 'page',
				'post_status'      => 'publish',
				'meta_key'         => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'       => $plantilla, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				'fields'           => 'ids',
				'posts_per_page'   => 1,
				'lang'             => msc_idioma_defecto(),
				'suppress_filters' => false,
			)
		);
		$cache[ $plantilla ] = $ids ? (int) $ids[0] : 0;
	}
	$id = $cache[ $plantilla ];
	if ( $id && function_exists( 'pll_get_post' ) ) {
		$traducida = pll_get_post( $id, msc_idioma_actual() );
		if ( $traducida ) {
			$id = (int) $traducida;
		}
	}
	return $id;
}

/**
 * Página de privacidad (la de WordPress) en el idioma actual.
 *
 * @return string
 */
function msc_url_privacidad() {
	$url = get_privacy_policy_url();
	return $url ? $url : msc_url( 'politica-de-privacidad' );
}

/**
 * Evita usar slugs reservados (pasadas, calendarios, page).
 *
 * @param string $slug        Slug propuesto.
 * @param int    $post_id     ID.
 * @param string $estado      Estado.
 * @param string $tipo        Tipo.
 * @return string
 */
function msc_evitar_slugs_reservados( $slug, $post_id, $estado, $tipo ) {
	$reservados = msc_slugs_reservados();
	if ( isset( $reservados[ $tipo ] ) && in_array( $slug, $reservados[ $tipo ], true ) ) {
		$slug .= '-2';
	}
	return $slug;
}
add_filter( 'wp_unique_post_slug', 'msc_evitar_slugs_reservados', 10, 4 );

/**
 * Evita los slugs "todas" y "page" en categorías de colección.
 *
 * @param array  $datos     Datos del término.
 * @param string $taxonomia Taxonomía.
 * @return array
 */
function msc_evitar_slugs_termino( $datos, $taxonomia ) {
	if ( 'categoria_coleccion' === $taxonomia && in_array( $datos['slug'], array( 'todas', 'page', 'sin-categoria' ), true ) ) {
		$datos['slug'] .= '-2';
	}
	return $datos;
}
add_filter( 'wp_insert_term_data', 'msc_evitar_slugs_termino', 10, 2 );
add_filter( 'wp_update_term_data', 'msc_evitar_slugs_termino', 10, 2 );

/**
 * Redirecciones canónicas 301:
 *  - Exposición vigente ↔ pasada según la fecha.
 *  - Pieza con categoría incorrecta en la URL.
 *  - Evento sin descripción → /eventos/.
 */
function msc_redireccion_canonica() {
	if ( is_preview() || ! is_singular( array( 'msc_coleccion', 'msc_exposicion', 'msc_evento', 'msc_publicacion' ) ) ) {
		return;
	}
	$post = get_queried_object();
	if ( ! $post instanceof WP_Post || 'publish' !== $post->post_status ) {
		return;
	}
	if ( 'msc_evento' === $post->post_type && ! msc_evento_tiene_ficha( $post->ID ) ) {
		wp_safe_redirect( msc_url( 'eventos', msc_idioma_entrada( $post->ID ) ), 301, 'MSC' );
		exit;
	}
	$canonica = get_permalink( $post );
	$pedida   = wp_parse_url( isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '', PHP_URL_PATH );
	$esperada = wp_parse_url( $canonica, PHP_URL_PATH );
	if ( $pedida && $esperada && untrailingslashit( rawurldecode( $pedida ) ) !== untrailingslashit( rawurldecode( $esperada ) ) ) {
		wp_safe_redirect( $canonica, 301, 'MSC' );
		exit;
	}
}
add_action( 'template_redirect', 'msc_redireccion_canonica', 1 );

/**
 * Valida que la categoría de la URL exista en piezas (/colecciones/{cat}/{pieza}/);
 * si la pieza no existe pero el segundo tramo es "page", deja que WordPress responda 404.
 *
 * @param WP_Query $q Consulta.
 */
function msc_ajustar_consulta_pieza( $q ) {
	if ( is_admin() || ! $q->is_main_query() ) {
		return;
	}
	if ( $q->get( 'msc_coleccion' ) && $q->get( 'msc_cat' ) ) {
		$q->set( 'post_type', 'msc_coleccion' );
	}
}
add_action( 'pre_get_posts', 'msc_ajustar_consulta_pieza', 5 );

/**
 * Migas de pan de la vista actual.
 *
 * @return array<int, array{titulo: string, url: ?string}>
 */
function msc_migas() {
	$migas        = array(
		array(
			'titulo' => __( 'Inicio', 'msc' ),
			'url'    => msc_url_base(),
		),
	);
	$anadir       = static function ( $titulo, $url = null ) use ( &$migas ) {
		$migas[] = array(
			'titulo' => wp_strip_all_tags( $titulo ),
			'url'    => $url,
		);
	};
	$colecciones  = static function () use ( $anadir ) {
		$anadir( __( 'Colecciones', 'msc' ), msc_url_pagina( 'msc-colecciones', 'colecciones' ) );
	};
	$exposiciones = static function () use ( $anadir ) {
		$anadir( __( 'Exposiciones', 'msc' ), msc_url_pagina( 'msc-exposiciones', 'exposiciones' ) );
	};

	if ( is_front_page() ) {
		return array();
	}
	if ( is_singular( 'msc_coleccion' ) ) {
		$colecciones();
		$cat = msc_categoria_principal( get_queried_object_id() );
		if ( $cat ) {
			$anadir( $cat->name, get_term_link( $cat ) );
		}
		$anadir( get_the_title( get_queried_object_id() ) );
	} elseif ( is_tax( 'categoria_coleccion' ) ) {
		$colecciones();
		$anadir( single_term_title( '', false ) );
	} elseif ( is_post_type_archive( 'msc_coleccion' ) ) {
		$colecciones();
		$anadir( __( 'Todas las piezas', 'msc' ) );
	} elseif ( is_singular( 'msc_exposicion' ) ) {
		$exposiciones();
		if ( 'pasada' === msc_estado_exposicion( get_queried_object_id() ) ) {
			$anadir( __( 'Exposiciones pasadas', 'msc' ), msc_url( 'exposiciones/pasadas' ) );
		}
		$anadir( get_the_title( get_queried_object_id() ) );
	} elseif ( is_post_type_archive( 'msc_exposicion' ) ) {
		$exposiciones();
		$anadir( __( 'Exposiciones pasadas', 'msc' ) );
	} elseif ( is_singular( 'msc_evento' ) ) {
		$anadir( __( 'Eventos', 'msc' ), msc_url( 'eventos' ) );
		$anadir( get_the_title( get_queried_object_id() ) );
	} elseif ( is_post_type_archive( 'msc_evento' ) ) {
		$anadir( __( 'Eventos', 'msc' ) );
	} elseif ( is_singular( 'msc_publicacion' ) ) {
		$anadir( __( 'Publicaciones', 'msc' ), msc_url( 'publicaciones' ) );
		$anadir( get_the_title( get_queried_object_id() ) );
	} elseif ( is_post_type_archive( 'msc_publicacion' ) ) {
		if ( 'calendarios' === get_query_var( 'msc_archivo' ) ) {
			$anadir( __( 'Publicaciones', 'msc' ), msc_url( 'publicaciones' ) );
			$anadir( __( 'Calendarios anteriores', 'msc' ) );
		} else {
			$anadir( __( 'Publicaciones', 'msc' ) );
		}
	} elseif ( is_page() ) {
		$id = get_queried_object_id();
		foreach ( array_reverse( get_post_ancestors( $id ) ) as $ancestro ) {
			$anadir( get_the_title( $ancestro ), get_permalink( $ancestro ) );
		}
		$anadir( get_the_title( $id ) );
	} elseif ( is_search() ) {
		$anadir( __( 'Resultados de búsqueda', 'msc' ) );
	} elseif ( is_404() ) {
		$anadir( __( 'Página no encontrada', 'msc' ) );
	} elseif ( is_singular() ) {
		$anadir( get_the_title( get_queried_object_id() ) );
	}
	return apply_filters( 'msc_migas', $migas );
}
