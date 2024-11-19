<?php

namespace EverAccounting\API;

use EverAccounting\Models\Bill;

defined( 'ABSPATH' ) || exit;

/**
 * Class Bills
 *
 * @since 2.0.0
 * @package EverAccounting\API
 */
class Bills extends Documents {
	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'bills';

	/**
	 * Checks if a given request has access to read bills.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 2.0.0
	 * @return true|\WP_Error True, if the request has read access, WP_Error object otherwise.
	 */
	public function get_items_permissions_check( $request ) {
		if ( ! current_user_can( 'eac_manage_bill' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability
			return new \WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to view bills.', 'wp-ever-accounting' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Checks if a given request has access to create a bill.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 2.0.0
	 * @return true|\WP_Error True, if the request has read access, WP_Error object otherwise.
	 */
	public function create_item_permissions_check( $request ) {
		if ( ! current_user_can( 'eac_manage_bill' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability
			return new \WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to create bills.', 'wp-ever-accounting' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Checks if a given request has access to read a bill.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 2.0.0
	 * @return true|\WP_Error True, if the request has read access, WP_Error object otherwise.
	 */
	public function get_item_permissions_check( $request ) {
		$bill = EAC()->bills->get( $request['id'] );

		if ( empty( $bill ) || ! current_user_can( 'eac_manage_bill' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability
			return new \WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to view this bill.', 'wp-ever-accounting' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Checks if a given request has access to update a bill.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 2.0.0
	 * @return true|\WP_Error True, if the request has read access, WP_Error object otherwise.
	 */
	public function update_item_permissions_check( $request ) {
		$bill = EAC()->bills->get( $request['id'] );

		if ( empty( $bill ) || ! current_user_can( 'eac_manage_bill' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability
			return new \WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to update this bill.', 'wp-ever-accounting' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Checks if a given request has access to delete a bill.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 2.0.0
	 * @return true|\WP_Error True, if the request has read access, WP_Error object otherwise.
	 */
	public function delete_item_permissions_check( $request ) {
		$bill = EAC()->bills->get( $request['id'] );

		if ( empty( $bill ) || ! current_user_can( 'eac_manage_bill' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability
			return new \WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to delete this bill.', 'wp-ever-accounting' ),
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
		$args      = apply_filters( 'eac_rest_bill_query', $args, $request );
		$items     = EAC()->bills->query( $args );
		$total     = EAC()->bills->query( $args, true );
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
	 * Retrieves a single bill.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 2.0.0
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_item( $request ) {
		$bill = EAC()->bills->get( $request['id'] );
		$data = $this->prepare_item_for_response( $bill, $request );

		return rest_ensure_response( $data );
	}

	/**
	 * Prepares a single item output for response.
	 *
	 * @param Bill             $item Bill object.
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @since 2.0.0
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function prepare_item_for_response( $item, $request ) {
		$data = array();

		foreach ( array_keys( $this->get_schema_properties() ) as $key ) {
			switch ( $key ) {
				case 'issue_date':
				case 'due_date':
				case 'sent_date':
				case 'payment_date':
				case 'date_created':
				case 'date_updated':
					$value = $this->prepare_date_response( $item->$key );
					break;
				case 'due_amount':
					$value = $item->get_due_amount();
					break;
				case 'contact':
					if ( ! empty( $item->contact ) ) {
						$value      = new \stdClass();
						$properties = array_keys( $this->get_schema_properties()[ $key ]['properties'] );
						foreach ( $properties as $property ) {
							$value->$property = $item->contact->$property;
						}
					}
					break;
				case 'items':
					$value = array();
					foreach ( $item->items as $item ) {
						$item_data = new \stdClass();
						foreach ( array_keys( $this->get_schema_properties()[ $key ]['items']['properties'] ) as $property ) {
							switch ( $property ) {
								case 'taxes':
									$taxes = array();
									foreach ( $item->taxes as $tax ) {
										$tax_data = new \stdClass();
										foreach ( array_keys( $this->get_schema_properties()[ $key ]['items']['properties']['taxes']['items']['properties'] ) as $tax_property ) {
											$tax_data->$tax_property = $tax->$tax_property;
										}
										$taxes[] = $tax_data;
									}
									$item_data->$property = $taxes;
									break;
								default:
									$item_data->$property = $item->$property;
									break;
							}
						}
						$value[] = $item_data;
					}
					break;
				case 'attachment':
					if ( ! empty( $item->attachment ) ) {
						$value      = new \stdClass();
						$properties = array_keys( $this->get_schema_properties()[ $key ]['properties'] );
						foreach ( $properties as $property ) {
							$value->$property = $item->attachment->$property;
						}
					}
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
		 * @param Bill           $item Item object used to create response.
		 * @param \WP_REST_Request  $request Request object.
		 */
		return apply_filters( 'eac_rest_prepare_bill', $response, $item, $request );
	}

	/**
	 * Prepares a single bill for create or update.
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
		return apply_filters( 'eac_rest_pre_insert_bill', $props, $request );
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
			'title'      => __( 'Bill', 'wp-ever-accounting' ),
			'type'       => 'object',
			'properties' => array(
				'id'                 => array(
					'description' => __( 'Unique identifier for the bill.', 'wp-ever-accounting' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'status'             => array(
					'description' => __( 'Status of the bill.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'default'     => 'draft',
				),
				'number'             => array(
					'description' => __( 'Bill number.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'required'    => true,
				),
				'reference'          => array(
					'description' => __( 'Bill reference.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'issue_date'         => array(
					'description' => __( 'Issue date.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit' ),
				),
				'due_date'           => array(
					'description' => __( 'Due date.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit' ),
				),
				'sent_date'          => array(
					'description' => __( 'Sent date.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit' ),
				),
				'payment_date'       => array(
					'description' => __( 'Payment date.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit' ),
				),
				'discount_value'     => array(
					'description' => __( 'Discount.', 'wp-ever-accounting' ),
					'type'        => 'number',
					'context'     => array( 'view', 'edit' ),
				),
				'discount_type'      => array(
					'description' => __( 'Discount type.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'enum'        => array( 'fixed', 'percentage' ),
					'context'     => array( 'view', 'edit' ),
				),
				'subtotal'           => array(
					'description' => __( 'Subtotal.', 'wp-ever-accounting' ),
					'type'        => 'number',
					'context'     => array( 'view', 'edit' ),
				),
				'discount'           => array(
					'description' => __( 'Discount total.', 'wp-ever-accounting' ),
					'type'        => 'number',
					'context'     => array( 'view', 'edit' ),
				),
				'tax'                => array(
					'description' => __( 'Tax total.', 'wp-ever-accounting' ),
					'type'        => 'number',
					'context'     => array( 'view', 'edit' ),
				),
				'total'              => array(
					'description' => __( 'Total.', 'wp-ever-accounting' ),
					'type'        => 'number',
					'context'     => array( 'view', 'edit' ),
				),
				'due_amount'         => array(
					'description' => __( 'Due total.', 'wp-ever-accounting' ),
					'type'        => 'float',
					'context'     => array( 'view', 'edit' ),
				),
				'currency'           => array(
					'description' => __( 'Currency code.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'exchange_rate'      => array(
					'description' => __( 'Exchange rate.', 'wp-ever-accounting' ),
					'type'        => 'number',
					'context'     => array( 'view', 'edit' ),
				),
				'contact_name'       => array(
					'description' => __( 'Contact name.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'contact_company'    => array(
					'description' => __( 'Contact company.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'contact_email'      => array(
					'description' => __( 'Contact email.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'contact_phone'      => array(
					'description' => __( 'Contact phone.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'contact_address'    => array(
					'description' => __( 'Contact address.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'contact_city'       => array(
					'description' => __( 'Contact city.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'contact_state'      => array(
					'description' => __( 'Contact state.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'contact_postcode'   => array(
					'description' => __( 'Contact postcode.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'contact_country'    => array(
					'description' => __( 'Contact country.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'contact_tax_number' => array(
					'description' => __( 'Contact tax number.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'note'               => array(
					'description' => __( 'Note.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'terms'              => array(
					'description' => __( 'Terms.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'attachment_id'      => array(
					'description' => __( 'Attachment ID.', 'wp-ever-accounting' ),
					'type'        => 'integer',
					'context'     => array( 'edit' ),
				),
				'attachment'         => array(
					'description' => __( 'Attachment.', 'wp-ever-accounting' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit' ),
					'properties'  => array(
						'id'   => array(
							'description' => __( 'Attachment ID.', 'wp-ever-accounting' ),
							'type'        => 'integer',
							'context'     => array( 'view', 'edit' ),
						),
						'url'  => array(
							'description' => __( 'Attachment URL.', 'wp-ever-accounting' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'name' => array(
							'description' => __( 'Attachment name.', 'wp-ever-accounting' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
					),
				),
				'contact_id'         => array(
					'description' => __( 'Contact ID.', 'wp-ever-accounting' ),
					'type'        => 'integer',
					'context'     => array( 'edit' ),
				),
				'contact'            => array(
					'description' => __( 'Contact.', 'wp-ever-accounting' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit' ),
					'properties'  => array(
						'id'   => array(
							'description' => __( 'Contact ID.', 'wp-ever-accounting' ),
							'type'        => 'integer',
							'context'     => array( 'view', 'edit' ),
						),
						'name' => array(
							'description' => __( 'Contact name.', 'wp-ever-accounting' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
					),
				),
				'payment_id'         => array(
					'description' => __( 'Payment ID.', 'wp-ever-accounting' ),
					'type'        => 'integer',
					'context'     => array( 'edit' ),
				),
				'items'              => array(
					'description' => __( 'Bill items.', 'wp-ever-accounting' ),
					'type'        => 'array',
					'context'     => array( 'view', 'edit' ),
					'items'       => array(
						'type'       => 'object',
						'properties' => array(
							'id'          => array(
								'description' => __( 'Item ID.', 'wp-ever-accounting' ),
								'type'        => 'integer',
								'context'     => array( 'view', 'edit' ),
								'readonly'    => true,
							),
							'name'        => array(
								'description' => __( 'Item name.', 'wp-ever-accounting' ),
								'type'        => 'string',
								'context'     => array( 'view', 'edit' ),
							),
							'description' => array(
								'description' => __( 'Item description.', 'wp-ever-accounting' ),
								'type'        => 'string',
								'context'     => array( 'view', 'edit' ),
							),
							'item_id'     => array(
								'description' => __( 'Item ID.', 'wp-ever-accounting' ),
								'type'        => 'mixed',
								'context'     => array( 'view', 'edit' ),
							),
							'quantity'    => array(
								'description' => __( 'Item quantity.', 'wp-ever-accounting' ),
								'type'        => 'integer',
								'context'     => array( 'view', 'edit' ),
							),
							'unit'        => array(
								'description' => __( 'Item unit.', 'wp-ever-accounting' ),
								'type'        => 'string',
								'context'     => array( 'view', 'edit' ),
							),
							'price'       => array(
								'description' => __( 'Item price per unit.', 'wp-ever-accounting' ),
								'type'        => 'number',
								'context'     => array( 'view', 'edit' ),
							),
							'taxes'       => array(
								'description' => __( 'Item taxes.', 'wp-ever-accounting' ),
								'type'        => 'array',
								'context'     => array( 'view', 'edit' ),
								'items'       => array(
									'type'       => 'object',
									'properties' => array(
										'id'       => array(
											'description' => __( 'Tax ID.', 'wp-ever-accounting' ),
											'type'        => 'integer',
											'context'     => array( 'view', 'edit' ),
											'readonly'    => true,
										),
										'tax_id'   => array(
											'description' => __( 'Tax ID.', 'wp-ever-accounting' ),
											'type'        => 'integer',
											'context'     => array( 'view', 'edit' ),
										),
										'name'     => array(
											'description' => __( 'Tax name.', 'wp-ever-accounting' ),
											'type'        => 'string',
											'context'     => array( 'view', 'edit' ),
										),
										'rate'     => array(
											'description' => __( 'Tax rate.', 'wp-ever-accounting' ),
											'type'        => 'number',
											'context'     => array( 'view', 'edit' ),
										),
										'amount'   => array(
											'description' => __( 'Tax amount.', 'wp-ever-accounting' ),
											'type'        => 'number',
											'context'     => array( 'view', 'edit' ),
										),
										'compound' => array(
											'description' => __( 'Compound tax.', 'wp-ever-accounting' ),
											'type'        => 'boolean',
											'context'     => array( 'view', 'edit' ),
										),
									),
								),
							),
							'subtotal'    => array(
								'description' => __( 'Item subtotal.', 'wp-ever-accounting' ),
								'type'        => 'number',
								'context'     => array( 'view', 'edit' ),
							),
							'discount'    => array(
								'description' => __( 'Item discount.', 'wp-ever-accounting' ),
								'type'        => 'number',
								'context'     => array( 'view', 'edit' ),
							),
							'tax'         => array(
								'description' => __( 'Item tax.', 'wp-ever-accounting' ),
								'type'        => 'number',
								'context'     => array( 'view', 'edit' ),
							),
							'total'       => array(
								'description' => __( 'Item total.', 'wp-ever-accounting' ),
								'type'        => 'number',
								'context'     => array( 'view', 'edit' ),
							),
						),
					),
				),
				'editable'           => array(
					'description' => __( 'Is editable.', 'wp-ever-accounting' ),
					'type'        => 'boolean',
					'context'     => array( 'view', 'edit' ),
				),
				'created_via'        => array(
					'description' => __( 'Created via.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'uuid'               => array(
					'description' => __( 'Unique identifier for the resource.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'format'      => 'uuid',
					'context'     => array( 'view', 'embed', 'edit' ),
					'readonly'    => true,
				),
				'date_updated'       => array(
					'description' => __( 'The date the bill was last updated, in the site\'s timezone.', 'wp-ever-accounting' ),
					'type'        => 'date-time',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'date_created'       => array(
					'description' => __( 'The date the bill was created, in the site\'s timezone.', 'wp-ever-accounting' ),
					'type'        => 'date-time',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
			),
		);

		return $schema;
	}
}
