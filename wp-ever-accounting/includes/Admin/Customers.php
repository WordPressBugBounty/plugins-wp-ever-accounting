<?php

namespace EverAccounting\Admin;

use EverAccounting\Models\Customer;
use EverAccounting\Utilities\ReportsUtil;

defined( 'ABSPATH' ) || exit;

/**
 * Class Customers
 *
 * @package EverAccounting\Admin\Sales
 */
class Customers {

	/**
	 * Customers constructor.
	 */
	public function __construct() {
		add_filter( 'eac_sales_page_tabs', array( __CLASS__, 'register_tabs' ) );
		add_action( 'admin_post_eac_edit_customer', array( __CLASS__, 'handle_edit' ) );
		add_action( 'eac_sales_page_customers_loaded', array( __CLASS__, 'page_loaded' ) );
		add_action( 'eac_sales_page_customers_content', array( __CLASS__, 'page_content' ) );
		add_action( 'eac_customer_profile_section_overview', array( __CLASS__, 'overview_section' ) );
		add_action( 'eac_customer_profile_section_payments', array( __CLASS__, 'payments_section' ) );
		add_action( 'eac_customer_profile_section_invoices', array( __CLASS__, 'invoices_section' ) );
		add_action( 'eac_customer_profile_section_notes', array( __CLASS__, 'notes_section' ) );
	}

