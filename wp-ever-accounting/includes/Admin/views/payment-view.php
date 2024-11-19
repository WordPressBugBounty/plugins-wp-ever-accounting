<?php
/**
 * Admin payment view.
 *
 * @since 1.0.0
 *
 * @package EverAccounting
 * @var Payment $payment Payment.
 * @var string  $action Action.
 */

use EverAccounting\Models\Payment;

defined( 'ABSPATH' ) || exit;

wp_verify_nonce( '_wpnonce' );
$id      = isset( $_GET['id'] ) ? absint( wp_unslash( $_GET['id'] ) ) : 0;
$payment = EAC()->payments->get( $id );

?>

<h1 class="wp-heading-inline">
	<?php esc_html_e( 'View Payment', 'wp-ever-accounting' ); ?>
	<a href="<?php echo esc_attr( remove_query_arg( array( 'action', 'id' ) ) ); ?>" title="<?php esc_attr_e( 'Go back', 'wp-ever-accounting' ); ?>">
		<span class="dashicons dashicons-undo"></span>
	</a>
</h1>

<div class="eac-poststuff">

	<div class="column-1">
		<div class="eac-card">
			<?php eac_get_template( 'content-payment.php', array( 'payment' => $payment ) ); ?>
		</div>
		<?php
		/**
		 * Fires action to inject custom content in the main column.
		 *
		 * @param Payment $payment Payment object.
		 *
		 * @since 1.0.0
		 */
		do_action( 'eac_payment_edit_core_content', $payment );
		?>
	</div>

	<div class="column-2">
		<div class="eac-card">
			<div class="eac-card__header">
				<h2 class="eac-card__title"><?php esc_html_e( 'Actions', 'wp-ever-accounting' ); ?></h2>
				<?php if ( $payment->editable ) : ?>
					<a href="<?php echo esc_url( $payment->get_edit_url() ); ?>">
						<?php esc_html_e( 'Edit', 'wp-ever-accounting' ); ?>
					</a>
				<?php endif; ?>
			</div>
			<div class="eac-card__body">
				<?php
				/**
				 * Fires to add custom actions.
				 *
				 * @param Payment $payment Payment object.
				 *
				 * @since 2.0.0
				 */
				do_action( 'eac_payment_view_misc_actions', $payment );
				?>
				<a href="#" class="button button-small button-block eac_print_document" data-target=".eac-document">
					<span class="dashicons dashicons-printer"></span> <?php esc_html_e( 'Print', 'wp-ever-accounting' ); ?>
				</a>
				<a href="#" class="button button-small button-block eac_share_document" data-url="<?php echo esc_url( $payment->get_public_url() ); ?>">
					<span class="dashicons dashicons-share"></span> <?php esc_html_e( 'Share', 'wp-ever-accounting' ); ?>
				</a>
			</div>
			<div class="eac-card__footer">
				<a class="del del_confirm" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'action', 'delete', $payment->get_edit_url() ), 'bulk-payments' ) ); ?>">
					<?php esc_html_e( 'Delete', 'wp-ever-accounting' ); ?>
				</a>
			</div>
		</div>

		<?php
		/**
		 * Fires action to inject custom content in the side column.
		 *
		 * @param Payment $payment Payment object.
		 *
		 * @since 1.0.0
		 */
		do_action( 'eac_payment_view_sidebar_content', $payment );
		?>

	</div><!-- .column-2 -->

</div><!-- .eac-poststuff -->

