<?php
/**
 * Customer Admin List Table.
 *
 * @since       1.0.2
 * @subpackage  Admin/ListTables
 * @package     EverAccounting
 */

use EverAccounting\Models\Customer;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( '\EverAccounting_List_Table' ) ) {
	require_once dirname( __FILE__ ) . '/class-list-table.php';
}

/**
 * Class EverAccounting_Customer_List_Table
 *
 * @since 1.1.0
 */
class EverAccounting_Customer_List_Table extends EverAccounting_List_Table {
	/**
	 * Default number of items to show per page
	 *
	 * @since 1.0.2
	 * @var string
	 */
	public $per_page = 20;

	/**
	 * Total number of item found
	 *
	 * @since 1.0.2
	 * @var int
	 */
	public $total_count;

	/**
	 * Number of active items found
	 *
	 * @since 1.0
	 * @var string
	 */
	public $active_count;

	/**
	 *  Number of inactive items found
	 *
	 * @since 1.0
	 * @var string
	 */
	public $inactive_count;

	/**
	 * Get things started
	 *
	 * @param array $args Optional. Arbitrary display and query arguments to pass through the list table. Default empty array.
	 *
	 * @since  1.0.2
	 *
	 * @see WP_List_Table::__construct()
	 */
	public function __construct( $args = array() ) {
		$args = (array) wp_parse_args(
			$args,
			array(
				'singular' => 'customer',
				'plural'   => 'customers',
			)
		);

		parent::__construct( $args );
	}

	/**
	 * Check if there is contents in the database.
	 *
	 * @return bool
	 * @since 1.0.2
	 */
	public function is_empty() {
		global $wpdb;

		return ! (int) $wpdb->get_var( "SELECT COUNT(id) from {$wpdb->prefix}ea_contacts WHERE type='customer'" );
	}

	/**
	 * Render blank state.
	 *
	 * @return void
	 * @since 1.0.2
	 */
	protected function render_blank_state() {
		$url = eaccounting_admin_url(
			array(
				'page'   => 'ea-sales',
				'tab'    => 'customers',
				'action' => 'edit',
			)
		);
		?>
		<div class="ea-empty-table">
			<p class="ea-empty-table__message">
				<?php echo esc_html__( 'Create customers to assign revenues, and later you can filter the transactions you made with them. You can store the name, address, email, phone number, etc. of a customer.', 'wp-ever-accounting' ); ?>
			</p>
			<a href="<?php echo esc_url( $url ); ?>" class="button-primary ea-empty-table__cta"><?php esc_html_e( 'Add Customer', 'wp-ever-accounting' ); ?></a>
			<a href="https://wpeveraccounting.com/docs/general/add-customers/?utm_source=listtable&utm_medium=link&utm_campaign=admin" class="button-secondary ea-empty-table__cta" target="_blank"><?php esc_html_e( 'Learn More', 'wp-ever-accounting' ); ?></a>
		</div>
		<?php
	}

	/**
	 * Define which columns to show on this screen.
	 *
	 * @return array
	 * @since 1.0.2
	 */
	public function define_columns() {
		return array(
			'cb'      => '<input type="checkbox" />',
			'thumb'   => '<span class="ea-thumb">&nbsp;</span>',
			'name'    => __( 'Name', 'wp-ever-accounting' ),
			'email'   => __( 'Contact', 'wp-ever-accounting' ),
			'street'  => __( 'Address', 'wp-ever-accounting' ),
			'paid'    => __( 'Paid', 'wp-ever-accounting' ),
			'due'     => __( 'Receivable', 'wp-ever-accounting' ),
			'enabled' => __( 'Enabled', 'wp-ever-accounting' ),
		);
	}

	/**
	 * Define sortable columns.
	 *
	 * @return array
	 * @since 1.0.2
	 */
	protected function define_sortable_columns() {
		return array(
			'name'    => array( 'name', false ),
			'email'   => array( 'email', false ),
			'street'  => array( 'street', false ),
			'enabled' => array( 'enabled', false ),
		);
	}

	/**
	 * Define bulk actions
	 *
	 * @return array
	 * @since 1.0.2
	 */
	public function define_bulk_actions() {
		return array(
			'enable'  => __( 'Enable', 'wp-ever-accounting' ),
			'disable' => __( 'Disable', 'wp-ever-accounting' ),
			'delete'  => __( 'Delete', 'wp-ever-accounting' ),
		);
	}


	/**
	 * Define primary column.
	 *
	 * @return string
	 * @since 1.0.2
	 */
	public function get_primary_column_name() {
		return 'name';
	}


