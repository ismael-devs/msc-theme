<?php
/**
 * Consultas reutilizables (Inicio, portadas y fichas) y ajustes de los listados.
 *
 * @package msc-contenido
 */

defined( 'ABSPATH' ) || exit;

/**
 * Cachea el resultado de una consulta en un transient versionado (12 h por defecto).
 *
 * @param string   $nombre   Nombre lógico.
 * @param callable $callback Función que devuelve el valor.
 * @param int      $ttl      Duración.
 * @return mixed
 */
function msc_cache( $nombre, $callback, $ttl = 12 * HOUR_IN_SECONDS ) {
	$clave = msc_clave_cache( $nombre );
	$valor = get_transient( $clave );
	if ( false === $valor ) {
		$valor = call_user_func( $callback );
		set_transient( $clave, $valor, $ttl );
	}
	return $valor;
}

/**
 * IDs de entradas con argumentos comunes (publicadas, idioma actual).
 *
 * @param array $args Argumentos de WP_Query.
 * @return int[]
 */
function msc_ids( $args ) {
	$args = array_merge(
		array(
			'post_status'         => 'publish',
			'fields'              => 'ids',
			'no_found_rows'       => true,
			'ignore_sticky_posts' => true,
			'suppress_filters'    => false,
		),
		$args
	);
	return array_map( 'intval', get_posts( $args ) );
}

/**
 * Fecha y hora de inicio de hoy en formato de base de datos (para eventos).
 *
 * @return string
 */
function msc_inicio_hoy() {
	$h = msc_hoy();
	return substr( $h, 0, 4 ) . '-' . substr( $h, 4, 2 ) . '-' . substr( $h, 6, 2 ) . ' 00:00:00';
}

/**
 * Exposiciones en cartelera (inicio ≤ hoy y cierre ≥ hoy o vacío).
 *
 * @param int $n Cantidad.
 * @return int[]
 */
function msc_exposiciones_vigentes( $n = 1 ) {
	$hoy = msc_hoy();
	return msc_ids(
		array(
			'post_type'      => 'msc_exposicion',
			'posts_per_page' => $n,
			'meta_key'       => 'fecha_inicio', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'orderby'        => 'meta_value',
			'order'          => 'DESC',
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				'relation' => 'AND',
				array(
					'key'     => 'fecha_inicio',
					'value'   => $hoy,
					'compare' => '<=',
				),
				array(
					'relation' => 'OR',
					array(
						'key'     => 'fecha_cierre',
						'value'   => $hoy,
						'compare' => '>=',
					),
					array(
						'key'     => 'fecha_cierre',
						'value'   => '',
						'compare' => '=',
					),
					array(
						'key'     => 'fecha_cierre',
						'compare' => 'NOT EXISTS',
					),
				),
			),
		)
	);
}

/**
 * Próximas exposiciones (inicio > hoy), la más cercana primero.
 *
 * @param int $n Cantidad.
 * @return int[]
 */
function msc_exposiciones_proximas( $n = 1 ) {
	return msc_ids(
		array(
			'post_type'      => 'msc_exposicion',
			'posts_per_page' => $n,
			'meta_key'       => 'fecha_inicio', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'orderby'        => 'meta_value',
			'order'          => 'ASC',
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				array(
					'key'     => 'fecha_inicio',
					'value'   => msc_hoy(),
					'compare' => '>',
				),
			),
		)
	);
}

/**
 * Exposición actual; si no hay, la próxima. 0 si no hay ninguna.
 *
 * @return int
 */
function msc_exposicion_actual() {
	return (int) msc_cache(
		'expo_actual',
		static function () {
			$ids = msc_exposiciones_vigentes( 1 );
			if ( ! $ids ) {
				$ids = msc_exposiciones_proximas( 1 );
			}
			return $ids ? $ids[0] : 0;
		}
	);
}

/**
 * Exposiciones pasadas más recientes.
 *
 * @param int $n Cantidad.
 * @return int[]
 */
