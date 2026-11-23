<?php
/**
 * Ficha de pieza: galería con miniaturas, ficha técnica, compartir y más piezas.
 *
 * @package msc-theme
 */

defined( 'ABSPATH' ) || exit;

get_header();
$msc_id  = get_queried_object_id();
$msc_cat = msc_categoria_principal( $msc_id );
$msc_gal = (array) msc_campo( 'galeria', $msc_id, false );
$msc_gal = array_values( array_filter( array_map( 'msc_id_imagen', $msc_gal ) ) );

msc_tema_cabecera( get_the_title( $msc_id ), '', $msc_cat ? msc_tema_etiqueta( $msc_cat->name, 'msc-etiqueta--caja' ) : '' );
?>
<div class="msc-contenedor msc-ficha">
	<?php if ( $msc_gal ) : ?>
		<div class="msc-visor" data-msc-visor>
			<figure class="msc-visor__principal">
				<?php
				echo msc_img( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					$msc_gal[0],
					array(
						'sizes'     => '(max-width: 820px) 100vw, 1312px',
						'tamano'    => 'msc-2400',
						'prioridad' => true,
						'clase'     => 'msc-visor__img',
					)
				);
				?>
			</figure>
			<?php if ( count( $msc_gal ) > 1 ) : ?>
				<ul class="msc-visor__miniaturas" role="list" aria-label="<?php esc_attr_e( 'Imágenes de la pieza', 'msc' ); ?>">
					<?php foreach ( $msc_gal as $msc_i => $msc_img ) : ?>
						<?php
						$msc_src = wp_get_attachment_image_src( $msc_img, 'msc-2400' );
						$msc_alt = get_post_meta( $msc_img, '_wp_attachment_image_alt', true );
						?>
						<li>
							<a href="<?php echo esc_url( $msc_src ? $msc_src[0] : '' ); ?>" data-srcset="<?php echo esc_attr( (string) wp_get_attachment_image_srcset( $msc_img, 'msc-2400' ) ); ?>" data-alt="<?php echo esc_attr( $msc_alt ); ?>"<?php echo 0 === $msc_i ? ' aria-current="true"' : ''; ?>>
								<?php
								echo wp_get_attachment_image(
									$msc_img,
									'thumbnail',
									false,
									array(
										'alt'     => '',
										'loading' => 'lazy',
									)
								);
								?>
								<span class="screen-reader-text">
									<?php
									/* translators: 1: número de imagen, 2: total. */
									echo esc_html( sprintf( __( 'Ver imagen %1$d de %2$d', 'msc' ), $msc_i + 1, count( $msc_gal ) ) );
									?>
								</span>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php echo msc_tema_texto( (string) msc_campo( 'descripcion', $msc_id ), 'msc-texto--legible' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

	<?php
	$msc_ficha = msc_tema_datos(
		array(
			__( 'Dimensiones', 'msc' )          => esc_html( (string) msc_campo( 'dimensiones', $msc_id ) ),
			__( 'Forma de adquisición', 'msc' ) => esc_html( (string) msc_campo( 'forma_adquisicion', $msc_id ) ),
			__( 'Número de inventario', 'msc' ) => esc_html( (string) msc_campo( 'numero_inventario', $msc_id ) ),
			__( 'Época o fecha', 'msc' )        => esc_html( (string) msc_campo( 'epoca', $msc_id ) ),
			__( 'Autor o procedencia', 'msc' )  => esc_html( (string) msc_campo( 'autor_procedencia', $msc_id ) ),
			__( 'Material o técnica', 'msc' )   => esc_html( (string) msc_campo( 'material_tecnica', $msc_id ) ),
			__( 'Crédito del donante', 'msc' )  => esc_html( (string) msc_campo( 'credito_donante', $msc_id ) ),
		),
		'msc-datos--ficha'
	);
	if ( $msc_ficha ) :
		?>
		<section class="msc-seccion" aria-labelledby="ficha-titulo">
			<h2 id="ficha-titulo"><?php esc_html_e( 'Ficha técnica', 'msc' ); ?></h2>
			<?php echo $msc_ficha; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</section>
	<?php endif; ?>

	<div class="msc-botones msc-botones--ficha">
		<button type="button" class="msc-btn msc-btn--primario" data-msc-compartir data-url="<?php echo esc_url( get_permalink( $msc_id ) ); ?>" data-titulo="<?php echo esc_attr( get_the_title( $msc_id ) ); ?>"><?php echo msc_icono( 'compartir', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php esc_html_e( 'Compartir', 'msc' ); ?></button>
		<?php if ( $msc_cat ) : ?>
			<a class="msc-btn msc-btn--secundario" href="<?php echo esc_url( get_term_link( $msc_cat ) ); ?>"><?php echo msc_icono( 'atras', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php /* translators: %s: categoría. */ echo esc_html( sprintf( __( 'Volver a %s', 'msc' ), $msc_cat->name ) ); ?></a>
		<?php endif; ?>
		<span class="msc-compartir-estado" role="status" aria-live="polite"></span>
	</div>

	<?php $msc_mas = msc_mas_piezas( $msc_id, 4 ); ?>
	<?php if ( $msc_mas && $msc_cat ) : ?>
		<section class="msc-seccion" aria-labelledby="mas-titulo">
			<h2 id="mas-titulo"><?php /* translators: %s: categoría. */ echo esc_html( sprintf( __( 'Más piezas de %s', 'msc' ), $msc_cat->name ) ); ?></h2>
			<?php echo msc_tema_rejilla( $msc_mas, 'c4' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</section>
	<?php endif; ?>
</div>
<?php
msc_tema_banda_colaborar( __( '¿Tienes una pieza que aportar al patrimonio sefardí?', 'msc' ) );
get_footer();
