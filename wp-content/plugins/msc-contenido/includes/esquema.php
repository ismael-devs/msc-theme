<?php
/**
 * Datos estructurados JSON-LD: Museum, ExhibitionEvent, Event, Book, VisualArtwork, BreadcrumbList.
 * Convive con Yoast / Rank Math: si uno está activo, no se emite BreadcrumbList (ya lo generan ellos).
 *
 * @package msc-contenido
 */

defined( 'ABSPATH' ) || exit;

/**
 * Indica si hay un plugin SEO que ya genera metadatos y migas.
 *
 * @return bool
 */
function msc_hay_plugin_seo() {
	return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || class_exists( 'RankMath' );
}

/**
 * URL absoluta de una imagen (tamaño grande).
 *
 * @param int $id Adjunto.
 * @return string|null
 */
function msc_url_imagen( $id ) {
	if ( ! $id ) {
		return null;
	}
	$url = wp_get_attachment_image_url( $id, 'large' );
	return $url ? $url : null;
}

/**
 * Nodo Museum (reutilizado como organizador y ubicación).
 *
 * @param bool $completo Si incluye horario, teléfonos, etc.
 * @return array
 */
function msc_esquema_museo( $completo = true ) {
	$nodo      = array(
		'@type' => 'Museum',
		'@id'   => home_url( '/#museo' ),
		'name'  => 'Museo Sefardí de Caracas "Morris E. Curiel"',
		'url'   => home_url( '/' ),
	);
	$direccion = msc_opcion( 'direccion' );
	if ( $direccion ) {
		$nodo['address'] = array(
			'@type'           => 'PostalAddress',
			'streetAddress'   => wp_strip_all_tags( $direccion ),
			'addressLocality' => 'Caracas',
			'addressCountry'  => 'VE',
		);
	}
	if ( ! $completo ) {
		return $nodo;
	}
	$logo = get_theme_mod( 'custom_logo' );
	if ( $logo ) {
		$nodo['logo'] = msc_url_imagen( $logo );
	}
	$telefono = msc_opcion( 'telefono' );
	if ( $telefono ) {
		$nodo['telephone'] = $telefono;
	}
	$correo = msc_opcion( 'correo' );
	if ( $correo ) {
		$nodo['email'] = $correo;
	}
	$lat = msc_opcion( 'latitud' );
	$lng = msc_opcion( 'longitud' );
	if ( $lat && $lng ) {
		$nodo['geo'] = array(
			'@type'     => 'GeoCoordinates',
			'latitude'  => (float) $lat,
			'longitude' => (float) $lng,
		);
	}
	$mismo = array();
	$ig    = msc_opcion( 'instagram' );
	if ( $ig ) {
		$mismo[] = 'https://www.instagram.com/' . rawurlencode( ltrim( $ig, '@' ) ) . '/';
	}
	if ( $mismo ) {
		$nodo['sameAs'] = $mismo;
	}
	$horario = msc_opcion( 'horario_estructurado' );
	if ( is_array( $horario ) ) {
		$especificaciones = array();
		foreach ( $horario as $fila ) {
			if ( empty( $fila['dias'] ) || empty( $fila['abre'] ) || empty( $fila['cierra'] ) ) {
				continue;
			}
			$especificaciones[] = array(
				'@type'     => 'OpeningHoursSpecification',
				'dayOfWeek' => array_values( (array) $fila['dias'] ),
				'opens'     => $fila['abre'],
				'closes'    => $fila['cierra'],
			);
		}
		if ( $especificaciones ) {
			$nodo['openingHoursSpecification'] = $especificaciones;
		}
	}
	$nodo['foundingDate'] = '1999';
	$nodo['founder']      = array(
		'@type' => 'Person',
		'name'  => 'Morris E. Curiel',
	);
	return $nodo;
}

/**
 * Nodo de la entrada actual según su tipo.
 *
 * @param int $id ID.
 * @return array|null
 */
