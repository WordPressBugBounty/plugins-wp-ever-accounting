<?php

namespace EverAccounting\Compatibility\Plugins;

defined( 'ABSPATH' ) || exit;

/**
 * Base class for a plugin compatibility class
 *
 * @since 2.2.0
 * @package EverAccounting
 */
abstract class Plugin {

	/**
	 * Determine if the plugin is active, and if so, register the events.
	 *
	 * @since 2.2.0
	 *
	 * @return void
	 */
	final public function __construct() {
		if ( ! $this->is_active() ) {
			return;
		}

		// Now that we've confirmed the plugin is active, let's register the events.
		$this->register_events();
	}

	/**
	 * Check if the plugin is active
	 *
	 * @since 2.2.0
	 *
	 * @return bool
	 */
	abstract public function is_active(): bool;

	/**
	 * Register the events for the plugin compatibility.
	 *
	 * @since 2.2.0
	 *
	 * @return void
	 */
	abstract protected function register_events(): void;
}
