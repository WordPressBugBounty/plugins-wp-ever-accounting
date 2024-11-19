<?php
/**
 * Admin view: Bill list
 *
 * @since 1.0.0
 * @package EverAccounting
 * @var $bill \EverAccounting\Models\Bill Bill object.
 */

defined( 'ABSPATH' ) || exit;
global $list_table;
?>
	<h1 class="wp-heading-inline">
		<?php esc_html_e( 'Bills', 'wp-ever-accounting' ); ?>
		<a href="<?php echo esc_attr( admin_url( 'admin.php?page=eac-purchases&tab=bills&action=add' ) ); ?>" class="button button-small">
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
		<input type="hidden" name="page" value="eac-purchases"/>
		<input type="hidden" name="tab" value="bills"/>
		<input type="hidden" name="status" value="<?php echo esc_attr( filter_input( INPUT_GET, 'status', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ); ?>"/>
	</form>
<?php
