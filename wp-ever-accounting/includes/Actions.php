<?php

namespace EverAccounting;

defined( 'ABSPATH' ) || exit;

/**
 * Actions class.
 *
 * @since 1.0.0
 * @package EverAccounting
 */
class Actions {

	/**
	 * Initialize the class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'get_actions' ), 0 );
		add_action( 'init', array( $this, 'post_actions' ), 0 );
	}

	/**
	 * Get actions.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function get_actions() {
		wp_verify_nonce( '_wpnonce' );
		$action = ! empty( $_GET['eac_action'] ) ? sanitize_key( wp_unslash( $_GET['eac_action'] ) ) : false;

		if ( ! empty( $action ) && is_scalar( $action ) ) {
			do_action( "eac_action_{$action}", $_GET );
		}
	}

	/**
	 * Post actions.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function post_actions() {
		wp_verify_nonce( '_wpnonce' );
		$action = ! empty( $_POST['eac_action'] ) ? sanitize_key( wp_unslash( $_POST['eac_action'] ) ) : false;

		if ( ! empty( $action ) ) {
			do_action( "eac_action_{$action}", $_POST );
		}
	}
}
