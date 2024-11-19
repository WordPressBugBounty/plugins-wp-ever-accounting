<?php

namespace EverAccounting\Models;

defined( 'ABSPATH' ) || exit;

/**
 * Term model.
 *
 * @since 1.0.0
 * @author  Sultan Nasir Uddin <manikdrmc@gmail.com>
 * @package EverAccounting
 * @subpackage Models
 * @extends Model Term model.
 *
 * @property int    $id ID of the category.
 * @property string $name Name of the category.
 * @property string $description Description of the category.
 * @property string $type Type of the category.
 * @property string $taxonomy Taxonomy of the category.
 * @property int    $parent_id Parent ID of the category.
 * @property string $date_created Date created of the category.
 * @property string $date_updated Date updated of the category.
 */
class Term extends Model {
	/**
	 * The table associated with the model.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $table = 'ea_terms';

	/**
	 * Meta type declaration for the object.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $meta_type = 'ea_term';

	/**
	 * Table columns.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $columns = array(
		'id',
		'name',
		'description',
		'type',
		'taxonomy',
		'parent_id',
	);

	/**
	 * The model's attributes.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $attributes = array();

	/**
	 * The attributes that should be cast.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $casts = array(
		'name'        => 'sanitize_text',
		'description' => 'sanitize_textarea',
		'type'        => 'sanitize_key',
		'taxonomy'    => 'sanitize_key',
		'parent_id'   => 'int',
	);

	/**
	 * The attributes that are searchable.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $searchable = array(
		'name',
		'description',
	);

	/**
	 * Indicates if the model should be timestamped.
	 *
	 * This boolean flag determines if the model should automatically manage `created_at` and `updated_at` timestamps.
	 *
	 * @since 1.0.0
	 * @var bool
	 */
	protected $has_timestamps = true;

	/**
	 * Create a new model instance.
	 *
	 * @param string|int|array $attributes Attributes.
	 *
	 * @return void
	 */
	public function __construct( $attributes = null ) {
		$this->attributes['taxonomy'] = $this->get_object_type();
		$this->query_vars['taxonomy'] = $this->get_object_type();
		$this->hidden[]               = 'taxonomy';
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
	 * Set taxonomy attribute.
	 *
	 * @param string $value Type of the term.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	protected function set_taxonomy_attribute( $value ) {
		if ( ! array_key_exists( $value, EAC()->terms->get_taxonomies() ) ) {
			$value = '';
		}

		$this->attributes['taxonomy'] = sanitize_text_field( $value );
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
			return new \WP_Error( 'missing_required', __( 'Term name is required.', 'wp-ever-accounting' ) );
		}
		if ( empty( $this->taxonomy ) ) {
			return new \WP_Error( 'missing_required', __( 'Term type is required.', 'wp-ever-accounting' ) );
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
}
