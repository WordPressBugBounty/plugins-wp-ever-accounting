<?php

namespace EverAccounting\API;

use EverAccounting\Models\Item;

defined( 'ABSPATH' ) || exit;

/**
 * Class Items
 *
 * @since 2.0.0
 * @package EverAccounting\API
 */
class Items extends Controller {
	/**
	 * Route base.
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	protected $rest_base = 'items';

	/**
	 * Registers the routes for the objects of the controller.
	 *
	 * @see register_rest_route()
	 * @since 2.0.0
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => $this->get_collection_params(),
				),
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_item' ),
					'permission_callback' => array( $this, 'create_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( \WP_REST_Server::CREATABLE ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		$get_item_args = array(
			'context' => $this->get_context_param( array( 'default' => 'view' ) ),
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			array(
				'args'   => array(
					'id' => array(
						'description' => __( 'Unique identifier for the item.', 'wp-ever-accounting' ),
					),
				),
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
					'args'                => $get_item_args,
				),
				array(
					'methods'             => \WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_item' ),
					'permission_callback' => array( $this, 'update_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( \WP_REST_Server::EDITABLE ),
				),
				array(
					'methods'             => \WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_item' ),
					'permission_callback' => array( $this, 'delete_item_permissions_check' ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	/**
	 * Checks if a given request has access to read items.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 2.0.0
	 * @return true|\WP_Error True, if the request has read access, WP_Error object otherwise.
	 */
	public function get_items_permissions_check( $request ) {
		if ( ! current_user_can( 'eac_read_items' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability
			return new \WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to view items.', 'wp-ever-accounting' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Checks if a given request has access to create an item.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 2.0.0
	 * @return true|\WP_Error True, if the request has read access, WP_Error object otherwise.
	 */
	public function create_item_permissions_check( $request ) {
		if ( ! current_user_can( 'eac_edit_items' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability
			return new \WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to create items.', 'wp-ever-accounting' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Checks if a given request has access to read a item.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 2.0.0
	 * @return true|\WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function get_item_permissions_check( $request ) {
		$item = EAC()->items->get( $request['id'] );

		if ( empty( $item ) || ! current_user_can( 'eac_read_items' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability
			return new \WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to view this item.', 'wp-ever-accounting' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Checks if a given request has access to update an item.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 2.0.0
	 * @return true|\WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function update_item_permissions_check( $request ) {
		$item = EAC()->items->get( $request['id'] );

		if ( empty( $item ) || ! current_user_can( 'eac_edit_items' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability
			return new \WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to update this item.', 'wp-ever-accounting' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Checks if a given request has access to delete a item.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 2.0.0
	 * @return true|\WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function delete_item_permissions_check( $request ) {
		$item = EAC()->items->get( $request['id'] );

		if ( empty( $item ) || ! current_user_can( 'eac_delete_items' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability
			return new \WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to delete this item.', 'wp-ever-accounting' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Retrieves a list of items.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 2.0.0
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_items( $request ) {
		$params = $this->get_collection_params();
		$args   = array();
		foreach ( $params as $key => $value ) {
			if ( isset( $request[ $key ] ) ) {
				$args[ $key ] = $request[ $key ];
			}
		}

		/**
		 * Filters the query arguments for a request.
		 *
		 * Enables adding extra arguments or setting defaults for a item request.
		 *
		 * @param array            $args Key value array of query var to query value.
		 * @param \WP_REST_Request $request The request used.
		 *
		 * @since 2.0.0
		 */
		$args      = apply_filters( 'eac_rest_item_query', $args, $request );
		$items     = EAC()->items->query( $args );
		$total     = EAC()->items->query( $args, true );
		$page      = isset( $request['page'] ) ? absint( $request['page'] ) : 1;
		$max_pages = ceil( $total / (int) $args['per_page'] );

		$results = array();
		foreach ( $items as $item ) {
			$data      = $this->prepare_item_for_response( $item, $request );
			$results[] = $this->prepare_response_for_collection( $data );
		}

		$response = rest_ensure_response( $results );

		$response->header( 'X-WP-Total', (int) $total );
		$response->header( 'X-WP-TotalPages', (int) $max_pages );

		$request_params = $request->get_query_params();
		$base           = add_query_arg( urlencode_deep( $request_params ), rest_url( sprintf( '%s/%s', $this->namespace, $this->rest_base ) ) );

		if ( $page > 1 ) {
			$prev_page = $page - 1;

			if ( $prev_page > $max_pages ) {
				$prev_page = $max_pages;
			}

			$prev_link = add_query_arg( 'page', $prev_page, $base );
			$response->link_header( 'prev', $prev_link );
		}
		if ( $max_pages > $page ) {
			$next_page = $page + 1;
			$next_link = add_query_arg( 'page', $next_page, $base );

			$response->link_header( 'next', $next_link );
		}

		return $response;
	}

	/**
	 * Retrieves a single item.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 2.0.0
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_item( $request ) {
		$item = EAC()->items->get( $request['id'] );
		$data = $this->prepare_item_for_response( $item, $request );

		return rest_ensure_response( $data );
	}

	/**
	 * Creates a single item.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 2.0.0
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function create_item( $request ) {
		if ( ! empty( $request['id'] ) ) {
			return new \WP_Error(
				'rest_exists',
				__( 'Cannot create existing item.', 'wp-ever-accounting' ),
				array( 'status' => 400 )
			);
		}

		$data = $this->prepare_item_for_database( $request );
		if ( is_wp_error( $data ) ) {
			return $data;
		}

		$item = EAC()->items->insert( $data );
		if ( is_wp_error( $item ) ) {
			return $item;
		}

		$response = $this->prepare_item_for_response( $item, $request );
		$response = rest_ensure_response( $response );

		$response->set_status( 201 );
		return $response;
	}

	/**
	 * Updates a single item.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 2.0.0
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function update_item( $request ) {
		$item = EAC()->items->get( $request['id'] );
		$data = $this->prepare_item_for_database( $request );
		if ( is_wp_error( $data ) ) {
			return $data;
		}

		$item = $item->fill( $data )->save();
		if ( is_wp_error( $item ) ) {
			return $item;
		}

		$response = $this->prepare_item_for_response( $item, $request );
		$response = rest_ensure_response( $response );

		return $response;
	}

	/**
	 * Deletes a single item.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 2.0.0
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function delete_item( $request ) {
		$item = EAC()->items->get( $request['id'] );
		$request->set_param( 'context', 'edit' );
		$data = $this->prepare_item_for_response( $item, $request );

		if ( ! $item->delete() ) {
			return new \WP_Error(
				'rest_cannot_delete',
				__( 'The item cannot be deleted.', 'wp-ever-accounting' ),
				array( 'status' => 500 )
			);
		}

		$response = new \WP_REST_Response();
		$response->set_data(
			array(
				'deleted'  => true,
				'previous' => $this->prepare_response_for_collection( $data ),
			)
		);

		return $response;
	}

	/**
	 * Prepares a single item output for response.
	 *
	 * @param Item             $item Item object.
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @since 2.0.0
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function prepare_item_for_response( $item, $request ) {
		$data = array();

		foreach ( array_keys( $this->get_schema_properties() ) as $key ) {
			switch ( $key ) {
				case 'category':
					$value = $item->category_id ? $this->get_endpoint_data( sprintf( '/%s/categories/%d', $this->namespace, $item->category_id ) ) : null;
					break;
				case 'taxes':
					$value = array();
					if ( ! empty( $item->taxes ) ) {
						$properties = array_keys( $this->get_schema_properties()[ $key ]['items'] );
						$value      = array_map(
							function ( $tax ) use ( $properties ) {
								return array_intersect_key( $tax->to_array(), array_flip( $properties ) );
							},
							$item->taxes
						);
					}
					break;
				case 'date_created':
				case 'date_updated':
					$value = $this->prepare_date_response( $item->$key );
					break;
				default:
					$value = $item->$key;
					break;
			}

			$data[ $key ] = $value;
		}

		$context  = ! empty( $request['context'] ) ? $request['context'] : 'view';
		$data     = $this->add_additional_fields_to_object( $data, $request );
		$data     = $this->filter_response_by_context( $data, $context );
		$response = rest_ensure_response( $data );

		/**
		 * Filter item data returned from the REST API.
		 *
		 * @param \WP_REST_Response $response The response object.
		 * @param Item              $item Item object used to create response.
		 * @param \WP_REST_Request  $request Request object.
		 */
		return apply_filters( 'eac_rest_prepare_item', $response, $item, $request );
	}

	/**
	 * Prepares a single item for create or update.
	 *
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @since 2.0.0
	 * @return array|\WP_Error Item object or WP_Error.
	 */
	protected function prepare_item_for_database( $request ) {
		$schema    = $this->get_item_schema();
		$data_keys = array_keys( array_filter( $schema['properties'], array( $this, 'filter_writable_props' ) ) );
		$props     = array();
		// Handle all writable props.
		foreach ( $data_keys as $key ) {
			$value = $request[ $key ];
			if ( ! is_null( $value ) ) {
				switch ( $key ) {
					default:
						$props[ $key ] = $value;
						break;
				}
			}
		}

		/**
		 * Filters item before it is inserted via the REST API.
		 *
		 * @param array            $props Item props.
		 * @param \WP_REST_Request $request Request object.
		 */
		return apply_filters( 'eac_rest_pre_insert_item', $props, $request );
	}

	/**
	 * Retrieves the item's schema, conforming to JSON Schema.
	 *
	 * @since 2.0.0
	 * @return array Item schema data.
	 */
	public function get_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => __( 'Item', 'wp-ever-accounting' ),
			'type'       => 'object',
			'properties' => array(
				'id'           => array(
					'description' => __( 'Unique identifier for the item.', 'wp-ever-accounting' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'embed', 'edit' ),
					'readonly'    => true,
					'arg_options' => array(
						'sanitize_callback' => 'intval',
					),
				),
				'type'         => array(
					'description' => __( 'Item type.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'enum'        => array_keys( EAC()->items->get_types() ),
					'context'     => array( 'view', 'edit' ),
					'required'    => true,
				),
				'name'         => array(
					'description' => __( 'Item name.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'required'    => true,
				),
				'description'  => array(
					'description' => __( 'Item description.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'unit'         => array(
					'description' => __( 'Measurement unit for the .', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'price'        => array(
					'description' => __( 'Item price.', 'wp-ever-accounting' ),
					'type'        => 'number',
					'context'     => array( 'view', 'edit' ),
					'required'    => true,
				),
				'cost'         => array(
					'description' => __( 'Item cost.', 'wp-ever-accounting' ),
					'type'        => 'number',
					'context'     => array( 'view', 'edit' ),
				),
				'taxes'        => array(
					'description' => __( 'Taxes for the item.', 'wp-ever-accounting' ),
					'type'        => 'array',
					'context'     => array( 'view', 'edit' ),
					'items'       => array(
						'id'             => array(
							'type'    => 'integer',
							'context' => array( 'view', 'edit' ),
						),
						'name'           => array(
							'type'    => 'string',
							'context' => array( 'view', 'edit' ),
						),
						'formatted_name' => array(
							'type'     => 'string',
							'context'  => array( 'view', 'edit' ),
							'readonly' => true,
						),
						'rate'           => array(
							'type'    => 'number',
							'context' => array( 'view', 'edit' ),
						),
						'compound'       => array(
							'type'    => 'boolean',
							'context' => array( 'view', 'edit' ),
							'default' => false,
						),
					),
				),
				'category_id'  => array(
					'description' => __( 'Category ID for the item.', 'wp-ever-accounting' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
				),
				'date_updated' => array(
					'description' => __( "The date the item was last updated, in the site's timezone.", 'wp-ever-accounting' ),
					'type'        => 'date-time',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'date_created' => array(
					'description' => __( "The date the item was created, in the site's timezone.", 'wp-ever-accounting' ),
					'type'        => 'date-time',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
			),
		);

		/**
		 * Filters the item's schema.
		 *
		 * @param array $schema Item schema data.
		 *
		 * @since 2.0.0
		 */
		$schema = apply_filters( 'eac_rest_item_schema', $schema );

		return $this->add_additional_fields_schema( $schema );
	}

	/**
	 * Retrieves the query params for the items' collection.
	 *
	 * @since 2.0.0
	 * @return array Collection parameters.
	 */
	public function get_collection_params() {
		$params           = parent::get_collection_params();
		$params['type']   = array(
			'description'       => __( 'Limit result set to items of a particular type.', 'wp-ever-accounting' ),
			'type'              => 'string',
			'enum'              => array_keys( EAC()->items->get_types() ),
			'default'           => '',
			'sanitize_callback' => 'sanitize_key',
		);
		$params['status'] = array(
			'description'       => __( 'Limit result set to items of a particular status.', 'wp-ever-accounting' ),
			'type'              => 'string',
			'enum'              => array( 'active', 'inactive' ),
			'default'           => '',
			'sanitize_callback' => 'sanitize_key',
		);

		return $params;
	}
}
