<?php
/**
 * Updates functions.
 *
 * @since 1.0.0
 * @package EverAccounting\Functions
 */

use EverAccounting\Utilities\DatabaseUtil;

defined( 'ABSPATH' ) || exit;

/**
 * Update settings to 1.2.0
 */
function eac_update_120_settings() {
	$settings     = get_option( 'eaccounting_settings', array() );
	$settings_map = array(
		'eac_business_name'                => 'company_name',
		'eac_business_email'               => 'company_email',
		'eac_business_phone'               => 'company_phone',
		'eac_business_logo'                => 'company_logo',
		'eac_business_tax_number'          => 'company_vat_number',
		'eac_base_currency'                => 'default_currency',
		'eac_year_start_date'              => 'financial_year_start',
		'eac_business_address'             => 'company_address',
		'eac_business_city'                => 'company_city',
		'eac_business_state'               => 'company_state',
		'eac_business_postcode'            => 'company_postcode',
		'eac_business_country'             => 'company_country',
		'eac_tax_enabled'                  => 'tax_enabled',
		'eac_tax_subtotal_rounding'        => 'tax_subtotal_rounding',
		'eac_tax_total_display'            => 'tax_display_totals',
		'eac_default_sales_account_id'     => 'default_account',
		'eac_default_sales_payment_method' => 'default_payment_method',
		'eac_invoice_prefix'               => 'invoice_prefix',
		'eac_invoice_digits'               => 'invoice_digit',
		'eac_invoice_due_date'             => 'invoice_due',
		'eac_invoice_note'                 => 'invoice_notes',
		'eac_invoice_item_label'           => 'invoice_item_label',
		'eac_invoice_price_label'          => 'invoice_price_label',
		'eac_invoice_quantity_label'       => 'invoice_quantity_label',
		'eac_bill_prefix'                  => 'bill_prefix',
		'eac_bill_digits'                  => 'bill_digit',
		'eac_bill_note'                    => 'bill_notes',
		'eac_bill_due_date'                => 'bill_due',
		'eac_bill_item_label'              => 'bill_item_label',
		'eac_bill_price_label'             => 'bill_price_label',
		'eac_bill_quantity_label'          => 'bill_quantity_label',
	);

	foreach ( $settings_map as $new_key => $old_key ) {
		if ( isset( $settings[ $old_key ] ) ) {
			update_option( $new_key, $settings[ $old_key ] );
		}
	}

	$currencies   = get_option( 'eac_exchange_rates', array() );
	$o_currencies = get_option( 'eaccounting_currencies', array() );
	if ( is_array( $o_currencies ) && ! empty( $o_currencies ) ) {
		$o_currencies = wp_list_pluck( $o_currencies, 'rate', 'code' );
		foreach ( $o_currencies as $code => $rate ) {
			if ( ! empty( $code ) && ! empty( $rate ) && eac_base_currency() !== $code ) {
				$currencies[ $code ] = $rate;
			}
		}
	}
	update_option( 'eac_exchange_rates', $currencies );
}

/**
 * Update Transactions to 1.2.0
 */
