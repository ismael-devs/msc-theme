<?php
/**
 * Campos personalizados registrados por código con Secure Custom Fields (SCF / ACF).
 * Viajan con el plugin: no se crean desde el administrador.
 *
 * @package msc-contenido
 */

defined( 'ABSPATH' ) || exit;

/**
 * Define un campo de forma abreviada.
 *
 * @param string $tipo     Tipo de campo SCF.
 * @param string $nombre   Nombre (meta key).
 * @param string $etiqueta Etiqueta visible.
 * @param array  $extra    Ajustes adicionales.
 * @return array
 */
function msc_f( $tipo, $nombre, $etiqueta, $extra = array() ) {
	$base = array(
		'type'  => $tipo,
		'name'  => $nombre,
		'label' => $etiqueta,
	);
	if ( 'image' === $tipo || 'gallery' === $tipo ) {
		$base['return_format'] = 'id';
		$base['preview_size']  = 'medium';
		$base['library']       = 'all';
		$base['mime_types']    = 'jpg,jpeg,png,webp,avif';
	}
	if ( 'gallery' === $tipo ) {
		$base['insert'] = 'append';
	}
	if ( 'wysiwyg' === $tipo ) {
		$base['tabs']         = 'all';
		$base['toolbar']      = 'basic';
		$base['media_upload'] = 0;
		$base['instructions'] = __( 'Párrafos de máximo 80 palabras. Usa Intro para separar párrafos.', 'msc' );
	}
	if ( 'repeater' === $tipo ) {
		$base['layout']       = 'block';
		$base['button_label'] = __( 'Añadir fila', 'msc' );
	}
	if ( 'true_false' === $tipo ) {
		$base['ui'] = 1;
	}
	if ( 'date_picker' === $tipo ) {
		$base['display_format'] = 'd/m/Y';
		$base['return_format']  = 'Ymd';
		$base['first_day']      = 1;
	}
	if ( 'date_time_picker' === $tipo ) {
		$base['display_format'] = 'd/m/Y H:i';
		$base['return_format']  = 'Y-m-d H:i:s';
		$base['first_day']      = 1;
	}
	if ( 'relationship' === $tipo ) {
		$base['return_format'] = 'id';
		$base['filters']       = array( 'search' );
	}
	if ( 'file' === $tipo ) {
		$base['return_format'] = 'id';
		$base['library']       = 'all';
	}
	return array_merge( $base, $extra );
}

/**
 * Pestaña del editor de campos.
 *
 * @param string $etiqueta Etiqueta.
 * @return array
 */
function msc_tab( $etiqueta ) {
	return array(
		'type'      => 'tab',
		'name'      => '',
		'label'     => $etiqueta,
		'placement' => 'top',
	);
}

/**
 * Asigna claves únicas y estables a los campos de un grupo (también a los subcampos).
 *
 * @param array  $campos  Campos.
 * @param string $prefijo Prefijo de clave.
 * @return array
 */
function msc_asignar_claves( $campos, $prefijo ) {
	$n = 0;
	foreach ( $campos as $i => $campo ) {
		++$n;
		$nombre = '' !== $campo['name'] ? $campo['name'] : 'tab' . $n;
		if ( empty( $campo['key'] ) ) {
			$campos[ $i ]['key'] = $prefijo . '_' . $nombre;
		}
		if ( ! empty( $campo['sub_fields'] ) ) {
			$campos[ $i ]['sub_fields'] = msc_asignar_claves( $campo['sub_fields'], $campos[ $i ]['key'] );
		}
	}
	return $campos;
}

/**
 * Registra un grupo de campos.
 *
 * @param string $clave     Clave corta del grupo.
 * @param string $titulo    Título.
 * @param array  $campos    Campos.
 * @param array  $ubicacion Reglas de ubicación.
 * @param array  $extra     Ajustes del grupo.
 */
function msc_grupo( $clave, $titulo, $campos, $ubicacion, $extra = array() ) {
	acf_add_local_field_group(
		array_merge(
			array(
				'key'                   => 'group_msc_' . $clave,
				'title'                 => $titulo,
				'fields'                => msc_asignar_claves( $campos, 'field_msc_' . $clave ),
				'location'              => $ubicacion,
				'position'              => 'normal',
				'style'                 => 'default',
				'label_placement'       => 'top',
				'instruction_placement' => 'label',
				'active'                => true,
			),
			$extra
		)
	);
}

/**
 * Regla de ubicación por tipo de contenido.
 *
 * @param string $tipo Tipo.
 * @return array
 */
function msc_en_tipo( $tipo ) {
	return array(
		array(
			array(
				'param'    => 'post_type',
				'operator' => '==',
				'value'    => $tipo,
			),
		),
	);
}

/**
 * Regla de ubicación por plantilla de página.
 *
 * @param string $plantilla Plantilla.
 * @return array
 */
function msc_en_plantilla( $plantilla ) {
	return array(
		array(
			array(
				'param'    => 'page_template',
				'operator' => '==',
				'value'    => $plantilla,
			),
		),
	);
}

/**
 * Repetidor de créditos (rol + nombre).
 *
 * @return array
 */
function msc_campo_creditos() {
	return msc_f(
		'repeater',
		'creditos',
		__( 'Créditos', 'msc' ),
		array(
			'layout'     => 'table',
			'sub_fields' => array(
				msc_f( 'text', 'rol', __( 'Rol', 'msc' ) ),
				msc_f( 'text', 'nombre', __( 'Nombre', 'msc' ) ),
			),
		)
	);
}

