<?php

namespace EverAccounting\Admin;

use EverAccounting\Models\Tax;

defined( 'ABSPATH' ) || exit;

/**
 * Taxes class.
 *
 * @since 3.0.0
 * @package EverAccounting\Admin
 */
class Taxes {

	/**
	 * TaxRates constructor.
	 */
	public function __construct() {
		add_action( 'admin_post_eac_edit_tax', array( __CLASS__, 'handle_edit' ) );
		add_action( 'eac_settings_taxes_tab_rates_content', array( __CLASS__, 'render_content' ) );
	}

	/**
	 * Edit tax.
	 *
	 * @since 1.2.0
	 * @return void
	 */
	public static function handle_edit() {
		check_admin_referer( 'eac_edit_tax' );
		$referer  = wp_get_referer();
		$id       = isset( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : 0;
		$name     = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$rate     = isset( $_POST['rate'] ) ? doubleval( wp_unslash( $_POST['rate'] ) ) : '';
		$compound = isset( $_POST['compound'] ) ? sanitize_text_field( wp_unslash( $_POST['compound'] ) ) : '';
		if ( $compound ) {
			$compound = 'yes' === $compound ? true : false;
		}
		$tax = EAC()->taxes->insert(
			array(
				'id'       => $id,
				'name'     => $name,
				'rate'     => $rate,
				'compound' => $compound,
			)
		);

		if ( is_wp_error( $tax ) ) {
			EAC()->flash->error( $tax->get_error_message() );
		} else {
			EAC()->flash->success( __( 'Tax saved successfully.', 'wp-ever-accounting' ) );
			$referer = add_query_arg(
				array(
					'action' => 'edit',
					'id'     => $tax->id,
				),
				$referer
			);
			$referer = remove_query_arg( array( 'add' ), $referer );
		}

		wp_safe_redirect( $referer );
		exit;
	}

	/**
	 * Render content.
	 *
	 * @since 3.0.0
	 * @return void
	 */
	public static function render_content() {
		$action = filter_input( INPUT_GET, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$id     = filter_input( INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT );

		switch ( $action ) {
			case 'add':
			case 'edit':
				include __DIR__ . '/views/tax-edit.php';
				break;
			default:
				global $list_table;
				$list_table = new ListTables\Taxes();
				$list_table->prepare_items();
				include __DIR__ . '/views/tax-list.php';
				break;
		}
	}
}
