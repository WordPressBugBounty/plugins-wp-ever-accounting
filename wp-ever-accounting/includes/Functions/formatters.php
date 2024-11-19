<?php

use EverAccounting\Utilities\I18nUtil;

defined( 'ABSPATH' ) || exit;

/**
 * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
 * Non-scalar values are ignored.
 *
 * @param string|array $value Data to sanitize.
 *
 * @since 1.1.6
 * @return string|array
 */
function eac_clean( $value ) {
	if ( is_array( $value ) ) {
		return array_map( 'eac_clean', $value );
	} else {
		return is_scalar( $value ) ? sanitize_text_field( $value ) : $value;
	}
}

/**
 * Display help tip.
 *
 * @param string $tip Help tip text.
 * @param bool   $allow_html Allow sanitized HTML if true or escape.
 *
 * @since 1.1.6 Renamed from eaccounting_tooltip() to eac_tooltip().
 * @since 1.0.2
 * @return string
 */
function eac_tooltip( $tip, $allow_html = false ) {
	if ( $allow_html ) {
		$tip = eac_sanitize_tooltip( $tip );
	} else {
		$tip = esc_attr( $tip );
	}

	return '<span class="eac-tooltip" title="' . wp_kses_post( $tip ) . '">[?]</span>';
}


/**
 * Sanitize a string destined to be a tooltip.
 *
 * @param string $text Data to sanitize.
 *
 * @since 1.1.6
 * @return string
 */
function eac_sanitize_tooltip( $text ) {
	return htmlspecialchars(
		wp_kses(
			html_entity_decode( $text ),
			array(
				'br'     => array(),
				'em'     => array(),
				'strong' => array(),
				'small'  => array(),
				'span'   => array(),
				'ul'     => array(),
				'li'     => array(),
				'ol'     => array(),
				'p'      => array(),
			)
		)
	);
}


/**
 * Get country address format.
 *
 * @param array  $fields Address fields.
 * @param string $separator How to separate address lines.
 *
 * @return string
 */
function eac_get_formatted_address( $fields = array(), $separator = '<br/>' ) {
	$defaults          = array(
		'name'     => '',
		'company'  => '',
		'address'  => '',
		'city'     => '',
		'state'    => '',
		'postcode' => '',
		'country'  => '',
	);
	$format            = apply_filters( 'eac_address_format', "<strong>{name}</strong>\n{company}\n{address}\n{city} {state} {postcode}\n{country}" );
	$fields            = array_map( 'trim', wp_parse_args( $fields, $defaults ) );
	$countries         = I18nUtil::get_countries();
	$fields['country'] = isset( $countries[ $fields['country'] ] ) ? $countries[ $fields['country'] ] : $fields['country'];
	$replacers         = array_map(
		'esc_html',
		array(
			'{name}'     => $fields['name'],
			'{company}'  => $fields['company'],
			'{address}'  => $fields['address'],
			'{city}'     => $fields['city'],
			'{state}'    => $fields['state'],
			'{postcode}' => $fields['postcode'],
			'{country}'  => $fields['country'],
		)
	);
	$formatted_address = str_replace( array_keys( $replacers ), $replacers, $format );
	// Clean up white space.
	$formatted_address = preg_replace( '/  +/', ' ', trim( $formatted_address ) );
	$formatted_address = preg_replace( '/\n\n+/', "\n", $formatted_address );
	// Break newlines apart and remove empty lines/trim commas and white space.
	$address_lines = array_map( 'trim', array_filter( explode( "\n", $formatted_address ) ) );
	// Now check if their any additional fields.
	$extra = array_diff_key( $fields, $defaults );
	foreach ( $extra as $key => $value ) {
		if ( ! empty( $value ) ) {
			switch ( $key ) {
				case 'tax_number':
					$address_lines[] = __( 'Tax #', 'wp-ever-accounting' ) . $value;
					break;
				default:
					$address_lines[] = $value;
			}
		}
	}

	// clean up white space.
	$address_lines = array_map( 'trim', $address_lines );
	// Remove empty lines.
	$address_lines = array_filter( $address_lines );

	return implode( $separator, $address_lines );
}


/**
 * Date Format - Allows to change date format.
 *
 * @since 1.0.0
 * @return string
 */
function eac_date_format() {
	$date_format = get_option( 'date_format' );
	if ( empty( $date_format ) ) {
		// Return default date format if the option is empty.
		$date_format = 'F j, Y';
	}

	return apply_filters( 'eac_date_format', $date_format );
}

/**
 * Time Format - Allows to change time format.
 *
 * @return string
 */
function eac_time_format() {
	$time_format = get_option( 'time_format' );
	if ( empty( $time_format ) ) {
		// Return default time format if the option is empty.
		$time_format = 'g:i a';
	}

	return apply_filters( 'eac_time_format', $time_format );
}

/**
 * Date Time Format - Allows to change date time format for everything WooCommerce.
 * Combines date and time formats.
 *
 * @return string
 */
function eac_date_time_format() {
	return eac_date_format() . '@' . eac_time_format();
}
