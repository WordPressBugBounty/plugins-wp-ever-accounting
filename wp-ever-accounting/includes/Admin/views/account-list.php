<?php
/**
 * Admin View: Account List
 *
 * @since 1.0.0
 * @package EverAccounting
 * @var $account Account Account object.
 */

use EverAccounting\Models\Account;

defined( 'ABSPATH' ) || exit;

global $eac_list_table;

?>
	<h1 class="wp-heading-inline">
		<?php esc_html_e( 'Accounts', 'wp-ever-accounting' ); ?>
		<?php if ( current_user_can( 'eac_edit_accounts' ) ) : // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability. ?>
			<a href="<?php echo esc_attr( admin_url( 'admin.php?page=eac-banking&tab=accounts&action=add' ) ); ?>" class="button button-small">
				<?php esc_html_e( 'Add New', 'wp-ever-accounting' ); ?>
			</a>
		<?php endif; ?>
		<?php if ( current_user_can( 'eac_manage_import' ) ) : // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability. ?>
			<a href="<?php echo esc_attr( admin_url( 'admin.php?page=eac-tools' ) ); ?>" class="button button-small">
				<?php esc_html_e( 'Import', 'wp-ever-accounting' ); ?>
			</a>
		<?php endif; ?>
		<?php if ( $eac_list_table->get_request_search() ) : ?>
			<span class="subtitle"><?php echo esc_html( sprintf( /* translators: %s: search query. */ __( 'Search results for "%s"', 'wp-ever-accounting' ), esc_html( $eac_list_table->get_request_search() ) ) ); ?></span>
		<?php endif; ?>
	</h1>
	<form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>">
		<?php $eac_list_table->views(); ?>
		<?php $eac_list_table->search_box( __( 'Search', 'wp-ever-accounting' ), 'search' ); ?>
		<?php $eac_list_table->display(); ?>
		<input type="hidden" name="page" value="eac-banking"/>
		<input type="hidden" name="tab" value="accounts"/>
		<input type="hidden" name="type" value="<?php echo esc_attr( filter_input( INPUT_GET, 'type', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ); ?>"/>
	</form>
<?php
