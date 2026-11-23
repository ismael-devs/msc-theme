<?php
/**
 * Visítanos: cómo llegar (mapa y contacto), horarios y entrada, visita guiada,
 * información práctica y "Qué encontrarás".
 *
 * @package msc-theme
 */

defined( 'ABSPATH' ) || exit;

get_header();
$msc_id = get_queried_object_id();
msc_tema_cabecera( get_the_title(), (string) msc_campo( 'intro', $msc_id ) );

$msc_tel = (string) msc_opcion( 'telefono' );
$msc_wa  = (string) msc_opcion( 'whatsapp' );
$msc_ig  = ltrim( (string) msc_opcion( 'instagram' ), '@' );
$msc_map = (string) msc_opcion( 'mapa_enlace' );
?>
<section id="como-llegar" class="msc-seccion msc-contenedor" aria-labelledby="llegar-titulo">
	<h2 id="llegar-titulo"><?php esc_html_e( 'Cómo llegar', 'msc' ); ?></h2>
	<?php if ( msc_opcion( 'direccion' ) ) : ?>
		<p><?php echo esc_html( msc_opcion( 'direccion' ) ); ?></p>
	<?php endif; ?>
	<?php get_template_part( 'parts/mapa' ); ?>
	<div class="msc-botones">
		<?php if ( $msc_map ) : ?>
			<a class="msc-btn msc-btn--primario" href="<?php echo esc_url( $msc_map ); ?>" rel="noopener"><?php esc_html_e( 'Abrir en el mapa', 'msc' ); ?></a>
		<?php endif; ?>
		<?php if ( $msc_tel ) : ?>
			<a class="msc-btn msc-btn--secundario" href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $msc_tel ) ); ?>"><?php /* translators: %s: teléfono. */ echo esc_html( sprintf( __( 'Llamar %s', 'msc' ), $msc_tel ) ); ?></a>
		<?php endif; ?>
		<?php if ( $msc_wa ) : ?>
			<a class="msc-btn msc-btn--secundario" href="<?php echo esc_url( 'https://wa.me/' . preg_replace( '/[^0-9]/', '', $msc_wa ) ); ?>" rel="noopener"><?php /* translators: %s: número de WhatsApp. */ echo esc_html( sprintf( __( 'WhatsApp %s', 'msc' ), $msc_wa ) ); ?></a>
		<?php endif; ?>
	</div>
	<?php if ( $msc_ig ) : ?>
		<p><a class="msc-enlace-fuerte" href="<?php echo esc_url( 'https://www.instagram.com/' . rawurlencode( $msc_ig ) . '/' ); ?>" rel="noopener">@<?php echo esc_html( $msc_ig ); ?></a></p>
	<?php endif; ?>
</section>

<section class="msc-seccion msc-contenedor" aria-labelledby="horarios-titulo">
	<h2 id="horarios-titulo"><?php esc_html_e( 'Horarios y entrada', 'msc' ); ?></h2>
	<?php
	$msc_filas = array();
	foreach ( array(
		'horarios' => __( 'Días y horario', 'msc' ),
		'tarifas'  => __( 'Tarifas', 'msc' ),
		'reservas' => __( 'Reservas', 'msc' ),
	) as $msc_clave => $msc_etiqueta ) {
		$msc_valor                  = (string) msc_opcion( $msc_clave );
		$msc_filas[ $msc_etiqueta ] = $msc_valor ? nl2br( esc_html( $msc_valor ) ) : msc_por_confirmar();
	}
	$msc_tabla = msc_tema_datos( $msc_filas, 'msc-datos--compacto msc-datos--lineas' );
	echo $msc_tabla ? $msc_tabla : '<p>' . esc_html__( 'Consulta nuestros horarios por teléfono o WhatsApp.', 'msc' ) . '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	?>
</section>

<section id="visita-guiada" class="msc-seccion msc-contenedor" aria-labelledby="visita-titulo" tabindex="-1">
	<h2 id="visita-titulo"><?php esc_html_e( 'Solicita una visita guiada', 'msc' ); ?></h2>
	<div class="msc-form-caja">
		<?php echo msc_formulario( 'visita', array( 'id_html' => 'form-visita' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</div>
</section>

<?php
$msc_practica = array();
foreach ( array(
	'accesibilidad'   => __( 'Accesibilidad', 'msc' ),
	'estacionamiento' => __( 'Estacionamiento', 'msc' ),
	'seguridad'       => __( 'Seguridad y acceso', 'msc' ),
	'normas'          => __( 'Normas de visita', 'msc' ),
) as $msc_clave => $msc_etiqueta ) {
	$msc_valor = (string) msc_opcion( $msc_clave );
	if ( $msc_valor ) {
		$msc_practica[ $msc_etiqueta ] = nl2br( esc_html( $msc_valor ) );
	} elseif ( current_user_can( 'edit_posts' ) ) {
		$msc_practica[ $msc_etiqueta ] = msc_por_confirmar();
	}
}
if ( $msc_practica ) :
	?>
	<section class="msc-seccion msc-contenedor" aria-labelledby="practica-titulo">
		<h2 id="practica-titulo"><?php esc_html_e( 'Información práctica', 'msc' ); ?></h2>
		<div class="msc-acordeon">
			<?php foreach ( $msc_practica as $msc_etiqueta => $msc_valor ) : ?>
				<details>
					<summary><?php echo esc_html( $msc_etiqueta ); ?></summary>
					<div class="msc-acordeon__cuerpo"><p><?php echo wp_kses_post( $msc_valor ); ?></p></div>
				</details>
			<?php endforeach; ?>
		</div>
	</section>
<?php endif; ?>

<?php
$msc_que = array_filter(
	(array) msc_campo( 'que_encontraras', $msc_id ),
	static function ( $q ) {
		return ! empty( $q['titulo'] );
	}
);
if ( $msc_que ) :
	?>
	<section class="msc-seccion msc-contenedor" aria-labelledby="encontraras-titulo">
		<h2 id="encontraras-titulo"><?php esc_html_e( 'Qué encontrarás', 'msc' ); ?></h2>
		<ul class="msc-rejilla msc-rejilla--c4" role="list">
			<?php foreach ( $msc_que as $msc_q ) : ?>
				<li>
					<?php
					echo msc_tema_tarjeta( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						array(
							'titulo' => $msc_q['titulo'],
							'url'    => $msc_q['enlace'] ?? '',
							'imagen' => $msc_q['imagen'] ?? 0,
						)
					);
					?>
				</li>
			<?php endforeach; ?>
		</ul>
	</section>
<?php endif; ?>
<?php
get_footer();
