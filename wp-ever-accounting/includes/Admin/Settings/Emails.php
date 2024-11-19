<?php

namespace EverAccounting\Admin\Settings;

/**
 * Class Emails.
 *
 * @since   1.0.0
 * @package EverAccounting\Admin\Settings
 */
class Emails extends Page {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct( 'emails', __( 'Emails', 'wp-ever-accounting' ) );
	}

	/**
	 * Get default section settings.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_default_section_settings() {
		return array(
			// new payment email section.
			array(
				'type'  => 'title',
				'title' => __( 'New Payment [Customer]', 'wp-ever-accounting' ),
				'desc'  => __( 'Email sent to the customer when a new payment is received.', 'wp-ever-accounting' ),
				'id'    => 'new_payment_customer_email',
			),
			// subject.
			array(
				'title'   => __( 'Subject', 'wp-ever-accounting' ),
				'type'    => 'text',
				'id'      => 'new_payment_customer_email_subject',
				'default' => __( 'Payment Receipt from {company_name}', 'wp-ever-accounting' ),
				'desc'    => __( 'Enter the subject for this email.', 'wp-ever-accounting' ),
			),
			// email body.
			array(
				'title'       => __( 'Content', 'wp-ever-accounting' ),
				'type'        => 'wp_editor',
				'id'          => 'new_payment_customer_email_content',
				'sanitize_cb' => 'sanitize_textarea_field',
				'default'     => __( 'Hello {customer_name},<br><br>We have received your payment of {payment_amount} on {payment_date}.<br><br> You can view your payment details by clicking the link below:<br>{payment_link}<br><br>Thank you for your business.<br><br>{business_name}', 'wp-ever-accounting' ),
				'desc'        => __( 'Available template tags:', 'wp-ever-accounting' )
								. '<br>{payment_amount} - ' . __( 'Payment amount.', 'wp-ever-accounting' )
								. '<br>{payment_date} - ' . __( 'Payment date.', 'wp-ever-accounting' )
								. '<br>{payment_number} - ' . __( 'Payment number.', 'wp-ever-accounting' )
								. '<br>{payment_link} - ' . __( 'Payment link.', 'wp-ever-accounting' )
								. '<br>{customer_name} - ' . __( 'Customer name.', 'wp-ever-accounting' )
								. '<br>{customer_company} - ' . __( 'Customer company.', 'wp-ever-accounting' )
								. '<br>{customer_email} - ' . __( 'Customer email.', 'wp-ever-accounting' )
								. '<br>{customer_phone} - ' . __( 'Customer phone.', 'wp-ever-accounting' )
								. '<br>{customer_address} - ' . __( 'Customer address.', 'wp-ever-accounting' )
								. '<br>{business_name} - ' . __( 'Business name.', 'wp-ever-accounting' )
								. '<br>{business_address} - ' . __( 'Business address.', 'wp-ever-accounting' ),

			),
			// end.
			array(
				'type' => 'sectionend',
				'id'   => 'new_payment_customer_email',
			),
			array(
				'type'  => 'title',
				'title' => __( 'New Payment [Admin]', 'wp-ever-accounting' ),
				'desc'  => __( 'Email sent to the admin when a new payment is received.', 'wp-ever-accounting' ),
				'id'    => 'new_payment_admin_email',
			),
			// enable disable.
			array(
				'title'   => __( 'Enable', 'wp-ever-accounting' ),
				'type'    => 'checkbox',
				'id'      => 'new_payment_admin_email_enable',
				'default' => 'yes',
				'desc'    => __( 'Enable this email notification.', 'wp-ever-accounting' ),
			),
			// subject.
			array(
				'title'   => __( 'Subject', 'wp-ever-accounting' ),
				'type'    => 'text',
				'id'      => 'new_payment_admin_email_subject',
				'default' => __( 'New Payment Received from {customer_name}', 'wp-ever-accounting' ),
				'desc'    => __( 'Enter the subject for this email.', 'wp-ever-accounting' ),
			),
			// email body.
			array(
				'title'       => __( 'Content', 'wp-ever-accounting' ),
				'type'        => 'wp_editor',
				'id'          => 'new_payment_admin_email_content',
				'sanitize_cb' => 'sanitize_textarea_field',
				'default'     => __( 'Hello,<br><br>A new payment of {payment_amount} has been received from {customer_name} on {payment_date}.<br><br> You can view the payment details by clicking the link below:<br>{payment_link}<br><br>{business_name}', 'wp-ever-accounting' ),
				'desc'        => __( 'Available template tags:', 'wp-ever-accounting' )
								. '<br>{payment_amount} - ' . __( 'Payment amount.', 'wp-ever-accounting' )
								. '<br>{payment_date} - ' . __( 'Payment date.', 'wp-ever-accounting' )
								. '<br>{payment_number} - ' . __( 'Payment number.', 'wp-ever-accounting' )
								. '<br>{payment_link} - ' . __( 'Payment link.', 'wp-ever-accounting' )
								. '<br>{customer_name} - ' . __( 'Customer name.', 'wp-ever-accounting' )
								. '<br>{customer_company} - ' . __( 'Customer company.', 'wp-ever-accounting' )
								. '<br>{customer_email} - ' . __( 'Customer email.', 'wp-ever-accounting' )
								. '<br>{customer_phone} - ' . __( 'Customer phone.', 'wp-ever-accounting' )
								. '<br>{customer_address} - ' . __( 'Customer address.', 'wp-ever-accounting' )
								. '<br>{business_name} - ' . __( 'Business name.', 'wp-ever-accounting' )
								. '<br>{business_address} - ' . __( 'Business address.', 'wp-ever-accounting' ),
			),
			// end.
			array(
				'type' => 'sectionend',
				'id'   => 'new_payment_admin_email',
			),
		);
	}
}
