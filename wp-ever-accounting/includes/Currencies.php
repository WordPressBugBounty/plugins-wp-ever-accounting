<?php

namespace EverAccounting;

defined( 'ABSPATH' ) || exit;

/**
 * Class Currencies.
 *
 * @since 1.0.0
 * @package EverAccounting\Controllers
 */
class Currencies {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'eac_currencies', array( __CLASS__, 'add_exchange_rates' ) );
	}

	/**
	 * Add conversion rates.
	 *
	 * @param array $currencies Currencies.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public static function add_exchange_rates( $currencies ) {
		$exchange_rates = get_option( 'eac_exchange_rates', array() );
		if ( is_array( $exchange_rates ) && ! empty( $exchange_rates ) ) {
			foreach ( $exchange_rates as $code => $exchange_rate ) {
				$currencies[ $code ]['rate'] = $exchange_rate;
			}
		}

		return $currencies;
	}
}
