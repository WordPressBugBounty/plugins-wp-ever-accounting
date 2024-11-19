<?php

namespace EverAccounting\Admin\Exporters;

use EverAccounting\Utilities\FileUtil;

/**
 * Handle exporters.
 *
 * @since   1.0.2
 *
 * @package EverAccounting\Admin\Exporters
 */
abstract class Exporter {
	/**
	 * Our export type. Used for export-type specific filters/actions.
	 *
	 * @since 1.0.2
	 * @var string
	 */
	public $export_type = 'default';

	/**
	 * Capability needed to perform the current export.
	 *
	 * @since  1.0.2
	 * @var    string
	 */
	public $capability = 'manage_options';

	/**
	 * Filename to export to.
	 *
	 * @since 1.0.2
	 * @var string
	 */
	protected $filename = '';

	/**
	 * The delimiter parameter sets the field delimiter (one character only).
	 *
	 * @since 1.0.2
	 * @var string
	 */
	protected $delimiter = ',';

	/**
	 * The character used to wrap text in the CSV.
	 *
	 * @since 1.0.2
	 * @var array
	 */
	protected $enclosure = '"';

	/**
	 * The character used to escape the enclosure character.
	 *
	 * @since 1.0.2
	 * @var array
	 */
	protected $escape = "\0"; // This should be double-quoted.

	/**
	 * Number exported.
	 *
	 * @since 1.0.2
	 * @var integer
	 */
	protected $position = 0;

	/**
	 * Page being exported
	 *
	 * @var integer
	 */
	protected $page = 1;

	/**
	 * Batch limit.
	 *
	 * @since 1.0.2
	 * @var integer
	 */
	protected $limit = 100;

	/**
	 * Total rows to export.
	 *
	 * @since 1.0.2
	 * @var integer
	 */
	protected $total = 0;

	/**
	 * Return an array of supported column names and ids.
	 *
	 * @since 1.0.2
	 * @return array
	 */
	abstract protected function get_columns();

	/**
	 * Prepare data that will be exported.
	 *
	 * @since 1.0.2
	 * @return array
	 */
	abstract protected function get_rows();

	/**
	 * Can we export?
	 *
	 * @since  1.0.2
	 * @return bool Whether we can export or not
	 */
	public function can_export() {
		return (bool) current_user_can( apply_filters( 'eac_export_capability', $this->capability ) );
	}


	/**
	 * Set filename to export to.
	 *
	 * @param string $filename Filename to export to.
	 */
	public function set_filename( $filename ) {
		$this->filename = sanitize_file_name( str_replace( '.csv', '', $filename ) . '.csv' );
	}

	/**
	 * Generate and return a filename.
	 *
	 * @return string
	 */
	public function get_filename() {
		$date = wp_date( 'Ymdhis' );

		return sanitize_file_name( "{$this->export_type}-$date.csv" );
	}

	/**
	 * Generate the CSV file.
	 *
	 * @param int $step Step number.
	 *
	 * @since 1.0.2
	 */
	public function process_step( $step ) {
		$this->page    = absint( $step );
		$wp_filesystem = FileUtil::get_fs();

		if ( 1 === $this->page ) {
			$wp_filesystem->delete( $this->get_file_path() );
		}

		$rows = $this->prepare_rows( $this->get_rows() );

		$file = $this->get_file();

		if ( 100 === $this->get_percent_complete() ) {
			$file = chr( 239 ) . chr( 187 ) . chr( 191 ) . $this->get_column_headers() . $file;
		}

		$file .= $rows;
		$wp_filesystem->put_contents( $this->get_file_path(), $file, FS_CHMOD_FILE );
	}

	/**
	 * Serve the file and remove once sent to the client.
	 *
	 * @since 1.0.2
	 */
	public function export() {
		$this->send_headers();
		$this->send_content( $this->get_file() );
		$wp_filesystem = FileUtil::get_fs();
		$wp_filesystem->delete( $this->get_file_path() );
		die();
	}

	/**
	 * Get total % complete.
	 *
	 * @since 1.0.2
	 * @return int
	 */
	public function get_percent_complete() {
		return $this->total ? floor( ( $this->get_total_exported() / $this->total ) * 100 ) : 100;
	}

