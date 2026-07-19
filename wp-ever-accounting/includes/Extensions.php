<?php

namespace EverAccounting;

defined( 'ABSPATH' ) || exit;

/**
 * Class Extensions.
 *
 * @since 1.0.0
 * @package EverAccounting
 */
class Extensions extends B8\Component {

	/**
	 * Extensions.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $extensions = array();

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register(): void {
		add_action( 'init', array( $this, 'register_extensions' ) );
	}

	/**
	 * Register extensions.
	 *
	 * @since 1.0.0
	 */
	public function register_extensions() {}
}
