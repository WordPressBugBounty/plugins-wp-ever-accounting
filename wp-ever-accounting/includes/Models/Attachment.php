<?php

namespace EverAccounting\Models;

defined( 'ABSPATH' ) || exit;

/**
 * Class Attachment
 *
 * @since 1.0.0
 * @package EverAccounting\Models
 *
 * @property int     $id Attachment ID.
 * @property string  $title Attachment title.
 * @property string  $url Attachment URL.
 * @property string  $path Attachment file path.
 * @property string $filesize Attachment file size.
 */
class Attachment extends Model {
	/**
	 * The table associated with the model.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $table = 'posts';

	/**
	 * The primary key for the model.
	 *
	 * This string specifies the primary key column for the model's table.
	 * By default, it is set to 'id', but it can be customized to match the primary key used in the table.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $primary_key = 'ID';

	/**
	 * Table columns.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $columns = array(
		'ID',
		'post_title',
		'post_type',
	);


	/**
	 * The attributes that have aliases.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $aliases = array(
		'title' => 'post_title',
		'id'    => 'ID',
	);

	/**
	 * Default query variables passed to Query.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $query_vars = array(
		'post_type' => 'attachment',
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
	 * Get url.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	protected function get_url_attribute() {
		return wp_get_attachment_url( $this->ID );
	}

	/**
	 * Get file path.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	protected function get_path_attribute() {
		return get_attached_file( $this->ID );
	}

	/**
	 * Get file size.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	protected function get_filesize_attribute() {
		$meta = wp_get_attachment_metadata( $this->ID );

		return isset( $meta['filesize'] ) ? $meta['filesize'] : filesize( get_attached_file( $this->ID ) );
	}
}
