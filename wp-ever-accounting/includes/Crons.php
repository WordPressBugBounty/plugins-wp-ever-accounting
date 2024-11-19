<?php

namespace EverAccounting;

defined( 'ABSPATH' ) || exit;

/**
 * Crons class.
 *
 * @since 1.0.0
 * @package EverAccounting
 */
class Crons {

	/**
	 * Crons constructor.
	 */
	public function __construct() {
		add_action( 'eac_hourly_event', array( $this, 'cleanup_scheduled_events' ) );
	}

	/**
	 * Cleanup scheduled events.
	 *
	 * @since 1.0.0
	 */
	public function cleanup_scheduled_events() {
		EAC()->queue()->cleanup();
	}
}
