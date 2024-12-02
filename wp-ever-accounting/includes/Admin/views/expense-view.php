<?php
/**
 * Admin View: Expense view
 *
 * @since 1.0.0
 *
 * @package EverAccounting
 * @var Expense $expense Expense.
 * @var string  $action Action.
 */

use EverAccounting\Models\Expense;

defined( 'ABSPATH' ) || exit;

wp_verify_nonce( '_wpnonce' );
$id      = isset( $_GET['id'] ) ? absint( wp_unslash( $_GET['id'] ) ) : 0;
$expense = EAC()->expenses->get( $id );

?>

<h1 class="wp-heading-inline">
	<?php esc_html_e( 'View Expense', 'wp-ever-accounting' ); ?>
	<a href="<?php echo esc_attr( admin_url( 'admin.php?page=eac-purchases&tab=expenses&action=add' ) ); ?>" class="button button-small">
		<?php esc_html_e( 'Add New', 'wp-ever-accounting' ); ?>
	</a>
	<a href="<?php echo esc_attr( remove_query_arg( array( 'action', 'id' ) ) ); ?>" title="<?php esc_attr_e( 'Go back', 'wp-ever-accounting' ); ?>">
		<span class="dashicons dashicons-undo"></span>
	</a>
</h1>

<div class="eac-poststuff">

	<div class="column-1">
		<div class="eac-card"><?php eac_get_template( 'content-expense.php', array( 'expense' => $expense ) ); ?></div>
		<?php
		/**
		 * Fires action to inject custom content in the main column.
		 *
		 * @param Expense $expense Expense object.
		 *
		 * @since 1.0.0
		 */
		do_action( 'eac_expense_edit_core_content', $expense );
		?>
	</div>

	<div class="column-2">
		<div class="eac-card">
			<div class="eac-card__header">
				<h2 class="eac-card__title"><?php esc_html_e( 'Actions', 'wp-ever-accounting' ); ?></h2>
				<?php if ( $expense->editable ) : ?>
					<a href="<?php echo esc_url( $expense->get_edit_url() ); ?>">
						<?php esc_html_e( 'Edit', 'wp-ever-accounting' ); ?>
					</a>
				<?php endif; ?>
			</div>
			<div class="eac-card__body">
				<?php
				/**
				 * Fires to add custom actions.
				 *
				 * @param Expense $expense Expense object.
				 *
				 * @since 2.0.0
				 */
				do_action( 'eac_expense_view_misc_actions', $expense );
				?>
				<a href="#" class="button button-small button-block eac_print_document" data-target=".eac-document">
					<span class="dashicons dashicons-printer"></span> <?php esc_html_e( 'Print', 'wp-ever-accounting' ); ?>
				</a>
				<a href="#" class="button button-small button-block eac_share_document" data-url="<?php echo esc_url( $expense->get_public_url() ); ?>">
					<span class="dashicons dashicons-share"></span> <?php esc_html_e( 'Share', 'wp-ever-accounting' ); ?>
				</a>
			</div>
			<div class="eac-card__footer">
				<a class="del del_confirm" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'action', 'delete', $expense->get_edit_url() ), 'bulk-expenses' ) ); ?>">
					<?php esc_html_e( 'Delete', 'wp-ever-accounting' ); ?>
				</a>
			</div>
		</div>

		<?php
		/**
		 * Fires action to inject custom content in the side column.
		 *
		 * @param Expense $expense Expense object.
		 *
		 * @since 1.0.0
		 */
		do_action( 'eac_expense_view_sidebar_content', $expense );
		?>

	</div><!-- .column-2 -->

</div><!-- .eac-poststuff -->

