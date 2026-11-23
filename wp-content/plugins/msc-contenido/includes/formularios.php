<?php
/**
 * Formularios propios: Colaboraciones, Visita guiada y Boletín.
 * Validación en servidor (y en cliente con HTML5), nonce, honeypot, tiempo mínimo y límite por IP.
 * Los envíos se guardan en el administrador (Museo → Envíos) y se notifican por correo.
 *
 * Para ampliar los campos cuando el museo los defina, edita msc_definicion_formulario()
 * o usa el filtro "msc_formulario_campos".
 *
 * @package msc-contenido
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registra el tipo "Envío de formulario" (privado).
 */
function msc_registrar_tipo_envio() {
	register_post_type(
		'msc_envio',
		array(
			'labels'          => array(
				'name'          => __( 'Envíos de formularios', 'msc' ),
				'singular_name' => __( 'Envío', 'msc' ),
				'menu_name'     => __( 'Envíos', 'msc' ),
				'edit_item'     => __( 'Ver envío', 'msc' ),
				'not_found'     => __( 'Todavía no hay envíos.', 'msc' ),
			),
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => 'msc-museo',
			'supports'        => array( 'title', 'editor' ),
			'capability_type' => 'page',
			'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
			'map_meta_cap'    => true,
			'rewrite'         => false,
		)
	);
}
add_action( 'init', 'msc_registrar_tipo_envio', 5 );

/**
 * Definición de los formularios.
 *
 * Tipos de campo: text, email, tel, number, date, textarea, radio, select, checkbox, file.
 * "condicion" => 'tipo:donacion' muestra el campo solo si se eligió esa opción (con JS);
 * sin JS se muestran todos, con su condición indicada en la etiqueta.
 *
 * @param string $id colaboracion|visita|boletin.
 * @return array
 */
