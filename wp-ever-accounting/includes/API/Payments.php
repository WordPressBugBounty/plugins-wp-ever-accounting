<?php

namespace EverAccounting\API;

use EverAccounting\Models\Payment;

defined( 'ABSPATH' ) || exit;

/**
 * Class Payments
 *
 * @since 2.0.0
 * @package EverAccounting\API
 */
class Payments extends Transactions {

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'payments';

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
						'description' => __( 'Unique identifier for the payment.', 'wp-ever-accounting' ),
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
	 * Checks if a given request has access to read payments.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 2.0.0
	 * @return true|\WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function get_items_permissions_check( $request ) {
		if ( ! current_user_can( 'eac_manage_payment' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability
			return new \WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to view payments.', 'wp-ever-accounting' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Checks if a given request has access to create payment.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 2.0.0
	 * @return true|\WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function create_item_permissions_check( $request ) {
		if ( ! current_user_can( 'eac_manage_payment' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability
			return new \WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to create payments.', 'wp-ever-accounting' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Checks if a given request has access to read payment.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 2.0.0
	 * @return true|\WP_Error True, if the request has read access, WP_Error object otherwise.
	 */
	public function get_item_permissions_check( $request ) {
		$payment = EAC()->payments->get( $request['id'] );

		if ( empty( $payment ) || ! current_user_can( 'eac_manage_payment' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability
			return new \WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to view this payment.', 'wp-ever-accounting' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Checks if a given request has access to update payment.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 2.0.0
	 * @return true|\WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function update_item_permissions_check( $request ) {
		$payment = EAC()->payments->get( $request['id'] );

		if ( empty( $payment ) || ! current_user_can( 'eac_manage_payment' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability
			return new \WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to update this payment.', 'wp-ever-accounting' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Checks if a given request has access to delete payment.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 2.0.0
	 * @return true|\WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function delete_item_permissions_check( $request ) {
		$payment = EAC()->payments->get( $request['id'] );

		if ( empty( $payment ) || ! current_user_can( 'eac_manage_payment' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability
			return new \WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to delete this payment.', 'wp-ever-accounting' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Retrieves a list of payments.
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
		 * Enables adding extra arguments or setting defaults for a payment request.
		 *
		 * @param array            $args Key value array of query var to query value.
		 * @param \WP_REST_Request $request The request used.
		 *
		 * @since 2.0.0
		 */
		$args = apply_filters( 'eac_rest_payment_query', $args, $request );

		$payments  = EAC()->payments->query( $args );
		$total     = EAC()->payments->query( $args, true );
		$max_pages = ceil( $total / (int) $args['per_page'] );

		$results = array();
		foreach ( $payments as $payment ) {
			$data      = $this->prepare_item_for_response( $payment, $request );
			$results[] = $this->prepare_response_for_collection( $data );
		}

		$response = rest_ensure_response( $results );

		$response->header( 'X-WP-Total', (int) $total );
		$response->header( 'X-WP-TotalPages', (int) $max_pages );

		return $response;
	}

	/**
	 * Retrieves a single payment.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 2.0.0
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_item( $request ) {
		$payment = EAC()->payments->get( $request['id'] );
		$data    = $this->prepare_item_for_response( $payment, $request );

		return rest_ensure_response( $data );
	}

	/**
	 * Creates a single payment.
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
				__( 'Cannot create existing payment.', 'wp-ever-accounting' ),
				array( 'status' => 400 )
			);
		}

		$data = $this->prepare_item_for_database( $request );
		if ( is_wp_error( $data ) ) {
			return $data;
		}

		$payment = EAC()->payments->insert( $data );
		if ( is_wp_error( $payment ) ) {
			return $payment;
		}

		$response = $this->prepare_item_for_response( $payment, $request );
		$response = rest_ensure_response( $response );

		$response->set_status( 201 );

		return $response;
	}

	/**
	 * Updates a single payment.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 2.0.0
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function update_item( $request ) {
		$payment = EAC()->payments->get( $request['id'] );
		$data    = $this->prepare_item_for_database( $request );
		if ( is_wp_error( $data ) ) {
			return $data;
		}

		$saved = $payment->fill( $data )->save();
		if ( is_wp_error( $saved ) ) {
			return $saved;
		}

		$response = $this->prepare_item_for_response( $saved, $request );

		return rest_ensure_response( $response );
	}

	/**
	 * Deletes a single payment.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 2.0.0
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function delete_item( $request ) {
		$payment = EAC()->payments->get( $request['id'] );
		$request->set_param( 'context', 'edit' );
		$data = $this->prepare_item_for_response( $payment, $request );

		if ( ! EAC()->payments->delete( $payment->id ) ) {
			return new \WP_Error(
				'rest_cannot_delete',
				__( 'The payment cannot be deleted.', 'wp-ever-accounting' ),
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
	 * Prepares a single payment output for response.
	 *
	 * @param Payment          $item Payment object.
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @since 2.0.0
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function prepare_item_for_response( $item, $request ) {
		$data = array();

		foreach ( array_keys( $this->get_schema_properties() ) as $key ) {
			$value = null;
			switch ( $key ) {
				case 'category':
					if ( ! empty( $item->category ) ) {
						$value      = new \stdClass();
						$properties = array_keys( $this->get_schema_properties()[ $key ]['properties'] );
						foreach ( $properties as $property ) {
							$value->$property = $item->category->$property;
						}
					}
					break;
				case 'account':
					if ( ! empty( $item->account ) ) {
						$value      = new \stdClass();
						$properties = array_keys( $this->get_schema_properties()[ $key ]['properties'] );
						foreach ( $properties as $property ) {
							$value->$property = $item->account->$property;
						}
					}
					break;
				case 'bill':
					if ( ! empty( $item->bill ) ) {
						$value      = new \stdClass();
						$properties = array_keys( $this->get_schema_properties()[ $key ]['properties'] );
						foreach ( $properties as $property ) {
							$value->$property = $item->bill->$property;
						}
					}
					break;

				case 'customer':
					if ( ! empty( $item->customer ) ) {
						$value      = new \stdClass();
						$properties = array_keys( $this->get_schema_properties()[ $key ]['properties'] );
						foreach ( $properties as $property ) {
							$value->$property = $item->customer->$property;
						}
					}
					break;

				case 'date_updated':
				case 'crated_at':
				case 'date':
					$value = $this->prepare_date_response( $item->$key );
					break;
				default:
					$value = isset( $item->$key ) ? $item->$key : null;
					break;
			}

			$data[ $key ] = $value;
		}

		$context  = ! empty( $request['context'] ) ? $request['context'] : 'view';
		$data     = $this->add_additional_fields_to_object( $data, $request );
		$data     = $this->filter_response_by_context( $data, $context );
		$response = rest_ensure_response( $data );

		/**
		 * Filter payment data returned from the REST API.
		 *
		 * @param \WP_REST_Response $response The response object.
		 * @param Payment           $item Payment object used to create response.
		 * @param \WP_REST_Request  $request Request object.
		 */
		return apply_filters( 'eac_rest_prepare_payment', $response, $item, $request );
	}

	/**
	 * Prepares a single payment for create or update.
	 *
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @since 2.0.0
	 * @return array|\WP_Error Payment object or WP_Error.
	 */
	protected function prepare_item_for_database( $request ) {
		$schema = $this->get_item_schema();
		$props  = array_keys( array_filter( $schema['properties'], array( $this, 'filter_writable_props' ) ) );
		$data   = array();
		foreach ( $props as $prop ) {
			if ( isset( $request[ $prop ] ) ) {
				switch ( $prop ) {
					case 'category':
						$category = EAC()->categories->get( $request[ $prop ]['id'] );
						if ( ! $category ) {
							return new \WP_Error(
								'rest_invalid_category',
								__( 'Invalid category.', 'wp-ever-accounting' ),
								array( 'status' => 400 )
							);
						}
						$data['category_id'] = $category->id;
						break;
					case 'category_id':
						$category = EAC()->categories->get( $request[ $prop ] );
						if ( ! $category ) {
							return new \WP_Error(
								'rest_invalid_category',
								__( 'Invalid category.', 'wp-ever-accounting' ),
								array( 'status' => 400 )
							);
						}
						$data['category_id'] = $category->id;
						break;
					case 'account':
						$account = EAC()->accounts->get( $request[ $prop ]['id'] );
						if ( ! $account ) {
							return new \WP_Error(
								'rest_invalid_account',
								__( 'Invalid account.', 'wp-ever-accounting' ),
								array( 'status' => 400 )
							);
						}
						$data['account_id'] = $account->id;
						break;

					case 'account_id':
						$account = EAC()->accounts->get( $request[ $prop ] );
						if ( ! $account ) {
							return new \WP_Error(
								'rest_invalid_account',
								__( 'Invalid account.', 'wp-ever-accounting' ),
								array( 'status' => 400 )
							);
						}
						$data['account_id'] = $account->id;
						break;

					case 'invoice':
						$invoice = EAC()->invoices->get( $request[ $prop ]['id'] );
						if ( ! $invoice ) {
							return new \WP_Error(
								'rest_invalid_invoice',
								__( 'Invalid invoice.', 'wp-ever-accounting' ),
								array( 'status' => 400 )
							);
						}
						$data['invoice_id'] = $invoice->id;
						break;

					case 'customer':
						$customer = EAC()->customers->get( $request[ $prop ]['id'] );
						if ( ! $customer ) {
							return new \WP_Error(
								'rest_invalid_customer',
								__( 'Invalid customer.', 'wp-ever-accounting' ),
								array( 'status' => 400 )
							);
						}
						$data['customer_id'] = $customer->id;
						break;

					case 'attachment':
						$attachment_id = $request[ $prop ]['id'];
						if ( ! empty( $attachment_id ) && 'attachment' === get_post_type( $attachment_id ) ) {
							$data['attachment_id'] = $attachment_id;
						}
						break;
					default:
						$data[ $prop ] = $request[ $prop ];
						break;
				}
			}
		}

		/**
		 * Filters payment before it is inserted via the REST API.
		 *
		 * @param array            $data Payment data.
		 * @param \WP_REST_Request $request Request object.
		 */
		return apply_filters( 'eac_rest_pre_insert_payment', $data, $request );
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
			'title'      => __( 'Payment', 'wp-ever-accounting' ),
			'type'       => 'object',
			'properties' => array(
				'id'               => array(
					'description' => __( 'Unique identifier for the payment.', 'wp-ever-accounting' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'embed', 'edit' ),
					'readonly'    => true,
					'arg_options' => array(
						'sanitize_callback' => 'intval',
					),
				),
				'number'           => array(
					'description' => __( 'Payment number.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'view', 'embed', 'edit' ),
				),
				'payment_date'     => array(
					'description' => __( 'The date the payment took place, in the site\'s timezone.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'format'      => 'string',
					'context'     => array( 'view', 'embed', 'edit' ),
				),
				'amount'           => array(
					'description' => __( 'Total amount of the payment.', 'wp-ever-accounting' ),
					'type'        => 'number',
					'context'     => array( 'view', 'embed', 'edit' ),
				),
				'formatted_amount' => array(
					'description' => __( 'Formatted total amount of the payment.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'view', 'embed' ),
				),
				'exchange_rate'    => array(
					'description' => __( 'Exchange rate of the payment.', 'wp-ever-accounting' ),
					'type'        => 'number',
					'context'     => array( 'view', 'embed', 'edit' ),
					'default'     => 1,
				),
				'reference'        => array(
					'description' => __( 'Reference of the payment.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'view', 'embed', 'edit' ),
				),
				'note'             => array(
					'description' => __( 'Note of the payment.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'view', 'embed', 'edit' ),
				),
				'payment_method'   => array(
					'description' => __( 'Payment method of the payment.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'view', 'embed', 'edit' ),
				),
				'account_id'       => array(
					'description' => __( 'Account ID of the payment.', 'wp-ever-accounting' ),
					'type'        => 'integer',
					'context'     => array( 'edit' ),
				),
				'account'          => array(
					'description' => __( 'Account of the payment.', 'wp-ever-accounting' ),
					'type'        => 'object',
					'context'     => array( 'view', 'embed', 'edit' ),
					'readonly'    => true,
					'properties'  => array(
						'id'   => array(
							'description' => __( 'Unique identifier for the account.', 'wp-ever-accounting' ),
							'type'        => 'integer',
							'context'     => array( 'view', 'embed', 'edit' ),
							'readonly'    => true,
							'required'    => true,
							'arg_options' => array(
								'sanitize_callback' => 'intval',
							),
						),
						'name' => array(
							'description' => __( 'Account name.', 'wp-ever-accounting' ),
							'type'        => 'string',
							'context'     => array( 'view', 'embed', 'edit' ),
						),
					),
				),
				'invoice_id'       => array(
					'description' => __( 'Invoice ID of the payment.', 'wp-ever-accounting' ),
					'type'        => 'integer',
					'context'     => array( 'edit' ),
				),
				'invoice'          => array(
					'description' => __( 'Invoice of the payment.', 'wp-ever-accounting' ),
					'type'        => 'object',
					'context'     => array( 'view', 'embed', 'edit' ),
					'properties'  => array(
						'id'   => array(
							'description' => __( 'Unique identifier for the document.', 'wp-ever-accounting' ),
							'type'        => 'integer',
							'context'     => array( 'view', 'embed', 'edit' ),
							'readonly'    => true,
							'arg_options' => array(
								'sanitize_callback' => 'intval',
							),
						),
						'name' => array(
							'description' => __( 'Invoice name.', 'wp-ever-accounting' ),
							'type'        => 'string',
							'context'     => array( 'view', 'embed', 'edit' ),
						),
					),
				),
				'customer_id'      => array(
					'description' => __( 'Customer ID of the payment.', 'wp-ever-accounting' ),
					'type'        => 'integer',
					'context'     => array( 'edit' ),
				),
				'customer'         => array(
					'description' => __( 'Customer of the payment.', 'wp-ever-accounting' ),
					'type'        => 'object',
					'context'     => array( 'view', 'embed', 'edit' ),
					'properties'  => array(
						'id'   => array(
							'description' => __( 'Unique identifier for the customer.', 'wp-ever-accounting' ),
							'type'        => 'integer',
							'context'     => array( 'view', 'embed', 'edit' ),
							'readonly'    => true,
							'arg_options' => array(
								'sanitize_callback' => 'intval',
							),
						),
						'name' => array(
							'description' => __( 'Vendor name.', 'wp-ever-accounting' ),
							'type'        => 'string',
							'context'     => array( 'view', 'embed', 'edit' ),
						),
					),
				),
				'category'         => array(
					'description' => __( 'Category of the payment.', 'wp-ever-accounting' ),
					'type'        => 'object',
					'context'     => array( 'view', 'embed', 'edit' ),
					'properties'  => array(
						'id'   => array(
							'description' => __( 'Unique identifier for the category.', 'wp-ever-accounting' ),
							'type'        => 'integer',
							'context'     => array( 'view', 'embed', 'edit' ),
							'readonly'    => true,
							'required'    => true,
							'arg_options' => array(
								'sanitize_callback' => 'intval',
							),
						),
						'name' => array(
							'description' => __( 'Category name.', 'wp-ever-accounting' ),
							'type'        => 'string',
							'context'     => array( 'view', 'embed', 'edit' ),
						),
					),
				),
				'attachment_id'    => array(
					'description' => __( 'Attachment ID of the payment.', 'wp-ever-accounting' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'embed', 'edit' ),
				),
				'parent_id'        => array(
					'description' => __( 'Parent payment ID.', 'wp-ever-accounting' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'embed', 'edit' ),
				),
				'editable'         => array(
					'description' => __( 'Whether the payment is editable.', 'wp-ever-accounting' ),
					'type'        => 'boolean',
					'context'     => array( 'view', 'embed', 'edit' ),
				),
				'uuid'             => array(
					'description' => __( 'UUID of the payment.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'view', 'embed', 'edit' ),
					'readonly'    => true,
				),
				'created_via'      => array(
					'description' => __( 'Created via of the payment.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'view', 'embed', 'edit' ),
				),
				'author_id'        => array(
					'description' => __( 'Author ID of the payment.', 'wp-ever-accounting' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'embed', 'edit' ),
					'readonly'    => true,
				),
				'date_updated'     => array(
					'description' => __( "The date the payment was last updated, in the site's timezone.", 'wp-ever-accounting' ),
					'type'        => 'date-time',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'date_created'     => array(
					'description' => __( "The date the payment was created, in the site's timezone.", 'wp-ever-accounting' ),
					'type'        => 'date-time',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
			),
		);

		/**
		 * Filters the payment's schema.
		 *
		 * @param array $schema Item schema data.
		 *
		 * @since 2.0.0
		 */
		$schema = apply_filters( 'eac_rest_payment_item_schema', $schema );

		return $this->add_additional_fields_schema( $schema );
	}
}
