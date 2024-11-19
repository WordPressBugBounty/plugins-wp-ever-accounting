<?php
/**
 * Core functions
 *
 * @version  1.1.0
 * @category Functions
 * @package  EverAccounting\Functions
 */

use EverAccounting\Utilities\I18nUtil;

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/Functions/formatters.php';
require_once __DIR__ . '/Functions/misc.php';
require_once __DIR__ . '/Functions/templates.php';

/**
 * Get the base currency.
 *
 * @since 1.0.0
 * @return string
 */
function eac_base_currency() {
	$currency = get_option( 'eac_base_currency', 'USD' );

	return apply_filters( 'eac_base_currency', strtoupper( $currency ) );
}

/**
 * Get currencies.
 *
 * @since 1.0.0
 * @return array Array of currencies.
 */
function eac_get_currencies() {
	$currencies = apply_filters(
		'eac_currencies',
		array_map(
			function ( $currency ) {
				$currency['formatted_name'] = esc_html( sprintf( '%s (%s) - (%s)', $currency['name'], $currency['symbol'], $currency['code'] ) );

				return $currency;
			},
			I18nUtil::get_currencies()
		)
	);

	// Fix rate for base currency.
	$base                             = eac_base_currency();
	$currencies[ $base ]['rate']      = 1;
	$currencies[ $base ]['position']  = get_option( 'eac_currency_position', 'before' );
	$currencies[ $base ]['thousand']  = stripslashes( get_option( 'eac_thousand_separator', ',' ) );
	$currencies[ $base ]['decimal']   = stripslashes( get_option( 'eac_decimal_separator', '.' ) );
	$currencies[ $base ]['precision'] = absint( get_option( 'eac_currency_precision', 2 ) );

	// now sort the currencies by formatted name but put the base currency at the top.
	uasort(
		$currencies,
		function ( $a, $b ) {
			if ( eac_base_currency() === $a['code'] ) {
				return - 1;
			}
			if ( eac_base_currency() === $b['code'] ) {
				return 1;
			}

			return strcasecmp( $a['formatted_name'], $b['formatted_name'] );
		}
	);

	return $currencies;
}

/**
 * Get currency config.
 *
 * @param string $currency The currency to get config for.
 *
 * @since 1.0.0
 * @return array
 */
function eac_get_currency_config( $currency = null ) {
	$currencies = eac_get_currencies();

	return array_key_exists( $currency, $currencies ) ? $currencies[ $currency ] : $currencies[ eac_base_currency() ];
}

/**
 * Format amount with currency code & number format
 *
 * @param string $amount Amount.
 * @param string $currency Currency code.
 *
 * @since 1.0.2
 * @return string
 */
function eac_format_amount( $amount, $currency = null ) {
	$currencies = eac_get_currencies();
	if ( ! is_numeric( $amount ) ) {
		$amount = eac_sanitize_amount( $amount, $currency );
	}
	$data     = array_key_exists( $currency, $currencies ) ? $currencies[ $currency ] : $currencies[ eac_base_currency() ];
	$negative = $amount < 0;
	$prefix   = 'before' === $data['position'] ? $data['symbol'] : '';
	$suffix   = 'after' === $data['position'] ? $data['symbol'] : '';

	$amount = $negative ? - $amount : $amount;
	$amount = number_format( $amount, $data['precision'], $data['decimal'], $data['thousand'] );

	return $negative ? sprintf( '-%s%s%s', $prefix, $amount, $suffix ) : sprintf( '%s%s%s', $prefix, $amount, $suffix );
}

/**
 * Sanitize price before inserting into database.
 *
 * @param string $amount Amount.
 * @param string $currency Currency.
 *
 * @since 1.0.2
 * @return float|int
 */
function eac_sanitize_amount( $amount, $currency = null ) {
	if ( ! is_numeric( $amount ) ) {
		$amount     = sanitize_text_field( $amount );
		$currencies = eac_get_currencies();
		$data       = array_key_exists( $currency, $currencies ) ? $currencies[ $currency ] : $currencies[ eac_base_currency() ];

		// Remove currency symbol.
		$amount = str_replace( $data['symbol'], '', $amount );

		// Remove any non-numeric characters except a thousand and decimal separators.
		$amount = preg_replace( '/[^0-9\\' . $data['thousand'] . '\\' . $data['decimal'] . '\-\+]/', '', $amount );
		// Replace a thousand and decimal separators with empty string and dot respectively.
		$amount = str_replace( array( $data['thousand'], $data['decimal'] ), array( '', '.' ), $amount );

		// Convert to int if amount is a whole number, otherwise convert to float.
		if ( preg_match( '/^([\-\+])?\d+$/', $amount ) ) {
			$amount = (int) $amount;
		} elseif ( preg_match( '/^([\-\+])?\d+\.\d+$/', $amount ) ) {
			$amount = (float) $amount;
		} else {
			$amount = 0;
		}
	}

	return $amount;
}

