<?php

namespace EverAccounting\Models;

use EverAccounting\ByteKit\Models\Relations\BelongsTo;
use EverAccounting\ByteKit\Models\Relations\HasMany;

/**
 * DocumentItem model.
 *
 * @since 1.0.0
 * @author  Sultan Nasir Uddin <manikdrmc@gmail.com>
 * @package EverAccounting
 * @subpackage Models
 *
 * @property int           $id ID of the item.
 * @property int           $document_id Document ID of the item.
 * @property int           $item_id Item ID of the item.
 * @property string        $type Type of the item.
 * @property string        $name Name of the item.
 * @property string        $description Description of the item.
 * @property string        $unit Unit of the item.
 * @property double        $price Price of the item.
 * @property double        $quantity Quantity of the item.
 * @property double        $subtotal Subtotal of the item.
 * @property double        $discount Discount of the item.
 * @property double        $tax Tax total of the item.
 * @property double        $total Total of the item.
 *
 * @property double        $discounted_subtotal Discounted subtotal of the document_item.
 * @property DocumentTax[] $taxes Taxes of the document_item.
 */
class DocumentItem extends Model {

	/**
	 * The table associated with the model.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $table = 'ea_document_items';

	/**
	 * The table columns of the model.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $columns = array(
		'id',
		'document_id',
		'item_id',
		'type',
		'name',
		'description',
		'unit',
		'price',
		'quantity',
		'subtotal',
		'discount',
		'tax',
		'total',
	);

	/**
	 * The attributes of the model.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $attributes = array(
		'type'     => 'standard',
		'quantity' => 1,
	);

	/**
	 * The attributes that should be cast.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $casts = array(
		'id'          => 'int',
		'item_id'     => 'int',
		'document_id' => 'int',
		'price'       => 'double',
		'quantity'    => 'double',
		'subtotal'    => 'double',
		'discount'    => 'double',
		'tax'         => 'double',
		'total'       => 'double',
	);

	/**
	 * Default query variables passed to Query class.
	 *
	 * This array contains default variables that are passed to the Query class when performing queries.
	 * These default values can be customized or overridden as needed.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $query_vars = array(
		'orderby' => 'id',
		'order'   => 'ASC',
	);

	/*
	|--------------------------------------------------------------------------
	| Property Definition Methods
	|--------------------------------------------------------------------------
	| This section contains static methods that define and return specific
	| property values related to the model.
	| These methods are accessible without creating an instance of the model.
	|--------------------------------------------------------------------------
	*/

	/*
	|--------------------------------------------------------------------------
	| Accessors, Mutators and Relationship Methods
	|--------------------------------------------------------------------------
	| This section contains methods for getting and setting attributes (accessors
	| and mutators) as well as defining relationships between models.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Tax relationship.
	 *
	 * @since 1.0.0
	 * @return HasMany
	 */
	public function taxes() {
		return $this->has_many( DocumentTax::class, 'document_item_id' );
	}

	/**
	 * Item relationship.
	 *
	 * @since 1.0.0
	 * @return BelongsTo
	 */
	public function item() {
		return $this->belongs_to( Item::class );
	}

	/**
	 * Document relationship.
	 *
	 * @since 1.0.0
	 * @return BelongsTo
	 */
	public function document() {
		return $this->belongs_to( Document::class );
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
		if ( empty( $this->type ) ) {
			return new \WP_Error( 'required_missing', __( 'Product type is required.', 'wp-ever-accounting' ) );
		}

		if ( empty( $this->quantity ) ) {
			return new \WP_Error( 'required_missing', __( 'Product quantity is required.', 'wp-ever-accounting' ) );
		}

		if ( empty( $this->document_id ) ) {
			return new \WP_Error( 'required_missing', __( 'Document ID is required.', 'wp-ever-accounting' ) );
		}

		return parent::save();
	}

	/**
	 * Delete the object from the database.
	 *
	 * @since 1.0.0
	 * @return true|\WP_Error True on success, WP_Error on failure.
	 */
	public function delete() {
		$this->taxes()->delete();

		return parent::delete();
	}

	/*
	|--------------------------------------------------------------------------
	| Tax Item Handling
	|--------------------------------------------------------------------------
	| Document item taxes are used for calculating taxes on each item.
	*/

	/**
	 * Set taxes.
	 *
	 * @param array $taxes Items.
	 *
	 * @since 1.0.0
	 * @return $this
	 */
	public function set_taxes( $taxes ) {
		$this->taxes()->delete();
		$this->taxes = array();
		foreach ( $taxes as $tax_data ) {
			if ( ! is_array( $tax_data ) || empty( $tax_data ) ) {
				continue;
			}
			$tax_data['tax_id'] = isset( $tax_data['tax_id'] ) ? absint( $tax_data['tax_id'] ) : 0;
			$tax                = EAC()->taxes->get( $tax_data['tax_id'] );

			// If tax rate not found, skip.
			if ( ! $tax ) {
				continue;
			}
			$tax_data                  = wp_parse_args(
				$tax_data,
				array(
					'name' => $tax->name,
					'rate' => $tax->rate,
				)
			);
			$doc_tax                   = DocumentTax::make( $tax_data );
			$doc_tax->tax_id           = $tax->id;
			$doc_tax->document_id      = $this->document_id;
			$doc_tax->document_item_id = $this->id;
			$doc_tax->amount           = 0;
			if ( $this->has_tax( $doc_tax->tax_id ) ) {
				continue;
			}

			$this->taxes = array_merge( $this->taxes, array( $doc_tax ) );
		}

		// Update item taxes.
		$disc_subtotal = max( 0, $this->subtotal - $this->discount );
		$simple_tax    = 0;
		$compound_tax  = 0;

		foreach ( $this->taxes as $item_tax ) {
			$item_tax->amount = $item_tax->compound ? 0 : ( $disc_subtotal * $item_tax->rate / 100 );
			$simple_tax      += $item_tax->compound ? 0 : $item_tax->amount;
		}

		foreach ( $this->taxes as $item_tax ) {
			if ( $item_tax->compound ) {
				$item_tax->amount = ( $disc_subtotal + $simple_tax ) * $item_tax->rate / 100;
				$compound_tax    += $item_tax->amount;
			}
		}

		$this->tax = $simple_tax + $compound_tax;

		return $this;
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
	 * Determine if a given tax is applied to the item or not.
	 *
	 * @param int $tax_id Tax ID.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function has_tax( $tax_id ) {
		foreach ( $this->taxes as $tax ) {
			if ( $tax->tax_id === $tax_id ) {
				return true;
			}
		}

		return false;
	}
}
