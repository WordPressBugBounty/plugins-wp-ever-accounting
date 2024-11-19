<?php

namespace EverAccounting\Controllers;

use EverAccounting\Models\Expense;

defined( 'ABSPATH' ) || exit;

/**
 * Expenses controller.
 *
 * @since 1.0.0
 * @author  Sultan Nasir Uddin <manikdrmc@gmail.com>
 * @package EverAccounting
 * @subpackage Controllers
 */
class Expenses {

	/**
	 * Get an expense from the database.
	 *
	 * @param mixed $expense Expense ID or object.
	 *
	 * @since 1.1.6
	 * @return Expense|null Expense object if found, otherwise null.
	 */
	public function get( $expense ) {
		return Expense::find( $expense );
	}

	/**
	 * Insert a new expense into the database.
	 *
	 * @param array $data Expense data.
	 * @param bool  $wp_error Optional. Whether to return a WP_Error on failure. Default false.
	 *
	 * @since 1.1.0
	 * @return Expense|false|\WP_Error Expense object on success, false or WP_Error on failure.
	 */
	public function insert( $data, $wp_error = true ) {
		return Expense::insert( $data, $wp_error );
	}

	/**
	 * Delete an expense from the database.
	 *
	 * @param int $id Expense ID.
	 *
	 * @since 1.1.0
	 * @return bool True on success, false on failure.
	 */
	public function delete( $id ) {
		$expense = $this->get( $id );
		if ( ! $expense ) {
			return false;
		}

		return $expense->delete();
	}

	/**
	 * Get query results for expenses.
	 *
	 * @param array $args Query arguments.
	 * @param bool  $count Optional. Whether to return only the total found expenses for the query.
	 *
	 * @since 1.1.0
	 * @return array|int|Expense[] Array of expense objects, the total found expenses for the query, or the total found expenses for the query as int when `$count` is true.
	 */
	public function query( $args = array(), $count = false ) {
		if ( $count ) {
			return Expense::count( $args );
		}

		return Expense::results( $args );
	}
}