function msc_definicion_formulario( $id ) {
	$privacidad  = array(
		'tipo'      => 'checkbox',
		'etiqueta'  => __( 'Acepto la política de privacidad', 'msc' ),
		'requerido' => true,
		'enlace'    => 'privacidad',
	);
	$formularios = array(
		'colaboracion' => array(
			'titulo' => __( 'Propuesta de colaboración', 'msc' ),
			'boton'  => __( 'Enviar propuesta', 'msc' ),
			'campos' => array(
				'tipo'           => array(
					'tipo'      => 'radio',
					'etiqueta'  => __( 'Tipo de colaboración', 'msc' ),
					'requerido' => true,
					'opciones'  => array(
						'donacion'    => __( 'Donación', 'msc' ),
						'adquisicion' => __( 'Adquisición', 'msc' ),
						'comodato'    => __( 'Comodato', 'msc' ),
					),
				),
				// Campos comunes de ejemplo: [por confirmar con el museo].
				'nombre'         => array(
					'tipo'         => 'text',
					'etiqueta'     => __( 'Nombre y apellido', 'msc' ),
					'requerido'    => true,
					'autocomplete' => 'name',
				),
				'correo'         => array(
					'tipo'         => 'email',
					'etiqueta'     => __( 'Correo electrónico', 'msc' ),
					'requerido'    => true,
					'autocomplete' => 'email',
				),
				'telefono'       => array(
					'tipo'         => 'tel',
					'etiqueta'     => __( 'Teléfono', 'msc' ),
					'autocomplete' => 'tel',
				),
				'ciudad'         => array(
					'tipo'         => 'text',
					'etiqueta'     => __( 'Ciudad y país', 'msc' ),
					'autocomplete' => 'address-level2',
				),
				// Bloques condicionales de ejemplo: [por confirmar con el museo].
				'procedencia'    => array(
					'tipo'      => 'textarea',
					'etiqueta'  => __( 'Procedencia o historia de la pieza', 'msc' ),
					'condicion' => 'tipo:donacion',
					'filas'     => 3,
				),
				'valor_estimado' => array(
					'tipo'      => 'text',
					'etiqueta'  => __( 'Valor estimado (opcional)', 'msc' ),
					'condicion' => 'tipo:adquisicion',
				),
				'plazo'          => array(
					'tipo'      => 'text',
					'etiqueta'  => __( 'Plazo propuesto del comodato', 'msc' ),
					'condicion' => 'tipo:comodato',
				),
				'mensaje'        => array(
					'tipo'        => 'textarea',
					'etiqueta'    => __( 'Mensaje', 'msc' ),
					'placeholder' => __( 'Describe la pieza o propuesta', 'msc' ),
					'requerido'   => true,
					'filas'       => 5,
				),
				'fotos'          => array(
					'tipo'     => 'file',
					'etiqueta' => __( 'Fotos de la pieza (opcional)', 'msc' ),
					'multiple' => true,
					'aceptar'  => 'image/jpeg,image/png,image/webp',
				),
				'privacidad'     => $privacidad,
			),
		),
		'visita'       => array(
			'titulo' => __( 'Solicitud de visita guiada', 'msc' ),
			'boton'  => __( 'Enviar solicitud', 'msc' ),
			'campos' => array(
				'nombre'     => array(
					'tipo'         => 'text',
					'etiqueta'     => __( 'Nombre', 'msc' ),
					'requerido'    => true,
					'autocomplete' => 'name',
				),
				'correo'     => array(
					'tipo'         => 'email',
					'etiqueta'     => __( 'Correo', 'msc' ),
					'requerido'    => true,
					'autocomplete' => 'email',
				),
				'grupo'      => array(
					'tipo'     => 'text',
					'etiqueta' => __( 'Grupo / institución', 'msc' ),
				),
				'personas'   => array(
					'tipo'     => 'number',
					'etiqueta' => __( 'Número de personas', 'msc' ),
					'min'      => 1,
					'max'      => 200,
				),
				'fecha'      => array(
					'tipo'     => 'date',
					'etiqueta' => __( 'Fecha deseada', 'msc' ),
				),
				'mensaje'    => array(
					'tipo'     => 'textarea',
					'etiqueta' => __( 'Mensaje', 'msc' ),
					'filas'    => 4,
				),
				'privacidad' => $privacidad,
			),
		),
		'boletin'      => array(
			'titulo' => __( 'Suscripción al boletín', 'msc' ),
			'boton'  => __( 'Suscribirme', 'msc' ),
			'campos' => array(
				'correo'     => array(
					'tipo'         => 'email',
					'etiqueta'     => __( 'Correo electrónico', 'msc' ),
					'placeholder'  => 'nombre@correo.com',
					'requerido'    => true,
					'autocomplete' => 'email',
				),
				'privacidad' => array_merge( $privacidad, array( 'etiqueta' => __( 'Acepto la política de privacidad.', 'msc' ) ) ),
			),
		),
	);
	if ( ! isset( $formularios[ $id ] ) ) {
		return array();
	}
	$definicion           = $formularios[ $id ];
	$definicion['campos'] = apply_filters( 'msc_formulario_campos', $definicion['campos'], $id );
	return apply_filters( 'msc_formulario_definicion', $definicion, $id );
}

/**
 * Firma del tiempo de carga (para descartar envíos automáticos demasiado rápidos).
 *
 * @param int $t Marca de tiempo.
 * @return string
 */
function msc_firma_tiempo( $t ) {
	return $t . '.' . substr( wp_hash( 'msc_form_' . $t ), 0, 12 );
}

/**
 * Dibuja un formulario.
 *
 * @param string $id       Formulario.
 * @param array  $opciones variante (claro|oscuro|pie), id_html, mostrar_titulo.
 * @return string HTML.
 */
