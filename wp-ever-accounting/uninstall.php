<?php
/**
 * EVerAccounting Uninstall
 *
 * Uninstalling EAccounting deletes user roles, tables, and options.
 *
 * @package EVerAccounting\Uninstaller
 * @version 1.1.0
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

/*
 * Only remove ALL product and page data if EAC_REMOVE_ALL_DATA constant is set to true in user's
 * wp-config.php. This is to prevent data loss when deleting the plugin from the backend
 * and to ensure only the site owner can perform this action.
 */
if ( defined( 'EAC_REMOVE_ALL_DATA' ) && true === EAC_REMOVE_ALL_DATA ) {
	\EverAccounting\Installer::uninstall();
}
