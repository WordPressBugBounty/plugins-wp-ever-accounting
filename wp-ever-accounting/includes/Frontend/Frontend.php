<?php

namespace EverAccounting\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * Class Frontend.
 *
 * @since 1.0.0
 * @package EverAccounting
 */
class Frontend extends \EverAccounting\B8\Component {

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register(): void {
		add_action( 'eac_page_header', array( __CLASS__, 'render_page_header' ) );
		add_action( 'eac_page_footer', array( __CLASS__, 'render_page_footer' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
		add_action( 'eac_handle_request_invoice', array( __CLASS__, 'render_invoice' ) );
		add_action( 'eac_handle_request_bill', array( __CLASS__, 'render_bill' ) );
		add_action( 'eac_handle_request_payment', array( __CLASS__, 'render_payment' ) );
		add_action( 'eac_handle_request_expense', array( __CLASS__, 'render_expense' ) );
	}

	/**
	 * Render page header.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function render_page_header() {
		wp_enqueue_style( 'eac-frontend' );
		EAC()->template->render(
			'site-header'
		);
	}

	/**
	 * Render page footer.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function render_page_footer() {
		EAC()->template->render(
			'site-footer',
		);
	}

	/**
	 * Enqueue scripts.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function enqueue_scripts() {
		EAC()->scripts->register_style( 'eac-frontend', 'styles/frontend.css' );
	}

	/**
	 * Render invoice.
	 *
	 * @param array $vars Query vars.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function render_invoice( $vars ) {
		$uuid    = isset( $vars['uuid'] ) ? sanitize_text_field( wp_unslash( $vars['uuid'] ) ) : '';
		$invoice = EAC()->invoices->get( array( 'uuid' => $uuid ) );
		if ( ! $invoice ) {
			wp_die( esc_html__( 'You attempted to view an invoice that does not exist.', 'wp-ever-accounting' ) );
		}

		EAC()->template->render(
			'single-invoice',
			array( 'invoice' => $invoice )
		);
	}

	/**
	 * Render bill.
	 *
	 * @param array $vars Query vars.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function render_bill( $vars ) {
		$uuid = isset( $vars['uuid'] ) ? sanitize_text_field( wp_unslash( $vars['uuid'] ) ) : '';
		$bill = EAC()->bills->get( array( 'uuid' => $uuid ) );
		if ( ! $bill ) {
			wp_die( esc_html__( 'You attempted to view a bill that does not exist.', 'wp-ever-accounting' ) );
		}

		EAC()->template->render(
			'single-bill',
			array( 'bill' => $bill )
		);
	}

	/**
	 * Render payment.
	 *
	 * @param array $vars Query vars.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function render_payment( $vars ) {
		$uuid    = isset( $vars['uuid'] ) ? sanitize_text_field( wp_unslash( $vars['uuid'] ) ) : '';
		$payment = EAC()->payments->get( array( 'uuid' => $uuid ) );
		if ( ! $payment ) {
			wp_die( esc_html__( 'You attempted to view a payment that does not exist.', 'wp-ever-accounting' ) );
		}

		EAC()->template->render(
			'single-payment',
			array( 'payment' => $payment )
		);
	}

	/**
	 * Render expense.
	 *
	 * @param array $vars Query vars.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function render_expense( $vars ) {
		$uuid    = isset( $vars['uuid'] ) ? sanitize_text_field( wp_unslash( $vars['uuid'] ) ) : '';
		$expense = EAC()->expenses->get( array( 'uuid' => $uuid ) );
		if ( ! $expense ) {
			wp_die( esc_html__( 'You attempted to view an expense that does not exist.', 'wp-ever-accounting' ) );
		}

		EAC()->template->render(
			'single-expense',
			array( 'expense' => $expense )
		);
	}
}
