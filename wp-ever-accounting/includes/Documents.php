<?php

namespace EverAccounting;

use EverAccounting\Models\Expense;
use EverAccounting\Models\Invoice;
use EverAccounting\Models\Payment;

defined( 'ABSPATH' ) || exit;

/**
 * Class Documents.
 *
 * @since   1.0.0
 * @package EverAccounting\Controllers
 */
class Documents {

	/**
	 * Documents constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Invoices.
		add_action( 'eac_payment_inserted', array( __CLASS__, 'invoice_payment_updated' ) );
		add_action( 'eac_payment_deleted', array( __CLASS__, 'invoice_payment_updated' ) );
		add_action( 'eac_payment_updated', array( __CLASS__, 'invoice_payment_updated' ) );
		add_action( 'eac_hourly_event', array( __CLASS__, 'maybe_overdue_invoices' ) );
		add_action( 'eac_invoice_status_transition', array( __CLASS__, 'invoice_status_transition' ), 10, 2 );

		// Bills.
		add_action( 'eac_expense_inserted', array( __CLASS__, 'bill_expense_updated' ) );
		add_action( 'eac_expense_deleted', array( __CLASS__, 'bill_expense_updated' ) );
		add_action( 'eac_expense_updated', array( __CLASS__, 'bill_expense_updated' ) );
		add_action( 'eac_hourly_event', array( __CLASS__, 'maybe_overdue_bills' ) );
		add_action( 'eac_bill_status_transition', array( __CLASS__, 'bill_status_transition' ), 10, 2 );
	}

	/**
	 * Payment updated so we need to update the invoice status.
	 *
	 * @param Payment $payment The payment being edited or deleted.
	 *
	 * @return void
	 */
	public static function invoice_payment_updated( $payment ) {
		$original = $payment->get_original();
		if ( array_key_exists( 'document_id', $original ) && $original['document_id'] !== $payment->document_id && $original['document_id'] > 0 ) {
			$old_document = EAC()->invoices->get( $original['document_id'] );
			if ( $old_document ) {
				$old_document->calculate_totals();
				$old_document->save();
			}
		}

		if ( $payment->invoice_id && $payment->invoice ) {
			$invoice = $payment->invoice;
			$invoice->calculate_totals();
			$invoice->save();
		}
	}

	/**
	 * Check for overdue invoices.
	 *
	 * @return void
	 */
	public static function maybe_overdue_invoices() {
		global $wpdb;
		$invoices = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT id FROM {$wpdb->prefix}ea_documents WHERE status NOT IN ('paid', 'cancelled', 'draft', 'overdue') AND due_date < %s",
				current_time( 'mysql' )
			)
		);

		if ( ! empty( $invoices ) ) {
			foreach ( $invoices as $invoice_id ) {
				$invoice = EAC()->invoices->get( $invoice_id );
				if ( $invoice ) {
					$invoice->status = 'overdue';
					$invoice->save();
				}
			}
		}
	}

	/**
	 * Bill status transition.
	 *
	 * @param Invoice $invoice Invoice object.
	 * @param string  $status New status.
	 */
	public static function invoice_status_transition( $invoice, $status ) {
		$invoice->notes()->insert(
			array(
				'parent_type' => 'invoice',
				// translators: %s: status.
				'content'     => sprintf( __( 'Status changed to %s', 'wp-ever-accounting' ), esc_html( $status ) ),
				'created_by'  => get_current_user_id(),
			)
		);
	}

	/**
	 * Expense updated so we need to update the bill status.
	 *
	 * @param Expense $expense The expense being edited or deleted.
	 *
	 * @return void
	 */
	public static function bill_expense_updated( $expense ) {
		$original = $expense->get_original();
		if ( array_key_exists( 'document_id', $original ) && $original['document_id'] !== $expense->document_id && $original['document_id'] > 0 ) {
			$old_document = EAC()->bills->get( $original['document_id'] );
			if ( $old_document ) {
				$old_document->calculate_totals();
				$old_document->save();
			}
		}

		if ( $expense->bill_id && $expense->bill ) {
			$bill = $expense->bill;
			$bill->calculate_totals();
			$bill->save();
		}
	}

	/**
	 * Check for overdue bills.
	 *
	 * @return void
	 */
	public static function maybe_overdue_bills() {
		global $wpdb;
		$bills = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT id FROM {$wpdb->prefix}ea_documents WHERE status NOT IN ('paid', 'cancelled', 'draft', 'overdue') AND due_date < %s",
				current_time( 'mysql' )
			)
		);

		if ( ! empty( $bills ) ) {
			foreach ( $bills as $bill_id ) {
				$bill = EAC()->bills->get( $bill_id );
				if ( $bill ) {
					$bill->status = 'overdue';
					$bill->save();
				}
			}
		}
	}

	/**
	 * Bill status transition.
	 *
	 * @param Invoice $bill Bill object.
	 * @param string  $status New status.
	 */
	public static function bill_status_transition( $bill, $status ) {
		$bill->notes()->insert(
			array(
				'parent_type' => 'bill',
				// translators: %s: status.
				'content'     => sprintf( __( 'Status changed to %s', 'wp-ever-accounting' ), esc_html( $status ) ),
				'created_by'  => get_current_user_id(),
			)
		);
	}
}
