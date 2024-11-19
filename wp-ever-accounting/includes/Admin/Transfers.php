<?php

namespace EverAccounting\Admin;

use EverAccounting\Models\Transfer;

defined( 'ABSPATH' ) || exit;

/**
 * Class Transfers
 *
 * @since 2.0.0
 * @package EverAccounting
 */
class Transfers {

	/**
	 * Transfers constructor.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		add_filter( 'eac_banking_page_tabs', array( __CLASS__, 'register_tabs' ) );
		add_filter( 'admin_post_eac_edit_transfer', array( __CLASS__, 'handle_edit' ) );
		add_action( 'eac_banking_page_transfers_loaded', array( __CLASS__, 'page_loaded' ) );
		add_action( 'eac_banking_page_transfers_content', array( __CLASS__, 'page_content' ) );
	}

	/**
	 * Register tab.
	 *
	 * @param array $tabs Tabs.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public static function register_tabs( $tabs ) {
		if ( current_user_can( 'eac_manage_transfer' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
			$tabs['transfers'] = __( 'Transfers', 'wp-ever-accounting' );
		}

		return $tabs;
	}

	/**
	 * Handle edit.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public static function handle_edit() {
		check_admin_referer( 'eac_edit_transfer' );

		if ( ! current_user_can( 'eac_manage_transfer' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
			wp_die( esc_html__( 'You do not have permission to edit transfers.', 'wp-ever-accounting' ) );
		}

		$referer  = wp_get_referer();
		$transfer = EAC()->transfers->insert(
			array(
				'id'                 => isset( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : 0,
				'from_account_id'    => isset( $_POST['from_account_id'] ) ? absint( wp_unslash( $_POST['from_account_id'] ) ) : 0,
				'from_exchange_rate' => isset( $_POST['from_exchange_rate'] ) ? floatval( wp_unslash( $_POST['from_exchange_rate'] ) ) : 1,
				'to_account_id'      => isset( $_POST['to_account_id'] ) ? absint( wp_unslash( $_POST['to_account_id'] ) ) : 0,
				'to_exchange_rate'   => isset( $_POST['to_exchange_rate'] ) ? floatval( wp_unslash( $_POST['to_exchange_rate'] ) ) : 1,
				'amount'             => isset( $_POST['amount'] ) ? floatval( wp_unslash( $_POST['amount'] ) ) : 0,
				'transfer_date'      => isset( $_POST['transfer_date'] ) ? sanitize_text_field( wp_unslash( $_POST['transfer_date'] ) ) : '',
				'payment_method'     => isset( $_POST['payment_method'] ) ? sanitize_text_field( wp_unslash( $_POST['payment_method'] ) ) : '',
				'reference'          => isset( $_POST['reference'] ) ? sanitize_text_field( wp_unslash( $_POST['reference'] ) ) : '',
				'note'               => isset( $_POST['note'] ) ? sanitize_textarea_field( wp_unslash( $_POST['note'] ) ) : '',
			)
		);

		if ( is_wp_error( $transfer ) ) {
			EAC()->flash->error( $transfer->get_error_message() );
		} else {
			EAC()->flash->success( __( 'Transfer saved successfully.', 'wp-ever-accounting' ) );
			$referer = add_query_arg( 'id', $transfer->id, $referer );
			$referer = remove_query_arg( array( 'add' ), $referer );
		}

		wp_safe_redirect( $referer );
		exit;
	}

	/**
	 * Handle page loaded.
	 *
	 * @param string $action Current action.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public static function page_loaded( $action ) {
		global $list_table;
		switch ( $action ) {
			case 'add':
				// Nothing to do here.
				break;

			case 'edit':
				$id = filter_input( INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT );
				if ( ! EAC()->transfers->get( $id ) ) {
					wp_die( esc_html__( 'You attempted to retrieve a transfer that does not exist. Perhaps it was deleted?', 'wp-ever-accounting' ) );
				}
				break;

			default:
				$screen     = get_current_screen();
				$list_table = new ListTables\Transfers();
				$list_table->prepare_items();
				$screen->add_option(
					'per_page',
					array(
						'label'   => __( 'Number of items per page:', 'wp-ever-accounting' ),
						'default' => 20,
						'option'  => 'eac_transfers_per_page',
					)
				);
				break;
		}
	}

	/**
	 * Handle page content.
	 *
	 * @param string $action Current action.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public static function page_content( $action ) {
		switch ( $action ) {
			case 'add':
			case 'edit':
				include __DIR__ . '/views/transfer-edit.php';
				break;
			default:
				include __DIR__ . '/views/transfer-list.php';
				break;
		}
	}
}
