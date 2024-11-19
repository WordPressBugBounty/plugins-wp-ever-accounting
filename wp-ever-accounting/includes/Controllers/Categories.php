<?php

namespace EverAccounting\Controllers;

use EverAccounting\Models\Category;

defined( 'ABSPATH' ) || exit;

/**
 * Categories controller.
 *
 * @since 1.0.0
 * @author  Sultan Nasir Uddin <manikdrmc@gmail.com>
 * @package EverAccounting
 * @subpackage Controllers
 */
class Categories {

	/**
	 * Get a category from the database.
	 *
	 * @param mixed $category Category ID or object.
	 *
	 * @since 1.1.6
	 * @return Category|null Category object if found, otherwise null.
	 */
	public function get( $category ) {
		return Category::find( $category );
	}

	/**
	 * Insert a new category into the database.
	 *
	 * @param array $data Category data.
	 * @param bool  $wp_error Optional. Whether to return a WP_Error on failure. Default false.
	 *
	 * @since 1.1.0
	 * @return Category|false|\WP_Error Category object on success, false or WP_Error on failure.
	 */
	public function insert( $data, $wp_error = true ) {
		return Category::insert( $data, $wp_error );
	}

	/**
	 * Delete a category from the database.
	 *
	 * @param int $id Category ID.
	 *
	 * @since 1.1.0
	 * @return bool True on success, false on failure.
	 */
	public function delete( $id ) {
		$category = $this->get( $id );
		if ( ! $category ) {
			return false;
		}

		return $category->delete();
	}

	/**
	 * Get query results for categories.
	 *
	 * @param array $args Query arguments.
	 * @param bool  $count Optional. Whether to return only the total found categories for the query.
	 *
	 * @since 1.1.0
	 * @return array|int|Category[] Array of category objects, the total found categories for the query, or the total found categories for the query as int when `$count` is true.
	 */
	public function query( $args = array(), $count = false ) {
		if ( $count ) {
			return Category::count( $args );
		}

		return Category::results( $args );
	}

	/**
	 * Get category types.
	 *
	 * @since 1.0.2
	 * @return array
	 */
	public function get_types() {
		$types = array(
			'item'    => esc_html__( 'Item', 'wp-ever-accounting' ),
			'payment' => esc_html__( 'Payment', 'wp-ever-accounting' ),
			'expense' => esc_html__( 'Expense', 'wp-ever-accounting' ),
		);

		return apply_filters( 'eac_category_types', $types );
	}
}
