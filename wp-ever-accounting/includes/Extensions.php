<?php

namespace EverAccounting;

defined( 'ABSPATH' ) || exit;

/**
 * Class Extensions
 *
 * @package EverAccounting
 */
class Extensions {

	/**
	 * Extensions.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $extensions = array();

	/**
	 * Extensions constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_extensions' ) );
	}

	/**
	 * Register extensions.
	 *
	 * @since 1.0.0
	 */
	public function register_extensions() {}
}
