<?php
/**
 * Shows whats new.
 *
 * @package    EverAccounting
 * @subpackage Admin
 * @version    1.1.0
 */

namespace EverAccounting\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Class Release
 *
 * @package EverAccounting\Admin
 */
class Release {

	/**
	 * Release constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_page' ) );
	}

	/**
	 * Registers the new releases page.
	 */
	public function register_page() {
		add_dashboard_page( '', '', 'manage_options', 'ea-release', array( $this, 'render_page' ) );
	}

	/**
	 * Render page.
	 *
	 * @since 1.1.0
	 */
	public function render_page() {
		$version = eaccounting()->get_version();

		wp_enqueue_style( 'ea-admin-styles', eaccounting()->assets_url() . 'css/admin.css', array(), $version );
		wp_enqueue_style( 'ea-release-styles', eaccounting()->assets_url() . 'css/release.css', array(), $version );

		include dirname( __FILE__ ) . '/views/admin-page-release.php';
	}

	/**
	 * Render admin title.
	 *
	 * @return string
	 */
	public function admin_title() {
		$title  = '';
		$title .= '<div class="ea-release_logo">';
		$title .= '<img scr="' . eaccounting()->plugin_url( '/assets/images/everaccountinglogo.png' ) . '" alt="ea-release-logo">';
		$title .= '</div>';
		$title .= '<div class="ea-release_tag">';
		$title .= '<h2 class="wp-heading-inline">' . esc_html__( 'Best WordPress Accounting Plugin', 'wp-ever-accounting' ) . '</h2>';
		$title .= '</div>';

		return $title;
	}
}

new Release();
