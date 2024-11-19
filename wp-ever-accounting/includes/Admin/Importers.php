<?php

namespace EverAccounting\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Importers class.
 *
 * @since 1.0.0
 */
class Importers {

	/**
	 * Importer constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_filter( 'eac_tools_page_tabs', array( __CLASS__, 'register_tabs' ), - 1 );
		add_action( 'eac_tools_page_import_content', array( __CLASS__, 'customers_import' ) );
		add_action( 'eac_tools_page_import_content', array( __CLASS__, 'vendors_import' ) );
		add_action( 'eac_tools_page_import_content', array( __CLASS__, 'categories_import' ) );
		add_action( 'eac_tools_page_import_content', array( __CLASS__, 'taxes_import' ) );
		add_action( 'eac_tools_page_import_content', array( __CLASS__, 'items_import' ) );
		add_action( 'eac_tools_page_import_content', array( __CLASS__, 'accounts_import' ) );
		add_action( 'eac_tools_page_import_content', array( __CLASS__, 'transfers_import' ) );
		add_action( 'eac_tools_page_import_content', array( __CLASS__, 'payments_import' ) );
		add_action( 'eac_tools_page_import_content', array( __CLASS__, 'expenses_import' ) );
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
		if ( current_user_can( 'manage_options' ) ) {
			$tabs['import'] = __( 'Import', 'wp-ever-accounting' );
		}

		return $tabs;
	}


	/**
	 * Export customers.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function customers_import() {
		?>
		<div class="eac-card">
			<div class="eac-card__header">
				<h2 class="eac-card__title"><?php esc_html_e( 'Import Customers', 'wp-ever-accounting' ); ?></h2>
			</div>
			<div class="eac-card__body">
				<form method="post" enctype="multipart/form-data" class="eac_importer" data-type="customers" data-nonce="<?php echo esc_attr( wp_create_nonce( 'eac_ajax_import' ) ); ?>">
					<p>
						<?php
						printf(
							/* translators: %s: import type */
							esc_html__( 'Import customers from CSV file. Download a %1$s sample file %2$s to learn how to format the CSV file.', 'wp-ever-accounting' ),
							'<a href="' . esc_url( EAC()->get_dir_url( 'samples/import/customers.csv' ) ) . '" download>',
							'</a>'
						);
						?>
					</p>
					<div class="eac-form-field">
						<label for="file"><?php esc_html_e( 'Select file', 'wp-ever-accounting' ); ?></label>
						<input type="file" name="file" id="file" accept="text/csv" required>
					</div>
					<?php submit_button( esc_html__( 'Import', 'wp-ever-accounting' ), 'secondary', null, false ); ?>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * Export categories.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function categories_import() {
		?>
		<div class="eac-card">
			<div class="eac-card__header">
				<h2 class="eac-card__title"><?php esc_html_e( 'Import Categories', 'wp-ever-accounting' ); ?></h2>
			</div>
			<div class="eac-card__body">
				<form method="post" enctype="multipart/form-data" class="eac_importer" data-type="categories" data-nonce="<?php echo esc_attr( wp_create_nonce( 'eac_ajax_import' ) ); ?>">
					<p>
						<?php
						printf(
						/* translators: %s: import type */
							esc_html__( 'Import categories from CSV file. Download a %1$s sample file %2$s to learn how to format the CSV file.', 'wp-ever-accounting' ),
							'<a href="' . esc_url( EAC()->get_dir_url( 'samples/import/categories.csv' ) ) . '" download>',
							'</a>'
						);
						?>
					</p>
					<div class="eac-form-field">
						<label for="file"><?php esc_html_e( 'Select file', 'wp-ever-accounting' ); ?></label>
						<input type="file" name="file" id="file" accept="text/csv" required>
					</div>
					<?php submit_button( esc_html__( 'Import', 'wp-ever-accounting' ), 'secondary', null, false ); ?>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * Export vendors.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function vendors_import() {
		?>
		<div class="eac-card">
			<div class="eac-card__header">
				<h2 class="eac-card__title"><?php esc_html_e( 'Import Vendors', 'wp-ever-accounting' ); ?></h2>
			</div>
			<div class="eac-card__body">
				<form method="post" enctype="multipart/form-data" class="eac_importer" data-type="vendors" data-nonce="<?php echo esc_attr( wp_create_nonce( 'eac_ajax_import' ) ); ?>">
					<p>
						<?php
						printf(
						/* translators: %s: import type */
							esc_html__( 'Import vendors from CSV file. Download a %1$s sample file %2$s to learn how to format the CSV file.', 'wp-ever-accounting' ),
							'<a href="' . esc_url( EAC()->get_dir_url( 'samples/import/vendors.csv' ) ) . '" download>',
							'</a>'
						);
						?>
					</p>
					<div class="eac-form-field">
						<label for="file"><?php esc_html_e( 'Select file', 'wp-ever-accounting' ); ?></label>
						<input type="file" name="file" id="file" accept="text/csv" required>
					</div>
					<?php submit_button( esc_html__( 'Import', 'wp-ever-accounting' ), 'secondary', null, false ); ?>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * Export taxes.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function taxes_import() {
		?>
		<div class="eac-card">
			<div class="eac-card__header">
				<h2 class="eac-card__title"><?php esc_html_e( 'Import Taxes', 'wp-ever-accounting' ); ?></h2>
			</div>
			<div class="eac-card__body">
				<form method="post" enctype="multipart/form-data" class="eac_importer" data-type="taxes" data-nonce="<?php echo esc_attr( wp_create_nonce( 'eac_ajax_import' ) ); ?>">
					<p>
						<?php
						printf(
						/* translators: %s: import type */
							esc_html__( 'Import taxes from CSV file. Download a %1$s sample file %2$s to learn how to format the CSV file.', 'wp-ever-accounting' ),
							'<a href="' . esc_url( EAC()->get_dir_url( 'samples/import/taxes.csv' ) ) . '" download>',
							'</a>'
						);
						?>
					</p>
					<div class="eac-form-field">
						<label for="file"><?php esc_html_e( 'Select file', 'wp-ever-accounting' ); ?></label>
						<input type="file" name="file" id="file" accept="text/csv" required>
					</div>
					<?php submit_button( esc_html__( 'Import', 'wp-ever-accounting' ), 'secondary', null, false ); ?>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * Export items.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function items_import() {
		?>
		<div class="eac-card">
			<div class="eac-card__header">
				<h2 class="eac-card__title"><?php esc_html_e( 'Import Items', 'wp-ever-accounting' ); ?></h2>
			</div>
			<div class="eac-card__body">
				<form method="post" enctype="multipart/form-data" class="eac_importer" data-type="items" data-nonce="<?php echo esc_attr( wp_create_nonce( 'eac_ajax_import' ) ); ?>">
					<p>
						<?php
						printf(
						/* translators: %s: import type */
							esc_html__( 'Import items from CSV file. Download a %1$s sample file %2$s to learn how to format the CSV file.', 'wp-ever-accounting' ),
							'<a href="' . esc_url( EAC()->get_dir_url( 'samples/import/items.csv' ) ) . '" download>',
							'</a>'
						);
						?>
					</p>
					<div class="eac-form-field">
						<label for="file"><?php esc_html_e( 'Select file', 'wp-ever-accounting' ); ?></label>
						<input type="file" name="file" id="file" accept="text/csv" required>
					</div>
					<?php submit_button( esc_html__( 'Import', 'wp-ever-accounting' ), 'secondary', null, false ); ?>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * Export accounts.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function accounts_import() {
		?>
		<div class="eac-card">
			<div class="eac-card__header">
				<h2 class="eac-card__title"><?php esc_html_e( 'Import Accounts', 'wp-ever-accounting' ); ?></h2>
			</div>
			<div class="eac-card__body">
				<form method="post" enctype="multipart/form-data" class="eac_importer" data-type="accounts" data-nonce="<?php echo esc_attr( wp_create_nonce( 'eac_ajax_import' ) ); ?>">
					<p>
						<?php
						printf(
						/* translators: %s: import type */
							esc_html__( 'Import accounts from CSV file. Download a %1$s sample file %2$s to learn how to format the CSV file.', 'wp-ever-accounting' ),
							'<a href="' . esc_url( EAC()->get_dir_url( 'samples/import/accounts.csv' ) ) . '" download>',
							'</a>'
						);
						?>
					</p>
					<div class="eac-form-field">
						<label for="file"><?php esc_html_e( 'Select file', 'wp-ever-accounting' ); ?></label>
						<input type="file" name="file" id="file" accept="text/csv" required>
					</div>
					<?php submit_button( esc_html__( 'Import', 'wp-ever-accounting' ), 'secondary', null, false ); ?>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * Export transfers.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function transfers_import() {
		?>
		<div class="eac-card">
			<div class="eac-card__header">
				<h2 class="eac-card__title"><?php esc_html_e( 'Import Transfers', 'wp-ever-accounting' ); ?></h2>
			</div>
			<div class="eac-card__body">
				<form method="post" enctype="multipart/form-data" class="eac_importer" data-type="transfers" data-nonce="<?php echo esc_attr( wp_create_nonce( 'eac_ajax_import' ) ); ?>">
					<p>
						<?php
						printf(
						/* translators: %s: import type */
							esc_html__( 'Import transfers from CSV file. Download a %1$s sample file %2$s to learn how to format the CSV file.', 'wp-ever-accounting' ),
							'<a href="' . esc_url( EAC()->get_dir_url( 'samples/import/transfers.csv' ) ) . '" download>',
							'</a>'
						);
						?>
					</p>
					<div class="eac-form-field">
						<label for="file"><?php esc_html_e( 'Select file', 'wp-ever-accounting' ); ?></label>
						<input type="file" name="file" id="file" accept="text/csv" required>
					</div>
					<?php submit_button( esc_html__( 'Import', 'wp-ever-accounting' ), 'secondary', null, false ); ?>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * Export expenses.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function expenses_import() {
		?>
		<div class="eac-card">
			<div class="eac-card__header">
				<h2 class="eac-card__title"><?php esc_html_e( 'Import Expenses', 'wp-ever-accounting' ); ?></h2>
			</div>
			<div class="eac-card__body">
				<form method="post" enctype="multipart/form-data" class="eac_importer" data-type="expenses" data-nonce="<?php echo esc_attr( wp_create_nonce( 'eac_ajax_import' ) ); ?>">
					<p>
						<?php
						printf(
						/* translators: %s: import type */
							esc_html__( 'Import expenses from CSV file. Download a %1$s sample file %2$s to learn how to format the CSV file.', 'wp-ever-accounting' ),
							'<a href="' . esc_url( EAC()->get_dir_url( 'samples/import/expenses.csv' ) ) . '" download>',
							'</a>'
						);
						?>
					</p>
					<div class="eac-form-field">
						<label for="file"><?php esc_html_e( 'Select file', 'wp-ever-accounting' ); ?></label>
						<input type="file" name="file" id="file" accept="text/csv" required>
					</div>
					<?php submit_button( esc_html__( 'Import', 'wp-ever-accounting' ), 'secondary', null, false ); ?>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * Export payments.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function payments_import() {
		?>
		<div class="eac-card">
			<div class="eac-card__header">
				<h2 class="eac-card__title"><?php esc_html_e( 'Import Payments', 'wp-ever-accounting' ); ?></h2>
			</div>
			<div class="eac-card__body">
				<form method="post" enctype="multipart/form-data" class="eac_importer" data-type="payments" data-nonce="<?php echo esc_attr( wp_create_nonce( 'eac_ajax_import' ) ); ?>">
					<p>
						<?php
						printf(
						/* translators: %s: import type */
							esc_html__( 'Import payments from CSV file. Download a %1$s sample file %2$s to learn how to format the CSV file.', 'wp-ever-accounting' ),
							'<a href="' . esc_url( EAC()->get_dir_url( 'samples/import/payments.csv' ) ) . '" download>',
							'</a>'
						);
						?>
					</p>
					<div class="eac-form-field">
						<label for="file"><?php esc_html_e( 'Select file', 'wp-ever-accounting' ); ?></label>
						<input type="file" name="file" id="file" accept="text/csv" required>
					</div>
					<?php submit_button( esc_html__( 'Import', 'wp-ever-accounting' ), 'secondary', null, false ); ?>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * Get importer class.
	 *
	 * @param string $type Importer type.
	 *
	 * @since 1.0.0
	 * @return Importers\Importer|null Importer class.
	 */
	public static function get_importer( $type ) {
		switch ( $type ) {
			case 'customers':
				$importer = Importers\Customers::class;
				break;
			case 'vendors':
				$importer = Importers\Vendors::class;
				break;
			case 'categories':
				$importer = Importers\Categories::class;
				break;
			case 'taxes':
				$importer = Importers\Taxes::class;
				break;
			case 'items':
				$importer = Importers\Items::class;
				break;
			case 'accounts':
				$importer = Importers\Accounts::class;
				break;
			case 'transfers':
				$importer = Importers\Transfers::class;
				break;
			case 'expenses':
				$importer = Importers\Expenses::class;
				break;
			case 'payments':
				$importer = Importers\Payments::class;
				break;
			default:
				/**
				 * Filter the import type.
				 *
				 * @since 1.0.2
				 */
				$importer = apply_filters( "eac_ajax_{$type}_importer", null );
		}

		return $importer;
	}
}
