<?php

namespace EverAccounting\API;

defined( 'ABSPATH' ) || exit;

/**
 * Class Controller
 *
 * @since 2.0.0
 * @package EverAccounting\API
 */
class Controller extends \WP_REST_Controller {
	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'eac/v1';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = '';


	/**
	 * Get normalized rest base.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	protected function get_normalized_rest_base() {
		return preg_replace( '/\(.*\)\//i', '', $this->rest_base );
	}

	/**
	 * Returns the value of schema['properties']
	 *
	 * i.e Schema fields.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	protected function get_schema_properties() {

		$schema     = $this->get_item_schema();
		$properties = isset( $schema['properties'] ) ? $schema['properties'] : array();

		// For back-compat, include any field with an empty schema
		// because it won't be present in $this->get_item_schema().
		foreach ( $this->get_additional_fields() as $field_name => $field_options ) {
			if ( is_null( $field_options['schema'] ) ) {
				$properties[ $field_name ] = $field_options;
			}
		}

		return $properties;
	}

	/**
	 * Filters fields by context.
	 *
	 * @param array       $fields Array of fields.
	 * @param string|null $context view, edit or embed.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	protected function filter_response_fields_by_context( $fields, $context ) {

		if ( empty( $context ) ) {
			return $fields;
		}

		foreach ( $fields as $name => $options ) {
			if ( ! empty( $options['context'] ) && ! in_array( $context, $options['context'], true ) ) {
				unset( $fields[ $name ] );
			}
		}

		return $fields;
	}

	/**
	 * Filters fields by an array of requested fields.
	 *
	 * @param array $fields Array of available fields.
	 * @param array $requested array of requested fields.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	protected function filter_response_fields_by_array( $fields, $requested ) {

		// Trim off any whitespace from the list array.
		$requested = array_map( 'trim', $requested );

		// Always persist 'id', because it can be needed for add_additional_fields_to_object().
		if ( in_array( 'id', $fields, true ) ) {
			$requested[] = 'id';
		}

		// Get rid of duplicate fields.
		$requested = array_unique( $requested );

		// Return the list of all included fields which are available.
		return array_reduce(
			$requested,
			function ( $response_fields, $field ) use ( $fields ) {

				if ( in_array( $field, $fields, true ) ) {
					$response_fields[] = $field;

					return $response_fields;
				}

				// Check for nested fields if $field is not a direct match.
				$nested_fields = explode( '.', $field );

				// A nested field is included so long as its top-level property is
				// present in the schema.
				if ( in_array( $nested_fields[0], $fields, true ) ) {
					$response_fields[] = $field;
				}

				return $response_fields;
			},
			array()
		);
	}

	/**
	 * Gets an array of fields to be included on the response.
	 *
	 * Included fields are based on item schema and `_fields=` request argument.
	 * Copied from WordPress 5.3 to support old versions.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 2.0.0
	 * @return array Fields to be included in the response.
	 */
	public function get_fields_for_response( $request ) {

		// Retrieve fields in the schema.
		$properties = $this->get_schema_properties();

		// Exclude fields that specify a different context than the request context.
		$properties = $this->filter_response_fields_by_context( $properties, $request['context'] );

		// We only need the field keys.
		$fields = array_keys( $properties );

		// Is the user filtering the response fields??
		if ( empty( $request['_fields'] ) ) {
			return $fields;
		}

		return $this->filter_response_fields_by_array( $fields, wp_parse_list( $request['_fields'] ) );
	}

	/**
	 * Limits an object to the requested fields.
	 *
	 * Included fields are based on the `_fields` request argument.
	 *
	 * @param array  $data Fields to include in the response.
	 * @param array  $fields Requested fields.
	 * @param string $prefix Prefix for the current field.
	 *
	 * @since 2.0.0
	 * @return array Fields to be included in the response.
	 */
	public function limit_object_to_requested_fields( $data, $fields, $prefix = '' ) {

		// Is the user filtering the response fields??
		if ( empty( $fields ) ) {
			return $data;
		}

		foreach ( $data as $key => $value ) {

			// Numeric arrays.
			if ( is_numeric( $key ) && is_array( $value ) ) {
				$data[ $key ] = $this->limit_object_to_requested_fields( $value, $fields, $prefix );
				continue;
			}

			// Generate a new prefix.
			$new_prefix = empty( $prefix ) ? $key : "$prefix.$key";

			// Check if it was requested.
			if ( ! empty( $key ) && ! $this->is_field_included( $new_prefix, $fields ) ) {
				unset( $data[ $key ] );
				continue;
			}

			if ( 'meta_data' !== $key && is_array( $value ) ) {
				$data[ $key ] = $this->limit_object_to_requested_fields( $value, $fields, $new_prefix );
			}
		}

		return $data;
	}