	/**
	 * Register tab.
	 *
	 * @param array $tabs Tabs.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public static function register_tabs( $tabs ) {
		if ( current_user_can( 'eac_manage_customer' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
			$tabs['customers'] = __( 'Customers', 'wp-ever-accounting' );
		}

		return $tabs;
	}

	/**
	 * Handle actions.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public static function handle_edit() {
		check_admin_referer( 'eac_edit_customer' );
		if ( ! current_user_can( 'eac_manage_customer' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
			wp_die( esc_html__( 'You do not have permission to edit customers.', 'wp-ever-accounting' ) );
		}

		$referer = wp_get_referer();
		$data    = array(
			'id'         => isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '',
			'name'       => isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '',
			'company'    => isset( $_POST['company'] ) ? sanitize_text_field( wp_unslash( $_POST['company'] ) ) : '',
			'email'      => isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '',
			'phone'      => isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '',
			'website'    => isset( $_POST['website'] ) ? esc_url_raw( wp_unslash( $_POST['website'] ) ) : '',
			'address'    => isset( $_POST['address'] ) ? sanitize_text_field( wp_unslash( $_POST['address'] ) ) : '',
			'city'       => isset( $_POST['city'] ) ? sanitize_text_field( wp_unslash( $_POST['city'] ) ) : '',
			'state'      => isset( $_POST['state'] ) ? sanitize_text_field( wp_unslash( $_POST['state'] ) ) : '',
			'postcode'   => isset( $_POST['postcode'] ) ? sanitize_text_field( wp_unslash( $_POST['postcode'] ) ) : '',
			'country'    => isset( $_POST['country'] ) ? sanitize_text_field( wp_unslash( $_POST['country'] ) ) : '',
			'tax_number' => isset( $_POST['tax_number'] ) ? sanitize_text_field( wp_unslash( $_POST['tax_number'] ) ) : '',
			'currency'   => isset( $_POST['currency'] ) ? sanitize_text_field( wp_unslash( $_POST['currency'] ) ) : '',
		);

		$customer = EAC()->customers->insert( $data );

		if ( is_wp_error( $customer ) ) {
			EAC()->flash->error( $customer->get_error_message() );
		} else {
			EAC()->flash->success( __( 'Customer saved successfully.', 'wp-ever-accounting' ) );
			$referer = add_query_arg( 'id', $customer->id, $referer );
			$referer = add_query_arg( 'action', 'view', $referer );
			$referer = remove_query_arg( array( 'add' ), $referer );
		}

		wp_safe_redirect( $referer );
		exit;
	}

	/**
	 * Handle page loaded.
	 *
	 * @param string $action Current action.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public static function page_loaded( $action ) {
		global $list_table;
		switch ( $action ) {
			case 'add':
				// Nothing to do here.
				break;

			case 'view':
			case 'edit':
				$id = filter_input( INPUT_GET, 'id', FILTER_VALIDATE_INT );
				if ( ! EAC()->customers->get( $id ) ) {
					wp_die( esc_html__( 'You attempted to retrieve a customer that does not exist. Perhaps it was deleted?', 'wp-ever-accounting' ) );
				}
				break;

			default:
				$screen     = get_current_screen();
				$list_table = new ListTables\Customers();
				$list_table->prepare_items();
				$screen->add_option(
					'per_page',
					array(
						'label'   => __( 'Number of items per page:', 'wp-ever-accounting' ),
						'default' => 20,
						'option'  => 'eac_customers_per_page',
					)
				);
				break;
		}
	}

	/**
	 * Output the customers page.
	 *
	 * @param string $action Current action.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function page_content( $action ) {
		switch ( $action ) {
			case 'add':
			case 'edit':
				include __DIR__ . '/views/customer-edit.php';
				break;

			case 'view':
				include __DIR__ . '/views/customer-view.php';
				break;

			default:
				include __DIR__ . '/views/customer-list.php';
				break;
		}
	}

	/**
	 * Customer overview.
	 *
	 * @param Customer $customer Customer object.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public static function overview_section( $customer ) {
		global $wpdb;
		wp_enqueue_script( 'eac-chartjs' );
		$year_start_date = EAC()->business->get_year_start_date();
		$year_end_date   = EAC()->business->get_year_end_date();
		$results         = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT SUM(amount/exchange_rate) as amount, MONTH(payment_date) AS month, YEAR(payment_date) AS year
				FROM {$wpdb->prefix}ea_transactions WHERE contact_id = %d
			    AND payment_date BETWEEN %s AND %s AND type='payment'",
				$customer->id,
				$year_start_date,
				$year_end_date
			)
		);
		$invoices        = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT SUM(total/exchange_rate) as total FROM {$wpdb->prefix}ea_documents WHERE contact_id = %d AND contact_id !='' AND type='invoice' AND status != 'draft'",
				$customer->id
			)
		);

		$paid = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT SUM(amount/exchange_rate) as total FROM {$wpdb->prefix}ea_transactions WHERE contact_id = %d AND contact_id != '' AND type='payment'",
				$customer->id
			)
		);

		$due = empty( $invoices ) ? 0 : max( $invoices - $paid, 0 );

		$chart_data = ReportsUtil::annualize_data( $results );
		$chart      = array(
			'type'     => 'line',
			'labels'   => array_keys( $chart_data ),
			'datasets' => array(
				array(
					'label'           => __( 'Payments', 'wp-ever-accounting' ),
					'backgroundColor' => '#3644ff',
					'borderColor'     => '#3644ff',
					'fill'            => false,
					'data'            => array_values( $chart_data ),
				),
			),
		);
		?>

		<h2 class="has--border"><?php esc_html_e( 'Overview', 'wp-ever-accounting' ); ?></h2>

		<div class="eac-chart">
			<canvas class="eac-chart" id="eac-customer-chart" style="height: 300px;margin-bottom: 20px;" data-datasets="<?php echo esc_attr( wp_json_encode( $chart ) ); ?>" data-currency="<?php echo esc_attr( EAC()->currencies->get_symbol( eac_base_currency() ) ); ?>"></canvas>
		</div>
		<div class="eac-stats stats--2">
			<div class="eac-stat">
				<div class="eac-stat__label"><?php esc_html_e( 'Due', 'wp-ever-accounting' ); ?></div>
				<div class="eac-stat__value"><?php echo esc_html( eac_format_amount( $due ) ); ?></div>
			</div>
			<div class="eac-stat">
				<div class="eac-stat__label"><?php esc_html_e( 'Paid', 'wp-ever-accounting' ); ?></div>
				<div class="eac-stat__value"><?php echo esc_html( eac_format_amount( $paid ) ); ?></div>
			</div>
		</div>

		<?php
		$attributes = array(
			array(
				'label' => __( 'Name', 'wp-ever-accounting' ),
				'value' => $customer->name,
			),
			array(
				'label' => __( 'Company', 'wp-ever-accounting' ),
				'value' => $customer->company,
			),
			array(
				'label' => __( 'Email', 'wp-ever-accounting' ),
				'value' => $customer->email,
			),
			array(
				'label' => __( 'Phone', 'wp-ever-accounting' ),
				'value' => $customer->phone,
			),
			array(
				'label' => __( 'Website', 'wp-ever-accounting' ),
				'value' => $customer->website,
			),
			array(
				'label' => __( 'Address', 'wp-ever-accounting' ),
				'value' => $customer->address,
			),
			array(
				'label' => __( 'City', 'wp-ever-accounting' ),
				'value' => $customer->city,
			),
			array(
				'label' => __( 'State', 'wp-ever-accounting' ),
				'value' => $customer->state,
			),
			array(
				'label' => __( 'Postcode', 'wp-ever-accounting' ),
				'value' => $customer->postcode,
			),
			array(
				'label' => __( 'Country', 'wp-ever-accounting' ),
				'value' => $customer->country_name,
			),
			array(
				'label' => __( 'Tax Number', 'wp-ever-accounting' ),
				'value' => $customer->tax_number,
			),
			array(
				'label' => __( 'Currency', 'wp-ever-accounting' ),
				'value' => $customer->currency,
			),
			array(
				'label' => __( 'Created', 'wp-ever-accounting' ),
				'value' => wp_date( eac_date_format(), strtotime( $customer->date_created ) ),
			),
			array(
				'label' => __( 'Updated', 'wp-ever-accounting' ),
				'value' => wp_date( eac_date_format(), strtotime( $customer->date_updated ) ),
			),
		);
		?>
		<h2><?php esc_html_e( 'Details', 'wp-ever-accounting' ); ?></h2>
		<table class="eac-table is--striped is--bordered">
			<tbody>
			<?php foreach ( $attributes as $attribute ) : ?>
				<tr>
					<th><?php echo esc_html( $attribute['label'] ); ?></th>
					<td><?php echo esc_html( empty( $attribute['value'] ) ? '&mdash;' : $attribute['value'] ); ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Customer payments.
	 *
	 * @param Customer $customer Customer object.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public static function payments_section( $customer ) {
		$payments = EAC()->payments->query(
			array(
				'contact_id'      => $customer->id,
				'contact_id__not' => '',
				'limit'           => 20,
				'orderby'         => 'payment_date',
				'order'           => 'DESC',
			)
		);
		?>
		<h2 class="has--border"><?php esc_html_e( 'Recent Payments', 'wp-ever-accounting' ); ?></h2>
		<table class="widefat fixed striped">
			<thead>
			<tr>
				<th><?php esc_html_e( 'Number', 'wp-ever-accounting' ); ?></th>
				<th><?php esc_html_e( 'Date', 'wp-ever-accounting' ); ?></th>
				<th><?php esc_html_e( 'Reference', 'wp-ever-accounting' ); ?></th>
				<th><?php esc_html_e( 'Amount', 'wp-ever-accounting' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php if ( $payments ) : ?>
				<?php foreach ( $payments as $payment ) : ?>
					<tr>
						<td>
							<a href="<?php echo esc_url( $payment->get_view_url() ); ?>">
								<?php echo esc_html( $payment->number ); ?>
							</a>
						<td><?php echo esc_html( $payment->payment_date ? wp_date( eac_date_format(), strtotime( $payment->payment_date ) ) : '&mdash;' ); ?></td>
						<td><?php echo esc_html( $payment->reference ? $payment->reference : '&mdash;' ); ?></td>
						<td><?php echo esc_html( $payment->formatted_amount ); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr>
					<td colspan="4"><?php esc_html_e( 'No payments found.', 'wp-ever-accounting' ); ?></td>
				</tr>
			<?php endif; ?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Customer invoices.
	 *
	 * @param Customer $customer Customer object.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public static function invoices_section( $customer ) {
		$invoices = EAC()->invoices->query(
			array(
				'contact_id'      => $customer->id,
				'contact_id__neq' => '',
				'limit'           => 20,
				'orderby'         => 'date',
				'order'           => 'DESC',
			)
		);
		?>
		<h2 class="has--border"><?php esc_html_e( 'Recent Invoices', 'wp-ever-accounting' ); ?></h2>
		<table class="widefat fixed striped">
			<thead>
			<tr>
				<th><?php esc_html_e( 'Number', 'wp-ever-accounting' ); ?></th>
				<th><?php esc_html_e( 'Date', 'wp-ever-accounting' ); ?></th>
				<th><?php esc_html_e( 'Total', 'wp-ever-accounting' ); ?></th>
				<th><?php esc_html_e( 'Status', 'wp-ever-accounting' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php if ( $invoices ) : ?>
				<?php foreach ( $invoices as $invoice ) : ?>
					<tr>
						<td>
							<a href="<?php echo esc_url( $invoice->get_view_url() ); ?>">
								<?php echo esc_html( $invoice->number ); ?>
							</a>
						<td><?php echo esc_html( $invoice->issue_date ); ?></td>
						<td><?php echo esc_html( $invoice->formatted_total ); ?></td>
						<td><?php echo esc_html( $invoice->status_label ); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr>
					<td colspan="4"><?php esc_html_e( 'No invoices found.', 'wp-ever-accounting' ); ?></td>
				</tr>
			<?php endif; ?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Customer notes.
	 *
	 * @param Customer $customer Customer object.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public static function notes_section( $customer ) {
		$notes = EAC()->notes->query(
			array(
				'parent_id'   => $customer->id,
				'parent_type' => 'customer',
				'orderby'     => 'date_created',
				'order'       => 'DESC',
				'limit'       => 20,
			)
		);
		?>

		<h2 class="has--border"><?php esc_html_e( 'Notes', 'wp-ever-accounting' ); ?></h2>
		<div class="eac-form-field">
			<label for="eac-note"><?php esc_html_e( 'Add Note', 'wp-ever-accounting' ); ?></label>
			<textarea id="eac-note" cols="30" rows="2" placeholder="<?php esc_attr_e( 'Enter Note', 'wp-ever-accounting' ); ?>"></textarea>
		</div>
		<button id="eac-add-note" type="button" class="button tw-mb-[20px]" data-parent_id="<?php echo esc_attr( $customer->id ); ?>" data-parent_type="customer" data-nonce="<?php echo esc_attr( wp_create_nonce( 'eac_add_note' ) ); ?>">
			<?php esc_html_e( 'Add Note', 'wp-ever-accounting' ); ?>
		</button>

		<?php include __DIR__ . '/views/note-list.php'; ?>
		<?php
	}
}
