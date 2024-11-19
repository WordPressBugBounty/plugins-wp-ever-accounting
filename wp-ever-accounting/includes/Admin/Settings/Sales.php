<?php

namespace EverAccounting\Admin\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Class Sales.
 *
 * @since   1.0.0
 * @package EverAccounting\Admin\Settings
 */
class Sales extends Page {
	/**
	 * Sales constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct( 'sales', __( 'Sales', 'wp-ever-accounting' ) );
	}

	/**
	 * Get settings tab sections.
	 *
	 * @since 3.0.0
	 * @return array
	 */
	protected function get_own_sections() {
		return array(
			''         => __( 'Options', 'wp-ever-accounting' ),
			'invoices' => __( 'Invoices', 'wp-ever-accounting' ),
		);
	}

	/**
	 * Get settings or the default section.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function get_default_section_settings() {
		return array(
			array(
				'title' => __( 'Payment Settings', 'wp-ever-accounting' ),
				'desc'  => __( 'Customize how your payment number gets generated automatically when you create a new payment.', 'wp-ever-accounting' ),
				'type'  => 'title',
				'id'    => 'payment_settings',
			),
			array(
				'title'       => __( 'Number Prefix', 'wp-ever-accounting' ),
				'desc'        => __( 'The prefix of the payment number.', 'wp-ever-accounting' ),
				'id'          => 'eac_payment_prefix',
				'type'        => 'text',
				'placeholder' => 'e.g. PAY-',
				'default'     => 'PAY-',
				'desc_tip'    => true,
			),
			array(
				'title'       => __( 'Minimum Digits', 'wp-ever-accounting' ),
				'desc'        => __( 'The minimum digits of the payment number.', 'wp-ever-accounting' ),
				'id'          => 'eac_payment_digits',
				'type'        => 'number',
				'placeholder' => 'e.g. 4',
				'default'     => 4,
				'desc_tip'    => true,
			),
			array(
				'type' => 'sectionend',
				'id'   => 'payment_settings',
			),
		);
	}

	/**
	 * Get settings or the default section.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function get_invoices_section_settings() {
		return array(
			array(
				'title' => __( 'Invoice Settings', 'wp-ever-accounting' ),
				'desc'  => __( 'Customize how your invoice number gets generated automatically when you create a new invoice.', 'wp-ever-accounting' ),
				'type'  => 'title',
				'id'    => 'general_settings',
			),
			// prefix.
			array(
				'title'       => __( 'Number Prefix', 'wp-ever-accounting' ),
				'desc'        => __( 'The prefix of the invoice number.', 'wp-ever-accounting' ),
				'id'          => 'eac_invoice_prefix',
				'type'        => 'text',
				'placeholder' => 'e.g. INV-',
				'default'     => 'INV-',
				'desc_tip'    => true,
			),
			// minimum digits of invoice number.
			array(
				'title'       => __( 'Minimum Digits', 'wp-ever-accounting' ),
				'desc'        => __( 'The minimum digits of the invoice number.', 'wp-ever-accounting' ),
				'id'          => 'eac_invoice_digits',
				'type'        => 'number',
				'placeholder' => 'e.g. 4',
				'default'     => 4,
				'desc_tip'    => true,
			),
			// due date.
			array(
				'title'       => __( 'Due Date', 'wp-ever-accounting' ),
				'desc'        => __( 'Specify how due date is automatically set when you create an invoice.', 'wp-ever-accounting' ),
				'id'          => 'eac_invoice_due_date',
				'type'        => 'number',
				'placeholder' => 'e.g. 30',
				'default'     => 30,
				'desc_tip'    => true,
			),
			// end section.
			array(
				'type' => 'sectionend',
				'id'   => 'general_settings',
			),
			array(
				'title' => __( 'Invoice Defaults', 'wp-ever-accounting' ),
				'desc'  => __( 'Customize the default values of your invoices.', 'wp-ever-accounting' ),
				'type'  => 'title',
				'id'    => 'defaults_settings',
			),
			array(
				'title'       => __( 'Notes', 'wp-ever-accounting' ),
				'desc'        => __( 'The note that will be added to the invoice automatically when you create a new invoice.', 'wp-ever-accounting' ),
				'id'          => 'eac_invoice_note',
				'type'        => 'textarea',
				'placeholder' => 'e.g. Thank you for your business!',
				'default'     => __( 'Thank you for your business!', 'wp-ever-accounting' ),
				'desc_tip'    => true,
			),
			array(
				'title'       => __( 'Terms', 'wp-ever-accounting' ),
				'desc'        => __( 'The terms that will be added to the invoice automatically when you create a new invoice.', 'wp-ever-accounting' ),
				'id'          => 'eac_invoice_terms',
				'type'        => 'textarea',
				'placeholder' => 'e.g. Payment is due within 30 days.',
				'default'     => __( 'Payment is due within 30 days.', 'wp-ever-accounting' ),
				'desc_tip'    => true,
			),
			// end section.
			array(
				'type' => 'sectionend',
				'id'   => 'defaults_settings',
			),
			array(
				'title' => __( 'Invoice Columns', 'wp-ever-accounting' ),
				'desc'  => __( 'Customize the columns of your invoices.', 'wp-ever-accounting' ),
				'type'  => 'title',
				'id'    => 'columns_settings',
			),

			// item name.
			array(
				'title'       => __( 'Item Label', 'wp-ever-accounting' ),
				'desc'        => __( 'The name of the item column.', 'wp-ever-accounting' ),
				'id'          => 'eac_invoice_col_item_label',
				'type'        => 'text',
				'placeholder' => 'e.g. Item',
				'default'     => __( 'Items', 'wp-ever-accounting' ),
				'desc_tip'    => true,
			),
			// price label.
			array(
				'title'       => __( 'Price Label', 'wp-ever-accounting' ),
				'desc'        => __( 'The name of the price column.', 'wp-ever-accounting' ),
				'id'          => 'eac_invoice_col_price_label',
				'type'        => 'text',
				'placeholder' => 'e.g. Price',
				'default'     => __( 'Price', 'wp-ever-accounting' ),
				'desc_tip'    => true,
			),
			// quantity label.
			array(
				'title'       => __( 'Quantity Label', 'wp-ever-accounting' ),
				'desc'        => __( 'The name of the quantity column.', 'wp-ever-accounting' ),
				'id'          => 'eac_invoice_col_quantity_label',
				'type'        => 'text',
				'placeholder' => 'e.g. Quantity',
				'default'     => __( 'Quantity', 'wp-ever-accounting' ),
				'desc_tip'    => true,
			),
			// tax label.
			array(
				'title'       => __( 'Tax Label', 'wp-ever-accounting' ),
				'desc'        => __( 'The name of the tax column.', 'wp-ever-accounting' ),
				'id'          => 'eac_invoice_col_tax_label',
				'type'        => 'text',
				'placeholder' => 'e.g. Tax',
				'default'     => __( 'Tax', 'wp-ever-accounting' ),
				'desc_tip'    => true,
			),
			// subtotal label.
			array(
				'title'       => __( 'Subtotal Label', 'wp-ever-accounting' ),
				'desc'        => __( 'The name of the subtotal column.', 'wp-ever-accounting' ),
				'id'          => 'eac_invoice_col_subtotal_label',
				'type'        => 'text',
				'placeholder' => 'e.g. Subtotal',
				'default'     => __( 'Subtotal', 'wp-ever-accounting' ),
				'desc_tip'    => true,
			),
			// end section.
			array(
				'type' => 'sectionend',
				'id'   => 'columns_settings',
			),
		);
	}
}