/**
 * Registra todos los grupos de campos.
 */
function msc_registrar_campos() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	/* ---------- Colecciones (pieza) ---------- */
	msc_grupo(
		'coleccion',
		__( 'Datos de la pieza', 'msc' ),
		array(
			msc_tab( __( 'Imágenes y descripción', 'msc' ) ),
			msc_f(
				'gallery',
				'galeria',
				__( 'Galería de imágenes', 'msc' ),
				array(
					'required'     => 1,
					'min'          => 1,
					'instructions' => __( 'La primera imagen es la principal. Cada imagen debe tener texto alternativo (descríbela en la biblioteca de medios).', 'msc' ),
				)
			),
			msc_f( 'wysiwyg', 'descripcion', __( 'Descripción', 'msc' ) ),
			msc_f( 'true_false', 'destacada', __( 'Pieza destacada', 'msc' ), array( 'instructions' => __( 'Aparece en "Piezas destacadas" de la portada de Colecciones.', 'msc' ) ) ),
			msc_tab( __( 'Ficha técnica', 'msc' ) ),
			msc_f( 'text', 'dimensiones', __( 'Dimensiones', 'msc' ), array( 'placeholder' => __( 'Alto × ancho × profundidad (cm)', 'msc' ) ) ),
			msc_f(
				'select',
				'forma_adquisicion',
				__( 'Forma de adquisición', 'msc' ),
				array(
					'choices'      => array(),
					'allow_null'   => 1,
					'ui'           => 0,
					'instructions' => __( 'La lista se edita en Museo → Ajustes del museo → Colecciones.', 'msc' ),
				)
			),
			msc_f( 'text', 'numero_inventario', __( 'Número de inventario', 'msc' ) ),
			msc_f( 'text', 'epoca', __( 'Época o fecha', 'msc' ) ),
			msc_f( 'text', 'autor_procedencia', __( 'Autor o procedencia', 'msc' ) ),
			msc_f( 'text', 'material_tecnica', __( 'Material o técnica', 'msc' ) ),
			msc_f( 'text', 'credito_donante', __( 'Crédito del donante', 'msc' ) ),
		),
		msc_en_tipo( 'msc_coleccion' ),
		array( 'hide_on_screen' => array( 'the_content', 'featured_image' ) )
	);

	/* ---------- Categoría de colección ---------- */
	msc_grupo(
		'categoria',
		__( 'Datos de la categoría', 'msc' ),
		array(
			msc_f( 'image', 'imagen_portada', __( 'Imagen de portada', 'msc' ) ),
			msc_f( 'color_picker', 'color_categoria', __( 'Color de la categoría', 'msc' ), array( 'instructions' => __( 'Punto de color junto al nombre. Libros #da2027 · Documentos #1b5f7a · Fotografías #1a8ca0 · Judaica #cc942b · Arte #c01f62', 'msc' ) ) ),
			msc_f( 'number', 'orden', __( 'Orden', 'msc' ), array( 'default_value' => 0 ) ),
		),
		array(
			array(
				array(
					'param'    => 'taxonomy',
					'operator' => '==',
					'value'    => 'categoria_coleccion',
				),
			),
		)
	);

	/* ---------- Exposición ---------- */
	msc_grupo(
		'exposicion',
		__( 'Datos de la exposición', 'msc' ),
		array(
			msc_tab( __( 'General', 'msc' ) ),
			msc_f( 'image', 'imagen_principal', __( 'Imagen principal / afiche', 'msc' ), array( 'required' => 1 ) ),
			msc_f(
				'date_picker',
				'fecha_inicio',
				__( 'Fecha de inicio', 'msc' ),
				array(
					'required'     => 1,
					'wrapper'      => array( 'width' => 50 ),
					'instructions' => __( 'Antes de esta fecha la exposición aparece como "Próxima".', 'msc' ),
				)
			),
			msc_f(
				'date_picker',
				'fecha_cierre',
				__( 'Fecha de cierre', 'msc' ),
				array(
					'wrapper'      => array( 'width' => 50 ),
					'instructions' => __( 'Al día siguiente pasa sola a "Exposiciones pasadas" (cambia su URL con redirección 301).', 'msc' ),
				)
			),
			msc_f(
				'repeater',
				'sede',
				__( 'Sede', 'msc' ),
				array(
					'layout'     => 'table',
					'sub_fields' => array( msc_f( 'text', 'nombre', __( 'Nombre de la sede', 'msc' ) ) ),
				)
			),
			msc_f( 'text', 'horario', __( 'Horario', 'msc' ) ),
			msc_f( 'wysiwyg', 'texto', __( 'Texto de la exposición', 'msc' ) ),
			msc_tab( __( 'Secciones temáticas', 'msc' ) ),
			msc_f(
				'repeater',
				'secciones',
				__( 'Secciones temáticas', 'msc' ),
				array(
					'sub_fields' => array(
						msc_f( 'text', 'titulo', __( 'Título', 'msc' ) ),
						msc_f( 'textarea', 'texto', __( 'Texto breve', 'msc' ), array( 'rows' => 3 ) ),
						msc_f( 'image', 'imagen', __( 'Imagen', 'msc' ) ),
						msc_f( 'url', 'enlace', __( 'Enlace', 'msc' ) ),
					),
				)
			),
			msc_tab( __( 'Créditos y multimedia', 'msc' ) ),
			msc_campo_creditos(),
			msc_f( 'oembed', 'video', __( 'Video (YouTube o Vimeo)', 'msc' ) ),
			msc_f( 'gallery', 'galeria', __( 'Galería', 'msc' ) ),
			msc_tab( __( 'Relacionados', 'msc' ) ),
			msc_f( 'relationship', 'publicacion_relacionada', __( 'Publicación relacionada', 'msc' ), array( 'post_type' => array( 'msc_publicacion' ) ) ),
			msc_f( 'relationship', 'piezas_relacionadas', __( 'Piezas relacionadas', 'msc' ), array( 'post_type' => array( 'msc_coleccion' ) ) ),
			msc_tab( __( 'Ideas futuras', 'msc' ) ),
			msc_f(
				'url',
				'recorrido_360',
				__( 'Enlace a recorrido virtual 360°', 'msc' ),
				array( 'instructions' => __( 'Campo reservado para una fase futura. Todavía no se muestra en el sitio.', 'msc' ) )
			),
		),
		msc_en_tipo( 'msc_exposicion' ),
		array( 'hide_on_screen' => array( 'the_content', 'featured_image' ) )
	);

	/* ---------- Evento ---------- */
	msc_grupo(
		'evento',
		__( 'Datos del evento', 'msc' ),
		array(
			msc_tab( __( 'General', 'msc' ) ),
			msc_f(
				'taxonomy',
				'tipo',
				__( 'Tipo de evento', 'msc' ),
				array(
					'taxonomy'      => 'tipo_evento',
					'field_type'    => 'select',
					'add_term'      => 1,
					'save_terms'    => 1,
					'load_terms'    => 1,
					'return_format' => 'id',
					'allow_null'    => 1,
				)
			),
			msc_f( 'date_time_picker', 'fecha_hora', __( 'Fecha y hora', 'msc' ), array( 'required' => 1 ) ),
			msc_f( 'text', 'lugar', __( 'Lugar', 'msc' ) ),
			msc_f(
				'repeater',
				'ponentes',
				__( 'Ponentes', 'msc' ),
				array(
					'layout'     => 'table',
					'sub_fields' => array( msc_f( 'text', 'nombre', __( 'Nombre', 'msc' ) ) ),
				)
			),
			msc_f( 'image', 'imagen', __( 'Imagen', 'msc' ) ),
			msc_f(
				'wysiwyg',
				'descripcion',
				__( 'Descripción', 'msc' ),
				array( 'instructions' => __( 'Si escribes una descripción, el evento tendrá su propia página. Si la dejas vacía, solo aparecerá como una línea en la lista de eventos. Párrafos de máximo 80 palabras.', 'msc' ) )
			),
			msc_tab( __( 'Multimedia y relacionados', 'msc' ) ),
			msc_f( 'oembed', 'video', __( 'Grabación (YouTube o Vimeo)', 'msc' ) ),
			msc_f( 'gallery', 'galeria', __( 'Galería', 'msc' ) ),
			msc_f( 'relationship', 'relacionados', __( 'Relacionados (exposición o publicación)', 'msc' ), array( 'post_type' => array( 'msc_exposicion', 'msc_publicacion' ) ) ),
		),
		msc_en_tipo( 'msc_evento' ),
		array( 'hide_on_screen' => array( 'the_content', 'featured_image' ) )
	);

	/* ---------- Publicación ---------- */
	$es_calendario = array(
		array(
			array(
				'field'    => 'field_msc_publicacion_tipo',
				'operator' => '==',
				'value'    => 'calendario',
			),
		),
	);
	msc_grupo(
		'publicacion',
		__( 'Datos de la publicación', 'msc' ),
		array(
			msc_tab( __( 'General', 'msc' ) ),
			msc_f(
				'radio',
				'tipo',
				__( 'Tipo de publicación', 'msc' ),
				array(
					'choices'       => array(
						'libro'      => __( 'Libro / catálogo', 'msc' ),
						'calendario' => __( 'Calendario', 'msc' ),
					),
					'default_value' => 'libro',
					'layout'        => 'horizontal',
				)
			),
			msc_f( 'image', 'portada', __( 'Portada', 'msc' ), array( 'required' => 1 ) ),
			msc_f( 'text', 'subtitulo', __( 'Subtítulo', 'msc' ) ),
			msc_f( 'number', 'anio', __( 'Año', 'msc' ), array( 'wrapper' => array( 'width' => 25 ) ) ),
			msc_f( 'number', 'paginas', __( 'Páginas', 'msc' ), array( 'wrapper' => array( 'width' => 25 ) ) ),
			msc_f( 'text', 'formato', __( 'Formato', 'msc' ), array( 'wrapper' => array( 'width' => 25 ) ) ),
			msc_f( 'number', 'tirada', __( 'Tirada (ejemplares)', 'msc' ), array( 'wrapper' => array( 'width' => 25 ) ) ),
			msc_campo_creditos(),
			msc_f( 'wysiwyg', 'resena', __( 'Reseña', 'msc' ) ),
			msc_tab( __( 'Consulta y relacionados', 'msc' ) ),
			msc_f(
				'url',
				'consulta_url',
				__( 'Enlace de consulta', 'msc' ),
				array( 'instructions' => __( '[Por confirmar con el museo] Enlace para consultar la publicación, como en la web actual. No se muestran botones de compra ni de descarga.', 'msc' ) )
			),
			msc_f( 'file', 'consulta_archivo', __( 'Archivo de consulta', 'msc' ), array( 'instructions' => __( '[Por confirmar con el museo] Alternativa al enlace: un archivo que se abre para leer en línea.', 'msc' ) ) ),
			msc_f( 'relationship', 'exposicion_relacionada', __( 'Exposición relacionada', 'msc' ), array( 'post_type' => array( 'msc_exposicion' ) ) ),
			msc_tab( __( 'Calendario', 'msc' ) ),
			msc_f(
				'message',
				'aviso_calendario',
				__( 'Calendario anual', 'msc' ),
				array( 'message' => __( 'Estos campos solo aplican cuando el tipo es "Calendario". Marca este calendario como destacado para que aparezca en el Inicio y en Publicaciones.', 'msc' ) )
			),
			msc_f(
				'text',
				'anio_civil',
				__( 'Año civil', 'msc' ),
				array(
					'placeholder'       => '2026',
					'wrapper'           => array( 'width' => 50 ),
					'conditional_logic' => $es_calendario,
				)
			),
			msc_f(
				'text',
				'anio_hebreo',
				__( 'Año hebreo', 'msc' ),
				array(
					'placeholder'       => '5787',
					'wrapper'           => array( 'width' => 50 ),
					'conditional_logic' => $es_calendario,
				)
			),
			msc_f(
				'file',
				'pdf',
				__( 'PDF del calendario', 'msc' ),
				array(
					'mime_types'        => 'pdf',
					'conditional_logic' => $es_calendario,
				)
			),
			msc_f(
				'true_false',
				'destacado',
				__( 'Calendario destacado', 'msc' ),
				array(
					'conditional_logic' => $es_calendario,
					'instructions'      => __( 'Se muestra el destacado con el año civil más reciente.', 'msc' ),
				)
			),
		),
		msc_en_tipo( 'msc_publicacion' ),
		array( 'hide_on_screen' => array( 'the_content', 'featured_image' ) )
	);

	msc_registrar_campos_paginas();
	msc_registrar_opciones();
}
add_action( 'acf/init', 'msc_registrar_campos' );

