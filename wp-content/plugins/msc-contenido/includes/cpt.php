<?php
/**
 * Tipos de contenido (CPT) y taxonomías.
 * Las URL se gestionan en urls.php (rewrite => false aquí) para controlar el anidado.
 *
 * @package msc-contenido
 */

defined( 'ABSPATH' ) || exit;

/**
 * Etiquetas de un tipo de contenido.
 *
 * @param string $singular Nombre en singular.
 * @param string $plural   Nombre en plural.
 * @param bool   $femenino Si el sustantivo es femenino.
 * @return array
 */
function msc_etiquetas_tipo( $singular, $plural, $femenino = true ) {
	return array(
		'name'               => $plural,
		'singular_name'      => $singular,
		'menu_name'          => $plural,
		/* translators: %s: nombre en singular. */
		'add_new_item'       => sprintf( $femenino ? __( 'Añadir nueva %s', 'msc' ) : __( 'Añadir nuevo %s', 'msc' ), mb_strtolower( $singular ) ),
		'add_new'            => $femenino ? __( 'Añadir nueva', 'msc' ) : __( 'Añadir nuevo', 'msc' ),
		/* translators: %s: nombre en singular. */
		'edit_item'          => sprintf( __( 'Editar %s', 'msc' ), mb_strtolower( $singular ) ),
		/* translators: %s: nombre en singular. */
		'view_item'          => sprintf( __( 'Ver %s', 'msc' ), mb_strtolower( $singular ) ),
		/* translators: %s: nombre en plural. */
		'search_items'       => sprintf( __( 'Buscar %s', 'msc' ), mb_strtolower( $plural ) ),
		'not_found'          => __( 'No se encontraron resultados.', 'msc' ),
		'not_found_in_trash' => __( 'No hay nada en la papelera.', 'msc' ),
		/* translators: %s: nombre en plural. */
		'all_items'          => sprintf( __( 'Todas las %s', 'msc' ), mb_strtolower( $plural ) ),
	);
}

/**
 * Registra los cuatro tipos de contenido del museo.
 */
function msc_registrar_tipos() {
	$comun = array(
		'public'          => true,
		'show_in_rest'    => true,
		'rewrite'         => false,
		'query_var'       => true,
		'has_archive'     => false,
		'capability_type' => 'post',
		'map_meta_cap'    => true,
		'supports'        => array( 'title', 'excerpt', 'revisions', 'thumbnail' ),
	);

	register_post_type(
		'msc_coleccion',
		array_merge(
			$comun,
			array(
				'labels'        => array_merge(
					msc_etiquetas_tipo( __( 'Pieza', 'msc' ), __( 'Colecciones', 'msc' ) ),
					array( 'all_items' => __( 'Todas las piezas', 'msc' ) )
				),
				'description'   => __( 'Piezas de las colecciones del museo.', 'msc' ),
				'menu_icon'     => 'dashicons-archive',
				'menu_position' => 20,
			)
		)
	);

	register_post_type(
		'msc_exposicion',
		array_merge(
			$comun,
			array(
				'labels'        => array_merge(
					msc_etiquetas_tipo( __( 'Exposición', 'msc' ), __( 'Exposiciones', 'msc' ) ),
					array( 'menu_name' => __( 'Exposiciones', 'msc' ) )
				),
				'menu_icon'     => 'dashicons-format-gallery',
				'menu_position' => 21,
			)
		)
	);

	register_post_type(
		'msc_evento',
		array_merge(
			$comun,
			array(
				'labels'        => msc_etiquetas_tipo( __( 'Evento', 'msc' ), __( 'Eventos', 'msc' ), false ),
				'menu_icon'     => 'dashicons-calendar-alt',
				'menu_position' => 22,
			)
		)
	);

	register_post_type(
		'msc_publicacion',
		array_merge(
			$comun,
			array(
				'labels'        => msc_etiquetas_tipo( __( 'Publicación', 'msc' ), __( 'Publicaciones', 'msc' ) ),
				'menu_icon'     => 'dashicons-book-alt',
				'menu_position' => 23,
			)
		)
	);
}
add_action( 'init', 'msc_registrar_tipos', 5 );

/**
 * Registra las taxonomías.
 */
function msc_registrar_taxonomias() {
	register_taxonomy(
		'categoria_coleccion',
		'msc_coleccion',
		array(
			'labels'            => array(
				'name'          => __( 'Categorías de colección', 'msc' ),
				'singular_name' => __( 'Categoría de colección', 'msc' ),
				'menu_name'     => __( 'Categorías', 'msc' ),
				'add_new_item'  => __( 'Añadir categoría', 'msc' ),
				'edit_item'     => __( 'Editar categoría', 'msc' ),
			),
			'public'            => true,
			'hierarchical'      => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => false,
			'query_var'         => 'categoria_coleccion',
		)
	);

	register_taxonomy(
		'tipo_evento',
		'msc_evento',
		array(
			'labels'             => array(
				'name'          => __( 'Tipos de evento', 'msc' ),
				'singular_name' => __( 'Tipo de evento', 'msc' ),
				'add_new_item'  => __( 'Añadir tipo de evento', 'msc' ),
			),
			'public'             => true,
			'publicly_queryable' => false,
			'hierarchical'       => true,
			'show_admin_column'  => true,
			'show_in_rest'       => true,
			'meta_box_cb'        => false,
			'rewrite'            => false,
		)
	);

	register_taxonomy(
		'tipo_publicacion',
		'msc_publicacion',
		array(
			'labels'             => array(
				'name'          => __( 'Tipos de publicación', 'msc' ),
				'singular_name' => __( 'Tipo de publicación', 'msc' ),
			),
			'public'             => true,
			'publicly_queryable' => false,
			'hierarchical'       => true,
			'show_admin_column'  => true,
			'show_in_rest'       => true,
			'show_in_menu'       => false,
			'meta_box_cb'        => false,
			'rewrite'            => false,
		)
	);
}
add_action( 'init', 'msc_registrar_taxonomias', 5 );

/**
 * Plantillas de página que ofrece el plugin. El tema las dibuja; si el tema no existe,
 * se muestran con page.php y sus campos siguen disponibles.
 *
 * @return array<string, string>
 */
function msc_plantillas_pagina() {
	return array(
		'msc-el-museo'       => __( 'El museo', 'msc' ),
		'msc-colecciones'    => __( 'Colecciones (portada)', 'msc' ),
		'msc-exposiciones'   => __( 'Exposiciones (portada)', 'msc' ),
		'msc-colaboraciones' => __( 'Colaboraciones', 'msc' ),
		'msc-visitanos'      => __( 'Visítanos', 'msc' ),
	);
}

/**
 * Añade las plantillas del museo al selector de plantillas de página.
 *
 * @param array $plantillas Plantillas del tema.
 * @return array
 */
function msc_anadir_plantillas( $plantillas ) {
	return array_merge( $plantillas, msc_plantillas_pagina() );
}
add_filter( 'theme_page_templates', 'msc_anadir_plantillas' );