	/**
	 * Renders the checkbox column in the currencies list table.
	 *
	 * @param Customer $customer The current object.
	 *
	 * @return string Displays a checkbox.
	 * @since  1.0.2
	 */
	public function column_cb( $customer ) {
		return sprintf( '<input type="checkbox" name="customer_id[]" value="%d"/>', esc_attr( $customer->get_id() ) );
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @param Customer $customer The current object.
	 * @param string   $column_name The name of the column.
	 *
	 * @return string The column value.
	 * @since 1.0.2
	 */
	public function column_default( $customer, $column_name ) {
		$customer_id = $customer->get_id();
		switch ( $column_name ) {
			case 'thumb':
				$view_url = eaccounting_admin_url(
					array(
						'page'        => 'ea-sales',
						'tab'         => 'customers',
						'action'      => 'view',
						'customer_id' => $customer_id,
					)
				);
				$value    = '<a href="' . esc_url( $view_url ) . '"><img src="' . esc_attr( $customer->get_avatar_url() ) . '" height="36" width="36" alt="' . esc_attr( $customer->get_name() ) . '"></a>';
				break;
			case 'name':
				$view_url = eaccounting_admin_url(
					array(
						'page'        => 'ea-sales',
						'tab'         => 'customers',
						'action'      => 'view',
						'customer_id' => $customer_id,
					)
				);
				$edit_url = eaccounting_admin_url(
					array(
						'page'        => 'ea-sales',
						'tab'         => 'customers',
						'action'      => 'edit',
						'customer_id' => $customer_id,
					)
				);
				$del_url  = eaccounting_admin_url(
					array(
						'page'        => 'ea-sales',
						'tab'         => 'customers',
						'action'      => 'delete',
						'customer_id' => $customer_id,
						'_wpnonce'    => wp_create_nonce( 'customer-nonce' ),
					)
				);
				$actions  = array(
					'view'   => sprintf( '<a href="%1$s">%2$s</a>', esc_url( $view_url ), __( 'View', 'wp-ever-accounting' ) ),
					'edit'   => sprintf( '<a href="%1$s">%2$s</a>', esc_url( $edit_url ), __( 'Edit', 'wp-ever-accounting' ) ),
					'delete' => sprintf( '<a href="%1$s" class="del">%2$s</a>', esc_url( $del_url ), __( 'Delete', 'wp-ever-accounting' ) ),
				);

				$value  = '<a href="' . esc_url( $view_url ) . '"><strong>' . $customer->get_name() . '</strong></a>';
				$value .= '<br>';
				$value .= '<small class=meta>' . $customer->get_company() . '</small>';
				$value .= $this->row_actions( $actions );
				break;

			case 'email':
				if ( ! empty( $customer->get_email() ) || ! empty( $customer->get_phone() ) ) {
					$value  = ! empty( $customer->get_email() ) ? '<a href="mailto:' . sanitize_email( $customer->get_email() ) . '">' . sanitize_email( $customer->get_email() ) . '</a><br>' : '';
					$value .= ! empty( $customer->get_phone() ) ? '<span class="contact_phone">' . esc_html( $customer->get_phone() ) . '</span>' : '';
				}
				if ( empty( $customer->get_email() ) && empty( $customer->get_phone() ) ) {
					$value = '&mdash;';
				}
				break;
			case 'street':
				$value = eaccounting_format_address(
					array(
						'city'    => $customer->get_city(),
						'state'   => $customer->get_state(),
						'country' => $customer->get_country_nicename(),
					),
					','
				);
				$value = ( '' !== $value ) ? $value : '&mdash;';
				break;
			case 'enabled':
				$value  = '<label class="ea-toggle">';
				$value .= '<input type="checkbox" class="customer-status" style="" value="true" data-id="' . esc_attr( $customer->get_id() ) . '" ' . checked( $customer->is_enabled(), true, false ) . '>';
				$value .= '<span data-label-off="' . esc_attr( __( 'No', 'wp-ever-accounting' ) ) . '" data-label-on="' . esc_attr( __( 'Yes', 'wp-ever-accounting' ) ) . '" class="ea-toggle-slider"></span>';
				$value .= '</label>';
				break;
			case 'due':
				$value = eaccounting_format_price( $customer->get_total_due() );
				break;
			case 'paid':
				$value = eaccounting_format_price( $customer->get_total_paid() );
				break;
			case 'actions':
				$edit_url = eaccounting_admin_url(
					array(
						'tab'         => 'customers',
						'action'      => 'edit',
						'customer_id' => $customer_id,
					)
				);
				$del_url  = eaccounting_admin_url(
					array(
						'tab'         => 'customers',
						'action'      => 'delete',
						'customer_id' => $customer_id,
						'_wpnonce'    => wp_create_nonce( 'customer-nonce' ),
					)
				);
				$actions  = array(
					'edit'   => sprintf( '<a href="%s" class="dashicons dashicons-edit"></a>', esc_url( $edit_url ) ),
					'delete' => sprintf( '<a href="%s" class="dashicons dashicons-trash del"></a>', esc_url( $del_url ) ),
				);
				$value    = $this->row_actions( $actions );
				break;
			default:
				return parent::column_default( $customer, $column_name );
		}

		return apply_filters( 'eaccounting_customer_list_table_' . $column_name, $value, $customer );
	}

	/**
	 * Renders the message to be displayed when there are no items.
	 *
	 * @return void
	 * @since  1.0.2
	 */
	public function no_items() {
		esc_html_e( 'There is no customers found.', 'wp-ever-accounting' );
	}

	/**
	 * Process the bulk actions
	 *
	 * @return void
	 * @since 1.0.2
	 */
	public function process_bulk_action() {
		$nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'bulk-customers' ) && ! wp_verify_nonce( $nonce, 'customer-nonce' ) ) {
			return;
		}