/**
 * Campos de las páginas editoriales.
 */
function msc_registrar_campos_paginas() {
	$intro = msc_f(
		'textarea',
		'intro',
		__( 'Texto introductorio', 'msc' ),
		array(
			'rows'         => 3,
			'instructions' => __( 'Hasta 80 palabras.', 'msc' ),
		)
	);

	/* ---------- Inicio ---------- */
	msc_grupo(
		'inicio',
		__( 'Contenido del Inicio', 'msc' ),
		array(
			msc_tab( __( 'Portada (hero)', 'msc' ) ),
			msc_f( 'image', 'hero_imagen', __( 'Foto principal', 'msc' ) ),
			msc_f( 'text', 'hero_titulo', __( 'Título', 'msc' ), array( 'default_value' => 'Museo Sefardí de Caracas Morris E. Curiel' ) ),
			msc_f( 'textarea', 'hero_frase', __( 'Frase breve de presentación', 'msc' ), array( 'rows' => 2 ) ),
			msc_f(
				'text',
				'hero_boton1',
				__( 'Botón 1', 'msc' ),
				array(
					'default_value' => 'Explora las colecciones',
					'wrapper'       => array( 'width' => 50 ),
				)
			),
			msc_f(
				'url',
				'hero_boton1_url',
				__( 'Enlace del botón 1', 'msc' ),
				array(
					'wrapper'      => array( 'width' => 50 ),
					'instructions' => __( 'Vacío: Colecciones.', 'msc' ),
				)
			),
			msc_f(
				'text',
				'hero_boton2',
				__( 'Botón 2', 'msc' ),
				array(
					'default_value' => 'Planifica tu visita',
					'wrapper'       => array( 'width' => 50 ),
				)
			),
			msc_f(
				'url',
				'hero_boton2_url',
				__( 'Enlace del botón 2', 'msc' ),
				array(
					'wrapper'      => array( 'width' => 50 ),
					'instructions' => __( 'Vacío: Visítanos.', 'msc' ),
				)
			),
			msc_tab( __( 'Memoria sefardí', 'msc' ) ),
			msc_f( 'text', 'memoria_titulo', __( 'Título', 'msc' ), array( 'default_value' => 'Memoria sefardí, de Sefarad a Venezuela' ) ),
			msc_f(
				'textarea',
				'memoria_texto',
				__( 'Texto', 'msc' ),
				array(
					'rows'          => 4,
					'default_value' => 'El Museo Sefardí de Caracas "Morris E. Curiel" fue fundado en 1999 para preservar la memoria de la comunidad judía sefardí, desde Sefarad hasta su arraigo en Venezuela. Está en el complejo de Maripérez, junto a la Gran Sinagoga Tiferet Israel, en Caracas.',
				)
			),
			msc_tab( __( 'Destacado', 'msc' ) ),
			msc_f(
				'select',
				'destacado_modo',
				__( 'Qué destacar', 'msc' ),
				array(
					'choices'       => array(
						'auto'       => __( 'Automático: exposición actual; si no hay, el próximo evento', 'msc' ),
						'exposicion' => __( 'Solo exposición actual o próxima', 'msc' ),
						'evento'     => __( 'Solo el próximo evento', 'msc' ),
						'ninguno'    => __( 'No mostrar', 'msc' ),
					),
					'default_value' => 'auto',
				)
			),
			msc_tab( __( 'Espacios', 'msc' ) ),
			msc_f( 'text', 'espacios_titulo', __( 'Título', 'msc' ), array( 'default_value' => 'Tres espacios de encuentro y conocimiento' ) ),
			msc_f(
				'repeater',
				'espacios',
				__( 'Espacios', 'msc' ),
				array(
					'max'        => 3,
					'sub_fields' => array(
						msc_f( 'image', 'imagen', __( 'Imagen', 'msc' ) ),
						msc_f( 'text', 'titulo', __( 'Título', 'msc' ) ),
						msc_f( 'textarea', 'texto', __( 'Texto', 'msc' ), array( 'rows' => 2 ) ),
						msc_f( 'url', 'enlace', __( 'Enlace (opcional)', 'msc' ) ),
					),
				)
			),
			msc_tab( __( 'Legado', 'msc' ) ),
			msc_f( 'text', 'legado_titulo', __( 'Título', 'msc' ), array( 'default_value' => 'Confía al museo la custodia de tu legado' ) ),
			msc_f(
				'textarea',
				'legado_texto',
				__( 'Texto', 'msc' ),
				array(
					'rows'          => 2,
					'default_value' => 'Invitamos a familias, coleccionistas e instituciones a confiar en el Museo Sefardí de Caracas la custodia definitiva de su legado.',
				)
			),
		),
		array(
			array(
				array(
					'param'    => 'page_type',
					'operator' => '==',
					'value'    => 'front_page',
				),
			),
		),
		array( 'hide_on_screen' => array( 'the_content' ) )
	);

	/* ---------- El museo ---------- */
	$consejo = static function ( $nombre, $etiqueta ) {
		return msc_f(
			'repeater',
			$nombre,
			$etiqueta,
			array(
				'layout'     => 'table',
				'sub_fields' => array(
					msc_f( 'text', 'nombre', __( 'Nombre', 'msc' ) ),
					msc_f( 'text', 'cargo', __( 'Cargo', 'msc' ) ),
				),
			)
		);
	};
	msc_grupo(
		'museo',
		__( 'Contenido de El museo', 'msc' ),
		array(
			msc_tab( __( 'Historia', 'msc' ) ),
			$intro,
			msc_f( 'image', 'historia_imagen', __( 'Foto del edificio', 'msc' ) ),
			msc_f( 'wysiwyg', 'historia_texto', __( 'Historia', 'msc' ) ),
			msc_f( 'image', 'fundador_imagen', __( 'Retrato del fundador', 'msc' ) ),
			msc_f( 'text', 'fundador_nombre', __( 'Nombre del fundador', 'msc' ), array( 'default_value' => 'Morris E. Curiel (1920–2007)' ) ),
			msc_f( 'textarea', 'fundador_texto', __( 'Semblanza', 'msc' ), array( 'rows' => 3 ) ),
			msc_tab( __( 'Misión y visión', 'msc' ) ),
			msc_f( 'textarea', 'mision', __( 'Misión', 'msc' ), array( 'rows' => 3 ) ),
			msc_f( 'textarea', 'vision', __( 'Visión', 'msc' ), array( 'rows' => 3 ) ),
			msc_tab( __( 'Espacios', 'msc' ) ),
			msc_f( 'textarea', 'espacios_intro', __( 'Introducción', 'msc' ), array( 'rows' => 2 ) ),
			msc_f(
				'repeater',
				'espacios',
				__( 'Espacios', 'msc' ),
				array(
					'sub_fields' => array(
						msc_f( 'image', 'imagen', __( 'Foto', 'msc' ) ),
						msc_f( 'text', 'titulo', __( 'Título', 'msc' ) ),
						msc_f( 'textarea', 'texto', __( 'Texto', 'msc' ), array( 'rows' => 3 ) ),
						msc_f( 'text', 'enlace_texto', __( 'Texto del enlace (opcional)', 'msc' ) ),
						msc_f( 'url', 'enlace_url', __( 'Enlace (opcional)', 'msc' ) ),
					),
				)
			),
			msc_tab( __( 'Consejos directivos', 'msc' ) ),
			msc_f( 'text', 'consejo_museo_titulo', __( 'Título del consejo del Museo', 'msc' ), array( 'default_value' => 'Consejo directivo del Museo' ) ),
			$consejo( 'consejo_museo', __( 'Consejo directivo del Museo', 'msc' ) ),
			msc_f( 'text', 'consejo_aiv_titulo', __( 'Título del consejo de la AIV', 'msc' ), array( 'default_value' => 'Consejo directivo de la Asociación Israelita de Venezuela' ) ),
			$consejo( 'consejo_aiv', __( 'Consejo directivo de la AIV', 'msc' ) ),
		),
		msc_en_plantilla( 'msc-el-museo' ),
		array( 'hide_on_screen' => array( 'the_content' ) )
	);

	/* ---------- Colecciones (portada) ---------- */
	msc_grupo( 'pcolecciones', __( 'Portada de Colecciones', 'msc' ), array( $intro ), msc_en_plantilla( 'msc-colecciones' ), array( 'hide_on_screen' => array( 'the_content' ) ) );

	/* ---------- Exposiciones (portada) ---------- */
	msc_grupo(
		'pexposiciones',
		__( 'Portada de Exposiciones', 'msc' ),
		array(
			$intro,
			msc_f( 'image', 'sala_imagen', __( 'Sala permanente: foto', 'msc' ) ),
			msc_f( 'wysiwyg', 'sala_texto', __( 'Sala permanente: texto', 'msc' ) ),
			msc_f(
				'text',
				'sala_boton1',
				__( 'Botón 1', 'msc' ),
				array(
					'default_value' => 'Conocer la sala',
					'wrapper'       => array( 'width' => 50 ),
				)
			),
			msc_f(
				'url',
				'sala_boton1_url',
				__( 'Enlace del botón 1', 'msc' ),
				array(
					'wrapper'      => array( 'width' => 50 ),
					'instructions' => __( 'Vacío: El museo → Espacios.', 'msc' ),
				)
			),
			msc_f(
				'text',
				'sala_boton2',
				__( 'Botón 2', 'msc' ),
				array(
					'default_value' => 'Planifica tu visita',
					'wrapper'       => array( 'width' => 50 ),
				)
			),
			msc_f(
				'url',
				'sala_boton2_url',
				__( 'Enlace del botón 2', 'msc' ),
				array(
					'wrapper'      => array( 'width' => 50 ),
					'instructions' => __( 'Vacío: Visítanos.', 'msc' ),
				)
			),
		),
		msc_en_plantilla( 'msc-exposiciones' ),
		array( 'hide_on_screen' => array( 'the_content' ) )
	);

	/* ---------- Colaboraciones ---------- */
	msc_grupo(
		'colaboraciones',
		__( 'Contenido de Colaboraciones', 'msc' ),
		array(
			$intro,
			msc_f(
				'repeater',
				'modalidades',
				__( 'Modalidades', 'msc' ),
				array(
					'sub_fields' => array(
						msc_f( 'text', 'titulo', __( 'Título', 'msc' ) ),
						msc_f( 'textarea', 'texto', __( 'Texto', 'msc' ), array( 'rows' => 3 ) ),
						msc_f( 'text', 'ancla', __( 'Ancla (donacion, adquisicion, comodato)', 'msc' ) ),
					),
				)
			),
			msc_f(
				'repeater',
				'pasos',
				__( 'Cómo funciona (pasos)', 'msc' ),
				array(
					'sub_fields' => array(
						msc_f( 'text', 'titulo', __( 'Paso', 'msc' ) ),
						msc_f( 'text', 'texto', __( 'Descripción breve', 'msc' ) ),
					),
				)
			),
		),
		msc_en_plantilla( 'msc-colaboraciones' ),
		array( 'hide_on_screen' => array( 'the_content' ) )
	);

	/* ---------- Visítanos ---------- */
	msc_grupo(
		'visitanos',
		__( 'Contenido de Visítanos', 'msc' ),
		array(
			$intro,
			msc_f(
				'repeater',
				'que_encontraras',
				__( 'Qué encontrarás', 'msc' ),
				array(
					'max'        => 4,
					'sub_fields' => array(
						msc_f( 'image', 'imagen', __( 'Imagen', 'msc' ) ),
						msc_f( 'text', 'titulo', __( 'Título', 'msc' ) ),
						msc_f( 'url', 'enlace', __( 'Enlace', 'msc' ) ),
					),
				)
			),
		),
		msc_en_plantilla( 'msc-visitanos' ),
		array( 'hide_on_screen' => array( 'the_content' ) )
	);
}

