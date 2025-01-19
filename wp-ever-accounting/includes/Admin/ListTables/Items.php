<?php

namespace EverAccounting\Admin\ListTables;

use EverAccounting\Models\Item;

defined( 'ABSPATH' ) || exit;

/**
 * Class Items.
 *
 * @since 1.0.0
 * @package EverAccounting\Admin\ListTables
 */
class Items extends ListTable {
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
					'singular' => 'item',
					'plural'   => 'items',
					'screen'   => get_current_screen(),
					'args'     => array(),
				)
			)
		);

		$this->base_url = admin_url( 'admin.php?page=eac-items&tab=items' );
	}

	/**
	 * Prepares the list for display.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function prepare_items() {
		$this->process_actions();
		$this->_column_headers = array( $this->get_columns(), get_hidden_columns( $this->screen ), $this->get_sortable_columns() );
		$per_page              = $this->get_items_per_page( 'eac_items_per_page', 20 );
		$paged                 = $this->get_pagenum();
		$search                = $this->get_request_search();
		$order_by              = $this->get_request_orderby();
		$order                 = $this->get_request_order();
		$args                  = array(
			'limit'       => $per_page,
			'page'        => $paged,
			'search'      => $search,
			'orderby'     => $order_by,
			'order'       => $order,
			'type'        => filter_input( INPUT_GET, 'type', FILTER_SANITIZE_FULL_SPECIAL_CHARS ),
			'category_id' => filter_input( INPUT_GET, 'category_id', FILTER_VALIDATE_INT ),
		);

		/**
		 * Filter the query arguments for the list table.
		 *
		 * @param array $args An associative array of arguments.
		 *
		 * @since 1.0.0
		 */
		$args        = apply_filters( 'eac_items_table_query_args', $args );
		$this->items = Item::results( $args );
		$total       = Item::count( $args );
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
			if ( EAC()->items->delete( $id ) ) {
				++$performed;
			}
		}
		if ( ! empty( $performed ) ) {
			// translators: %s: number of items deleted.
			EAC()->flash->success( sprintf( __( '%s item(s) deleted successfully.', 'wp-ever-accounting' ), number_format_i18n( $performed ) ) );
		}
	}

	/**
	 * Outputs 'no items' message.
	 *
	 * @since 1.0.0
	 */
	public function no_items() {
		esc_html_e( 'No items found.', 'wp-ever-accounting' );
	}

	/**
	 * Returns an associative array listing all the views that can be used
	 * with this table.
	 *
	 * @since 1.0.0
	 *
	 * @return string[] An array of HTML links keyed by their view.
	 */
	protected function get_views() {
		$current     = $this->get_request_type( 'all' );
		$types_links = array();
		$types       = array_merge( array( 'all' => __( 'All', 'wp-ever-accounting' ) ), EAC()->items->get_types() );

		foreach ( $types as $type => $label ) {
			$link  = 'all' === $type ? $this->base_url : add_query_arg( 'type', $type, $this->base_url );
			$args  = 'all' === $type ? array() : array( 'type' => $type );
			$count = Item::count( $args );
			$label = sprintf( '%s <span class="count">(%s)</span>', esc_html( $label ), number_format_i18n( $count ) );

			$types_links[ $type ] = array(
				'url'     => $link,
				'label'   => $label,
				'current' => $current === $type,
			);
		}

		return $this->get_views_links( $types_links );
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
		static $has_items;
		if ( ! isset( $has_items ) ) {
			$has_items = $this->has_items();
		}

		echo '<div class="alignleft actions">';

		if ( 'top' === $which ) {
			$this->category_filter( 'item' );
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
			'cb'           => '<input type="checkbox" />',
			'name'         => __( 'Name', 'wp-ever-accounting' ),
			'type'         => __( 'Type', 'wp-ever-accounting' ),
			'category'     => __( 'Category', 'wp-ever-accounting' ),
			'cost'         => __( 'Cost', 'wp-ever-accounting' ),
			'price'        => __( 'Price', 'wp-ever-accounting' ),
			'date_created' => __( 'Date', 'wp-ever-accounting' ),
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
			'name'         => array( 'name', false ),
			'type'         => array( 'type', false ),
			'category'     => array( 'category_id', false ),
			'cost'         => array( 'cost', false ),
			'price'        => array( 'price', false ),
			'date_created' => array( 'date_created', false ),
		);
	}

	/**
	 * Define primary column.
	 *
	 * @since 1.0.2
	 * @return string
	 */
	public function get_primary_column_name() {
		return 'name';
	}

	/**
	 * Renders the checkbox column.
	 *
	 * @param Item $item The current object.
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
	 * @param Item $item The current object.
	 *
	 * @since  1.0.0
	 * @return string Displays the name.
	 */
	public function column_name( $item ) {
		return sprintf(
			'<a class="row-title" href="%s">%s</a>',
			esc_url( $item->get_edit_url() ),
			wp_kses_post( $item->name )
		);
	}

	/**
	 * Renders the type column.
	 *
	 * @param Item $item The current object.
	 *
	 * @since  1.0.0
	 * @return string Displays the type.
	 */
	public function column_type( $item ) {
		$types = EAC()->items->get_types();

		return isset( $types[ $item->type ] ) ? esc_html( $types[ $item->type ] ) : $item->type;
	}

	/**
	 * Renders the category column.
	 *
	 * @param Item $item The current object.
	 *
	 * @since  1.0.0
	 * @return string Displays the category.
	 */
	public function column_category( $item ) {
		if ( $item->category ) {
			return sprintf( '<a href="%s">%s</a>', esc_url( add_query_arg( 'category_id', $item->category->id, $this->base_url ) ), wp_kses_post( $item->category->name ) );
		}

		return '&mdash;';
	}

	/**
	 * Renders the price column.
	 *
	 * @param Item $item The current object.
	 *
	 * @since  1.0.0
	 * @return string Displays the price.
	 */
	public function column_price( $item ) {
		return esc_html( $item->formatted_price );
	}

	/**
	 * Renders the cost column.
	 *
	 * @param Item $item The current object.
	 *
	 * @since  1.0.0
	 * @return string Displays the cost.
	 */
	public function column_cost( $item ) {
		return esc_html( $item->formatted_cost );
	}

	/**
	 * Renders the date column.
	 *
	 * @param Item $item The current object.
	 *
	 * @since  1.0.0
	 * @return string Displays the date.
	 */
	public function column_date_created( $item ) {
		return esc_html( wp_date( 'Y-m-d', strtotime( $item->date_created ) ) );
	}

	/**
	 * Generates and displays row actions links.
	 *
	 * @param Item   $item The comment object.
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
			'id'     => sprintf( '#%d', esc_attr( $item->id ) ),
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

		if ( ! current_user_can( 'eac_delete_items' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Reason: This is a custom capability.
			unset( $actions['delete'] );
		}

		if ( ! current_user_can( 'eac_edit_items' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Reason: This is a custom capability.
			unset( $actions['edit'] );
		}

		return $this->row_actions( $actions );
	}
}