function msc_esquema_entrada( $id ) {
	$tipo   = get_post_type( $id );
	$nodo   = array(
		'@id'  => get_permalink( $id ) . '#contenido',
		'name' => wp_strip_all_tags( get_the_title( $id ) ),
		'url'  => get_permalink( $id ),
	);
	$imagen = msc_url_imagen( msc_imagen_principal( $id ) );
	if ( $imagen ) {
		$nodo['image'] = $imagen;
	}
	$resumen = msc_resumen( $id, 40 );
	if ( $resumen ) {
		$nodo['description'] = $resumen;
	}
	$idioma             = msc_idioma_entrada( $id );
	$nodo['inLanguage'] = $idioma;

	switch ( $tipo ) {
		case 'msc_exposicion':
			$nodo['@type'] = 'ExhibitionEvent';
			$inicio        = msc_fecha_objeto( get_post_meta( $id, 'fecha_inicio', true ) );
			$cierre        = msc_fecha_objeto( get_post_meta( $id, 'fecha_cierre', true ) );
			if ( $inicio ) {
				$nodo['startDate'] = $inicio->format( 'Y-m-d' );
			}
			if ( $cierre ) {
				$nodo['endDate'] = $cierre->format( 'Y-m-d' );
			}
			$nodo['eventStatus']         = 'https://schema.org/EventScheduled';
			$nodo['eventAttendanceMode'] = 'https://schema.org/OfflineEventAttendanceMode';
			$nodo['location']            = msc_esquema_museo( false );
			$nodo['organizer']           = array( '@id' => home_url( '/#museo' ) );
			return $nodo;

		case 'msc_evento':
			$nodo['@type'] = 'Event';
			$fecha         = msc_fecha_objeto( get_post_meta( $id, 'fecha_hora', true ) );
			if ( $fecha ) {
				$nodo['startDate'] = $fecha->format( 'c' );
			}
			$nodo['eventStatus']         = 'https://schema.org/EventScheduled';
			$nodo['eventAttendanceMode'] = 'https://schema.org/OfflineEventAttendanceMode';
			$lugar                       = msc_esquema_museo( false );
			$texto_lugar                 = (string) get_post_meta( $id, 'lugar', true );
			if ( $texto_lugar ) {
				$lugar = array(
					'@type'   => 'Place',
					'name'    => $texto_lugar,
					'address' => $lugar['address'] ?? 'Caracas, Venezuela',
				);
			}
			$nodo['location']  = $lugar;
			$nodo['organizer'] = array( '@id' => home_url( '/#museo' ) );
			$ponentes          = msc_campo( 'ponentes', $id );
			if ( is_array( $ponentes ) ) {
				$personas = array();
				foreach ( $ponentes as $p ) {
					if ( ! empty( $p['nombre'] ) ) {
						$personas[] = array(
							'@type' => 'Person',
							'name'  => $p['nombre'],
						);
					}
				}
				if ( $personas ) {
					$nodo['performer'] = $personas;
				}
			}
			return $nodo;

		case 'msc_publicacion':
			$es_calendario     = 'calendario' === get_post_meta( $id, 'tipo', true );
			$nodo['@type']     = $es_calendario ? 'CreativeWork' : 'Book';
			$nodo['publisher'] = array( '@id' => home_url( '/#museo' ) );
			$anio              = get_post_meta( $id, $es_calendario ? 'anio_civil' : 'anio', true );
			if ( $anio ) {
				$nodo['datePublished'] = (string) $anio;
			}
			$paginas = get_post_meta( $id, 'paginas', true );
			if ( $paginas && ! $es_calendario ) {
				$nodo['numberOfPages'] = (int) $paginas;
			}
			$sub = get_post_meta( $id, 'subtitulo', true );
			if ( $sub ) {
				$nodo['alternativeHeadline'] = $sub;
			}
			return $nodo;

		case 'msc_coleccion':
			$cat           = msc_categoria_principal( $id );
			$es_arte       = $cat && in_array( $cat->slug, array( 'arte', 'fotografias' ), true );
			$nodo['@type'] = $es_arte ? 'VisualArtwork' : 'CreativeWork';
			$autor         = get_post_meta( $id, 'autor_procedencia', true );
			if ( $autor ) {
				$nodo['creator'] = array(
					'@type' => 'Person',
					'name'  => $autor,
				);
			}
			$epoca = get_post_meta( $id, 'epoca', true );
			if ( $epoca ) {
				$nodo['dateCreated'] = $epoca;
			}
			$material = get_post_meta( $id, 'material_tecnica', true );
			if ( $material ) {
				$nodo[ $es_arte ? 'artMedium' : 'material' ] = $material;
			}
			$inventario = get_post_meta( $id, 'numero_inventario', true );
			if ( $inventario ) {
				$nodo['identifier'] = $inventario;
			}
			if ( $cat ) {
				$nodo['genre'] = $cat->name;
			}
			$nodo['isPartOf'] = array( '@id' => home_url( '/#museo' ) );
			return $nodo;
	}
	return null;
}

