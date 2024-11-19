<?php

namespace EverAccounting\API;

use EverAccounting\Models\Customer;

defined( 'ABSPATH' ) || exit;

/**
 * Class Customers
 *
 * @since 2.0.0
 * @package EverAccounting\API
 */
class Customers extends Contacts {
	/**
	 * Route base.
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	protected $rest_base = 'customers';

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
						'description' => __( 'Unique identifier for the customer.', 'wp-ever-accounting' ),
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
	 * Checks if a given request has access to read customers.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 2.0.0
	 * @return true|\WP_Error True, if the request has read access, WP_Error object otherwise.
	 */
	public function get_items_permissions_check( $request ) {
		if ( ! current_user_can( 'eac_manage_customer' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability
			return new \WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to view customers.', 'wp-ever-accounting' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Checks if a given request has access to create a customer.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 2.0.0
	 * @return true|\WP_Error True, if the request has read access, WP_Error object otherwise.
	 */
	public function create_item_permissions_check( $request ) {
		if ( ! current_user_can( 'eac_manage_customer' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability
			return new \WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to create customers.', 'wp-ever-accounting' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Checks if a given request has access to read a customer.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 2.0.0
	 * @return true|\WP_Error True, if the request has read access, WP_Error object otherwise.
	 */
	public function get_item_permissions_check( $request ) {
		$customer = EAC()->customers->get( $request['id'] );

		if ( empty( $customer ) || ! current_user_can( 'eac_manage_customer' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability
			return new \WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to view this customer.', 'wp-ever-accounting' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Checks if a given request has access to update a customer.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 2.0.0
	 * @return true|\WP_Error True, if the request has read access, WP_Error object otherwise.
	 */
	public function update_item_permissions_check( $request ) {
		$customer = EAC()->customers->get( $request['id'] );

		if ( empty( $customer ) || ! current_user_can( 'eac_manage_customer' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability
			return new \WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to update this customer.', 'wp-ever-accounting' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Checks if a given request has access to delete a customer.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 2.0.0
	 * @return true|\WP_Error True, if the request has read access, WP_Error object otherwise.
	 */
	public function delete_item_permissions_check( $request ) {
		$customer = EAC()->customers->get( $request['id'] );

		if ( empty( $customer ) || ! current_user_can( 'eac_manage_customer' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability
			return new \WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to delete this customer.', 'wp-ever-accounting' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Retrieves a list of customers.
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
		 * Enables adding extra arguments or setting defaults for a customer request.
		 *
		 * @param array            $args Key value array of query var to query value.
		 * @param \WP_REST_Request $request The request used.
		 *
		 * @since 2.0.0
		 */
		$args = apply_filters( 'eac_rest_customer_query', $args, $request );

		$customers = EAC()->customers->query( $args );
		$total     = EAC()->customers->query( $args, true );
		$max_pages = ceil( $total / (int) $args['per_page'] );

		$results = array();
		foreach ( $customers as $customer ) {
			$data      = $this->prepare_item_for_response( $customer, $request );
			$results[] = $this->prepare_response_for_collection( $data );
		}

		$response = rest_ensure_response( $results );

		$response->header( 'X-WP-Total', (int) $total );
		$response->header( 'X-WP-TotalPages', (int) $max_pages );

		return $response;
	}

	/**
	 * Retrieves a single customer.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 2.0.0
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_item( $request ) {
		$customer = EAC()->customers->get( $request['id'] );
		$data     = $this->prepare_item_for_response( $customer, $request );

		return rest_ensure_response( $data );
	}

	/**
	 * Creates a single customer.
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
				__( 'Cannot create existing customer.', 'wp-ever-accounting' ),
				array( 'status' => 400 )
			);
		}

		$data = $this->prepare_item_for_database( $request );
		if ( is_wp_error( $data ) ) {
			return $data;
		}

		$customer = EAC()->customers->insert( $data );
		if ( is_wp_error( $customer ) ) {
			return $customer;
		}

		$response = $this->prepare_item_for_response( $customer, $request );
		$response = rest_ensure_response( $response );

		$response->set_status( 201 );

		return $response;
	}

	/**
	 * Updates a single customer.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 2.0.0
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function update_item( $request ) {
		$customer = EAC()->customers->get( $request['id'] );
		$data     = $this->prepare_item_for_database( $request );
		if ( is_wp_error( $data ) ) {
			return $data;
		}

		$saved = $customer->fill( $data )->save();
		if ( is_wp_error( $saved ) ) {
			return $saved;
		}

		$response = $this->prepare_item_for_response( $customer, $request );
		$response = rest_ensure_response( $response );

		return $response;
	}

	/**
	 * Deletes a single customer.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 2.0.0
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function delete_item( $request ) {
		$customer = EAC()->customers->get( $request['id'] );
		$request->set_param( 'context', 'edit' );
		$data = $this->prepare_item_for_response( $customer, $request );

		if ( ! EAC()->customers->delete( $customer->id ) ) {
			return new \WP_Error(
				'rest_cannot_delete',
				__( 'The customer cannot be deleted.', 'wp-ever-accounting' ),
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
	 * Prepares a single customer output for response.
	 *
	 * @param customer         $item customer object.
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @since 2.0.0
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function prepare_item_for_response( $item, $request ) {
		$data = array();

		foreach ( array_keys( $this->get_schema_properties() ) as $key ) {
			switch ( $key ) {
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
		 * Filter customer data returned from the REST API.
		 *
		 * @param \WP_REST_Response $response The response object.
		 * @param customer          $item customer object used to create response.
		 * @param \WP_REST_Request  $request Request object.
		 */
		return apply_filters( 'eac_rest_prepare_customer', $response, $item, $request );
	}

	/**
	 * Prepares a single customer for creation or update.
	 *
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @return array|\WP_Error Customer object or WP_Error.
	 * @since 2.0.0
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
		 * Filters customer before it is inserted via the REST API.
		 *
		 * @param array            $props customer props.
		 * @param \WP_REST_Request $request Request object.
		 */
		return apply_filters( 'eac_rest_pre_insert_customer', $props, $request );
	}

	/**
	 * Retrieves the item's schema, conforming to JSON Schema.
	 *
	 * @since 2.0.0
	 * @return array Item schema data.
	 */
	public function get_item_schema() {
		$schema          = parent::get_item_schema();
		$schema['title'] = __( 'Customer', 'wp-ever-accounting' );
		/**
		 * Filters the customer's schema.
		 *
		 * @param array $schema Item schema data.
		 *
		 * @since 2.0.0
		 */
		$schema = apply_filters( 'eac_rest_customer_item_schema', $schema );

		return $this->add_additional_fields_schema( $schema );
	}
}
