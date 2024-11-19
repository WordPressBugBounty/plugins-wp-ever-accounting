<?php

namespace EverAccounting;

use EverAccounting\Models\Expense;
use EverAccounting\Models\Payment;

defined( 'ABSPATH' ) || exit;

/**
 * Class Banking
 *
 * @package EverAccounting
 */
class Banking {

	/**
	 * Banking constructor.
	 */
	public function __construct() {
		add_action( 'eac_payment_inserted', array( __CLASS__, 'update_account_balance' ) );
		add_action( 'eac_payment_deleted', array( __CLASS__, 'update_account_balance' ) );
		add_action( 'eac_payment_updated', array( __CLASS__, 'update_account_balance' ) );
		add_action( 'eac_expense_inserted', array( __CLASS__, 'update_account_balance' ) );
		add_action( 'eac_expense_updated', array( __CLASS__, 'update_account_balance' ) );
		add_action( 'eac_expense_deleted', array( __CLASS__, 'update_account_balance' ) );
	}

	/**
	 * Update account balance.
	 *
	 * @param Payment|Expense $payment The payment being edited or deleted.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function update_account_balance( $payment ) {
		// if the account id is changed then we have to update for the old account too.
		if ( $payment->get_original( 'account_id' ) && $payment->is_dirty( 'account_id' ) ) {
			$old_account = EAC()->accounts->get( $payment->get_original( 'account_id' ) );
			if ( $old_account ) {
				$old_account->update_balance();
			}
		}

		if ( $payment->account_id > 0 ) {
			$account = EAC()->accounts->get( $payment->account_id );
			if ( $account ) {
				$account->update_balance();
			}
		}
	}
}
