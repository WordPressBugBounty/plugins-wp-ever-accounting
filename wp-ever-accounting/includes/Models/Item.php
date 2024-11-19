<?php

namespace EverAccounting\Models;

use EverAccounting\ByteKit\Models\Relations\BelongsTo;
use EverAccounting\ByteKit\Models\Relations\BelongsToMany;

/**
 * Item model.
 *
 * @since 1.0.0
 * @author  Sultan Nasir Uddin <manikdrmc@gmail.com>
 * @package EverAccounting
 * @subpackage Models
 *
 * @property int    $id ID of the item.
 * @property string $type Type of the item.
 * @property string $name Name of the item.
 * @property string $description Description of the item.
 * @property string $unit Unit of the item.
 * @property double $price Price of the item.
 * @property double $cost Cost of the item.
 * @property array  $tax_ids Tax IDs of the item.
 * @property int    $category_id Category ID of the item.
 * @property string $date_created Date created of the item.
 * @property string $date_updated Date updated of the item.
 *
 * @property-read string   $formatted_name Formatted name of the item.
 * @property-read string   $formatted_price Formatted price of the item.
 * @property-read string   $formatted_cost Formatted cost of the item.
 * @property-read Category $category Category of the item.
 * @property-read Tax[]    $taxes Taxes of the item.
 */
class Item extends Model {

	/**
	 * The table associated with the model.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $table = 'ea_items';

	/**
	 * Meta type declaration for the object.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $meta_type = 'ea_item';

	/**
	 * The table columns of the model.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $columns = array(
		'id',
		'type',
		'name',
		'description',
		'unit',
		'price',
		'cost',
		'tax_ids',
		'category_id',
	);

	/**
	 * The model's attributes.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $attributes = array(
		'type' => 'standard',
	);

	/**
	 * The properties that should be cast.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $casts = array(
		'type'        => 'sanitize_text',
		'name'        => 'sanitize_text',
		'description' => 'sanitize_textarea',
		'unit'        => 'sanitize_text',
		'price'       => 'double',
		'cost'        => 'double',
		'tax_ids'     => 'id_list',
		'category_id' => 'int',
	);

	/**
	 * The accessors to append to the model's array form.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $appends = array(
		'formatted_name',
		'formatted_price',
		'formatted_cost',
	);

	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @since 1.0.0
	 * @var bool
	 */
	protected $has_timestamps = true;

	/**
	 * The properties that are searchable.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $searchable = array(
		'name',
		'description',
	);

	/*
	|--------------------------------------------------------------------------
	| Accessors, Mutators, Relationship and Validation Methods
	|--------------------------------------------------------------------------
	| This section contains methods for getting and setting properties (accessors
	| and mutators) as well as defining relationships between models. It also includes
	| a data validation method that ensures data integrity before saving.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Cast item type.
	 *
	 * @param string $value Item type.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function set_item_type_attribute( $value ) {
		$this->attributes['type'] = array_key_exists( $value, EAC()->items->get_types() ) ? $value : 'standard';
	}

	/**
	 * Get formatted name.
	 *
	 * @since 1.1.6
	 * @return string
	 */
	protected function get_formatted_name_attribute() {
		return sprintf( '%s (#%s)', $this->name, $this->id );
	}

	/**
	 * Get formatted sale price.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_formatted_price_attribute() {
		return eac_format_amount( $this->price );
	}

	/**
	 * Get formatted purchase price.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	protected function get_formatted_cost_attribute() {
		return eac_format_amount( $this->cost );
	}

	/**
	 * Get items category
	 *
	 * @since 1.2.1
	 * @return BelongsTo
	 */
	public function category() {
		return $this->belongs_to( Category::class );
	}

	/**
	 * Get tax rates.
	 *
	 * @since 1.2.1
	 * @return BelongsToMany
	 */
	public function taxes() {
		return $this->belongs_to_many( Tax::class );
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
			return new \WP_Error( 'missing_required', __( 'Item name is required.', 'wp-ever-accounting' ) );
		}
		if ( empty( $this->type ) ) {
			return new \WP_Error( 'missing_required', __( 'Item type is required.', 'wp-ever-accounting' ) );
		}

		if ( empty( $this->cost ) ) {
			$this->cost = $this->price;
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
		return admin_url( 'admin.php?page=eac-items&tab=items&action=edit&id=' . $this->id );
	}

	/**
	 * Get view URL.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_view_url() {
		return admin_url( 'admin.php?page=eac-items&tab=items&action=view&id=' . $this->id );
	}
}
