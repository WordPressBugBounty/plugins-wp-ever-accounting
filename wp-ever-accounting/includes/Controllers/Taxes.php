<?php

namespace EverAccounting\Controllers;

use EverAccounting\Models\Tax;

defined( 'ABSPATH' ) || exit;

/**
 * Taxes controller.
 *
 * @since 1.0.0
 * @author  Sultan Nasir Uddin <manikdrmc@gmail.com>
 * @package EverAccounting
 * @subpackage Controllers
 */
class Taxes {

	/**
	 * Get a tax from the database.
	 *
	 * @param mixed $tax Tax ID or object.
	 *
	 * @since 1.1.6
	 * @return Tax|null Tax object if found, otherwise null.
	 */
	public function get( $tax ) {
		return Tax::find( $tax );
	}

	/**
	 * Insert a new tax into the database.
	 *
	 * @param array $data Tax data.
	 * @param bool  $wp_error Optional. Whether to return a WP_Error on failure. Default false.
	 *
	 * @since 1.1.0
	 * @return Tax|false|\WP_Error Tax object on success, false or WP_Error on failure.
	 */
	public function insert( $data, $wp_error = true ) {
		return Tax::insert( $data, $wp_error );
	}

	/**
	 * Delete a tax from the database.
	 *
	 * @param int $id Tax ID.
	 *
	 * @since 1.1.0
	 * @return bool True on success, false on failure.
	 */
	public function delete( $id ) {
		$tax = $this->get( $id );
		if ( ! $tax ) {
			return false;
		}

		return $tax->delete();
	}

	/**
	 * Get query results for taxes.
	 *
	 * @param array $args Query arguments.
	 * @param bool  $count Optional. Whether to return only the total found taxes for the query.
	 *
	 * @since 1.1.0
	 * @return array|int|Tax[] Array of tax objects, the total found taxes for the query, or the total found taxes for the query as int when `$count` is true.
	 */
	public function query( $args = array(), $count = false ) {
		if ( $count ) {
			return Tax::count( $args );
		}

		return Tax::results( $args );
	}
}
