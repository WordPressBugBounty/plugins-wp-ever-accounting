<?php

namespace EverAccounting\Controllers;

use EverAccounting\Models\Account;

defined( 'ABSPATH' ) || exit;

/**
 * Accounts controller.
 *
 * @since 1.0.0
 * @author  Sultan Nasir Uddin <manikdrmc@gmail.com>
 * @package EverAccounting
 * @subpackage Controllers
 */
class Accounts {

	/**
	 * Get an account from the database.
	 *
	 * @param mixed $account Account ID or object.
	 *
	 * @since 1.1.6
	 * @return Account|null Account object if found, otherwise null.
	 */
	public function get( $account ) {
		return Account::find( $account );
	}

	/**
	 * Insert a new account into the database.
	 *
	 * @param array $data Account data.
	 * @param bool  $wp_error Optional. Whether to return a WP_Error on failure. Default false.
	 *
	 * @since 1.1.0
	 * @return Account|false|\WP_Error Account object on success, false or WP_Error on failure.
	 */
	public function insert( $data, $wp_error = true ) {
		return Account::insert( $data, $wp_error );
	}

	/**
	 * Delete an account from the database.
	 *
	 * @param int $id Account ID.
	 *
	 * @since 1.1.0
	 * @return bool True on success, false on failure.
	 */
	public function delete( $id ) {
		$account = $this->get( $id );
		if ( ! $account ) {
			return false;
		}

		return $account->delete();
	}

	/**
	 * Get query results for accounts.
	 *
	 * @param array $args Query arguments.
	 * @param bool  $count Optional. Whether to return only the total found accounts for the query.
	 *
	 * @since 1.1.0
	 * @return array|int|Account[] Array of account objects, the total found accounts for the query, or the total found accounts for the query as int when `$count` is true.
	 */
	public function query( $args = array(), $count = false ) {
		if ( $count ) {
			return Account::count( $args );
		}

		return Account::results( $args );
	}

	/**
	 * Get account types.
	 *
	 * @since 1.0.2
	 * @return array
	 */
	public function get_types() {
		$account_types = array(
			'bank' => __( 'Bank', 'wp-ever-accounting' ),
			'card' => __( 'Card', 'wp-ever-accounting' ),
		);

		return apply_filters( 'eac_account_types', $account_types );
	}
}
