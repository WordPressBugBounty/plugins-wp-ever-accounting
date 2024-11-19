<?php

namespace EverAccounting\Models;

use EverAccounting\ByteKit\Models\Relations\BelongsTo;

defined( 'ABSPATH' ) || exit;

/**
 * Transaction model.
 *
 * @since 1.0.0
 * @author  Sultan Nasir Uddin <manikdrmc@gmail.com>
 * @package EverAccounting
 * @subpackage Models
 *
 * @property int             $id ID of the item.
 * @property string          $type Type of the transaction.
 * @property string          $number Number of the transaction.
 * @property string          $payment_date Date of the transaction.
 * @property double          $amount Amount of the transaction.
 * @property string          $currency Currency of the transaction.
 * @property double          $exchange_rate Exchange rate of the transaction.
 * @property string          $reference Reference of the transaction.
 * @property string          $note Note of the transaction.
 * @property string          $payment_method Payment mode of the transaction.
 * @property int             $account_id Account ID of the transaction.
 * @property int             $document_id Document ID of the transaction.
 * @property int             $contact_id Contact ID of the transaction.
 * @property int             $category_id Category ID of the transaction.
 * @property int             $attachment_id Attachment ID of the transaction.
 * @property int             $author_id Author ID of the transaction.
 * @property int             $parent_id Parent ID of the transaction.
 * @property bool            $editable Whether the transaction is editable.
 * @property string          $created_via Created via of the transaction.
 * @property string          $uuid UUID of the transaction.
 * @property string          $date_updated Date the transaction was last updated.
 * @property string          $date_created Date the transaction was created.
 *
 * @property-read string     $formatted_amount Formatted amount of the transaction.
 * @property-read Account    $account Related account.
 * @property-read Category   $category Related category.
 * @property-read Contact    $contact Related contact.
 * @property-read Customer   $customer Related customer.
 * @property-read Vendor     $vendor Related vendor.
 * @property-read Attachment $attachment Related attachment.
 */
class Transaction extends Model {
	/**
	 * The table associated with the model.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $table = 'ea_transactions';

	/**
	 * Meta type declaration for the object.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $meta_type = 'ea_transaction';

	/**
	 * Table columns.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $columns = array(
		'id',
		'type',
		'number',
		'payment_date',
		'amount',
		'currency',
		'exchange_rate',
		'reference',
		'note',
		'payment_method',
		'account_id',
		'document_id',
		'contact_id',
		'category_id',
		'attachment_id',
		'author_id',
		'parent_id',
		'editable',
		'created_via',
		'uuid',
		'date_updated',
		'date_created',
	);

	/**
	 * The attributes of the model.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $attributes = array(
		'created_via' => 'manual',
	);

	/**
	 * The attributes that should be cast.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $casts = array(
		'id'             => 'int',
		'type'           => 'sanitize_text',
		'number'         => 'sanitize_text',
		'payment_date'   => 'date',
		'amount'         => 'float',
		'currency'       => 'sanitize_text',
		'exchange_rate'  => 'double',
		'reference'      => 'sanitize_text',
		'note'           => 'sanitize_textarea',
		'payment_method' => 'sanitize_text',
		'account_id'     => 'int',
		'document_id'    => 'int',
		'contact_id'     => 'int',
		'category_id'    => 'int',
		'attachment_id'  => 'int',
		'author_id'      => 'int',
		'parent_id'      => 'int',
		'editable'       => 'bool',
		'created_via'    => 'sanitize_text',
		'uuid'           => 'sanitize_text',
	);

	/**
	 * The accessors to append to the model's array form.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $appends = array(
		'formatted_number',
		'formatted_amount',
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
		'number',
		'note',
	);

	/**
	 * Create a new model instance.
	 *
	 * @param string|array|object $attributes The model attributes.
	 */
	public function __construct( $attributes = array() ) {
		$this->attributes['uuid']     = wp_generate_uuid4();
		$this->attributes['currency'] = eac_base_currency();
		parent::__construct( $attributes );
	}

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
	 * Returns the formatted number.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	protected function get_formatted_number_attribute() {
		$number = empty( $this->number ) ? $this->get_next_number() : $this->number;
		$prefix = strtoupper( substr( $this->type, 0, 3 ) ) . '-';
		$next   = str_pad( $number, 4, '0', STR_PAD_LEFT );

		return $prefix . $next;
	}

	/**
	 * Returns the formatted amount.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	protected function get_formatted_amount_attribute() {
		return eac_format_amount( $this->amount, $this->currency );
	}

	/**
	 * Transaction related account.
	 *
	 * @since 1.0.0
	 * @return BelongsTo
	 */
	public function account() {
		return $this->belongs_to( Account::class );
	}

	/**
	 * Transaction related category.
	 *
	 * @since 1.0.0
	 * @return BelongsTo
	 */
	public function category() {
		return $this->belongs_to( Category::class );
	}

	/**
	 * Transaction related contact.
	 *
	 * @since 1.0.0
	 * @return BelongsTo
	 */
	public function customer() {
		return $this->belongs_to( Customer::class, 'contact_id' );
	}

	/**
	 * Transaction related contact.
	 *
	 * @since 1.0.0
	 * @return BelongsTo
	 */
	public function vendor() {
		return $this->belongs_to( Vendor::class, 'contact_id' );
	}

	/**
	 * Attachment relation.
	 *
	 * @since 1.0.0
	 * @return BelongsTo
	 */
	public function attachment() {
		return $this->belongs_to( Attachment::class, 'attachment_id' );
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

		// check if the number is empty.
		if ( empty( $this->number ) ) {
			$this->number = $this->get_next_number();
		}

		if ( empty( $this->uuid ) ) {
			$this->uuid = wp_generate_uuid4();
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
	/**
	 * Get max voucher number.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_max_number() {
		global $wpdb;
		$max = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT `number` FROM {$wpdb->prefix}{$this->table} WHERE `type` = %s ORDER BY `number` DESC", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Already prepared.
				$this->type
			)
		);

		// Get the number part of the max number using regex.
		return (int) preg_replace( '/[^0-9]/', '', $max );
	}

	/**
	 * Set next transaction number.
	 *
	 * @since 1.0.0
	 * @return int
	 */
	public function get_next_number() {
		$max = (int) $this->get_max_number();

		return $max + 1;
	}
}
