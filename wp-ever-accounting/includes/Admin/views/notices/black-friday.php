<?php
/**
 * Admin notice for black friday promo.
 *
 * @since 2.2.7
 * @package EverAccounting\Admin\Notices\Views
 * @return void
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="notice-body">
	<div class="notice-icon">
		<img src="<?php echo esc_attr( EAC()->get_assets_url( 'images/black-friday-icon.png' ) ); ?>" alt="Ever Accounting Black Friday Promo" />
	</div>
	<div class="notice-content">
		<h3>
			<?php esc_html_e( 'Black Friday Mega Sale! Get Flat 60% OFF on Ever Accounting Extensions !!', 'wp-ever-accounting' ); ?>
		</h3>
		<p>
			<?php
			echo wp_kses_post(
				sprintf(
				/* translators: 1. Offer Percentage, 2. Coupon Code. */
					__( 'Unlock premium extensions at an unbeatable price this Black Friday! Enjoy %1$s on all extensions with code %2$s. Hurry, this deal ends soon!', 'wp-ever-accounting' ),
					'<strong>' . esc_attr( '60% OFF' ) . '</strong>',
					'<strong>' . esc_attr( 'BFCM25' ) . '</strong>'
				)
			);
			?>
		</p>
	</div>
</div>
<div class="notice-footer">
	<a href="<?php echo esc_url( trailingslashit( EAC()->plugin_uri ) . 'extensions/?utm_source=plugin&utm_medium=notice&utm_campaign=black-friday-sale&discount=BFCM25#pricing-list' ); ?>" class="primary black-friday-upgrade-btn" target="_blank">
		<span class="dashicons dashicons-cart"></span>
		<?php esc_html_e( 'Claim your discount !!', 'wp-ever-accounting' ); ?>
	</a>
	<a href="#" data-snooze="<?php echo esc_attr( WEEK_IN_SECONDS ); ?>">
		<span class="dashicons dashicons-clock"></span>
		<?php esc_html_e( 'Remind me later', 'wp-ever-accounting' ); ?>
	</a>
	<a href="#" data-dismiss>
		<span class="dashicons dashicons-remove"></span>
		<?php esc_html_e( 'Never show this again!', 'wp-ever-accounting' ); ?>
	</a>
	<a href="#" class="btn-data-dismiss" data-dismiss><span class="dashicons dashicons-dismiss"></span></a>
</div>
