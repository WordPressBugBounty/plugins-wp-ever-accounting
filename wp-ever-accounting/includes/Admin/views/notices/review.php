<?php
/**
 * Admin notice for review.
 *
 * @since 2.1.9
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
			<?php esc_html_e( 'Enjoying Ever Accounting?', 'wp-ever-accounting' ); ?>
		</h3>
		<p>
			<?php
			echo wp_kses_post(
				sprintf(
				// translators: %1$s: WP Ever Accounting Plugin link, %2$s: WordPress.org review link.
					__( 'We hope you had a wonderful experience using %1$s. Please take a moment to show us your support by leaving a 5-star review on <a href="%2$s" target="_blank"><strong>WordPress.org</strong></a>. Thank you! 😊', 'wp-ever-accounting' ),
					'<a href="https://wordpress.org/plugins/wp-ever-accounting/" target="_blank"><strong>WP Ever Accounting</strong></a>',
					'https://wordpress.org/support/plugin/wp-ever-accounting/reviews/?filter=5#new-post'
				)
			);
			?>
		</p>
	</div>
</div>
<div class="notice-footer">
	<a class="primary" href="https://wordpress.org/support/plugin/wp-ever-accounting/reviews/?filter=5#new-post" target="_blank">
		<span class="dashicons dashicons-heart"></span>
		<?php esc_html_e( 'Sure, I\'d love to help!', 'wp-ever-accounting' ); ?>
	</a>
	<a href="#" data-snooze>
		<span class="dashicons dashicons-clock"></span>
		<?php esc_html_e( 'Maybe later', 'wp-ever-accounting' ); ?>
	</a>
	<a href="#" data-dismiss>
		<span class="dashicons dashicons-smiley"></span>
		<?php esc_html_e( 'I\'ve already left a review', 'wp-ever-accounting' ); ?>
	</a>
</div>
