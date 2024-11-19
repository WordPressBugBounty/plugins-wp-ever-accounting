<?php

namespace EverAccounting\Admin\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Class Taxes
 *
 * @since   1.0.0
 * @package EverAccounting\Admin\Settings
 */
class Taxes extends Page {

	/**
	 * Taxes constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct( 'taxes', __( 'Taxes', 'wp-ever-accounting' ) );
	}

	/**
	 * Get settings tab sections.
	 *
	 * @since 3.0.0
	 * @return array
	 */
	protected function get_own_sections() {
		return array(
			''      => __( 'Options', 'wp-ever-accounting' ),
			'rates' => __( 'Rates', 'wp-ever-accounting' ),
		);
	}

	/**
	 * Get settings or the default section.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_default_section_settings() {
		return array(
			array(
				'title' => __( 'Tax options', 'wp-ever-accounting' ),
				'type'  => 'title',
				'id'    => 'tax_options',
			),
			array(
				'title'   => __( 'Enable Taxes', 'wp-ever-accounting' ),
				'desc'    => __( 'Enable tax rates and calculations.', 'wp-ever-accounting' ),
				'id'      => 'eac_tax_enabled',
				'type'    => 'checkbox',
				'default' => 'no',
			),
			array(
				'title'    => __( 'Display tax totals', 'wp-ever-accounting' ),
				'id'       => 'eac_tax_total_display',
				'type'     => 'select',
				'default'  => 'single',
				'desc_tip' => true,
				'options'  => array(
					'single'   => __( 'As a single total', 'wp-ever-accounting' ),
					'itemized' => __( 'Itemized', 'wp-ever-accounting' ),
				),
			),

			array(
				'type' => 'sectionend',
				'id'   => 'tax_options',
			),
		);
	}
}
