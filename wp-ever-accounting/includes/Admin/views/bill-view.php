<?php
/**
 * Admin View: Bill View
 *
 * @since 1.0.0
 * @package EverAccounting
 */

use EverAccounting\Models\Bill;

defined( 'ABSPATH' ) || exit;

wp_verify_nonce( '_wpnonce' );
$id                = isset( $_GET['id'] ) ? absint( wp_unslash( $_GET['id'] ) ) : 0;
$bill              = EAC()->bills->get( $id );
$mark_received_url = wp_nonce_url(
	add_query_arg(
		array(
			'eac_action'  => 'bill_action',
			'id'          => $bill->id,
			'bill_action' => 'mark_received',
		)
	),
	'eac_bill_action'
);
?>
<h1 class="wp-heading-inline">
	<?php esc_html_e( 'View Bill', 'wp-ever-accounting' ); ?>
	<a href="<?php echo esc_attr( admin_url( 'admin.php?page=eac-purchases&tab=bills&action=add' ) ); ?>" class="button button-small">
		<?php esc_html_e( 'Add New', 'wp-ever-accounting' ); ?>
	</a>
	<a href="<?php echo esc_attr( remove_query_arg( array( 'action', 'id' ) ) ); ?>" title="<?php esc_attr_e( 'Go back', 'wp-ever-accounting' ); ?>">
		<span class="dashicons dashicons-undo"></span>
	</a>
</h1>


<div class="eac-poststuff">

	<div class="column-1">
		<div class="eac-card"><?php eac_get_template( 'content-bill.php', array( 'bill' => $bill ) ); ?></div>
		<?php
		/**
		 * Fires action to inject custom content in the main column.
		 *
		 * @param Bill $bill Bill object.
		 *
		 * @since 1.0.0
		 */
		do_action( 'eac_bill_edit_core_content', $bill );
		?>
	</div>

	<div class="column-2">
		<div class="eac-card">
			<div class="eac-card__header">
				<h2 class="eac-card__title"><?php esc_html_e( 'Actions', 'wp-ever-accounting' ); ?></h2>
				<?php if ( $bill->editable ) : ?>
					<a href="<?php echo esc_url( $bill->get_edit_url() ); ?>">
						<?php esc_html_e( 'Edit', 'wp-ever-accounting' ); ?>
					</a>
				<?php endif; ?>
			</div>

			<div class="eac-card__body">
				<?php if ( $bill->is_status( 'draft' ) ) : ?>
					<a href="<?php echo esc_url( $mark_received_url ); ?>" class="button button-primary button-small button-block">
						<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Mark Received', 'wp-ever-accounting' ); ?>
					</a>
				<?php elseif ( ! $bill->is_status( 'draft' ) && ! $bill->is_paid() ) : ?>
					<a href="#" class="button button-primary button-small button-block eac-add-bill-payment" data-id="<?php echo esc_attr( $bill->id ); ?>" data-due="<?php echo esc_attr( $bill->get_due_amount() ); ?>" data-currency="<?php echo esc_attr( $bill->currency ); ?>">
						<span class="dashicons dashicons-money-alt"></span> <?php esc_html_e( 'Add Payment', 'wp-ever-accounting' ); ?>
					</a>
				<?php endif; ?>
				<a href="#" class="button button-small button-block eac_print_document" data-target=".eac-document">
					<span class="dashicons dashicons-printer"></span> <?php esc_html_e( 'Print', 'wp-ever-accounting' ); ?>
				</a>
				<a href="#" class="button button-small button-block eac_share_document" data-url="<?php echo esc_url( $bill->get_public_url() ); ?>">
					<span class="dashicons dashicons-share"></span> <?php esc_html_e( 'Share', 'wp-ever-accounting' ); ?>
				</a>

				<?php
				/**
				 * Fires to add custom actions.
				 *
				 * @param Bill $bill Bill object.
				 *
				 * @since 2.0.0
				 */
				do_action( 'eac_bill_view_misc_actions', $bill );
				?>
			</div>

			<div class="eac-card__footer">
				<a class="del del_confirm" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'action', 'delete', $bill->get_edit_url() ), 'bulk-bills' ) ); ?>"><?php esc_html_e( 'Delete', 'wp-ever-accounting' ); ?></a>
			</div>
		</div>

		<div class="eac-card">
			<div class="eac-card__header">
				<h3 class="eac-card__title"><?php esc_html_e( 'Attachment', 'wp-ever-accounting' ); ?></h3>
			</div>
			<div class="eac-card__body">
				<?php
				eac_file_uploader(
					array(
						'value'    => $bill->attachment_id,
						'readonly' => true,
					)
				);
				?>
			</div>
		</div>

		<?php
		/**
		 * Fires action to inject custom content in the side column.
		 *
		 * @param Bill $bill Bill object.
		 *
		 * @since 1.0.0
		 */
		do_action( 'eac_bill_view_sidebar_content', $bill );
		?>

	</div><!-- .column-2 -->

