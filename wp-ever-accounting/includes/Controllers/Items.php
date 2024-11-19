<?php

namespace EverAccounting\Controllers;

use EverAccounting\Models\Item;

defined( 'ABSPATH' ) || exit;

/**
 * Items controller.
 *
 * @since 1.0.0
 * @author  Sultan Nasir Uddin <manikdrmc@gmail.com>
 * @package EverAccounting
 * @subpackage Controllers
 */
class Items {

	/**
	 * Get an item from the database.
	 *
	 * @param mixed $item Item ID or object.
	 *
	 * @since 1.1.6
	 * @return Item|null Item object if found, otherwise null.
	 */
	public function get( $item ) {
		return Item::find( $item );
	}

	/**
	 * Insert a new item into the database.
	 *
	 * @param array $data Item data.
	 * @param bool  $wp_error Optional. Whether to return a WP_Error on failure. Default false.
	 *
	 * @since 1.1.0
	 * @return Item|false|\WP_Error Item object on success, false or WP_Error on failure.
	 */
	public function insert( $data, $wp_error = true ) {
		return Item::insert( $data, $wp_error );
	}

	/**
	 * Delete an item from the database.
	 *
	 * @param int $id Item ID.
	 *
	 * @since 1.1.0
	 * @return bool True on success, false on failure.
	 */
	public function delete( $id ) {
		$item = $this->get( $id );
		if ( ! $item ) {
			return false;
		}

		return $item->delete();
	}

	/**
	 * Get query results for items.
	 *
	 * @param array $args Query arguments.
	 * @param bool  $count Optional. Whether to return only the total found items for the query.
	 *
	 * @since 1.1.0
	 * @return array|int|Item[] Array of item objects, the total found items for the query, or the total found items for the query as int when `$count` is true.
	 */
	public function query( $args = array(), $count = false ) {
		if ( $count ) {
			return Item::count( $args );
		}

		return Item::results( $args );
	}

	/**
	 * Get item types.
	 *
	 * @since 1.0.2
	 * @return array
	 */
	public function get_types() {
		return apply_filters(
			'eac_item_types',
			array(
				'standard' => __( 'Standard', 'wp-ever-accounting' ),
				'fee'      => __( 'Fee', 'wp-ever-accounting' ),
			)
		);
	}

	/**
	 * Get item units
	 *
	 * @return array
	 * @since 1.1.6
	 */
	public function get_units() {
		return apply_filters(
			'eac_item_units',
			array(
				'box'   => __( 'Box', 'wp-ever-accounting' ),
				'cm'    => __( 'Centimeter', 'wp-ever-accounting' ),
				'day'   => __( 'Day', 'wp-ever-accounting' ),
				'doz'   => __( 'Dozen', 'wp-ever-accounting' ),
				'ft'    => __( 'Feet', 'wp-ever-accounting' ),
				'gm'    => __( 'Gram', 'wp-ever-accounting' ),
				'hr'    => __( 'Hour', 'wp-ever-accounting' ),
				'inch'  => __( 'Inch', 'wp-ever-accounting' ),
				'kg'    => __( 'Kilogram', 'wp-ever-accounting' ),
				'km'    => __( 'Kilometer', 'wp-ever-accounting' ),
				'l'     => __( 'Liter', 'wp-ever-accounting' ),
				'lb'    => __( 'Pound', 'wp-ever-accounting' ),
				'm'     => __( 'Meter', 'wp-ever-accounting' ),
				'mg'    => __( 'Milligram', 'wp-ever-accounting' ),
				'mile'  => __( 'Mile', 'wp-ever-accounting' ),
				'min'   => __( 'Minute', 'wp-ever-accounting' ),
				'mm'    => __( 'Millimeter', 'wp-ever-accounting' ),
				'month' => __( 'Month', 'wp-ever-accounting' ),
				'oz'    => __( 'Ounce', 'wp-ever-accounting' ),
				'pc'    => __( 'Piece', 'wp-ever-accounting' ),
				'sec'   => __( 'Second', 'wp-ever-accounting' ),
				'unit'  => __( 'Unit', 'wp-ever-accounting' ),
				'week'  => __( 'Week', 'wp-ever-accounting' ),
				'year'  => __( 'Year', 'wp-ever-accounting' ),
			)
		);
	}
}