function msc_exposiciones_pasadas( $n = 4 ) {
	return msc_cache(
		'expo_pasadas_' . $n,
		static function () use ( $n ) {
			return msc_ids(
				array(
					'post_type'      => 'msc_exposicion',
					'posts_per_page' => $n,
					'meta_key'       => 'fecha_cierre', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					'orderby'        => 'meta_value',
					'order'          => 'DESC',
					'meta_query'     => array( msc_condicion_pasada() ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				)
			);
		}
	);
}

/**
 * Condición meta "exposición pasada".
 *
 * @return array
 */
function msc_condicion_pasada() {
	// Cierre estrictamente anterior a hoy (BETWEEN excluye los valores vacíos).
	$ayer = msc_fecha_objeto( msc_hoy() )->modify( '-1 day' )->format( 'Ymd' );
	return array(
		'key'     => 'fecha_cierre',
		'value'   => array( '19000101', $ayer ),
		'compare' => 'BETWEEN',
		'type'    => 'CHAR',
	);
}

/**
 * Otras exposiciones (excluye la actual).
 *
 * @param int $excluir ID a excluir.
 * @param int $n       Cantidad.
 * @return int[]
 */
function msc_otras_exposiciones( $excluir, $n = 4 ) {
	return msc_ids(
		array(
			'post_type'      => 'msc_exposicion',
			'posts_per_page' => $n,
			'post__not_in'   => array( $excluir ),
			'meta_key'       => 'fecha_inicio', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'orderby'        => 'meta_value',
			'order'          => 'DESC',
		)
	);
}

/**
 * Próximos eventos (desde hoy), el más cercano primero.
 *
 * @param int $n Cantidad (-1 todos).
 * @return int[]
 */
function msc_eventos_proximos( $n = -1 ) {
	return msc_cache(
		'eventos_proximos_' . $n,
		static function () use ( $n ) {
			return msc_ids(
				array(
					'post_type'      => 'msc_evento',
					'posts_per_page' => $n,
					'meta_key'       => 'fecha_hora', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					'orderby'        => 'meta_value',
					'order'          => 'ASC',
					'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
						array(
							'key'     => 'fecha_hora',
							'value'   => msc_inicio_hoy(),
							'compare' => '>=',
						),
					),
				)
			);
		}
	);
}

/**
 * Otros eventos para la ficha (próximos primero y luego los pasados más recientes).
 *
 * @param int $excluir ID a excluir.
 * @param int $n       Cantidad.
 * @return int[]
 */
function msc_otros_eventos( $excluir, $n = 3 ) {
	$ids = array_values( array_diff( msc_eventos_proximos( $n + 1 ), array( $excluir ) ) );
	if ( count( $ids ) < $n ) {
		$pasados = msc_ids(
			array(
				'post_type'      => 'msc_evento',
				'posts_per_page' => $n,
				'post__not_in'   => array_merge( $ids, array( $excluir ) ),
				'meta_key'       => 'fecha_hora', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'orderby'        => 'meta_value',
				'order'          => 'DESC',
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'     => 'fecha_hora',
						'value'   => msc_inicio_hoy(),
						'compare' => '<',
					),
				),
			)
		);
		$ids     = array_merge( $ids, $pasados );
	}
	return array_slice( $ids, 0, $n );
}

/**
 * Argumentos para publicaciones de tipo calendario.
 *
 * @return array
 */
function msc_args_calendarios() {
	return array(
		'post_type' => 'msc_publicacion',
		'tax_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			array(
				'taxonomy' => 'tipo_publicacion',
				'field'    => 'slug',
				'terms'    => 'calendario',
			),
		),
		'meta_key'  => 'anio_civil', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		'orderby'   => 'meta_value_num',
		'order'     => 'DESC',
	);
}

/**
 * Calendario vigente: el destacado con el año civil más reciente (si no hay destacado, el más reciente).
 *
 * @return int
 */
function msc_calendario_vigente() {
	return (int) msc_cache(
		'calendario_vigente',
		static function () {
			$args                   = msc_args_calendarios();
			$args['posts_per_page'] = 1;
			$destacados             = msc_ids(
				array_merge(
					$args,
					array(
						'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
							array(
								'key'   => 'destacado',
								'value' => '1',
							),
						),
					)
				)
			);
			if ( ! $destacados ) {
				$destacados = msc_ids( $args );
			}
			return $destacados ? $destacados[0] : 0;
		}
	);
}

/**
 * Calendarios anteriores (excluye el vigente). La cantidad se ajusta en Museo → Textos de portadas.
 *
 * @param int|null $n Cantidad.
 * @return int[]
 */
function msc_calendarios_anteriores( $n = null ) {
	$n                      = $n ? $n : (int) msc_opcion( 'calendarios_anteriores' );
	$n                      = $n > 0 ? $n : 4;
	$args                   = msc_args_calendarios();
	$args['posts_per_page'] = $n;
	$args['post__not_in']   = array( msc_calendario_vigente() );
	return msc_ids( $args );
}

/**
 * Título del calendario: "Calendario 2026 · 5787".
 *
 * @param int $id ID.
 * @return string
 */
