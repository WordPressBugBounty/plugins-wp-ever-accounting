<?php
/**
 * Admin View: Payment List
 *
 * @package EverAccounting
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

global $list_table;
?>
	<h1 class="wp-heading-inline">
		<?php esc_html_e( 'Payments', 'wp-ever-accounting' ); ?>
		<a href="<?php echo esc_attr( admin_url( 'admin.php?page=eac-sales&tab=payments&action=add' ) ); ?>" class="button button-small">
			<?php esc_html_e( 'Add New', 'wp-ever-accounting' ); ?>
		</a>
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
		<input type="hidden" name="tab" value="payments"/>
	</form>
<?php
