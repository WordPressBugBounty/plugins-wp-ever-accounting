<?php

namespace EverAccounting\Controllers;

use EverAccounting\Models\Transaction;

defined( 'ABSPATH' ) || exit;

/**
 * Transactions controller.
 *
 * @since 1.0.0
 * @author  Sultan Nasir Uddin <manikdrmc@gmail.com>
 * @package EverAccounting
 * @subpackage Controllers
 */
class Transactions {

	/**
	 * Get a transaction from the database.
	 *
	 * @param mixed $transaction Transaction ID or object.
	 *
	 * @since 1.1.6
	 * @return Transaction|null Transaction object if found, otherwise null.
	 */
	public function get( $transaction ) {
		return Transaction::find( $transaction );
	}

	/**
	 * Insert a new transaction into the database.
	 *
	 * @param array $data Transaction data.
	 * @param bool  $wp_error Optional. Whether to return a WP_Error on failure. Default false.
	 *
	 * @since 1.1.0
	 * @return Transaction|false|\WP_Error Transaction object on success, false or WP_Error on failure.
	 */
	public function insert( $data, $wp_error = true ) {
		return Transaction::insert( $data, $wp_error );
	}

	/**
	 * Delete a transaction from the database.
	 *
	 * @param int $id Transaction ID.
	 *
	 * @since 1.1.0
	 * @return bool True on success, false on failure.
	 */
	public function delete( $id ) {
		$transaction = $this->get( $id );
		if ( ! $transaction ) {
			return false;
		}

		return $transaction->delete();
	}

	/**
	 * Get query results for transactions.
	 *
	 * @param array $args Query arguments.
	 * @param bool  $count Optional. Whether to return only the total found transactions for the query.
	 *
	 * @since 1.1.0
	 * @return array|int|Transaction[] Array of transaction objects, the total found transactions for the query, or the total found transactions for the query as int when `$count` is true.
	 */
	public function query( $args = array(), $count = false ) {
		if ( $count ) {
			return Transaction::count( $args );
		}

		return Transaction::results( $args );
	}

	/**
	 * Get transaction types.
	 *
	 * @since 1.0.2
	 * @return array
	 */
	public function get_types() {
		return apply_filters(
			'eac_transaction_types',
			array(
				'payment' => esc_html__( 'Payment', 'wp-ever-accounting' ),
				'expense' => esc_html__( 'Expense', 'wp-ever-accounting' ),
			)
		);
	}
}
