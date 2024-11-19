<?php

namespace EverAccounting\Controllers;

defined( 'ABSPATH' ) || exit;


/**
 * Class Currencies
 *
 * @since 1.0.0
 * @package EverAccounting\Controllers
 */
class Currencies {

	/**
	 * Get symbol of currency
	 *
	 * @param string $currency Currency to get symbol.
	 *
	 * @return string Currency symbol.
	 * @since 2.0.0
	 */
	public function get_symbol( $currency = null ) {
		return $this->get_config( $currency )['symbol'];
	}

	/**
	 * Get currency name.
	 *
	 * @param string $currency Currency to get name.
	 *
	 * @return string Currency name.
	 * @since 2.0.0
	 */
	public function get_name( $currency = null ) {
		return $this->get_config( $currency )['formatted_name'];
	}

	/**
	 * Get currency precision.
	 *
	 * @param string $currency Currency to get precision.
	 *
	 * @return int Currency precision.
	 * @since 2.0.0
	 */
	public function get_precision( $currency = null ) {
		return $this->get_config( $currency )['precision'];
	}

	/**
	 * Get currency position.
	 *
	 * @param string $currency Currency to get position.
	 *
	 * @return string Currency position.
	 * @since 2.0.0
	 */
	public function get_position( $currency = null ) {
		return $this->get_config( $currency )['position'];
	}

	/**
	 * Get currency thousand separator.
	 *
	 * @param string $currency Currency to get thousand separator.
	 *
	 * @return string Currency thousand separator.
	 * @since 2.0.0
	 */
	public function get_thousand( $currency = null ) {
		return $this->get_config( $currency )['thousand'];
	}

	/**
	 * Get currency decimal separator.
	 *
	 * @param string $currency Currency to get decimal separator.
	 *
	 * @return string Currency decimal separator.
	 * @since 2.0.0
	 */
	public function get_decimal( $currency = null ) {
		return $this->get_config( $currency )['decimal'];
	}

	/**
	 * Get exchange rate.
	 *
	 * @param string $currency Currency to get rate.
	 *
	 * @return float Currency rate.
	 * @since 2.0.0
	 */
	public function get_rate( $currency = null ) {
		return $this->get_config( $currency )['rate'];
	}

	/**
	 * Get config.
	 *
	 * @param string $currency Currency to get config for.
	 *
	 * @return array
	 * @since 2.0.0
	 */
	public function get_config( $currency = null ) {
		$currencies = eac_get_currencies();
		return array_key_exists( $currency, $currencies ) ? $currencies[ $currency ] : $currencies[ eac_base_currency() ];
	}
}