</div><!-- .eac-poststuff -->

<script type="text/html" id="tmpl-eac-bill-payment">
	<form>
		<div class="eac-modal-header">
			<h3><?php esc_html_e( 'Add Bill Payment', 'wp-ever-accounting' ); ?></h3>
		</div>

		<div class="eac-modal-body">
			<div class="eac-form-field">
				<label for="payment_date"><?php esc_html_e( 'Payment Date', 'wp-ever-accounting' ); ?>&nbsp;<abbr title="required"></abbr></label>
				<input type="text" name="payment_date" id="payment_date" value="<?php echo esc_attr( date_i18n( get_option( 'date_format' ) ) ); ?>" class="eac_datepicker" required>
			</div>
			<div class="eac-form-field">
				<label for="account_id"><?php esc_html_e( 'Account', 'wp-ever-accounting' ); ?>&nbsp;<abbr title="required"></abbr></label>
				<select name="account_id" id="account_id" class="eac_select2 account_id" data-action="eac_json_search" data-type="account" data-placeholder="<?php esc_html_e( 'Select an account', 'wp-ever-accounting' ); ?>" required>
					<option value=""><?php esc_html_e( 'Select an account', 'wp-ever-accounting' ); ?></option>
				</select>
			</div>
			<div class="eac-form-field">
				<label for="exchange_rate"><?php esc_html_e( 'Exchange Rate', 'wp-ever-accounting' ); ?>&nbsp;<abbr title="required"></abbr></label>
				<input type="text" name="exchange_rate" id="exchange_rate" value="1.00" class="eac_exchange_rate" data-currency="<?php echo esc_attr( $bill->currency ); ?>" required>
			</div>

			<div class="eac-form-field">
				<label for="amount"><?php esc_html_e( 'Amount', 'wp-ever-accounting' ); ?>&nbsp;<abbr title="required"></abbr></label>
				<input type="text" name="amount" id="amount" class="eac_amount" value="<?php echo esc_attr( $bill->get_due_amount() ); ?>" data-currncy="<?php echo esc_attr( $bill->currency ); ?>" required>
			</div>

			<div class="eac-form-field">
				<label for="payment_method"><?php esc_html_e( 'Payment Method', 'wp-ever-accounting' ); ?></label>
				<select name="payment_method" id="payment_method">
					<option value=""><?php esc_html_e( 'Select Payment Method', 'wp-ever-accounting' ); ?></option>
					<?php foreach ( eac_get_payment_methods() as $key => $value ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="eac-form-field">
				<label for="reference"><?php esc_html_e( 'Reference', 'wp-ever-accounting' ); ?></label>
				<input type="text" name="reference" id="reference">
			</div>

			<div class="eac-form-field">
				<label for="note"><?php esc_html_e( 'Description', 'wp-ever-accounting' ); ?></label>
				<textarea name="note" id="note" rows="3"></textarea>
			</div>
		</div>

		<div class="eac-modal-footer">
			<button type="submit" class="button button-primary"><?php esc_html_e( 'Add Payment', 'wp-ever-accounting' ); ?></button>
			<button type="button" class="button" data-modal-close><?php esc_html_e( 'Cancel', 'wp-ever-accounting' ); ?></button>
		</div>

		<input type="hidden" name="bill_id" value="<?php echo esc_attr( $bill->id ); ?>">
		<input type="hidden" name="vendor_id" value="<?php echo esc_attr( $bill->contact_id ); ?>">
	</form>
</script>
