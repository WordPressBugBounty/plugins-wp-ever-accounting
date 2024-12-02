<?php

namespace EverAccounting;

use EverAccounting\Controllers\Accounts;
use EverAccounting\Controllers\Bills;
use EverAccounting\Controllers\Business;
use EverAccounting\Controllers\Categories;
use EverAccounting\Controllers\Currencies;
use EverAccounting\Controllers\Customers;
use EverAccounting\Controllers\Expenses;
use EverAccounting\Controllers\Invoices;
use EverAccounting\Controllers\Items;
use EverAccounting\Controllers\Notes;
use EverAccounting\Controllers\Payments;
use EverAccounting\Controllers\Taxes;
use EverAccounting\Controllers\Terms;
use EverAccounting\Controllers\Transfers;
use EverAccounting\Controllers\Vendors;

/**
 * Class Plugin.
 *
 * @since 1.0.0
 * @author  Sultan Nasir Uddin <manikdrmc@gmail.com>
 * @package EverAccounting
 *
 * @property Accounts   $accounts Accounts controller.
 * @property Bills      $bills Bills controller.
 * @property Business   $business Business controller.
 * @property Categories $categories Categories controller.
 * @property Currencies $currencies Currencies controller.
 * @property Customers  $customers Customers controller.
 * @property Expenses   $expenses Expenses controller.
 * @property Invoices   $invoices Invoices controller.
 * @property Items      $items Items controller.
 * @property Notes      $notes Notes controller.
 * @property Payments   $payments Payments controller.
 * @property Taxes      $taxes Taxes controller.
 * @property Terms      $terms Terms controller.
 * @property Transfers  $transfers Transfers controller.
 * @property Vendors    $vendors Vendors controller.
 */
class Plugin extends \EverAccounting\ByteKit\Plugin {
	/**
	 * Plugin constructor.
	 *
	 * @param array $data The plugin data.
	 *
	 * @since 1.0.0
	 */
	protected function __construct( $data ) {
		$data = array_merge(
			$data,
			array(
				'prefix'       => 'eac',
				'settings_url' => admin_url( 'admin.php?page=eac-settings' ),
			),
		);

		parent::__construct( $data );
		$this->define_constants();
		$this->includes();
		$this->init_hooks();
	}

