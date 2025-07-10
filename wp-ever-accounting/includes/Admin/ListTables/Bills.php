<?php

namespace EverAccounting\Admin\ListTables;

use EverAccounting\Models\Bill;
use EverAccounting\Utilities\ReportsUtil;

defined( 'ABSPATH' ) || exit;

/**
 * Class Bills.
 *
 * @since 1.0.0
 * @package EverAccounting\Admin\ListTables
 */
class Bills extends ListTable {
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
					'singular' => 'bill',
					'plural'   => 'bills',
					'screen'   => get_current_screen(),
					'args'     => array(),
				)
			)
		);

		$this->base_url = admin_url( 'admin.php?page=eac-purchases&tab=bills' );
	}

	/**
	 * Prepares the list for display.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function prepare_items() {
		$this->process_actions();
		$per_page   = $this->get_items_per_page( 'eac_bills_per_page', 20 );
		$paged      = $this->get_pagenum();
		$search     = $this->get_request_search();
		$order_by   = $this->get_request_orderby();
		$order      = $this->get_request_order();
		$contact_id = filter_input( INPUT_GET, 'vendor_id', FILTER_VALIDATE_INT );
		$year_month = filter_input( INPUT_GET, 'm', FILTER_VALIDATE_INT );
		$args       = array(
			'limit'      => $per_page,
			'page'       => $paged,
			'search'     => $search,
			'orderby'    => $order_by,
			'order'      => $order,
			'status'     => $this->get_request_status(),
			'contact_id' => $contact_id,
		);

		if ( ! empty( $year_month ) && preg_match( '/^[0-9]{6}$/', $year_month ) ) {
			$year                        = (int) substr( $year_month, 0, 4 );
			$month                       = (int) substr( $year_month, 4, 2 );
			$start                       = get_gmt_from_date( "$year-$month-01 00:00:00" );
			$end                         = get_gmt_from_date( date_create( "$year-$month" )->format( 'Y-m-t 23:59:59' ) );
			$args['issue_date__between'] = array( $start, $end );
		}

		/**
		 * Filter the query arguments for the list table.
		 *
		 * @param array $args An associative array of arguments.
		 *
		 * @since 1.0.0
		 */
		$args = apply_filters( 'eac_bills_table_query_args', $args );

		$this->items = Bill::results( $args );
		$total       = Bill::count( $args );

		$this->set_pagination_args(
			array(
				'total_items' => $total,
				'per_page'    => $per_page,
			)
		);
	}

	/**
	 * handle bulk set draft action.
	 *
	 * @param array $ids List of item IDs.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function bulk_set_draft( $ids ) {
		if ( ! current_user_can( 'eac_edit_bills' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Reason: This is a custom capability.
			EAC()->flash->error( __( 'You do not have permission to perform this action.', 'wp-ever-accounting' ) );
			return;
		}

		$performed = 0;
		foreach ( $ids as $id ) {
			$bill = EAC()->bills->get( $id );
			if ( $bill && $bill->fill( array( 'status' => 'draft' ) )->save() ) {
				++$performed;
			}
		}
		if ( ! empty( $performed ) ) {
			// translators: %s: number of items updated.
			EAC()->flash->success( sprintf( __( '%s bill(s) marked as draft successfully.', 'wp-ever-accounting' ), number_format_i18n( $performed ) ) );
		}
	}

	/**
	 * handle bulk set received action.
	 *
	 * @param array $ids List of item IDs.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function bulk_set_received( $ids ) {
		if ( ! current_user_can( 'eac_edit_bills' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Reason: This is a custom capability.
			EAC()->flash->error( __( 'You do not have permission to perform this action.', 'wp-ever-accounting' ) );
			return;
		}

		$performed = 0;
		foreach ( $ids as $id ) {
			$bill = EAC()->bills->get( $id );
			if ( $bill && $bill->fill( array( 'status' => 'received' ) )->save() ) {
				++$performed;
			}
		}
		if ( ! empty( $performed ) ) {
			// translators: %s: number of items updated.
			EAC()->flash->success( sprintf( __( '%s bill(s) marked as received successfully.', 'wp-ever-accounting' ), number_format_i18n( $performed ) ) );
		}
	}

	/**
	 * handle bulk set overdue action.
	 *
	 * @param array $ids List of item IDs.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function bulk_set_overdue( $ids ) {
		if ( ! current_user_can( 'eac_edit_bills' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Reason: This is a custom capability.
			EAC()->flash->error( __( 'You do not have permission to perform this action.', 'wp-ever-accounting' ) );
			return;
		}

		$performed = 0;
		foreach ( $ids as $id ) {
			$bill = EAC()->bills->get( $id );
			if ( $bill && $bill->fill( array( 'status' => 'overdue' ) )->save() ) {
				++$performed;
			}
		}
		if ( ! empty( $performed ) ) {
			// translators: %s: number of items updated.
			EAC()->flash->success( sprintf( __( '%s bill(s) marked as overdue successfully.', 'wp-ever-accounting' ), number_format_i18n( $performed ) ) );
		}
	}

	/**
	 * handle bulk set cancelled action.
	 *
	 * @param array $ids List of item IDs.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function bulk_set_cancelled( $ids ) {
		if ( ! current_user_can( 'eac_edit_bills' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Reason: This is a custom capability.
			EAC()->flash->error( __( 'You do not have permission to perform this action.', 'wp-ever-accounting' ) );
			return;
		}

		$performed = 0;
		foreach ( $ids as $id ) {
			$bill = EAC()->bills->get( $id );
			if ( $bill && $bill->fill( array( 'status' => 'cancelled' ) )->save() ) {
				++$performed;
			}
		}
		if ( ! empty( $performed ) ) {
			// translators: %s: number of items updated.
			EAC()->flash->success( sprintf( __( '%s bill(s) marked as cancelled successfully.', 'wp-ever-accounting' ), number_format_i18n( $performed ) ) );
		}
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
		if ( ! current_user_can( 'eac_delete_bills' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Reason: This is a custom capability.
			EAC()->flash->error( __( 'You do not have permission to delete bills.', 'wp-ever-accounting' ) );
			return;
		}

		$performed = 0;
		foreach ( $ids as $id ) {
			if ( EAC()->bills->delete( $id ) ) {
				++$performed;
			}
		}
		if ( ! empty( $performed ) ) {
			// translators: %s: number of items deleted.
			EAC()->flash->success( sprintf( __( '%s bill(s) deleted successfully.', 'wp-ever-accounting' ), number_format_i18n( $performed ) ) );
		}
	}

	/**
	 * Outputs 'no items' message.
	 *
	 * @since 1.0.0
	 */
	public function no_items() {
		esc_html_e( 'No bills found.', 'wp-ever-accounting' );
	}

	/**
	 * Returns an associative array listing all the views that can be used
	 * with this table.
	 *
	 * Provides a list of roles and user count for that role for easy
	 * filtering of the user table.
	 *
	 * @since 1.0.0
	 * @return string[] An array of HTML links keyed by their view.
	 */
	protected function get_views() {
		$current      = $this->get_request_status( 'all' );
		$status_links = array();
		$statuses     = EAC()->bills->get_statuses();

		foreach ( $statuses as $status => $label ) {
			$link  = 'all' === $status ? $this->base_url : add_query_arg( 'status', $status, $this->base_url );
			$args  = 'all' === $status ? array() : array( 'status' => $status );
			$count = Bill::count( $args );
			$label = sprintf( '%s <span class="count">(%s)</span>', esc_html( $label ), number_format_i18n( $count ) );

			$status_links[ $status ] = array(
				'url'     => $link,
				'label'   => $label,
				'current' => $current === $status,
			);
		}

		return $this->get_views_links( $status_links );
	}

	/**
	 * Retrieves an associative array of bulk actions available on this table.
	 *
	 * @since 1.0.0
	 * @return array Array of bulk action labels keyed by their action.
	 */
	protected function get_bulk_actions() {
		$actions = array();

		if ( current_user_can( 'eac_edit_bills' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Reason: This is a custom capability.
			$actions['set_draft']     = __( 'Set Draft', 'wp-ever-accounting' );
			$actions['set_received']  = __( 'Set Received', 'wp-ever-accounting' );
			$actions['set_overdue']   = __( 'Set Overdue', 'wp-ever-accounting' );
			$actions['set_cancelled'] = __( 'Set Cancelled', 'wp-ever-accounting' );
		}

		if ( current_user_can( 'eac_delete_bills' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Reason: This is a custom capability.
			$actions['delete'] = __( 'Delete', 'wp-ever-accounting' );
		}

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
			$date_column = ReportsUtil::get_localized_time_sql( 'issue_date' );
			$months      = $wpdb->get_results(
			// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$wpdb->prepare(
					"SELECT DISTINCT YEAR( {$date_column} ) AS year, MONTH( {$date_column} ) AS month
					FROM {$wpdb->prefix}ea_documents
					WHERE type = %s AND issue_date IS NOT NULL
					ORDER BY issue_date DESC",
					'bill'
				)
			// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			);
			$this->date_filter( $months );
			$this->contact_filter( 'vendor' );
			submit_button( __( 'Filter', 'wp-ever-accounting' ), '', 'filter_action', false );
		}

		echo '</div>';
	}

	/**
	 * Gets a list of columns for the list table.
	 *
	 * @since 1.0.0
	 * @return string[] Array of column titles keyed by their column name.
	 */
	public function get_columns() {
		return array(
			'cb'           => '<input type="checkbox" />',
			'number'       => __( 'Bill #', 'wp-ever-accounting' ),
			'issue_date'   => __( 'Issue Date', 'wp-ever-accounting' ),
			'payment_date' => __( 'Payment Date', 'wp-ever-accounting' ),
			'vendor_id'    => __( 'Vendor', 'wp-ever-accounting' ),
			'reference'    => __( 'Order #', 'wp-ever-accounting' ),
			'status'       => __( 'Status', 'wp-ever-accounting' ),
			'total'        => __( 'Total', 'wp-ever-accounting' ),
		);
	}

	/**
	 * Gets a list of sortable columns for the list table.
	 *
	 * @since 1.0.0
	 * @return array Array of sortable columns.
	 */
	protected function get_sortable_columns() {
		return array(
			'number'       => array( 'number', false ),
			'issue_date'   => array( 'issue_date', false ),
			'payment_date' => array( 'payment_date', false ),
			'vendor_id'    => array( 'vendor_id', false ),
			'reference'    => array( 'reference', false ),
			'status'       => array( 'status', false ),
			'total'        => array( 'total', false ),
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
	 * @param Bill $item The current object.
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
	 * @param Bill $item The current object.
	 *
	 * @since  1.0.0
	 * @return string Displays the name.
	 */
	public function column_number( $item ) {
		return sprintf( '<a class="row-title" href="%s">%s</a>', esc_url( $item->get_view_url() ), wp_kses_post( $item->number ) );
	}

	/**
	 * Renders the date column.
	 *
	 * @param Bill $item The current object.
	 *
	 * @since  1.0.0
	 * @return string Displays the date.
	 */
	public function column_issue_date( $item ) {
		$date     = $item->issue_date ? eac_format_datetime( $item->issue_date, eac_date_format() ) : '&mdash;';
		$metadata = $item->due_date ? sprintf( /* translators: %s Due Date */ __( 'Due: %s', 'wp-ever-accounting' ), eac_format_datetime( $item->due_date, eac_date_format() ) ) : '';

		return sprintf( '%s%s', $date, $this->column_metadata( $metadata ) );
	}


	/**
	 * Renders the vendor column.
	 *
	 * @param Bill $item The current object.
	 *
	 * @since  1.0.0
	 * @return string Displays the vendor.
	 */
	public function column_vendor_id( $item ) {
		if ( $item->vendor ) {
			return sprintf( '<a href="%s">%s</a>', esc_url( $item->vendor->get_view_url() ), wp_kses_post( $item->vendor->name ) );
		}

		return '&mdash;';
	}

	/**
	 * Renders the reference column.
	 *
	 * @param Bill $item The current object.
	 *
	 * @since  1.0.0
	 * @return string Displays the reference.
	 */
	public function column_reference( $item ) {
		return $item->reference ? esc_html( $item->reference ) : '&mdash;';
	}

	/**
	 * Renders the price column.
	 *
	 * @param Bill $item The current object.
	 *
	 * @since  1.0.0
	 * @return string Displays the price.
	 */
	public function column_total( $item ) {
		return esc_html( $item->formatted_total );
	}

	/**
	 * Renders the status column.
	 *
	 * @param Bill $item The current object.
	 *
	 * @since 1.0.0
	 * @return string Displays the status.
	 */
	public function column_status( $item ) {
		return sprintf( '<span class="eac-status is--%1$s">%2$s</span>', esc_attr( $item->status ), esc_html( $item->status_label ) );
	}

	/**
	 * Generates and displays row actions links.
	 *
	 * @param Bill   $item The object.
	 * @param string $column_name Current column name.
	 * @param string $primary Primary column name.
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

		if ( ! current_user_can( 'eac_delete_bills' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Reason: This is a custom capability.
			unset( $actions['delete'] );
		}

		if ( ! current_user_can( 'eac_edit_bills' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Reason: This is a custom capability.
			unset( $actions['edit'] );
		}

		return $this->row_actions( $actions );
	}
}
