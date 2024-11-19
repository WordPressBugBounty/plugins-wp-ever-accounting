<?php

namespace EverAccounting;

defined( 'ABSPATH' ) || exit;

/**
 * Caches class.
 *
 * @since 2.0.0
 * @package EverAccounting
 */
class Caches {

	/**
	 * Cache constructor.
	 */
	public function __construct() {
		add_action( 'eac_payment_saved', array( $this, 'clear_payment_cache' ) );
		add_action( 'eac_payment_deleted', array( $this, 'clear_payment_cache' ) );
		add_action( 'eac_expense_saved', array( $this, 'clear_expense_cache' ) );
		add_action( 'eac_expense_deleted', array( $this, 'clear_expense_cache' ) );
	}

	/**
	 * Clear payment cache.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function clear_payment_cache() {
		delete_transient( 'eac_payments_report' );
		delete_transient( 'eac_profits_report' );
	}

	/**
	 * Clear expense cache.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function clear_expense_cache() {
		delete_transient( 'eac_expenses_report' );
		delete_transient( 'eac_profits_report' );
	}
}
