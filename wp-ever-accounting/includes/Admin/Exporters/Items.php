<?php
/**
 * Handle items export.
 *
 * @since 1.0.2
 *
 * @package EverAccounting\Admin\Exporters
 */

namespace EverAccounting\Admin\Exporters;

use EverAccounting\Models\Item;

defined( 'ABSPATH' ) || exit();


/**
 * Class Items.
 *
 * @since   1.0.2
 *
 * @package EverAccounting\Admin\Exporters
 */
class Items extends Exporter {

	/**
	 * Our export type. Used for export-type specific filters/actions.
	 *
	 * @since 1.0.2
	 * @var string
	 */
	public $export_type = 'items';

	/**
	 * Return an array of columns to export.
	 *
	 * @since  1.0.2
	 * @return array
	 */
	public function get_columns() {
		$hidden = array( 'id', 'user_id', 'parent_id', 'created_via' );

		return array_diff( ( new Item() )->get_columns(), $hidden );
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

		$args = apply_filters( 'eac_export_items_args', $args );

		$items = EAC()->items->query( $args );
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
