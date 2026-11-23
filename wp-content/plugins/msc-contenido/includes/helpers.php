<?php
/**
 * Funciones de ayuda para leer campos y dar formato.
 *
 * @package msc-contenido
 */

defined( 'ABSPATH' ) || exit;

/**
 * Indica si Secure Custom Fields (o ACF) está activo.
 *
 * @return bool
 */
function msc_scf_activo() {
	return function_exists( 'get_field' ) && function_exists( 'acf_add_local_field_group' );
}

/**
 * Lee un campo personalizado de una entrada. Funciona aunque SCF no esté activo.
 *
 * @param string   $nombre  Nombre del campo.
 * @param int|null $post_id ID de la entrada (por defecto la actual).
 * @param bool     $formato Si se aplica el formato de SCF.
 * @return mixed
 */
function msc_campo( $nombre, $post_id = null, $formato = true ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	if ( ! $post_id ) {
		return null;
	}
	if ( msc_scf_activo() ) {
		return get_field( $nombre, $post_id, $formato );
	}
	return get_post_meta( $post_id, $nombre, true );
}

/**
 * Lee un campo de la página de opciones "Museo".
 * Si el idioma actual no es el español y existe la versión "_en" con valor, la devuelve.
 *
 * @param string $nombre Nombre del campo.
 * @return mixed
 */
function msc_opcion( $nombre ) {
	static $cache = array();
	$idioma       = msc_idioma_actual();
	$clave        = $nombre . '|' . $idioma;
	if ( array_key_exists( $clave, $cache ) ) {
		return $cache[ $clave ];
	}
	$valor = null;
	if ( msc_scf_activo() ) {
		if ( 'es' !== $idioma ) {
			$traducido = get_field( $nombre . '_' . $idioma, 'option' );
			if ( ! msc_vacio( $traducido ) ) {
				$valor = $traducido;
			}
		}
		if ( null === $valor ) {
			$valor = get_field( $nombre, 'option' );
		}
	} else {
		$valor = get_option( 'options_' . $nombre );
	}
	$cache[ $clave ] = $valor;
	return $valor;
}

/**
 * Comprueba si un valor de campo está vacío (cadenas en blanco, arrays vacíos, null).
 *
 * @param mixed $valor Valor.
 * @return bool
 */
function msc_vacio( $valor ) {
	if ( is_string( $valor ) ) {
		return '' === trim( wp_strip_all_tags( $valor ) ) && false === stripos( $valor, '<img' ) && false === stripos( $valor, '<iframe' );
	}
	if ( is_array( $valor ) ) {
		return array() === array_filter(
			$valor,
			static function ( $v ) {
				return ! msc_vacio( $v );
			}
		);
	}
	return null === $valor || false === $valor || 0 === $valor;
}

/**
 * Marcador "[por confirmar]" visible solo para quien puede editar.
 * Al público no se le muestra nada.
 *
 * @param string $texto Texto del marcador.
 * @return string HTML.
 */
function msc_por_confirmar( $texto = '' ) {
	if ( ! current_user_can( 'edit_posts' ) ) {
		return '';
	}
	$texto = $texto ? $texto : __( '[por confirmar]', 'msc' );
	return '<span class="msc-por-confirmar" title="' . esc_attr__( 'Solo lo ven los editores', 'msc' ) . '">' . esc_html( $texto ) . '</span>';
}

/**
 * Devuelve la fecha actual en la zona horaria de WordPress (America/Caracas).
 *
 * @return DateTimeImmutable
 */
function msc_ahora() {
	return current_datetime();
}

/**
 * Convierte una fecha guardada (Ymd o Y-m-d H:i:s) a DateTimeImmutable en la zona del sitio.
 *
 * @param string $valor Fecha guardada.
 * @return DateTimeImmutable|null
 */
function msc_fecha_objeto( $valor ) {
	if ( empty( $valor ) ) {
		return null;
	}
	$zona  = wp_timezone();
	$valor = (string) $valor;
	if ( preg_match( '/^\d{8}$/', $valor ) ) {
		$fecha = DateTimeImmutable::createFromFormat( '!Ymd', $valor, $zona );
	} else {
		$fecha = date_create_immutable( $valor, $zona );
	}
	return $fecha ? $fecha : null;
}

/**
 * Da formato a una fecha guardada.
 *
 * @param string $valor   Fecha guardada.
 * @param string $formato Formato de date_i18n.
 * @return string
 */
function msc_formatear_fecha( $valor, $formato = 'j \d\e F \d\e Y' ) {
	$fecha = msc_fecha_objeto( $valor );
	if ( ! $fecha ) {
		return '';
	}
	return wp_date( $formato, $fecha->getTimestamp() );
}

