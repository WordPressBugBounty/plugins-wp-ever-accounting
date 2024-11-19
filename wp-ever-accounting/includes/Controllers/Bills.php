<?php

namespace EverAccounting\Controllers;

use EverAccounting\Models\Bill;

defined( 'ABSPATH' ) || exit;

/**
 * Bills controller.
 *
 * @since 1.0.0
 * @author  Sultan Nasir Uddin <manikdrmc@gmail.com>
 * @package EverAccounting
 * @subpackage Controllers
 */
class Bills {

	/**
	 * Get a bill from the database.
	 *
	 * @param mixed $bill Bill ID or object.
	 *
	 * @since 1.1.6
	 * @return Bill|null Bill object if found, otherwise null.
	 */
	public function get( $bill ) {
		return Bill::find( $bill );
	}

	/**
	 * Get instance of a bill or make a new instance.
	 *
	 * @param mixed $bill Bill ID or object.
	 */

	/**
	 * Insert a new bill into the database.
	 *
	 * @param array $data Bill data.
	 * @param bool  $wp_error Optional. Whether to return a WP_Error on failure. Default false.
	 *
	 * @since 1.1.0
	 * @return Bill|false|\WP_Error Bill object on success, false or WP_Error on failure.
	 */
	public function insert( $data, $wp_error = true ) {
		return Bill::insert( $data, $wp_error );
	}

	/**
	 * Delete a bill from the database.
	 *
	 * @param int $id Bill ID.
	 *
	 * @since 1.1.0
	 * @return bool True on success, false on failure.
	 */
	public function delete( $id ) {
		$bill = $this->get( $id );
		if ( ! $bill ) {
			return false;
		}

		return $bill->delete();
	}

	/**
	 * Get query results for bills.
	 *
	 * @param array $args Query arguments.
	 * @param bool  $count Optional. Whether to return only the total found bills for the query.
	 *
	 * @since 1.1.0
	 * @return array|int|Bill[] Array of bill objects, the total found bills for the query, or the total found bills for the query as int when `$count` is true.
	 */
	public function query( $args = array(), $count = false ) {
		if ( $count ) {
			return Bill::count( $args );
		}

		return Bill::results( $args );
	}

	/**
	 * Get bill statuses.
	 *
	 * @since 1.1.0
	 * @return array Bill statuses.
	 */
	public function get_statuses() {
		$statuses = array(
			'draft'     => esc_html__( 'Draft', 'wp-ever-accounting' ),
			'received'  => esc_html__( 'Received', 'wp-ever-accounting' ),
			'partial'   => esc_html__( 'Partial', 'wp-ever-accounting' ),
			'paid'      => esc_html__( 'Paid', 'wp-ever-accounting' ),
			'overdue'   => esc_html__( 'Overdue', 'wp-ever-accounting' ),
			'cancelled' => esc_html__( 'Cancelled', 'wp-ever-accounting' ),
		);

		return apply_filters( 'eac_bill_statuses', $statuses );
	}

	/**
	 * Get bill columns.
	 *
	 * @since 1.1.0
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'item'     => get_option( 'eac_bill_col_item_label', esc_html__( 'Item', 'wp-ever-accounting' ) ),
			'quantity' => get_option( 'eac_bill_col_quantity_label', esc_html__( 'Quantity', 'wp-ever-accounting' ) ),
			'price'    => get_option( 'eac_bill_col_price_label', esc_html__( 'Price', 'wp-ever-accounting' ) ),
			'tax'      => get_option( 'eac_bill_col_tax_label', esc_html__( 'Tax', 'wp-ever-accounting' ) ),
			'subtotal' => get_option( 'eac_bill_col_subtotal_label', esc_html__( 'Subtotal', 'wp-ever-accounting' ) ),
		);

		return apply_filters( 'eac_bill_columns', $columns );
	}
}
