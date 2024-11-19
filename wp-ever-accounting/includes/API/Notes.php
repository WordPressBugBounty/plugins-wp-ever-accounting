<?php

namespace EverAccounting\API;

use EverAccounting\Models\Note;

defined( 'ABSPATH' ) || exit;

/**
 * Class Notes
 *
 * @since 2.0.0
 * @package EverAccounting\API
 */
class Notes extends Controller {
	/**
	 * Route base.
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	protected $rest_base = 'notes';

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
						'description' => __( 'Unique identifier for the note.', 'wp-ever-accounting' ),
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
	 * Checks if a given request has access to read notes.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 2.0.0
	 * @return true|\WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function get_items_permissions_check( $request ) {
		if ( ! current_user_can( 'manage_accounting' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability
			return new \WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to view notes.', 'wp-ever-accounting' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Checks if a given request has access to create a note.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 2.0.0
	 * @return true|\WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function create_item_permissions_check( $request ) {
		if ( ! current_user_can( 'manage_accounting' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability
			return new \WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to create notes.', 'wp-ever-accounting' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Checks if a given request has access to read a note.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 2.0.0
	 * @return true|\WP_Error True, if the request has read access, WP_Error object otherwise.
	 */
	public function get_item_permissions_check( $request ) {
		$note = EAC()->notes->get( $request['id'] );

		if ( empty( $note ) || ! current_user_can( 'manage_accounting' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability
			return new \WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to view this note.', 'wp-ever-accounting' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Checks if a given request has access to update a note.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 2.0.0
	 * @return true|\WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function update_item_permissions_check( $request ) {
		$note = EAC()->notes->get( $request['id'] );

		if ( empty( $note ) || ! current_user_can( 'manage_accounting' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability
			return new \WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to update this note.', 'wp-ever-accounting' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Checks if a given request has access to delete a note.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 2.0.0
	 * @return true|\WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function delete_item_permissions_check( $request ) {
		$note = EAC()->notes->get( $request['id'] );

		if ( empty( $note ) || ! current_user_can( 'manage_accounting' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability
			return new \WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to delete this note.', 'wp-ever-accounting' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Retrieves a list of notes.
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
		 * Enables adding extra arguments or setting defaults for a note request.
		 *
		 * @param array            $args Key value array of query var to query value.
		 * @param \WP_REST_Request $request The request used.
		 *
		 * @since 2.0.0
		 */
		$args = apply_filters( 'eac_rest_note_query', $args, $request );

		$notes     = EAC()->notes->query( $args );
		$total     = EAC()->notes->query( $args, true );
		$max_pages = ceil( $total / (int) $args['per_page'] );

		$results = array();
		foreach ( $notes as $note ) {
			$data      = $this->prepare_item_for_response( $note, $request );
			$results[] = $this->prepare_response_for_collection( $data );
		}

		$response = rest_ensure_response( $results );

		$response->header( 'X-WP-Total', (int) $total );
		$response->header( 'X-WP-TotalPages', (int) $max_pages );

		return $response;
	}

	/**
	 * Retrieves a single note.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 2.0.0
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_item( $request ) {
		$note = EAC()->notes->get( $request['id'] );
		$data = $this->prepare_item_for_response( $note, $request );

		return rest_ensure_response( $data );
	}

	/**
	 * Creates a single note.
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
				__( 'Cannot create existing note.', 'wp-ever-accounting' ),
				array( 'status' => 400 )
			);
		}

		$data = $this->prepare_item_for_database( $request );
		if ( is_wp_error( $data ) ) {
			return $data;
		}

		$note = EAC()->notes->insert( $data );
		if ( is_wp_error( $note ) ) {
			return $note;
		}

		$response = $this->prepare_item_for_response( $note, $request );
		$response = rest_ensure_response( $response );

		$response->set_status( 201 );

		return $response;
	}

	/**
	 * Updates a single note.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 2.0.0
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function update_item( $request ) {
		$note = EAC()->notes->get( $request['id'] );
		$data = $this->prepare_item_for_database( $request );
		if ( is_wp_error( $data ) ) {
			return $data;
		}

		$saved = $note->fill( $data )->save();
		if ( is_wp_error( $saved ) ) {
			return $saved;
		}

		$response = $this->prepare_item_for_response( $saved, $request );

		return rest_ensure_response( $response );
	}

	/**
	 * Deletes a single note.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @since 2.0.0
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function delete_item( $request ) {
		$note = EAC()->notes->get( $request['id'] );
		$request->set_param( 'context', 'edit' );
		$data = $this->prepare_item_for_response( $note, $request );

		if ( ! $note->delete() ) {
			return new \WP_Error(
				'rest_cannot_delete',
				__( 'The note cannot be deleted.', 'wp-ever-accounting' ),
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
	 * Prepares a single note output for response.
	 *
	 * @param Note             $item Note object.
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @since 2.0.0
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
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
		 * Filter note data returned from the REST API.
		 *
		 * @param \WP_REST_Response $response The response object.
		 * @param Note          $item Note object used to create response.
		 * @param \WP_REST_Request  $request Request object.
		 */
		return apply_filters( 'eac_rest_prepare_note', $response, $item, $request );
	}

	/**
	 * Prepares a single note for create or update.
	 *
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @since 2.0.0
	 * @return array|\WP_Error Note object or WP_Error.
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
		 * Filters note before it is inserted via the REST API.
		 *
		 * @param array            $data Note data.
		 * @param \WP_REST_Request $request Request object.
		 */
		return apply_filters( 'eac_rest_pre_insert_note', $data, $request );
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
			'title'      => __( 'Note', 'wp-ever-accounting' ),
			'type'       => 'object',
			'properties' => array(
				'id'           => array(
					'description' => __( 'Unique identifier for the note.', 'wp-ever-accounting' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'embed', 'edit' ),
					'readonly'    => true,
					'arg_options' => array(
						'sanitize_callback' => 'intval',
					),
				),
				'parent_id'    => array(
					'description' => __( 'Parent ID.', 'wp-ever-accounting' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'embed', 'edit' ),
					'required'    => true,
				),
				'parent_type'  => array(
					'description' => __( 'Parent type.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'view', 'embed', 'edit' ),
					'required'    => true,
				),
				'content'      => array(
					'description' => __( 'Content of the note.', 'wp-ever-accounting' ),
					'type'        => 'string',
					'context'     => array( 'view', 'embed', 'edit' ),
				),
				'author_id'    => array(
					'description' => __( 'Creator ID.', 'wp-ever-accounting' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'embed', 'edit' ),
				),
				'date_updated' => array(
					'description' => __( "The date the note was last updated, in the site's timezone.", 'wp-ever-accounting' ),
					'type'        => 'date-time',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'date_created' => array(
					'description' => __( "The date the note was created, in the site's timezone.", 'wp-ever-accounting' ),
					'type'        => 'date-time',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
			),
		);

		/**
		 * Filters the note's schema.
		 *
		 * @param array $schema Item schema data.
		 *
		 * @since 2.0.0
		 */
		$schema = apply_filters( 'eac_rest_note_item_schema', $schema );

		return $this->add_additional_fields_schema( $schema );
	}
}
