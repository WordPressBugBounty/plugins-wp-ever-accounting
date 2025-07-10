<?php

namespace EverAccounting\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Changelog class.
 *
 * @since 2.2.2
 * @package EverAccounting\Admin
 */
class Changelog {

	/**
	 * Static array to hold the changelog entries.
	 *
	 * @since 2.2.2
	 * @var array
	 */
	public static $changelog = array(
		'2.2.2' => array( __CLASS__, 'changelog_2_2_2' ),
	);

	/**
	 * Changelog constructor.
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'redirect_to_changelog' ) );
		add_filter( 'eac_admin_menus', array( __CLASS__, 'add_changelog_menu' ) );
		add_filter( 'eac_changelog_page_tabs', array( __CLASS__, 'register_tabs' ) );
		add_action( 'eac_changelog_page_changelog_content', array( __CLASS__, 'render_changelog' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
	}

	/**
	 * Redirect to changelog page.
	 *
	 * This method checks if the database version has been updated and if the user has the required capability to view the changelog.
	 * If both conditions are met, it redirects the user to the changelog page for the current version.
	 *
	 * @since 2.2.2
	 * @return void
	 */
	public function redirect_to_changelog() {
		if ( ! get_option( 'eac_version_updated' ) || ! current_user_can( 'eac_manage_options' ) ) {  // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Reason: This is a custom capability.
			return;
		}

		// Delete the option to prevent multiple redirects.
		delete_option( 'eac_version_updated' );
		$version = EAC()->get_db_version();
		if ( ! $version || ! isset( self::$changelog[ $version ] ) || ! is_callable( self::$changelog[ $version ] ) ) {
			return;
		}

		wp_safe_redirect( admin_url( 'admin.php?page=eac-changelog&version=' . $version ) );
		exit;
	}

	/**
	 * Add the changelog menu to the admin menus.
	 *
	 * @param array $menus Existing admin menus.
	 *
	 * @since 2.2.2
	 * @return array Modified admin menus with changelog.
	 */
	public static function add_changelog_menu( $menus ) {

		if ( is_array( $menus ) ) {
			$menus[] = array(
				'page_title' => __( 'Changelog', 'wp-ever-accounting' ),
				'menu_title' => '',
				'capability' => 'eac_manage_options',
				'menu_slug'  => 'eac-changelog',
				'position'   => 100,
			);
		}

		return $menus;
	}

