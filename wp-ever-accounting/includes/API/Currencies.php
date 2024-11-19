<?php

namespace EverAccounting\API;

defined( 'ABSPATH' ) || exit();

/**
 * CurrenciesController.php
 *
 * @since 2.0.0
 * @subpackage EverAccounting\API
 * @package   EverAccounting
 */
class Currencies extends Controller {
	/**
	 * Route base.
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	protected $rest_base = 'currencies';

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
					'permission_callback' => '__return_true',
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
			'/' . $this->rest_base . '/(?P<code>[A-Z]{3})',
			array(
				'args'   => array(
					'id' => array(
						'description' => __( 'Currency code.', 'wp-ever-accounting' ),
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
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	/**
	 * Checks if a given request has access to read currencies.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 2.0.0
	 * @return true|\WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function get_items_permissions_check( $request ) {
		if ( ! current_user_can( 'eac_manage_currency' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability
			return new \WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to view currencies.', 'wp-ever-accounting' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Checks if a given request has access to create a currency.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 2.0.0
	 * @return true|\WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function create_item_permissions_check( $request ) {
		if ( ! current_user_can( 'eac_manage_currency' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability
			return new \WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to create currencies.', 'wp-ever-accounting' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Checks if a given request has access to read a currency.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 2.0.0
	 * @return true|\WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function get_item_permissions_check( $request ) {
		if ( ! current_user_can( 'eac_manage_currency' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability
			return new \WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to view this currency.', 'wp-ever-accounting' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Checks if a given request has access to update a currency.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 2.0.0
	 * @return true|\WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function update_item_permissions_check( $request ) {
		if ( ! current_user_can( 'eac_manage_currency' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability
			return new \WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to update this currency.', 'wp-ever-accounting' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Checks if a given request has access to delete a currency.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 2.0.0
	 * @return true|\WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function delete_item_permissions_check( $request ) {
		if ( ! current_user_can( 'eac_manage_currency' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability
			return new \WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to delete this currency.', 'wp-ever-accounting' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Retrieves a list of currencies.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 2.0.0
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_items( $request ) {
		$params = $this->get_collection_params();
		foreach ( $params as $key => $value ) {
			if ( isset( $request[ $key ] ) ) {
				$args[ $key ] = $request[ $key ];
			}
		}

		/**
		 * Filters the query arguments for a request.
		 *
		 * Enables adding extra arguments or setting defaults for a currency request.
		 *
		 * @param array            $args Key value array of query var to query value.
		 * @param \WP_REST_Request $request The request used.
		 *
		 * @since 2.0.0
		 */
		$args       = apply_filters( 'eac_rest_currency_query', $args, $request );
		$currencies = eac_get_currencies();
		$per_page   = empty( $args['per_page'] ) ? 20 : $args['per_page'];
		$page       = empty( $args['page'] ) ? 1 : $args['page'];
		$offset     = ( $page - 1 ) * $per_page;

		$total   = count( $currencies );
		$results = $total > $offset ? array_slice( $currencies, $offset, $per_page ) : array();

		$data = array();
		foreach ( $results as $currency ) {
			$data[] = $this->prepare_item_for_response( $currency, $request );
		}

		$response = rest_ensure_response( $data );
		$response->header( 'X-Total-Count', $total );
		$response->header( 'X-Total-Pages', ceil( $total / (int) $per_page ) );

