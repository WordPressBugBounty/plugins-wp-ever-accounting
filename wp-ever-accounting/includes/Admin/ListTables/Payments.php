<?php

namespace EverAccounting\Admin\ListTables;

use EverAccounting\Models\Payment;

defined( 'ABSPATH' ) || exit;

/**
 * Class Payments.
 *
 * @since 1.0.0
 * @package EverAccounting
 */
class Payments extends ListTable {
	/**
	 * Constructor.
	 *
	 * @param array $args An associative array of arguments.
	 *
	 * @see WP_List_Table::__construct() for more information on default arguments.
	 * @since 1.0.0
	 */
	public function __construct( $args = array() ) {
		parent::__construct(
			wp_parse_args(
				$args,
				array(
					'singular' => 'payment',
					'plural'   => 'payments',
					'screen'   => get_current_screen(),
					'args'     => array(),
				)
			)
		);
		$this->base_url = admin_url( 'admin.php?page=eac-sales&tab=payments' );
	}

	/**
	 * Prepares the list for display.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function prepare_items() {
		$this->process_actions();
		$per_page    = $this->get_items_per_page( 'eac_payments_per_page', 20 );
		$paged       = $this->get_pagenum();
		$search      = $this->get_request_search();
		$order_by    = $this->get_request_orderby();
		$order       = $this->get_request_order();
		$account_id  = filter_input( INPUT_GET, 'account_id', FILTER_VALIDATE_INT );
		$category_id = filter_input( INPUT_GET, 'category_id', FILTER_VALIDATE_INT );
		$contact_id  = filter_input( INPUT_GET, 'customer_id', FILTER_VALIDATE_INT );
		$date        = filter_input( INPUT_GET, 'm', FILTER_VALIDATE_INT );
		$args        = array(
			'limit'       => $per_page,
			'page'        => $paged,
			'search'      => $search,
			'orderby'     => $order_by,
			'order'       => $order,
			'status'      => $this->get_request_status(),
			'account_id'  => $account_id,
			'category_id' => $category_id,
			'contact_id'  => $contact_id,
		);

		if ( ! empty( $date ) ) {
			$month                         = (int) substr( $date, 4, 2 );
			$year                          = (int) substr( $date, 0, 4 );
			$start                         = wp_date( 'Y-m-d H:i:s', mktime( 0, 0, 0, $month, 1, $year ) );
			$end                           = wp_date( 'Y-m-d H:i:s', mktime( 23, 59, 59, $month + 1, 0, $year ) );
			$args['payment_date__between'] = array( $start, $end );
		}

		/**
		 * Filter the query arguments for the list table.
		 *
		 * @param array $args An associative array of arguments.
		 *
		 * @since 1.0.0
		 */
		$args = apply_filters( 'eac_payments_table_query_args', $args );

		$this->items = Payment::results( $args );
		$total       = Payment::count( $args );