/**
 * Rango de fechas legible: "3 de marzo – 30 de junio de 2026".
 *
 * @param string $inicio Fecha Ymd.
 * @param string $cierre Fecha Ymd.
 * @return string
 */
function msc_rango_fechas( $inicio, $cierre ) {
	$a = msc_fecha_objeto( $inicio );
	$b = msc_fecha_objeto( $cierre );
	if ( $a && $b ) {
		$mismo_anio = $a->format( 'Y' ) === $b->format( 'Y' );
		$desde      = wp_date( $mismo_anio ? 'j \d\e F' : 'j \d\e F \d\e Y', $a->getTimestamp() );
		return $desde . ' – ' . wp_date( 'j \d\e F \d\e Y', $b->getTimestamp() );
	}
	if ( $a ) {
		/* translators: %s: fecha de inicio. */
		return sprintf( __( 'Desde el %s', 'msc' ), wp_date( 'j \d\e F \d\e Y', $a->getTimestamp() ) );
	}
	return '';
}

/**
 * Cuenta las palabras de un texto (incluye acentos).
 *
 * @param string $texto Texto plano.
 * @return int
 */
function msc_contar_palabras( $texto ) {
	$texto = trim( wp_strip_all_tags( (string) $texto ) );
	if ( '' === $texto ) {
		return 0;
	}
	return count( preg_split( '/\s+/u', $texto ) );
}

/**
 * Devuelve los párrafos de un HTML que superan el máximo de palabras.
 *
 * @param string $html   HTML.
 * @param int    $maximo Máximo de palabras por párrafo.
 * @return int[] Número de palabras de cada párrafo largo.
 */
function msc_parrafos_largos( $html, $maximo = 80 ) {
	$largos = array();
	$html   = wpautop( (string) $html );
	if ( preg_match_all( '#<p[^>]*>(.*?)</p>#is', $html, $m ) ) {
		foreach ( $m[1] as $parrafo ) {
			$n = msc_contar_palabras( $parrafo );
			if ( $n > $maximo ) {
				$largos[] = $n;
			}
		}
	}
	return $largos;
}

/**
 * Datos de una imagen (para tarjetas y JSON-LD).
 *
 * @param int|array|null $imagen ID o array de SCF.
 * @return int ID de adjunto o 0.
 */
function msc_id_imagen( $imagen ) {
	if ( is_array( $imagen ) && isset( $imagen['ID'] ) ) {
		return (int) $imagen['ID'];
	}
	if ( is_array( $imagen ) && isset( $imagen['id'] ) ) {
		return (int) $imagen['id'];
	}
	return is_numeric( $imagen ) ? (int) $imagen : 0;
}

/**
 * Imagen principal de cualquier tipo de contenido del museo.
 *
 * @param int $post_id ID.
 * @return int ID de adjunto o 0.
 */
function msc_imagen_principal( $post_id ) {
	$tipo = get_post_type( $post_id );
	$id   = 0;
	switch ( $tipo ) {
		case 'msc_coleccion':
			$galeria = msc_campo( 'galeria', $post_id, false );
			if ( is_array( $galeria ) && $galeria ) {
				$id = msc_id_imagen( reset( $galeria ) );
			}
			break;
		case 'msc_exposicion':
			$id = msc_id_imagen( msc_campo( 'imagen_principal', $post_id, false ) );
			break;
		case 'msc_evento':
			$id = msc_id_imagen( msc_campo( 'imagen', $post_id, false ) );
			break;
		case 'msc_publicacion':
			$id = msc_id_imagen( msc_campo( 'portada', $post_id, false ) );
			break;
	}
	if ( ! $id ) {
		$id = (int) get_post_thumbnail_id( $post_id );
	}
	return $id;
}

/**
 * Texto plano breve para descripciones (metadatos, JSON-LD, tarjetas).
 *
 * @param int $post_id ID.
 * @param int $palabras Palabras máximas.
 * @return string
 */
function msc_resumen( $post_id, $palabras = 30 ) {
	$extracto = get_post_field( 'post_excerpt', $post_id );
	if ( $extracto ) {
		return wp_trim_words( $extracto, $palabras );
	}
	$campos = array(
		'msc_coleccion'   => 'descripcion',
		'msc_exposicion'  => 'texto',
		'msc_evento'      => 'descripcion',
		'msc_publicacion' => 'resena',
	);
	$tipo   = get_post_type( $post_id );
	if ( isset( $campos[ $tipo ] ) ) {
		$texto = msc_campo( $campos[ $tipo ], $post_id, false );
		if ( $texto ) {
			return wp_trim_words( wp_strip_all_tags( $texto ), $palabras );
		}
	}
	return wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', $post_id ) ), $palabras );
}