	/**
	 * Get count of records exported.
	 *
	 * @since 1.0.2
	 * @return int
	 */
	public function get_total_exported() {
		return ( ( $this->page - 1 ) * $this->limit ) + $this->position;
	}

	/**
	 * Get file path to export to.
	 *
	 * @since 1.0.2
	 * @return string
	 */
	protected function get_file_path() {
		$upload_dir = wp_upload_dir();

		return trailingslashit( $upload_dir['basedir'] ) . $this->get_filename();
	}

	/**
	 * Get the file contents.
	 *
	 * @since 1.0.2
	 * @return string
	 */
	protected function get_file() {
		$file          = '';
		$wp_filesystem = FileUtil::get_fs();
		// check if file exists.
		if ( $wp_filesystem->exists( $this->get_file_path() ) ) {
			$file = $wp_filesystem->get_contents( $this->get_file_path() );
		} else {
			// create file if not exists.
			$wp_filesystem->put_contents( $this->get_file_path(), $file, FS_CHMOD_FILE );
		}

		return $file;
	}

	/**
	 * Export rows in CSV format.
	 *
	 * @param array $rows Rows to export.
	 *
	 * @since 1.0.2
	 * @return string
	 */
	protected function prepare_rows( $rows ) {
		$buffer = fopen( 'php://output', 'w' );
		ob_start();
		foreach ( $rows as $row ) {
			$this->prepare_row( $row, $buffer );
		}
		return ob_get_clean();
	}

	/**
	 * Export rows to an array ready for the CSV.
	 *
	 * @param array    $row Data to export.
	 * @param resource $buffer Output buffer.
	 *
	 * @since 1.0.2
	 */
	protected function prepare_row( $row, $buffer ) {
		$prepared = array();
		foreach ( $this->get_columns() as $column ) {
			if ( isset( $row[ $column ] ) ) {
				$prepared[] = $this->format_data( $row[ $column ] );
			} else {
				$prepared[] = '';
			}
		}

		fputcsv( $buffer, $prepared, $this->delimiter, $this->enclosure, $this->escape );

		++$this->position;
	}

	/**
	 * Format and escape data ready for the CSV file.
	 *
	 * @param mixed $data Data to format.
	 *
	 * @since 1.0.2
	 *
	 * @return string
	 */
	protected function format_data( $data ) {
		if ( ! is_scalar( $data ) ) {
			if ( is_a( $data, '\EverAccounting\DateTime' ) ) {
				$data = $data->date( 'Y-m-d G:i:s' );
			} else {
				$data = ''; // Not supported.
			}
		} elseif ( is_bool( $data ) ) {
			$data = $data ? 1 : 0;
		}

		$use_mb = function_exists( 'mb_convert_encoding' );

		if ( $use_mb ) {
			$encoding = mb_detect_encoding( $data, 'UTF-8, ISO-8859-1', true );
			$data     = 'UTF-8' === $encoding ? $data : utf8_encode( $data );
		}

		return $this->escape_data( $data );
	}

	/**
	 * Escape a string to be used in a CSV context
	 *
	 * @param string $data CSV field to escape.
	 *
	 * @since 1.0.2
	 *
	 * @return string
	 */
	protected function escape_data( $data ) {
		$active_content_triggers = array( '=', '+', '-', '@' );

		if ( in_array( mb_substr( $data, 0, 1 ), $active_content_triggers, true ) ) {
			$data = "'" . $data;
		}

		return $data;
	}

	/**
	 * Get column headers in CSV format.
	 *
	 * @since 1.0.2
	 * @return string
	 */
	protected function get_column_headers() {
		$columns    = $this->get_columns();
		$export_row = array();
		$buffer     = fopen( 'php://output', 'w' );
		ob_start();

		foreach ( $columns as $column_name ) {
			$export_row[] = $this->format_data( $column_name );
		}

		fputcsv( $buffer, $export_row, $this->delimiter, $this->enclosure, $this->escape );

		return ob_get_clean();
	}

	/**
	 * Set the export headers.
	 *
	 * @since 1.0.2
	 * @return void
	 */
	protected function send_headers() {
		ignore_user_abort( true );
		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . $this->get_filename() );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );
	}

	/**
	 * Set the export content.
	 *
	 * @param string $content All CSV content.
	 *
	 * @since 1.0.2
	 */
	protected function send_content( $content ) {
		echo wp_kses_post( $content );
	}
}
