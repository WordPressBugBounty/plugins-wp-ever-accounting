<?php

namespace EverAccounting\Admin\ListTables;

use EverAccounting\Models\Customer;

defined( 'ABSPATH' ) || exit;

/**
 * Class Customers.
 *
 * @since 1.0.0
 * @package EverAccounting\Admin\ListTables
 */
class Customers extends ListTable {
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
					'singular' => 'customer',
					'plural'   => 'customers',
					'screen'   => get_current_screen(),
					'args'     => array(),
				)
			)
		);
		$this->base_url = admin_url( 'admin.php?page=eac-sales&tab=customers' );
	}

	/**
	 * Prepares the list for display.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function prepare_items() {
		$this->process_actions();
		$per_page = $this->get_items_per_page( 'eac_customers_per_page', 20 );
		$paged    = $this->get_pagenum();
		$search   = $this->get_request_search();
		$order_by = $this->get_request_orderby();
		$order    = $this->get_request_order();
		$args     = array(
			'limit'   => $per_page,
			'page'    => $paged,
			'search'  => $search,
			'orderby' => $order_by,
			'order'   => $order,
		);
		/**
		 * Filter the query arguments for the list table.
		 *
		 * @param array $args An associative array of arguments.
		 *
		 * @since 1.0.0
		 */
		$args        = apply_filters( 'eac_customers_table_query_args', $args );
		$this->items = EAC()->customers->query( $args );
		$total       = EAC()->customers->query( $args, true );

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
			if ( EAC()->customers->delete( $id ) ) {
				++$performed;
			}
		}
		if ( ! empty( $performed ) ) {
			// translators: %s: number of customers.
			EAC()->flash->success( sprintf( __( '%s customer(s) deleted successfully.', 'wp-ever-accounting' ), number_format_i18n( $performed ) ) );
		}
	}


	/**
	 * Outputs 'results' message.
	 *
	 * @since 1.0.0
	 */
	public function no_items() {
		esc_html_e( 'No customers found.', 'wp-ever-accounting' );
	}

	/**
	 * Retrieves an associative array of bulk actions available on this table.
	 *
	 * @since 1.0.0
	 * @return array Array of bulk action labels keyed by their action.
	 */
	protected function get_bulk_actions() {
		return array(
			'delete' => __( 'Delete', 'wp-ever-accounting' ),
		);
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
		static $has_items;
		if ( ! isset( $has_items ) ) {
			$has_items = $this->has_items();
		}

		if ( 'top' === $which ) {
			ob_start();
			$this->country_filter( 'active' );
			$output = ob_get_clean();
			if ( ! empty( $output ) && $this->has_items() ) {
				echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				submit_button( __( 'Filter', 'wp-ever-accounting' ), 'alignleft', 'filter_action', false );
			}
		}
	}

	/**
	 * Gets a list of columns for the list table.
	 *
	 * @since 1.0.0
	 * @return string[] Array of column titles keyed by their column name.
	 */
	public function get_columns() {
		return array(
			'cb'      => '<input type="checkbox" />',
			'name'    => __( 'Name', 'wp-ever-accounting' ),
			'email'   => __( 'Email', 'wp-ever-accounting' ),
			'phone'   => __( 'Phone', 'wp-ever-accounting' ),
			'country' => __( 'Country', 'wp-ever-accounting' ),
			'date'    => __( 'Date', 'wp-ever-accounting' ),
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
			'name'    => array( 'name', false ),
			'email'   => array( 'email', false ),
			'phone'   => array( 'phone', false ),
			'country' => array( 'country', false ),
			'due'     => array( 'due', false ),
			'date'    => array( 'date_created', false ),
		);
	}

	/**
	 * Define primary column.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_primary_column_name() {
		return 'name';
	}

	/**
	 * Renders the checkbox column.
	 *
	 * @param Customer $item The current object.
	 *
	 * @since  1.0.0
	 * @return string Displays a checkbox.
	 */
	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="id[]" value="%d"/>', esc_attr( $item->id ) );
	}

	/**
	 * Renders the name column.
	 *
	 * @param Customer $item The current object.
	 *
	 * @since  1.0.0
	 * @return string Displays the name.
	 */
	public function column_name( $item ) {
		return sprintf(
			'<a class="row-title" href="%s">%s</a>',
			esc_url( $item->get_view_url() ),
			wp_kses_post( $item->name )
		);
	}

	/**
	 * Renders the email column.
	 *
	 * @param Customer $item The current object.
	 *
	 * @since 1.0.0
	 * @return string Displays the email.
	 */
	public function column_email( $item ) {
		return $item->email ? esc_html( $item->email ) : '&mdash;';
	}

	/**
	 * Renders the phone column.
	 *
	 * @param Customer $item The current object.
	 *
	 * @since 1.0.0
	 * @return string Displays the phone.
	 */
	public function column_phone( $item ) {
		return $item->phone ? esc_html( $item->phone ) : '&mdash;';
	}

	/**
	 * Renders the country column.
	 *
	 * @param Customer $item The current object.
	 *
	 * @since 1.0.0
	 * @return string Displays the country.
	 */
	public function column_country( $item ) {
		return $item->country_name ? esc_html( $item->country_name ) : '&mdash;';
	}

	/**
	 * Renders the date column.
	 *
	 * @param Customer $item The current object.
	 *
	 * @since 1.0.0
	 * @return string Displays the date.
	 */
	public function column_date( $item ) {
		return wp_date( eac_date_format(), strtotime( $item->date_created ) );
	}

	/**
	 * Generates and displays row actions links.
	 *
	 * @param Customer $item The customer object.
	 * @param string   $column_name Current column name.
	 * @param string   $primary Primary column name.
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
				'<a href="%s" class="del">%s</a>',
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
