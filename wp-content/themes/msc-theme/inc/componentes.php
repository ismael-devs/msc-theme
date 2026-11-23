<?php
/**
 * Componentes reutilizables (parciales en PHP): imagen, migas, cabecera, tarjetas,
 * píldoras, paginación, bandas, calendario, marquee, multimedia.
 *
 * @package msc-theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Imagen responsiva con srcset/sizes. Sin imagen devuelve un bloque neutro (para no romper la cuadrícula).
 *
 * @param int|array|null $imagen ID o array de SCF.
 * @param array          $args   sizes, loading, prioridad, clase, tamano, vacia.
 * @return string
 */
function msc_img( $imagen, $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'sizes'     => '(max-width: 820px) 100vw, 50vw',
			'loading'   => 'lazy',
			'prioridad' => false,
			'clase'     => '',
			'tamano'    => 'msc-1600',
			'vacia'     => true,
		)
	);
	$id   = function_exists( 'msc_id_imagen' ) ? msc_id_imagen( $imagen ) : (int) $imagen;
	if ( ! $id ) {
		return $args['vacia'] ? '<span class="msc-media msc-media--vacia ' . esc_attr( $args['clase'] ) . '" aria-hidden="true"></span>' : '';
	}
	$atributos = array(
		'class'    => trim( 'msc-media ' . $args['clase'] ),
		'sizes'    => $args['sizes'],
		'loading'  => $args['prioridad'] ? 'eager' : $args['loading'],
		'decoding' => $args['prioridad'] ? 'sync' : 'async',
	);
	if ( $args['prioridad'] ) {
		$atributos['fetchpriority'] = 'high';
	}
	return wp_get_attachment_image( $id, $args['tamano'], false, $atributos );
}

/**
 * Imprime las migas de pan.
 */
function msc_tema_migas() {
	if ( ! function_exists( 'msc_migas' ) ) {
		return;
	}
	$migas = msc_migas();
	if ( count( $migas ) < 2 ) {
		return;
	}
	echo '<nav class="msc-migas" aria-label="' . esc_attr__( 'Migas de pan', 'msc' ) . '"><ol>';
	$ultima = count( $migas ) - 1;
	foreach ( $migas as $i => $miga ) {
		if ( $i === $ultima || ! $miga['url'] ) {
			echo '<li><span aria-current="page">' . esc_html( $miga['titulo'] ) . '</span></li>';
		} else {
			echo '<li><a href="' . esc_url( $miga['url'] ) . '">' . esc_html( $miga['titulo'] ) . '</a></li>';
		}
	}
	echo '</ol></nav>';
}

/**
 * Cabecera de página: migas, etiquetas opcionales, h1 e introducción.
 *
 * @param string $titulo Título.
 * @param string $intro  Introducción (texto plano).
 * @param string $antes  HTML antes del título (etiquetas, píldoras).
 * @param string $despues HTML después de la introducción.
 */
