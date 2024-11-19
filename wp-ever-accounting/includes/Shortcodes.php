<?php

namespace EverAccounting;

defined( 'ABSPATH' ) || exit;

/**
 * Class Shortcodes.
 *
 * @since 1.0.0
 * * @author  Sultan Nasir Uddin <manikdrmc@gmail.com>
 * * @package EverAccounting
 */
class Shortcodes {

	/**
	 * Shortcodes constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_filter( 'query_vars', array( $this, 'add_query_vars' ), 99 );
		add_shortcode( 'eac_payment', array( $this, 'render_payment' ) );
		add_shortcode( 'eac_expense', array( $this, 'render_expense' ) );
		add_shortcode( 'eac_invoice', array( $this, 'render_invoice' ) );
		add_shortcode( 'eac_bill', array( $this, 'render_bill' ) );
	}

	/**
	 * Add query vars.
	 *
	 * @param array $vars Query vars.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function add_query_vars( $vars ) {
		$vars[] = 'uuid';

		return $vars;
	}

	/**
	 * Revenue shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @since 1.0.0
	 */
	public function render_payment( $atts ) {
		// get the uuid from query vars.
		$uuid = get_query_var( 'uuid' );

		$atts = shortcode_atts(
			array(
				'uuid' => sanitize_text_field( $uuid ),
			),
			$atts,
			'eac_payment'
		);

		$payment = EAC()->payments->get( array( 'uuid' => $atts['uuid'] ) );

		if ( ! $payment ) {
			return '';
		}

		ob_start();
		eac_get_template( 'payment.php', array( 'payment' => $payment ) );

		return ob_get_clean();
	}
}