	/**
	 * Given an array of fields to include in a response, some of which may be
	 * `nested.fields`, determine whether the provided field should be included
	 * in the response body.
	 *
	 * Copied from WordPress 5.3 to support old versions.
	 *
	 * @param string $field A field to test for inclusion in the response body.
	 * @param array  $fields An array of string fields supported by the endpoint.
	 *
	 * @see   rest_is_field_included()
	 *
	 * @since 2.0.0
	 * @return bool Whether to include the field or not.
	 */
	public function is_field_included( $field, $fields ) {
		if ( in_array( $field, $fields, true ) ) {
			return true;
		}

		foreach ( $fields as $accepted_field ) {
			// Check to see if $field is the parent of any item in $fields.
			// A field "parent" should be accepted if "parent.child" is accepted.
			if ( strpos( $accepted_field, "$field." ) === 0 ) {
				return true;
			}
			// Conversely, if "parent" is accepted, all "parent.child" fields
			// should also be accepted.
			if ( strpos( $field, "$accepted_field." ) === 0 ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Only return writable props from schema.
	 *
	 * @param array $schema Schema.
	 *
	 * @return bool
	 */
	protected function filter_writable_props( $schema ) {
		return empty( $schema['readonly'] );
	}

	/**
	 * Convert date to RFC format
	 *
	 * @param string|null $date Date. Default null.
	 *
	 * @since 2.0.0
	 * @return string|null ISO8601/RFC3339 formatted datetime.
	 */
	protected function prepare_date_response( $date = null ) {
		// Use the date if passed.
		if ( ! empty( $date ) || '0000-00-00 00:00:00' !== $date ) {
			return mysql_to_rfc3339( $date );
		}

		return null;
	}

	/**
	 * Prepares a date for the database.
	 *
	 * @param string|null $date Date. Default null.
	 * @param string      $format Date format. Default 'Y-m-d H:i:s'.
	 *
	 * @since 2.2.1
	 *
	 * @return string|null GMT datetime if valid, null otherwise.
	 */
	protected function prepare_date_for_database( $date = null, $format = 'Y-m-d H:i:s' ) {
		$timestamp = null;
		if ( is_numeric( $date ) ) {
			$timestamp = (int) $date;
		} elseif ( 1 === preg_match( '/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})(Z|((-|\+)\d{2}:\d{2}))$/', $date, $date_bits ) ) {
			$offset    = ! empty( $date_bits[7] ) ? iso8601_timezone_to_offset( $date_bits[7] ) : wc_timezone_offset();
			$timestamp = gmmktime( $date_bits[4], $date_bits[5], $date_bits[6], $date_bits[2], $date_bits[3], $date_bits[1] ) - $offset;
		} elseif ( ! empty( $date ) && false !== strtotime( $date ) ) {
			$timestamp = get_gmt_from_date( gmdate( 'Y-m-d H:i:s', strtotime( $date ) ), 'U' );
		}

		return isset( $timestamp )
			? ( new \DateTime( "@{$timestamp}", new \DateTimeZone( 'UTC' ) ) )->format( $format )
			: null;
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
			'search'   => array(
				'description'       => __( 'Limit results to those matching a string.', 'wp-ever-accounting' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			),
			'include'  => array(
				'description'       => __( 'Limit result set to specific ids.', 'wp-ever-accounting' ),
				'type'              => 'array',
				'items'             => array( 'type' => 'integer' ),
				'default'           => array(),
				'sanitize_callback' => 'wp_parse_id_list',
			),
			'order'    => array(
				'description'       => __( 'Order sort attribute ascending or descending.', 'wp-ever-accounting' ),
				'type'              => 'string',
				'default'           => 'desc',
				'enum'              => array( 'asc', 'desc' ),
				'validate_callback' => 'rest_validate_request_arg',
			),
			'orderby'  => array(
				'description'       => __( 'Sort collection by object attribute.', 'wp-ever-accounting' ),
				'type'              => 'string',
				'default'           => 'date_created',
				'validate_callback' => 'rest_validate_request_arg',
			),
			'offset'   => array(
				'description'       => __( 'Offset the result set by a specific number of items.', 'wp-ever-accounting' ),
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
				'validate_callback' => 'rest_validate_request_arg',
			),
		);

		return $params;
	}


	/**
	 * Get data from a REST API endpoint.
	 * This method provides a standardized way to call API endpoints.
	 *
	 * @param string $endpoint Endpoint.
	 * @param array  $params Params to pass with request.
	 * @param string $method Request method.
	 *
	 * @since 2.0.0
	 *
	 * @return array|\WP_Error
	 */
	public function get_endpoint_data( $endpoint, $params = array(), $method = 'GET' ) {
		$request = new \WP_REST_Request( $method, $endpoint );
		if ( $params && 'GET' === $method ) {
			$request->set_query_params( $params );
		} elseif ( $params && 'POST' === $method ) {
			$request->set_body_params( $params );
		}
		$response = rest_do_request( $request );
		$server   = rest_get_server();
		$json     = wp_json_encode( $server->response_to_data( $response, false ) );

		return json_decode( $json, true );
	}
}