		return $response;
	}

	/**
	 * Retrieves a single currency.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 2.0.0
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_item( $request ) {
		$code       = strtoupper( $request['code'] );
		$currencies = eac_get_currencies();
		$currency   = isset( $currencies[ $code ] ) ? $currencies[ $code ] : null;
		if ( empty( $currency ) ) {
			return new \WP_Error(
				'rest_currency_invalid',
				__( 'Invalid currency code.', 'wp-ever-accounting' ),
				array( 'status' => 404 )
			);
		}

		$response = $this->prepare_item_for_response( $currency, $request );

		return rest_ensure_response( $response );
	}

	/**
	 * Creates a single currency.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 2.0.0
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function create_item( $request ) {
		$option = get_option( 'eac_currencies', array() );
		$code   = strtoupper( $request['code'] );
		if ( isset( $option[ $code ] ) ) {
			return new \WP_Error(
				'rest_currency_exists',
				__( 'Cannot create existing currency.', 'wp-ever-accounting' ),
				array( 'status' => 400 )
			);
		}

		$data = $this->prepare_item_for_database( $request );
		if ( is_wp_error( $data ) ) {
			return $data;
		}

		$option[ $code ] = $data;
		update_option( 'eac_currencies', $option );

		$response = $this->prepare_item_for_response( $option[ $code ], $request );

		return rest_ensure_response( $response );
	}

	/**
	 * Updates a single currency.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 2.0.0
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function update_item( $request ) {
		$currencies = eac_get_currencies();
		$code       = strtoupper( $request['code'] );
		if ( empty( $code ) || ! isset( $currencies[ $code ] ) ) {
			return new \WP_Error(
				'rest_currency_invalid',
				__( 'Invalid currency code.', 'wp-ever-accounting' ),
				array( 'status' => 404 )
			);
		}

		$currency         = $currencies[ $code ];
		$data             = $this->prepare_item_for_database( $request );
		$options          = get_option( 'eac_currencies', array() );
		$options[ $code ] = array(
			'rate'               => isset( $data['rate'] ) ? floatval( $data['rate'] ) : $currency['rate'],
			'precision'          => isset( $data['precision'] ) ? intval( $data['precision'] ) : $currency['precision'],
			'decimal_separator'  => isset( $data['decimal_separator'] ) ? $data['decimal_separator'] : $currency['decimal_separator'],
			'thousand_separator' => isset( $data['thousand_separator'] ) ? $data['thousand_separator'] : $currency['thousand_separator'],
			'position'           => isset( $data['position'] ) ? $data['position'] : $currency['position'],
		);

		update_option( 'eac_currencies', $options );

		$response = $this->prepare_item_for_response( $options[ $code ], $request );

		return rest_ensure_response( $response );
	}

	/**
	 * Deletes a single currency.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 2.0.0
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function delete_item( $request ) {
		$currencies = eac_get_currencies();
		$code       = strtoupper( $request['code'] );
		if ( empty( $code ) || ! isset( $currencies[ $code ] ) ) {
			return new \WP_Error(
				'rest_currency_invalid',
				__( 'Invalid currency code.', 'wp-ever-accounting' ),
				array( 'status' => 404 )
			);
		}

		$options = get_option( 'eac_currencies', array() );
		unset( $options[ $code ] );
		update_option( 'eac_currencies', $options );

		return new \WP_REST_Response( null, 204 );
	}

	/**
	 * Prepares the item for the REST response.
	 *
	 * @param mixed            $item WordPress representation of the item.
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @since 2.0.0
	 *
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function prepare_item_for_response( $item, $request ) {
		return $item;
	}

	/**
	 * Prepares a single currency for create or update.
	 *
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @since 2.0.0
	 * @return array|\WP_Error currency object or WP_Error.
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
		 * Filters currency before it is inserted via the REST API.
		 *
		 * @param array            $props currency props.
		 * @param \WP_REST_Request $request Request object.
		 */
		return apply_filters( 'eac_rest_pre_insert_currency', $props, $request );
	}

	/**
	 * Retrieves the query params for the items' collection.
	 *
	 * @since 2.0.0
	 * @return array Collection parameters.
	 */
	public function get_collection_params() {
		$params = array(
			'context'  => $this->get_context_param(),
			'page'     => array(
				'description'       => __( 'Current page of the collection.', 'wp-ever-accounting' ),
				'type'              => 'integer',
				'default'           => 1,
				'sanitize_callback' => 'absint',
				'validate_callback' => 'rest_validate_request_arg',
				'minimum'           => 1,
			),
			'per_page' => array(
				'description'       => __( 'Maximum number of items to be returned in result set.', 'wp-ever-accounting' ),
				'type'              => 'integer',
				'default'           => 10,
				'minimum'           => 1,
				'maximum'           => 100,
				'sanitize_callback' => 'absint',
				'validate_callback' => 'rest_validate_request_arg',
			),
		);

		return $params;
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
			'title'      => __( 'currency', 'wp-ever-accounting' ),
			'type'       => 'object',
			'properties' => array(
				'code'               => array(
					'description' => __( 'currency code.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'view', 'embed', 'edit' ),
					'readonly'    => true,
					'required'    => true,
				),
				'name'               => array(
					'description' => __( 'currency name.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'rate'               => array(
					'description' => __( 'currency exchange rate.', 'wp-ever-accounting' ),
					'type'        => 'number',
					'context'     => array( 'view', 'edit' ),
					'required'    => true,
				),
				'precision'          => array(
					'description' => __( 'currency decimals.', 'wp-ever-accounting' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
					'required'    => true,
				),
				'symbol'             => array(
					'description' => __( 'currency symbol.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'decimal_separator'  => array(
					'description' => __( 'currency decimal separator.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'required'    => true,
				),
				'thousand_separator' => array(
					'description' => __( 'currency thousand separator.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'required'    => true,
				),
				'position'           => array(
					'description' => __( 'currency position.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'enum'        => array( 'before', 'after' ),
					'context'     => array( 'view', 'edit' ),
					'required'    => true,
				),
			),
		);

		/**
		 * Filters the currency's schema.
		 *
		 * @param array $schema Item schema data.
		 *
		 * @since 2.0.0
		 */
		$schema = apply_filters( 'eac_rest_currency_item_schema', $schema );

		return $this->add_additional_fields_schema( $schema );
	}
}