function msc_titulo_calendario( $id ) {
	$civil  = (string) msc_campo( 'anio_civil', $id );
	$hebreo = (string) msc_campo( 'anio_hebreo', $id );
	if ( ! $civil ) {
		return get_the_title( $id );
	}
	/* translators: %s: año civil. */
	$titulo = sprintf( __( 'Calendario %s', 'msc' ), $civil );
	return $hebreo ? $titulo . ' · ' . $hebreo : $titulo;
}

/**
 * Piezas destacadas.
 *
 * @param int $n Cantidad.
 * @return int[]
 */
function msc_piezas_destacadas( $n = 4 ) {
	return msc_cache(
		'piezas_destacadas_' . $n,
		static function () use ( $n ) {
			return msc_ids(
				array(
					'post_type'      => 'msc_coleccion',
					'posts_per_page' => $n,
					'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
						array(
							'key'   => 'destacada',
							'value' => '1',
						),
					),
				)
			);
		}
	);
}

/**
 * Más piezas de la misma categoría.
 *
 * @param int $id ID de la pieza.
 * @param int $n  Cantidad.
 * @return int[]
 */
function msc_mas_piezas( $id, $n = 4 ) {
	$cat = msc_categoria_principal( $id );
	if ( ! $cat ) {
		return array();
	}
	return msc_ids(
		array(
			'post_type'      => 'msc_coleccion',
			'posts_per_page' => $n,
			'post__not_in'   => array( $id ),
			'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => 'categoria_coleccion',
					'terms'    => $cat->term_id,
				),
			),
		)
	);
}

/**
 * Otras publicaciones (catálogo, sin calendarios).
 *
 * @param int $excluir ID.
 * @param int $n       Cantidad.
 * @return int[]
 */
function msc_otras_publicaciones( $excluir, $n = 4 ) {
	return msc_ids(
		array(
			'post_type'      => 'msc_publicacion',
			'posts_per_page' => $n,
			'post__not_in'   => array( $excluir ),
			'tax_query'      => array( msc_condicion_no_calendario() ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		)
	);
}

/**
 * Condición de taxonomía para excluir calendarios.
 *
 * @return array
 */
function msc_condicion_no_calendario() {
	return array(
		'taxonomy' => 'tipo_publicacion',
		'field'    => 'slug',
		'terms'    => 'calendario',
		'operator' => 'NOT IN',
	);
}

/**
 * Categorías de colección ordenadas por el campo "orden".
 *
 * @return WP_Term[]
 */
function msc_categorias_coleccion() {
	$terminos = get_terms(
		array(
			'taxonomy'   => 'categoria_coleccion',
			'hide_empty' => false,
		)
	);
	if ( is_wp_error( $terminos ) ) {
		return array();
	}
	usort(
		$terminos,
		static function ( $a, $b ) {
			$oa = (int) get_term_meta( $a->term_id, 'orden', true );
			$ob = (int) get_term_meta( $b->term_id, 'orden', true );
			return $oa === $ob ? strcmp( $a->name, $b->name ) : $oa <=> $ob;
		}
	);
	return $terminos;
}

/**
 * Color de una categoría (campo o, por defecto, el de la paleta).
 *
 * @param WP_Term $termino Término.
 * @return string
 */
function msc_color_categoria( $termino ) {
	$color = get_term_meta( $termino->term_id, 'color_categoria', true );
	if ( $color ) {
		return $color;
	}
	$defecto = array(
		'libros'      => '#da2027',
		'documentos'  => '#1b5f7a',
		'fotografias' => '#1a8ca0',
		'judaica'     => '#cc942b',
		'arte'        => '#c01f62',
	);
	return $defecto[ $termino->slug ] ?? '#24335c';
}

/**
 * Años disponibles (para filtros) de un campo de fecha.
 *
 * @param string $tipo  Tipo de contenido.
 * @param string $campo Campo de fecha.
 * @param bool   $pasados Solo fechas anteriores a hoy.
 * @return int[]
 */
function msc_anios_disponibles( $tipo, $campo, $pasados = true ) {
	return msc_cache(
		'anios_' . $tipo . '_' . $campo,
		static function () use ( $tipo, $campo, $pasados ) {
			global $wpdb;
			$hoy = 'fecha_hora' === $campo ? msc_inicio_hoy() : msc_hoy();
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$filas = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT DISTINCT SUBSTRING(pm.meta_value, 1, 4) FROM {$wpdb->postmeta} pm INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE p.post_type = %s AND p.post_status = 'publish' AND pm.meta_key = %s AND pm.meta_value <> '' AND ( %d = 0 OR pm.meta_value < %s ) ORDER BY 1 DESC",
					$tipo,
					$campo,
					$pasados ? 1 : 0,
					$hoy
				)
			);
			return array_values( array_filter( array_map( 'intval', $filas ) ) );
		}
	);
}

