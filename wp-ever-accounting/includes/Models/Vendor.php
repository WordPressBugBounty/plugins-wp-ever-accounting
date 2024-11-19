<?php

namespace EverAccounting\Models;

defined( 'ABSPATH' ) || exit;

/**
 * Class Vendor.
 *
 * @since   1.1.0
 * @package EAccounting\Models
 */
class Vendor extends Contact {
	/**
	 * Object type in singular form.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $object_type = 'vendor';

	/**
	 * Create a new model instance.
	 *
	 * @param string|array|object $attributes The model attributes.
	 */
	public function __construct( $attributes = array() ) {
		$this->attributes['type'] = $this->get_object_type();
		$this->query_vars['type'] = $this->get_object_type();
		parent::__construct( $attributes );
	}

	/*
	|--------------------------------------------------------------------------
	| Accessors, Mutators and Relationship Methods
	|--------------------------------------------------------------------------
	| This section contains methods for getting and setting attributes (accessors
	| and mutators) as well as defining relationships between models.
	|--------------------------------------------------------------------------
	*/

	/*
	|--------------------------------------------------------------------------
	| CRUD Methods
	|--------------------------------------------------------------------------
	| This section contains methods for creating, reading, updating, and deleting
	| objects in the database.
	|--------------------------------------------------------------------------
	*/
	/**
	 * Save the object to the database.
	 *
	 * @since 1.0.0
	 * @return \WP_Error|static WP_Error on failure, or the object on success.
	 */
	public function save() {
		// if email is provided, check if it is unique.
		if ( ! empty( $this->email ) ) {
			$existing = $this->find( array( 'email' => $this->email ) );
			if ( ! empty( $existing ) && $existing->id !== $this->id ) {
				return new \WP_Error( 'duplicate', __( 'Vendor with same email already exists.', 'wp-ever-accounting' ) );
			}
		}

		// if phone is provided, check if it is unique.
		if ( ! empty( $this->phone ) ) {
			$existing = $this->find( array( 'phone' => $this->phone ) );
			if ( ! empty( $existing ) && $existing->id !== $this->id ) {
				return new \WP_Error( 'duplicate', __( 'Vendor with same phone already exists.', 'wp-ever-accounting' ) );
			}
		}

		return parent::save();
	}

	/*
	|--------------------------------------------------------------------------
	| Helper Methods
	|--------------------------------------------------------------------------
	| This section contains utility methods that are not directly related to this
	| object but can be used to support its functionality.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Get edit URL.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_edit_url() {
		return admin_url( 'admin.php?page=eac-purchases&tab=vendors&action=edit&id=' . $this->id );
	}

	/**
	 * Get view URL.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_view_url() {
		return admin_url( 'admin.php?page=eac-purchases&tab=vendors&action=view&id=' . $this->id );
	}
}
