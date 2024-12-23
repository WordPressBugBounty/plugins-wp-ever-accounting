<?php

namespace EverAccounting\Models;

use EverAccounting\ByteKit\Models\Relations\BelongsTo;
use EverAccounting\ByteKit\Models\Relations\HasMany;

/**
 * Invoice model.
 *
 * @since 1.0.0
 * @package EverAccounting
 * @subpackage Models
 * @extends Document
 *
 * @author  Sultan Nasir Uddin <manikdrmc@gmail.com>
 *
 * @property int    $id Invoice ID.
 * @property int    $vendor_id Vendor ID.
 * @property string $order_number Order number.
 * @property string $status_label Formatted status.
 *
 * @property Vendor $vendor Vendor relation.
 */
class Bill extends Document {
	/**
	 * The type of the object. Used for actions and filters. e.g. post, user, etc.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $object_type = 'bill';

	/**
	 * Default query variables passed to Query class when parsing.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $query_vars = array(
		'type' => 'bill',
	);

	/**
	 * The attributes that are searchable.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $searchable = array(
		'number',
		'contact_name',
		'contact_company',
		'contact_email',
		'contact_phone',
		'contact_address',
		'contact_city',
		'contact_state',
		'contact_postcode',
		'contact_country',
		'contact_tax_number',
	);

	/**
	 * Attributes that have transition effects when changed.
	 *
	 * This array lists attributes that should trigger transition effects when their values change.
	 * It is often used for managing state changes or triggering animations in user interfaces.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $transitionable = array(
		'status',
	);

	/**
	 * Create a new model instance.
	 *
	 * @param mixed $attributes The attributes to fill the model with.
	 */
	public function __construct( $attributes = null ) {
		$due_after        = get_option( 'eac_bill_due_date', 7 );
		$this->attributes = array_merge(
			$this->attributes,
			array(
				'type'       => $this->get_object_type(),
				'issue_date' => current_time( 'mysql' ),
				'due_date'   => wp_date( 'Y-m-d', strtotime( '+' . $due_after . ' days' ) ),
				'note'       => get_option( 'eac_bill_note', '' ),
				'currency'   => eac_base_currency(),
				'uuid'       => wp_generate_uuid4(),
			)
		);

		$this->aliases['vendor_id']    = 'contact_id';
		$this->aliases['order_number'] = 'reference';
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
	 * Get formatted status.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	protected function get_status_label_attribute() {
		$statuses = EAC()->bills->get_statuses();

		return array_key_exists( $this->status, $statuses ) ? $statuses[ $this->status ] : $this->status;
	}

	/**
	 * Vendor relation.
	 *
	 * @since 1.0.0
	 * @return BelongsTo
	 */
	public function vendor() {
		return $this->belongs_to( Vendor::class, 'contact_id' );
	}

	/**
	 * Payments relation.
	 *
	 * @since 1.0.0
	 * @return HasMany
	 */
	public function payments() {
		return $this->has_many( Expense::class, 'document_id' );
	}

	/**
	 * Notes relation.
	 *
	 * @since 1.0.0
	 * @return HasMany
	 */
	public function notes() {
		return $this->has_many( Note::class, 'parent_id' )->set( 'parent_type', 'bill' );
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

		// if number is empty, set next available number.
		if ( empty( $this->number ) ) {
			$this->number = $this->get_next_number();
		}

		// if status is paid but no payment date is set, set it to current time. also if not paid, set it to null.
		if ( 'paid' === $this->status && empty( $this->payment_date ) ) {
			$this->payment_date = current_time( 'mysql' );
		} elseif ( 'paid' !== $this->status ) {
			$this->payment_date = null;
		}

		// if status is not paid or partial, remove payments.
		if ( ! in_array( $this->status, array( 'paid', 'partial', 'overdue' ), true ) ) {
			$this->payments()->delete();
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
		$this->payments()->delete();

		return parent::delete();
	}

	/*
	|--------------------------------------------------------------------------
	| Line Item Handling
	|--------------------------------------------------------------------------
	| Line items are used for products, and fees within the document.
	*/

	/**
	 * Set items.
	 *
	 * @param array $items Items.
	 *
	 * @since 1.0.0
	 * @return $this
	 */
	public function set_items( $items ) {
		$items_total = 0;
		foreach ( $items as $i => &$itemdata ) {
			$quantity = isset( $itemdata['quantity'] ) ? floatval( $itemdata['quantity'] ) : 1;
			$item_id  = isset( $itemdata['item_id'] ) ? absint( $itemdata['item_id'] ) : 0;
			$item     = EAC()->items->get( $item_id );

			// If item not found, skip.
			if ( ! $item || $quantity <= 0 ) {
				unset( $items[ $i ] );
				continue;
			}

			$itemdata['name']        = isset( $itemdata['name'] ) ? sanitize_text_field( $itemdata['name'] ) : $item->name;
			$itemdata['description'] = isset( $itemdata['description'] ) ? sanitize_text_field( $itemdata['description'] ) : $item->description;
			$itemdata['unit']        = isset( $itemdata['unit'] ) ? sanitize_text_field( $itemdata['unit'] ) : $item->unit;
			$itemdata['type']        = isset( $itemdata['type'] ) ? sanitize_text_field( $itemdata['type'] ) : $item->type;
			$itemdata['price']       = isset( $itemdata['price'] ) ? floatval( $itemdata['price'] ) : $item->price;
			$itemdata['subtotal']    = $itemdata['price'] * $quantity;
			$itemdata['discount']    = 0;
			$itemdata['tax']         = 0;
			$itemdata['total']       = 0;

			if ( array_key_exists( 'taxes', $itemdata ) && is_array( $itemdata['taxes'] ) ) {
				foreach ( $itemdata['taxes'] as $j => &$taxdata ) {
					if ( ! is_array( $taxdata ) || empty( $taxdata ) ) {
						continue;
					}
					$taxdata['tax_id'] = isset( $taxdata['tax_id'] ) ? absint( $taxdata['tax_id'] ) : 0;
					$tax               = EAC()->taxes->get( $taxdata['tax_id'] );
					// If tax rate not found, skip.
					if ( ! $tax ) {
						unset( $itemdata['taxes'][ $j ] );
						continue;
					}
					$taxdata['name']   = isset( $taxdata['name'] ) ? sanitize_text_field( $taxdata['name'] ) : $tax->name;
					$taxdata['rate']   = isset( $taxdata['rate'] ) ? floatval( $taxdata['rate'] ) : $tax->rate;
					$taxdata['amount'] = 0;
				}
			}

			$items_total += 'standard' === $itemdata['type'] ? $itemdata['subtotal'] : 0;
		}

		// Discount calculation.
		$discount = 'percentage' === $this->discount_type ? ( $items_total * $this->discount_value ) / 100 : $this->discount_value;
		$discount = min( $discount, $items_total );
		foreach ( $items as $item ) {
			$item['discount']  = 'standard' === $item['type'] ? ( $discount / $items_total ) * $item['subtotal'] : 0;
			$line              = DocumentItem::make();
			$line->item_id     = $item['item_id'];
			$line->name        = $item['name'];
			$line->description = $item['description'];
			$line->unit        = $item['unit'];
			$line->type        = $item['type'];
			$line->quantity    = $item['quantity'];
			$line->price       = $item['price'];
			$line->subtotal    = $item['subtotal'];
			$line->discount    = min( $item['discount'], $item['subtotal'] );
			$line->tax         = $item['tax'];
			if ( array_key_exists( 'taxes', $item ) ) {
				$line->set_taxes( $item['taxes'] );
			}

			if ( $this->is_taxed() ) {
				$line->total = $line->subtotal - $line->discount + $line->tax;
			} else {
				$line->total = $line->subtotal - $line->discount;
			}
			$this->items = is_array( $this->items ) ? array_merge( $this->items, array( $line ) ) : array( $line );
		}

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
	 * Set next available number.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_next_number() {
		$max    = $this->get_max_number();
		$prefix = get_option( 'eac_bill_prefix', strtoupper( substr( $this->get_object_type(), 0, 3 ) ) . '-' );
		$number = str_pad( $max + 1, get_option( 'eac_bill_digits', 4 ), '0', STR_PAD_LEFT );

		return $prefix . $number;
	}

	/**
	 * Get total paid.
	 *
	 * @since 1.0.0
	 * @return float
	 */
	public function get_paid_amount() {
		$paid = 0;
		foreach ( $this->payments as $payment ) {
			$paid += eac_convert_currency( $payment->amount, $payment->exchange_rate, $this->exchange_rate );
		}

		return round( $paid, EAC()->currencies->get_precision( $this->currency ) );
	}

	/**
	 * Get the due amount.
	 *
	 * @since 1.0.0
	 * @return float
	 */
	public function get_due_amount() {
		$due = max( 0, $this->total - $this->get_paid_amount() );
		// we will ignore any decimal places so that dealing with multiple currencies is easier.
		return round( $due, 2 );
	}

	/**
	 * Calculate the totals amount of the invoice.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function calculate_totals() {
		$this->subtotal = $this->get_items_totals( 'subtotal', true );
		$this->discount = $this->get_items_totals( 'discount', true );
		$this->tax      = $this->get_items_totals( 'tax', true );
		$this->total    = $this->get_items_totals( 'total', true );

		// set the status based on the total.
		$paid_amount = $this->get_paid_amount();
		$due_amount  = $this->get_due_amount();
		if ( $paid_amount > 0 && $due_amount > 0 ) {
			$this->status = 'partial';
		} elseif ( round( $due_amount, 1 ) <= 0 ) {
			$this->status = 'paid';
		} elseif ( in_array( $this->status, array( 'paid', 'partial' ), true ) && $this->$paid_amount <= 0 && 'overdue' !== $this->status ) {
			$this->status = 'sent';
		}

		return array(
			'subtotal' => $this->subtotal,
			'discount' => $this->discount,
			'tax'      => $this->tax,
			'total'    => $this->total,
			'balance'  => $due_amount,
		);
	}

	/**
	 * Get totals.
	 *
	 * @param string $column Column name.
	 * @param bool   $round Round the value or not.
	 *
	 * @since 1.0.0
	 */
	public function get_items_totals( $column = 'total', $round = false ) {
		$total = 0;
		foreach ( $this->items as $item ) {
			$amount = $item->$column ?? 0;
			$total += $round ? round( $amount, 2 ) : $amount;
		}

		return $round ? round( $total, 2 ) : $total;
	}


	/**
	 * Get itemized taxes.
	 *
	 * @since 1.0.0
	 * @return DocumentTax[]
	 */
	public function get_itemized_taxes() {
		$taxes = array();
		foreach ( $this->items as $item ) {
			if ( ! empty( $item->taxes ) ) {
				foreach ( $item->taxes as $tax ) {
					if ( ! isset( $taxes[ $tax->tax_id ] ) ) {
						$taxes[ $tax->tax_id ] = $tax;
					} else {
						$taxes[ $tax->tax_id ]->amount += $tax->amount;
					}
				}
			}
		}

		return $taxes;
	}

	/**
	 * Is taxed.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function is_taxed() {
		return 'yes' === get_option( 'eac_tax_enabled', 'no' ) || ( $this->exists() && $this->tax > 0 );
	}

	/**
	 * Checks if the invoice has a given status.
	 *
	 * @param string $status Status to check.
	 *
	 * @since 1.1.0
	 * @return bool
	 */
	public function is_status( $status ) {
		return $this->status === $status;
	}


	/**
	 * Returns if an order has been paid for based on the order status.
	 *
	 * @since 1.10
	 * @return bool
	 */
	public function is_paid() {
		return $this->is_status( 'paid' );
	}

	/**
	 * Checks if the invoice is draft.
	 *
	 * @since 1.1.0
	 * @return bool
	 */
	public function is_draft() {
		return $this->is_status( 'draft' );
	}

	/**
	 * Checks if the invoice is due.
	 *
	 * @since 1.1.0
	 * @return bool
	 */
	public function is_overdue() {
		return ! ( empty( $this->due_date ) || $this->is_paid() ) && strtotime( date_i18n( 'Y-m-d 23:59:00' ) ) > strtotime( date_i18n( 'Y-m-d 23:59:00', strtotime( $this->due_date ) ) );
	}

	/**
	 * Checks if an order needs payment, based on status and order total.
	 *
	 * @return bool
	 */
	public function needs_payment() {
		return ! $this->is_status( 'paid' ) && $this->total > 0;
	}

	/**
	 * Get edit URL.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_edit_url() {
		return admin_url( 'admin.php?page=eac-purchases&tab=bills&action=edit&id=' . $this->id );
	}

	/**
	 * Get view URL.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_view_url() {
		return admin_url( 'admin.php?page=eac-purchases&tab=bills&action=view&id=' . $this->id );
	}

	/**
	 * Get the public URL.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_public_url() {
		return site_url( 'eac/bill/?uuid=' . $this->uuid );
	}
}
