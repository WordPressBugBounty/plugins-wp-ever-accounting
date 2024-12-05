<?php

namespace EverAccounting;

use EverAccounting\Admin\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Class Installer.
 *
 * @since   1.0.0
 * @package EverAccounting
 */
class Installer {
	/**
	 * Update callbacks.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $updates = array(
		'2.0.0' => array(
			'eac_update_120_settings',
			'eac_update_120_transactions',
			'eac_update_120_documents',
			'eac_update_120_accounts',
			'eac_update_120_categories',
			'eac_update_120_contacts',
			'eac_update_120_items',
			'eac_update_120_notes',
			'eac_update_120_misc',
		),
		'2.0.4' => array(
			'eac_update_204_roles',
		),
	);

	/**
	 * Construct and initialize the plugin aware trait.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'check_update' ), 5 );
		add_action( 'admin_notices', array( $this, 'update_notice' ) );
		add_action( 'admin_init', array( $this, 'activation_redirect' ) );
		add_action( 'eac_run_update_callback', array( $this, 'run_update_callback' ), 10, 2 );
		add_action( 'eac_update_db_version', array( $this, 'update_db_version' ) );
	}

	/**
	 * Check the plugin version and run the updater if necessary.
	 *
	 * This check is done on all requests and runs if the versions do not match.
	 *
	 * @since 1.2.1
	 * @return void
	 */
	public function check_update() {
		$db_version      = EAC()->get_db_version();
		$current_version = EAC()->get_version();
		$requires_update = version_compare( $db_version, $current_version, '<' );
		$can_install     = ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) && ! defined( 'IFRAME_REQUEST' );
		if ( $can_install && $requires_update && ! EAC()->queue()->get_next( 'eac_run_update_callback' ) ) {
			static::install();
			$update_versions = array_keys( $this->updates );
			usort( $update_versions, 'version_compare' );
			if ( ! is_null( $db_version ) && version_compare( $db_version, end( $update_versions ), '<' ) ) {
				$this->update();
			} else {
				EAC()->update_db_version( $current_version );
			}
		}
	}

	/**
	 * Update the plugin.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function update() {
		$db_version = EAC()->get_db_version();
		$loop       = 0;
		foreach ( $this->updates as $version => $callbacks ) {
			$callbacks = (array) $callbacks;
			if ( version_compare( $db_version, $version, '<' ) ) {
				foreach ( $callbacks as $callback ) {
					EAC()->queue()->schedule_single(
						time() + $loop,
						'eac_run_update_callback',
						array(
							'callback' => $callback,
							'version'  => $version,
						)
					);
					++$loop;
				}
			}
			++$loop;
		}

		if ( version_compare( EAC()->get_db_version(), EAC()->get_version(), '<' ) &&
			! EAC()->queue()->get_next( 'eac_update_db_version' ) ) {
			EAC()->queue()->schedule_single(
				time() + $loop,
				'eac_update_db_version',
				array(
					'version' => EAC()->get_version(),
				)
			);
		}
	}

	/**
	 * Display an update notice.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function update_notice() {
		if ( EAC()->queue()->get_next( 'eac_run_update_callback' ) ) {
			?>
			<div class="notice notice-info is-dismissible">
				<p><?php esc_html_e( 'Ever Accounting is updating the database in the background. Please wait.', 'wp-ever-accounting' ); ?></p>
			</div>
			<?php
		}
	}

	/**
	 * Redirect to the welcome page on activation.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function activation_redirect() {
		if ( ! get_transient( 'eac_installed' ) || ! current_user_can( 'eac_manage_options' ) ) {  // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Reason: This is a custom capability.
			return;
		}
		delete_transient( 'eac_installed' );
		flush_rewrite_rules();
		wp_safe_redirect( add_query_arg( 'page', 'ever-accounting', admin_url( 'admin.php' ) ) );
		exit;
	}

	/**
	 * Run the update callback.
	 *
	 * @param string $callback The callback to run.
	 * @param string $version The version of the callback.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function run_update_callback( $callback, $version ) {
		require_once __DIR__ . '/Functions/updates.php';
		if ( is_callable( $callback ) ) {
			$result = (bool) call_user_func( $callback );
			if ( $result ) {
				EAC()->queue()->add(
					'eac_run_update_callback',
					array(
						'callback' => $callback,
						'version'  => $version,
					)
				);
			}
		}
	}

	/**
	 * Update the plugin version.
	 *
	 * @param string $version The version to update to.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function update_db_version( $version ) {
		EAC()->update_db_version( $version );
	}

	/**
	 * Install the plugin.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function install() {
		if ( ! is_blog_installed() ) {
			return;
		}
		self::create_tables();
		self::create_roles();
		self::create_cron_jobs();
		self::save_settings();
		EAC()->add_db_version();

		// Set installation date.
		add_option( 'eac_install_date', wp_date( 'U' ) );
		set_transient( 'eac_installed', 1, 60 );

		// Force a flush of rewrite rules even if the corresponding hook isn't initialized yet.
		if ( ! has_action( 'eac_flush_rewrite_rules' ) ) {
			flush_rewrite_rules();
		}

		/**
		 * Perform actions after the plugin is installed.
		 *
		 * @since 1.0.0
		 */
		do_action( 'eac_installed' );
	}

	/**
	 * Create tables.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function create_tables() {
		global $wpdb;
		$wpdb->hide_errors();
		$collate = $wpdb->has_cap( 'collation' ) ? $wpdb->get_charset_collate() : '';

		$tables = "
CREATE TABLE {$wpdb->prefix}ea_accounts (
id BIGINT(20) NOT NULL AUTO_INCREMENT,
type VARCHAR(50) NOT NULL DEFAULT 'account',
name VARCHAR(191) NOT NULL,
number VARCHAR(100) NOT NULL,
balance DOUBLE(15, 4) NOT NULL DEFAULT 0.00,
currency VARCHAR(3) NOT NULL DEFAULT 'USD',
date_created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
date_updated DATETIME DEFAULT NULL,
PRIMARY KEY (id),
UNIQUE KEY number (number),
KEY bank_name (name),
KEY bank_type (type)
) $collate;

CREATE TABLE {$wpdb->prefix}ea_contactmeta (
meta_id BIGINT(20) NOT NULL AUTO_INCREMENT,
ea_contact_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
meta_key VARCHAR(191) DEFAULT NULL,
meta_value LONGTEXT,
PRIMARY KEY (meta_id),
KEY ea_contact_id (ea_contact_id),
KEY meta_key (meta_key(191))
) $collate;

CREATE TABLE {$wpdb->prefix}ea_contacts (
id BIGINT(20) NOT NULL AUTO_INCREMENT,
type VARCHAR(30) DEFAULT 'customer',
name VARCHAR(100) NOT NULL,
company VARCHAR(100) NOT NULL,
email VARCHAR(100) DEFAULT NULL,
phone VARCHAR(50) DEFAULT NULL,
website VARCHAR(100) DEFAULT NULL,
address VARCHAR(191) DEFAULT NULL,
city VARCHAR(50) DEFAULT NULL,
state VARCHAR(50) DEFAULT NULL,
postcode VARCHAR(20) DEFAULT NULL,
country VARCHAR(3) DEFAULT NULL,
tax_number VARCHAR(50) DEFAULT NULL,
currency VARCHAR(3) NOT NULL DEFAULT 'USD',
user_id BIGINT(20) UNSIGNED DEFAULT NULL,
created_via VARCHAR(20) DEFAULT 'manual',
date_created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
date_updated DATETIME DEFAULT NULL,
PRIMARY KEY (id),
KEY name (name(100)),
KEY type (type),
KEY email (email(100)),
KEY phone (phone(50)),
KEY currency (currency),
KEY user_id (user_id)
) $collate;

CREATE TABLE {$wpdb->prefix}ea_document_items (
id BIGINT(20) NOT NULL AUTO_INCREMENT,
document_id BIGINT(20) UNSIGNED DEFAULT NULL,
item_id BIGINT(20) UNSIGNED DEFAULT NULL,
type VARCHAR(20) NOT NULL DEFAULT 'standard',
name VARCHAR(191) NOT NULL,
description VARCHAR(160) DEFAULT NULL,
unit VARCHAR(20) DEFAULT NULL,
price DOUBLE(15, 4) NOT NULL DEFAULT 0.00,
quantity DOUBLE(7, 2) NOT NULL DEFAULT 1,
subtotal DOUBLE(15, 4) NOT NULL DEFAULT 0.00,
discount DOUBLE(15, 4) NOT NULL DEFAULT 0.00,
tax DOUBLE(15, 4) NOT NULL DEFAULT 0.00,
total DOUBLE(15, 4) NOT NULL DEFAULT 0.00,
currency VARCHAR(3) NOT NULL DEFAULT 'USD',
PRIMARY KEY (id),
KEY type (type),
KEY name (name),
KEY price (price),
KEY quantity (quantity),
KEY subtotal (subtotal),
KEY total (total)
) $collate;

CREATE TABLE {$wpdb->prefix}ea_document_taxes (
id BIGINT(20) NOT NULL AUTO_INCREMENT,
document_id BIGINT(20) UNSIGNED NOT NULL,
document_item_id BIGINT(20) UNSIGNED NOT NULL,
tax_id BIGINT(20) UNSIGNED NOT NULL,
name VARCHAR(191) NOT NULL,
rate DOUBLE(15, 4) NOT NULL,
compound TINYINT(1) NOT NULL DEFAULT 0,
amount DOUBLE(15, 4) NOT NULL DEFAULT 0.00,
currency VARCHAR(3) NOT NULL DEFAULT 'USD',
PRIMARY KEY (id),
KEY document_id (document_id),
KEY document_item_id (document_item_id),
KEY tax_id (tax_id)
) $collate;

CREATE TABLE {$wpdb->prefix}ea_documentmeta (
meta_id BIGINT(20) NOT NULL AUTO_INCREMENT,
ea_document_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
meta_key VARCHAR(191) DEFAULT NULL,
meta_value LONGTEXT,
PRIMARY KEY (meta_id),
KEY ea_document_id (ea_document_id),
KEY meta_key (meta_key(191))
) $collate;

CREATE TABLE {$wpdb->prefix}ea_documents (
id BIGINT(20) NOT NULL AUTO_INCREMENT,
type VARCHAR(20) NOT NULL DEFAULT 'invoice',
status VARCHAR(20) NOT NULL DEFAULT 'draft',
number VARCHAR(30) NOT NULL,
reference VARCHAR(191) DEFAULT NULL,
issue_date DATETIME  NOT NULL DEFAULT CURRENT_TIMESTAMP,
due_date DATETIME DEFAULT NULL,
sent_date DATETIME DEFAULT NULL,
payment_date DATETIME DEFAULT NULL,
discount_value DOUBLE(15, 4) DEFAULT 0,
discount_type ENUM('fixed', 'percentage') DEFAULT 'fixed',
subtotal DOUBLE(15, 4) DEFAULT 0,
discount DOUBLE(15, 4) DEFAULT 0,
tax DOUBLE(15, 4) DEFAULT 0,
total DOUBLE(15, 4) DEFAULT 0,
currency VARCHAR(3) NOT NULL DEFAULT 'USD',
exchange_rate DOUBLE(15, 8) NOT NULL DEFAULT 1.0,
contact_name VARCHAR(100) NOT NULL,
contact_company VARCHAR(100) NOT NULL,
contact_email VARCHAR(100) DEFAULT NULL,
contact_phone VARCHAR(50) DEFAULT NULL,
contact_address TEXT DEFAULT NULL,
contact_city VARCHAR(50) DEFAULT NULL,
contact_state VARCHAR(50) DEFAULT NULL,
contact_postcode VARCHAR(20) DEFAULT NULL,
contact_country VARCHAR(3) DEFAULT NULL,
contact_tax_number VARCHAR(50) DEFAULT NULL,
note TEXT DEFAULT NULL,
terms TEXT DEFAULT NULL,
attachment_id BIGINT(20) UNSIGNED DEFAULT NULL,
contact_id BIGINT(20) UNSIGNED NOT NULL,
parent_id BIGINT(20) UNSIGNED DEFAULT NULL,
author_id BIGINT(20) UNSIGNED DEFAULT NULL,
editable TINYINT(1) NOT NULL DEFAULT 1,
created_via VARCHAR(20) DEFAULT 'manual',
uuid VARCHAR(36) DEFAULT NULL,
date_created DATETIME  NOT NULL DEFAULT CURRENT_TIMESTAMP,
date_updated DATETIME DEFAULT NULL,
PRIMARY KEY (id),
UNIQUE KEY uuid (uuid),
KEY type (type),
KEY status (status),
KEY total (total),
KEY contact_id (contact_id),
KEY contact_name (contact_name),
KEY contact_email (contact_email),
KEY contact_phone (contact_phone),
KEY contact_city (contact_city)
) $collate;

CREATE TABLE {$wpdb->prefix}ea_items (
id BIGINT(20) NOT NULL AUTO_INCREMENT,
type VARCHAR(50) NOT NULL DEFAULT 'standard',
name VARCHAR(191) NOT NULL,
description TEXT DEFAULT NULL,
unit VARCHAR(50) DEFAULT NULL,
price DOUBLE(15, 4) NOT NULL,
cost DOUBLE(15, 4) NOT NULL,
tax_ids VARCHAR(191) DEFAULT NULL,
category_id INT(11) DEFAULT NULL,
created_via VARCHAR(20) DEFAULT 'manual',
date_created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
date_updated DATETIME DEFAULT NULL,
PRIMARY KEY (id),
KEY name (name),
KEY type (type),
KEY price (price),
KEY cost (cost)
) $collate;

CREATE TABLE {$wpdb->prefix}ea_itemmeta (
meta_id BIGINT(20) NOT NULL AUTO_INCREMENT,
ea_item_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
meta_key VARCHAR(191) DEFAULT NULL,
meta_value LONGTEXT,
PRIMARY KEY (meta_id),
KEY ea_item_id (ea_item_id),
KEY meta_key (meta_key(191))
) $collate;

CREATE TABLE {$wpdb->prefix}ea_notes (
id BIGINT(20) NOT NULL AUTO_INCREMENT,
parent_id BIGINT(20) UNSIGNED NOT NULL,
parent_type VARCHAR(20) NOT NULL,
content TEXT DEFAULT NULL,
author_id BIGINT(20) UNSIGNED DEFAULT NULL,
date_created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
date_updated DATETIME DEFAULT NULL,
PRIMARY KEY (id),
KEY parent_id (parent_id),
KEY parent_type (parent_type)
) $collate;

CREATE TABLE {$wpdb->prefix}ea_terms (
id BIGINT(20) NOT NULL AUTO_INCREMENT,
taxonomy VARCHAR(20) NOT NULL DEFAULT 'category',
name VARCHAR(191) NOT NULL,
description TEXT DEFAULT NULL,
type VARCHAR(20) DEFAULT NULL,
parent_id BIGINT(20) UNSIGNED DEFAULT NULL,
date_created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
date_updated DATETIME DEFAULT NULL,
PRIMARY KEY (id),
KEY name (name),
KEY type (type),
KEY taxonomy (taxonomy),
KEY parent_id (parent_id)
) $collate;

CREATE TABLE {$wpdb->prefix}ea_termmeta (
meta_id BIGINT(20) NOT NULL AUTO_INCREMENT,
ea_term_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
meta_key VARCHAR(191) DEFAULT NULL,
meta_value LONGTEXT,
PRIMARY KEY (meta_id),
KEY ea_term_id (ea_term_id),
KEY meta_key (meta_key(191))
) $collate;

CREATE TABLE {$wpdb->prefix}ea_transactionmeta (
meta_id BIGINT(20) NOT NULL AUTO_INCREMENT,
ea_transaction_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
meta_key VARCHAR(191) DEFAULT NULL,
meta_value LONGTEXT,
PRIMARY KEY (meta_id),
KEY ea_transaction_id (ea_transaction_id),
KEY meta_key (meta_key(191))
) $collate;

CREATE TABLE {$wpdb->prefix}ea_transactions (
id BIGINT(20) NOT NULL AUTO_INCREMENT,
type VARCHAR(20) DEFAULT NULL,
number VARCHAR(30) NOT NULL,
payment_date DATETIME  NOT NULL DEFAULT CURRENT_TIMESTAMP,
amount DOUBLE(15, 4) NOT NULL,
currency VARCHAR(3) NOT NULL DEFAULT 'USD',
exchange_rate DOUBLE(15, 8) NOT NULL DEFAULT 1.0,
reference VARCHAR(191) DEFAULT NULL,
note TEXT DEFAULT NULL,
payment_method VARCHAR(100) DEFAULT NULL,
account_id BIGINT(20) UNSIGNED NOT NULL,
contact_id BIGINT(20) UNSIGNED DEFAULT NULL,
document_id BIGINT(20) UNSIGNED DEFAULT NULL,
category_id BIGINT(20) UNSIGNED NOT NULL,
attachment_id BIGINT(20) UNSIGNED DEFAULT NULL,
author_id BIGINT(20) UNSIGNED DEFAULT NULL,
parent_id BIGINT(20) UNSIGNED DEFAULT NULL,
editable TINYINT(1) NOT NULL DEFAULT 1,
created_via VARCHAR(20) DEFAULT 'manual',
uuid VARCHAR(36) DEFAULT NULL,
date_created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
date_updated DATETIME DEFAULT NULL,
PRIMARY KEY (id),
UNIQUE KEY uuid (uuid),
KEY type (type),
KEY number (number),
KEY amount (amount),
KEY currency (currency),
KEY exchange_rate (exchange_rate),
KEY account_id (account_id),
KEY category_id (category_id),
KEY contact_id (contact_id)
) $collate;

CREATE TABLE {$wpdb->prefix}ea_transfers (
id BIGINT(20) NOT NULL AUTO_INCREMENT,
payment_id BIGINT(20) UNSIGNED NOT NULL,
expense_id BIGINT(20) UNSIGNED NOT NULL,
transfer_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
amount DOUBLE(15, 4) NOT NULL DEFAULT 0.00,
currency VARCHAR(3) NOT NULL DEFAULT 'USD',
payment_method VARCHAR(100) DEFAULT NULL,
reference VARCHAR(191) DEFAULT NULL,
note TEXT DEFAULT NULL,
date_created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
date_updated DATETIME DEFAULT NULL,
PRIMARY KEY (id),
KEY payment_id (payment_id),
KEY expense_id (expense_id)
) $collate;
";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $tables );
	}

	/**
	 * Create roles and capabilities.
	 *
	 * @since 1.1.6
	 * @return void
	 */
	public static function create_roles() {
		global $wp_roles;

		if ( ! class_exists( 'WP_Roles' ) ) {
			return;
		}

		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new \WP_Roles();
		}

		// Dummy gettext calls to get strings in the catalog.
		_x( 'Accounting Manager', 'User role', 'wp-ever-accounting' );
		_x( 'Accountant', 'User role', 'wp-ever-accounting' );

		// Accountant role.
		add_role(
			'eac_accountant',
			'Accountant',
			array(
				'manage_accounting'   => true,
				'eac_manage_customer' => true,
				'eac_manage_vendor'   => true,
				'eac_manage_account'  => true,
				'eac_manage_payment'  => true,
				'eac_manage_expense'  => true,
				'eac_manage_transfer' => true,
				'eac_manage_category' => true,
				'eac_manage_currency' => true,
				'eac_manage_item'     => true,
				'eac_manage_invoice'  => true,
				'eac_manage_bill'     => true,
				'eac_manage_tax'      => true,
				'read'                => true,
			)
		);

		// Accounting manager role.
		add_role(
			'eac_manager',
			'Accounting Manager',
			array(
				'manage_accounting'   => true,
				'eac_manage_report'   => true,
				'eac_manage_options'  => true,
				'eac_manage_product'  => true,
				'eac_manage_customer' => true,
				'eac_manage_vendor'   => true,
				'eac_manage_account'  => true,
				'eac_manage_payment'  => true,
				'eac_manage_expense'  => true,
				'eac_manage_transfer' => true,
				'eac_manage_category' => true,
				'eac_manage_currency' => true,
				'eac_manage_item'     => true,
				'eac_manage_invoice'  => true,
				'eac_manage_bill'     => true,
				'eac_manage_tax'      => true,
				'eac_manage_import'   => true,
				'eac_manage_export'   => true,
				'read'                => true,
			)
		);

		// add caps to admin.
		global $wp_roles;

		if ( is_object( $wp_roles ) ) {
			$wp_roles->add_cap( 'administrator', 'manage_accounting' );
			$wp_roles->add_cap( 'administrator', 'eac_manage_report' );
			$wp_roles->add_cap( 'administrator', 'eac_manage_options' );
			$wp_roles->add_cap( 'administrator', 'eac_manage_customer' );
			$wp_roles->add_cap( 'administrator', 'eac_manage_vendor' );
			$wp_roles->add_cap( 'administrator', 'eac_manage_account' );
			$wp_roles->add_cap( 'administrator', 'eac_manage_payment' );
			$wp_roles->add_cap( 'administrator', 'eac_manage_expense' );
			$wp_roles->add_cap( 'administrator', 'eac_manage_transfer' );
			$wp_roles->add_cap( 'administrator', 'eac_manage_category' );
			$wp_roles->add_cap( 'administrator', 'eac_manage_currency' );
			$wp_roles->add_cap( 'administrator', 'eac_manage_item' );
			$wp_roles->add_cap( 'administrator', 'eac_manage_invoice' );
			$wp_roles->add_cap( 'administrator', 'eac_manage_bill' );
			$wp_roles->add_cap( 'administrator', 'eac_manage_tax' );
			$wp_roles->add_cap( 'administrator', 'eac_manage_import' );
			$wp_roles->add_cap( 'administrator', 'eac_manage_export' );
		}
	}

	/**
	 * Save settings.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function save_settings() {
		$pages = Settings::get_pages();
		foreach ( $pages as $page ) {
			if ( ! is_subclass_of( $page, Admin\Settings\Page::class ) || ! method_exists( $page, 'get_sections' ) ) {
				continue;
			}

			$sections = array_unique( array_merge( array( '' ), array_keys( $page->get_sections() ) ) );
			foreach ( $sections as $section ) {
				$settings = $page->get_section_settings( $section );
				foreach ( $settings as $setting ) {
					if ( isset( $setting['default'] ) && isset( $setting['id'] ) ) {
						$autoload = isset( $setting['autoload'] ) ? (bool) $setting['autoload'] : true;
						add_option( $setting['id'], $setting['default'], '', ( $autoload ? 'yes' : 'no' ) );
					}
				}
			}
		}
	}

	/**
	 * Create cron jobs.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function create_cron_jobs() {
		// every hour.
		if ( ! wp_next_scheduled( 'eac_hourly_event' ) ) {
			wp_schedule_event( time(), 'hourly', 'eac_hourly_event' );
		}
	}

	/**
	 * Uninstall the plugin.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function uninstall() {
		global $wpdb;
		if ( ! is_blog_installed() ) {
			return;
		}

		// Remove roles.
		remove_role( 'eac_accountant' );
		remove_role( 'eac_manager' );

		// Remove options.
		$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'eac\_%';" );

		// Clear cron jobs.
		wp_clear_scheduled_hook( 'eac_hourly_event' );
		wp_clear_scheduled_hook( 'eac_daily_event' );
		wp_clear_scheduled_hook( 'eac_weekly_event' );

		// Drop tables.
		$tables = array(
			'ea_accounts',
			'ea_contactmeta',
			'ea_contacts',
			'ea_document_items',
			'ea_document_taxes',
			'ea_documentmeta',
			'ea_documents',
			'ea_items',
			'ea_itemmeta',
			'ea_notes',
			'ea_terms',
			'ea_termmeta',
			'ea_transactionmeta',
			'ea_transactions',
			'ea_transfers',
		);

		foreach ( $tables as $table ) {
			$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}{$table};" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		}
	}
}