/**
 * Página de opciones "Museo" (datos globales) con versión en inglés de los textos.
 */
function msc_registrar_opciones() {
	if ( ! function_exists( 'acf_add_options_page' ) ) {
		return;
	}
	acf_add_options_page(
		array(
			'page_title' => __( 'Ajustes del museo', 'msc' ),
			'menu_title' => __( 'Museo', 'msc' ),
			'menu_slug'  => 'msc-museo',
			'capability' => 'edit_pages',
			'icon_url'   => 'dashicons-bank',
			'position'   => 19,
			'redirect'   => false,
		)
	);

	$traducibles = array();
	$t           = static function ( $campo ) use ( &$traducibles ) {
		$traducibles[] = $campo;
		return $campo;
	};

	$campos = array(
		msc_tab( __( 'Contacto', 'msc' ) ),
		$t(
			msc_f(
				'textarea',
				'direccion',
				__( 'Dirección completa', 'msc' ),
				array(
					'rows'          => 2,
					'default_value' => 'Paseo Colón con Av. Principal de Maripérez, complejo Gran Sinagoga Tiferet Israel / AIV, Caracas',
				)
			)
		),
		$t( msc_f( 'text', 'direccion_corta', __( 'Dirección corta (pie de página)', 'msc' ), array( 'default_value' => 'Paseo Colón con Av. Principal de Maripérez, Caracas' ) ) ),
		msc_f(
			'text',
			'telefono',
			__( 'Teléfono', 'msc' ),
			array(
				'default_value' => '+58 212 5781489',
				'wrapper'       => array( 'width' => 50 ),
			)
		),
		msc_f(
			'text',
			'whatsapp',
			__( 'WhatsApp', 'msc' ),
			array(
				'default_value' => '+58 424 2787933',
				'wrapper'       => array( 'width' => 50 ),
			)
		),
		msc_f(
			'text',
			'instagram',
			__( 'Usuario de Instagram (sin @)', 'msc' ),
			array(
				'default_value' => 'museosefardiccs',
				'wrapper'       => array( 'width' => 50 ),
			)
		),
		msc_f(
			'email',
			'correo',
			__( 'Correo de contacto', 'msc' ),
			array(
				'wrapper'      => array( 'width' => 50 ),
				'instructions' => __( '[Por confirmar] Si está vacío no se muestra.', 'msc' ),
			)
		),
		msc_f( 'url', 'mapa_enlace', __( 'Enlace "Abrir en el mapa"', 'msc' ), array( 'instructions' => __( 'Enlace de Google Maps u OpenStreetMap.', 'msc' ) ) ),
		msc_f( 'url', 'mapa_embed', __( 'Mapa incrustado (URL del iframe)', 'msc' ), array( 'instructions' => __( 'Opcional. Se carga solo cuando el visitante lo pide, para no penalizar la velocidad.', 'msc' ) ) ),
		msc_f( 'text', 'latitud', __( 'Latitud', 'msc' ), array( 'wrapper' => array( 'width' => 50 ) ) ),
		msc_f( 'text', 'longitud', __( 'Longitud', 'msc' ), array( 'wrapper' => array( 'width' => 50 ) ) ),

		msc_tab( __( 'Horarios y visita', 'msc' ) ),
		$t(
			msc_f(
				'textarea',
				'horarios',
				__( 'Días y horario', 'msc' ),
				array(
					'rows'         => 2,
					'instructions' => __( '[Por confirmar] Texto visible.', 'msc' ),
				)
			)
		),
		$t( msc_f( 'textarea', 'tarifas', __( 'Tarifas', 'msc' ), array( 'rows' => 2 ) ) ),
		$t( msc_f( 'textarea', 'reservas', __( 'Reservas', 'msc' ), array( 'rows' => 2 ) ) ),
		msc_f(
			'repeater',
			'horario_estructurado',
			__( 'Horario para buscadores (datos estructurados)', 'msc' ),
			array(
				'layout'       => 'table',
				'instructions' => __( 'Opcional: Google lo usa para mostrar el horario.', 'msc' ),
				'sub_fields'   => array(
					msc_f(
						'checkbox',
						'dias',
						__( 'Días', 'msc' ),
						array(
							'choices' => array(
								'Monday'    => __( 'Lun', 'msc' ),
								'Tuesday'   => __( 'Mar', 'msc' ),
								'Wednesday' => __( 'Mié', 'msc' ),
								'Thursday'  => __( 'Jue', 'msc' ),
								'Friday'    => __( 'Vie', 'msc' ),
								'Saturday'  => __( 'Sáb', 'msc' ),
								'Sunday'    => __( 'Dom', 'msc' ),
							),
							'layout'  => 'horizontal',
						)
					),
					msc_f(
						'time_picker',
						'abre',
						__( 'Abre', 'msc' ),
						array(
							'display_format' => 'H:i',
							'return_format'  => 'H:i',
						)
					),
					msc_f(
						'time_picker',
						'cierra',
						__( 'Cierra', 'msc' ),
						array(
							'display_format' => 'H:i',
							'return_format'  => 'H:i',
						)
					),
				),
			)
		),
		$t( msc_f( 'textarea', 'accesibilidad', __( 'Accesibilidad', 'msc' ), array( 'rows' => 3 ) ) ),
		$t( msc_f( 'textarea', 'estacionamiento', __( 'Estacionamiento', 'msc' ), array( 'rows' => 3 ) ) ),
		$t( msc_f( 'textarea', 'seguridad', __( 'Seguridad y acceso', 'msc' ), array( 'rows' => 3 ) ) ),
		$t( msc_f( 'textarea', 'normas', __( 'Normas de visita', 'msc' ), array( 'rows' => 3 ) ) ),

		msc_tab( __( 'Instituciones aliadas', 'msc' ) ),
		msc_f(
			'repeater',
			'aliados',
			__( 'Instituciones aliadas (marquee del Inicio)', 'msc' ),
			array(
				'layout'     => 'table',
				'sub_fields' => array(
					msc_f( 'image', 'logo', __( 'Logo', 'msc' ) ),
					msc_f( 'text', 'nombre', __( 'Nombre', 'msc' ) ),
					msc_f( 'url', 'enlace', __( 'Enlace', 'msc' ) ),
				),
			)
		),

		msc_tab( __( 'Textos de portadas', 'msc' ) ),
		$t( msc_f( 'textarea', 'intro_eventos', __( 'Eventos: texto introductorio', 'msc' ), array( 'rows' => 2 ) ) ),
		$t( msc_f( 'textarea', 'intro_publicaciones', __( 'Publicaciones: texto introductorio', 'msc' ), array( 'rows' => 2 ) ) ),
		$t( msc_f( 'textarea', 'intro_pasadas', __( 'Exposiciones pasadas: texto introductorio', 'msc' ), array( 'rows' => 2 ) ) ),
		$t( msc_f( 'textarea', 'intro_calendarios', __( 'Calendarios anteriores: texto introductorio', 'msc' ), array( 'rows' => 2 ) ) ),
		$t( msc_f( 'text', 'boletin_texto', __( 'Texto breve del boletín', 'msc' ) ) ),
		msc_f(
			'number',
			'calendarios_anteriores',
			__( 'Cantidad de calendarios anteriores', 'msc' ),
			array(
				'default_value' => 4,
				'min'           => 1,
				'max'           => 50,
				'instructions'  => __( '[Por confirmar] Cuántos se muestran en /publicaciones/calendarios/.', 'msc' ),
			)
		),

		msc_tab( __( 'Colecciones', 'msc' ) ),
		msc_f(
			'textarea',
			'formas_adquisicion',
			__( 'Formas de adquisición', 'msc' ),
			array(
				'rows'          => 4,
				'default_value' => "Donación\nCompra\nComodato",
				'instructions'  => __( 'Una por línea. [Lista final por confirmar]', 'msc' ),
			)
		),

		msc_tab( __( 'Formularios', 'msc' ) ),
		msc_f( 'email', 'formularios_correo', __( 'Correo de destino de los formularios', 'msc' ), array( 'instructions' => __( '[Por confirmar] Si está vacío se usa el correo del administrador.', 'msc' ) ) ),
		msc_f( 'true_false', 'formularios_copia', __( 'Enviar copia de recepción a quien escribe', 'msc' ), array( 'default_value' => 1 ) ),
		msc_f(
			'number',
			'adjuntos_maximo',
			__( 'Máximo de fotos adjuntas', 'msc' ),
			array(
				'default_value' => 3,
				'min'           => 0,
				'max'           => 10,
				'wrapper'       => array( 'width' => 50 ),
			)
		),
		msc_f(
			'number',
			'adjuntos_mb',
			__( 'Tamaño máximo por foto (MB)', 'msc' ),
			array(
				'default_value' => 5,
				'min'           => 1,
				'max'           => 20,
				'wrapper'       => array( 'width' => 50 ),
			)
		),
	);

	// Pestaña "English": duplica los textos traducibles con sufijo _en.
	$campos[] = msc_tab( 'English' );
	$campos[] = msc_f( 'message', 'aviso_en', 'English', array( 'message' => __( 'Versión en inglés de los textos globales. Si un campo está vacío, en /en/ se muestra el texto en español.', 'msc' ) ) );
	foreach ( $traducibles as $campo ) {
		$copia                  = $campo;
		$copia['name']         .= '_en';
		$copia['label']        .= ' (EN)';
		$copia['default_value'] = '';
		unset( $copia['instructions'] );
		$campos[] = $copia;
	}
	$campos[] = msc_f(
		'repeater',
		'aliados_en',
		__( 'Nombres de instituciones aliadas (EN)', 'msc' ),
		array(
			'layout'       => 'table',
			'instructions' => __( 'En el mismo orden que la lista en español.', 'msc' ),
			'sub_fields'   => array( msc_f( 'text', 'nombre', __( 'Nombre', 'msc' ) ) ),
		)
	);

	msc_grupo(
		'opciones',
		__( 'Ajustes del museo', 'msc' ),
		$campos,
		array(
			array(
				array(
					'param'    => 'options_page',
					'operator' => '==',
					'value'    => 'msc-museo',
				),
			),
		)
	);
}

