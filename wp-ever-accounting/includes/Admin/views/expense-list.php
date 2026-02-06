<?php
/**
 * Admin View: Expense List
 *
 * @package EverAccounting
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

global $eac_list_table;
?>
	<h1 class="wp-heading-inline">
		<?php esc_html_e( 'Expenses', 'wp-ever-accounting' ); ?>
		<?php if ( current_user_can( 'eac_edit_expenses' ) ) : // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability. ?>
			<a href="<?php echo esc_attr( admin_url( 'admin.php?page=eac-purchases&tab=expenses&action=add' ) ); ?>" class="button button-small">
				<?php esc_html_e( 'Add New', 'wp-ever-accounting' ); ?>
			</a>
		<?php endif; ?>
		<?php if ( current_user_can( 'eac_manage_import' ) ) : // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability. ?>
			<a href="<?php echo esc_attr( admin_url( 'admin.php?page=eac-tools' ) ); ?>" class="button button-small">
				<?php esc_html_e( 'Import', 'wp-ever-accounting' ); ?>
			</a>
		<?php endif; ?>
		<?php if ( $eac_list_table->get_request_search() ) : ?>
			<?php // translators: %s: search query. ?>
			<span class="subtitle"><?php echo esc_html( sprintf( __( 'Search results for "%s"', 'wp-ever-accounting' ), esc_html( $eac_list_table->get_request_search() ) ) ); ?></span>
		<?php endif; ?>
	</h1>

	<form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>">
		<?php $eac_list_table->views(); ?>
		<?php $eac_list_table->search_box( __( 'Search', 'wp-ever-accounting' ), 'search' ); ?>
		<?php $eac_list_table->display(); ?>
		<input type="hidden" name="page" value="eac-purchases"/>
		<input type="hidden" name="tab" value="expenses"/>
	</form>
<?php
