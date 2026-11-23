<?php
/**
 * Datos de ejemplo: se crean desde Herramientas → Datos de ejemplo MSC o con WP-CLI.
 *
 * Uso:
 *   wp msc seed [--reset]   Crea páginas, menú, opciones y contenido de prueba.
 *   wp msc limpiar          Borra el contenido de prueba.
 *   wp msc estados          Ejecuta la revisión diaria de estados.
 *
 * @package msc-contenido
 */

defined( 'ABSPATH' ) || exit;

/**
 * Generador de datos de ejemplo (usado por la pantalla del admin y por WP-CLI).
 */
class MSC_Datos_Ejemplo {

	/**
	 * Mensajes del proceso.
	 *
	 * @var string[]
	 */
	public $mensajes = array();

	/**
	 * Registra un mensaje.
	 *
	 * @param string $texto Mensaje.
	 */
	private function log( $texto ) {
		$this->mensajes[] = $texto;
	}

	/**
	 * Imágenes generadas en esta ejecución (por etiqueta).
	 *
	 * @var array<string, int>
	 */
	private $imagenes = array();

	/**
	 * Crea los datos de ejemplo.
	 *
	 * ## OPTIONS
	 *
	 * [--reset]
	 * : Borra antes el contenido de ejemplo existente.
	 *
	 * @param array $args  Argumentos.
	 * @param array $assoc Opciones.
	 */
	public function seed( $args, $assoc ) {
		if ( ! msc_scf_activo() ) {
			$this->log( 'Activa Secure Custom Fields antes de crear los datos de ejemplo.' );
			return false;
		}
		if ( function_exists( 'set_time_limit' ) ) {
			set_time_limit( 300 ); // phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged
		}
		if ( ! empty( $assoc['reset'] ) ) {
			$this->limpiar( array(), array() );
		}
		update_option( 'permalink_structure', '/%postname%/' );
		update_option( 'timezone_string', 'America/Caracas' );
		update_option( 'date_format', 'j \d\e F \d\e Y' );
		update_option( 'blogdescription', 'Preservando la memoria de la comunidad judía sefardí en Venezuela desde 1999.' );

		$this->terminos();
		$paginas = $this->paginas();
		$this->opciones();
		$this->menus( $paginas );
		$pubs   = $this->publicaciones();
		$piezas = $this->piezas();
		$expos  = $this->exposiciones( $pubs, $piezas );
		$this->eventos( $expos, $pubs );

		msc_registrar_reglas_url();
		flush_rewrite_rules( false );
		msc_limpiar_cache();
		msc_revisar_estados();
		$this->log( 'Datos de ejemplo creados.' );
		return true;
	}

	/**
	 * Borra el contenido de ejemplo (marcado con _msc_ejemplo).
	 *
	 * @param array $args  Argumentos.
	 * @param array $assoc Opciones.
	 */
	public function limpiar( $args, $assoc ) {
		$ids = get_posts(
			array(
				'post_type'      => 'any',
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'meta_key'       => '_msc_ejemplo', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'lang'           => '',
			)
		);
		foreach ( $ids as $id ) {
			wp_delete_post( $id, true );
		}
		$adjuntos = get_posts(
			array(
				'post_type'        => 'attachment',
				'post_status'      => 'any',
				'posts_per_page'   => -1,
				'fields'           => 'ids',
				'meta_key'         => '_msc_ejemplo', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'lang'             => '',
				'suppress_filters' => true,
			)
		);
		foreach ( $adjuntos as $id ) {
			wp_delete_attachment( $id, true );
		}
		$this->log( sprintf( 'Borradas %d entradas y %d imágenes de ejemplo.', count( $ids ), count( $adjuntos ) ) );
	}

	/**
	 * Ejecuta la revisión de estados (como el cron diario).
	 */
	public function estados() {
		$this->log( sprintf( 'Cambios de estado: %d', msc_revisar_estados() ) );
	}