function msc_tema_cabecera( $titulo, $intro = '', $antes = '', $despues = '' ) {
	echo '<header class="msc-cabecera msc-contenedor">';
	msc_tema_migas();
	if ( $antes ) {
		echo '<div class="msc-cabecera__antes">' . $antes . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	echo '<h1 class="msc-cabecera__titulo">' . esc_html( $titulo ) . '</h1>';
	if ( $intro ) {
		echo '<p class="msc-cabecera__intro">' . esc_html( $intro ) . '</p>';
	}
	if ( $despues ) {
		echo $despues; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	echo '</header>';
}

/**
 * Texto enriquecido de un campo (párrafos, enlaces). Vacío → nada.
 *
 * @param string $html  HTML.
 * @param string $clase Clase.
 * @return string
 */
function msc_tema_texto( $html, $clase = '' ) {
	if ( ! $html || ( function_exists( 'msc_vacio' ) && msc_vacio( $html ) ) ) {
		return '';
	}
	return '<div class="msc-texto ' . esc_attr( $clase ) . '">' . wp_kses_post( wpautop( $html ) ) . '</div>';
}

/**
 * Píldora de estado.
 *
 * @param string $estado Estado.
 * @return string
 */
function msc_tema_pildora( $estado ) {
	if ( ! function_exists( 'msc_etiqueta_estado' ) ) {
		return '';
	}
	return '<span class="msc-pildora ' . esc_attr( msc_clase_estado( $estado ) ) . '">' . esc_html( msc_etiqueta_estado( $estado ) ) . '</span>';
}

/**
 * Etiqueta en versalitas (categoría, tipo).
 *
 * @param string $texto Texto.
 * @param string $clase Clase extra.
 * @return string
 */
function msc_tema_etiqueta( $texto, $clase = '' ) {
	return $texto ? '<span class="msc-etiqueta ' . esc_attr( $clase ) . '">' . esc_html( $texto ) . '</span>' : '';
}

/**
 * Tarjeta genérica (pieza, exposición, publicación, categoría).
 *
 * @param array $a titulo, url, imagen, meta, antes, nivel, sizes, clase, ratio.
 * @return string
 */
function msc_tema_tarjeta( $a ) {
	$a     = wp_parse_args(
		$a,
		array(
			'titulo' => '',
			'url'    => '',
			'imagen' => 0,
			'meta'   => '',
			'antes'  => '',
			'nivel'  => 3,
			'sizes'  => '(max-width: 820px) 50vw, 25vw',
			'clase'  => '',
			'ratio'  => '',
			'boton'  => '',
		)
	);
	$h     = 'h' . (int) $a['nivel'];
	$html  = '<article class="msc-tarjeta ' . esc_attr( $a['clase'] ) . '">';
	$html .= '<div class="msc-tarjeta__media' . ( $a['ratio'] ? ' msc-ratio-' . esc_attr( $a['ratio'] ) : '' ) . '">' . msc_img(
		$a['imagen'],
		array(
			'sizes'  => $a['sizes'],
			'tamano' => 'msc-800',
		)
	) . '</div>';
	$html .= '<div class="msc-tarjeta__cuerpo">';
	if ( $a['antes'] ) {
		$html .= '<div class="msc-tarjeta__antes">' . $a['antes'] . '</div>';
	}
	$titulo = esc_html( $a['titulo'] );
	if ( $a['url'] ) {
		$titulo = '<a class="msc-tarjeta__enlace" href="' . esc_url( $a['url'] ) . '">' . $titulo . '</a>';
	}
	$html .= '<' . $h . ' class="msc-tarjeta__titulo">' . $titulo . '</' . $h . '>';
	if ( $a['meta'] ) {
		$html .= '<p class="msc-tarjeta__meta">' . wp_kses_post( $a['meta'] ) . '</p>';
	}
	if ( $a['boton'] ) {
		$html .= $a['boton'];
	}
	$html .= '</div></article>';
	return $html;
}

/**
 * Tarjeta de una entrada del museo según su tipo.
 *
 * @param int   $id   ID.
 * @param array $args Ajustes de msc_tema_tarjeta.
 * @return string
 */
function msc_tema_tarjeta_entrada( $id, $args = array() ) {
	$tipo  = get_post_type( $id );
	$meta  = '';
	$antes = '';
	switch ( $tipo ) {
		case 'msc_coleccion':
			$meta = (string) msc_campo( 'forma_adquisicion', $id );
			if ( ! empty( $args['con_categoria'] ) ) {
				$cat   = msc_categoria_principal( $id );
				$antes = $cat ? msc_tema_etiqueta( $cat->name ) : '';
			}
			break;
		case 'msc_exposicion':
			$cierre = msc_fecha_objeto( get_post_meta( $id, 'fecha_cierre', true ) );
			$inicio = msc_fecha_objeto( get_post_meta( $id, 'fecha_inicio', true ) );
			$f      = $cierre ? $cierre : $inicio;
			$meta   = $f ? $f->format( 'Y' ) : '';
			break;
		case 'msc_publicacion':
			$meta = 'calendario' === get_post_meta( $id, 'tipo', true ) ? (string) msc_campo( 'anio_civil', $id ) : (string) msc_campo( 'anio', $id );
			break;
		case 'msc_evento':
			$meta = msc_formatear_fecha( get_post_meta( $id, 'fecha_hora', true ), 'j \d\e F \d\e Y' );
			break;
	}
	unset( $args['con_categoria'] );
	return msc_tema_tarjeta(
		array_merge(
			array(
				'titulo' => get_the_title( $id ),
				'url'    => get_permalink( $id ),
				'imagen' => msc_imagen_principal( $id ),
				'meta'   => esc_html( $meta ),
				'antes'  => $antes,
			),
			$args
		)
	);
}

/**
 * Cuadrícula de tarjetas.
 *
 * @param int[]  $ids      IDs.
 * @param string $columnas Clase de columnas (c4, c3, c2).
 * @param array  $args     Ajustes de tarjeta.
 * @return string
 */
function msc_tema_rejilla( $ids, $columnas = 'c4', $args = array() ) {
	if ( ! $ids ) {
		return '';
	}
	$html = '<ul class="msc-rejilla msc-rejilla--' . esc_attr( $columnas ) . '" role="list">';
	foreach ( $ids as $id ) {
		$html .= '<li>' . msc_tema_tarjeta_entrada( $id, $args ) . '</li>';
	}
	return $html . '</ul>';
}

/**
 * Paginación accesible (44 px) que conserva los filtros.
 *
 * @param WP_Query|null $consulta Consulta.
 */
function msc_tema_paginacion( $consulta = null ) {
	global $wp_query;
	$consulta = $consulta ? $consulta : $wp_query;
	$total    = (int) $consulta->max_num_pages;
	if ( $total < 2 ) {
		return;
	}
	$actual  = max( 1, (int) get_query_var( 'paged' ) );
	$filtros = array();
	foreach ( array( 'msc_q', 'msc_forma', 'msc_orden', 'msc_anio', 'msc_tipo' ) as $var ) {
		$v = get_query_var( $var );
		if ( '' !== (string) $v ) {
			$filtros[ $var ] = $v;
		}
	}
	$enlaces = paginate_links(
		array(
			'total'              => $total,
			'current'            => $actual,
			'type'               => 'array',
			'prev_text'          => msc_icono( 'izquierda', 16 ) . '<span class="screen-reader-text">' . esc_html__( 'Página anterior', 'msc' ) . '</span>',
			'next_text'          => msc_icono( 'derecha', 16 ) . '<span class="screen-reader-text">' . esc_html__( 'Página siguiente', 'msc' ) . '</span>',
			'before_page_number' => '<span class="screen-reader-text">' . esc_html__( 'Página', 'msc' ) . ' </span>',
			'add_args'           => $filtros,
			'mid_size'           => 1,
		)
	);
	if ( ! $enlaces ) {
		return;
	}
	echo '<nav class="msc-paginacion" aria-label="' . esc_attr__( 'Paginación', 'msc' ) . '"><ul>';
	foreach ( $enlaces as $enlace ) {
		echo '<li>' . $enlace . '</li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- paginate_links() ya escapa sus URL.
	}
	echo '</ul></nav>';
}

/**
 * Botón/enlace.
 *
 * @param string $texto   Texto.
 * @param string $url     URL.
 * @param string $variante primario|secundario|dorado.
 * @param string $extra   Atributos extra.
 * @return string
 */
function msc_tema_boton( $texto, $url, $variante = 'primario', $extra = '' ) {
	if ( ! $texto || ! $url ) {
		return '';
	}
	return '<a class="msc-btn msc-btn--' . esc_attr( $variante ) . '" href="' . esc_url( $url ) . '"' . $extra . '>' . esc_html( $texto ) . '</a>';
}

/**
 * Banda oscura con título y botón dorado.
 *
 * @param string $titulo Título.
 * @param string $texto  Texto (opcional).
 * @param string $botones HTML de botones.
 * @param string $id      ID del encabezado.
 */
function msc_tema_banda( $titulo, $texto, $botones, $id = '' ) {
	$id = $id ? $id : 'banda-' . wp_unique_id();
	echo '<section class="msc-banda" aria-labelledby="' . esc_attr( $id ) . '"><div class="msc-contenedor msc-banda__interior">';
	echo '<div class="msc-banda__texto"><h2 id="' . esc_attr( $id ) . '">' . esc_html( $titulo ) . '</h2>';
	if ( $texto ) {
		echo '<p>' . esc_html( $texto ) . '</p>';
	}
	echo '</div><div class="msc-banda__botones">' . $botones . '</div></div></section>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Banda oscura "Colaborar con el museo".
 *
 * @param string $titulo Título.
 */
function msc_tema_banda_colaborar( $titulo ) {
	msc_tema_banda( $titulo, '', msc_tema_boton( __( 'Colaborar con el museo', 'msc' ), msc_url_pagina( 'msc-colaboraciones', 'colaboraciones' ), 'dorado' ) );
}

/**
 * Banda oscura del boletín.
 */
function msc_tema_banda_boletin() {
	if ( ! function_exists( 'msc_formulario' ) ) {
		return;
	}
	$id = 'boletin-' . wp_unique_id();
	echo '<section class="msc-banda msc-banda--boletin" aria-labelledby="' . esc_attr( $id ) . '"><div class="msc-contenedor">';
	echo '<h2 id="' . esc_attr( $id ) . '">' . esc_html__( 'Recibe nuestras novedades', 'msc' ) . '</h2>';
	$texto = msc_opcion( 'boletin_texto' );
	if ( $texto ) {
		echo '<p>' . esc_html( $texto ) . '</p>';
	}
	echo msc_formulario( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		'boletin',
		array(
			'variante' => 'oscuro',
			'id_html'  => $id . '-form',
		)
	);
	echo '</div></section>';
}

/**
 * Fila de la lista de eventos (con flecha solo si tiene ficha).
 *
 * @param int  $id     ID.
 * @param bool $fecha_larga Mostrar año.
 * @return string
 */
function msc_tema_fila_evento( $id, $fecha_larga = false ) {
	$ficha = msc_evento_tiene_ficha( $id );
	$tipos = get_the_terms( $id, 'tipo_evento' );
	$tipo  = $tipos && ! is_wp_error( $tipos ) ? $tipos[0]->name : '';
	$fecha = msc_formatear_fecha( get_post_meta( $id, 'fecha_hora', true ), $fecha_larga ? 'd/m/Y' : 'd/m' );
	$html  = '<li class="msc-fila-evento' . ( $ficha ? ' tiene-ficha' : '' ) . '" id="evento-' . esc_attr( get_post_field( 'post_name', $id ) ) . '">';
	$html .= '<span class="msc-fila-evento__fecha">' . esc_html( $fecha ) . '</span>';
	$html .= '<span class="msc-fila-evento__datos">';
	if ( $tipo ) {
		$html .= '<span class="msc-fila-evento__tipo">' . esc_html( $tipo ) . '</span>';
	}
	if ( $ficha ) {
		$html .= '<a class="msc-fila-evento__titulo" href="' . esc_url( get_permalink( $id ) ) . '">' . esc_html( get_the_title( $id ) ) . msc_icono( 'flecha', 16 ) . '</a>';
	} else {
		$html .= '<span class="msc-fila-evento__titulo">' . esc_html( get_the_title( $id ) ) . '</span>';
	}
	$html .= '</span></li>';
	return $html;
}

/**
 * Lista de datos (ficha técnica, datos de evento...). Solo filas con valor.
 *
 * @param array<string, string> $filas  Etiqueta => valor (HTML permitido).
 * @param string                $clase  Clase.
 * @return string
 */
function msc_tema_datos( $filas, $clase = '' ) {
	// Acepta etiqueta => valor o una lista de pares array( etiqueta, valor ).
	$pares = array();
	foreach ( $filas as $clave => $valor ) {
		$pares[] = is_array( $valor ) ? $valor : array( $clave, $valor );
	}
	$pares = array_filter(
		$pares,
		static function ( $p ) {
			return '' !== trim( wp_strip_all_tags( (string) $p[1] ) );
		}
	);
	if ( ! $pares ) {
		return '';
	}
	$html = '<dl class="msc-datos ' . esc_attr( $clase ) . '">';
	foreach ( $pares as $p ) {
		$html .= '<div class="msc-datos__fila"><dt>' . esc_html( $p[0] ) . '</dt><dd>' . wp_kses_post( $p[1] ) . '</dd></div>';
	}
	return $html . '</dl>';
}

/**
 * Créditos (rol + nombre).
 *
 * @param array|null $creditos Filas.
 * @return string
 */
function msc_tema_creditos( $creditos ) {
	if ( ! is_array( $creditos ) ) {
		return '';
	}
	$filas = array();
	foreach ( $creditos as $c ) {
		if ( ! empty( $c['nombre'] ) ) {
			$filas[] = array( $c['rol'] ?? '', esc_html( $c['nombre'] ) );
		}
	}
	return msc_tema_datos( $filas, 'msc-datos--creditos' );
}

/**
 * Video (oEmbed) con carga diferida.
 *
 * @param string $url    URL del video.
 * @param string $titulo Título accesible.
 * @return string
 */
function msc_tema_video( $url, $titulo ) {
	if ( ! $url ) {
		return '';
	}
	$html = wp_oembed_get( $url );
	if ( ! $html ) {
		return '<p><a href="' . esc_url( $url ) . '">' . esc_html__( 'Ver el video', 'msc' ) . '</a></p>';
	}
	$html = str_replace( '<iframe ', '<iframe loading="lazy" title="' . esc_attr( $titulo ) . '" ', preg_replace( '/\s(width|height|title)="[^"]*"/', '', $html ) );
	return '<div class="msc-video">' . $html . '</div>';
}

/**
 * Galería de fotos (enlazan a la imagen grande).
 *
 * @param int[]|null $ids IDs.
 * @return string
 */
function msc_tema_galeria( $ids ) {
	if ( ! is_array( $ids ) || ! $ids ) {
		return '';
	}
	$html = '<ul class="msc-galeria" role="list">';
	foreach ( $ids as $img ) {
		$id    = msc_id_imagen( $img );
		$html .= '<li><a href="' . esc_url( (string) wp_get_attachment_image_url( $id, 'msc-2400' ) ) . '">' . msc_img(
			$id,
			array(
				'sizes'  => '(max-width: 820px) 100vw, 33vw',
				'tamano' => 'msc-800',
			)
		) . '<span class="screen-reader-text">' . esc_html__( 'Ver la foto en grande', 'msc' ) . '</span></a></li>';
	}
	return $html . '</ul>';
}

/**
 * Bloque del calendario vigente.
 *
 * @param string $variante inicio|publicaciones|calendarios.
 */
function msc_tema_calendario( $variante = 'inicio' ) {
	$id = msc_calendario_vigente();
	if ( ! $id ) {
		return;
	}
	$pdf     = msc_id_imagen( msc_campo( 'pdf', $id, false ) );
	$pdf_url = $pdf ? wp_get_attachment_url( $pdf ) : '';
	$peso    = $pdf ? size_format( (int) filesize( get_attached_file( $pdf ) ) ) : '';
	$titulo  = msc_titulo_calendario( $id );
	$desc    = 'inicio' === $variante ? wp_strip_all_tags( (string) msc_campo( 'resena', $id ) ) : (string) msc_campo( 'subtitulo', $id );
	$hid     = 'calendario-' . $variante;
	$nivel   = 'calendarios' === $variante ? 'h2' : 'h2';
	?>
	<section class="msc-calendario msc-calendario--<?php echo esc_attr( $variante ); ?>" aria-labelledby="<?php echo esc_attr( $hid ); ?>">
		<div class="msc-calendario__portada">
			<?php
			echo msc_img( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				msc_campo( 'portada', $id, false ),
				array(
					'sizes'  => 'inicio' === $variante ? '(max-width: 820px) 100vw, 200px' : '(max-width: 820px) 100vw, 1312px',
					'tamano' => 'inicio' === $variante ? 'msc-800' : 'msc-1600',
				)
			);
			?>
		</div>
		<div class="msc-calendario__texto">
			<?php if ( 'inicio' === $variante ) : ?>
				<p class="msc-sobretitulo"><?php esc_html_e( 'Calendario anual del museo', 'msc' ); ?></p>
			<?php endif; ?>
			<<?php echo esc_attr( $nivel ); ?> id="<?php echo esc_attr( $hid ); ?>" class="msc-calendario__titulo"><?php echo esc_html( $titulo ); ?></<?php echo esc_attr( $nivel ); ?>>
			<?php if ( $desc ) : ?>
				<p class="msc-calendario__desc"><?php echo esc_html( wp_trim_words( $desc, 40 ) ); ?></p>
			<?php endif; ?>
			<?php if ( 'inicio' === $variante && $pdf_url ) : ?>
				<p class="msc-calendario__peso">PDF · <?php echo esc_html( $peso ); ?></p>
			<?php endif; ?>
			<div class="msc-botones">
				<?php if ( $pdf_url ) : ?>
					<a class="msc-btn msc-btn--primario" href="<?php echo esc_url( $pdf_url ); ?>" download>
						<?php echo 'inicio' === $variante ? esc_html__( 'Descargar calendario', 'msc' ) : esc_html__( 'Descargar PDF', 'msc' ); ?>
						<span class="screen-reader-text"><?php echo esc_html( $titulo . ' (PDF' . ( $peso ? ', ' . $peso : '' ) . ')' ); ?></span>
					</a>
				<?php endif; ?>
				<?php if ( 'publicaciones' === $variante && msc_calendarios_anteriores( 1 ) ) : ?>
					<?php echo msc_tema_boton( __( 'Ver calendarios anteriores', 'msc' ), msc_url( 'publicaciones/calendarios' ), 'secundario' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php endif; ?>
			</div>
			<?php if ( 'inicio' === $variante && msc_calendarios_anteriores( 1 ) ) : ?>
				<p><a class="msc-enlace-flecha" href="<?php echo esc_url( msc_url( 'publicaciones/calendarios' ) ); ?>"><?php esc_html_e( 'Ver calendarios anteriores', 'msc' ); ?> <?php echo msc_icono( 'flecha', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a></p>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

/**
 * Marquee de instituciones aliadas: ciclo continuo; se detiene al pasar el ratón,
 * al enfocar con el teclado o al tocarlo (otro toque lo reanuda).
 * Con prefers-reduced-motion se muestra como cuadrícula estática.
 */
function msc_tema_marquee() {
	$aliados = msc_opcion( 'aliados' );
	if ( ! is_array( $aliados ) || ! $aliados ) {
		return;
	}
	$nombres_en = 'en' === msc_idioma_actual() ? msc_opcion( 'aliados_en' ) : null;
	$items      = '';
	foreach ( array_values( $aliados ) as $i => $a ) {
		$nombre = $a['nombre'] ?? '';
		if ( is_array( $nombres_en ) && ! empty( $nombres_en[ $i ]['nombre'] ) ) {
			$nombre = $nombres_en[ $i ]['nombre'];
		}
		$logo      = msc_id_imagen( $a['logo'] ?? 0 );
		$contenido = $logo
			? wp_get_attachment_image(
				$logo,
				'msc-480',
				false,
				array(
					'alt'     => $nombre,
					'loading' => 'lazy',
					'sizes'   => '200px',
					'class'   => 'msc-marquee__logo',
				)
			)
			: '<span class="msc-marquee__nombre">' . esc_html( $nombre ) . '</span>';
		$items    .= '<li class="msc-marquee__item">' . ( ! empty( $a['enlace'] ) ? '<a href="' . esc_url( $a['enlace'] ) . '" rel="noopener">' . $contenido . '</a>' : $contenido ) . '</li>';
	}
	// Copia decorativa para el bucle: oculta a lectores de pantalla y fuera del orden de tabulación.
	$copia = str_replace( '<a href=', '<a tabindex="-1" href=', $items );
	?>
	<section class="msc-seccion msc-contenedor msc-aliados" aria-labelledby="aliados-titulo">
		<h2 id="aliados-titulo"><?php esc_html_e( 'Instituciones aliadas', 'msc' ); ?></h2>
		<div class="msc-marquee" data-msc-marquee>
			<div class="msc-marquee__pista">
				<ul class="msc-marquee__grupo" role="list"><?php echo $items; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></ul>
				<ul class="msc-marquee__grupo msc-marquee__copia" aria-hidden="true" role="list"><?php echo $copia; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></ul>
			</div>
		</div>
	</section>
	<?php
}

/**
 * Formulario de filtros (colecciones, exposiciones pasadas, eventos) en un <details>.
 *
 * @param string $titulo  Título del bloque.
 * @param string $accion  URL de envío.
 * @param string $campos  HTML de los campos.
 * @param bool   $abierto Abierto por defecto (en escritorio lo abre el JS).
 */
function msc_tema_filtros( $titulo, $accion, $campos, $abierto = true ) {
	?>
	<details class="msc-filtros" data-msc-filtros<?php echo $abierto ? ' open' : ''; ?>>
		<summary><?php echo esc_html( $titulo ); ?></summary>
		<form class="msc-filtros__form" action="<?php echo esc_url( $accion ); ?>" method="get" role="search">
			<?php echo $campos; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<button type="submit" class="msc-btn msc-btn--primario msc-btn--bloque"><?php esc_html_e( 'Aplicar', 'msc' ); ?></button>
		</form>
	</details>
	<?php
}

/**
 * Campo de filtro.
 *
 * @param string $tipo     text|select.
 * @param string $nombre   Variable.
 * @param string $etiqueta Etiqueta.
 * @param array  $opciones Opciones para select (valor => texto).
 * @param string $placeholder Placeholder.
 * @return string
 */
function msc_tema_campo_filtro( $tipo, $nombre, $etiqueta, $opciones = array(), $placeholder = '' ) {
	$id    = 'filtro-' . $nombre;
	$valor = (string) get_query_var( $nombre );
	$html  = '<div class="msc-form__campo"><label for="' . esc_attr( $id ) . '">' . esc_html( $etiqueta ) . '</label>';
	if ( 'select' === $tipo ) {
		$html .= '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $nombre ) . '">';
		foreach ( $opciones as $v => $t ) {
			$html .= '<option value="' . esc_attr( $v ) . '"' . selected( $valor, (string) $v, false ) . '>' . esc_html( $t ) . '</option>';
		}
		$html .= '</select>';
	} else {
		$html .= '<input type="search" id="' . esc_attr( $id ) . '" name="' . esc_attr( $nombre ) . '" value="' . esc_attr( $valor ) . '" placeholder="' . esc_attr( $placeholder ) . '">';
	}
	return $html . '</div>';
}

/**
 * Mensaje de estado vacío.
 *
 * @param string $texto Texto.
 * @return string
 */
function msc_tema_vacio( $texto ) {
	return '<p class="msc-vacio">' . esc_html( $texto ) . '</p>';
}
