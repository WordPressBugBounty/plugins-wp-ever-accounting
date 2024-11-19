<?php

namespace EverAccounting\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Class Reports
 *
 * @since 1.0.0
 * @package EverAccounting\Admin
 */
class Reports {

	/**
	 * Reports constructor.
	 */
	public function __construct() {
		add_filter( 'eac_reports_page_tabs', array( __CLASS__, 'register_tabs' ) );
		add_action( 'eac_reports_page_sales_content', array( __CLASS__, 'sales_report' ) );
		add_action( 'eac_reports_page_expenses_content', array( __CLASS__, 'expenses_report' ) );
		add_action( 'eac_reports_page_profits_content', array( __CLASS__, 'profits_report' ) );
		add_action( 'eac_reports_page_taxes_content', array( __CLASS__, 'taxes_report' ) );
	}

	/**
	 * Register tab.
	 *
	 * @param array $tabs Tabs.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public static function register_tabs( $tabs ) {
		if ( current_user_can( 'eac_manage_report' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
			$tabs['sales']    = __( 'Sales', 'wp-ever-accounting' );
			$tabs['expenses'] = __( 'Expenses', 'wp-ever-accounting' );
			$tabs['profits']  = __( 'Profits', 'wp-ever-accounting' );
		}

		return $tabs;
	}

	/**
	 * Render the sales report.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function sales_report() {
		Reports\Sales::render();
	}

	/**
	 * Render the expenses report.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function expenses_report() {
		Reports\Expenses::render();
	}

	/**
	 * Render the profits report.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function profits_report() {
		Reports\Profits::render();
	}

	/**
	 * Render the taxes report.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function taxes_report() {
		Reports\Taxes::render();
	}
}
