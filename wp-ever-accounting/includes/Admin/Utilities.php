<?php

namespace EverAccounting\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Utilities class.
 *
 * @since 1.0.0
 * @package EverAccounting\Admin
 */
class Utilities {
	/**
	 * Get admin menus.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public static function get_menus() {
		$menus = array(
			array(
				'capability' => 'eac_manage_item',
				'menu_slug'  => 'eac-items',
				'menu_title' => __( 'Items', 'wp-ever-accounting' ),
				'page_title' => __( 'Items', 'wp-ever-accounting' ),
				'position'   => 20,
			),
			array(
				'page_title' => __( 'Sales', 'wp-ever-accounting' ),
				'menu_title' => __( 'Sales', 'wp-ever-accounting' ),
				'capability' => 'manage_accounting',
				'menu_slug'  => 'eac-sales',
				'position'   => 30,
			),
			array(
				'page_title' => __( 'Purchases', 'wp-ever-accounting' ),
				'menu_title' => __( 'Purchases', 'wp-ever-accounting' ),
				'capability' => 'manage_accounting',
				'menu_slug'  => 'eac-purchases',
				'position'   => 40,
			),
			array(
				'page_title' => __( 'Banking', 'wp-ever-accounting' ),
				'menu_title' => __( 'Banking', 'wp-ever-accounting' ),
				'capability' => 'manage_accounting',
				'menu_slug'  => 'eac-banking',
				'position'   => 50,
			),
			array(
				'page_title' => __( 'Tools', 'wp-ever-accounting' ),
				'menu_title' => __( 'Tools', 'wp-ever-accounting' ),
				'capability' => 'manage_accounting',
				'menu_slug'  => 'eac-tools',
				'position'   => 60,
			),
			array(
				'page_title' => __( 'Reports', 'wp-ever-accounting' ),
				'menu_title' => __( 'Reports', 'wp-ever-accounting' ),
				'capability' => 'eac_manage_report',
				'menu_slug'  => 'eac-reports',
				'position'   => 90,
			),
			array(
				'page_title' => __( 'Settings', 'wp-ever-accounting' ),
				'menu_title' => __( 'Settings', 'wp-ever-accounting' ),
				'capability' => 'eac_manage_options',
				'menu_slug'  => 'eac-settings',
				'position'   => 100,
			),
		);

		return apply_filters( 'eac_admin_menus', $menus );
	}

	/**
	 * Get page ids.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public static function get_screen_ids() {
		$screen_ids = array(
			'toplevel_page_' . Menus::PARENT_SLUG,
			Menus::PARENT_SLUG . '_page_dashboard',
		);

		foreach ( self::get_menus() as $page ) {
			$screen_ids[] = Menus::PARENT_SLUG . '_page_' . $page['menu_slug'];
		}

		return $screen_ids;
	}
}
