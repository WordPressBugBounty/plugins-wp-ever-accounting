<?php

namespace EverAccounting\Admin;

use EverAccounting\Models\Bill;
use EverAccounting\Models\DocumentItem;
use EverAccounting\Models\DocumentTax;

defined( 'ABSPATH' ) || exit;

/**
 * Class Bills
 *
 * @package EverAccounting\Admin\Sales
 */
class Bills {

	/**
	 * Bills constructor.
	 */
	public function __construct() {
		add_filter( 'eac_purchases_page_tabs', array( __CLASS__, 'register_tabs' ) );
		add_action( 'admin_post_eac_edit_bill', array( __CLASS__, 'handle_edit' ) );
		add_action( 'eac_action_bill_action', array( __CLASS__, 'handle_action' ) );
		add_action( 'eac_purchases_page_bills_loaded', array( __CLASS__, 'page_loaded' ) );
		add_action( 'eac_purchases_page_bills_content', array( __CLASS__, 'page_content' ) );
		add_action( 'eac_bill_view_sidebar_content', array( __CLASS__, 'bill_notes' ) );
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
		if ( current_user_can( 'eac_manage_bill' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
			$tabs['bills'] = __( 'Bills', 'wp-ever-accounting' );
		}

		return $tabs;
	}

	/**
	 * Handle edit.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function handle_edit() {
		check_admin_referer( 'eac_edit_bill' );

		if ( ! current_user_can( 'eac_manage_bill' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
			wp_die( esc_html__( 'You do not have permission to edit bills.', 'wp-ever-accounting' ) );
		}

		$referer                  = wp_get_referer();
		$id                       = isset( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : 0;
		$items                    = isset( $_POST['items'] ) ? map_deep( wp_unslash( $_POST['items'] ), 'sanitize_text_field' ) : array();
		$bill                     = Bill::make( $id );
		$bill->contact_id         = isset( $_POST['contact_id'] ) ? absint( wp_unslash( $_POST['contact_id'] ) ) : 0;
		$bill->contact_name       = isset( $_POST['contact_name'] ) ? sanitize_text_field( wp_unslash( $_POST['contact_name'] ) ) : '';
		$bill->contact_email      = isset( $_POST['contact_email'] ) ? sanitize_text_field( wp_unslash( $_POST['contact_email'] ) ) : '';
		$bill->contact_phone      = isset( $_POST['contact_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['contact_phone'] ) ) : '';
		$bill->contact_address    = isset( $_POST['contact_address'] ) ? sanitize_text_field( wp_unslash( $_POST['contact_address'] ) ) : '';
		$bill->contact_city       = isset( $_POST['contact_city'] ) ? sanitize_text_field( wp_unslash( $_POST['contact_city'] ) ) : '';
		$bill->contact_state      = isset( $_POST['contact_state'] ) ? sanitize_text_field( wp_unslash( $_POST['contact_state'] ) ) : '';
		$bill->contact_postcode   = isset( $_POST['contact_postcode'] ) ? sanitize_text_field( wp_unslash( $_POST['contact_postcode'] ) ) : '';
		$bill->contact_country    = isset( $_POST['contact_country'] ) ? sanitize_text_field( wp_unslash( $_POST['contact_country'] ) ) : '';
		$bill->contact_tax_number = isset( $_POST['contact_tax_number'] ) ? sanitize_text_field( wp_unslash( $_POST['contact_tax_number'] ) ) : '';
		$bill->order_number       = isset( $_POST['order_number'] ) ? sanitize_text_field( wp_unslash( $_POST['order_number'] ) ) : '';
		$bill->attachment_id      = isset( $_POST['attachment_id'] ) ? absint( wp_unslash( $_POST['attachment_id'] ) ) : 0;
		$bill->currency           = isset( $_POST['currency'] ) ? sanitize_text_field( wp_unslash( $_POST['currency'] ) ) : eac_base_currency();
		$bill->exchange_rate      = isset( $_POST['exchange_rate'] ) ? floatval( wp_unslash( $_POST['exchange_rate'] ) ) : 1;
		$bill->discount_type      = isset( $_POST['discount_type'] ) ? sanitize_text_field( wp_unslash( $_POST['discount_type'] ) ) : 'fixed';
		$bill->discount_value     = isset( $_POST['discount_value'] ) ? floatval( wp_unslash( $_POST['discount_value'] ) ) : 0;
		$bill->status             = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : 'draft';
		$bill->note               = isset( $_POST['note'] ) ? sanitize_textarea_field( wp_unslash( $_POST['note'] ) ) : '';
		$bill->terms              = isset( $_POST['terms'] ) ? sanitize_textarea_field( wp_unslash( $_POST['terms'] ) ) : '';
		$bill->items()->delete();
		$bill->items = array();
		$bill->set_items( $items );
		$bill->calculate_totals();
		$retval = $bill->save();
		if ( is_wp_error( $retval ) ) {
			EAC()->flash->error( $retval->get_error_message() );
		}

		// save bill items and taxes.
		foreach ( $bill->items as $item ) {
			$item->document_id = $bill->id;
			$item->save();
			$taxes = $item->taxes;
			foreach ( $taxes as $tax ) {
				$tax->document_id      = $bill->id;
				$tax->document_item_id = $item->id;
				$tax->save();
			}
		}

		EAC()->flash->success( __( 'Bill saved successfully.', 'wp-ever-accounting' ) );
		$referer = add_query_arg( 'id', $bill->id, $referer );
		$referer = add_query_arg( 'action', 'view', $referer );
		$referer = remove_query_arg( array( 'add' ), $referer );
		wp_safe_redirect( $referer );
		exit;
	}

	/**
	 * Handle action.
	 *
	 * @param array $posted Posted data.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function handle_action( $posted ) {
		check_admin_referer( 'eac_bill_action' );
		if ( ! current_user_can( 'eac_manage_bill' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'wp-ever-accounting' ) );
		}

		$id      = isset( $posted['id'] ) ? absint( wp_unslash( $posted['id'] ) ) : 0;
		$action  = isset( $posted['bill_action'] ) ? sanitize_text_field( wp_unslash( $posted['bill_action'] ) ) : '';
		$referer = wp_get_referer();
		// if any of the required fields are missing, bail.
		if ( ! $id || ! $action ) {
			wp_die( esc_html__( 'Invalid request.', 'wp-ever-accounting' ) );
		}

		$bill = EAC()->bills->get( $id );
		if ( ! $bill ) {
			wp_die( esc_html__( 'You attempted to perform an action on an bill that does not exist.', 'wp-ever-accounting' ) );
		}

		switch ( $action ) {
			case 'mark_received':
				$bill->status = 'sent';
				if ( $bill->save() ) {
					EAC()->flash->success( __( 'Invoice marked as received.', 'wp-ever-accounting' ) );
				} else {
					EAC()->flash->error( __( 'Failed to mark bill as received.', 'wp-ever-accounting' ) );
				}
				break;
			default:
				do_action( 'eac_bill_action_' . $action, $bill );
		}

		$referer = remove_query_arg( array( 'eac_action' ), $referer );
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

			case 'view':
			case 'edit':
				$id = filter_input( INPUT_GET, 'id', FILTER_VALIDATE_INT );
				if ( ! EAC()->bills->get( $id ) ) {
					wp_die( esc_html__( 'You attempted to retrieve a bill that does not exist. Perhaps it was deleted?', 'wp-ever-accounting' ) );
				}
				if ( 'edit' === $action && ! EAC()->bills->get( $id )->editable ) {
					wp_die( esc_html__( 'You attempted to edit a bill that is not editable.', 'wp-ever-accounting' ) );
				}
				break;

			default:
				$screen     = get_current_screen();
				$list_table = new ListTables\Bills();
				$list_table->prepare_items();
				$screen->add_option(
					'per_page',
					array(
						'label'   => __( 'Number of items per page:', 'wp-ever-accounting' ),
						'default' => 20,
						'option'  => 'eac_bills_per_page',
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
				include __DIR__ . '/views/bill-edit.php';
				break;
			case 'view':
				include __DIR__ . '/views/bill-view.php';
				break;
			default:
				include __DIR__ . '/views/bill-list.php';
				break;
		}
	}

	/**
	 * Bill notes.
	 *
	 * @param Bill $bill Bill object.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function bill_notes( $bill ) {
		// bail if bill not exist.
		if ( ! $bill->exists() ) {
			return;
		}

		$notes = EAC()->notes->query(
			array(
				'parent_id'   => $bill->id,
				'parent_type' => 'bill',
				'orderby'     => 'date_created',
				'order'       => 'DESC',
				'limit'       => 20,
			)
		);
		?>
		<div class="eac-card">
			<div class="eac-card__header">
				<h3 class="eac-card__title"><?php esc_html_e( 'Notes', 'wp-ever-accounting' ); ?></h3>
			</div>
			<div class="eac-card__body">
				<div class="eac-form-field">
					<label for="eac-note"><?php esc_html_e( 'Add Note', 'wp-ever-accounting' ); ?></label>
					<textarea id="eac-note" cols="30" rows="2" placeholder="<?php esc_attr_e( 'Enter Note', 'wp-ever-accounting' ); ?>"></textarea>
				</div>
				<button id="eac-add-note" type="button" class="button tw-mb-[20px]" data-parent_id="<?php echo esc_attr( $bill->id ); ?>" data-parent_type="bill" data-nonce="<?php echo esc_attr( wp_create_nonce( 'eac_add_note' ) ); ?>">
					<?php esc_html_e( 'Add Note', 'wp-ever-accounting' ); ?>
				</button>

				<?php include __DIR__ . '/views/note-list.php'; ?>
			</div>
		</div>
		<?php
	}
}
