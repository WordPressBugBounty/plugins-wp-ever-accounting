<?php

namespace EverAccounting\Models;

use EverAccounting\ByteKit\Models\Relations\BelongsTo;

defined( 'ABSPATH' ) || exit;

/**
 * Transfer model.
 *
 * @since 1.0.0
 * @author  Sultan Nasir Uddin <manikdrmc@gmail.com>
 * @package EverAccounting
 * @subpackage Models
 *
 * @property int          $id ID of the item.
 * @property int          $expense_id Expense ID of the transfer.
 * @property int          $payment_id Payment ID of the item.
 * @property string       $transfer_date Date the transfer was transferred.
 * @property float        $amount Amount of the transfer.
 * @property string       $currency Currency of the transfer.
 * @property string       $payment_method Payment method of the transfer.
 * @property string       $reference Reference of the transfer.
 * @property string       $note Note of the transfer.
 * @property string       $date_created Date the transfer was created.
 * @property string       $date_updated Date the transfer was last updated.
 *
 * @property-read string  $formatted_amount Formatted amount.
 * @property-read Payment $payment Payment object.
 * @property-read Expense $expense Expense object.
 */
class Transfer extends Model {
	/**
	 * The table associated with the model.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $table = 'ea_transfers';

	/**
	 * The table columns of the model.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $columns = array(
		'id',
		'expense_id',
		'payment_id',
		'transfer_date',
		'amount',
		'currency',
		'payment_method',
		'reference',
		'note',
	);

	/**
	 * The attributes that should be cast.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $casts = array(
		'payment_id'     => 'int',
		'expense_id'     => 'int',
		'transfer_date'  => 'datetime',
		'amount'         => 'double',
		'currency'       => 'string',
		'payment_method' => 'string',
		'reference'      => 'string',
		'note'           => 'string',
	);

	/**
	 * The searchable attributes.
	 *
	 * This array lists the properties that can be searched when querying the model.
	 * It is used to filter results based on user input or other criteria.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $searchable = array(
		'reference',
		'amount',
		'date',
	);

	/**
	 * Whether the model should be timestamped.
	 *
	 * @since 1.0.0
	 * @var bool
	 */
	protected $has_timestamps = true;

	/*
	|--------------------------------------------------------------------------
	| Accessors, Mutators and Relationship
	|--------------------------------------------------------------------------
	| This section contains methods for getting and setting properties (accessors
	| and mutators) as well as defining relationships between models.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Formatted amount.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_formatted_amount_attribute() {
		return eac_format_amount( $this->amount, $this->currency );
	}

	/**
	 * Payment relationship.
	 *
	 * @since 1.0.0
	 * @return BelongsTo
	 */
	public function payment() {
		return $this->belongs_to( Payment::class, 'payment_id' );
	}

	/**
	 * Expense relationship.
	 *
	 * @since 1.0.0
	 * @return BelongsTo
	 */
	public function expense() {
		return $this->belongs_to( Expense::class, 'expense_id' );
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
		$from_account_id = $this->from_account_id ? $this->from_account_id : ( $this->expense && $this->expense->account_id ? $this->expense->account_id : 0 );
		$to_account_id   = $this->to_account_id ? $this->to_account_id : ( $this->payment && $this->payment->account_id ? $this->payment->account_id : 0 );
		// Prepare vars.
		$from_account = Account::find( $this->from_account_id );
		$to_account   = Account::find( $this->to_account_id );
		$expense      = Expense::make( $this->expense_id );
		$payment      = Payment::make( $this->payment_id );

		// Check if accounts are valid.
		if ( ! $from_account || ! $to_account ) {
			return new \WP_Error( 'invalid_account', __( 'Invalid account.', 'wp-ever-accounting' ) );
		}

		// Check if accounts are same.
		if ( $from_account_id === $to_account_id ) {
			return new \WP_Error( 'same_account', __( 'From and to accounts cannot be the same.', 'wp-ever-accounting' ) );
		}

		// Check if amount is valid.
		if ( empty( $this->amount ) || ! is_numeric( $this->amount ) ) {
			return new \WP_Error( 'invalid_amount', __( 'Invalid amount.', 'wp-ever-accounting' ) );
		}

		if ( ! empty( $this->from_exchange_rate ) ) {
			$from_rate = floatval( $this->from_exchange_rate );
		} elseif ( ! empty( $this->expense ) && ! empty( $this->expense->exchange_rate ) ) {
			$from_rate = floatval( $this->expense->exchange_rate );
		} else {
			$from_rate = floatval( EAC()->currencies->get_rate( $from_account->currency ) );
		}

		if ( ! empty( $this->to_exchange_rate ) ) {
			$to_rate = floatval( $this->to_exchange_rate );
		} elseif ( ! empty( $this->payment ) && ! empty( $this->payment->exchange_rate ) ) {
			$to_rate = floatval( $this->payment->exchange_rate );
		} else {
			$to_rate = floatval( EAC()->currencies->get_rate( $to_account->currency ) );
		}

		if ( empty( $this->transfer_date ) ) {
			$this->transfer_date = current_time( 'mysql' );
		}

		$expense->fill(
			array(
				'status'         => 'completed',
				'payment_date'   => $this->transfer_date,
				'amount'         => $this->amount,
				'currency'       => $from_account->currency,
				'exchange_rate'  => $from_rate,
				'reference'      => $this->reference,
				'note'           => $this->note,
				'payment_method' => $this->payment_method,
				'account_id'     => $from_account_id,
				'editable'       => false,
			)
		);

		$ret_val1 = $expense->save();
		if ( is_wp_error( $ret_val1 ) ) {
			return $ret_val1;
		}

		$amount = $this->amount;
		if ( $from_account->currency !== $to_account->currency ) {
			$amount = eac_convert_currency( $amount, $from_rate, $to_rate );
		}

		$payment->fill(
			array(
				'status'         => 'completed',
				'payment_date'   => $this->transfer_date,
				'amount'         => $amount,
				'currency'       => $to_account->currency,
				'exchange_rate'  => $to_rate,
				'reference'      => $this->reference,
				'note'           => $this->note,
				'payment_method' => $this->payment_method,
				'account_id'     => $to_account_id,
				'editable'       => false,
			)
		);

		$ret_val2 = $payment->save();
		if ( is_wp_error( $ret_val2 ) ) {
			return $ret_val2;
		}

		$this->fill(
			array(
				'expense_id' => $expense->id,
				'payment_id' => $payment->id,
				'currency'   => $from_account->currency,
			)
		);

		return parent::save();
	}

	/**
	 * Delete the object from the database.
	 *
	 * @since 1.0.0
	 * @return true|\WP_Error True on success, WP_Error on failure.
	 */
	public function delete() {
		$this->expense()->delete();
		$this->payment()->delete();

		return parent::delete();
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
		return admin_url( 'admin.php?page=eac-banking&tab=transfers&action=edit&id=' . $this->id );
	}

	/**
	 * Get view URL.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_view_url() {
		return admin_url( 'admin.php?page=eac-banking&tab=transfers&action=view&id=' . $this->id );
	}
}