function msc_formulario( $id, $opciones = array() ) {
	$def = msc_definicion_formulario( $id );
	if ( ! $def ) {
		return '';
	}
	$opciones = wp_parse_args(
		$opciones,
		array(
			'variante' => 'claro',
			'id_html'  => 'form-' . $id,
		)
	);
	$id_html  = sanitize_html_class( $opciones['id_html'] );

	// Estado tras el envío (patrón PRG).
	$errores = array();
	$valores = array();
	$exito   = false;
	// phpcs:disable WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['msc_form'], $_GET['msc_id'] ) && sanitize_key( wp_unslash( $_GET['msc_id'] ) ) === $id_html ) {
		$estado = get_transient( 'msc_form_' . sanitize_key( wp_unslash( $_GET['msc_form'] ) ) );
		if ( is_array( $estado ) ) {
			$errores = $estado['errores'];
			$valores = $estado['valores'];
		}
	}
	if ( isset( $_GET['msc_enviado'] ) && sanitize_key( wp_unslash( $_GET['msc_enviado'] ) ) === $id_html ) {
		$exito = true;
	}
	// phpcs:enable

	$tiene_archivos = false;
	foreach ( $def['campos'] as $campo ) {
		if ( 'file' === $campo['tipo'] ) {
			$tiene_archivos = true;
		}
	}

	ob_start();
	?>
	<form id="<?php echo esc_attr( $id_html ); ?>" class="msc-form msc-form--<?php echo esc_attr( $id ); ?> msc-form--<?php echo esc_attr( $opciones['variante'] ); ?>" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post"<?php echo $tiene_archivos ? ' enctype="multipart/form-data"' : ''; ?> data-msc-form novalidate>
		<div class="msc-form__estado" role="status" aria-live="polite" tabindex="-1">
			<?php if ( $exito ) : ?>
				<p class="msc-aviso msc-aviso--ok"><?php echo esc_html( 'boletin' === $id ? __( 'Gracias. Tu suscripción quedó registrada.', 'msc' ) : __( 'Gracias. Recibimos tu mensaje y te responderemos pronto.', 'msc' ) ); ?></p>
			<?php elseif ( $errores ) : ?>
				<div class="msc-aviso msc-aviso--error">
					<p><?php esc_html_e( 'Revisa los campos marcados:', 'msc' ); ?></p>
					<ul>
						<?php foreach ( $errores as $campo_id => $mensaje ) : ?>
							<li>
								<?php if ( 'general' === $campo_id ) : ?>
									<?php echo esc_html( $mensaje ); ?>
								<?php else : ?>
									<a href="#<?php echo esc_attr( $id_html . '-' . $campo_id ); ?>"><?php echo esc_html( $mensaje ); ?></a>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
		</div>
		<input type="hidden" name="action" value="msc_formulario">
		<input type="hidden" name="msc_formulario" value="<?php echo esc_attr( $id ); ?>">
		<input type="hidden" name="msc_id_html" value="<?php echo esc_attr( $id_html ); ?>">
		<input type="hidden" name="msc_t" value="<?php echo esc_attr( msc_firma_tiempo( time() ) ); ?>">
		<input type="hidden" name="msc_volver" value="<?php echo esc_url( msc_url_peticion() ); ?>">
		<?php wp_nonce_field( 'msc_formulario_' . $id, 'msc_nonce' ); ?>
		<div class="msc-form__trampa" aria-hidden="true">
			<label for="<?php echo esc_attr( $id_html ); ?>-web"><?php esc_html_e( 'No rellenes este campo', 'msc' ); ?></label>
			<input type="text" id="<?php echo esc_attr( $id_html ); ?>-web" name="msc_web" value="" tabindex="-1" autocomplete="off">
		</div>
		<?php
		foreach ( $def['campos'] as $campo_id => $campo ) {
			echo msc_campo_formulario( $id_html, $campo_id, $campo, $valores[ $campo_id ] ?? '', $errores[ $campo_id ] ?? '', $def ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		?>
		<button type="submit" class="msc-btn <?php echo 'oscuro' === $opciones['variante'] ? 'msc-btn--dorado' : 'msc-btn--primario'; ?> msc-form__enviar"><?php echo esc_html( $def['boton'] ); ?></button>
	</form>
	<?php
	return (string) ob_get_clean();
}

/**
 * HTML de un campo.
 *
 * @param string $id_html  ID del formulario.
 * @param string $campo_id Clave.
 * @param array  $campo    Definición.
 * @param mixed  $valor    Valor previo.
 * @param string $error    Error.
 * @param array  $def      Definición completa (para condiciones).
 * @return string
 */
function msc_campo_formulario( $id_html, $campo_id, $campo, $valor, $error, $def ) {
	$dom_id    = $id_html . '-' . $campo_id;
	$requerido = ! empty( $campo['requerido'] );
	$aria      = array();
	$ayuda     = '';
	if ( 'file' === $campo['tipo'] ) {
		$max = (int) ( msc_opcion( 'adjuntos_maximo' ) ?? 3 );
		$mb  = (int) ( msc_opcion( 'adjuntos_mb' ) ?? 5 );
		$max = $max > 0 ? $max : 3;
		$mb  = $mb > 0 ? $mb : 5;
		/* translators: 1: número de archivos, 2: megabytes. */
		$ayuda = sprintf( __( 'Hasta %1$d fotos JPG, PNG o WebP de máximo %2$d MB cada una.', 'msc' ), $max, $mb );
		if ( 0 === $max ) {
			return '';
		}
	}
	if ( $ayuda ) {
		$aria[] = $dom_id . '-ayuda';
	}
	if ( $error ) {
		$aria[] = $dom_id . '-error';
	}
	$atributos = '';
	if ( $requerido ) {
		$atributos .= ' required aria-required="true"';
	}
	if ( $error ) {
		$atributos .= ' aria-invalid="true"';
	}
	if ( $aria ) {
		$atributos .= ' aria-describedby="' . esc_attr( implode( ' ', $aria ) ) . '"';
	}
	if ( ! empty( $campo['autocomplete'] ) ) {
		$atributos .= ' autocomplete="' . esc_attr( $campo['autocomplete'] ) . '"';
	}
	if ( ! empty( $campo['placeholder'] ) ) {
		$atributos .= ' placeholder="' . esc_attr( $campo['placeholder'] ) . '"';
	}
	foreach ( array( 'min', 'max' ) as $limite ) {
		if ( isset( $campo[ $limite ] ) ) {
			$atributos .= ' ' . $limite . '="' . esc_attr( $campo[ $limite ] ) . '"';
		}
	}

	$condicion_html = '';
	$nota           = '';
	if ( ! empty( $campo['condicion'] ) ) {
		list( $padre, $valor_padre ) = explode( ':', $campo['condicion'] );
		$condicion_html              = ' data-msc-condicion="' . esc_attr( $padre ) . '" data-msc-valor="' . esc_attr( $valor_padre ) . '"';
		$etiqueta_padre              = $def['campos'][ $padre ]['opciones'][ $valor_padre ] ?? $valor_padre;
		/* translators: %s: opción elegida. */
		$nota = ' <span class="msc-form__condicion">' . esc_html( sprintf( __( '(solo para %s)', 'msc' ), $etiqueta_padre ) ) . '</span>';
	}
	$marca = $requerido ? ' <span class="msc-form__requerido" aria-hidden="true">*</span>' : '';

	ob_start();
	echo '<div class="msc-form__campo msc-form__campo--' . esc_attr( $campo['tipo'] ) . ( $error ? ' has-error' : '' ) . '"' . $condicion_html . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	switch ( $campo['tipo'] ) {
		case 'radio':
			echo '<fieldset id="' . esc_attr( $dom_id ) . '"' . ( $error ? ' aria-invalid="true" aria-describedby="' . esc_attr( $dom_id ) . '-error"' : '' ) . '>';
			echo '<legend>' . esc_html( $campo['etiqueta'] ) . $marca . '</legend>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			foreach ( $campo['opciones'] as $clave => $texto ) {
				$oid = $dom_id . '-' . $clave;
				printf(
					'<label class="msc-form__opcion" for="%1$s"><input type="radio" id="%1$s" name="%2$s" value="%3$s"%4$s%5$s> <span>%6$s</span></label>',
					esc_attr( $oid ),
					esc_attr( $campo_id ),
					esc_attr( $clave ),
					checked( $valor, $clave, false ),
					$requerido ? ' required' : '',
					esc_html( $texto )
				);
			}
			echo '</fieldset>';
			break;

		case 'checkbox':
			$enlace = '';
			if ( isset( $campo['enlace'] ) && 'privacidad' === $campo['enlace'] ) {
				$enlace = ' <a href="' . esc_url( msc_url_privacidad() ) . '">' . esc_html__( 'Leer', 'msc' ) . '</a>';
			}
			printf(
				'<label class="msc-form__casilla" for="%1$s"><input type="checkbox" id="%1$s" name="%2$s" value="1"%3$s%4$s> <span>%5$s%6$s</span></label>',
				esc_attr( $dom_id ),
				esc_attr( $campo_id ),
				checked( (string) $valor, '1', false ),
				$atributos, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				esc_html( $campo['etiqueta'] ),
				$enlace // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			);
			break;

		case 'textarea':
			printf( '<label for="%1$s">%2$s%3$s%4$s</label>', esc_attr( $dom_id ), esc_html( $campo['etiqueta'] ), $marca, $nota ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			printf( '<textarea id="%1$s" name="%2$s" rows="%3$d" maxlength="5000"%4$s>%5$s</textarea>', esc_attr( $dom_id ), esc_attr( $campo_id ), (int) ( $campo['filas'] ?? 4 ), $atributos, esc_textarea( (string) $valor ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			break;

		case 'select':
			printf( '<label for="%1$s">%2$s%3$s%4$s</label>', esc_attr( $dom_id ), esc_html( $campo['etiqueta'] ), $marca, $nota ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			printf( '<select id="%1$s" name="%2$s"%3$s>', esc_attr( $dom_id ), esc_attr( $campo_id ), $atributos ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '<option value="">' . esc_html__( 'Elige una opción', 'msc' ) . '</option>';
			foreach ( $campo['opciones'] as $clave => $texto ) {
				printf( '<option value="%1$s"%2$s>%3$s</option>', esc_attr( $clave ), selected( $valor, $clave, false ), esc_html( $texto ) );
			}
			echo '</select>';
			break;

		case 'file':
			printf( '<label for="%1$s">%2$s%3$s%4$s</label>', esc_attr( $dom_id ), esc_html( $campo['etiqueta'] ), $marca, $nota ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			printf(
				'<input type="file" id="%1$s" name="%2$s%3$s" accept="%4$s"%5$s%6$s>',
				esc_attr( $dom_id ),
				esc_attr( $campo_id ),
				! empty( $campo['multiple'] ) ? '[]' : '',
				esc_attr( $campo['aceptar'] ?? '' ),
				! empty( $campo['multiple'] ) ? ' multiple' : '',
				$atributos // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			);
			break;

		default:
			printf( '<label for="%1$s">%2$s%3$s%4$s</label>', esc_attr( $dom_id ), esc_html( $campo['etiqueta'] ), $marca, $nota ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			printf( '<input type="%1$s" id="%2$s" name="%3$s" value="%4$s" maxlength="200"%5$s>', esc_attr( $campo['tipo'] ), esc_attr( $dom_id ), esc_attr( $campo_id ), esc_attr( (string) $valor ), $atributos ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	if ( $ayuda ) {
		echo '<p class="msc-form__ayuda" id="' . esc_attr( $dom_id ) . '-ayuda">' . esc_html( $ayuda ) . '</p>';
	}
	if ( $error ) {
		echo '<p class="msc-form__error" id="' . esc_attr( $dom_id ) . '-error">' . esc_html( $error ) . '</p>';
	}
	echo '</div>';
	return (string) ob_get_clean();
}

/**
 * URL de la petición actual (sin parámetros de estado del formulario).
 *
 * @return string
 */
function msc_url_peticion() {
	$uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/';
	return remove_query_arg( array( 'msc_form', 'msc_id', 'msc_enviado' ), home_url( $uri ) );
}

/**
 * IP del visitante, en forma de hash (no se guarda la IP en claro).
 *
 * @return string
 */
function msc_hash_ip() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	return substr( wp_hash( 'msc_ip_' . $ip ), 0, 20 );
}

/**
 * Procesa un envío.
 */
function msc_procesar_formulario() {
	$id     = isset( $_POST['msc_formulario'] ) ? sanitize_key( wp_unslash( $_POST['msc_formulario'] ) ) : '';
	$def    = msc_definicion_formulario( $id );
	$html   = isset( $_POST['msc_id_html'] ) ? sanitize_key( wp_unslash( $_POST['msc_id_html'] ) ) : 'form-' . $id;
	$volver = isset( $_POST['msc_volver'] ) ? esc_url_raw( wp_unslash( $_POST['msc_volver'] ) ) : home_url( '/' );
	$volver = wp_validate_redirect( $volver, home_url( '/' ) );

	if ( ! $def ) {
		wp_safe_redirect( $volver );
		exit;
	}

	$errores = array();
	$valores = array();

	// Seguridad.
	$nonce_ok = isset( $_POST['msc_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['msc_nonce'] ) ), 'msc_formulario_' . $id );
	$trampa   = isset( $_POST['msc_web'] ) && '' !== $_POST['msc_web'];
	$tiempo   = isset( $_POST['msc_t'] ) ? sanitize_text_field( wp_unslash( $_POST['msc_t'] ) ) : '';
	$t        = (int) strtok( $tiempo, '.' );
	$firma_ok = $t && hash_equals( msc_firma_tiempo( $t ), $tiempo );
	$rapido   = $firma_ok && ( time() - $t ) < 3;
	$clave_ip = 'msc_lim_' . msc_hash_ip();
	$intentos = (int) get_transient( $clave_ip );

	if ( $trampa || $rapido ) {
		// Robot: respondemos como si todo hubiera ido bien, sin guardar nada.
		wp_safe_redirect( add_query_arg( 'msc_enviado', $html, $volver ) . '#' . $html );
		exit;
	}
	if ( ! $nonce_ok || ! $firma_ok ) {
		$errores['general'] = __( 'La página caducó. Recárgala y vuelve a enviar el formulario.', 'msc' );
	}
	if ( $intentos >= 8 ) {
		$errores['general'] = __( 'Has enviado varios formularios seguidos. Inténtalo de nuevo en unos minutos.', 'msc' );
	}

	// Validación.
	foreach ( $def['campos'] as $campo_id => $campo ) {
		if ( 'file' === $campo['tipo'] ) {
			continue;
		}
		$crudo = isset( $_POST[ $campo_id ] ) ? wp_unslash( $_POST[ $campo_id ] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$crudo = is_array( $crudo ) ? '' : (string) $crudo;
		switch ( $campo['tipo'] ) {
			case 'email':
				$valor = sanitize_email( $crudo );
				if ( '' !== trim( $crudo ) && ! is_email( $valor ) ) {
					$errores[ $campo_id ] = __( 'Escribe un correo electrónico válido.', 'msc' );
				}
				break;
			case 'textarea':
				$valor = sanitize_textarea_field( $crudo );
				break;
			case 'number':
				$valor = '' === $crudo ? '' : (string) absint( $crudo );
				break;
			case 'date':
				$valor = preg_match( '/^\d{4}-\d{2}-\d{2}$/', $crudo ) ? $crudo : '';
				break;
			case 'radio':
			case 'select':
				$valor = array_key_exists( $crudo, $campo['opciones'] ) ? $crudo : '';
				break;
			case 'checkbox':
				$valor = '1' === $crudo ? '1' : '';
				break;
			default:
				$valor = sanitize_text_field( $crudo );
		}
		// Los campos condicionales solo cuentan si su condición se cumple.
		if ( ! empty( $campo['condicion'] ) ) {
			list( $padre, $valor_padre ) = explode( ':', $campo['condicion'] );
			$elegido                     = isset( $_POST[ $padre ] ) ? sanitize_key( wp_unslash( $_POST[ $padre ] ) ) : '';
			if ( $elegido !== $valor_padre ) {
				$valor = '';
			}
		}
		$valores[ $campo_id ] = $valor;
		if ( ! empty( $campo['requerido'] ) && '' === $valor && ! isset( $errores[ $campo_id ] ) ) {
			$errores[ $campo_id ] = 'checkbox' === $campo['tipo']
				? __( 'Debes aceptar la política de privacidad.', 'msc' )
				/* translators: %s: nombre del campo. */
				: sprintf( __( 'Completa el campo "%s".', 'msc' ), $campo['etiqueta'] );
		}
	}

	// Archivos.
	$archivos = array();
	foreach ( $def['campos'] as $campo_id => $campo ) {
		if ( 'file' !== $campo['tipo'] || empty( $_FILES[ $campo_id ] ) ) {
			continue;
		}
		$max   = (int) msc_opcion( 'adjuntos_maximo' );
		$max   = $max > 0 ? $max : 3;
		$mb    = (int) msc_opcion( 'adjuntos_mb' );
		$mb    = $mb > 0 ? $mb : 5;
		$lista = msc_normalizar_archivos( $_FILES[ $campo_id ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		if ( count( $lista ) > $max ) {
			/* translators: %d: número máximo. */
			$errores[ $campo_id ] = sprintf( __( 'Puedes adjuntar hasta %d fotos.', 'msc' ), $max );
			continue;
		}
		foreach ( $lista as $archivo ) {
			$tipo = wp_check_filetype_and_ext(
				$archivo['tmp_name'],
				$archivo['name'],
				array(
					'jpg|jpeg' => 'image/jpeg',
					'png'      => 'image/png',
					'webp'     => 'image/webp',
				)
			);
			if ( UPLOAD_ERR_OK !== $archivo['error'] || ! $tipo['type'] ) {
				$errores[ $campo_id ] = __( 'Solo se aceptan fotos JPG, PNG o WebP.', 'msc' );
			} elseif ( $archivo['size'] > $mb * MB_IN_BYTES ) {
				/* translators: %d: megabytes. */
				$errores[ $campo_id ] = sprintf( __( 'Cada foto debe pesar como máximo %d MB.', 'msc' ), $mb );
			} else {
				$archivos[] = $archivo;
			}
		}
	}

	set_transient( $clave_ip, $intentos + 1, 10 * MINUTE_IN_SECONDS );

	if ( $errores ) {
		$token = wp_generate_password( 16, false );
		set_transient(
			'msc_form_' . strtolower( $token ),
			array(
				'errores' => $errores,
				'valores' => array_diff_key( $valores, array( 'privacidad' => true ) ),
			),
			10 * MINUTE_IN_SECONDS
		);
		wp_safe_redirect(
			add_query_arg(
				array(
					'msc_form' => strtolower( $token ),
					'msc_id'   => $html,
				),
				$volver
			) . '#' . $html
		);
		exit;
	}

	msc_guardar_envio( $id, $def, $valores, $archivos, $volver );
	wp_safe_redirect( add_query_arg( 'msc_enviado', $html, $volver ) . '#' . $html );
	exit;
}
add_action( 'admin_post_msc_formulario', 'msc_procesar_formulario' );
add_action( 'admin_post_nopriv_msc_formulario', 'msc_procesar_formulario' );

/**
 * Convierte $_FILES de un campo múltiple en una lista.
 *
 * @param array $f Entrada de $_FILES.
 * @return array
 */
function msc_normalizar_archivos( $f ) {
	$lista = array();
	if ( is_array( $f['name'] ) ) {
		foreach ( $f['name'] as $i => $nombre ) {
			if ( '' === $nombre || UPLOAD_ERR_NO_FILE === (int) $f['error'][ $i ] ) {
				continue;
			}
			$lista[] = array(
				'name'     => sanitize_file_name( $nombre ),
				'type'     => $f['type'][ $i ],
				'tmp_name' => $f['tmp_name'][ $i ],
				'error'    => (int) $f['error'][ $i ],
				'size'     => (int) $f['size'][ $i ],
			);
		}
	} elseif ( '' !== $f['name'] && UPLOAD_ERR_NO_FILE !== (int) $f['error'] ) {
		$f['name'] = sanitize_file_name( $f['name'] );
		$lista[]   = $f;
	}
	return $lista;
}

/**
 * Guarda el envío, sube los adjuntos y envía los correos.
 *
 * @param string $id       Formulario.
 * @param array  $def      Definición.
 * @param array  $valores  Valores.
 * @param array  $archivos Archivos validados.
 * @param string $origen   Página de origen.
 */
function msc_guardar_envio( $id, $def, $valores, $archivos, $origen ) {
	$filas = array();
	foreach ( $def['campos'] as $campo_id => $campo ) {
		if ( 'file' === $campo['tipo'] || 'privacidad' === $campo_id || ! isset( $valores[ $campo_id ] ) || '' === $valores[ $campo_id ] ) {
			continue;
		}
		$valor = $valores[ $campo_id ];
		if ( isset( $campo['opciones'][ $valor ] ) ) {
			$valor = $campo['opciones'][ $valor ];
		}
		$filas[ $campo['etiqueta'] ] = $valor;
	}
	$nombre = $valores['nombre'] ?? ( $valores['correo'] ?? '' );
	$titulo = $def['titulo'] . ' — ' . $nombre;

	$contenido = '';
	foreach ( $filas as $etiqueta => $valor ) {
		$contenido .= '<p><strong>' . esc_html( $etiqueta ) . ':</strong><br>' . nl2br( esc_html( $valor ) ) . "</p>\n";
	}
	$contenido .= '<p><small>' . esc_html__( 'Enviado desde:', 'msc' ) . ' ' . esc_url( $origen ) . '</small></p>';

	$envio_id = wp_insert_post(
		array(
			'post_type'    => 'msc_envio',
			'post_status'  => 'private',
			'post_title'   => $titulo,
			'post_content' => $contenido,
		)
	);
	if ( ! $envio_id || is_wp_error( $envio_id ) ) {
		return;
	}
	update_post_meta( $envio_id, '_msc_formulario', $id );
	update_post_meta( $envio_id, '_msc_correo', $valores['correo'] ?? '' );
	update_post_meta( $envio_id, '_msc_valores', $valores );

	// Adjuntos: se guardan en la biblioteca de medios, asociados al envío.
	$rutas = array();
	if ( $archivos ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		foreach ( $archivos as $archivo ) {
			$subido = wp_handle_upload( $archivo, array( 'test_form' => false ) );
			if ( isset( $subido['file'] ) ) {
				$adjunto = wp_insert_attachment(
					array(
						'post_mime_type' => $subido['type'],
						'post_title'     => sanitize_file_name( $archivo['name'] ),
						'post_status'    => 'inherit',
					),
					$subido['file'],
					$envio_id
				);
				if ( $adjunto && ! is_wp_error( $adjunto ) ) {
					wp_update_attachment_metadata( $adjunto, wp_generate_attachment_metadata( $adjunto, $subido['file'] ) );
				}
				$rutas[] = $subido['file'];
			}
		}
	}

	// Correo al museo.
	$destino = msc_opcion( 'formularios_correo' );
	$destino = $destino ? $destino : get_option( 'admin_email' );
	$asunto  = '[' . get_bloginfo( 'name' ) . '] ' . $titulo;
	$cuerpo  = $def['titulo'] . "\n\n";
	foreach ( $filas as $etiqueta => $valor ) {
		$cuerpo .= $etiqueta . ': ' . $valor . "\n";
	}
	$cuerpo   .= "\n" . __( 'Ver en el administrador:', 'msc' ) . ' ' . admin_url( 'post.php?post=' . $envio_id . '&action=edit' );
	$cabeceras = array();
	if ( ! empty( $valores['correo'] ) && is_email( $valores['correo'] ) ) {
		$cabeceras[] = 'Reply-To: ' . $valores['correo'];
	}
	wp_mail( $destino, $asunto, $cuerpo, $cabeceras, $rutas );

	// Copia de recepción.
	if ( 'boletin' !== $id && msc_opcion( 'formularios_copia' ) !== false && ! empty( $valores['correo'] ) && is_email( $valores['correo'] ) ) {
		$copia = __( 'Hola:', 'msc' ) . "\n\n" . __( 'Recibimos tu mensaje en el Museo Sefardí de Caracas. Te responderemos pronto. Esta es una copia de lo que enviaste:', 'msc' ) . "\n\n";
		foreach ( $filas as $etiqueta => $valor ) {
			$copia .= $etiqueta . ': ' . $valor . "\n";
		}
		wp_mail( $valores['correo'], __( 'Recibimos tu mensaje — Museo Sefardí de Caracas', 'msc' ), $copia );
	}

	/**
	 * Tras guardar un envío (para integrar un servicio de boletín, CRM, etc.).
	 *
	 * @param int    $envio_id ID.
	 * @param string $id       Formulario.
	 * @param array  $valores  Valores.
	 */
	do_action( 'msc_envio_guardado', $envio_id, $id, $valores );
}

/**
 * Columnas del listado de envíos.
 *
 * @param array $columnas Columnas.
 * @return array
 */
function msc_columnas_envios( $columnas ) {
	return array(
		'cb'         => $columnas['cb'],
		'title'      => __( 'Envío', 'msc' ),
		'formulario' => __( 'Formulario', 'msc' ),
		'correo'     => __( 'Correo', 'msc' ),
		'date'       => __( 'Fecha', 'msc' ),
	);
}
add_filter( 'manage_msc_envio_posts_columns', 'msc_columnas_envios' );

/**
 * Contenido de las columnas de envíos.
 *
 * @param string $columna Columna.
 * @param int    $id      ID.
 */
function msc_columna_envio( $columna, $id ) {
	if ( 'formulario' === $columna ) {
		$def = msc_definicion_formulario( (string) get_post_meta( $id, '_msc_formulario', true ) );
		echo esc_html( $def['titulo'] ?? '' );
	} elseif ( 'correo' === $columna ) {
		echo esc_html( (string) get_post_meta( $id, '_msc_correo', true ) );
	}
}
add_action( 'manage_msc_envio_posts_custom_column', 'msc_columna_envio', 10, 2 );