function eac_update_120_transactions() {
	global $wpdb;
	$wpdb->ea_transactions = $wpdb->prefix . 'ea_transactions';
	$wpdb->query( "UPDATE $wpdb->ea_transactions SET type = 'payment' WHERE type = 'income'" );
	$wpdb->query( "UPDATE $wpdb->ea_transactions SET currency = currency_code, note = description, exchange_rate = currency_rate, uuid = UUID(), author_id = creator_id" );
	$wpdb->query( "UPDATE $wpdb->ea_transactions SET author_id = creator_id" );
	$wpdb->query( "UPDATE $wpdb->ea_transactions JOIN (SELECT @rank := 0) r SET number=CONCAT('PAY-',LPAD(@rank:=@rank+1, 5, '0')) WHERE type='payment' AND number = ''" );
	$wpdb->query( "UPDATE $wpdb->ea_transactions JOIN (SELECT @rank := 0) r SET number=CONCAT('EXP-',LPAD(@rank:=@rank+1, 5, '0')) WHERE type='expense' AND number = ''" );

	$wpdb->query( "ALTER TABLE $wpdb->ea_transactions MODIFY number VARCHAR(30) NOT NULL AFTER type" );
	$wpdb->query( "ALTER TABLE $wpdb->ea_transactions MODIFY currency VARCHAR(3) NOT NULL DEFAULT 'USD' AFTER amount" );
	$wpdb->query( "ALTER TABLE $wpdb->ea_transactions MODIFY exchange_rate DOUBLE(15, 8) NOT NULL DEFAULT 1.0 AFTER currency" );
	$wpdb->query( "ALTER TABLE $wpdb->ea_transactions MODIFY reference VARCHAR(191) DEFAULT NULL AFTER exchange_rate" );
	$wpdb->query( "ALTER TABLE $wpdb->ea_transactions MODIFY note TEXT DEFAULT NULL AFTER reference" );
	$wpdb->query( "ALTER TABLE $wpdb->ea_transactions MODIFY payment_method VARCHAR(100) DEFAULT NULL AFTER note" );
	$wpdb->query( "ALTER TABLE $wpdb->ea_transactions MODIFY date_created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER uuid" );

	// Drop the old columns.
	DatabaseUtil::drop_columns( 'ea_transactions', 'currency_code, currency_rate, description, reconciled, creator_id' );

	$wpdb->ea_transfers = $wpdb->prefix . 'ea_transfers';
	$wpdb->query( "UPDATE $wpdb->ea_transfers SET payment_id = income_id" );
	$wpdb->query( "ALTER TABLE $wpdb->ea_transfers MODIFY date_created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER note" );
	$wpdb->query(
		"UPDATE $wpdb->ea_transfers
	JOIN $wpdb->ea_transactions AS payment ON payment.id = $wpdb->ea_transfers.payment_id
	JOIN $wpdb->ea_transactions AS expense ON expense.id = $wpdb->ea_transfers.expense_id
	SET $wpdb->ea_transfers.transfer_date = expense.payment_date,
		$wpdb->ea_transfers.amount = expense.amount,
		$wpdb->ea_transfers.currency = expense.currency,
		$wpdb->ea_transfers.payment_method = expense.payment_method,
		$wpdb->ea_transfers.reference = expense.reference,
		$wpdb->ea_transfers.note = expense.note"
	);

	// Drop the old columns.
	DatabaseUtil::drop_columns( 'ea_transfers', 'creator_id,income_id' );

	// Now any transaction that id is either in expense_id or payment_id set editable to 0.
	$table = $wpdb->prefix . 'ea_transactions';
	$wpdb->query(
		"UPDATE $table JOIN {$wpdb->prefix }ea_transfers AS transfer ON transfer.expense_id = $table.id OR transfer.payment_id = $table.id SET $table.editable = 0"
	);
}

/**
 * Update Documents to 1.2.0
 */
