<?php

namespace EverAccounting\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Notices class.
 *
 * @since 2.1.9
 * @package EverAccounting\Admin\Notices
 */
class Notices {

	/**
	 * Notices constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'admin_notices' ) );
	}

	/**
	 * Admin notices.
	 *
	 * @since 1.0.0
	 */
	public function admin_notices() {
		$installed_time = absint( get_option( 'eac_install_date' ) );
		$current_time   = absint( wp_date( 'U' ) );

		if ( ! defined( 'EAC_ESTIMATES_VERSION' ) && is_plugin_active( 'wp-ever-accounting/wp-ever-accounting.php' ) ) {
			EAC()->notices->add(
				array(
					'message'     => __DIR__ . '/views/notices/eac-estimates.php',
					'notice_id'   => 'eac_estimates_flash50_04062025',
					'style'       => 'border-left-color: #77B82E;',
					'dismissible' => true,
				)
			);
		}

		if ( ! defined( 'EAC_WC_VERSION' ) && is_plugin_active( 'wp-ever-accounting/wp-ever-accounting.php' ) && class_exists( 'WooCommerce' ) ) {
			EAC()->notices->add(
				array(
					'message'     => __DIR__ . '/views/notices/eac-woocommerce.php',
					'notice_id'   => 'eac_woocommerce_flash50_04062025', // Old IDs: eac_woocommerce_early_bird_sale.
					'style'       => 'border-left-color: #77B82E;',
					'dismissible' => true,
				)
			);
		}

		// Show after 5 days.
		if ( $installed_time && $current_time > ( $installed_time + ( 5 * DAY_IN_SECONDS ) ) ) {
			EAC()->notices->add(
				array(
					'message'     => __DIR__ . '/views/notices/review.php',
					'dismissible' => false,
					'notice_id'   => 'eac_review',
					'style'       => 'border-left-color: #77B82E;',
				)
			);
		}
	}
}
