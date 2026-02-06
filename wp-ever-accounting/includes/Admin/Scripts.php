<?php

namespace EverAccounting\Admin;

use EverAccounting\Models\Bill;

defined( 'ABSPATH' ) || exit;

/**
 * Class Scripts
 *
 * @package EverAccounting\Admin
 */
class Scripts {

	/**
	 * Scripts constructor.
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'register_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Register admin scripts.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_scripts() {
		// 3rd party scripts.
		EAC()->scripts->register_script( 'eac-chartjs', 'scripts/chartjs.js', array( 'jquery' ), true );
		EAC()->scripts->register_script( 'eac-inputmask', 'scripts/inputmask.js', array( 'jquery' ), true );
		EAC()->scripts->register_script( 'eac-select2', 'scripts/select2.js', array( 'jquery' ), true );
		EAC()->scripts->register_script( 'eac-tiptip', 'scripts/tiptip.js', array( 'jquery' ), true );
		EAC()->scripts->register_script( 'eac-printthis', 'scripts/printthis.js', array( 'jquery' ), true );
		EAC()->scripts->register_script( 'eac-timepicker', 'scripts/timepicker.js', array( 'jquery', 'jquery-ui-datepicker' ), true );

		// Packages.
		EAC()->scripts->register_script( 'eac-money', 'packages/money.js' );
		EAC()->scripts->register_script( 'eac-autonumeric', 'scripts/autonumeric.js', array(), true );

		// Plugins.
		EAC()->scripts->register_script( 'eac-modal', 'scripts/modal.js', array( 'jquery' ), true );
		EAC()->scripts->register_script( 'eac-form', 'scripts/form.js', array( 'jquery' ), true );
		EAC()->scripts->register_script( 'eac-api', 'scripts/api.js', array( 'wp-backbone' ), true );

		// Plugin scripts.
		EAC()->scripts->register_script( 'eac-admin', 'scripts/admin.js', array( 'jquery', 'eac-inputmask', 'eac-select2', 'eac-printthis', 'eac-tiptip', 'eac-timepicker', 'jquery-ui-tooltip', 'eac-money', 'wp-ajax-response' ), true );

		EAC()->scripts->register_style( 'eac-jquery-ui', 'styles/jquery-ui.css' );
		EAC()->scripts->register_style( 'eac-admin', 'styles/admin.css', array( 'eac-jquery-ui' ) );
	}

	/**
	 * Enqueue admin scripts.
	 *
	 * @param string $hook The current admin page.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function enqueue_scripts( $hook ) {
		// TODO: Remove black friday notice after when the promo is over.
		EAC()->scripts->enqueue_style( 'eac-black-friday', 'styles/admin-black-friday.css' );

		if ( ! in_array( $hook, Utilities::get_screen_ids(), true ) ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_script( 'eac-api' );
		wp_enqueue_script( 'eac-form' );
		wp_enqueue_script( 'eac-modal' );
		wp_enqueue_script( 'eac-admin' );
		wp_enqueue_style( 'eac-admin' );

		// Localize script.
		wp_localize_script(
			'eac-api',
			'eac_api_vars',
			array(
				'root'      => sanitize_url( get_rest_url() ),
				'nonce'     => wp_create_nonce( 'wp_rest' ),
				'namespace' => 'eac/v1/',
			)
		);

		wp_localize_script(
			'eac-admin',
			'eac_admin_vars',
			array(
				'ajax_url'      => admin_url( 'admin-ajax.php' ),
				'base_currency' => eac_base_currency(),
				'currencies'    => eac_get_currencies(),
				'search_nonce'  => wp_create_nonce( 'eac_search_action' ),
				'upload_nonce'  => wp_create_nonce( 'eac_upload_action' ),
				'i18n'          => array(
					'confirm_delete' => __( 'Are you sure you want to delete this?', 'wp-ever-accounting' ),
					'close'          => __( 'Close', 'wp-ever-accounting' ),
					'share_prompt'   => __( 'Link is copied to clipboard. Want to open it in a new tab?', 'wp-ever-accounting' ),
				),
			)
		);

		if ( 'toplevel_page_ever-accounting' === $hook || 'ever-accounting_page_eac-reports' === $hook || 'ever-accounting_page_eac-banking' === $hook ) {
			wp_enqueue_script( 'eac-chartjs' );
		}
	}
}
