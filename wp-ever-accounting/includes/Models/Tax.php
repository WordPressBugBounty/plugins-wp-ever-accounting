<?php

namespace EverAccounting\Models;

/**
 * Tax model.
 *
 * @since 1.0.0
 * @author  Sultan Nasir Uddin <manikdrmc@gmail.com>
 * @package EverAccounting
 * @subpackage Models
 *
 * @property int         $id ID of the tax.
 * @property string      $name Name of the tax.
 * @property string      $description Description of the tax.
 * @property double      $rate Rate of the tax.
 * @property bool        $compound Whether the tax is compound.
 *
 * @property-read string $formatted_name Formatted name of the tax.
 */
class Tax extends Term {
	/**
	 * Object type in singular form.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $object_type = 'tax';

	/**
	 * The accessors to append to the model's array form.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $appends = array(
		'rate',
		'compound',
	);

	/**
	 * Create a new model instance.
	 *
	 * @param string|int|array $attributes Attributes.
	 *
	 * @return void
	 */
	public function __construct( $attributes = null ) {
		$this->hidden[] = 'type';
		$this->hidden[] = 'parent_id';
		$this->casts    = array_merge(
			$this->casts,
			array(
				'rate'     => 'float',
				'compound' => 'bool',
			)
		);
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

	/**
	 * Get rate attribute.
	 *
	 * @since 1.0.0
	 * @return double
	 */
	protected function get_rate_attribute() {
		return $this->get_meta( 'rate' );
	}

	/**
	 * Get compound attribute.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	protected function get_compound_attribute() {
		return $this->cast( 'compound', $this->get_meta( 'compound' ) );
	}

	/**
	 * Set rate attribute.
	 *
	 * @param double $value Rate of the tax.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function set_rate_attribute( $value ) {
		$this->set_meta( 'rate', $value );
	}

	/**
	 * Set compound attribute.
	 *
	 * @param bool $value Whether the tax is compound.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function set_compound_attribute( $value ) {
		$this->set_meta( 'compound', $this->cast( 'compound', $value ) );
	}

	/**
	 * Get formatted name.
	 *
	 * @since 1.1.6
	 * @return string
	 */
	protected function get_formatted_name_attribute() {
		return sprintf( '%1$s (%2$s%%)', $this->name, $this->rate );
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
			return new \WP_Error( 'missing_required', __( 'Tax name is required.', 'wp-ever-accounting' ) );
		}
		if ( empty( $this->rate ) ) {
			return new \WP_Error( 'missing_required', __( 'Tax rate is required.', 'wp-ever-accounting' ) );
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
		return admin_url( 'admin.php?page=eac-settings&tab=taxes&section=rates&action=edit&id=' . $this->id );
	}
}
