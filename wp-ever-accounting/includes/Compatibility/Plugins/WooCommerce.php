<?php

namespace EverAccounting\Compatibility\Plugins;

defined( 'ABSPATH' ) || exit;

/**
 * WooCommerce compatibility class
 *
 * @since 2.2.0
 * @package EverAccounting
 */
class WooCommerce extends Plugin {

	/**
	 * Check if the plugin is active
	 *
	 * @since 2.2.0
	 *
	 * @return bool
	 */
	public function is_active(): bool {
		return class_exists( 'WooCommerce' );
	}

	/**
	 * Register the events for the plugin compatibility.
	 *
	 * @since 2.2.0
	 *
	 * @return void
	 */
	protected function register_events(): void {
		add_filter( 'woocommerce_prevent_admin_access', array( $this, 'allow_admin_access' ) );
	}

	/**
	 * Allow admin access for account_manager and accountant
	 *
	 * @param bool $prevent_access Whether to prevent access to the admin area.
	 *
	 * @return bool
	 * @since 2.2.0
	 */
	public function allow_admin_access( $prevent_access ): bool {

		$allowed_roles = array( 'eac_auditor', 'eac_accountant', 'eac_manager' );
		foreach ( $allowed_roles as $role ) {
			if ( current_user_can( $role ) ) {
				return false;
			}
		}

		return $prevent_access;
	}
}
