<?php
/**
 * Admin View: Bill Address
 *
 * @since  1.0.0
 * @package EverAccounting
 * @var Bill $bill Bill object
 */

use EverAccounting\Models\Bill;

defined( 'ABSPATH' ) || exit;
echo wp_kses_post(
	eac_get_formatted_address(
		array(
			'name'       => $bill->contact_name,
			'company'    => $bill->contact_company,
			'address'    => $bill->contact_address,
			'city'       => $bill->contact_city,
			'state'      => $bill->contact_state,
			'postcode'   => $bill->contact_postcode,
			'country'    => $bill->contact_country,
			'phone'      => $bill->contact_phone,
			'email'      => $bill->contact_email,
			'tax_number' => $bill->contact_tax_number,
		)
	)
);

printf( '<input type="hidden" name="contact_name" value="%s">', esc_attr( $bill->contact_name ) );
printf( '<input type="hidden" name="contact_company" value="%s">', esc_attr( $bill->contact_company ) );
printf( '<input type="hidden" name="contact_address" value="%s">', esc_attr( $bill->contact_address ) );
printf( '<input type="hidden" name="contact_city" value="%s">', esc_attr( $bill->contact_city ) );
printf( '<input type="hidden" name="contact_state" value="%s">', esc_attr( $bill->contact_state ) );
printf( '<input type="hidden" name="contact_postcode" value="%s">', esc_attr( $bill->contact_postcode ) );
printf( '<input type="hidden" name="contact_country" value="%s">', esc_attr( $bill->contact_country ) );
printf( '<input type="hidden" name="contact_phone" value="%s">', esc_attr( $bill->contact_phone ) );
printf( '<input type="hidden" name="contact_email" value="%s">', esc_attr( $bill->contact_email ) );
printf( '<input type="hidden" name="contact_tax_number" value="%s">', esc_attr( $bill->contact_tax_number ) );
