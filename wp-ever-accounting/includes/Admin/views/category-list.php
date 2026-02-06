<?php
/**
 * Admin view: Category list
 *
 * @package EverAccounting
 * @since 1.0.0
 * @var $category \EverAccounting\Models\Category Category object.
 */

defined( 'ABSPATH' ) || exit;
global $eac_list_table;
?>
	<h1 class="wp-heading-inline">
		<?php esc_html_e( 'Categories', 'wp-ever-accounting' ); ?>
		<a href="<?php echo esc_attr( admin_url( 'admin.php?page=eac-settings&tab=categories&action=add' ) ); ?>" class="button button-small">
			<?php esc_html_e( 'Add New', 'wp-ever-accounting' ); ?>
		</a>
		<a href="<?php echo esc_attr( admin_url( 'admin.php?page=eac-tools' ) ); ?>" class="button button-small">
			<?php esc_html_e( 'Import', 'wp-ever-accounting' ); ?>
		</a>
		<?php if ( $eac_list_table->get_request_search() ) : ?>
			<span class="subtitle"><?php echo esc_html( sprintf( /* translators: %s: search query. */ __( 'Search results for "%s"', 'wp-ever-accounting' ), esc_html( $eac_list_table->get_request_search() ) ) ); ?></span>
		<?php endif; ?>
	</h1>
	<form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>">
		<?php $eac_list_table->views(); ?>
		<?php $eac_list_table->search_box( __( 'Search', 'wp-ever-accounting' ), 'search' ); ?>
		<?php $eac_list_table->display(); ?>
		<input type="hidden" name="page" value="eac-settings"/>
		<input type="hidden" name="tab" value="categories"/>
		<input type="hidden" name="type" value="<?php echo esc_attr( filter_input( INPUT_GET, 'type', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ); ?>"/>
	</form>
<?php
