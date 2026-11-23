<?php
/**
 * Ayudas del administrador: avisos, columnas, validaciones y página de redirecciones.
 *
 * @package msc-contenido
 */

defined( 'ABSPATH' ) || exit;

/**
 * Aviso si falta Secure Custom Fields / ACF.
 */
function msc_aviso_scf() {
	if ( msc_scf_activo() || ! current_user_can( 'activate_plugins' ) ) {
		return;
	}
	echo '<div class="notice notice-error"><p>' . wp_kses_post( __( '<strong>MSC Contenido</strong> necesita el plugin <strong>Secure Custom Fields</strong> (o ACF PRO) para editar los campos del museo. Instálalo desde Plugins → Añadir nuevo.', 'msc' ) ) . '</p></div>';
}
add_action( 'admin_notices', 'msc_aviso_scf' );

/**
 * Valida que todas las imágenes de la galería de una pieza tengan texto alternativo.
 *
 * @param bool|string $valido Resultado previo.
 * @param mixed       $valor  IDs de imágenes.
 * @return bool|string
 */
function msc_validar_alt_galeria( $valido, $valor ) {
	if ( true !== $valido || ! is_array( $valor ) ) {
		return $valido;
	}
	$sin_alt = array();
	foreach ( $valor as $id ) {
		$alt = trim( (string) get_post_meta( (int) $id, '_wp_attachment_image_alt', true ) );
		if ( '' === $alt ) {
			$sin_alt[] = get_the_title( (int) $id );
		}
	}
	if ( $sin_alt ) {
		/* translators: %s: lista de imágenes. */
		return sprintf( __( 'Estas imágenes no tienen texto alternativo: %s. Haz clic en cada imagen y escribe una descripción breve en "Texto alternativo".', 'msc' ), implode( ', ', $sin_alt ) );
	}
	return $valido;
}
add_filter( 'acf/validate_value/key=field_msc_coleccion_galeria', 'msc_validar_alt_galeria', 10, 2 );

/**
 * Tras guardar: avisa (sin bloquear) de párrafos de más de 80 palabras.
 *
 * @param int|string $post_id ID.
 */
function msc_revisar_parrafos( $post_id ) {
	if ( ! is_numeric( $post_id ) || ! function_exists( 'get_field_objects' ) ) {
		return;
	}
	$largos = array();
	$campos = get_field_objects( (int) $post_id, false );
	if ( ! is_array( $campos ) ) {
		return;
	}
	foreach ( $campos as $campo ) {
		if ( in_array( $campo['type'], array( 'wysiwyg', 'textarea' ), true ) && is_string( $campo['value'] ) ) {
			foreach ( msc_parrafos_largos( $campo['value'] ) as $n ) {
				$largos[] = $campo['label'] . ' (' . $n . ')';
			}
		}
	}
	if ( $largos ) {
		set_transient( 'msc_parrafos_' . get_current_user_id(), $largos, 60 );
	}
}
add_action( 'acf/save_post', 'msc_revisar_parrafos', 40 );

/**
 * Muestra el aviso de párrafos largos.
 */
function msc_aviso_parrafos() {
	$clave  = 'msc_parrafos_' . get_current_user_id();
	$largos = get_transient( $clave );
	if ( ! $largos ) {
		return;
	}
	delete_transient( $clave );
	echo '<div class="notice notice-warning is-dismissible"><p>' . esc_html__( 'Hay párrafos de más de 80 palabras. Para facilitar la lectura, divídelos con Intro:', 'msc' ) . ' ' . esc_html( implode( ', ', $largos ) ) . '</p></div>';
}
add_action( 'admin_notices', 'msc_aviso_parrafos' );

/**
 * Columnas de los listados del administrador.
 *
 * @param array $columnas Columnas.
 * @return array
 */
function msc_columnas_exposicion( $columnas ) {
	$columnas['msc_estado'] = __( 'Estado', 'msc' );
	$columnas['msc_fechas'] = __( 'Fechas', 'msc' );
	unset( $columnas['date'] );
	return $columnas;
}
add_filter( 'manage_msc_exposicion_posts_columns', 'msc_columnas_exposicion' );

/**
 * Columnas de eventos.
 *
 * @param array $columnas Columnas.
 * @return array
 */
function msc_columnas_evento( $columnas ) {
	$columnas['msc_fechas'] = __( 'Fecha y hora', 'msc' );
	$columnas['msc_estado'] = __( 'Estado', 'msc' );
	$columnas['msc_ficha']  = __( 'Ficha propia', 'msc' );
	unset( $columnas['date'] );
	return $columnas;
}
add_filter( 'manage_msc_evento_posts_columns', 'msc_columnas_evento' );

