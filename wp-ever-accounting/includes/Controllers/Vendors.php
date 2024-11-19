<?php

namespace EverAccounting\Controllers;

use EverAccounting\Models\Vendor;

defined( 'ABSPATH' ) || exit;

/**
 * Vendors controller.
 *
 * @since 1.0.0
 * @author  Sultan Nasir Uddin <manikdrmc@gmail.com>
 * @package EverAccounting
 * @subpackage Controllers
 */
class Vendors {

	/**
	 * Get a vendor from the database.
	 *
	 * @param mixed $vendor Vendor ID or object.
	 *
	 * @since 1.1.6
	 * @return Vendor|null Vendor object if found, otherwise null.
	 */
	public function get( $vendor ) {
		return Vendor::find( $vendor );
	}

	/**
	 * Insert a new vendor into the database.
	 *
	 * @param array $data Vendor data.
	 * @param bool  $wp_error Optional. Whether to return a WP_Error on failure. Default false.
	 *
	 * @since 1.1.0
	 * @return Vendor|false|\WP_Error Vendor object on success, false or WP_Error on failure.
	 */
	public function insert( $data, $wp_error = true ) {
		return Vendor::insert( $data, $wp_error );
	}

	/**
	 * Delete a vendor from the database.
	 *
	 * @param int $id Vendor ID.
	 *
	 * @since 1.1.0
	 * @return bool True on success, false on failure.
	 */
	public function delete( $id ) {
		$vendor = $this->get( $id );
		if ( ! $vendor ) {
			return false;
		}

		return $vendor->delete();
	}

	/**
	 * Get query results for vendors.
	 *
	 * @param array $args Query arguments.
	 * @param bool  $count Optional. Whether to return only the total found vendors for the query.
	 *
	 * @since 1.1.0
	 * @return array|int|Vendor[] Array of vendor objects, the total found vendors for the query, or the total found vendors for the query as int when `$count` is true.
	 */
	public function query( $args = array(), $count = false ) {
		if ( $count ) {
			return Vendor::count( $args );
		}

		return Vendor::results( $args );
	}
}
