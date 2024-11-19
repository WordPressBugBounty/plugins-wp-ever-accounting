<?php

namespace EverAccounting\Controllers;

defined( 'ABSPATH' ) || exit;

/**
 * Business controller.
 *
 * @since 1.0.0
 * @author  Sultan Nasir Uddin <manikdrmc@gmail.com>
 * @package EverAccounting
 * @subpackage Controllers
 */
class Business {

	/**
	 * Get the name of the company.
	 *
	 * @since 1.0.0
	 * @return string Company name.
	 */
	public function get_name() {
		return get_option( 'eac_company_name', '' );
	}

	/**
	 * Get the email of the company.
	 *
	 * @since 1.0.0
	 * @return string Company email.
	 */
	public function get_email() {
		return get_option( 'eac_company_email', '' );
	}

	/**
	 * Get the phone number of the company.
	 *
	 * @since 1.0.0
	 * @return string Company phone number.
	 */
	public function get_phone() {
		return get_option( 'eac_company_phone', '' );
	}

	/**
	 * Get the address of the company.
	 *
	 * @since 1.0.0
	 * @return string Company address.
	 */
	public function get_address() {
		return get_option( 'eac_company_address', '' );
	}

	/**
	 * Get the city of the company.
	 *
	 * @since 1.0.0
	 * @return string Company city.
	 */
	public function get_city() {
		return get_option( 'eac_company_city', '' );
	}

	/**
	 * Get the state of the company.
	 *
	 * @since 1.0.0
	 * @return string Company state.
	 */
	public function get_state() {
		return get_option( 'eac_business_state', '' );
	}

	/**
	 * Get the zip code of the business.
	 *
	 * @since 1.0.0
	 * @return string Company postcode code.
	 */
	public function get_postcode() {
		return get_option( 'eac_business_postcode', '' );
	}

	/**
	 * Get the country of the business.
	 *
	 * @since 1.0.0
	 * @return string Company country.
	 */
	public function get_country() {
		return get_option( 'eac_business_country', '' );
	}

	/**
	 * Get the logo of the business.
	 *
	 * @since 1.0.0
	 * @return string Company logo.
	 */
	public function get_logo() {
		return get_option( 'eac_business_logo', '' );
	}

	/**
	 * Get currency.
	 *
	 * @since 1.0.0
	 * @return string Currency code.
	 */
	public function get_currency() {
		$currency = get_option( 'eac_base_currency', 'USD' );

		return empty( $currency ) ? 'USD' : $currency;
	}

	/**
	 * Get the business's tax number.
	 *
	 * @since 1.0.0
	 * @return string Company tax number.
	 */
	public function get_tax_number() {
		return get_option( 'eac_business_tax_number', '' );
	}

	/**
	 * Get financial start date.
	 *
	 * @param string $year Year.
	 *
	 * @since 1.1.6
	 * @return string
	 */
	public function get_year_start_date( $year = '' ) {
		if ( empty( $year ) ) {
			$year = wp_date( 'Y' );
		}

		$year_start = get_option( 'eac_year_start_date', '01-01' );
		$dates      = explode( '-', $year_start );
		$month      = ! empty( $dates[0] ) ? $dates[0] : '01';
		$day        = ! empty( $dates[1] ) ? $dates[1] : '01';
		$year       = empty( $year ) ? (int) wp_date( 'Y' ) : absint( $year );

		return wp_date( 'Y-m-d', mktime( 0, 0, 0, $month, $day, $year ) );
	}

	/**
	 * Get financial end date.
	 *
	 * @param string $year Year.
	 *
	 * @since 1.1.6
	 * @return string
	 */
	public function get_year_end_date( $year = '' ) {
		if ( empty( $year ) ) {
			$year = wp_date( 'Y' );
		}

		$start_date = $this->get_year_start_date( $year );
		// if the year is current year, then end date is today.

		if ( wp_date( 'Y' ) === $year ) {
			return wp_date( 'Y-m-d' );
		}

		return wp_date( 'Y-m-d', strtotime( $start_date . ' +1 year -1 day' ) );
	}
}
