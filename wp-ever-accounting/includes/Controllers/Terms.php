<?php

namespace EverAccounting\Controllers;

use EverAccounting\Models\Term;

defined( 'ABSPATH' ) || exit;

/**
 * Payments controller.
 *
 * @since 2.0.0
 * @author  Sultan Nasir Uddin <manikdrmc@gmail.com>
 * @package EverAccounting
 * @subpackage Controllers
 */
class Terms {

	/**
	 * Get a term from the database.
	 *
	 * @param mixed $term Term ID or object.
	 *
	 * @since 2.0.0
	 * @return Term|null Term object if found, otherwise null.
	 */
	public function get( $term ) {
		return Term::find( $term );
	}

	/**
	 * Insert a new term into the database.
	 *
	 * @param array $data Term data.
	 * @param bool  $wp_error Optional. Whether to return a WP_Error on failure. Default false.
	 *
	 * @since 2.0.0
	 * @return Term|false|\WP_Error Term object on success, false or WP_Error on failure.
	 */
	public function insert( $data, $wp_error = true ) {
		return Term::insert( $data, $wp_error );
	}

	/**
	 * Delete a term from the database.
	 *
	 * @param int $id Term ID.
	 *
	 * @since 2.0.0
	 * @return bool True on success, false on failure.
	 */
	public function delete( $id ) {
		$term = $this->get( $id );
		if ( ! $term ) {
			return false;
		}

		return $term->delete();
	}

	/**
	 * Get query results for categories.
	 *
	 * @param array $args Query arguments.
	 * @param bool  $count Optional. Whether to return only the total found categories for the query.
	 *
	 * @since 1.1.0
	 * @return array|int|Term[] Array of category objects, the total found categories for the query, or the total found categories for the query as int when `$count` is true.
	 */
	public function query( $args = array(), $count = false ) {
		if ( $count ) {
			return Term::count( $args );
		}

		return Term::results( $args );
	}

	/**
	 * Get taxonomies.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_taxonomies() {
		$types = array(
			'category' => __( 'Category', 'wp-ever-accounting' ),
			'tax'      => __( 'Tax', 'wp-ever-accounting' ),
		);

		return apply_filters( 'eac_term_taxonomies', $types );
	}
}