/**
 * Formas de adquisición editables (desde la página de opciones).
 *
 * @return array<string, string>
 */
function msc_formas_adquisicion() {
	$texto = function_exists( 'get_field' ) ? get_field( 'formas_adquisicion', 'option', false ) : '';
	if ( ! $texto ) {
		$texto = "Donación\nCompra\nComodato";
	}
	$lista = array();
	foreach ( preg_split( '/\r\n|\r|\n/', $texto ) as $linea ) {
		$linea = trim( $linea );
		if ( '' !== $linea ) {
			$lista[ $linea ] = $linea;
		}
	}
	return $lista;
}

/**
 * Rellena las opciones del selector "Forma de adquisición".
 *
 * @param array $campo Campo.
 * @return array
 */
function msc_cargar_formas_adquisicion( $campo ) {
	$campo['choices'] = msc_formas_adquisicion();
	return $campo;
}
add_filter( 'acf/load_field/name=forma_adquisicion', 'msc_cargar_formas_adquisicion' );

/**
 * Al guardar una publicación: sincroniza la taxonomía tipo_publicacion con el campo "tipo"
 * y la imagen destacada con la imagen principal (útil en listados del admin y redes sociales).
 *
 * @param int|string $post_id ID.
 */
function msc_sincronizar_al_guardar( $post_id ) {
	if ( ! is_numeric( $post_id ) || wp_is_post_revision( (int) $post_id ) ) {
		return;
	}
	$post_id = (int) $post_id;
	$tipo    = get_post_type( $post_id );
	if ( 'msc_publicacion' === $tipo ) {
		$valor   = get_field( 'tipo', $post_id, false );
		$valor   = $valor ? $valor : 'libro';
		$termino = get_term_by( 'slug', $valor, 'tipo_publicacion' );
		if ( ! $termino ) {
			$nombre     = 'calendario' === $valor ? __( 'Calendario', 'msc' ) : __( 'Libro / catálogo', 'msc' );
			$nuevo      = wp_insert_term( $nombre, 'tipo_publicacion', array( 'slug' => $valor ) );
			$termino_id = is_wp_error( $nuevo ) ? 0 : (int) $nuevo['term_id'];
		} else {
			$termino_id = (int) $termino->term_id;
		}
		if ( $termino_id ) {
			wp_set_object_terms( $post_id, array( $termino_id ), 'tipo_publicacion' );
		}
	}
	if ( in_array( $tipo, array( 'msc_coleccion', 'msc_exposicion', 'msc_evento', 'msc_publicacion' ), true ) ) {
		$imagen = msc_imagen_principal( $post_id );
		$actual = (int) get_post_thumbnail_id( $post_id );
		if ( $imagen && $imagen !== $actual && 0 !== $imagen ) {
			// Solo si la imagen proviene de los campos y no de la destacada.
			set_post_thumbnail( $post_id, $imagen );
		}
	}
}
add_action( 'acf/save_post', 'msc_sincronizar_al_guardar', 20 );
