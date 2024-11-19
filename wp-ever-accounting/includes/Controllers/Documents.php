<?php

namespace EverAccounting\Controllers;

use EverAccounting\Models\Document;

defined( 'ABSPATH' ) || exit;

/**
 * Documents controller.
 *
 * @since 1.0.0
 * @author  Sultan Nasir Uddin <manikdrmc@gmail.com>
 * @package EverAccounting
 * @subpackage Controllers
 */
class Documents {

	/**
	 * Get a document from the database.
	 *
	 * @param mixed $document Document ID or object.
	 *
	 * @since 1.1.6
	 * @return Document|null Document object if found, otherwise null.
	 */
	public function get( $document ) {
		return Document::find( $document );
	}

	/**
	 * Insert a new document into the database.
	 *
	 * @param array $data Document data.
	 * @param bool  $wp_error Optional. Whether to return a WP_Error on failure. Default false.
	 *
	 * @since 1.1.0
	 * @return Document|false|\WP_Error Document object on success, false or WP_Error on failure.
	 */
	public function insert( $data, $wp_error = true ) {
		return Document::insert( $data, $wp_error );
	}

	/**
	 * Delete a document from the database.
	 *
	 * @param int $id Document ID.
	 *
	 * @since 1.1.0
	 * @return bool True on success, false on failure.
	 */
	public function delete( $id ) {
		$document = $this->get( $id );
		if ( ! $document ) {
			return false;
		}

		return $document->delete();
	}

	/**
	 * Get query results for documents.
	 *
	 * @param array $args Query arguments.
	 * @param bool  $count Optional. Whether to return only the total found documents for the query.
	 *
	 * @since 1.1.0
	 * @return array|int|Document[] Array of document objects, the total found documents for the query, or the total found documents for the query as int when `$count` is true.
	 */
	public function query( $args = array(), $count = false ) {
		if ( $count ) {
			return Document::count( $args );
		}

		return Document::results( $args );
	}

	/**
	 * Get document types.
	 *
	 * @since 1.0.2
	 * @return array
	 */
	public function get_types() {
		$document_types = array(
			'invoice'  => __( 'Invoice', 'wp-ever-accounting' ),
			'receipt'  => __( 'Receipt', 'wp-ever-accounting' ),
			'contract' => __( 'Contract', 'wp-ever-accounting' ),
		);

		return apply_filters( 'eac_document_types', $document_types );
	}
}
