<?php
/**
 * Admin View: Transfer List
 *
 * @since 1.0.0
 * @package EverAccounting
 * @var $transfer \EverAccounting\Models\Transfer Transfer object.
 */

defined( 'ABSPATH' ) || exit;

global $list_table;

?>
	<h1 class="wp-heading-inline">
		<?php esc_html_e( 'Transfers', 'wp-ever-accounting' ); ?>
		<?php if ( current_user_can( 'eac_edit_transfers' ) ) : // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability. ?>
		<a href="<?php echo esc_attr( admin_url( 'admin.php?page=eac-banking&tab=transfers&action=add' ) ); ?>" class="button button-small">
			<?php esc_html_e( 'Add New', 'wp-ever-accounting' ); ?>
		</a>
		<?php endif; ?>
		<?php if ( current_user_can( 'eac_edit_transfers' ) ) : // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability. ?>
		<a href="<?php echo esc_attr( admin_url( 'admin.php?page=eac-tools' ) ); ?>" class="button button-small">
			<?php esc_html_e( 'Import', 'wp-ever-accounting' ); ?>
		</a>
		<?php endif; ?>
		<?php if ( $list_table->get_request_search() ) : ?>
			<span class="subtitle"><?php echo esc_html( sprintf( /* translators: %s: Get requested search string */ __( 'Search results for "%s"', 'wp-ever-accounting' ), esc_html( $list_table->get_request_search() ) ) ); ?></span>
		<?php endif; ?>
	</h1>
	<form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>">
		<?php $list_table->views(); ?>
		<?php $list_table->search_box( __( 'Search', 'wp-ever-accounting' ), 'search' ); ?>
		<?php $list_table->display(); ?>
		<input type="hidden" name="page" value="eac-banking"/>
		<input type="hidden" name="tab" value="transfers"/>
	</form>
<?php