	/**
	 * Define constants.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function define_constants() {
		$upload_dir = wp_upload_dir( null, false );

		define( 'EAC_VERSION', $this->get_version() );
		define( 'EAC_PLUGIN_FILE', $this->get_file() );
		define( 'EAC_PLUGIN_BASENAME', $this->get_basename() );
		define( 'EAC_PLUGIN_PATH', $this->get_dir_path() . '/' );
		define( 'EAC_PLUGIN_URL', $this->get_dir_url() . '/' );
		define( 'EAC_ADMIN_PATH', $this->get_dir_path() . '/admin/' );
		define( 'EAC_UPLOADS_BASEDIR', $upload_dir['basedir'] . '/eac/' );
		define( 'EAC_UPLOADS_DIR', $upload_dir['basedir'] . '/eac/' );
		define( 'EAC_UPLOADS_URL', $upload_dir['baseurl'] . '/eac/' );
		define( 'EAC_LOG_DIR', $upload_dir['basedir'] . '/eac-logs/' );
		define( 'EAC_ASSETS_URL', $this->get_assets_url() . '/' );
		define( 'EAC_ASSETS_DIR', $this->get_assets_path() . '/' );
		define( 'EAC_TEMPLATES_DIR', $this->get_template_path() . '/' );
	}

	/**
	 * Include required files.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function includes() {
		require_once __DIR__ . '/functions.php';
		require_once dirname( __DIR__ ) . '/vendor/woocommerce/action-scheduler/action-scheduler.php';
	}

	/**
	 * Hook into actions and filters.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init_hooks() {
		register_activation_hook( $this->get_file(), array( Installer::class, 'install' ) );
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ), -1 );
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Run on plugins loaded.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function plugins_loaded() {
		$this->services->add( 'installer', new Installer() );

		$controllers = array(
			'EverAccounting\Controllers\Accounts',
			'EverAccounting\Controllers\Bills',
			'EverAccounting\Controllers\Business',
			'EverAccounting\Controllers\Categories',
			'EverAccounting\Controllers\Currencies',
			'EverAccounting\Controllers\Customers',
			'EverAccounting\Controllers\Expenses',
			'EverAccounting\Controllers\Invoices',
			'EverAccounting\Controllers\Items',
			'EverAccounting\Controllers\Notes',
			'EverAccounting\Controllers\Payments',
			'EverAccounting\Controllers\Taxes',
			'EverAccounting\Controllers\Transfers',
			'EverAccounting\Controllers\Terms',
			'EverAccounting\Controllers\Vendors',
		);

		foreach ( $controllers as $controller ) {
			$controller_name = substr( $controller, strrpos( $controller, '\\' ) + 1 );
			$this->services->add( strtolower( $controller_name ), $controller );
		}

		$handlers = array(
			'EverAccounting\Actions',
			'EverAccounting\Currencies',
			'EverAccounting\Contacts',
			'EverAccounting\Crons',
			'EverAccounting\Documents',
			'EverAccounting\Banking',
			'EverAccounting\Shortcodes',
			'EverAccounting\Transactions',
			'EverAccounting\Transfers',
			'EverAccounting\Caches',
			'EverAccounting\Frontend\Frontend',
			'EverAccounting\Frontend\Rewrites',
		);
		foreach ( $handlers as $handler ) {
			$this->services->add( $handler );
		}

		if ( is_admin() ) {
			$handles = array(
				'EverAccounting\Admin\Admin',
				'EverAccounting\Admin\Menus',
				'EverAccounting\Admin\Scripts',
				'EverAccounting\Admin\Ajax',
				'EverAccounting\Admin\Dashboard',
				'EverAccounting\Admin\Items',
				'EverAccounting\Admin\Payments',
				'EverAccounting\Admin\Invoices',
				'EverAccounting\Admin\Customers',
				'EverAccounting\Admin\Expenses',
				'EverAccounting\Admin\Importers',
				'EverAccounting\Admin\Exporters',
				'EverAccounting\Admin\Bills',
				'EverAccounting\Admin\Vendors',
				'EverAccounting\Admin\Accounts',
				'EverAccounting\Admin\Transfers',
				'EverAccounting\Admin\Reports',
				'EverAccounting\Admin\Settings',
				'EverAccounting\Admin\Currencies',
				'EverAccounting\Admin\Taxes',
				'EverAccounting\Admin\Categories',
				'EverAccounting\Admin\Extensions',
				// 'EverAccounting\Admin\Setup',
			);
			foreach ( $handles as $handle ) {
				$this->services->add( $handle );
			}
		}

		/**
		 * Fires when the plugin is initialized.
		 *
		 * @since 1.0.0
		 */
		do_action( 'eac_init' );
	}

	/**
	 * Register REST routes.
	 *
	 * @since 1.6.1
	 */
	public function register_routes() {
		$handlers = apply_filters(
			'eac_rest_handlers',
			array(
				'EverAccounting\API\Items',
				'EverAccounting\API\Taxes',
				'EverAccounting\API\Categories',
				'EverAccounting\API\Currencies',
				'EverAccounting\API\Customers',
				'EverAccounting\API\Vendors',
				'EverAccounting\API\Customers',
				'EverAccounting\API\Accounts',
				'EverAccounting\API\Notes',
				'EverAccounting\API\Expenses',
				'EverAccounting\API\Payments',
				'EverAccounting\API\Utilities',
				'EverAccounting\API\Invoices',
				'EverAccounting\API\Bills',
			)
		);
		foreach ( $handlers as $controller ) {
			if ( class_exists( $controller ) ) {
				$this->$controller = new $controller();
				$this->$controller->register_routes();
			}
		}
	}

	/**
	 * Get queue instance.
	 *
	 * @since 1.0.0
	 * @return \EverAccounting\Core\Queue
	 */
	public function queue() {
		return Core\Queue::instance();
	}
}
