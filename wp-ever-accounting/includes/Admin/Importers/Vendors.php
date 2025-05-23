<?php
/**
 * Handle vendors import.
 *
 * @since 1.0.2
 *
 * @package EverAccounting\Admin\Importers
 */

namespace EverAccounting\Admin\Importers;

/**
 * Customers class.
 *
 * @since 1.0.0
 */
class Vendors extends Importer {
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
			'type',
			'date_updated',
		);

		$data = array_diff_key( $data, array_flip( $protected ) );
		return EAC()->vendors->insert( $data );
	}
}