/**
 * Columnas de publicaciones.
 *
 * @param array $columnas Columnas.
 * @return array
 */
function msc_columnas_publicacion( $columnas ) {
	$columnas['msc_anio']      = __( 'Año', 'msc' );
	$columnas['msc_destacado'] = __( 'Calendario destacado', 'msc' );
	return $columnas;
}
add_filter( 'manage_msc_publicacion_posts_columns', 'msc_columnas_publicacion' );

/**
 * Columnas de piezas.
 *
 * @param array $columnas Columnas.
 * @return array
 */
function msc_columnas_coleccion( $columnas ) {
	$nuevas = array();
	foreach ( $columnas as $clave => $valor ) {
		if ( 'title' === $clave ) {
			$nuevas['msc_miniatura'] = '';
		}
		$nuevas[ $clave ] = $valor;
	}
	$nuevas['msc_destacada'] = __( 'Destacada', 'msc' );
	return $nuevas;
}
add_filter( 'manage_msc_coleccion_posts_columns', 'msc_columnas_coleccion' );

/**
 * Contenido de las columnas.
 *
 * @param string $columna Columna.
 * @param int    $id      ID.
 */
function msc_contenido_columnas( $columna, $id ) {
	switch ( $columna ) {
		case 'msc_estado':
			$estado = 'msc_exposicion' === get_post_type( $id ) ? msc_estado_exposicion( $id ) : msc_estado_evento( $id );
			echo esc_html( msc_etiqueta_estado( $estado ) );
			break;
		case 'msc_fechas':
			if ( 'msc_exposicion' === get_post_type( $id ) ) {
				echo esc_html( msc_rango_fechas( get_post_meta( $id, 'fecha_inicio', true ), get_post_meta( $id, 'fecha_cierre', true ) ) );
			} else {
				echo esc_html( msc_formatear_fecha( get_post_meta( $id, 'fecha_hora', true ), 'd/m/Y H:i' ) );
			}
			break;
		case 'msc_ficha':
			echo msc_evento_tiene_ficha( $id ) ? esc_html__( 'Sí', 'msc' ) : esc_html__( 'No (solo línea en la lista)', 'msc' );
			break;
		case 'msc_anio':
			$es_cal = 'calendario' === get_post_meta( $id, 'tipo', true );
			$valor  = get_post_meta( $id, $es_cal ? 'anio_civil' : 'anio', true );
			echo esc_html( (string) $valor );
			break;
		case 'msc_destacado':
			echo get_post_meta( $id, 'destacado', true ) ? '★' : '';
			break;
		case 'msc_destacada':
			echo get_post_meta( $id, 'destacada', true ) ? '★' : '';
			break;
		case 'msc_miniatura':
			$imagen = msc_imagen_principal( $id );
			if ( $imagen ) {
				echo wp_get_attachment_image( $imagen, array( 48, 48 ) );
			}
			break;
	}
}
add_action( 'manage_posts_custom_column', 'msc_contenido_columnas', 10, 2 );

/**
 * Página "Museo → Redirecciones".
 */
function msc_menu_redirecciones() {
	add_submenu_page(
		'msc-museo',
		__( 'Redirecciones 301', 'msc' ),
		__( 'Redirecciones', 'msc' ),
		'manage_options',
		'msc-redirecciones',
		'msc_pantalla_redirecciones'
	);
}
add_action( 'admin_menu', 'msc_menu_redirecciones', 20 );

/**
 * Pantalla de redirecciones.
 */
