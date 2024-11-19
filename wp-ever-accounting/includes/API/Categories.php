<?php

namespace EverAccounting\API;

use EverAccounting\Models\Category;

defined( 'ABSPATH' ) || exit;

/**
 * Class Categories
 *
 * @since 2.0.0
 * @package EverAccounting\API
 */
class Categories extends Controller {
	/**
	 * Route base.
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	protected $rest_base = 'categories';

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
						'description' => __( 'Unique identifier for the Category.', 'wp-ever-accounting' ),
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
	 * Checks if a given request has access to read categories.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return true|\WP_Error True if the request has read access, WP_Error object otherwise.
	 * @since 2.0.0
	 */
	public function get_items_permissions_check( $request ) {
		if ( ! current_user_can( 'eac_manage_category' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability
			return new \WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to view categories.', 'wp-ever-accounting' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Checks if a given request has access to create a category.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return true|\WP_Error True if the request has read access, WP_Error object otherwise.
	 * @since 2.0.0
	 */
	public function create_item_permissions_check( $request ) {
		if ( ! current_user_can( 'eac_manage_category' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability
			return new \WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to create categories.', 'wp-ever-accounting' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Checks if a given request has access to read a category.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return true|\WP_Error True, if the request has read access, WP_Error object otherwise.
	 * @since 2.0.0
	 */
	public function get_item_permissions_check( $request ) {
		$category = EAC()->categories->get( $request['id'] );

		if ( empty( $category ) || ! current_user_can( 'eac_manage_category' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability
			return new \WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to view this category.', 'wp-ever-accounting' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Checks if a given request has access to update a category.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return true|\WP_Error True if the request has read access, WP_Error object otherwise.
	 * @since 2.0.0
	 */
	public function update_item_permissions_check( $request ) {
		$category = EAC()->categories->get( $request['id'] );

		if ( empty( $category ) || ! current_user_can( 'eac_manage_category' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability
			return new \WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to update this category.', 'wp-ever-accounting' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Checks if a given request has access to delete a category.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return true|\WP_Error True if the request has read access, WP_Error object otherwise.
	 * @since 2.0.0
	 */
	public function delete_item_permissions_check( $request ) {
		$category = EAC()->categories->get( $request['id'] );

		if ( empty( $category ) || ! current_user_can( 'eac_manage_category' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability
			return new \WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to delete this category.', 'wp-ever-accounting' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Retrieves a list of categories.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 * @since 2.0.0
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
		 * Enables adding extra arguments or setting defaults for a category request.
		 *
		 * @param array $args Key value array of query var to query value.
		 * @param \WP_REST_Request $request The request used.
		 *
		 * @since 2.0.0
		 */
		$args = apply_filters( 'eac_rest_category_query', $args, $request );

		$categories = EAC()->categories->query( $args );
		$total      = EAC()->categories->query( $args, true );
		$max_pages  = ceil( $total / (int) $args['per_page'] );

		$results = array();
		foreach ( $categories as $category ) {
			$data      = $this->prepare_item_for_response( $category, $request );
			$results[] = $this->prepare_response_for_collection( $data );
		}

		$response = rest_ensure_response( $results );

		$response->header( 'X-WP-Total', (int) $total );
		$response->header( 'X-WP-TotalPages', (int) $max_pages );

		return $response;
	}

	/**
	 * Retrieves a single category.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 * @since 2.0.0
	 */
	public function get_item( $request ) {
		$category = EAC()->categories->get( $request['id'] );
		$data     = $this->prepare_item_for_response( $category, $request );

		return rest_ensure_response( $data );
	}

	/**
	 * Creates a single category.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 * @since 2.0.0
	 */
	public function create_item( $request ) {
		if ( ! empty( $request['id'] ) ) {
			return new \WP_Error(
				'rest_exists',
				__( 'Cannot create existing category.', 'wp-ever-accounting' ),
				array( 'status' => 400 )
			);
		}

		$data = $this->prepare_item_for_database( $request );
		if ( is_wp_error( $data ) ) {
			return $data;
		}

		$category = EAC()->categories->insert( $data );
		if ( is_wp_error( $category ) ) {
			return $category;
		}

		$response = $this->prepare_item_for_response( $category, $request );
		$response = rest_ensure_response( $response );

		$response->set_status( 201 );

		return $response;
	}

	/**
	 * Updates a single category.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 * @since 2.0.0
	 */
	public function update_item( $request ) {
		$category = EAC()->categories->get( $request['id'] );
		$data     = $this->prepare_item_for_database( $request );
		if ( is_wp_error( $data ) ) {
			return $data;
		}

		$saved = $category->fill( $data )->save();
		if ( is_wp_error( $saved ) ) {
			return $saved;
		}

		$response = $this->prepare_item_for_response( $saved, $request );

		return rest_ensure_response( $response );
	}

	/**
	 * Deletes a single category.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 * @since 2.0.0
	 */
	public function delete_item( $request ) {
		$category = EAC()->categories->get( $request['id'] );
		$request->set_param( 'context', 'edit' );
		$data = $this->prepare_item_for_response( $category, $request );

		if ( ! EAC()->categories->delete( $category->id ) ) {
			return new \WP_Error(
				'rest_cannot_delete',
				__( 'The category cannot be deleted.', 'wp-ever-accounting' ),
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
	 * Prepares a single category output for response.
	 *
	 * @param Category         $item Category object.
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 * @since 2.0.0
	 */
	public function prepare_item_for_response( $item, $request ) {
		$data = array();

		foreach ( array_keys( $this->get_schema_properties() ) as $key ) {
			switch ( $key ) {
				case 'date_updated':
				case 'crated_at':
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
		 * Filter category data returned from the REST API.
		 *
		 * @param \WP_REST_Response $response The response object.
		 * @param Category $item Category object used to create response.
		 * @param \WP_REST_Request $request Request object.
		 */
		return apply_filters( 'eac_rest_prepare_category', $response, $item, $request );
	}

	/**
	 * Prepares a single category for create or update.
	 *
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @return array|\WP_Error Category object or WP_Error.
	 * @since 2.0.0
	 */
	protected function prepare_item_for_database( $request ) {
		$schema = $this->get_item_schema();
		$props  = array_keys( array_filter( $schema['properties'], array( $this, 'filter_writable_props' ) ) );
		$data   = array();
		foreach ( $props as $prop ) {
			if ( isset( $request[ $prop ] ) ) {
				switch ( $prop ) {
					default:
						$data[ $prop ] = $request[ $prop ];
						break;
				}
			}
		}

		/**
		 * Filters category before it is inserted via the REST API.
		 *
		 * @param array $data Category data.
		 * @param \WP_REST_Request $request Request object.
		 */
		return apply_filters( 'eac_rest_pre_insert_category', $data, $request );
	}


	/**
	 * Retrieves the item's schema, conforming to JSON Schema.
	 *
	 * @return array Item schema data.
	 * @since 2.0.0
	 */
	public function get_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => __( 'Category', 'wp-ever-accounting' ),
			'type'       => 'object',
			'properties' => array(
				'id'          => array(
					'description' => __( 'Unique identifier for the category.', 'wp-ever-accounting' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'embed', 'edit' ),
					'readonly'    => true,
					'arg_options' => array(
						'sanitize_callback' => 'intval',
					),
				),
				'name'        => array(
					'description' => __( 'Category name.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'required'    => true,
				),
				'type'        => array(
					'description' => __( 'Category type.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'enum'        => array_keys( EAC()->categories->get_types() ),
					'context'     => array( 'view', 'edit' ),
					'required'    => true,
				),
				'description' => array(
					'description' => __( 'Category description.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
			),
		);

		/**
		 * Filters the category's schema.
		 *
		 * @param array $schema Item schema data.
		 *
		 * @since 2.0.0
		 */
		$schema = apply_filters( 'eac_rest_category_item_schema', $schema );

		return $this->add_additional_fields_schema( $schema );
	}
}
