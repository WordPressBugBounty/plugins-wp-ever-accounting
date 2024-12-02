<?php

namespace EverAccounting\Admin\ListTables;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Class Table.
 *
 * @since 1.0.0
 * @package EverAccounting\Admin\ListTables
 */
abstract class ListTable extends \WP_List_Table {
	/**
	 * Current page URL.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $base_url;

	/**
	 * Constructor.
	 *
	 * @param array $args An associative array of arguments.
	 *
	 * @see WP_List_Table::__construct() for more information on default arguments.
	 * @since 1.0.0
	 */
	public function __construct( $args = array() ) {
		parent::__construct( $args );
		remove_filter( "manage_{$this->screen->id}_columns", array( $this, 'get_columns' ), 0 );
	}

	/**
	 * Return the sortable column specified for this request to order the results by, if any.
	 *
	 * @return string
	 */
	protected function get_request_orderby() {
		wp_verify_nonce( '_wpnonce' );
		$orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : '';

		return $orderby;
	}

	/**
	 * Return the order specified for this request, if any.
	 *
	 * @return string
	 */
	protected function get_request_order() {
		if ( ! empty( $_GET['order'] ) && 'desc' === strtolower( sanitize_text_field( wp_unslash( $_GET['order'] ) ) ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$order = 'DESC';
		} else {
			$order = 'ASC';
		}

		return $order;
	}

	/**
	 * Return the status filter for this request, if any.
	 *
	 * @param string $fallback Default status.
	 *
	 * @since 1.2.1
	 * @return string
	 */
	protected function get_request_status( $fallback = null ) {
		wp_verify_nonce( '_wpnonce' );
		$status = ( ! empty( $_GET['status'] ) ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';

		return empty( $status ) ? $fallback : $status;
	}

	/**
	 * Return the type filter for this request, if any.
	 *
	 * @param string $fallback Default type.
	 *
	 * @since 1.2.1
	 * @return string
	 */
	protected function get_request_type( $fallback = null ) {
		wp_verify_nonce( '_wpnonce' );
		$type = ( ! empty( $_GET['type'] ) ) ? sanitize_text_field( wp_unslash( $_GET['type'] ) ) : '';

		return empty( $type ) ? $fallback : $type;
	}

	/**
	 * Return the search filter for this request, if any.
	 *
	 * @since 1.2.1
	 * @return string
	 */
	public function get_request_search() {
		wp_verify_nonce( '_wpnonce' );

		return ! empty( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
	}

	/**
	 * Checks if the current request has a bulk action. If that is the case it will validate and will
	 * execute the bulk method handler. Regardless if the action is valid or not it will redirect to
	 * the previous page removing the current arguments that makes this request a bulk action.
	 */
	protected function process_actions() {
		$this->_column_headers = array( $this->get_columns(), get_hidden_columns( $this->screen ), $this->get_sortable_columns() );

		// Detect when a bulk action is being triggered.
		$action = $this->current_action();
		if ( ! empty( $action ) && array_key_exists( $action, $this->get_bulk_actions() ) ) {

			check_admin_referer( 'bulk-' . $this->_args['plural'] );

			$ids    = isset( $_GET['id'] ) ? map_deep( wp_unslash( $_GET['id'] ), 'intval' ) : array();
			$ids    = wp_parse_id_list( $ids );
			$method = 'bulk_' . $action;
			if ( array_key_exists( $action, $this->get_bulk_actions() ) && method_exists( $this, $method ) && ! empty( $ids ) ) {
				$this->$method( $ids );
			}
		}

		if ( isset( $_GET['_wpnonce'] ) && isset( $_SERVER['REQUEST_URI'] ) ) {
			wp_safe_redirect(
				remove_query_arg(
					array( '_wp_http_referer', '_wpnonce', 'id', 'action', 'action2' ),
					esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) )
				)
			);
			exit;
		}
	}

	/**
	 * Render column metadata.
	 *
	 * @param array|string $items Items to render.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function column_metadata( $items ) {
		if ( ! empty( $items ) ) {
			$items    = is_array( $items ) ? $items : array( $items );
			$items    = array_filter( $items );
			$metadata = sprintf( '<div class="column-metadata"><span>%s</span></div>', implode( '</span><span>', $items ) );

			return wp_kses_post( $metadata );
		}

		return '';
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @param Object $item The current item.
	 * @param string $column_name The name of the column.
	 *
	 * @since 1.0.0
	 * @return string The column value.
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'status':
				$statuses = array(
					'active'   => __( 'Active', 'wp-ever-accounting' ),
					'inactive' => __( 'Inactive', 'wp-ever-accounting' ),
				);
				$status   = isset( $item->$column_name ) ? $item->$column_name : '';
				$label    = isset( $statuses[ $status ] ) ? $statuses[ $status ] : '';

				return sprintf( '<span class="eac-status is--%1$s">%2$s</span>', esc_attr( $status ), esc_html( $label ) );

			default:
				if ( is_object( $item ) && isset( $item->$column_name ) ) {
					return empty( $item->$column_name ) ? '&mdash;' : wp_kses_post( $item->$column_name );
				}
		}

		return '&mdash;';
	}

	/**
	 * Category filter
	 *
	 * @param string $type type of category.
	 *
	 * @since 1.2.1
	 * @return void
	 */
	protected function category_filter( $type ) {
		$category_id = filter_input( INPUT_GET, 'category_id', FILTER_SANITIZE_NUMBER_INT );
		$category    = empty( $category_id ) ? null : EAC()->categories->get( $category_id );
		?>
		<select class="eac_select2" name="category_id" id="filter-by-category" data-action="eac_json_search" data-type="category" data-subtype="<?php echo esc_attr( $type ); ?>" data-placeholder="<?php esc_attr_e( 'Filter by category', 'wp-ever-accounting' ); ?>">
			<?php if ( ! empty( $category ) ) : ?>
				<option value="<?php echo esc_attr( $category->id ); ?>" <?php selected( $category_id, $category->id ); ?>>
					<?php echo esc_html( $category->name ); ?>
				</option>
			<?php endif; ?>
		</select>
		<?php
	}

	/**
	 * Account filter
	 *
	 * @since 1.2.1
	 * @return void
	 */
	protected function account_filter() {
		$account_id = filter_input( INPUT_GET, 'account_id', FILTER_SANITIZE_NUMBER_INT );
		$account    = empty( $account_id ) ? null : EAC()->accounts->get( $account_id );
		?>
		<select class="eac_select2" name="account_id" id="filter-by-account" data-action="eac_json_search" data-type="account" data-placeholder="<?php esc_attr_e( 'Filter by account', 'wp-ever-accounting' ); ?>">
			<?php if ( ! empty( $account ) ) : ?>
				<option value="<?php echo esc_attr( $account->id ); ?>" <?php selected( $account_id, $account->id ); ?>>
					<?php echo esc_html( $account->name ); ?>
				</option>
			<?php endif; ?>
		</select>
		<?php
	}

	/**
	 * Contact filter
	 *
	 * @param string $type type of contact.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	protected function contact_filter( $type ) {
		if ( 'customer' === $type ) {
			$customer_id = filter_input( INPUT_GET, 'customer_id', FILTER_SANITIZE_NUMBER_INT );
			$customer    = empty( $customer_id ) ? null : EAC()->customers->get( $customer_id );
			?>
			<select class="eac_select2" name="customer_id" id="filter-by-customer" data-action="eac_json_search" data-type="customer" data-placeholder="<?php esc_attr_e( 'Filter by customer', 'wp-ever-accounting' ); ?>">
				<?php if ( ! empty( $customer ) ) : ?>
					<option value="<?php echo esc_attr( $customer->id ); ?>" <?php selected( $customer_id, $customer->id ); ?>>
						<?php echo esc_html( $customer->name ); ?>
					</option>
				<?php endif; ?>
			</select>
			<?php
		} else {
			$vendor_id = filter_input( INPUT_GET, 'vendor_id', FILTER_SANITIZE_NUMBER_INT );
			$vendor    = empty( $vendor_id ) ? null : EAC()->vendors->get( $vendor_id );
			?>
			<select class="eac_select2" name="vendor_id" id="filter-by-vendor" data-action="eac_json_search" data-type="vendor" data-placeholder="<?php esc_attr_e( 'Filter by vendor', 'wp-ever-accounting' ); ?>">
				<?php if ( ! empty( $vendor ) ) : ?>
					<option value="<?php echo esc_attr( $vendor->id ); ?>" <?php selected( $vendor_id, $vendor->id ); ?>>
						<?php echo esc_html( $vendor->name ); ?>
					</option>
				<?php endif; ?>
			</select>
			<?php
		}
	}

	/**
	 * Date filter
	 *
	 * @param array $months Months.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	protected function date_filter( $months ) {
		$m           = filter_input( INPUT_GET, 'm', FILTER_SANITIZE_NUMBER_INT );
		$month_count = count( $months );
		if ( ! $month_count || ( 1 === $month_count && 0 === (int) $months[0]->month ) ) {
			return;
		}
		?>
		<select name="m" id="filter-by-date" class="eac_select2" data-placeholder="<?php esc_attr_e( 'Filter by date', 'wp-ever-accounting' ); ?>">
			<option<?php selected( $m, 0 ); ?> style='display: none'><?php esc_attr_e( 'Filter by date', 'wp-ever-accounting' ); ?></option>
			<?php
			foreach ( $months as $arc_row ) {
				if ( 0 === (int) $arc_row->year || 0 === (int) $arc_row->month ) {
					continue;
				}

				$month = zeroise( $arc_row->month, 2 );
				$year  = $arc_row->year;

				printf(
					"<option %s value='%s'>%s</option>\n",
					selected( $m, $year . $month, false ),
					esc_attr( $arc_row->year . $month ),
					esc_html( wp_date( 'M Y', strtotime( $year . '-' . $month . '-01' ) ) )
				);
			}
			?>
		</select>
		<?php
	}
}
