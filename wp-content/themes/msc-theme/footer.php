<?php
/**
 * Pie: franja dorada, boletín (Inicio), contacto y enlaces "Explora".
 *
 * @package msc-theme
 */

defined( 'ABSPATH' ) || exit;

$msc_direccion = function_exists( 'msc_opcion' ) ? msc_opcion( 'direccion_corta' ) : '';
$msc_telefono  = function_exists( 'msc_opcion' ) ? msc_opcion( 'telefono' ) : '';
$msc_correo    = function_exists( 'msc_opcion' ) ? msc_opcion( 'correo' ) : '';
$msc_ig        = function_exists( 'msc_opcion' ) ? ltrim( (string) msc_opcion( 'instagram' ), '@' ) : '';
$msc_enlaces   = msc_tema_enlaces_menu();
$msc_enlaces[] = array(
	'titulo' => __( 'Política de privacidad', 'msc' ),
	'url'    => function_exists( 'msc_url_privacidad' ) ? msc_url_privacidad() : get_privacy_policy_url(),
	'actual' => false,
);

/**
 * Bloque de datos de contacto.
 */
$msc_contacto = static function () use ( $msc_direccion, $msc_telefono, $msc_correo, $msc_ig ) {
	echo '<address class="msc-pie__contacto">';
	if ( $msc_direccion ) {
		echo '<span>' . esc_html( $msc_direccion ) . '</span>';
	}
	if ( $msc_telefono ) {
		echo '<a href="tel:' . esc_attr( preg_replace( '/[^0-9+]/', '', $msc_telefono ) ) . '">' . esc_html( $msc_telefono ) . '</a>';
	}
	if ( $msc_correo ) {
		echo '<a href="mailto:' . esc_attr( antispambot( $msc_correo ) ) . '">' . esc_html( antispambot( $msc_correo ) ) . '</a>';
	} else {
		echo msc_por_confirmar( __( '[Correo por confirmar]', 'msc' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	if ( $msc_ig ) {
		echo '<a class="msc-pie__ig" href="' . esc_url( 'https://www.instagram.com/' . rawurlencode( $msc_ig ) . '/' ) . '" rel="noopener">@' . esc_html( $msc_ig ) . '</a>';
	}
	echo '</address>';
};
?>
</main>
<footer class="msc-pie<?php echo is_front_page() ? ' msc-pie--inicio' : ''; ?>">
	<div class="msc-contenedor msc-pie__interior">
		<?php if ( is_front_page() && function_exists( 'msc_formulario' ) ) : ?>
			<div class="msc-pie__col msc-pie__boletin">
				<h2><?php esc_html_e( 'Boletín', 'msc' ); ?></h2>
				<?php
				echo msc_formulario( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					'boletin',
					array(
						'variante' => 'pie',
						'id_html'  => 'boletin-pie',
					)
				);
				?>
			</div>
			<div class="msc-pie__col">
				<h2><?php esc_html_e( 'Contacto', 'msc' ); ?></h2>
				<?php $msc_contacto(); ?>
			</div>
		<?php else : ?>
			<div class="msc-pie__col msc-pie__col--ancha">
				<h2 class="msc-pie__nombre">Museo Sefardí de Caracas</h2>
				<?php $msc_contacto(); ?>
			</div>
		<?php endif; ?>
		<nav class="msc-pie__col msc-pie__explora" aria-labelledby="msc-pie-explora">
			<h2 id="msc-pie-explora"><?php esc_html_e( 'Explora', 'msc' ); ?></h2>
			<ul>
				<?php foreach ( $msc_enlaces as $msc_e ) : ?>
					<li><a href="<?php echo esc_url( $msc_e['url'] ); ?>"><?php echo esc_html( $msc_e['titulo'] ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</nav>
	</div>
	<p class="msc-contenedor msc-pie__legal">© <?php echo esc_html( gmdate( 'Y' ) ); ?> Museo Sefardí de Caracas "Morris E. Curiel"</p>
</footer>
<?php wp_footer(); ?>
</body>
</html>
