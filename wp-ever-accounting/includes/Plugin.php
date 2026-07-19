<?php
/**
 * Plugin main class file.
 *
 * @package EverAccounting
 */

namespace EverAccounting;

defined( 'ABSPATH' ) || exit;

/**
 * Class Plugin.
 *
 * @since 1.0.0
 * @author  Sultan Nasir Uddin <manikdrmc@gmail.com>
 * @package EverAccounting
 */
class Plugin extends B8\App {

	/**
	 * Components to register.
	 *
	 * @since 2.2.8
	 * @var array<int|string, class-string>
	 */

	protected array $components = array(
		'accounts'   => \EverAccounting\Controllers\Accounts::class,
		'bills'      => \EverAccounting\Controllers\Bills::class,
		\EverAccounting\Controllers\Business::class,
		'categories' => \EverAccounting\Controllers\Categories::class,
		'currencies' => \EverAccounting\Controllers\Currencies::class,
		'customers'  => \EverAccounting\Controllers\Customers::class,
		'expenses'   => \EverAccounting\Controllers\Expenses::class,
		'invoices'   => \EverAccounting\Controllers\Invoices::class,
		'items'      => \EverAccounting\Controllers\Items::class,
		'notes'      => \EverAccounting\Controllers\Notes::class,
		'payments'   => \EverAccounting\Controllers\Payments::class,
		'taxes'      => \EverAccounting\Controllers\Taxes::class,
		'transfers'  => \EverAccounting\Controllers\Transfers::class,
		'terms'      => \EverAccounting\Controllers\Terms::class,
		'vendors'    => \EverAccounting\Controllers\Vendors::class,

		\EverAccounting\Currencies::class,
		\EverAccounting\Contacts::class,
		\EverAccounting\Crons::class,
		\EverAccounting\Documents::class,
		\EverAccounting\Banking::class,
		\EverAccounting\Shortcodes::class,
		\EverAccounting\Transactions::class,
		\EverAccounting\Transfers::class,
		\EverAccounting\Caches::class,
		\EverAccounting\Frontend\Frontend::class,
		\EverAccounting\Frontend\Rewrites::class,

		\EverAccounting\Admin\Admin::class,
		\EverAccounting\Admin\Menus::class,
		\EverAccounting\Admin\Scripts::class,
		\EverAccounting\Admin\Ajax::class,
		\EverAccounting\Admin\Dashboard::class,
		\EverAccounting\Admin\Items::class,
		\EverAccounting\Admin\Payments::class,
		\EverAccounting\Admin\Invoices::class,
		\EverAccounting\Admin\Customers::class,
		\EverAccounting\Admin\Expenses::class,
		\EverAccounting\Admin\Importers::class,
		\EverAccounting\Admin\Exporters::class,
		\EverAccounting\Admin\Bills::class,
		\EverAccounting\Admin\Vendors::class,
		\EverAccounting\Admin\Accounts::class,
		\EverAccounting\Admin\Transfers::class,
		\EverAccounting\Admin\Reports::class,
		\EverAccounting\Admin\Settings::class,
		\EverAccounting\Admin\Currencies::class,
		\EverAccounting\Admin\Taxes::class,
		\EverAccounting\Admin\Categories::class,
		// EverAccounting\Admin\Setup::class,
		\EverAccounting\Admin\Notices::class,
		\EverAccounting\Admin\Changelog::class,
	);

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function bootstrap(): void {
		define( 'EAC_VERSION', $this->version );
		define( 'EAC_PLUGIN_FILE', $this->file );
		define( 'EAC_PLUGIN_BASENAME', $this->basename() );
		define( 'EAC_PLUGIN_PATH', $this->plugin_path() . '/' );
		define( 'EAC_PLUGIN_URL', $this->plugin_url() . '/' );
		define( 'EAC_ADMIN_PATH', $this->plugin_path() . '/admin/' );

		$upload_dir = wp_upload_dir( null, false );
		define( 'EAC_UPLOADS_BASEDIR', $upload_dir['basedir'] . '/eac/' );
		define( 'EAC_UPLOADS_DIR', $upload_dir['basedir'] . '/eac/' );
		define( 'EAC_UPLOADS_URL', $upload_dir['baseurl'] . '/eac/' );
		define( 'EAC_LOG_DIR', $upload_dir['basedir'] . '/eac-logs/' );
		define( 'EAC_ASSETS_URL', $this->assets_url() . '/' );
		define( 'EAC_ASSETS_DIR', $this->assets_path() . '/' );
		define( 'EAC_TEMPLATES_DIR', $this->templates_path() . '/' );

		// Load action-scheduler.
		require_once dirname( __DIR__ ) . '/vendor/woocommerce/action-scheduler/action-scheduler.php';

		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ), -1 );
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
		add_filter( 'plugin_action_links_' . $this->basename(), array( $this, 'plugin_action_links' ) );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
	}

	/**
	 * Boot the plugin components.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function plugins_loaded(): void {
		$this->boot( $this->components );

		/**
		 * Fires when the plugin is initialized.
		 *
		 * @since 1.0.0
		 */
		do_action( 'eac_init' );

		$this->do_action( 'loaded' );
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
				$this->$controller = $this->make( $controller );
				$this->$controller->register_routes();
			}
		}
	}

	/**
	 * Add plugin action links.
	 *
	 * @since 1.0.0
	 * @param array<string, string> $links Plugin action links.
	 * @return array<string, string>
	 */
	public function plugin_action_links( array $links ): array {
		$settings = sprintf(
			'<a href="%s">%s</a>',
			esc_url( (string) $this->get( 'settings_url' ) ),
			esc_html__( 'Settings', 'wp-ever-accounting' )
		);

		return array_merge( array( 'settings' => $settings ), $links );
	}

	/**
	 * Add the plugin row meta links.
	 *
	 * @since 1.0.0
	 * @param array<int, string> $links Plugin row meta links.
	 * @param string             $file  Plugin file path relative to the plugins directory.
	 * @return array<int, string>
	 */
	public function plugin_row_meta( array $links, string $file ): array {
		if ( $file !== $this->basename() ) {
			return $links;
		}

		$links[] = sprintf(
			'<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
			esc_url( (string) $this->get( 'review_url' ) ),
			esc_html__( 'Review', 'wp-ever-accounting' )
		);

		return $links;
	}

	/**
	 * Get queue instance.
	 *
	 * @since 1.0.0
	 * @return \EverAccounting\Foundation\Queue
	 */
	public function queue() {
		return Foundation\Queue::instance();
	}
}
