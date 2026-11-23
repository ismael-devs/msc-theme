<?php
/**
 * Comandos WP-CLI del Museo Sefardí.
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
 * Comandos "wp msc".
 */
class MSC_CLI {

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
		$datos = new MSC_Datos_Ejemplo();
		$ok    = $datos->seed( $args, $assoc );
		$this->imprimir( $datos, $ok );
	}

	/**
	 * Borra el contenido de ejemplo.
	 *
	 * @param array $args  Argumentos.
	 * @param array $assoc Opciones.
	 */
	public function limpiar( $args, $assoc ) {
		$datos = new MSC_Datos_Ejemplo();
		$datos->limpiar( $args, $assoc );
		$this->imprimir( $datos, true );
	}

	/**
	 * Ejecuta la revisión de estados (como el cron diario).
	 */
	public function estados() {
		$datos = new MSC_Datos_Ejemplo();
		$datos->estados();
		$this->imprimir( $datos, true );
	}

	/**
	 * Imprime los mensajes del generador.
	 *
	 * @param MSC_Datos_Ejemplo $datos Generador.
	 * @param bool              $ok    Resultado.
	 */
	private function imprimir( $datos, $ok ) {
		$ultimo = array_pop( $datos->mensajes );
		foreach ( $datos->mensajes as $m ) {
			WP_CLI::log( $m );
		}
		if ( false === $ok ) {
			WP_CLI::error( (string) $ultimo );
		}
		WP_CLI::success( (string) $ultimo );
	}
}

WP_CLI::add_command( 'msc', 'MSC_CLI' );
