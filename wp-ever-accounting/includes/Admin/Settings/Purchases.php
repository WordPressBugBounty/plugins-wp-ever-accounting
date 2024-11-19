<?php

namespace EverAccounting\Admin\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Class Purchases.
 *
 * @since   1.0.0
 * @package EverAccounting\Admin\Settings
 */
class Purchases extends Page {
	/**
	 * Purchases constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct( 'purchases', __( 'Purchases', 'wp-ever-accounting' ) );
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
			'bills' => __( 'Bills', 'wp-ever-accounting' ),
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
				'title' => __( 'Expense Settings', 'wp-ever-accounting' ),
				'desc'  => __( 'Customize how your expense number gets generated automatically when you create a new expense.', 'wp-ever-accounting' ),
				'type'  => 'title',
				'id'    => 'expense_settings',
			),
			array(
				'title'       => __( 'Number Prefix', 'wp-ever-accounting' ),
				'desc'        => __( 'The prefix of the expense number.', 'wp-ever-accounting' ),
				'id'          => 'eac_expense_prefix',
				'type'        => 'text',
				'placeholder' => 'e.g. EXP-',
				'default'     => 'EXP-',
				'desc_tip'    => true,
			),
			array(
				'title'       => __( 'Minimum Digits', 'wp-ever-accounting' ),
				'desc'        => __( 'The minimum digits of the expense number.', 'wp-ever-accounting' ),
				'id'          => 'eac_expense_digits',
				'type'        => 'number',
				'placeholder' => 'e.g. 4',
				'default'     => 4,
				'desc_tip'    => true,
			),
			array(
				'type' => 'sectionend',
				'id'   => 'expense_settings',
			),
		);
	}

	/**
	 * Get expenses settings array.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function get_bills_section_settings() {
		return array(
			array(
				'title' => __( 'Bill Settings', 'wp-ever-accounting' ),
				'desc'  => __( 'Customize how your bill number gets generated automatically when you create a new bill.', 'wp-ever-accounting' ),
				'type'  => 'title',
				'id'    => 'general_settings',
			),
			// prefix.
			array(
				'title'       => __( 'Number Prefix', 'wp-ever-accounting' ),
				'desc'        => __( 'The prefix of the bill number.', 'wp-ever-accounting' ),
				'id'          => 'eac_bill_prefix',
				'type'        => 'text',
				'placeholder' => 'e.g. BILL-',
				'default'     => 'BILL-',
				'desc_tip'    => true,
			),
			// minimum digits of bill number.
			array(
				'title'       => __( 'Minimum Digits', 'wp-ever-accounting' ),
				'desc'        => __( 'The minimum digits of the bill number.', 'wp-ever-accounting' ),
				'id'          => 'eac_bill_digits',
				'type'        => 'number',
				'placeholder' => 'e.g. 4',
				'default'     => 4,
				'desc_tip'    => true,
			),
			// due date.
			array(
				'title'       => __( 'Due Date', 'wp-ever-accounting' ),
				'desc'        => __( 'Specify how due date is automatically set when you create a bill.', 'wp-ever-accounting' ),
				'id'          => 'eac_bill_due_date',
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
				'title' => __( 'Bill Defaults', 'wp-ever-accounting' ),
				'desc'  => __( 'Customize the default values of your bills.', 'wp-ever-accounting' ),
				'type'  => 'title',
				'id'    => 'defaults_settings',
			),
			// note.
			array(
				'title'       => __( 'Notes', 'wp-ever-accounting' ),
				'desc'        => __( 'The note that will be added to the bill automatically when you create a new bill.', 'wp-ever-accounting' ),
				'id'          => 'eac_bill_note',
				'type'        => 'textarea',
				'placeholder' => 'e.g. Thank you for your business!',
				'default'     => __( 'Thank you for your business!', 'wp-ever-accounting' ),
				'desc_tip'    => true,
			),
			// terms.
			array(
				'title'       => __( 'Terms', 'wp-ever-accounting' ),
				'desc'        => __( 'The terms that will be added to the bill automatically when you create a new bill.', 'wp-ever-accounting' ),
				'id'          => 'eac_bill_terms',
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
				'title' => __( 'Bill Columns', 'wp-ever-accounting' ),
				'desc'  => __( 'Customize the columns of your bills.', 'wp-ever-accounting' ),
				'type'  => 'title',
				'id'    => 'columns_settings',
			),
			// item name.
			array(
				'title'       => __( 'Item Label', 'wp-ever-accounting' ),
				'desc'        => __( 'The name of the item column.', 'wp-ever-accounting' ),
				'id'          => 'eac_bill_col_item_label',
				'type'        => 'text',
				'placeholder' => 'e.g. Item',
				'default'     => __( 'Items', 'wp-ever-accounting' ),
				'desc_tip'    => true,
			),
			// price label.
			array(
				'title'       => __( 'Price Label', 'wp-ever-accounting' ),
				'desc'        => __( 'The name of the price column.', 'wp-ever-accounting' ),
				'id'          => 'eac_bill_col_price_label',
				'type'        => 'text',
				'placeholder' => 'e.g. Price',
				'default'     => __( 'Price', 'wp-ever-accounting' ),
				'desc_tip'    => true,
			),
			// quantity label.
			array(
				'title'       => __( 'Quantity Label', 'wp-ever-accounting' ),
				'desc'        => __( 'The name of the quantity column.', 'wp-ever-accounting' ),
				'id'          => 'eac_bill_col_quantity_label',
				'type'        => 'text',
				'placeholder' => 'e.g. Quantity',
				'default'     => __( 'Quantity', 'wp-ever-accounting' ),
				'desc_tip'    => true,
			),
			// tax label.
			array(
				'title'       => __( 'Tax Label', 'wp-ever-accounting' ),
				'desc'        => __( 'The name of the tax column.', 'wp-ever-accounting' ),
				'id'          => 'eac_bill_col_tax_label',
				'type'        => 'text',
				'placeholder' => 'e.g. Tax',
				'default'     => __( 'Tax', 'wp-ever-accounting' ),
				'desc_tip'    => true,
			),
			// subtotal label.
			array(
				'title'       => __( 'Subtotal Label', 'wp-ever-accounting' ),
				'desc'        => __( 'The name of the subtotal column.', 'wp-ever-accounting' ),
				'id'          => 'eac_bill_col_subtotal_label',
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
