<?php
/**
 * Plugin Name:       Ever Accounting
 * Plugin URI:        https://wpeveraccounting.com/
 * Description:       Manage your business finances right from your WordPress dashboard.
 * Version:           2.0.4
 * Requires at least: 4.7.0
 * Tested up to:      6.7
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

defined( 'ABSPATH' ) || exit();

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/vendor-prefixed/autoload.php';

// Migrate legacy version.
if ( get_option( 'eaccounting_version' ) ) {
	update_option( 'eac_version', get_option( 'eaccounting_version' ) );
	update_option( 'eac_install_date', get_option( 'eaccounting_install_date', wp_date( 'U' ) ) );
	delete_option( 'eaccounting_version' );
}

/**
 * Main instance of EverAccounting.
 *
 * @since  1.0.0
 * @return EverAccounting\Plugin
 */
function EAC() { // phpcs:ignore
	return EverAccounting\Plugin::create( __FILE__ );
}

// Instantiate the plugin.
EAC();
