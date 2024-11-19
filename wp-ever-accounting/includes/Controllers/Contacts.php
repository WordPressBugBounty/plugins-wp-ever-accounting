<?php

namespace EverAccounting\Controllers;

use EverAccounting\Models\Contact;

defined( 'ABSPATH' ) || exit;

/**
 * Contacts controller.
 *
 * @since 1.0.0
 * @author  Sultan Nasir Uddin <manikdrmc@gmail.com>
 * @package EverAccounting
 * @subpackage Controllers
 */
class Contacts {

	/**
	 * Get a contact from the database.
	 *
	 * @param mixed $contact Contact ID or object.
	 *
	 * @since 1.1.6
	 * @return Contact|null Contact object if found, otherwise null.
	 */
	public function get( $contact ) {
		return Contact::find( $contact );
	}

	/**
	 * Insert a new contact into the database.
	 *
	 * @param array $data Contact data.
	 * @param bool  $wp_error Optional. Whether to return a WP_Error on failure. Default false.
	 *
	 * @since 1.1.0
	 * @return Contact|false|\WP_Error Contact object on success, false or WP_Error on failure.
	 */
	public function insert( $data, $wp_error = true ) {
		return Contact::insert( $data, $wp_error );
	}

	/**
	 * Delete a contact from the database.
	 *
	 * @param int $id Contact ID.
	 *
	 * @since 1.1.0
	 * @return bool True on success, false on failure.
	 */
	public function delete( $id ) {
		$contact = $this->get( $id );
		if ( ! $contact ) {
			return false;
		}

		return $contact->delete();
	}

	/**
	 * Get query results for contacts.
	 *
	 * @param array $args Query arguments.
	 * @param bool  $count Optional. Whether to return only the total found contacts for the query.
	 *
	 * @since 1.1.0
	 * @return array|int|Contact[] Array of contact objects, the total found contacts for the query, or the total found contacts for the query as int when `$count` is true.
	 */
	public function query( $args = array(), $count = false ) {
		if ( $count ) {
			return Contact::count( $args );
		}

		return Contact::results( $args );
	}

	/**
	 * Get contact types.
	 *
	 * @since 1.0.2
	 * @return array
	 */
	public function get_types() {
		$contact_types = array(
			'customer' => esc_html__( 'Customer', 'wp-ever-accounting' ),
			'vendor'   => esc_html__( 'Vendor', 'wp-ever-accounting' ),
		);

		return apply_filters( 'eac_contact_types', $contact_types );
	}
}
