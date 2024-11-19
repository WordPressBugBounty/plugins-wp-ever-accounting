<?php

namespace EverAccounting\Controllers;

use EverAccounting\Models\Customer;

defined( 'ABSPATH' ) || exit;

/**
 * Customers controller.
 *
 * @since 1.0.0
 * @author  Sultan Nasir Uddin <manikdrmc@gmail.com>
 * @package EverAccounting
 * @subpackage Controllers
 */
class Customers {

	/**
	 * Get a customer from the database.
	 *
	 * @param mixed $customer Customer ID or object.
	 *
	 * @since 1.1.6
	 * @return Customer|null Customer object if found, otherwise null.
	 */
	public function get( $customer ) {
		return Customer::find( $customer );
	}

	/**
	 * Insert a new customer into the database.
	 *
	 * @param array $data Customer data.
	 * @param bool  $wp_error Optional. Whether to return a WP_Error on failure. Default false.
	 *
	 * @since 1.1.0
	 * @return Customer|false|\WP_Error Customer object on success, false or WP_Error on failure.
	 */
	public function insert( $data, $wp_error = true ) {
		return Customer::insert( $data, $wp_error );
	}

	/**
	 * Delete a customer from the database.
	 *
	 * @param int $id Customer ID.
	 *
	 * @since 1.1.0
	 * @return bool True on success, false on failure.
	 */
	public function delete( $id ) {
		$customer = $this->get( $id );
		if ( ! $customer ) {
			return false;
		}

		return $customer->delete();
	}

	/**
	 * Get query results for customers.
	 *
	 * @param array $args Query arguments.
	 * @param bool  $count Optional. Whether to return only the total found customers for the query.
	 *
	 * @since 1.1.0
	 * @return array|int|Customer[] Array of customer objects, the total found customers for the query, or the total found customers for the query as int when `$count` is true.
	 */
	public function query( $args = array(), $count = false ) {
		if ( $count ) {
			return Customer::count( $args );
		}

		return Customer::results( $args );
	}
}
