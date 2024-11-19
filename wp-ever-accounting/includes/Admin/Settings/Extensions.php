<?php

namespace EverAccounting\Admin\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Extensions.
 *
 * @since 1.0.0
 * @package EverAccounting
 */
class Extensions extends Page {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct( 'extensions', __( 'Extensions', 'wp-ever-accounting' ) );
	}

	/**
	 * Get settings tab sections.
	 *
	 * @since 3.0.0
	 * @return array
	 */
	protected function get_own_sections() {
		return array();
	}
}
