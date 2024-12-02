<?php

namespace EverAccounting\Admin\ListTables;

use EverAccounting\Models\Bill;

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
		$date       = filter_input( INPUT_GET, 'm', FILTER_VALIDATE_INT );
		$args       = array(
			'limit'      => $per_page,
			'page'       => $paged,
			'search'     => $search,
			'orderby'    => $order_by,
			'order'      => $order,
			'status'     => $this->get_request_status(),
			'contact_id' => $contact_id,
		);

		if ( ! empty( $date ) ) {
			$month                       = (int) substr( $date, 4, 2 );
			$year                        = (int) substr( $date, 0, 4 );
			$start                       = wp_date( 'Y-m-d H:i:s', mktime( 0, 0, 0, $month, 1, $year ) );
			$end                         = wp_date( 'Y-m-d H:i:s', mktime( 23, 59, 59, $month + 1, 0, $year ) );
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
		$actions = array(
			'set_draft'     => __( 'Set Draft', 'wp-ever-accounting' ),
			'set_received'  => __( 'Set Received', 'wp-ever-accounting' ),
			'set_overdue'   => __( 'Set Overdue', 'wp-ever-accounting' ),
			'set_cancelled' => __( 'Set Cancelled', 'wp-ever-accounting' ),
			'delete'        => __( 'Delete', 'wp-ever-accounting' ),
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
					"SELECT DISTINCT YEAR( issue_date ) AS year, MONTH( issue_date ) AS month
					FROM {$wpdb->prefix}ea_documents
					WHERE type = %s AND issue_date IS NOT NULL
					ORDER BY issue_date DESC",
					'invoice'
				)
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
		$date     = $item->issue_date ? esc_html( wp_date( 'd M Y', strtotime( $item->issue_date ) ) ) : '&mdash;';
		$metadata = $item->due_date ? sprintf( __( 'Due: %s', 'wp-ever-accounting' ), esc_html( wp_date( eac_date_format(), strtotime( $item->due_date ) ) ) ) : ''; // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment

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

		return $this->row_actions( $actions );
	}
}