		$this->set_pagination_args(
			array(
				'total_items' => $total,
				'per_page'    => $per_page,
			)
		);
	}

	/**
	 * handle bulk delete action.
	 *
	 * @param array $ids List of item IDs.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function bulk_delete( $ids ) {
		$performed = 0;
		foreach ( $ids as $id ) {
			if ( EAC()->payments->delete( $id ) ) {
				++$performed;
			}
		}
		if ( ! empty( $performed ) ) {
			// translators: %s: number of items deleted.
			EAC()->flash->success( sprintf( __( '%s payment(s) deleted successfully.', 'wp-ever-accounting' ), number_format_i18n( $performed ) ) );
		}
	}

	/**
	 * Outputs 'no results' message.
	 *
	 * @since 1.0.0
	 */
	public function no_items() {
		esc_html_e( 'No payments found.', 'wp-ever-accounting' );
	}

	/**
	 * Retrieves an associative array of bulk actions available on this table.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of bulk action labels keyed by their action.
	 */
	protected function get_bulk_actions() {
		$actions = array(
			'delete' => __( 'Delete', 'wp-ever-accounting' ),
		);

		return $actions;
	}

	/**
	 * Outputs the controls to allow user roles to be changed in bulk.
	 *
	 * @param string $which Whether invoked above ("top") or below the table ("bottom").
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function extra_tablenav( $which ) {
		global $wpdb;
		static $has_items;
		if ( ! isset( $has_items ) ) {
			$has_items = $this->has_items();
		}
		echo '<div class="alignleft actions">';
		if ( 'top' === $which ) {
			$months = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT DISTINCT YEAR( payment_date ) AS year, MONTH( payment_date ) AS month
					FROM {$wpdb->prefix}ea_transactions
					WHERE type = %s AND payment_date IS NOT NULL
					ORDER BY payment_date DESC",
					'payment'
				)
			);

			$this->date_filter( $months );
			$this->contact_filter( 'customer' );
			$this->account_filter();
			$this->category_filter( 'payment' );
			submit_button( __( 'Filter', 'wp-ever-accounting' ), '', 'filter_action', false );
		}
		echo '</div>';
	}

	/**
	 * Gets a list of columns for the list table.
	 *
	 * @since 1.0.0
	 *
	 * @return string[] Array of column titles keyed by their column name.
	 */
	public function get_columns() {
		return array(
			'cb'          => '<input type="checkbox" />',
			'number'      => __( 'Payment #', 'wp-ever-accounting' ),
			'date'        => __( 'Date', 'wp-ever-accounting' ),
			'account_id'  => __( 'Account', 'wp-ever-accounting' ),
			'customer_id' => __( 'Customer', 'wp-ever-accounting' ),
			'invoice_id'  => __( 'Invoice', 'wp-ever-accounting' ),
			'reference'   => __( 'Reference', 'wp-ever-accounting' ),
			'amount'      => __( 'Amount', 'wp-ever-accounting' ),
		);
	}

	/**
	 * Gets a list of sortable columns for the list table.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of sortable columns.
	 */
	protected function get_sortable_columns() {
		return array(
			'date'        => array( 'payment_date', true ),
			'number'      => array( 'number', false ),
			'account_id'  => array( 'account_id', false ),
			'invoice_id'  => array( 'invoice_id', false ),
			'customer_id' => array( 'customer_id', false ),
			'reference'   => array( 'reference', false ),
			'amount'      => array( 'amount', false ),
		);
	}

	/**
	 * Define primary column.
	 *
	 * @since 1.0.2
	 * @return string
	 */
	public function get_primary_column_name() {
		return 'number';
	}

	/**
	 * Renders the checkbox column.
	 *
	 * @param Payment $item The current object.
	 *
	 * @since  1.0.0
	 * @return string Displays a checkbox.
	 */
	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="id[]" value="%d"/>', esc_attr( $item->id ) );
	}

	/**
	 * Renders the number column.
	 *
	 * @param Payment $item The current object.
	 *
	 * @since  1.0.0
	 * @return string Displays the number.
	 */
	public function column_number( $item ) {
		return sprintf(
			'<a class="row-title" href="%s">%s</a>',
			esc_url( $item->get_view_url() ),
			wp_kses_post( $item->number )
		);
	}

	/**
	 * Renders the name column.
	 *
	 * @param Payment $item The current object.
	 *
	 * @since  1.0.0
	 * @return string Displays the name.
	 */
	public function column_date( $item ) {
		return $item->payment_date ? wp_date( eac_date_format(), strtotime( $item->payment_date ) ) : '&mdash;';
	}

	/**
	 * Renders the account column.
	 *
	 * @param Payment $item The current object.
	 *
	 * @since  1.0.0
	 * @return string Displays the account.
	 */
	public function column_account_id( $item ) {
		$account  = $item->account ? sprintf( '<a href="%s">%s</a>', esc_url( $item->account->get_view_url() ), wp_kses_post( $item->account->name ) ) : '&mdash;';
		$metadata = $item->account && $item->account->number ? ucfirst( $item->account->number ) : '';

		return sprintf( '%s%s', $account, $this->column_metadata( $metadata ) );
	}

	/**
	 * Renders the invoice column.
	 *
	 * @param Payment $item The current object.
	 *
	 * @since  1.0.0
	 * @return string Displays the category.
	 */
	public function column_invoice_id( $item ) {
		$invoice  = '&mdash;';
		$metadata = '';
		if ( $item->invoice ) {
			$invoice = sprintf( '<a href="%s">%s</a>', esc_url( $item->invoice->get_view_url() ), wp_kses_post( $item->invoice->number ) );
		}

		return sprintf( '%s', empty( $this->column_metadata( $metadata ) ) ? $invoice : $this->column_metadata( $metadata ) );
	}

	/**
	 * Renders the customer column.
	 *
	 * @param Payment $item The current object.
	 *
	 * @since  1.0.0
	 * @return string Displays the customer.
	 */
	public function column_customer_id( $item ) {
		$customer = $item->customer ? sprintf( '<a href="%s">%s</a>', esc_url( $item->customer->get_view_url() ), wp_kses_post( $item->customer->name ) ) : '&mdash;';
		$metadata = $item->customer && $item->customer->company ? $item->customer->company : '';

		return sprintf( '%s%s', $customer, $this->column_metadata( $metadata ) );
	}

	/**
	 * Renders the amount column.
	 *
	 * @param Payment $item The current object.
	 *
	 * @since  1.0.0
	 * @return string Displays the amount.
	 */
	public function column_amount( $item ) {
		return esc_html( $item->formatted_amount );
	}

	/**
	 * Generates and displays row actions links.
	 *
	 * @param Payment $item The comment object.
	 * @param string  $column_name Current column name.
	 * @param string  $primary Primary column name.
	 *
	 * @since 1.0.0
	 * @return string Row actions output.
	 */
	protected function handle_row_actions( $item, $column_name, $primary ) {
		if ( $primary !== $column_name ) {
			return null;
		}
		$actions = array(
			'edit'   => sprintf(
				'<a href="%s">%s</a>',
				esc_url( $item->get_edit_url() ),
				__( 'Edit', 'wp-ever-accounting' )
			),
			'delete' => sprintf(
				'<a href="%s" class="del del_confirm">%s</a>',
				esc_url(
					wp_nonce_url(
						add_query_arg(
							array(
								'action' => 'delete',
								'id'     => $item->id,
							),
							$this->base_url
						),
						'bulk-' . $this->_args['plural']
					)
				),
				__( 'Delete', 'wp-ever-accounting' )
			),
		);

		if ( ! $item->editable ) {
			unset( $actions['edit'] );
		}

		return $this->row_actions( $actions );
	}
}
