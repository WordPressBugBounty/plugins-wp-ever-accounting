<?php

namespace EverAccounting\API;

use EverAccounting\Utilities\I18nUtil;

defined( 'ABSPATH' ) || exit;

/**
 * Class Utilities
 *
 * @package EverAccounting\API
 */
class Utilities extends Controller {

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'utilities';

	/**
	 * Registers the routes for the objects of the controller.
	 *
	 * @see register_rest_route()
	 * @since 2.0.0
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/currencies',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_currencies' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_accounting' );  // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Reason: This is a custom capability.
				},
			)
		);
		// Countries.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/countries',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_countries' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_accounting' );  // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Reason: This is a custom capability.
				},
			)
		);
	}

	/**
	 * Get currencies.
	 *
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @return \WP_REST_Response
	 */
	public function get_currencies( $request ) {
		$currencies = I18nUtil::get_currencies();

		return rest_ensure_response( $currencies );
	}

	/**
	 * Get countries.
	 *
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @return \WP_REST_Response
	 */
	public function get_countries( $request ) {
		$countries = I18nUtil::get_countries();

		return rest_ensure_response( $countries );
	}
}
