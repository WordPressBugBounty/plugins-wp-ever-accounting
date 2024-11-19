<?php

namespace EverAccounting\Admin\Importers;

use EverAccounting\Utilities\FileUtil;

defined( 'ABSPATH' ) || exit;

/**
 * Class Importer.
 *
 * @since 1.0.0
 *
 * @package EverAccounting\Admin\Tools\Importers
 */
abstract class Importer {
	/**
	 * Capability needed to perform the current import.
	 *
	 * @since  1.0.0
	 * @var    string
	 */
	protected $capability = 'manage_options';

	/**
	 * The file being imported.
	 *
	 * @since  1.0.0
	 * @var string
	 */
	protected $file = '';

	/**
	 * The parsed CSV data being imported.
	 *
	 * @since  1.0.0
	 * @var   array
	 */
	protected $rows = array();

	/**
	 * Current position in the import.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	protected $position = 0;

	/**
	 * Start time of current import.
	 * (default value: 0)
	 *
	 * @since 1.0.0
	 * @var int
	 */
	protected $start_time = 0;

	/**
	 * Constructor.
	 *
	 * @param string $file File path.
	 * @param int    $position Position.
	 *
	 * @since 1.0.0
	 */
	public function __construct( $file = '', $position = 0 ) {
		$this->file     = $file;
		$this->position = $position;
		if ( ! empty( $this->file ) && file_exists( $this->file ) ) {
			$this->rows = $this->parse_csv( $this->file );
		}
	}

	/**
	 * Can user import?
	 *
	 * @since  1.0.2
	 * @return bool Whether the user can import or not
	 */
	public function can_import() {
		return (bool) current_user_can( apply_filters( 'eac_import_capability', $this->capability ) );
	}

	/**
	 * Process the import.
	 *
	 * @since 1.0.2
	 * @return int
	 */
	public function import() {
		if ( ! $this->can_import() ) {
			return 0;
		}

		$this->start_time = time();
		$rows             = $this->rows;

		if ( ! empty( $this->position ) && $this->position < count( $rows ) ) {
			$rows = array_slice( $rows, $this->position, $this->position );
		}

		$imported = 0;
		foreach ( $rows as $row ) {
			++$this->position;
			if ( ! is_wp_error( $this->import_item( $row ) ) ) {
				++$imported;
			}
			if ( $this->time_exceeded() || $this->memory_exceeded() ) {
				break;
			}
		}

		return $imported;
	}

	/**
	 * Get the progress.
	 *
	 * @since 1.0.0
	 * @return int
	 */
	public function get_percent_complete() {
		$count = count( $this->rows );
		if ( ! $count || ! $this->position ) {
			return 0;
		}

		$percent = absint( ( $this->position / $count ) * 100 );

		if ( $percent >= 100 ) {
			FileUtil::delete( $this->file );
		}

		return $percent;
	}

	/**
	 * Get position.
	 *
	 * @since 1.0.2
	 * @return int
	 */
	public function get_position() {
		return $this->position;
	}

	/**
	 * Abstract method to import item.
	 *
	 * @param array $data item data.
	 *
	 * @since 1.0.2
	 * @return mixed
	 */
	abstract protected function import_item( $data );

	/**
	 * Parse the CSV file.
	 *
	 * @param string $file File path.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	protected function parse_csv( $file ) {
		if ( ! file_exists( $file ) ) {
			return array();
		}

		$rows = array_map( 'str_getcsv', FileUtil::file( $file ) );
		array_walk(
			$rows,
			function ( &$a ) use ( $rows ) {
				// Make sure the two arrays have the same lengths.
				$min     = min( count( $rows[0] ), count( $a ) );
				$headers = array_slice( $rows[0], 0, $min );
				if ( 'efbbbf' === substr( bin2hex( $headers[0] ), 0, 6 ) ) {
					$headers[0] = substr( $headers[0], 3 );
				}
				$values = array_slice( $a, 0, $min );
				$a      = array_combine( $headers, $values );
			}
		);
		array_shift( $rows );

		// Parse row.
		foreach ( $rows as &$row ) {
			$this->parse_row( $row );
		}

		return $rows;
	}

	/**
	 * Parse row.
	 *
	 * @param array $row Row data.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	protected function parse_row( &$row ) {
		$row = array_map( 'wp_unslash', $row );
		$row = array_map( 'trim', $row );
		foreach ( $row as &$value ) {
			if ( function_exists( 'mb_convert_encoding' ) ) {
				$encoding = mb_detect_encoding( $value, mb_detect_order(), true );
				if ( $encoding ) {
					$value = mb_convert_encoding( $value, 'UTF-8', $encoding );
				} else {
					$value = mb_convert_encoding( $value, 'UTF-8', 'UTF-8' );
				}
			} else {
				$value = wp_check_invalid_utf8( $value, true );
			}

			if ( in_array( mb_substr( $value, 0, 2 ), array( "'=", "'+", "'-", "'@" ), true ) ) {
				$value = mb_substr( $value, 1 );
			}
		}

		return $row;
	}

	/**
	 * Time exceeded.
	 *
	 * Ensures the batch never exceeds a sensible time limit.
	 * A timeout limit of 30s is common on shared hosting.
	 *
	 * @return bool
	 */
	protected function time_exceeded() {
		$finish = $this->start_time + 20; // 20 seconds
		$return = false;
		if ( time() >= $finish ) {
			$return = true;
		}

		return $return;
	}

	/**
	 * Memory exceeded
	 *
	 * Ensures the batch process never exceeds 90%
	 * of the maximum WordPress memory.
	 *
	 * @return bool
	 */
	protected function memory_exceeded() {
		$memory_limit   = $this->get_memory_limit() * 0.9; // 90% of max memory
		$current_memory = memory_get_usage( true );
		$return         = false;
		if ( $current_memory >= $memory_limit ) {
			$return = true;
		}

		return $return;
	}

	/**
	 * Get memory limit
	 *
	 * @return int
	 */
	protected function get_memory_limit() {
		if ( function_exists( 'ini_get' ) ) {
			$memory_limit = ini_get( 'memory_limit' );
		} else {
			// Sensible default.
			$memory_limit = '128M';
		}

		if ( ! $memory_limit || - 1 === intval( $memory_limit ) ) {
			// Unlimited, set to 32GB.
			$memory_limit = '32000M';
		}

		return intval( $memory_limit ) * 1024 * 1024;
	}
}
