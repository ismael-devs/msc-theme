<?php
/**
 * Eventos: próximos (tarjetas, con estado vacío) y pasados (lista por año con filtros y paginación).
 *
 * @package msc-theme
 */

defined( 'ABSPATH' ) || exit;

global $wp_query;
get_header();
msc_tema_cabecera( __( 'Eventos', 'msc' ), (string) msc_opcion( 'intro_eventos' ) );
$msc_proximos = msc_eventos_proximos();
?>
<section class="msc-seccion msc-contenedor" aria-labelledby="proximos-titulo">
	<h2 id="proximos-titulo"><?php esc_html_e( 'Próximos eventos', 'msc' ); ?></h2>
	<?php if ( $msc_proximos ) : ?>
		<ul class="msc-rejilla msc-rejilla--c2" role="list">
			<?php foreach ( $msc_proximos as $msc_ev ) : ?>
				<?php
				$msc_tipos = get_the_terms( $msc_ev, 'tipo_evento' );
				$msc_meta  = array_filter(
					array(
						$msc_tipos && ! is_wp_error( $msc_tipos ) ? $msc_tipos[0]->name : '',
						msc_formatear_fecha( get_post_meta( $msc_ev, 'fecha_hora', true ), 'j \d\e F \d\e Y, H:i' ),
					)
				);
				$msc_ficha = msc_evento_tiene_ficha( $msc_ev );
				?>
				<li>
					<?php
					echo msc_tema_tarjeta( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						array(
							'titulo' => get_the_title( $msc_ev ),
							'url'    => '',
							'imagen' => msc_imagen_principal( $msc_ev ),
							'antes'  => msc_tema_pildora( 'proximo' ) . '<p class="msc-tarjeta__sobre">' . esc_html( implode( ' · ', $msc_meta ) ) . '</p>',
							'sizes'  => '(max-width: 820px) 100vw, 50vw',
							'clase'  => 'msc-tarjeta--evento',
							'boton'  => $msc_ficha ? '<a class="msc-btn msc-btn--secundario" href="' . esc_url( get_permalink( $msc_ev ) ) . '">' . esc_html__( 'Ver evento', 'msc' ) . '<span class="screen-reader-text">: ' . esc_html( get_the_title( $msc_ev ) ) . '</span></a>' : '',
						)
					);
					?>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php else : ?>
		<?php echo msc_tema_vacio( __( 'Por ahora no hay eventos programados.', 'msc' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	<?php endif; ?>
</section>

<section class="msc-seccion msc-contenedor" aria-labelledby="pasados-titulo">
	<h2 id="pasados-titulo"><?php esc_html_e( 'Eventos pasados', 'msc' ); ?></h2>
	<?php
	$msc_tipos_opc = array( '' => __( 'Todos', 'msc' ) );
	$msc_terminos  = get_terms(
		array(
			'taxonomy'   => 'tipo_evento',
			'hide_empty' => true,
		)
	);
	if ( ! is_wp_error( $msc_terminos ) ) {
		foreach ( $msc_terminos as $msc_t ) {
			$msc_tipos_opc[ $msc_t->slug ] = $msc_t->name;
		}
	}
	$msc_anios = array( '' => __( 'Todos', 'msc' ) );
	foreach ( msc_anios_disponibles( 'msc_evento', 'fecha_hora' ) as $msc_a ) {
		$msc_anios[ $msc_a ] = (string) $msc_a;
	}
	msc_tema_filtros(
		__( 'Buscar y filtrar', 'msc' ),
		msc_url( 'eventos' ),
		msc_tema_campo_filtro( 'text', 'msc_q', __( 'Buscar', 'msc' ), array(), __( 'Título, ponente o tema', 'msc' ) )
		. msc_tema_campo_filtro( 'select', 'msc_tipo', __( 'Tipo', 'msc' ), $msc_tipos_opc )
		. msc_tema_campo_filtro( 'select', 'msc_anio', __( 'Año', 'msc' ), $msc_anios ),
		msc_vista_filtrada()
	);

	if ( have_posts() ) :
		$msc_anio_actual = null;
		foreach ( $wp_query->posts as $msc_p ) :
			$msc_f    = msc_fecha_objeto( get_post_meta( $msc_p->ID, 'fecha_hora', true ) );
			$msc_anio = $msc_f ? $msc_f->format( 'Y' ) : '';
			if ( $msc_anio !== $msc_anio_actual ) :
				if ( null !== $msc_anio_actual ) {
					echo '</ul>';
				}
				$msc_anio_actual = $msc_anio;
				?>
				<h3 class="msc-anio"><?php echo esc_html( $msc_anio ); ?></h3>
				<ul class="msc-lista-eventos" role="list">
				<?php
			endif;
			echo msc_tema_fila_evento( $msc_p->ID ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		endforeach;
		echo '</ul>';
		msc_tema_paginacion();
	else :
		echo msc_tema_vacio( __( 'No hay eventos pasados que coincidan con tu búsqueda.', 'msc' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	endif;
	?>
</section>
<?php
msc_tema_banda_boletin();
get_footer();
