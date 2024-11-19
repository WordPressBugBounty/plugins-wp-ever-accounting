<?php

namespace EverAccounting\Admin;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Class Extensions
 *
 * @package EverAccounting\Admin
 * @since 1.0.0
 */
class Extensions {

	/**
	 * Extensions constructor.
	 */
	public function __construct() {
		add_filter( 'eac_extensions_page_tabs', array( __CLASS__, 'register_tabs' ), -1 );
		add_action( 'eac_extensions_page_extensions_content', array( __CLASS__, 'extensions_content' ) );
	}

	/**
	 * Register tabs.
	 *
	 * @param array $tabs Tabs.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public static function register_tabs( $tabs ) {
		$tabs['extensions'] = __( 'Extensions', 'wp-ever-accounting' );

		return $tabs;
	}

	/**
	 * Extensions content.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function extensions_content() {
		$extensions = self::get_extensions();
		?>
		<div class="eac-section-header">
			<h1><?php esc_html_e( 'Extensions', 'wp-ever-accounting' ); ?></h1>
		</div>
		<div class="eac-grid cols-3 eac-card--extensions">
			<?php foreach ( $extensions as $extension ) : ?>
				<div class="eac-card">
					<div class="eac-card__header">
						<a href="<?php echo esc_url( $extension->info->link ); ?>" title="<?php echo esc_html( $extension->info->title ); ?>" target="_blank">
							<img src="<?php echo esc_url( EAC()->get_assets_url() . 'images/img1.png' ); ?>" alt="<?php echo esc_html( $extension->info->title ); ?>" class="eac-card__thumbnail">
							<img src="<?php echo esc_url( $extension->info->thumbnail ); ?>" alt="<?php echo esc_html( $extension->info->title ); ?>" class="eac-card__thumbnail">
						</a>
					</div>
					<div class="eac-card__body">
						<a href="<?php echo esc_url( $extension->info->link ); ?>" title="<?php echo esc_html( $extension->info->title ); ?>" target="_blank">
							<h2 class="eac-card__title"><?php echo esc_html( $extension->info->title ); ?></h2>
						</a>
						<p><?php echo wp_kses_post( $extension->info->excerpt ); ?></p>
						<a class="button-primary eac-card__btn" href="<?php echo esc_url( $extension->info->link ); ?>" target="_blank"><?php esc_html_e( 'Get this Extension', 'wp-ever-accounting' ); ?></a>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Get extensions.
	 *
	 * @since 1.0.0
	 * @return array Extensions.
	 */
	public static function get_extensions() {
		$extensions = array(
			(object) array(
				'info' => (object) array(
					'title'     => 'Extension 1',
					'link'      => 'https://wpeveraccounting.com/?post_type=download&p=962',
					'thumbnail' => 'https://wpeveraccounting.com/wp-content/uploads/edd/2024/10/slack-integration-150x150.png',
					'excerpt'   => 'This is an extension.',
				),
			),
			(object) array(
				'info' => (object) array(
					'title'     => 'Extension 2',
					'link'      => 'https://wpeveraccounting.com/?post_type=download&p=927',
					'thumbnail' => 'https://wpeveraccounting.com/wp-content/uploads/edd/2024/10/woocommerce-integration-150x150.png',
					'excerpt'   => 'This is an extension.',
				),
			),
			(object) array(
				'info' => (object) array(
					'title'     => 'Extension 2',
					'link'      => 'https://wpeveraccounting.com/?post_type=download&p=927',
					'thumbnail' => 'https://wpeveraccounting.com/wp-content/uploads/edd/2024/10/woocommerce-integration-150x150.png',
					'excerpt'   => 'This is an extension.',
				),
			),
			(object) array(
				'info' => (object) array(
					'title'     => 'Extension 2',
					'link'      => 'https://wpeveraccounting.com/?post_type=download&p=927',
					'thumbnail' => 'https://wpeveraccounting.com/wp-content/uploads/edd/2024/10/woocommerce-integration-150x150.png',
					'excerpt'   => 'This is an extension.',
				),
			),
		);

		$cache = get_transient( 'eac_extensions_feed' );

		// TODO: The bellow code is not working because of the API response.
		if ( empty( $cache ) ) {
			$url = 'https://wpeveraccounting.com/edd-api/products/';

			$feed = wp_remote_get( esc_url_raw( $url ), array( 'sslverify' => false ) );

			if ( ! is_wp_error( $feed ) ) {
				if ( isset( $feed['body'] ) && strlen( $feed['body'] ) > 0 ) {
					$body  = wp_remote_retrieve_body( $feed );
					$cache = json_decode( $body )->products;
					set_transient( 'eac_extensions_feed', $cache, 3600 );
				}
			}
		}

		return $extensions;
	}
}