function eac_update_120_documents() {
	global $wpdb;
	$wpdb->ea_documents = $wpdb->prefix . 'ea_documents';
	$wpdb->query( "UPDATE $wpdb->ea_documents SET number = document_number" );
	$wpdb->query( "UPDATE $wpdb->ea_documents SET reference = order_number" );
	$wpdb->query( "UPDATE $wpdb->ea_documents SET currency = currency_code" );
	$wpdb->query( "UPDATE $wpdb->ea_documents SET exchange_rate = currency_rate" );
	$wpdb->query( "UPDATE $wpdb->ea_documents SET tax = total_tax" );
	$wpdb->query( "UPDATE $wpdb->ea_documents SET discount_value = discount" );
	$wpdb->query( "UPDATE $wpdb->ea_documents SET discount = total_discount" );
	$wpdb->query( "UPDATE $wpdb->ea_documents SET author_id = creator_id" );
	$wpdb->query( "UPDATE $wpdb->ea_documents SET uuid = UUID()" );

	$wpdb->query( "ALTER TABLE $wpdb->ea_documents MODIFY COLUMN `number` VARCHAR(30) NOT NULL AFTER status" );
	$wpdb->query( "ALTER TABLE $wpdb->ea_documents MODIFY COLUMN reference VARCHAR(191) DEFAULT NULL AFTER `number`" );
	$wpdb->query( "ALTER TABLE $wpdb->ea_documents MODIFY COLUMN sent_date DATETIME DEFAULT NULL AFTER due_date" );
	$wpdb->query( "ALTER TABLE $wpdb->ea_documents MODIFY COLUMN discount DOUBLE(15, 4) DEFAULT 0 AFTER subtotal" );
	$wpdb->query( "ALTER TABLE $wpdb->ea_documents MODIFY COLUMN tax DOUBLE(15, 4) DEFAULT 0 AFTER discount" );
	$wpdb->query( "ALTER TABLE $wpdb->ea_documents MODIFY COLUMN discount_value DOUBLE(15, 4) DEFAULT 0 AFTER payment_date" );
	$wpdb->query( "ALTER TABLE $wpdb->ea_documents MODIFY COLUMN date_created DATETIME  NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER uuid" );
	$wpdb->query( "ALTER TABLE $wpdb->ea_documents MODIFY COLUMN note TEXT DEFAULT NULL AFTER contact_tax_number" );
	$wpdb->query( "ALTER TABLE $wpdb->ea_documents MODIFY COLUMN terms TEXT DEFAULT NULL AFTER note" );
	$wpdb->query( "ALTER TABLE $wpdb->ea_documents MODIFY COLUMN contact_id BIGINT(20) UNSIGNED NOT NULL AFTER attachment_id" );
	$wpdb->query( "ALTER TABLE $wpdb->ea_documents MODIFY COLUMN  parent_id BIGINT(20) UNSIGNED DEFAULT NULL AFTER author_id" );

	$documents = $wpdb->get_results( "SELECT id, address FROM $wpdb->ea_documents WHERE address IS NOT NULL AND address != ''" );
	foreach ( $documents as $document ) {
		$address = maybe_unserialize( $document->address );
		$address = wp_parse_args(
			$address,
			array(
				'name'       => '',
				'company'    => '',
				'street'     => '',
				'city'       => '',
				'state'      => '',
				'postcode'   => '',
				'country'    => '',
				'email'      => '',
				'phone'      => '',
				'vat_number' => '',
			)
		);
		$mapping = array(
			'contact_name'       => 'name',
			'contact_company'    => 'company',
			'contact_address'    => 'street',
			'contact_city'       => 'city',
			'contact_state'      => 'state',
			'contact_postcode'   => 'postcode',
			'contact_country'    => 'country',
			'contact_email'      => 'email',
			'contact_phone'      => 'phone',
			'contact_tax_number' => 'vat_number',
		);

		$data = array();
		foreach ( $mapping as $new => $old ) {
			$data[ $new ] = $address[ $old ];
		}
		$data = array_filter( $data );
		if ( empty( $data ) ) {
			continue;
		}
		$wpdb->update( $wpdb->ea_documents, $data, array( 'id' => $document->id ) );
	}

	// Drop the old columns.
	DatabaseUtil::drop_columns( 'ea_documents', 'document_number, order_number, currency_code, currency_rate, total_tax, total_discount, total_shipping, total_fees, tax_inclusive, category_id, key, creator_id, address' );

	// Documents Items.
	$wpdb->ea_document_items = $wpdb->prefix . 'ea_document_items';
	$wpdb->query( "UPDATE $wpdb->ea_document_items SET name = item_name" );
	$wpdb->query( "UPDATE $wpdb->ea_document_items SET currency = currency_code" );

	$wpdb->query( "ALTER TABLE $wpdb->ea_document_items DROP COLUMN item_name" );
	$wpdb->query( "ALTER TABLE $wpdb->ea_document_items DROP COLUMN extra" );
	$wpdb->query( "ALTER TABLE $wpdb->ea_document_items DROP COLUMN tax_rate" );
	$wpdb->query( "ALTER TABLE $wpdb->ea_document_items DROP COLUMN currency_code" );
	$wpdb->query( "ALTER TABLE $wpdb->ea_document_items DROP COLUMN date_created" );

	$wpdb->query( "ALTER TABLE $wpdb->ea_document_items MODIFY COLUMN unit VARCHAR(20) DEFAULT NULL AFTER item_id" );
	$wpdb->query( "ALTER TABLE $wpdb->ea_document_items MODIFY COLUMN description VARCHAR(160) DEFAULT NULL AFTER item_id" );
	$wpdb->query( "ALTER TABLE $wpdb->ea_document_items MODIFY COLUMN name VARCHAR(191) NOT NULL AFTER item_id" );
	$wpdb->query( "ALTER TABLE $wpdb->ea_document_items MODIFY COLUMN type VARCHAR(20) NOT NULL DEFAULT 'standard' AFTER item_id" );
}

