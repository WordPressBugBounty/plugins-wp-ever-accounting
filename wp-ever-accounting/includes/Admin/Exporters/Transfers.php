<?php
/**
 * Handle transfers export.
 *
 * @since 1.0.2
 *
 * @package EverAccounting\Admin\Exporters
 */

namespace EverAccounting\Admin\Exporters;

use EverAccounting\Models\Transfer;

defined( 'ABSPATH' ) || exit();


/**
 * Class Transfers.
 *
 * @since   1.0.2
 *
 * @package EverAccounting\Admin\Exporters
 */
class Transfers extends Exporter {

	/**
	 * Our export type. Used for export-type specific filters/actions.
	 *
	 * @since 1.0.2
	 * @var string
	 */
	public $export_type = 'transfers';

	/**
	 * Return an array of columns to export.
	 *
	 * @since  1.0.2
	 * @return array
	 */
	public function get_columns() {
		$hidden = array( 'id', 'user_id', 'parent_id', 'created_via', 'expense_id', 'payment_id' );

		return array_merge( array( 'from_account_id', 'to_account_id' ), array_diff( ( new Transfer() )->get_columns(), $hidden ) );
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

		$args = apply_filters( 'eac_export_transfers_args', $args );

		$items       = EAC()->transfers->query( $args );
		$this->total = EAC()->transfers->query( $args, true );
		$rows        = array();

		foreach ( $items as $item ) {
			$row = array();
			foreach ( $this->get_columns() as $column ) {
				switch ( $column ) {
					case 'from_account_id':
						$value = $item->expense ? $item->expense->account_id : null;
						break;
					case 'to_account_id':
						$value = $item->payment ? $item->payment->account_id : null;
						break;
					default:
						$value = isset( $item->{$column} ) ? $item->{$column} : null;
				}

				$row[ $column ] = $value;
			}
			if ( ! empty( $row ) ) {
				$dates = array(
					'transfer_date',
					'date_created',
					'date_updated',
				);

				foreach ( $dates as $date ) {
					if ( isset( $row[ $date ] ) && ! empty( $row[ $date ] ) ) {
						$row[ $date ] = eac_format_datetime( $row[ $date ] );
					}
				}

				$rows[] = $row;
			}
		}

		return $rows;
	}
}
