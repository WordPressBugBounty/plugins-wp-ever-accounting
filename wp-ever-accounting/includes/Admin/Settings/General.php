<?php

namespace EverAccounting\Admin\Settings;

use EverAccounting\Utilities\I18nUtil;

defined( 'ABSPATH' ) || exit;

/**
 * Class General.
 *
 * @since   1.0.0
 * @package EverAccounting\Admin\Settings
 */
class General extends Page {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct( 'general', __( 'General', 'wp-ever-accounting' ) );
	}

	/**
	 * Get settings tab sections.
	 *
	 * @since 3.0.0
	 * @return array
	 */
	protected function get_own_sections() {
		return array(
			''         => __( 'General', 'wp-ever-accounting' ),
			'currency' => __( 'Currency', 'wp-ever-accounting' ),
		);
	}

	/**
	 * Get settings.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_default_section_settings() {
		return array(
			array(
				'title' => __( 'Business Information', 'wp-ever-accounting' ),
				'type'  => 'title',
				'id'    => 'general_settings',
			),
			array(
				'title'       => __( 'Name', 'wp-ever-accounting' ),
				'desc'        => __( 'The name of your business. This will be used in the invoice, bill, and other documents.', 'wp-ever-accounting' ),
				'id'          => 'eac_business_name',
				'type'        => 'text',
				'placeholder' => 'e.g. XYZ Ltd.',
				'default'     => esc_html( get_bloginfo( 'name' ) ),
				'desc_tip'    => true,
			),
			array(
				'title'       => __( 'Email', 'wp-ever-accounting' ),
				'desc'        => __( 'The email address of your business. This will be used in the invoice, bill, and other documents.', 'wp-ever-accounting' ),
				'id'          => 'eac_business_email',
				'type'        => 'email',
				'placeholder' => get_option( 'admin_email' ),
				'default'     => get_option( 'admin_email' ),
				'desc_tip'    => true,
			),
			array(
				'title'       => __( 'Phone', 'wp-ever-accounting' ),
				'desc'        => __( 'The phone number of your business. This will be used in the invoice, bill, and other documents.', 'wp-ever-accounting' ),
				'id'          => 'eac_business_phone',
				'type'        => 'text',
				'placeholder' => 'e.g. +1 123 456 7890',
				'default'     => '',
				'desc_tip'    => true,
			),
			array(
				'title'       => __( 'Logo', 'wp-ever-accounting' ),
				'desc'        => __( 'The logo of your business. This will be used in the invoice, bill, and other documents.', 'wp-ever-accounting' ),
				'id'          => 'eac_business_logo',
				'type'        => 'text',
				'placeholder' => 'e.g. http://example.com/logo.png',
				'default'     => '',
				'desc_tip'    => true,
			),
			array(
				'title'       => __( 'Tax Number', 'wp-ever-accounting' ),
				'desc'        => __( 'The tax number of your business. This will be used in the invoice, bill, and other documents.', 'wp-ever-accounting' ),
				'id'          => 'eac_business_tax_number',
				'type'        => 'text',
				'placeholder' => 'e.g. 123456789',
				'default'     => '',
				'desc_tip'    => true,
			),
			array(
				'title'       => __( 'Financial Year Start', 'wp-ever-accounting' ),
				'desc'        => __( 'The start date of your financial year.', 'wp-ever-accounting' ),
				'id'          => 'eac_year_start_date',
				'type'        => 'text',
				'placeholder' => 'e.g. 01-01',
				'default'     => '01-01',
				'desc_tip'    => true,
				'class'       => 'eac_datepicker',
				'data-format' => 'mm-dd',
			),
			array(
				'type' => 'sectionend',
				'id'   => 'general_settings',
			),
			array(
				'title' => __( 'Business Address', 'wp-ever-accounting' ),
				'type'  => 'title',
				'id'    => 'business_address',
			),
			array(
				'title'       => __( 'Address', 'wp-ever-accounting' ),
				'desc'        => __( 'The street address of your business.', 'wp-ever-accounting' ),
				'id'          => 'eac_business_address',
				'type'        => 'text',
				'placeholder' => 'e.g. 123 Main Street',
				'default'     => '',
				'desc_tip'    => true,
			),
			array(
				'title'       => __( 'City', 'wp-ever-accounting' ),
				'desc'        => __( 'The city in which your business is located. This will be used in the invoice, bill, and other documents.', 'wp-ever-accounting' ),
				'id'          => 'eac_business_city',
				'type'        => 'text',
				'placeholder' => 'e.g. Manhattan',
				'default'     => '',
				'desc_tip'    => true,
			),
			array(
				'title'       => __( 'State', 'wp-ever-accounting' ),
				'desc'        => __( 'The state in which your business is located.', 'wp-ever-accounting' ),
				'id'          => 'eac_business_state',
				'type'        => 'text',
				'placeholder' => 'e.g. New York',
				'default'     => '',
				'desc_tip'    => true,
			),
			array(
				'title'       => __( 'ZIP', 'wp-ever-accounting' ),
				'desc'        => __( 'The postcode or ZIP code of your business (if any). This will be used in the invoice, bill, and other documents.', 'wp-ever-accounting' ),
				'id'          => 'eac_business_postcode',
				'type'        => 'text',
				'placeholder' => 'e.g. 10001',
				'default'     => '',
				'desc_tip'    => true,
			),
			array(
				'title'       => __( 'Country', 'wp-ever-accounting' ),
				'desc'        => __( 'The country in which your business is located.', 'wp-ever-accounting' ),
				'id'          => 'eac_business_country',
				'type'        => 'select',
				'options'     => I18nUtil::get_countries(),
				'class'       => 'eac_select2',
				'default'     => 'US',
				'placeholder' => __( 'Select a country&hellip;', 'wp-ever-accounting' ),
				'desc_tip'    => true,
			),
			array(
				'type' => 'sectionend',
				'id'   => 'business_address',
			),
		);
	}

	/**
	 * Get currency section settings.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_currency_section_settings() {
		return array(
			// currency options.
			array(
				'title' => __( 'Currency Settings', 'wp-ever-accounting' ),
				'type'  => 'title',
				'id'    => 'currency_options',
			),
			// currency.
			array(
				'title'    => __( 'Base Currency', 'wp-ever-accounting' ),
				'desc'     => __( 'The base currency of your business. Currency can not be changed once you have recorded any transaction.', 'wp-ever-accounting' ),
				'id'       => 'eac_base_currency',
				'type'     => 'select',
				'default'  => 'USD',
				'class'    => 'eac_select2',
				'options'  => wp_list_pluck( eac_get_currencies(), 'formatted_name', 'code' ),
				'value'    => get_option( 'eac_base_currency', 'USD' ),
				'desc_tip' => true,
			),

			array(
				'title'    => __( 'Currency Position', 'wp-ever-accounting' ),
				'desc'     => __( 'The position of the currency symbol.', 'wp-ever-accounting' ),
				'id'       => 'eac_currency_position',
				'type'     => 'select',
				'default'  => 'before',
				'options'  => array(
					'before' => __( 'Before', 'wp-ever-accounting' ),
					'after'  => __( 'After', 'wp-ever-accounting' ),
				),
				'desc_tip' => true,
			),
			array(
				'title'       => __( 'Thousand Separator', 'wp-ever-accounting' ),
				'desc'        => __( 'The character used to separate thousands.', 'wp-ever-accounting' ),
				'id'          => 'eac_thousand_separator',
				'type'        => 'text',
				'placeholder' => ',',
				'default'     => ',',
				'desc_tip'    => true,
			),
			array(
				'title'       => __( 'Decimal Separator', 'wp-ever-accounting' ),
				'desc'        => __( 'The character used to separate decimals.', 'wp-ever-accounting' ),
				'id'          => 'eac_decimal_separator',
				'type'        => 'text',
				'placeholder' => '.',
				'default'     => '.',
				'desc_tip'    => true,
			),
			array(
				'title'       => __( 'Currency Precision', 'wp-ever-accounting' ),
				'desc'        => __( 'The number of decimal places to display.', 'wp-ever-accounting' ),
				'id'          => 'eac_currency_precision',
				'type'        => 'number',
				'placeholder' => '2',
				'default'     => 2,
				'desc_tip'    => true,
			),
			// exchange rates.
			array(
				'title' => __( 'Exchange Rates', 'wp-ever-accounting' ),
				'id'    => 'eac_exchange_rates',
				'type'  => 'exchange_rates',
			),

			array(
				'type' => 'sectionend',
				'id'   => 'currency_options',
			),
		);
	}
}