/**
 * Update items to 1.2.0
 */
function eac_update_120_accounts() {
	global $wpdb;
	$wpdb->ea_accounts = $wpdb->prefix . 'ea_accounts';
	$wpdb->query( "UPDATE $wpdb->ea_accounts SET currency = currency_code" );
	$wpdb->query( "ALTER TABLE $wpdb->ea_accounts MODIFY COLUMN type VARCHAR(50) NOT NULL DEFAULT 'account' AFTER id" );
	$wpdb->query( "ALTER TABLE $wpdb->ea_accounts MODIFY COLUMN date_created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER currency" );

	// accounts having opening balance insert as payments.
	$accounts = $wpdb->get_results( "SELECT id, opening_balance, currency, date_created FROM $wpdb->ea_accounts WHERE opening_balance != ''" );
	foreach ( $accounts as $account ) {
		$payment = new \EverAccounting\Models\Payment();
		$payment->fill(
			array(
				'account_id'    => $account->id,
				'amount'        => $account->opening_balance,
				'currency'      => $account->currency,
				'exchange_rate' => EAC()->currencies->get_rate( $account->currency ),
				'payment_date'  => ! empty( $account->date_created ) ? $account->date_created : current_time( 'mysql' ),
				'note'          => __( 'Opening Balance', 'wp-ever-accounting' ),
			)
		);
		$payment->save();
	}

	// now drop the opening balance column.
	$wpdb->query( "ALTER TABLE $wpdb->ea_accounts DROP COLUMN opening_balance" );

	// now query all the accounts and update the balance.
	$accounts = EAC()->accounts->query( array( 'limit' => - 1 ) );
	foreach ( $accounts as $account ) {
		$account->update_balance();
	}

	// Drop the old columns.
	DatabaseUtil::drop_columns( 'ea_accounts', 'creator_id, currency_code, enabled, thumbnail_id, bank_name, bank_phone, bank_address' );

	wp_cache_flush();
}

/**
 * Update Categories to 1.2.0
 */
function eac_update_120_categories() {
	global $wpdb;
	$wpdb->ea_categories = $wpdb->prefix . 'ea_categories';
	$wpdb->ea_terms      = $wpdb->prefix . 'ea_terms';
	$wpdb->query( "UPDATE $wpdb->ea_categories SET type = 'payment' WHERE type = 'income'" );
	$wpdb->query( "DELETE FROM $wpdb->ea_categories WHERE type = 'other'" );
	$wpdb->query( "INSERT INTO $wpdb->ea_terms (id, name, type, date_created) SELECT id, name, type, date_created FROM $wpdb->ea_categories" );
	DatabaseUtil::drop_tables( 'ea_categories' );
}

/**
 * Update Contacts to 1.2.0
 */
