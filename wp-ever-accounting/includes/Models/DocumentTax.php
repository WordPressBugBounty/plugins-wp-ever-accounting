<?php

namespace EverAccounting\Models;

use EverAccounting\ByteKit\Models\Relations\BelongsTo;

/**
 * DocumentItemTax model.
 *
 * @since 1.0.0
 * @author  Sultan Nasir Uddin <manikdrmc@gmail.com>
 * @package EverAccounting
 * @subpackage Models
 *
 * @property int               $id ID of the item tax.
 * @property int               $document_id Document ID of the item tax.
 * @property int               $document_item_id Document item ID of the item tax.
 * @property int               $tax_id Tax ID of the item tax.
 * @property string            $name Name of the item tax.
 * @property double            $rate Rate of the item tax.
 * @property bool              $compound Compound of the item tax.
 * @property double            $amount Amount of the item tax.
 *
 * @property-read string       $formatted_name Formatted name of the item tax.
 * @property-read DocumentItem $item Item relationship.
 * @property-read Tax          $tax Tax relationship.
 * @property-read Document     $document Document relationship.
 */
class DocumentTax extends Model {

	/**
	 * The table associated with the model.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $table = 'ea_document_taxes';

	/**
	 * The table columns of the model.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $columns = array(
		'id',
		'document_id',
		'document_item_id',
		'tax_id',
		'name',
		'rate',
		'compound',
		'amount',
	);

	/**
	 * The attributes that should be cast.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $casts = array(
		'id'               => 'int',
		'document_id'      => 'int',
		'document_item_id' => 'int',
		'tax_id'           => 'int',
		'rate'             => 'double',
		'compound'         => 'bool',
		'amount'           => 'double',
	);

	/**
	 * The accessors to append to the model's array form.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $appends = array(
		'formatted_name',
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
	| Accessors, Mutators and Relationship Methods
	|--------------------------------------------------------------------------
	| This section contains methods for getting and setting attributes (accessors
	| and mutators) as well as defining relationships between models.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Get the formatted name of the document_item_tax.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	protected function get_formatted_name_attribute() {
		return $this->name . ' (' . $this->rate . '%)';
	}

	/**
	 * Item relationship.
	 *
	 * @since 1.0.0
	 * @return BelongsTo
	 */
	public function item() {
		return $this->belongs_to( DocumentItem::class );
	}

	/**
	 * Tax relationship.
	 *
	 * @since 1.0.0
	 * @return BelongsTo
	 */
	public function tax() {
		return $this->belongs_to( Tax::class );
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
		if ( empty( $this->rate ) ) {
			return new \WP_Error( 'missing_required', __( 'Tax rate is required.', 'wp-ever-accounting' ) );
		}

		if ( empty( $this->tax_id ) ) {
			return new \WP_Error( 'missing_required', __( 'Tax ID is required.', 'wp-ever-accounting' ) );
		}

		if ( empty( $this->document_id ) ) {
			return new \WP_Error( 'missing_required', __( 'Document ID is required.', 'wp-ever-accounting' ) );
		}

		return parent::save();
	}
}
