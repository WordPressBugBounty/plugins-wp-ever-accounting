<?php

namespace EverAccounting;

defined( 'ABSPATH' ) || exit;

/**
 * Class Transactions.
 *
 * @since 1.0.0
 * @package EverAccounting
 */
class Transactions extends B8\Component {

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register(): void {
		add_action( 'ever_accounting_payment_saved', array( $this, 'reset_report_cache' ) );
		add_action( 'ever_accounting_payment_deleted', array( $this, 'reset_report_cache' ) );
		add_action( 'ever_accounting_expense_saved', array( $this, 'reset_report_cache' ) );
		add_action( 'ever_accounting_expense_deleted', array( $this, 'reset_report_cache' ) );
	}

	/**
	 * Reset report cache.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function reset_report_cache() {
		delete_transient( 'eac_payments_reports' );
		delete_transient( 'eac_expenses_reports' );
		delete_transient( 'eac_profit_reports' );
	}
}