/**
 * Ajusta las consultas principales de los listados: tamaño de página, orden y filtros.
 *
 * @param WP_Query $q Consulta.
 */
function msc_ajustar_listados( $q ) {
	if ( is_admin() || ! $q->is_main_query() ) {
		return;
	}
	$busqueda = trim( (string) $q->get( 'msc_q' ) );
	$meta     = array();
	$tax      = array();

	// Colecciones: categoría o "todas".
	if ( $q->is_tax( 'categoria_coleccion' ) || ( $q->is_post_type_archive( 'msc_coleccion' ) && 'todas' === $q->get( 'msc_archivo' ) ) ) {
		$q->set( 'post_type', 'msc_coleccion' );
		$q->set( 'posts_per_page', 12 );
		$forma = (string) $q->get( 'msc_forma' );
		if ( '' !== $forma ) {
			$meta[] = array(
				'key'   => 'forma_adquisicion',
				'value' => $forma,
			);
		}
		switch ( $q->get( 'msc_orden' ) ) {
			case 'antiguas':
				$q->set( 'orderby', 'date' );
				$q->set( 'order', 'ASC' );
				break;
			case 'titulo':
				$q->set( 'orderby', 'title' );
				$q->set( 'order', 'ASC' );
				break;
			default:
				$q->set( 'orderby', 'date' );
				$q->set( 'order', 'DESC' );
		}
	} elseif ( $q->is_post_type_archive( 'msc_exposicion' ) ) {
		// Exposiciones pasadas.
		$q->set( 'posts_per_page', 8 );
		$q->set( 'meta_key', 'fecha_cierre' );
		$q->set( 'orderby', 'meta_value' );
		$q->set( 'order', 'DESC' );
		$meta[] = msc_condicion_pasada();
		$anio   = absint( $q->get( 'msc_anio' ) );
		if ( $anio ) {
			$meta[] = array(
				'key'     => 'fecha_cierre',
				'value'   => array( $anio . '0101', $anio . '1231' ),
				'compare' => 'BETWEEN',
			);
		}
	} elseif ( $q->is_post_type_archive( 'msc_evento' ) ) {
		// Lista de eventos pasados (los próximos se muestran aparte, sin paginar).
		$q->set( 'posts_per_page', 20 );
		$q->set( 'meta_key', 'fecha_hora' );
		$q->set( 'orderby', 'meta_value' );
		$q->set( 'order', 'DESC' );
		$meta[] = array(
			'key'     => 'fecha_hora',
			'value'   => msc_inicio_hoy(),
			'compare' => '<',
		);
		$anio   = absint( $q->get( 'msc_anio' ) );
		if ( $anio ) {
			$meta[] = array(
				'key'     => 'fecha_hora',
				'value'   => array( $anio . '-01-01 00:00:00', $anio . '-12-31 23:59:59' ),
				'compare' => 'BETWEEN',
			);
		}
		$tipo = sanitize_title( (string) $q->get( 'msc_tipo' ) );
		if ( $tipo ) {
			$tax[] = array(
				'taxonomy' => 'tipo_evento',
				'field'    => 'slug',
				'terms'    => $tipo,
			);
		}
	} elseif ( $q->is_post_type_archive( 'msc_publicacion' ) ) {
		if ( 'calendarios' === $q->get( 'msc_archivo' ) ) {
			// La plantilla usa msc_calendario_vigente() y msc_calendarios_anteriores(); aquí solo limitamos.
			$q->set( 'posts_per_page', 1 );
			$q->set( 'no_found_rows', true );
		} else {
			$q->set( 'posts_per_page', 8 );
			$tax[] = msc_condicion_no_calendario();
			$q->set( 'meta_key', '' );
			$q->set( 'orderby', array( 'date' => 'DESC' ) );
		}
	} elseif ( $q->is_search() ) {
		$q->set( 'post_type', array( 'msc_coleccion', 'msc_exposicion', 'msc_evento', 'msc_publicacion', 'page' ) );
		$q->set( 'posts_per_page', 60 );
		$q->set( 'msc_buscar_meta', true );
	} else {
		return;
	}

	if ( '' !== $busqueda ) {
		$q->set( 's', $busqueda );
		$q->set( 'msc_buscar_meta', true );
	}
	if ( $meta ) {
		$q->set( 'meta_query', array_merge( array( 'relation' => 'AND' ), $meta ) );
	}
	if ( $tax ) {
		$existente = (array) $q->get( 'tax_query' );
		$q->set( 'tax_query', array_merge( $existente, $tax ) );
	}
}
add_action( 'pre_get_posts', 'msc_ajustar_listados', 20 );
