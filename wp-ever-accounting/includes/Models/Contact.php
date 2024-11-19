<?php

namespace EverAccounting\Models;

use EverAccounting\Utilities\I18nUtil;

defined( 'ABSPATH' ) || exit;

/**
 * Class Contact
 *
 * @since   1.0.0
 * @package EverAccounting\Models
 *
 * @property int          $id ID of the contact.
 * @property string       $type Type of the contact.
 * @property string       $name Name of the contact.
 * @property string       $company Company of the contact.
 * @property string       $email Email of the contact.
 * @property string       $phone Phone of the contact.
 * @property string       $website Website url of the contact.
 * @property string       $address Address line of the contact.
 * @property string       $city City of the contact.
 * @property string       $state State of the contact.
 * @property string       $postcode Postcode of the contact.
 * @property string       $country Country of the contact.
 * @property string       $tax_number Tax number of the contact.
 * @property string       $currency Currency code of the contact.
 * @property int          $user_id User ID of the contact.
 * @property string       $created_via Created via of the contact.
 * @property string       $date_created Date created of the contact.
 * @property string       $date_updated Date updated of the contact.
 *
 * @property-read  string $formatted_name Get formatted name.
 * @property-read  string $country_name Get country name.
 */
class Contact extends Model {
	/**
	 * The table associated with the model.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $table = 'ea_contacts';

	/**
	 * Meta type declaration for the object.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $meta_type = 'ea_contact';

	/**
	 * Table columns.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $columns = array(
		'id',
		'type',
		'name',
		'company',
		'email',
		'phone',
		'website',
		'address',
		'city',
		'state',
		'postcode',
		'country',
		'tax_number',
		'currency',
		'user_id',
		'created_via',
	);

	/**
	 * The attributes that should be cast.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $casts = array(
		'id'          => 'int',
		'type'        => 'sanitize_text',
		'name'        => 'sanitize_text',
		'company'     => 'sanitize_text',
		'email'       => 'sanitize_email',
		'phone'       => 'sanitize_text',
		'website'     => 'sanitize_url',
		'address'     => 'sanitize_text',
		'city'        => 'sanitize_text',
		'state'       => 'sanitize_text',
		'postcode'    => 'sanitize_text',
		'country'     => 'sanitize_text',
		'tax_number'  => 'sanitize_text',
		'currency'    => 'sanitize_text',
		'user_id'     => 'int',
		'created_via' => 'sanitize_text',
	);

	/**
	 * The accessors to append to the model's array form.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $appends = array(
		'formatted_name',
		'country_name',
	);

	/**
	 * The attributes that should be hidden for serialization.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $hidden = array(
		'type',
	);

	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @since 1.0.0
	 * @var bool
	 */
	protected $has_timestamps = true;

	/**
	 * The attributes that are searchable.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $searchable = array(
		'name',
		'company',
		'email',
		'phone',
		'address',
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
	 * Get country name.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	protected function get_country_name_attribute() {
		$countries = I18nUtil::get_countries();

		return isset( $countries[ $this->country ] ) ? $countries[ $this->country ] : $this->country;
	}

	/**
	 * Get formatted name.
	 *
	 * @since 1.1.6
	 * @return string
	 */
	protected function get_formatted_name_attribute() {
		$company = $this->company ? ' (' . $this->company . ')' : '';

		return $this->name . $company;
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
			return new \WP_Error( 'missing_required', __( 'Name is required.', 'wp-ever-accounting' ) );
		}

		if ( empty( $this->currency ) ) {
			$this->set( 'currency', eac_base_currency() );
		}

		if ( empty( $this->author_id ) && is_user_logged_in() ) {
			$this->author_id = get_current_user_id();
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