function eac_update_120_contacts() {
	global $wpdb;
	$wpdb->ea_contacts = $wpdb->prefix . 'ea_contacts';
	$wpdb->query( "UPDATE $wpdb->ea_contacts SET tax_number = vat_number" );
	$wpdb->query( "UPDATE $wpdb->ea_contacts SET currency = currency_code" );
	$wpdb->query( "ALTER TABLE $wpdb->ea_contacts MODIFY COLUMN type VARCHAR(30) DEFAULT 'customer' AFTER id" );
	$wpdb->query( "ALTER TABLE $wpdb->ea_contacts MODIFY COLUMN website VARCHAR(191) DEFAULT NULL AFTER phone" );
	$wpdb->query( "ALTER TABLE $wpdb->ea_contacts MODIFY COLUMN country VARCHAR(3) DEFAULT NULL AFTER postcode" );
	$wpdb->query( "ALTER TABLE $wpdb->ea_contacts MODIFY COLUMN date_created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER created_via" );

	// Drop the old columns.
	DatabaseUtil::drop_columns( 'ea_contacts', 'vat_number, currency_code, attachment, enabled, creator_id, thumbnail_id, user_id, street, birth_date' );

	// Contacts.
	$wpdb->ea_contactmeta = $wpdb->prefix . 'ea_contactmeta';
	$wpdb->query( "UPDATE $wpdb->ea_contactmeta SET ea_contact_id = contact_id" );
	$wpdb->query( "ALTER TABLE $wpdb->ea_contactmeta MODIFY COLUMN ea_contact_id INT(11) NOT NULL AFTER meta_id" );

	// Drop the old columns.
	DatabaseUtil::drop_columns( 'ea_contactmeta', 'contact_id' );
}


/**
 * Update Items to 1.2.0
 */
function eac_update_120_items() {
	global $wpdb;
	$wpdb->ea_items = $wpdb->prefix . 'ea_items';
	$wpdb->query( "UPDATE $wpdb->ea_items SET price = sale_price, cost = purchase_price" );
	$wpdb->query( "ALTER TABLE $wpdb->ea_items MODIFY type VARCHAR(50) NOT NULL AFTER id" );
	$wpdb->query( "ALTER TABLE $wpdb->ea_items MODIFY unit VARCHAR(50) DEFAULT NULL AFTER description" );
	$wpdb->query( "ALTER TABLE $wpdb->ea_items MODIFY price DECIMAL(10,2) DEFAULT NULL AFTER unit" );
	$wpdb->query( "ALTER TABLE $wpdb->ea_items MODIFY cost DOUBLE(15, 4) NOT NULL AFTER price" );
	$wpdb->query( "ALTER TABLE $wpdb->ea_items MODIFY tax_ids VARCHAR(191) DEFAULT NULL AFTER cost" );
	$wpdb->query( "ALTER TABLE $wpdb->ea_items MODIFY created_via VARCHAR(20) DEFAULT 'manual' AFTER category_id" );

	// Drop the old columns.
	DatabaseUtil::drop_columns( 'ea_items', 'sale_price, purchase_price, sku, quantity, sales_tax, purchase_tax, enabled, creator_id, thumbnail_id' );
}

/**
 * Update Notes to 1.2.0
 */
function eac_update_120_notes() {
	global $wpdb;
	$wpdb->ea_notes = $wpdb->prefix . 'ea_notes';
	$wpdb->query( "UPDATE $wpdb->ea_notes SET author_id = creator_id" );
	$wpdb->query( "UPDATE $wpdb->ea_notes SET parent_type = type" );
	$wpdb->query( "UPDATE $wpdb->ea_notes SET content = note" );
	$wpdb->query( "ALTER TABLE $wpdb->ea_notes MODIFY date_created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER author_id" );

	// Drop the old columns.
	DatabaseUtil::drop_columns( 'ea_notes', 'type, note, extra, creator_id' );
}

/**
 * Update Payments to 1.2.0
 */
function eac_update_120_misc() {
	global $wpdb;
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}ea_currencies" );
	update_option( 'eac_setup_wizard_completed', 'yes' );
}
