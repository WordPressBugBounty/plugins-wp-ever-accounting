<?php

namespace EverAccounting\Models;

use EverAccounting\ByteKit\Models\Relations\HasMany;

defined( 'ABSPATH' ) || exit;

/**
 * Category model.
 *
 * @since 1.0.0
 * @author  Sultan Nasir Uddin <manikdrmc@gmail.com>
 * @package EverAccounting
 * @subpackage Models
 * @extends Model Category model.
 *
 * @property int          $id ID of the category.
 * @property string       $type Type of the category.
 * @property string       $name Name of the category.
 * @property string       $description Description of the category.
 *
 * @property-read  string $formatted_name Formatted name of the category.
 */
class Category extends Term {
	/**
	 * Object type in singular form.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $object_type = 'category';

	/*
	|--------------------------------------------------------------------------
	| Accessors, Mutators and Relationship Methods
	|--------------------------------------------------------------------------
	| This section contains methods for getting and setting attributes (accessors
	| and mutators) as well as defining relationships between models.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Set the type of the category.
	 *
	 * @param string $type Status of the category.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function set_type_attribute( $type ) {
		if ( ! array_key_exists( $type, EAC()->categories->get_types() ) ) {
			$type = '';
		}

		$this->attributes['type'] = sanitize_text_field( $type );
	}

	/**
	 * Get the formatted name of the category.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	protected function get_formatted_name_attribute() {
		return sprintf( '%s (#%d)', $this->name, $this->id );
	}

	/**
	 * Get the transactions of the category.
	 *
	 * @since 1.0.0
	 * @return HasMany
	 */
	public function transactions() {
		return $this->has_many( Transaction::class );
	}

	/**
	 * Get the revenues of the category.
	 *
	 * @since 1.0.0
	 * @return HasMany
	 */
	public function payments() {
		return $this->has_many( Payment::class );
	}

	/**
	 * Get the expenses of the category.
	 *
	 * @since 1.0.0
	 * @return HasMany
	 */
	public function expenses() {
		return $this->has_many( Expense::class );
	}

	/**
	 * Get items of the category.
	 *
	 * @since 1.0.0
	 * @return HasMany
	 */
	public function items() {
		return $this->has_many( Item::class );
	}

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
		if ( empty( $this->name ) ) {
			return new \WP_Error( 'missing_required', __( 'Category name is required.', 'wp-ever-accounting' ) );
		}

		if ( empty( $this->type ) ) {
			return new \WP_Error( 'missing_required', __( 'Category type is required.', 'wp-ever-accounting' ) );
		}

		// Duplicate check. Same type and name should not exist.
		$existing = static::results(
			array(
				'type'     => $this->type,
				'taxonomy' => $this->taxonomy,
				'name'     => $this->name,
				'limit'    => 1,
			)
		);
		if ( ! empty( $existing ) && $existing[0]->id !== $this->id ) {
			return new \WP_Error( 'duplicate', __( 'Category with same name and type already exists.', 'wp-ever-accounting' ) );
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
		return admin_url( 'admin.php?page=eac-settings&tab=categories&action=edit&id=' . $this->id );
	}
}
