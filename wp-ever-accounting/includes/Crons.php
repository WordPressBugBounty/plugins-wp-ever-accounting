<?php

namespace EverAccounting;

defined( 'ABSPATH' ) || exit;

/**
 * Crons class.
 *
 * @since 1.0.0
 * @package EverAccounting
 */
class Crons extends B8\Component {

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register(): void {
		add_action( 'eac_hourly_event', array( $this, 'cleanup_scheduled_events' ) );
	}

	/**
	 * Cleanup scheduled events.
	 *
	 * @since 1.0.0
	 */
	public function cleanup_scheduled_events() {
		wp_clear_scheduled_hook( 'eac_hourly_event' );
	}
}
