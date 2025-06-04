<?php
/**
 * Admin notice for upgrading to EAC Estimates extension.
 *
 * @since 2.2.1
 * @package EverAccounting\Admin\Views\Notices
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="notice-body">
	<div class="notice-icon">
		<img src="<?php echo esc_attr( EAC()->get_assets_url( 'images/plugin-icon.png' ) ); ?>" alt="Ever Accounting">
	</div>
	<div class="notice-content">
		<h3>
			<?php esc_html_e( '🎉 Flash Sale Alert!', 'wp-ever-accounting' ); ?>
		</h3>
		<p>
			<?php
			echo wp_kses_post(
				sprintf(
				// translators: %1$s: Extension link, %2$s: WP Ever Accounting Plugin link, %2$s: Discount percentage, %4$s: Call to action link.
					__( 'The %1$s for %2$s is now available! 💥 Enjoy an exclusive <strong>%3$s discount</strong> for a limited time — use <code>FLASH50</code> at checkout. Don\'t miss out — %4$s', 'wp-ever-accounting' ),
					'<a href="https://wpeveraccounting.com/extensions/estimates/?utm_source=plugin&utm_medium=notice&utm_campaign=flash-sale" target="_blank"><strong>Estimates Extension</strong></a>',
					'<a href="https://wordpress.org/plugins/wp-ever-accounting/" target="_blank"><strong>WP Ever Accounting</strong></a>',
					'50%',
					'<a href="https://wpeveraccounting.com/extensions/estimates/?utm_source=plugin&utm_medium=notice&utm_campaign=flash-sale" target="_blank" style="text-decoration: none; font-weight: bold; margin-top: 5px; display: inline-block;">Get the Extension →</a>'
				)
			);
			?>
		</p>
	</div>
</div>
<div class="notice-footer">
	<a class="primary" href="https://wpeveraccounting.com/extensions/estimates/?utm_source=plugin&utm_medium=notice&utm_campaign=flash-sale" target="_blank">
		<span class="dashicons dashicons-cart"></span>
		<?php esc_attr_e( 'Get the Extension', 'wp-ever-accounting' ); ?>
	</a>
	<a href="#" data-snooze="<?php echo esc_attr( MONTH_IN_SECONDS ); ?>">
		<span class="dashicons dashicons-clock"></span>
		<?php esc_attr_e( 'Maybe later', 'wp-ever-accounting' ); ?>
	</a>
	<a href="#" data-dismiss>
		<span class="dashicons dashicons-no-alt"></span>
		<?php esc_html_e( 'Close permanently', 'wp-ever-accounting' ); ?>
	</a>
</div>
