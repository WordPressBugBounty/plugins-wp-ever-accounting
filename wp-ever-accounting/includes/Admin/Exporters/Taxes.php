<?php
/**
 * Handle taxes export.
 *
 * @since 1.0.2
 *
 * @package EverAccounting\Admin\Exporters
 */

namespace EverAccounting\Admin\Exporters;

use EverAccounting\Models\Tax;

defined( 'ABSPATH' ) || exit();


/**
 * Class Taxes.
 *
 * @since   1.0.2
 *
 * @package EverAccounting\Admin\Exporters
 */
class Taxes extends Exporter {

	/**
	 * Our export type. Used for export-type specific filters/actions.
	 *
	 * @since 1.0.2
	 * @var string
	 */
	public $export_type = 'taxes';

	/**
	 * Return an array of columns to export.
	 *
	 * @since  1.0.2
	 * @return array
	 */
	public function get_columns() {
		$hidden    = array( 'id', 'description', 'type', 'taxonomy', 'parent_id', 'date_created', 'date_updated' );
		$columns   = array_diff( ( new Tax() )->get_columns(), $hidden );
		$columns[] = 'rate';
		$columns[] = 'compound';
		$columns[] = 'date_created';
		$columns[] = 'date_updated';

		return $columns;
	}

	/**
	 * Get export data.
	 *
	 * @since 1.0.2
	 * @return array
	 */
	public function get_rows() {
		$args = array(
			'orderby' => 'id',
			'order'   => 'ASC',
			'page'    => $this->page,
			'limit'   => $this->limit,
		);

		$args = apply_filters( 'eac_export_taxes_args', $args );

		$items = EAC()->taxes->query( $args );
		$rows  = array();

		foreach ( $items as $item ) {
			$row = array();
			foreach ( $this->get_columns() as $column ) {
				switch ( $column ) {
					default:
						$value = isset( $item->{$column} ) ? $item->{$column} : null;
				}

				$row[ $column ] = $value;
			}
			if ( ! empty( $row ) ) {
				$rows[] = $row;
			}
		}

		return $rows;
	}
}
