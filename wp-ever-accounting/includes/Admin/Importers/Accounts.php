<?php
/**
 * Handle accounts import.
 *
 * @since 1.0.2
 *
 * @package EverAccounting\Admin\Importers
 */

namespace EverAccounting\Admin\Importers;

/**
 * Accounts class.
 *
 * @since 1.0.0
 */
class Accounts extends Importer {
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

		$data = array_diff_key( $data, array_flip( $protected ) );
		return EAC()->accounts->insert( $data );
	}
}
