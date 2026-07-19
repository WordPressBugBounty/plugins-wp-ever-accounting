<?php
/**
 * Plugin Name:       Ever Accounting
 * Plugin URI:        https://wpeveraccounting.com/
 * Description:       Manage your business finances right from your WordPress dashboard.
 * Version:           2.3.0
 * Requires at least: 5.0
 * Tested up to:      7.0
 * Requires PHP:      7.4
 * Author:            EverAccounting
 * Author URI:        https://wpeveraccounting.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wp-ever-accounting
 * Domain Path:       /languages/
 *
 * @package EverAccounting
 */

use EverAccounting\Installer;
use EverAccounting\Plugin;

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/vendor/autoload.php';

// Migrate legacy version.
if ( get_option( 'eaccounting_version' ) ) {
	update_option( 'eac_version', get_option( 'eaccounting_version' ) );
	update_option( 'eac_install_date', get_option( 'eaccounting_install_date', wp_date( 'U' ) ) );
	delete_option( 'eaccounting_version' );
}

$data = array(
	'version'      => '2.3.0',
	'name'         => 'Ever Accounting',
	'prefix'       => 'eac',
	'hook_prefix'  => 'eac',
	'text_domain'  => 'wp-ever-accounting',
	'settings_url' => admin_url( 'admin.php?page=eac-settings' ),
	'review_url'   => 'https://wordpress.org/support/plugin/wp-ever-accounting/reviews/#new-post',
);

Plugin::create( __FILE__, $data );

/**
 * Main instance of EverAccounting.
 *
 * @since  1.0.0
 * @return Plugin
 */
function EAC() { // phpcs:ignore
	return Plugin::instance();
}

EAC()->on_activation( array( Installer::class, 'install' ) );
EAC()->on_deactivation( array( Installer::class, 'deactivate' ) );

EAC()->bootstrap();
