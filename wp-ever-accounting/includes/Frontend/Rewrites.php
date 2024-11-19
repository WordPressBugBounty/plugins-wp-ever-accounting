<?php

namespace EverAccounting\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * Class Rewrites
 *
 * @since 1.0.0
 * @package EverAccounting
 */
class Rewrites {

	/**
	 * Rewrites constructor.
	 */
	public function __construct() {
		add_filter( 'query_vars', array( __CLASS__, 'add_query_vars' ), 0 );
		add_action( 'init', array( __CLASS__, 'add_endpoint' ), 0 );
		add_action( 'parse_request', array( __CLASS__, 'handle_request' ), PHP_INT_MAX );
	}

	/**
	 * Add query vars.
	 *
	 * @param array $vars Query vars.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public static function add_query_vars( $vars ) {
		$vars[] = 'eac';
		$vars[] = 'route';
		$vars[] = 'uuid';

		return $vars;
	}

	/**
	 * Add endpoint.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function add_endpoint() {
		add_rewrite_rule( '^eac/([^/]*)/?', 'index.php?eac=1&route=$matches[1]', 'top' );
	}

	/**
	 * Handle request.
	 *
	 * @param \WP $wp WP.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public static function handle_request( $wp ) {
		if ( ! empty( $wp->query_vars['eac'] ) && ! empty( $wp->query_vars['route'] ) ) {
			$route = sanitize_text_field( wp_unslash( $wp->query_vars['route'] ) );

			if ( has_action( "eac_handle_request_{$route}" ) ) {
				/**
				 * Handle specific route request.
				 *
				 * @param array $vars Query vars.
				 *
				 * @since 2.0.0
				 */
				do_action( "eac_handle_request_{$route}", $wp->query_vars );

				exit();
			}
		}
	}
}
