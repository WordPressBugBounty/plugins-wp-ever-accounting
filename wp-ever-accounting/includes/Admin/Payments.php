<?php

namespace EverAccounting\Admin;

use EverAccounting\Models\Payment;

defined( 'ABSPATH' ) || exit;

/**
 * Class Payments
 *
 * @package EverAccounting\Admin\Sales
 */
class Payments {
	/**
	 * Payments constructor.
	 */
	public function __construct() {
		add_filter( 'eac_sales_page_tabs', array( __CLASS__, 'register_tabs' ) );
		add_action( 'admin_post_eac_edit_payment', array( __CLASS__, 'handle_edit' ) );
		add_action( 'admin_post_eac_update_payment', array( __CLASS__, 'handle_update' ) );
		add_action( 'eac_sales_page_payments_loaded', array( __CLASS__, 'page_loaded' ) );
		add_action( 'eac_sales_page_payments_content', array( __CLASS__, 'page_content' ) );
		add_action( 'eac_payment_view_sidebar_content', array( __CLASS__, 'payment_attachment' ) );
		add_action( 'eac_payment_view_sidebar_content', array( __CLASS__, 'payment_notes' ) );
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
		if ( current_user_can( 'eac_manage_payment' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
			$tabs['payments'] = __( 'Payments', 'wp-ever-accounting' );
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
		check_admin_referer( 'eac_edit_payment' );
		if ( ! current_user_can( 'eac_manage_payment' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
			wp_die( esc_html__( 'You do not have permission to edit payments.', 'wp-ever-accounting' ) );
		}

		$referer = wp_get_referer();
		$data    = array(
			'id'             => isset( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : 0,
			'payment_date'   => isset( $_POST['payment_date'] ) ? sanitize_text_field( wp_unslash( $_POST['payment_date'] ) ) : '',
			'account_id'     => isset( $_POST['account_id'] ) ? absint( wp_unslash( $_POST['account_id'] ) ) : 0,
			'amount'         => isset( $_POST['amount'] ) ? floatval( wp_unslash( $_POST['amount'] ) ) : 0,
			'exchange_rate'  => isset( $_POST['exchange_rate'] ) ? floatval( wp_unslash( $_POST['exchange_rate'] ) ) : 1,
			'category_id'    => isset( $_POST['category_id'] ) ? absint( wp_unslash( $_POST['category_id'] ) ) : 0,
			'contact_id'     => isset( $_POST['contact_id'] ) ? absint( wp_unslash( $_POST['contact_id'] ) ) : 0,
			'attachment_id'  => isset( $_POST['attachment_id'] ) ? absint( wp_unslash( $_POST['attachment_id'] ) ) : 0,
			'payment_method' => isset( $_POST['payment_method'] ) ? sanitize_text_field( wp_unslash( $_POST['payment_method'] ) ) : '',
			'reference'      => isset( $_POST['reference'] ) ? sanitize_text_field( wp_unslash( $_POST['reference'] ) ) : '',
			'note'           => isset( $_POST['note'] ) ? sanitize_textarea_field( wp_unslash( $_POST['note'] ) ) : '',
			'status'         => isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : 'active',
		);

		$payment = EAC()->payments->insert( $data );
		if ( is_wp_error( $payment ) ) {
			EAC()->flash->error( $payment->get_error_message() );
		} else {
			EAC()->flash->success( __( 'Payment saved successfully.', 'wp-ever-accounting' ) );
			$referer = add_query_arg( 'id', $payment->id, $referer );
			$referer = add_query_arg( 'action', 'view', $referer );
			$referer = remove_query_arg( array( 'add' ), $referer );
		}

		wp_safe_redirect( $referer );
		exit;
	}

	/**
	 * Handle update.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function handle_update() {
		check_admin_referer( 'eac_update_payment' );
		if ( ! current_user_can( 'eac_manage_payment' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
			wp_die( esc_html__( 'You do not have permission to update payments.', 'wp-ever-accounting' ) );
		}

		$referer        = wp_get_referer();
		$id             = isset( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : 0;
		$status         = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : '';
		$attachment_id  = isset( $_POST['attachment_id'] ) ? absint( wp_unslash( $_POST['attachment_id'] ) ) : 0;
		$payment_action = isset( $_POST['payment_action'] ) ? sanitize_text_field( wp_unslash( $_POST['payment_action'] ) ) : '';
		$payment        = EAC()->payments->get( $id );

		// bail if payment is not found.
		if ( ! $payment ) {
			EAC()->flash->error( __( 'Payment not found.', 'wp-ever-accounting' ) );

			return;
		}

		// Update payment status.
		if ( ! empty( $status ) && $status !== $payment->status ) {
			$payment->status = $status;
		}

		// Update payment attachment.
		if ( $attachment_id !== $payment->attachment_id ) {
			$payment->attachment_id = $attachment_id;
		}

		if ( $payment->is_dirty() && $payment->save() ) {
			$ret = $payment->save();
			if ( is_wp_error( $ret ) ) {
				EAC()->flash->error( $ret->get_error_message() );
			} else {
				EAC()->flash->success( __( 'Payment updated successfully.', 'wp-ever-accounting' ) );
			}
		}

		// todo handle payment action.
		if ( ! empty( $payment_action ) ) {
			switch ( $payment_action ) {
				case 'send_receipt':
					// Send payment.
					break;
				default:
					/**
					 * Fires action to handle custom payment actions.
					 *
					 * @param Payment $payment Payment object.
					 *
					 * @since 1.0.0
					 */
					do_action( 'eac_payment_action_' . $payment_action, $payment );
					break;
			}
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

			case 'view':
			case 'edit':
				$id = filter_input( INPUT_GET, 'id', FILTER_VALIDATE_INT );
				if ( ! EAC()->payments->get( $id ) ) {
					wp_die( esc_html__( 'You attempted to retrieve a payment that does not exist. Perhaps it was deleted?', 'wp-ever-accounting' ) );
				}
				if ( 'edit' === $action && ! EAC()->payments->get( $id )->editable ) {
					wp_die( esc_html__( 'You attempted to edit a payment that is not editable.', 'wp-ever-accounting' ) );
				}
				break;

			default:
				$screen     = get_current_screen();
				$list_table = new ListTables\Payments();
				$list_table->prepare_items();
				$screen->add_option(
					'per_page',
					array(
						'label'   => __( 'Number of items per page:', 'wp-ever-accounting' ),
						'default' => 20,
						'option'  => 'eac_payments_per_page',
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
				include __DIR__ . '/views/payment-edit.php';
				break;
			case 'view':
				include __DIR__ . '/views/payment-view.php';
				break;
			default:
				include __DIR__ . '/views/payment-list.php';
				break;
		}
	}

	/**
	 * Payment attachment.
	 *
	 * @param Payment $payment Payment object.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function payment_attachment( $payment ) {
		?>
		<div class="eac-card">
			<div class="eac-card__header">
				<h3 class="eac-card__title"><?php esc_html_e( 'Attachment', 'wp-ever-accounting' ); ?></h3>
			</div>
			<div class="eac-card__body">
				<?php
				eac_file_uploader(
					array(
						'value'    => $payment->attachment_id,
						'readonly' => true,
					)
				);
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Payment notes.
	 *
	 * @param Payment $payment Payment object.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function payment_notes( $payment ) {
		// bail if payment is not found.
		if ( ! $payment->exists() ) {
			return;
		}
		$notes = EAC()->notes->query(
			array(
				'parent_id'   => $payment->id,
				'parent_type' => 'payment',
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
				<button id="eac-add-note" type="button" class="button tw-mb-[20px]" data-parent_id="<?php echo esc_attr( $payment->id ); ?>" data-parent_type="payment" data-nonce="<?php echo esc_attr( wp_create_nonce( 'eac_add_note' ) ); ?>">
					<?php esc_html_e( 'Add Note', 'wp-ever-accounting' ); ?>
				</button>

				<?php include __DIR__ . '/views/note-list.php'; ?>
			</div>
		</div>
		<?php
	}
}
