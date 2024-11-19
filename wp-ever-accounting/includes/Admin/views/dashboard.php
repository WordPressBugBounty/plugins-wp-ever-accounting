<?php
/**
 * Admin View: Dashboard
 *
 * @since 2.0.0
 * @package EverAccounting
 */

defined( 'ABSPATH' ) || exit;


?>

<div class="wrap eac-wrap">
	<h1 class="wp-heading-inline !tw-mb-2"><?php esc_html_e( 'Dashboard', 'wp-ever-accounting' ); ?></h1>

	<?php
	/**
	 * Fire action to add dashboard widgets.
	 *
	 * @since 2.0.0
	 * @hook eac_dashboard_core_widgets
	 */
	do_action( 'eac_dashboard_overview_widgets' );
	?>

	<div class="eac-grid cols-2">
		<?php
		/**
		 * Fire action to add dashboard widgets.
		 *
		 * @since 2.0.0
		 * @hook eac_dashboard_advanced_widgets
		 */
		do_action( 'eac_dashboard_advanced_widgets' );
		?>
	</div>

	<div class="eac-grid cols-3">
		<?php
		/**
		 * Fire action to add dashboard widgets.
		 *
		 * @since 2.0.0
		 * @hook eac_dashboard_widgets
		 */
		do_action( 'eac_dashboard_widgets' );
		?>
	</div>
</div>