		$ids = isset( $_GET['customer_id'] ) ? wp_parse_id_list( wp_unslash( $_GET['customer_id'] ) ) : false;

		if ( ! is_array( $ids ) ) {
			$ids = array( $ids );
		}

		$ids = array_map( 'absint', $ids );
		$ids = array_filter( $ids );

		if ( empty( $ids ) ) {
			return;
		}

		$action = $this->current_action();
		foreach ( $ids as $id ) {
			switch ( $action ) {
				case 'enable':
					eaccounting_insert_customer(
						array(
							'id'      => $id,
							'enabled' => '1',
						)
					);
					break;
				case 'disable':
					eaccounting_insert_customer(
						array(
							'id'      => $id,
							'enabled' => '0',
						)
					);
					break;
				case 'delete':
					eaccounting_delete_customer( $id );
					break;
				default:
					do_action( 'eaccounting_customers_do_bulk_action_' . $this->current_action(), $id );
			}
		}

		if ( $nonce ) {
			wp_safe_redirect(
				remove_query_arg(
					array(
						'customer_id',
						'action',
						'_wpnonce',
						'_wp_http_referer',
						'action2',
						'paged',
					)
				)
			);
			exit();
		}
	}

	/**
	 * Retrieve the view types
	 *
	 * @access public
	 * @return array $views All the views available
	 * @since 1.0.2
	 */
	public function get_views() {
		$base           = eaccounting_admin_url( array( 'tab' => 'customers' ) );
		$current        = filter_input( INPUT_GET, 'status', FILTER_SANITIZE_STRING );
		$total_count    = '&nbsp;<span class="count">(' . $this->total_count . ')</span>';
		$active_count   = '&nbsp;<span class="count">(' . $this->active_count . ')</span>';
		$inactive_count = '&nbsp;<span class="count">(' . $this->inactive_count . ')</span>';

		$views = array(
			'all'      => sprintf( '<a href="%s"%s>%s</a>', esc_url( remove_query_arg( 'status', $base ) ), 'all' === $current || '' === $current ? ' class="current"' : '', __( 'All', 'wp-ever-accounting' ) . $total_count ),
			'active'   => sprintf( '<a href="%s"%s>%s</a>', esc_url( add_query_arg( 'status', 'active', $base ) ), 'active' === $current ? ' class="current"' : '', __( 'Active', 'wp-ever-accounting' ) . $active_count ),
			'inactive' => sprintf( '<a href="%s"%s>%s</a>', esc_url( add_query_arg( 'status', 'inactive', $base ) ), 'inactive' === $current ? ' class="current"' : '', __( 'Inactive', 'wp-ever-accounting' ) . $inactive_count ),
		);
		return $views;
	}

	/**
	 * Retrieve all the data for the table.
	 * Setup the final data for the table
	 *
	 * @return void
	 * @since 1.0.2
	 */
	public function prepare_items() {
		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		$this->process_bulk_action();

		$page    = filter_input( INPUT_GET, 'paged', FILTER_SANITIZE_NUMBER_INT, array( 'options' => array( 'default' => 1 ) ) );
		$search  = filter_input( INPUT_GET, 's', FILTER_SANITIZE_STRING );
		$order   = filter_input( INPUT_GET, 'order', FILTER_SANITIZE_STRING, array( 'options' => array( 'default' => 'DESC' ) ) );
		$orderby = filter_input( INPUT_GET, 'orderby', FILTER_SANITIZE_STRING, array( 'options' => array( 'default' => 'id' ) ) );
		$status  = filter_input( INPUT_GET, 'status', FILTER_SANITIZE_STRING );

		$per_page = $this->per_page;

		$args = wp_parse_args(
			$this->query_args,
			array(
				'number'   => $per_page,
				'offset'   => $per_page * ( $page - 1 ),
				'per_page' => $per_page,
				'page'     => $page,
				'search'   => $search,
				'status'   => $status,
				'orderby'  => eaccounting_clean( $orderby ),
				'order'    => eaccounting_clean( $order ),
				'type'     => 'customer',
			)
		);

		$args = apply_filters( 'eaccounting_customer_table_query_args', $args, $this );

		$this->items = eaccounting_get_customers( $args );

		$this->active_count   = eaccounting_get_customers(
			array_merge(
				$args,
				array(
					'status'      => 'active',
					'count_total' => true,
				)
			)
		);
		$this->inactive_count = eaccounting_get_customers(
			array_merge(
				$args,
				array(
					'status'      => 'inactive',
					'count_total' => true,
				)
			)
		);

		$this->total_count = $this->active_count + $this->inactive_count;
		switch ( $status ) {
			case 'active':
				$total_items = $this->active_count;
				break;
			case 'inactive':
				$total_items = $this->inactive_count;
				break;
			case 'any':
			default:
				$total_items = $this->total_count;
				break;
		}

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total_items / $per_page ),
			)
		);
	}
}
