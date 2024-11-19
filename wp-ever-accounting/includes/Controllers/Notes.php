<?php

namespace EverAccounting\Controllers;

use EverAccounting\Models\Note;

defined( 'ABSPATH' ) || exit;

/**
 * Notes controller.
 *
 * @since 1.0.0
 * @author  Sultan Nasir Uddin <manikdrmc@gmail.com>
 * @package EverAccounting
 * @subpackage Controllers
 */
class Notes {

	/**
	 * Get a note from the database.
	 *
	 * @param mixed $note Note ID or object.
	 *
	 * @since 1.1.6
	 * @return Note|null Note object if found, otherwise null.
	 */
	public function get( $note ) {
		return Note::find( $note );
	}

	/**
	 * Insert a new note into the database.
	 *
	 * @param array $data Note data.
	 * @param bool  $wp_error Optional. Whether to return a WP_Error on failure. Default false.
	 *
	 * @since 1.1.0
	 * @return Note|false|\WP_Error Note object on success, false or WP_Error on failure.
	 */
	public function insert( $data, $wp_error = true ) {
		return Note::insert( $data, $wp_error );
	}

	/**
	 * Delete a note from the database.
	 *
	 * @param int $id Note ID.
	 *
	 * @since 1.1.0
	 * @return bool True on success, false on failure.
	 */
	public function delete( $id ) {
		$note = $this->get( $id );
		if ( ! $note ) {
			return false;
		}

		return $note->delete();
	}

	/**
	 * Get query results for notes.
	 *
	 * @param array $args Query arguments.
	 * @param bool  $count Optional. Whether to return only the total found notes for the query.
	 *
	 * @since 1.1.0
	 * @return array|int|Note[] Array of note objects, the total found notes for the query, or the total found notes for the query as int when `$count` is true.
	 */
	public function query( $args = array(), $count = false ) {
		if ( $count ) {
			return Note::count( $args );
		}

		return Note::results( $args );
	}
}