	/**
	 * Register tabs for the changelog page.
	 *
	 * @param array $tabs Existing tabs.
	 *
	 * @since 2.2.2
	 * @return array Modified tabs with changelog.
	 */
	public static function register_tabs( $tabs ) {
		remove_submenu_page( 'ever-accounting', 'eac-changelog' );
		if ( current_user_can( 'eac_read_reports' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
			$tabs['changelog'] = __( 'Changelog', 'wp-ever-accounting' );
		}

		return $tabs;
	}

	/**
	 * Render the changelog content.
	 *
	 * @since 2.2.2
	 * @return void
	 */
	public static function render_changelog() {
		wp_verify_nonce( '_nonce' );
		$version   = isset( $_GET['version'] ) ? sanitize_text_field( wp_unslash( $_GET['version'] ) ) : 0;
		$changelog = array();
		if ( $version && isset( self::$changelog[ $version ] ) && is_callable( self::$changelog[ $version ] ) ) {
			$changelog = call_user_func( self::$changelog[ $version ] );
		}
		?>
		<h1 class="wp-heading-inline"><?php echo esc_html__( 'Changelog', 'wp-ever-accounting' ); ?></h1>
		<p class="description">
			<?php echo esc_html__( 'Below is the changelog for Ever Accounting. This update delivers several enhancements designed to improve overall performance, reliability, code quality and new features.', 'wp-ever-accounting' ); ?>
		</p>
		<div class="eac-changelog">
			<div class="eac-poststuff">
				<div class="column-1">
					<div class="eac-card">
						<div class="eac-card__header">
							<h3 class="eac-card__title"><?php echo esc_html( 'v' . $version ); ?></h3>
						</div>
						<div class="eac-card__body">
							<div class="eac-changelog__items">
								<?php if ( ! empty( $changelog ) && is_array( $changelog ) ) : ?>
									<?php foreach ( $changelog as $change ) : ?>
										<div class="eac-changelog__item">
											<h3 class="eac-changelog__item-title">
												<?php echo esc_html( $change['title'] ); ?>
												<sup class="eac-changelog__item-badge badge--<?php echo esc_attr( $change['type'] ); ?>" title="<?php echo esc_attr( $change['badge'] ); ?>">
													<?php echo esc_html( $change['badge'] ); ?>
												</sup>
											</h3>
											<div class="eac-changelog__item-message">
												<?php echo esc_html( $change['message'] ); ?>
											</div>
										</div>
									<?php endforeach; ?>
								<?php else : ?>
									<p><?php echo esc_html__( 'No changes found for this version.', 'wp-ever-accounting' ); ?></p>
								<?php endif; ?>
							</div>
						</div>
					</div>
				</div>
				<div class="column-2">
					<!-- Support and Documentation-->
					<div class="eac-card">
						<div class="eac-card__header">
							<h3 class="eac-card__title"><?php esc_html_e( 'Support', 'wp-ever-accounting' ); ?></h3>
						</div>
						<div class="eac-card__body">
							<p><?php echo esc_html__( 'If you have any questions or need support, please visit our support page.', 'wp-ever-accounting' ); ?></p>
							<p><?php echo esc_html__( 'Prefer email? Please send your queries to us directly at: support@wpeveraccounting.com', 'wp-ever-accounting' ); ?></p>
						</div>
						<div class="eac-card__footer">
							<a href="https://wpeveraccounting.com/contact-us/" class="button button-secondary" target="_blank">
								<?php echo esc_html__( 'Get Support', 'wp-ever-accounting' ); ?>
							</a>
							<a href="https://wpeveraccounting.com/docs/" class="button button-secondary" target="_blank">
								<?php echo esc_html__( 'View Documentation', 'wp-ever-accounting' ); ?>
							</a>
						</div>
					</div>
					<!-- Extensions -->
					<div class="eac-card">
						<div class="eac-card__header">
							<h3 class="eac-card__title"><?php esc_html_e( 'Extensions', 'wp-ever-accounting' ); ?></h3>
						</div>
						<div class="eac-card__body">
							<p><?php echo esc_html__( 'Enhance your accounting experience with our premium extensions.', 'wp-ever-accounting' ); ?></p>
							<ul>
								<li>
									<a href="https://wpeveraccounting.com/extensions/woocommerce/" target="_blank">
										<?php echo esc_html__( 'WooCommerce Integration', 'wp-ever-accounting' ); ?>
									</a>
								</li>
								<li>
									<a href="https://wpeveraccounting.com/extensions/estimates/" target="_blank">
										<?php echo esc_html__( 'Estimates', 'wp-ever-accounting' ); ?>
									</a>
								</li>
							</ul>
						</div>
						<div class="eac-card__footer">
							<a href="https://wpeveraccounting.com/extensions/" class="button button-secondary" target="_blank">
								<?php echo esc_html__( 'View All Extensions', 'wp-ever-accounting' ); ?>
							</a>
						</div>
					</div>
					<!-- Write a review on WordPress.org -->
					<div class="eac-card">
						<div class="eac-card__header">
							<h3 class="eac-card__title"><?php esc_html_e( 'Rate Us', 'wp-ever-accounting' ); ?></h3>
						</div>
						<div class="eac-card__body">
							<p><?php echo esc_html__( 'If you like WP Ever Accounting, please consider leaving a review on WordPress.org.', 'wp-ever-accounting' ); ?></p>
						</div>
						<div class="eac-card__footer">
							<a href="https://wordpress.org/support/plugin/wp-ever-accounting/reviews/?rate=5#new-post" class="button button-secondary" target="_blank">
								<?php echo esc_html__( 'Leave a Review', 'wp-ever-accounting' ); ?>
							</a>
						</div>
					</div>
					<!-- Need Help? -->
					<div class="eac-card">
						<div class="eac-card__header">
							<h3 class="eac-card__title"><?php esc_html_e( 'Need Help?', 'wp-ever-accounting' ); ?></h3>
						</div>
						<div class="eac-card__body">
							<p><?php echo esc_html__( 'If you need help, please join our community or contact us directly.', 'wp-ever-accounting' ); ?></p>
							<ul>
								<li>
									<a href="https://www.facebook.com/everaccounting" target="_blank">
										<?php echo esc_html__( 'Join Community', 'wp-ever-accounting' ); ?>
									</a>
								</li>
								<li>
									<a href="https://wpeveraccounting.com/contact-us/" target="_blank">
										<?php echo esc_html__( 'Request a Feature', 'wp-ever-accounting' ); ?>
									</a>
								</li>
								<li>
									<a href="https://wpeveraccounting.com/contact-us/" target="_blank">
										<?php echo esc_html__( 'Report a Bug', 'wp-ever-accounting' ); ?>
									</a>
								</li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Get the changelogs.
	 *
	 * @since 2.2.2
	 * @return array Changelogs
	 */
	public static function changelog_2_2_2() {
		return array(
			array(
				'type'    => 'enhancement',
				'badge'   => __( 'Enhancement', 'wp-ever-accounting' ),
				'title'   => __( 'Improved Date Handling', 'wp-ever-accounting' ),
				'message' => __( 'Enhanced date handling for better compatibility across various time zones.', 'wp-ever-accounting' ),
			),
			array(
				'type'    => 'enhancement',
				'badge'   => __( 'Enhancement', 'wp-ever-accounting' ),
				'title'   => __( 'Refactored Directory Structure', 'wp-ever-accounting' ),
				'message' => __( 'Refactored the plugin directory structure for better organization and maintainability.', 'wp-ever-accounting' ),
			),
			array(
				'type'    => 'feature',
				'badge'   => __( 'New Feature', 'wp-ever-accounting' ),
				'title'   => __( 'Dynamic JavaScript Components', 'wp-ever-accounting' ),
				'message' => __( 'Added a customizable datetimepicker and improved JavaScript logic to handle dynamic field updates based on currency changes, ensuring more accurate and intuitive data entry on forms.', 'wp-ever-accounting' ),
			),
			array(
				'type'    => 'bugfix',
				'badge'   => __( 'Bug Fix', 'wp-ever-accounting' ),
				'title'   => __( 'Fixed Date Format Issue', 'wp-ever-accounting' ),
				'message' => __( 'Resolved a minor issue affecting certain date formats.', 'wp-ever-accounting' ),
			),
			array(
				'type'    => 'bugfix',
				'badge'   => __( 'Bug Fix', 'wp-ever-accounting' ),
				'title'   => __( 'Chart Rendering on Account Overview', 'wp-ever-accounting' ),
				'message' => __( 'Fixed a bug that caused display issues in charts shown on the Account overview page.', 'wp-ever-accounting' ),
			),
			array(
				'type'    => 'bugfix',
				'badge'   => __( 'Bug Fix', 'wp-ever-accounting' ),
				'title'   => __( 'Expense Attachment Display', 'wp-ever-accounting' ),
				'message' => __( 'Corrected an issue where attachments were missing in the expense view page.', 'wp-ever-accounting' ),
			),
			array(
				'type'    => 'security',
				'badge'   => __( 'Security', 'wp-ever-accounting' ),
				'title'   => __( 'Remove Unused Code', 'wp-ever-accounting' ),
				'message' => __( 'Removed unused code and files to streamline the plugin.', 'wp-ever-accounting' ),
			),
		);
	}

	/**
	 * Enqueue scripts for the changelog page.
	 *
	 * @since 2.2.2
	 * @return void
	 */
	public static function enqueue_scripts() {
		$css = 'a[href*="eac-changelog"] { display: none !important; }';

		wp_add_inline_style( 'common', $css );
	}
}