	/**
	 * Genera (una vez) una imagen de marcador con GD.
	 *
	 * @param string $etiqueta Texto.
	 * @param int    $ancho    Ancho.
	 * @param int    $alto     Alto.
	 * @return int ID del adjunto.
	 */
	private function imagen( $etiqueta, $ancho = 1600, $alto = 1000 ) {
		$clave = $etiqueta . $ancho . 'x' . $alto;
		if ( isset( $this->imagenes[ $clave ] ) ) {
			return $this->imagenes[ $clave ];
		}
		if ( ! function_exists( 'imagecreatetruecolor' ) ) {
			return 0; // Sin la extensión GD los ejemplos se crean sin imágenes.
		}
		$im    = imagecreatetruecolor( $ancho, $alto );
		$fondo = imagecolorallocate( $im, 0xdf, 0xe4, 0xf0 );
		$borde = imagecolorallocate( $im, 0x8f, 0x99, 0xb8 );
		$tinta = imagecolorallocate( $im, 0x4a, 0x53, 0x68 );
		imagefill( $im, 0, 0, $fondo );
		for ( $i = 0; $i < 4; $i++ ) {
			imagerectangle( $im, $i, $i, $ancho - 1 - $i, $alto - 1 - $i, $borde );
		}
		$texto  = '[' . remove_accents( $etiqueta ) . ']';
		$fuente = 5;
		$escala = max( 1, (int) floor( $ancho / 400 ) );
		$tw     = imagefontwidth( $fuente ) * strlen( $texto );
		$th     = imagefontheight( $fuente );
		$capa   = imagecreatetruecolor( $tw, $th );
		imagefill( $capa, 0, 0, $fondo );
		imagestring( $capa, $fuente, 0, 0, $texto, $tinta );
		imagecopyresized( $im, $capa, (int) ( ( $ancho - $tw * $escala ) / 2 ), (int) ( ( $alto - $th * $escala ) / 2 ), 0, 0, $tw * $escala, $th * $escala, $tw, $th );
		ob_start();
		imagejpeg( $im, null, 82 );
		$bits   = ob_get_clean();
		$nombre = 'msc-ejemplo-' . sanitize_title( $etiqueta ) . '-' . $ancho . 'x' . $alto . '.jpg';
		$subida = wp_upload_bits( $nombre, null, $bits );
		if ( ! empty( $subida['error'] ) ) {
			$this->log( $subida['error'] );
			return 0;
		}
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$id = wp_insert_attachment(
			array(
				'post_mime_type' => 'image/jpeg',
				'post_title'     => $etiqueta,
				'post_name'      => 'msc-ejemplo-' . sanitize_title( $etiqueta ) . '-' . $ancho . 'x' . $alto,
				'post_status'    => 'inherit',
			),
			$subida['file']
		);
		wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $subida['file'] ) );
		update_post_meta( $id, '_wp_attachment_image_alt', $etiqueta . ' (imagen de ejemplo)' );
		update_post_meta( $id, '_msc_ejemplo', 1 );
		$this->imagenes[ $clave ] = $id;
		return $id;
	}

	/**
	 * Crea un PDF mínimo de ejemplo.
	 *
	 * @param string $titulo Título.
	 * @return int ID.
	 */
	private function pdf( $titulo ) {
		$texto   = str_replace( array( '(', ')', '\\' ), '', $titulo . ' - PDF de ejemplo' );
		$objs    = array(
			'<< /Type /Catalog /Pages 2 0 R >>',
			'<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
			'<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>',
			null,
			'<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
		);
		$flujo   = 'BT /F1 24 Tf 72 760 Td (' . $texto . ') Tj ET';
		$objs[3] = '<< /Length ' . strlen( $flujo ) . " >>\nstream\n" . $flujo . "\nendstream";
		$pdf     = "%PDF-1.4\n";
		$offs    = array();
		foreach ( $objs as $i => $o ) {
			$offs[] = strlen( $pdf );
			$pdf   .= ( $i + 1 ) . " 0 obj\n" . $o . "\nendobj\n";
		}
		$xref = strlen( $pdf );
		$pdf .= 'xref' . "\n0 " . ( count( $objs ) + 1 ) . "\n0000000000 65535 f \n";
		foreach ( $offs as $o ) {
			$pdf .= sprintf( "%010d 00000 n \n", $o );
		}
		$pdf   .= 'trailer << /Size ' . ( count( $objs ) + 1 ) . " /Root 1 0 R >>\nstartxref\n" . $xref . "\n%%EOF";
		$subida = wp_upload_bits( sanitize_title( $titulo ) . '-ejemplo.pdf', null, $pdf );
		$id     = wp_insert_attachment(
			array(
				'post_mime_type' => 'application/pdf',
				'post_title'     => $titulo,
				'post_name'      => 'msc-ejemplo-pdf-' . sanitize_title( $titulo ),
				'post_status'    => 'inherit',
			),
			$subida['file']
		);
		update_post_meta( $id, '_msc_ejemplo', 1 );
		return $id;
	}

	/**
	 * Crea o actualiza una entrada de ejemplo por slug.
	 *
	 * @param string $tipo   Tipo.
	 * @param string $titulo Título.
	 * @param array  $campos Campos SCF.
	 * @param array  $extra  Datos de wp_insert_post.
	 * @return int
	 */
	private function entrada( $tipo, $titulo, $campos = array(), $extra = array() ) {
		$slug      = $extra['post_name'] ?? sanitize_title( $titulo );
		$existente = get_page_by_path( $slug, OBJECT, $tipo );
		// get_page_by_path() también encuentra adjuntos con el mismo slug: solo vale el mismo tipo.
		if ( $existente && $existente->post_type !== $tipo ) {
			$existente = null;
		}
		$datos     = array_merge(
			array(
				'post_type'   => $tipo,
				'post_title'  => $titulo,
				'post_name'   => $slug,
				'post_status' => 'publish',
			),
			$extra
		);
		if ( $existente ) {
			$datos['ID'] = $existente->ID;
		}
		$id = wp_insert_post( $datos );
		update_post_meta( $id, '_msc_ejemplo', 1 );
		foreach ( $campos as $nombre => $valor ) {
			update_field( $nombre, $valor, $id );
		}
		do_action( 'acf/save_post', $id ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
		return $id;
	}

	/**
	 * Taxonomías.
	 */
	private function terminos() {
		$cats  = array(
			'libros'      => array( 'Libros', '#da2027' ),
			'documentos'  => array( 'Documentos', '#1b5f7a' ),
			'fotografias' => array( 'Fotografías', '#1a8ca0' ),
			'judaica'     => array( 'Judaica', '#cc942b' ),
			'arte'        => array( 'Arte', '#c01f62' ),
		);
		$orden = 0;
		foreach ( $cats as $slug => $d ) {
			$t = term_exists( $slug, 'categoria_coleccion' );
			if ( ! $t ) {
				$t = wp_insert_term(
					$d[0],
					'categoria_coleccion',
					array(
						'slug'        => $slug,
						'description' => '[Descripción breve de la categoría]',
					)
				);
			}
			$tid = (int) $t['term_id'];
			update_field( 'color_categoria', $d[1], 'categoria_coleccion_' . $tid );
			update_field( 'orden', ++$orden, 'categoria_coleccion_' . $tid );
			update_field( 'imagen_portada', $this->imagen( $d[0], 800, 600 ), 'categoria_coleccion_' . $tid );
		}
		foreach ( array( 'Charla', 'Presentación de libro', 'Concierto' ) as $tipo ) {
			if ( ! term_exists( sanitize_title( $tipo ), 'tipo_evento' ) ) {
				wp_insert_term( $tipo, 'tipo_evento' );
			}
		}
		foreach ( array(
			'libro'      => 'Libro / catálogo',
			'calendario' => 'Calendario',
		) as $slug => $nombre ) {
			if ( ! term_exists( $slug, 'tipo_publicacion' ) ) {
				wp_insert_term( $nombre, 'tipo_publicacion', array( 'slug' => $slug ) );
			}
		}
	}

	/**
	 * Páginas editoriales con sus plantillas y textos reales de docs/04.
	 *
	 * @return array<string, int>
	 */
	private function paginas() {
		$p = array();

		// La portada debe existir antes de guardar sus campos (su ubicación es "página de inicio").
		$p['inicio'] = $this->entrada( 'page', 'Inicio', array(), array( 'post_name' => 'inicio' ) );
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $p['inicio'] );

		$p['inicio'] = $this->entrada(
			'page',
			'Inicio',
			array(
				'hero_imagen'     => $this->imagen( 'Foto principal del museo', 2400, 1200 ),
				'hero_titulo'     => 'Museo Sefardí de Caracas Morris E. Curiel',
				'hero_frase'      => '[Frase breve de presentación]',
				'hero_boton1'     => 'Explora las colecciones',
				'hero_boton2'     => 'Planifica tu visita',
				'memoria_titulo'  => 'Memoria sefardí, de Sefarad a Venezuela',
				'memoria_texto'   => 'El Museo Sefardí de Caracas "Morris E. Curiel" fue fundado en 1999 para preservar la memoria de la comunidad judía sefardí, desde Sefarad hasta su arraigo en Venezuela. Está en el complejo de Maripérez, junto a la Gran Sinagoga Tiferet Israel, en Caracas.',
				'destacado_modo'  => 'auto',
				'espacios_titulo' => 'Tres espacios de encuentro y conocimiento',
				'espacios'        => array(
					array(
						'imagen' => $this->imagen( 'Sala de exposición permanente', 1200, 800 ),
						'titulo' => 'Sala de exposición permanente',
						'texto'  => 'Seis módulos sobre la huella y el arraigo de la comunidad sefardí en Venezuela.',
						'enlace' => '',
					),
					array(
						'imagen' => $this->imagen( 'Sala de lectura Isaac Chocrón', 1200, 800 ),
						'titulo' => 'Sala de lectura Isaac Chocrón',
						'texto'  => 'Un homenaje al dramaturgo venezolano, para la lectura y la tertulia literaria.',
						'enlace' => '',
					),
					array(
						'imagen' => $this->imagen( 'Biblioteca', 1200, 800 ),
						'titulo' => 'Biblioteca',
						'texto'  => 'Fondo documental especializado en judaísmo, historia sefardí y cultura universal.',
						'enlace' => '',
					),
				),
				'legado_titulo'   => 'Confía al museo la custodia de tu legado',
				'legado_texto'    => 'Invitamos a familias, coleccionistas e instituciones a confiar en el Museo Sefardí de Caracas la custodia definitiva de su legado.',
			),
			array( 'post_name' => 'inicio' )
		);
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $p['inicio'] );

		$p['museo'] = $this->entrada(
			'page',
			'El museo',
			array(
				'intro'                => '[Frase introductoria opcional sobre el museo]',
				'historia_imagen'      => $this->imagen( 'Foto del edificio del museo', 1600, 600 ),
				'historia_texto'       => "<p>El Museo Sefardí de Caracas \"Morris E. Curiel\" fue fundado en 1999 como una asociación civil sin fines de lucro adscrita a la Asociación Israelita de Venezuela, institución matriz de la comunidad judía sefardí fundada en 1930.</p>\n<p>Ubicado en el complejo de Maripérez en Caracas, junto a la Gran Sinagoga Tiferet Israel, el museo se sitúa dentro del eje cultural capitalino que reúne a importantes instituciones artísticas y culturales del país.</p>",
				'fundador_imagen'      => $this->imagen( 'Retrato de Morris E. Curiel', 1600, 600 ),
				'fundador_nombre'      => 'Morris E. Curiel (1920–2007)',
				'fundador_texto'       => 'Descendiente de una familia sefardí originaria de Curazao, empresario, banquero y filántropo.',
				'mision'               => 'Investigar, recolectar, conservar, interpretar y exhibir el patrimonio material e inmaterial de la comunidad judía sefardí.',
				'vision'               => 'Consolidarse como una institución museística de referencia nacional e internacional en la preservación y divulgación de la memoria sefardí.',
				'espacios_intro'       => '[Introducción a los espacios]',
				'espacios'             => array(
					array(
						'imagen'       => $this->imagen( 'Sala de exposición permanente', 1600, 500 ),
						'titulo'       => 'Sala de exposición permanente',
						'texto'        => 'Seis módulos sobre la huella y el arraigo de la comunidad sefardí en Venezuela.',
						'enlace_texto' => 'Ver la sala permanente en Exposiciones',
						'enlace_url'   => home_url( '/exposiciones/#sala-permanente' ),
					),
					array(
						'imagen' => $this->imagen( 'Sala de lectura Isaac Chocrón', 1600, 500 ),
						'titulo' => 'Sala de lectura Isaac Chocrón',
						'texto'  => 'Un homenaje al dramaturgo venezolano, para la lectura y la tertulia literaria.',
					),
					array(
						'imagen' => $this->imagen( 'Biblioteca', 1600, 500 ),
						'titulo' => 'Biblioteca',
						'texto'  => 'Fondo documental especializado en judaísmo, historia sefardí y cultura universal.',
					),
				),
				'consejo_museo_titulo' => 'Consejo directivo del Museo',
				'consejo_museo'        => array(
					array(
						'nombre' => 'Dr. Abraham Levy Benshimol',
						'cargo'  => 'Presidente',
					),
					array(
						'nombre' => 'Priscila Abecasis de Bigio',
						'cargo'  => 'Vicepresidente',
					),
					array(
						'nombre' => 'Anny Cudisevich Echerman',
						'cargo'  => 'Directora Ejecutiva',
					),
					array(
						'nombre' => '[Por completar]',
						'cargo'  => '[Cargo]',
					),
				),
				'consejo_aiv_titulo'   => 'Consejo directivo de la Asociación Israelita de Venezuela',
				'consejo_aiv'          => array(
					array(
						'nombre' => 'Dr. Isaac Benjamin Fhima',
						'cargo'  => 'Presidente',
					),
					array(
						'nombre' => 'Samuel Chocrón Nahon',
						'cargo'  => 'Primer vicepresidente',
					),
					array(
						'nombre' => '[Por completar]',
						'cargo'  => '[Cargo]',
					),
				),
			),
			array(
				'post_name'  => 'el-museo',
				'menu_order' => 1,
				'meta_input' => array( '_wp_page_template' => 'msc-el-museo' ),
			)
		);

		$p['colecciones'] = $this->entrada(
			'page',
			'Colecciones',
			array( 'intro' => '[Texto introductorio, hasta 80 palabras]' ),
			array(
				'post_name'  => 'colecciones',
				'menu_order' => 2,
				'meta_input' => array( '_wp_page_template' => 'msc-colecciones' ),
			)
		);

		$p['exposiciones'] = $this->entrada(
			'page',
			'Exposiciones',
			array(
				'intro'       => '[Texto introductorio]',
				'sala_imagen' => $this->imagen( 'Foto de la sala', 1600, 600 ),
				'sala_texto'  => '<p>[Texto de la sala permanente · máx. 80 palabras por párrafo]</p>',
				'sala_boton1' => 'Conocer la sala',
				'sala_boton2' => 'Planifica tu visita',
			),
			array(
				'post_name'  => 'exposiciones',
				'menu_order' => 3,
				'meta_input' => array( '_wp_page_template' => 'msc-exposiciones' ),
			)
		);

		$p['colaboraciones'] = $this->entrada(
			'page',
			'Colaboraciones',
			array(
				'intro'       => '[Texto introductorio]',
				'modalidades' => array(
					array(
						'titulo' => 'Donación',
						'texto'  => '[Texto de la modalidad]',
						'ancla'  => 'donacion',
					),
					array(
						'titulo' => 'Adquisición',
						'texto'  => '[Texto de la modalidad]',
						'ancla'  => 'adquisicion',
					),
					array(
						'titulo' => 'Comodato',
						'texto'  => '[Texto de la modalidad]',
						'ancla'  => 'comodato',
					),
				),
				'pasos'       => array(
					array(
						'titulo' => '[Paso 1]',
						'texto'  => '[Descripción breve]',
					),
					array(
						'titulo' => '[Paso 2]',
						'texto'  => '[Descripción breve]',
					),
					array(
						'titulo' => '[Paso 3]',
						'texto'  => '[Descripción breve]',
					),
				),
			),
			array(
				'post_name'  => 'colaboraciones',
				'menu_order' => 6,
				'meta_input' => array( '_wp_page_template' => 'msc-colaboraciones' ),
			)
		);

		$p['visitanos'] = $this->entrada(
			'page',
			'Visítanos',
			array(
				'intro'           => '[Texto introductorio]',
				'que_encontraras' => array(
					array(
						'imagen' => $this->imagen( 'Sala permanente', 800, 500 ),
						'titulo' => 'Sala permanente',
						'enlace' => home_url( '/exposiciones/#sala-permanente' ),
					),
					array(
						'imagen' => $this->imagen( 'Exposiciones', 800, 500 ),
						'titulo' => 'Exposiciones',
						'enlace' => home_url( '/exposiciones/' ),
					),
					array(
						'imagen' => $this->imagen( 'Colecciones', 800, 500 ),
						'titulo' => 'Colecciones',
						'enlace' => home_url( '/colecciones/' ),
					),
					array(
						'imagen' => $this->imagen( 'Eventos', 800, 500 ),
						'titulo' => 'Eventos',
						'enlace' => home_url( '/eventos/' ),
					),
				),
			),
			array(
				'post_name'  => 'visitanos',
				'menu_order' => 7,
				'meta_input' => array( '_wp_page_template' => 'msc-visitanos' ),
			)
		);

		$p['privacidad'] = $this->entrada(
			'page',
			'Política de privacidad',
			array(),
			array(
				'post_name'    => 'politica-de-privacidad',
				'menu_order'   => 9,
				'post_content' => "<!-- wp:paragraph -->\n<p>[Texto de la política de privacidad por confirmar con el museo.]</p>\n<!-- /wp:paragraph -->",
			)
		);
		update_option( 'wp_page_for_privacy_policy', $p['privacidad'] );
		return $p;
	}

	/**
	 * Opciones globales del museo.
	 */
	private function opciones() {
		$valores = array(
			'direccion'              => 'Paseo Colón con Av. Principal de Maripérez, complejo Gran Sinagoga Tiferet Israel / AIV, Caracas',
			'direccion_corta'        => 'Paseo Colón con Av. Principal de Maripérez, Caracas',
			'telefono'               => '+58 212 5781489',
			'whatsapp'               => '+58 424 2787933',
			'instagram'              => 'museosefardiccs',
			'mapa_enlace'            => 'https://www.openstreetmap.org/search?query=Gran%20Sinagoga%20Tiferet%20Israel%2C%20Caracas',
			'formas_adquisicion'     => "Donación\nCompra\nComodato",
			'calendarios_anteriores' => 4,
			'intro_eventos'          => '[Texto introductorio]',
			'intro_publicaciones'    => '[Texto introductorio]',
			'intro_pasadas'          => '[Texto introductorio]',
			'intro_calendarios'      => '[Texto introductorio]',
			'boletin_texto'          => '[Texto breve del boletín]',
			'formularios_copia'      => 1,
			'adjuntos_maximo'        => 3,
			'adjuntos_mb'            => 5,
			'aliados'                => array(
				array( 'nombre' => 'Asociación Israelita de Venezuela' ),
				array( 'nombre' => 'Hebraica' ),
				array( 'nombre' => 'CAIV' ),
				array( 'nombre' => 'Nuevo Mundo Israelita' ),
				array( 'nombre' => 'Revista Maguén-Escudo' ),
			),
		);
		foreach ( $valores as $nombre => $valor ) {
			update_field( $nombre, $valor, 'option' );
		}
	}

	/**
	 * Menús: principal y pie.
	 *
	 * @param array $p Páginas.
	 */
	private function menus( $p ) {
		$menu = wp_get_nav_menu_object( 'Principal' );
		$id   = $menu ? $menu->term_id : wp_create_nav_menu( 'Principal' );
		foreach ( wp_get_nav_menu_items( $id ) ? wp_get_nav_menu_items( $id ) : array() as $item ) {
			wp_delete_post( $item->ID, true );
		}
		$elementos = array(
			array( 'page', $p['museo'] ),
			array( 'page', $p['colecciones'] ),
			array( 'page', $p['exposiciones'] ),
			array( 'custom', home_url( '/eventos/' ), 'Eventos' ),
			array( 'custom', home_url( '/publicaciones/' ), 'Publicaciones' ),
			array( 'page', $p['colaboraciones'] ),
			array( 'page', $p['visitanos'] ),
		);
		$orden     = 0;
		foreach ( $elementos as $e ) {
			$datos = array(
				'menu-item-status'   => 'publish',
				'menu-item-position' => ++$orden,
			);
			if ( 'page' === $e[0] ) {
				$datos['menu-item-type']      = 'post_type';
				$datos['menu-item-object']    = 'page';
				$datos['menu-item-object-id'] = $e[1];
			} else {
				$datos['menu-item-type']  = 'custom';
				$datos['menu-item-url']   = $e[1];
				$datos['menu-item-title'] = $e[2];
			}
			wp_update_nav_menu_item( $id, 0, $datos );
		}
		$ubicaciones              = get_theme_mod( 'nav_menu_locations', array() );
		$ubicaciones['principal'] = $id;
		set_theme_mod( 'nav_menu_locations', $ubicaciones );
	}

	/**
	 * Publicaciones y calendarios.
	 *
	 * @return int[]
	 */
	private function publicaciones() {
		$ids = array();
		$cal = array(
			array( '2026', '5787', true ),
			array( '2025', '5786', false ),
			array( '2024', '5785', false ),
			array( '2023', '5784', false ),
			array( '2022', '5783', false ),
		);
		foreach ( $cal as $c ) {
			$this->entrada(
				'msc_publicacion',
				'Calendario ' . $c[0],
				array(
					'tipo'        => 'calendario',
					'portada'     => $this->imagen( 'Portada del calendario ' . $c[0], 800, 1100 ),
					'subtitulo'   => 'Publicación anual del museo',
					'anio_civil'  => $c[0],
					'anio_hebreo' => $c[1],
					'pdf'         => $this->pdf( 'Calendario ' . $c[0] ),
					'destacado'   => $c[2] ? 1 : 0,
					'resena'      => '<p>[Descripción breve del calendario de este año]</p>',
				),
				array( 'post_name' => 'calendario-' . $c[0] )
			);
		}
		for ( $i = 1; $i <= 10; $i++ ) {
			$ids[] = $this->entrada(
				'msc_publicacion',
				sprintf( '[Publicación de ejemplo %02d]', $i ),
				array(
					'tipo'      => 'libro',
					'portada'   => $this->imagen( 'Portada ' . $i, 800, 1100 ),
					'subtitulo' => '[Subtítulo]',
					'anio'      => 2024 - $i,
					'paginas'   => 100 + $i * 8,
					'formato'   => '[Formato]',
					'tirada'    => 1 === $i % 2 ? 500 : '',
					'creditos'  => array(
						array(
							'rol'    => '[Rol]',
							'nombre' => '[Nombre]',
						),
						array(
							'rol'    => '[Rol]',
							'nombre' => '[Nombre]',
						),
					),
					'resena'    => 1 === $i % 3 ? '' : '<p>[Reseña · opcional]</p>',
				),
				array( 'post_name' => sprintf( 'publicacion-ejemplo-%02d', $i ) )
			);
		}
		return $ids;
	}

	/**
	 * Piezas de colección.
	 *
	 * @return int[]
	 */
	private function piezas() {
		$ids    = array();
		$formas = array( 'Donación', 'Compra', 'Comodato' );
		$plan   = array(
			'libros'      => 14,
			'documentos'  => 3,
			'fotografias' => 3,
			'judaica'     => 3,
			'arte'        => 3,
		);
		$n      = 0;
		foreach ( $plan as $cat => $cantidad ) {
			$termino = get_term_by( 'slug', $cat, 'categoria_coleccion' );
			for ( $i = 1; $i <= $cantidad; $i++ ) {
				++$n;
				$galeria = array(
					$this->imagen( 'Imagen principal de la pieza', 1600, 1000 ),
					$this->imagen( 'Detalle 1', 1200, 1200 ),
					$this->imagen( 'Detalle 2', 1200, 1200 ),
					$this->imagen( 'Detalle 3', 1200, 1200 ),
				);
				$id      = $this->entrada(
					'msc_coleccion',
					sprintf( '[Pieza de ejemplo %02d]', $n ),
					array(
						'galeria'           => $galeria,
						'descripcion'       => '<p>[Descripción · párrafos de máx. 80 palabras]</p>',
						'dimensiones'       => '[Alto × ancho × prof.]',
						'forma_adquisicion' => $formas[ $n % 3 ],
						'destacada'         => 1 === $n % 6 ? 1 : 0,
						'numero_inventario' => 1 === $i ? '[Código]' : '',
						'epoca'             => '[Siglo / año]',
						'autor_procedencia' => '[Nombre / lugar]',
						'material_tecnica'  => 0 === $n % 2 ? '[Descripción]' : '',
						'credito_donante'   => 'Donación' === $formas[ $n % 3 ] ? '[Nombre o familia]' : '',
					),
					array(
						'post_name' => sprintf( 'pieza-ejemplo-%02d', $n ),
						'post_date' => gmdate( 'Y-m-d H:i:s', time() - $n * DAY_IN_SECONDS ),
					)
				);
				wp_set_object_terms( $id, array( $termino->term_id ), 'categoria_coleccion' );
				$ids[] = $id;
			}
		}
		return $ids;
	}

	/**
	 * Exposiciones (vigente, próxima y pasadas; títulos reales de las pasadas según docs/04).
	 *
	 * @param int[] $pubs   Publicaciones.
	 * @param int[] $piezas Piezas.
	 * @return int[]
	 */
	private function exposiciones( $pubs, $piezas ) {
		$hoy   = msc_ahora();
		$ids   = array();
		$base  = array(
			'sede'      => array( array( 'nombre' => '[Nombre de la sede]' ) ),
			'horario'   => '[Horario]',
			'texto'     => '<p>[Texto · párrafos de máx. 80 palabras]</p>',
			'secciones' => array_map(
				function ( $i ) {
					return array(
						'titulo' => '[Sección ' . $i . ']',
						'texto'  => '[Texto breve]',
						'imagen' => $this->imagen( 'Sección ' . $i, 800, 500 ),
					);
				},
				range( 1, 6 )
			),
			'creditos'  => array(
				array(
					'rol'    => '[Rol]',
					'nombre' => '[Nombre]',
				),
				array(
					'rol'    => '[Rol]',
					'nombre' => '[Nombre]',
				),
				array(
					'rol'    => '[Rol]',
					'nombre' => '[Nombre]',
				),
			),
			'galeria'   => array( $this->imagen( 'Foto 1', 1200, 800 ), $this->imagen( 'Foto 2', 1200, 800 ), $this->imagen( 'Foto 3', 1200, 800 ) ),
		);
		$lista = array(
			array( '[Exposición actual de ejemplo]', 'exposicion-actual-ejemplo', $hoy->modify( '-30 days' ), $hoy->modify( '+60 days' ) ),
			array( '[Próxima exposición de ejemplo]', 'proxima-exposicion-ejemplo', $hoy->modify( '+90 days' ), $hoy->modify( '+180 days' ) ),
			array( 'Janucá', 'januca', new DateTimeImmutable( '2024-12-01' ), new DateTimeImmutable( '2024-12-31' ) ),
			array( 'Calendario 5784', 'calendario-5784', new DateTimeImmutable( '2023-09-01' ), new DateTimeImmutable( '2023-12-15' ) ),
			array( 'Sefardíes de Curazao a Coro', 'sefardies-de-curazao-a-coro', new DateTimeImmutable( '2022-05-01' ), new DateTimeImmutable( '2022-09-30' ) ),
			array( 'Los Pardo', 'los-pardo', new DateTimeImmutable( '2019-11-17' ), new DateTimeImmutable( '2020-02-28' ) ),
		);
		for ( $i = 1; $i <= 6; $i++ ) {
			$anio    = 2019 - $i;
			$lista[] = array( sprintf( '[Exposición pasada de ejemplo %d]', $i ), 'exposicion-pasada-ejemplo-' . $i, new DateTimeImmutable( $anio . '-03-01' ), new DateTimeImmutable( $anio . '-06-30' ) );
		}
		foreach ( $lista as $k => $e ) {
			$campos = array_merge(
				$base,
				array(
					'imagen_principal'        => $this->imagen( 'Imagen de la exposición', 1600, 900 ),
					'fecha_inicio'            => $e[2]->format( 'Ymd' ),
					'fecha_cierre'            => $e[3]->format( 'Ymd' ),
					'video'                   => 0 === $k ? 'https://www.youtube.com/watch?v=dQw4w9WgXcQ' : '',
					'publicacion_relacionada' => array( $pubs[ $k % count( $pubs ) ] ),
					'piezas_relacionadas'     => array( $piezas[ $k % count( $piezas ) ] ),
				)
			);
			$ids[]  = $this->entrada( 'msc_exposicion', $e[0], $campos, array( 'post_name' => $e[1] ) );
		}
		return $ids;
	}

	/**
	 * Eventos (próximos con ficha; pasados con y sin ficha).
	 *
	 * @param int[] $expos Exposiciones.
	 * @param int[] $pubs  Publicaciones.
	 */
	private function eventos( $expos, $pubs ) {
		$hoy   = msc_ahora();
		$tipos = get_terms(
			array(
				'taxonomy'   => 'tipo_evento',
				'hide_empty' => false,
				'fields'     => 'ids',
			)
		);
		$lista = array(
			array( '+12 days 11:00', true ),
			array( '+40 days 18:30', true ),
			array( '-20 days 11:00', true ),
			array( '-75 days 11:00', true ),
			array( '-120 days 19:00', false ),
			array( '-400 days 11:00', true ),
			array( '-430 days 11:00', false ),
			array( '-500 days 18:00', false ),
		);
		foreach ( $lista as $i => $e ) {
			$fecha  = $hoy->modify( $e[0] );
			$evento = $this->entrada(
				'msc_evento',
				$e[1] ? sprintf( '[Evento de ejemplo %d con ficha]', $i + 1 ) : sprintf( '[Evento de ejemplo %d sin ficha]', $i + 1 ),
				array(
					'tipo'         => $tipos[ $i % count( $tipos ) ],
					'fecha_hora'   => $fecha->format( 'Y-m-d H:i:s' ),
					'lugar'        => '[Lugar]',
					'ponentes'     => array( array( 'nombre' => '[Nombre del ponente]' ) ),
					'imagen'       => $this->imagen( 'Imagen del evento', 1600, 900 ),
					'descripcion'  => $e[1] ? '<p>[Descripción · párrafos de máx. 80 palabras]</p>' : '',
					'galeria'      => $e[1] && $i > 1 ? array( $this->imagen( 'Foto 1', 1200, 800 ), $this->imagen( 'Foto 2', 1200, 800 ), $this->imagen( 'Foto 3', 1200, 800 ) ) : array(),
					'relacionados' => $e[1] ? array( $expos[ $i % count( $expos ) ] ) : array(),
				),
				array( 'post_name' => 'evento-ejemplo-' . ( $i + 1 ) )
			);
			wp_set_object_terms( $evento, array( (int) $tipos[ $i % count( $tipos ) ] ), 'tipo_evento' );
		}
	}
}
