<?php
/**
 * Admin view: Customer list
 *
 * @since 1.0.0
 * @package EverAccounting
 * @var $customer \EverAccounting\Models\Customer Customer object.
 */

defined( 'ABSPATH' ) || exit;

global $list_table;

?>
	<h1 class="wp-heading-inline">
		<?php esc_html_e( 'Customers', 'wp-ever-accounting' ); ?>
		<?php if ( current_user_can( 'eac_edit_customers' ) ) : // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Reason: This is a custom capability. ?>
			<a href="<?php echo esc_attr( admin_url( 'admin.php?page=eac-sales&tab=customers&action=add' ) ); ?>" class="button button-small">
				<?php esc_html_e( 'Add New', 'wp-ever-accounting' ); ?>
			</a>
		<?php endif; ?>
		<?php if ( current_user_can( 'eac_manage_import' ) ) : // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Reason: This is a custom capability. ?>
			<a href="<?php echo esc_attr( admin_url( 'admin.php?page=eac-tools' ) ); ?>" class="button button-small">
				<?php esc_html_e( 'Import', 'wp-ever-accounting' ); ?>
			</a>
		<?php endif; ?>
		<?php if ( $list_table->get_request_search() ) : ?>
			<?php // translators: %s: search query. ?>
			<span class="subtitle"><?php echo esc_html( sprintf( __( 'Search results for "%s"', 'wp-ever-accounting' ), esc_html( $list_table->get_request_search() ) ) ); ?></span>
		<?php endif; ?>
	</h1>
	<form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>">
		<?php $list_table->views(); ?>
		<?php $list_table->search_box( __( 'Search', 'wp-ever-accounting' ), 'search' ); ?>
		<?php $list_table->display(); ?>
		<input type="hidden" name="page" value="eac-sales"/>
		<input type="hidden" name="tab" value="customers"/>
	</form>
<?php
