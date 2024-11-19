<?php

namespace EverAccounting\Controllers;

use EverAccounting\Models\Payment;

defined( 'ABSPATH' ) || exit;

/**
 * Payments controller.
 *
 * @since 1.0.0
 * @author  Sultan Nasir Uddin <manikdrmc@gmail.com>
 * @package EverAccounting
 * @subpackage Controllers
 */
class Payments {

	/**
	 * Get a payment from the database.
	 *
	 * @param mixed $payment Payment ID or object.
	 *
	 * @since 1.1.6
	 * @return Payment|null Payment object if found, otherwise null.
	 */
	public function get( $payment ) {
		return Payment::find( $payment );
	}

	/**
	 * Insert a new payment into the database.
	 *
	 * @param array $data Payment data.
	 * @param bool  $wp_error Optional. Whether to return a WP_Error on failure. Default false.
	 *
	 * @since 1.1.0
	 * @return Payment|false|\WP_Error Payment object on success, false or WP_Error on failure.
	 */
	public function insert( $data, $wp_error = true ) {
		return Payment::insert( $data, $wp_error );
	}

	/**
	 * Delete a payment from the database.
	 *
	 * @param int $id Payment ID.
	 *
	 * @since 1.1.0
	 * @return bool True on success, false on failure.
	 */
	public function delete( $id ) {
		$payment = $this->get( $id );
		if ( ! $payment ) {
			return false;
		}

		return $payment->delete();
	}

	/**
	 * Get query results for payments.
	 *
	 * @param array $args Query arguments.
	 * @param bool  $count Optional. Whether to return only the total found payments for the query.
	 *
	 * @since 1.1.0
	 * @return array|int|Payment[] Array of payment objects, the total found payments for the query, or the total found payments for the query as int when `$count` is true.
	 */
	public function query( $args = array(), $count = false ) {
		if ( $count ) {
			return Payment::count( $args );
		}

		return Payment::results( $args );
	}
}
