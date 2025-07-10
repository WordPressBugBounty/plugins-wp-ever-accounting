<?php
/**
 * Handle items import.
 *
 * @since 1.0.2
 *
 * @package EverAccounting\Admin\Importers
 */

namespace EverAccounting\Admin\Importers;

/**
 * Items class.
 *
 * @since 1.0.0
 */
class Items extends Importer {
	/**
	 * Abstract method to import item.
	 *
	 * @param array $data Item data.
	 *
	 * @since 1.0.2
	 * @return mixed Inserted item ID.
	 */
	public function import_item( $data ) {
		$protected = array(
			'id',
			'date_updated',
		);

		$data  = array_diff_key( $data, array_flip( $protected ) );
		$dates = array(
			'date_created',
			'date_updated',
		);

		foreach ( $dates as $date ) {
			if ( isset( $data[ $date ] ) && ! empty( $data[ $date ] ) ) {
				$data[ $date ] = get_gmt_from_date( $data[ $date ] );
			}
		}

		// if the item have category.
		if ( ! empty( $data['category'] ) ) {
			$category = EAC()->categories->get(
				array(
					'name' => $data['category'],
					'type' => 'item',
				)
			);

			if ( ! $category ) {
				$category = EAC()->categories->insert(
					array(
						'name' => sanitize_text_field( $data['category'] ),
						'type' => 'item',
					)
				);
			}

			if ( $category ) {
				$data['category_id'] = $category->id;
			}
		}

		return EAC()->items->insert( $data );
	}
}
