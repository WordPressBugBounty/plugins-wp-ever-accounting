<?php

namespace ByteKit\Models\Example;

/**
 * Example Country model.
 *
 * @since 1.0.0
 */
class Country extends \ByteKit\Models\Model {
	/**
	 * The table associated with the model.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $table_name = 'countries';

	/**
	 * Meta type.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $meta_type = 'country';

	/**
	 * Columns of the table.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $columns = array(
		'id',
		'code',
		'name',
		'continent',
		'region',
		'surface_area',
		'indep_year',
		'population',
		'life_expectancy',
		'gnp',
		'local_name',
		'government_form',
		'president',
	);

	/**
	 * Model's casts data.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $casts = array(
		'surface_area'    => 'float',
		'indep_year'      => 'int',
		'population'      => 'int',
		'life_expectancy' => 'float',
		'gnp'             => 'float',
	);

	/**
	 * Model's aliases data.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $aliases = array();

	/**
	 * Searchable properties.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $searchable = array(
		'name',
		'local_name',
		'region',
	);

	/**
	 * Default query arguments.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected $query_args = array();
}
