<?php

namespace EverAccounting\Controllers;

use EverAccounting\Models\Expense;
use EverAccounting\Models\Payment;
use EverAccounting\Models\Transfer;

defined( 'ABSPATH' ) || exit;

/**
 * Transfers controller.
 *
 * @since 1.0.0
 * @author  Sultan Nasir Uddin <manikdrmc@gmail.com>
 * @package EverAccounting
 * @subpackage Controllers
 */
class Transfers {

	/**
	 * Get an item from the database.
	 *
	 * @param mixed $transfer Transfer ID or object.
	 *
	 * @since 1.1.6
	 * @return Transfer|null Transfer object if found, otherwise null.
	 */
	public function get( $transfer ) {
		return Transfer::find( $transfer );
	}

	/**
	 * Insert a new item into the database.
	 *
	 * @param array $data Transfer data.
	 * @param bool  $wp_error Optional. Whether to return a WP_Error on failure. Default false.
	 *
	 * @since 1.1.0
	 * @return Transfer|false|\WP_Error Transfer object on success, false or WP_Error on failure.
	 */
	public function insert( $data, $wp_error = true ) {
		return Transfer::insert( $data, $wp_error );
	}

	/**
	 * Delete an item from the database.
	 *
	 * @param int $id Transfer ID.
	 *
	 * @since 1.1.0
	 * @return bool True on success, false on failure.
	 */
	public function delete( $id ) {
		$transfer = $this->get( $id );
		if ( ! $transfer ) {
			return false;
		}

		return $transfer->delete();
	}

	/**
	 * Get query results for items.
	 *
	 * @param array $args Query arguments.
	 * @param bool  $count Optional. Whether to return only the total found items for the query.
	 *
	 * @since 1.1.0
	 * @return array|int|Transfer[] Array of item objects, the total found items for the query, or the total found items for the query as int when `$count` is true.
	 */
	public function query( $args = array(), $count = false ) {
		if ( $count ) {
			return Transfer::count( $args );
		}

		return Transfer::results( $args );
	}
}
