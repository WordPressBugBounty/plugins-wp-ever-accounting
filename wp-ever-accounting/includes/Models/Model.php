<?php
/**
 * Abstract Model class.
 *
 * @package EverAccounting\Models
 */

namespace EverAccounting\Models;

defined( 'ABSPATH' ) || exit;

/**
 * Abstract class Model.
 *
 * @since 1.2.0
 * @package EverAccounting
 * @subpackage Models
 */
abstract class Model extends \EverAccounting\ByteKit\Models\Model {
	/**
	 * Get hook prefix. Default is the object type.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_hook_prefix() {
		return 'eac_' . $this->get_object_type();
	}
}
