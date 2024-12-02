<?php
/**
 * Admin View: Transfer edit
 *
 * @since 1.0.0
 * @package EverAccounting
 * @var $transfer Transfer
 */

use EverAccounting\Models\Transfer;

defined( 'ABSPATH' ) || exit;

$id       = filter_input( INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT );
$transfer = Transfer::make( $id );

?>
<h1 class="wp-heading-inline">
	<?php if ( $transfer->exists() ) : ?>
		<?php esc_html_e( 'Edit Transfer', 'wp-ever-accounting' ); ?>
		<a href="<?php echo esc_attr( admin_url( 'admin.php?page=eac-banking&tab=transfers&action=add' ) ); ?>" class="button button-small">
			<?php esc_html_e( 'Add New', 'wp-ever-accounting' ); ?>
		</a>
	<?php else : ?>
		<?php esc_html_e( 'Add Transfer', 'wp-ever-accounting' ); ?>
	<?php endif; ?>
	<a href="<?php echo esc_attr( remove_query_arg( array( 'action', 'id' ) ) ); ?>" title="<?php esc_attr_e( 'Go back', 'wp-ever-accounting' ); ?>">
		<span class="dashicons dashicons-undo"></span>
	</a>
</h1>


<form id="eac-edit-transfer" name="transfer" method="post" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>">
	<div class="eac-poststuff">
		<div class="column-1">

			<div class="eac-card">
				<div class="eac-card__header">
					<h2 class="eac-card__title"><?php esc_html_e( 'Transfer Attributes', 'wp-ever-accounting' ); ?></h2>
				</div>
				<div class="eac-card__body grid--fields">
					<?php
					eac_form_field(
						array(
							'type'             => 'select',
							'name'             => 'from_account_id',
							'label'            => __( 'From Account', 'wp-ever-accounting' ),
							'options'          => array( $transfer->expense ? $transfer->expense->account : null ),
							'value'            => $transfer->expense ? $transfer->expense->account_id : null,
							'class'            => 'eac_select2',
							'required'         => true,
							'tooltip'          => __( 'Select the account.', 'wp-ever-accounting' ),
							'data-placeholder' => __( 'Select an account', 'wp-ever-accounting' ),
							'data-action'      => 'eac_json_search',
							'data-type'        => 'account',
							'option_value'     => 'id',
							'option_label'     => 'formatted_name',
							'suffix'           => sprintf(
								'<a class="addon" href="%s" target="_blank" title="%s"><span class="dashicons dashicons-plus"></span></a>',
								esc_url( admin_url( 'admin.php?page=eac-banking&tab=accounts&action=add' ) ),
								__( 'Add Account', 'wp-ever-accounting' )
							),
						)
					);

					eac_form_field(
						array(
							'type'             => 'select',
							'name'             => 'to_account_id',
							'label'            => __( 'To Account', 'wp-ever-accounting' ),
							'options'          => array( $transfer->payment ? $transfer->payment->account : null ),
							'value'            => $transfer->payment ? $transfer->payment->account_id : null,
							'class'            => 'eac_select2',
							'required'         => true,
							'tooltip'          => __( 'Select the account.', 'wp-ever-accounting' ),
							'data-placeholder' => __( 'Select an account', 'wp-ever-accounting' ),
							'data-action'      => 'eac_json_search',
							'data-type'        => 'account',
							'option_value'     => 'id',
							'option_label'     => 'formatted_name',
							'suffix'           => sprintf(
								'<a class="addon" href="%s" target="_blank" title="%s"><span class="dashicons dashicons-plus"></span></a>',
								esc_url( admin_url( 'admin.php?page=eac-banking&tab=accounts&action=add' ) ),
								__( 'Add Account', 'wp-ever-accounting' )
							),
						)
					);

					eac_form_field(
						array(
							'name'          => 'from_exchange_rate',
							'label'         => __( 'From Exchange Rate', 'wp-ever-accounting' ),
							'value'         => $transfer->expense ? $transfer->expense->exchange_rate : '',
							'default'       => 1,
							'placeholder'   => '0.00',
							'required'      => true,
							'prefix'        => '1 ' . eac_base_currency() . ' = ',
							'class'         => 'eac_exchange_rate',
							'attr-step'     => 'any',
							'readonly'      => $transfer->expense && eac_base_currency() === $transfer->expense->currency,
							'data-currency' => $transfer->expense ? $transfer->expense->currency : eac_base_currency(),
							'data-source'   => ':input[name="from_account_id"]',
						)
					);

					eac_form_field(
						array(
							'type'          => 'text',
							'name'          => 'to_exchange_rate',
							'label'         => __( 'To Exchange Rate', 'wp-ever-accounting' ),
							'value'         => $transfer->payment ? $transfer->payment->exchange_rate : '',
							'default'       => 1,
							'placeholder'   => '0.00',
							'required'      => true,
							'prefix'        => '1 ' . eac_base_currency() . ' = ',
							'class'         => 'eac_exchange_rate',
							'attr-step'     => 'any',
							'readonly'      => $transfer->payment && eac_base_currency() === $transfer->payment->currency,
							'data-currency' => $transfer->payment ? $transfer->payment->currency : eac_base_currency(),
							'data-source'   => ':input[name="to_account_id"]',
						)
					);

					eac_form_field(
						array(
							'type'          => 'text',
							'name'          => 'amount',
							'label'         => __( 'Amount', 'wp-ever-accounting' ),
							'placeholder'   => '0.00',
							'value'         => $transfer->amount,
							'required'      => true,
							'data-currency' => $transfer->currency ? $transfer->currency : eac_base_currency(),
							'class'         => 'eac_amount',
							'data-source'   => ':input[name="from_account_id"]',
						)
					);

					eac_form_field(
						array(
							'data_type'   => 'date',
							'name'        => 'transfer_date',
							'label'       => __( 'Date', 'wp-ever-accounting' ),
							'placeholder' => 'YYYY-MM-DD',
							'value'       => $transfer->transfer_date,
							'default'     => wp_date( 'Y-m-d' ),
							'required'    => true,
							'class'       => 'eac_datepicker',
						)
					);

					eac_form_field(
						array(
							'type'        => 'select',
							'name'        => 'payment_method',
							'label'       => __( 'Payment Method', 'wp-ever-accounting' ),
							'value'       => $transfer->payment_method,
							'options'     => eac_get_payment_methods(),
							'placeholder' => __( 'Select payment method', 'wp-ever-accounting' ),
						)
					);

					eac_form_field(
						array(
							'type'        => 'text',
							'name'        => 'reference',
							'label'       => __( 'Reference', 'wp-ever-accounting' ),
							'value'       => $transfer->reference,
							'placeholder' => __( 'Enter reference', 'wp-ever-accounting' ),
						)
					);

					eac_form_field(
						array(
							'type'          => 'textarea',
							'name'          => 'note',
							'label'         => __( 'Notes', 'wp-ever-accounting' ),
							'value'         => $transfer->note,
							'placeholder'   => __( 'Enter description', 'wp-ever-accounting' ),
							'wrapper_class' => 'is--full',
						)
					);
					?>
				</div>
			</div>

			<?php
			/**
			 * Fires action to inject custom content in the main column.
			 *
			 * @param Transfer $transfer Transfer object.
			 *
			 * @since 1.0.0
			 */
			do_action( 'eac_transfer_edit_core_content', $transfer );
			?>
		</div><!-- .column-1 -->
		<div class="column-2">
			<div class="eac-card">
				<div class="eac-card__header">
					<h3 class="eac-card__title"><?php esc_html_e( 'Save', 'wp-ever-accounting' ); ?></h3>
				</div>

				<?php if ( has_action( 'eac_transfer_edit_misc_actions' ) ) : ?>
					<div class="eac-card__body">
						<?php
						/**
						 * Fires to add custom actions.
						 *
						 * @param Transfer $transfer Transfer object.
						 *
						 * @since 2.0.0
						 */
						do_action( 'eac_transfer_edit_misc_actions', $transfer );
						?>
					</div>
				<?php endif; ?>

				<div class="eac-card__footer">
					<?php if ( $transfer->exists() ) : ?>
						<a class="del del_confirm" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'action', 'delete', $transfer->get_edit_url() ), 'bulk-transfers' ) ); ?>">
							<?php esc_html_e( 'Delete', 'wp-ever-accounting' ); ?>
						</a>
						<button class="button button-primary"><?php esc_html_e( 'Update Transfer', 'wp-ever-accounting' ); ?></button>
					<?php else : ?>
						<button class="button button-primary button-large tw-w-full"><?php esc_html_e( 'Save Transfer', 'wp-ever-accounting' ); ?></button>
					<?php endif; ?>
				</div>
			</div><!-- .eac-card -->

			<?php
			/**
			 * Fires action to inject custom content in the side column.
			 *
			 * @param Transfer $transfer Transfer object.
			 *
			 * @since 1.0.0
			 */
			do_action( 'eac_transfer_edit_sidebar_content', $transfer );
			?>

		</div><!-- .column-2 -->
	</div>


	<?php wp_nonce_field( 'eac_edit_transfer' ); ?>
	<input type="hidden" name="action" value="eac_edit_transfer"/>
	<input type="hidden" name="id" value="<?php echo esc_attr( $transfer->id ); ?>"/>
</form>