/**
 * Convert price from one currency to another.
 *
 * @param string $amount Amount.
 * @param float  $from Rate of the currency to convert from.
 * @param float  $to Rate of the currency to convert to.
 *
 * @since 1.0.2
 * @return float Converted amount.
 */
function eac_convert_currency( $amount, $from = 1, $to = 1 ) {
	$currencies = eac_get_currencies();
	if ( ! is_numeric( $amount ) ) {
		$amount = eac_sanitize_amount( $amount );
	}

	// If from or to any of these is not exchange rate instead a valid currency code, then get the exchange rate.
	if ( ! is_numeric( $from ) && strlen( $from ) === 3 && array_key_exists( $from, $currencies ) ) {
		$from = EAC()->currencies->get_rate( $from );
	}
	if ( ! is_numeric( $to ) && strlen( $to ) === 3 && array_key_exists( $to, $currencies ) ) {
		$to = EAC()->currencies->get_rate( $to );
	}

	if ( ! is_numeric( $from ) || $from <= 0 || ! is_numeric( $to ) || $to <= 0 ) {
		return 0;
	}

	// No need to convert same currency.
	if ( $from === $to ) {
		return $amount;
	}

	// from amount is in base currency.
	if ( 1 !== $from ) {
		$amount = $amount / $from;
	}

	// to amount is in base currency.
	if ( 1 !== $to ) {
		$amount = $amount * $to;
	}

	return $amount;
}

/**
 * Get only numbers from the string.
 *
 * @param string   $number Number to get only numbers from.
 *
 * @param bool|int $decimals Allow decimal. If true, then allow decimal. If false, then only allow integers. If a number, then allow that many decimal places.
 *
 * @since 1.0.2
 * @return int|float|null
 */
function eac_sanitize_number( $number, $decimals = 2 ) {
	// Convert multiple dots to just one.
	$number = preg_replace( '/\.(?![^.]+$)|[^0-9.-]/', '', eac_clean( $number ) );

	if ( $decimals ) {
		$number = (float) preg_replace( '/[^0-9.-]/', '', $number );
		// if allow decimal is a number, then use that as the number of decimals.
		if ( is_numeric( $decimals ) ) {
			$number = number_format( floatval( $number ), $decimals, '.', '' );
		}

		return $number;
	}

	return (int) preg_replace( '/[^0-9]/', '', $number );
}

/**
 * Round a number using the built-in `round` function, but unless the value to round is numeric
 * (a number or a string that can be parsed as a number), apply 'floatval' first to it
 * (so it will convert it to 0 in most cases).
 *
 * @param mixed $val The value to round.
 * @param int   $decimals The optional number of decimal digits to round to.
 * @param int   $mode A constant to specify the mode in which rounding occurs.
 *
 * @return float The value rounded to the given decimals as a float, or the supplied default value.
 */
function eac_round_number( $val, $decimals = 6, $mode = PHP_ROUND_HALF_UP ) {
	$val = eac_sanitize_number( $val, $decimals );

	return round( $val, $decimals, $mode );
}

/**
 * Get payment methods.
 *
 * @since 1.0.2
 * @return array
 */
function eac_get_payment_methods() {
	return apply_filters(
		'eac_payment_methods',
		array(
			'cash'   => esc_html__( 'Cash', 'wp-ever-accounting' ),
			'check'  => esc_html__( 'Cheque', 'wp-ever-accounting' ),
			'credit' => esc_html__( 'Credit Card', 'wp-ever-accounting' ),
			'debit'  => esc_html__( 'Debit Card', 'wp-ever-accounting' ),
			'bank'   => esc_html__( 'Bank Transfer', 'wp-ever-accounting' ),
			'paypal' => esc_html__( 'PayPal', 'wp-ever-accounting' ),
			'other'  => esc_html__( 'Other', 'wp-ever-accounting' ),
		)
	);
}