/**
 * Nodo BreadcrumbList a partir de msc_migas().
 *
 * @return array|null
 */
function msc_esquema_migas() {
	$migas = msc_migas();
	if ( count( $migas ) < 2 ) {
		return null;
	}
	$elementos = array();
	$pos       = 0;
	foreach ( $migas as $miga ) {
		++$pos;
		$elemento         = array(
			'@type'    => 'ListItem',
			'position' => $pos,
			'name'     => $miga['titulo'],
		);
		$url              = $miga['url'] ? $miga['url'] : msc_url_actual();
		$elemento['item'] = $url;
		$elementos[]      = $elemento;
	}
	return array(
		'@type'           => 'BreadcrumbList',
		'itemListElement' => $elementos,
	);
}

/**
 * URL canónica de la vista actual (sin parámetros de filtro).
 *
 * @return string
 */
function msc_url_actual() {
	if ( is_singular() ) {
		return get_permalink( get_queried_object_id() );
	}
	if ( is_tax() || is_category() || is_tag() ) {
		$t = get_term_link( get_queried_object() );
		return is_wp_error( $t ) ? home_url( '/' ) : $t;
	}
	if ( is_post_type_archive() ) {
		$tipo    = get_query_var( 'post_type' );
		$tipo    = is_array( $tipo ) ? reset( $tipo ) : $tipo;
		$archivo = get_query_var( 'msc_archivo' );
		if ( 'pasadas' === $archivo ) {
			return msc_url( 'exposiciones/pasadas' );
		}
		if ( 'calendarios' === $archivo ) {
			return msc_url( 'publicaciones/calendarios' );
		}
		if ( 'todas' === $archivo ) {
			return msc_url( 'colecciones/todas' );
		}
		return get_post_type_archive_link( $tipo );
	}
	return msc_url_base();
}

/**
 * Imprime el JSON-LD en el <head>.
 */
function msc_imprimir_esquema() {
	if ( is_admin() || is_feed() ) {
		return;
	}
	$grafo = array();
	if ( is_front_page() || is_page_template( 'msc-visitanos' ) ) {
		$grafo[] = msc_esquema_museo( true );
	}
	if ( is_singular( array( 'msc_coleccion', 'msc_exposicion', 'msc_evento', 'msc_publicacion' ) ) ) {
		$nodo = msc_esquema_entrada( get_queried_object_id() );
		if ( $nodo ) {
			$grafo[] = $nodo;
		}
	}
	if ( ! msc_hay_plugin_seo() || apply_filters( 'msc_forzar_migas_esquema', false ) ) {
		$migas = msc_esquema_migas();
		if ( $migas ) {
			$grafo[] = $migas;
		}
	}
	$grafo = apply_filters( 'msc_esquema_grafo', $grafo );
	if ( ! $grafo ) {
		return;
	}
	$datos = array(
		'@context' => 'https://schema.org',
		'@graph'   => array_values( $grafo ),
	);
	echo '<script type="application/ld+json">' . wp_json_encode( $datos, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . "</script>\n";
}
add_action( 'wp_head', 'msc_imprimir_esquema', 20 );
