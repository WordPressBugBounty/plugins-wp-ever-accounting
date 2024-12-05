<?php

namespace EverAccounting\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Exporters class.
 *
 * @since 1.0.0
 * @package EverAccounting\Admin
 */
class Exporters {

	/**
	 * Exporter constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_filter( 'eac_tools_page_tabs', array( __CLASS__, 'register_tabs' ), - 1 );
		add_action( 'eac_action_download_export_file', array( __CLASS__, 'handle_csv_download' ) );
		add_action( 'eac_tools_page_export_content', array( __CLASS__, 'customers_export' ) );
		add_action( 'eac_tools_page_export_content', array( __CLASS__, 'vendors_export' ) );
		add_action( 'eac_tools_page_export_content', array( __CLASS__, 'categories_export' ) );
		add_action( 'eac_tools_page_export_content', array( __CLASS__, 'taxes_export' ) );
		add_action( 'eac_tools_page_export_content', array( __CLASS__, 'items_export' ) );
		add_action( 'eac_tools_page_export_content', array( __CLASS__, 'accounts_export' ) );
		add_action( 'eac_tools_page_export_content', array( __CLASS__, 'transfers_export' ) );
		add_action( 'eac_tools_page_export_content', array( __CLASS__, 'payments_export' ) );
		add_action( 'eac_tools_page_export_content', array( __CLASS__, 'expenses_export' ) );
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
		if ( current_user_can( 'eac_manage_export' ) ) {  // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Reason: This is a custom capability.
			$tabs['export'] = __( 'Export', 'wp-ever-accounting' );
		}

		return $tabs;
	}


	/**
	 * Handle CSV download.
	 *
	 * @param array $posted Posted data.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function handle_csv_download( $posted ) {
		check_admin_referer( 'eac_download_file' );
		$type     = isset( $posted['type'] ) ? sanitize_key( wp_unslash( $posted['type'] ) ) : '';
		$filename = isset( $posted['filename'] ) ? sanitize_text_field( wp_unslash( $posted['filename'] ) ) : '';
		$exporter = self::get_exporter( $type );
		if ( ! $exporter || ! is_subclass_of( $exporter, Exporters\Exporter::class ) ) {
			wp_die( esc_html__( 'Invalid export type.', 'wp-ever-accounting' ) );
		}
		$exporter = new $exporter();
		if ( ! $exporter->can_export() ) {
			wp_die( esc_html__( 'You do not have permission to export.', 'wp-ever-accounting' ) );
		}

		if ( ! empty( $filename ) ) {
			$exporter->set_filename( $filename );
		}

		$exporter->export();
		exit;
	}

	/**
	 * Export customers.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function customers_export() {
		?>
		<div class="eac-card">
			<div class="eac-card__header">
				<h2 class="eac-card__title"><?php esc_html_e( 'Export Customers', 'wp-ever-accounting' ); ?></h2>
			</div>
			<div class="eac-card__body">
				<form method="post" class="eac_exporter" data-type="customers" data-nonce="<?php echo esc_attr( wp_create_nonce( 'eac_ajax_export' ) ); ?>">
					<p><?php esc_html_e( 'Export customers from this site as CSV file. Exported file can be imported into other site.', 'wp-ever-accounting' ); ?></p>
					<?php submit_button( esc_html__( 'Export', 'wp-ever-accounting' ), 'secondary', null, false ); ?>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * Export vendors.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function vendors_export() {
		?>
		<div class="eac-card">
			<div class="eac-card__header">
				<h2 class="eac-card__title"><?php esc_html_e( 'Export Vendors', 'wp-ever-accounting' ); ?></h2>
			</div>
			<div class="eac-card__body">
				<form method="post" class="eac_exporter" data-type="vendors" data-nonce="<?php echo esc_attr( wp_create_nonce( 'eac_ajax_export' ) ); ?>">
					<p><?php esc_html_e( 'Export vendors from this site as CSV file. Exported file can be imported into other site.', 'wp-ever-accounting' ); ?></p>
					<?php submit_button( esc_html__( 'Export', 'wp-ever-accounting' ), 'secondary', null, false ); ?>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * Export categories.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function categories_export() {
		?>
		<div class="eac-card">
			<div class="eac-card__header">
				<h2 class="eac-card__title"><?php esc_html_e( 'Export Categories', 'wp-ever-accounting' ); ?></h2>
			</div>
			<div class="eac-card__body">
				<form method="post" class="eac_exporter" data-type="categories" data-nonce="<?php echo esc_attr( wp_create_nonce( 'eac_ajax_export' ) ); ?>">
					<p><?php esc_html_e( 'Export categories from this site as CSV file. Exported file can be imported into other site.', 'wp-ever-accounting' ); ?></p>
					<?php submit_button( esc_html__( 'Export', 'wp-ever-accounting' ), 'secondary', null, false ); ?>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * Export taxes.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function taxes_export() {
		?>
		<div class="eac-card">
			<div class="eac-card__header">
				<h2 class="eac-card__title"><?php esc_html_e( 'Export Taxes', 'wp-ever-accounting' ); ?></h2>
			</div>
			<div class="eac-card__body">
				<form method="post" class="eac_exporter" data-type="taxes" data-nonce="<?php echo esc_attr( wp_create_nonce( 'eac_ajax_export' ) ); ?>">
					<p><?php esc_html_e( 'Export taxes from this site as CSV file. Exported file can be imported into other site.', 'wp-ever-accounting' ); ?></p>
					<?php submit_button( esc_html__( 'Export', 'wp-ever-accounting' ), 'secondary', null, false ); ?>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * Export items.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function items_export() {
		?>
		<div class="eac-card">
			<div class="eac-card__header">
				<h2 class="eac-card__title"><?php esc_html_e( 'Export Items', 'wp-ever-accounting' ); ?></h2>
			</div>
			<div class="eac-card__body">
				<form method="post" class="eac_exporter" data-type="items" data-nonce="<?php echo esc_attr( wp_create_nonce( 'eac_ajax_export' ) ); ?>">
					<p><?php esc_html_e( 'Export items from this site as CSV file. Exported file can be imported into other site.', 'wp-ever-accounting' ); ?></p>
					<?php submit_button( esc_html__( 'Export', 'wp-ever-accounting' ), 'secondary', null, false ); ?>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * Export accounts.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function accounts_export() {
		?>
		<div class="eac-card">
			<div class="eac-card__header">
				<h2 class="eac-card__title"><?php esc_html_e( 'Export Accounts', 'wp-ever-accounting' ); ?></h2>
			</div>
			<div class="eac-card__body">
				<form method="post" class="eac_exporter" data-type="accounts" data-nonce="<?php echo esc_attr( wp_create_nonce( 'eac_ajax_export' ) ); ?>">
					<p><?php esc_html_e( 'Export accounts from this site as CSV file. Exported file can be imported into other site.', 'wp-ever-accounting' ); ?></p>
					<?php submit_button( esc_html__( 'Export', 'wp-ever-accounting' ), 'secondary', null, false ); ?>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * Export transfers.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function transfers_export() {
		?>
		<div class="eac-card">
			<div class="eac-card__header">
				<h2 class="eac-card__title"><?php esc_html_e( 'Export Transfers', 'wp-ever-accounting' ); ?></h2>
			</div>
			<div class="eac-card__body">
				<form method="post" class="eac_exporter" data-type="transfers" data-nonce="<?php echo esc_attr( wp_create_nonce( 'eac_ajax_export' ) ); ?>">
					<p><?php esc_html_e( 'Export transfers from this site as CSV file. Exported file can be imported into other site.', 'wp-ever-accounting' ); ?></p>
					<?php submit_button( esc_html__( 'Export', 'wp-ever-accounting' ), 'secondary', null, false ); ?>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * Export payments.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function payments_export() {
		?>
		<div class="eac-card">
			<div class="eac-card__header">
				<h2 class="eac-card__title"><?php esc_html_e( 'Export Payments', 'wp-ever-accounting' ); ?></h2>
			</div>
			<div class="eac-card__body">
				<form method="post" class="eac_exporter" data-type="payments" data-nonce="<?php echo esc_attr( wp_create_nonce( 'eac_ajax_export' ) ); ?>">
					<p><?php esc_html_e( 'Export payments from this site as CSV file. Exported file can be imported into other site.', 'wp-ever-accounting' ); ?></p>
					<?php submit_button( esc_html__( 'Export', 'wp-ever-accounting' ), 'secondary', null, false ); ?>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * Export expenses.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function expenses_export() {
		?>
		<div class="eac-card">
			<div class="eac-card__header">
				<h2 class="eac-card__title"><?php esc_html_e( 'Export Expenses', 'wp-ever-accounting' ); ?></h2>
			</div>
			<div class="eac-card__body">
				<form method="post" class="eac_exporter" data-type="expenses" data-nonce="<?php echo esc_attr( wp_create_nonce( 'eac_ajax_export' ) ); ?>">
					<p><?php esc_html_e( 'Export expenses from this site as CSV file. Exported file can be imported into other site.', 'wp-ever-accounting' ); ?></p>
					<?php submit_button( esc_html__( 'Export', 'wp-ever-accounting' ), 'secondary', null, false ); ?>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * Get exporter class.
	 *
	 * @param string $type Exporter type.
	 *
	 * @since 1.0.0
	 * @return Exporters\Exporter|null Exporter class.
	 */
	public static function get_exporter( $type ) {
		switch ( $type ) {
			case 'customers':
				$exporter = Exporters\Customers::class;
				break;
			case 'vendors':
				$exporter = Exporters\Vendors::class;
				break;
			case 'categories':
				$exporter = Exporters\Categories::class;
				break;
			case 'taxes':
				$exporter = Exporters\Taxes::class;
				break;
			case 'items':
				$exporter = Exporters\Items::class;
				break;
			case 'accounts':
				$exporter = Exporters\Accounts::class;
				break;
			case 'transfers':
				$exporter = Exporters\Transfers::class;
				break;
			case 'expenses':
				$exporter = Exporters\Expenses::class;
				break;
			case 'payments':
				$exporter = Exporters\Payments::class;
				break;
			default:
				/**
				 * Filter the export type.
				 *
				 * @since 1.0.2
				 */
				$exporter = apply_filters( "eac_ajax_{$type}_exporter", null );
		}

		return $exporter;
	}
}