function msc_pantalla_redirecciones() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( isset( $_POST['msc_redirecciones_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['msc_redirecciones_nonce'] ) ), 'msc_redirecciones' ) ) {
		$texto = isset( $_POST['msc_redirecciones_manuales'] ) ? sanitize_textarea_field( wp_unslash( $_POST['msc_redirecciones_manuales'] ) ) : '';
		update_option( 'msc_redirecciones_manuales', $texto, false );
		msc_limpiar_cache();
		echo '<div class="notice notice-success"><p>' . esc_html__( 'Redirecciones guardadas.', 'msc' ) . '</p></div>';
	}
	$archivo = MSC_CONTENIDO_DIR . 'data/redirects.csv';
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Redirecciones 301 del sitio antiguo', 'msc' ); ?></h1>
		<p><?php esc_html_e( 'Las direcciones antiguas (.html) llevan a su página nueva. Si el destino es una ficha que todavía no existe, se usa el respaldo.', 'msc' ); ?></p>
		<form method="post">
			<?php wp_nonce_field( 'msc_redirecciones', 'msc_redirecciones_nonce' ); ?>
			<h2><?php esc_html_e( 'Reglas adicionales', 'msc' ); ?></h2>
			<p><?php esc_html_e( 'Una por línea: origen destino [respaldo]. Ejemplo: /viejo.html /exposiciones/ — tienen prioridad sobre la tabla.', 'msc' ); ?></p>
			<textarea name="msc_redirecciones_manuales" rows="8" class="large-text code"><?php echo esc_textarea( (string) get_option( 'msc_redirecciones_manuales', '' ) ); ?></textarea>
			<?php submit_button(); ?>
		</form>
		<h2><?php esc_html_e( 'Tabla incluida en el plugin (data/redirects.csv)', 'msc' ); ?></h2>
		<table class="widefat striped">
			<thead><tr><th><?php esc_html_e( 'Origen', 'msc' ); ?></th><th><?php esc_html_e( 'Destino', 'msc' ); ?></th><th><?php esc_html_e( 'Respaldo', 'msc' ); ?></th><th><?php esc_html_e( 'Estado', 'msc' ); ?></th><th><?php esc_html_e( 'Nota', 'msc' ); ?></th></tr></thead>
			<tbody>
			<?php
			if ( is_readable( $archivo ) ) {
				$filas = array_map(
					static function ( $l ) {
						return str_getcsv( $l, ',', '"', '\\' );
					},
					file( $archivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES )
				);
				array_shift( $filas );
				foreach ( $filas as $f ) {
					$f = array_pad( $f, 5, '' );
					echo '<tr><td><code>' . esc_html( $f[0] ) . '</code></td><td>' . esc_html( $f[1] ) . '</td><td>' . esc_html( $f[2] ) . '</td><td>' . esc_html( $f[3] ) . '</td><td>' . esc_html( $f[4] ) . '</td></tr>';
				}
			}
			?>
			</tbody>
		</table>
	</div>
	<?php
}

/**
 * Enlace al Inicio para cambiar el calendario destacado y los aliados (atajos del escritorio).
 */
function msc_widget_escritorio() {
	wp_add_dashboard_widget(
		'msc_atajos',
		__( 'Museo Sefardí: atajos', 'msc' ),
		static function () {
			$enlaces = array(
				admin_url( 'post-new.php?post_type=msc_coleccion' ) => __( 'Publicar una pieza', 'msc' ),
				admin_url( 'post-new.php?post_type=msc_exposicion' ) => __( 'Publicar una exposición', 'msc' ),
				admin_url( 'post-new.php?post_type=msc_evento' ) => __( 'Publicar un evento', 'msc' ),
				admin_url( 'post-new.php?post_type=msc_publicacion' ) => __( 'Publicar una publicación o calendario', 'msc' ),
				admin_url( 'edit.php?post_type=msc_publicacion' ) => __( 'Cambiar el calendario destacado (marca "Calendario destacado")', 'msc' ),
				admin_url( 'admin.php?page=msc-museo' ) => __( 'Datos del museo, horarios e instituciones aliadas', 'msc' ),
				admin_url( 'edit.php?post_type=msc_envio' ) => __( 'Ver envíos de formularios', 'msc' ),
			);
			echo '<ul>';
			foreach ( $enlaces as $url => $texto ) {
				echo '<li><a href="' . esc_url( $url ) . '">' . esc_html( $texto ) . '</a></li>';
			}
			echo '</ul>';
		}
	);
}
add_action( 'wp_dashboard_setup', 'msc_widget_escritorio' );

/**
 * El sitio no tiene Noticias ni comentarios: oculta "Entradas" y "Comentarios" del menú
 * para que el equipo no publique en secciones que no se muestran.
 * Desactivable con add_filter( 'msc_ocultar_entradas', '__return_false' ).
 */
function msc_simplificar_menu() {
	if ( ! apply_filters( 'msc_ocultar_entradas', true ) ) {
		return;
	}
	remove_menu_page( 'edit.php' );
	remove_menu_page( 'edit-comments.php' );
}
add_action( 'admin_menu', 'msc_simplificar_menu', 99 );

/**
 * Comentarios y pings cerrados en todo el sitio.
 */
add_filter( 'comments_open', '__return_false', 20 );
add_filter( 'pings_open', '__return_false', 20 );
