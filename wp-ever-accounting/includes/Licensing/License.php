<?php

namespace EverAccounting\Licensing;

defined( 'ABSPATH' ) || exit;

/**
 * Class License
 *
 * @package EverAccounting\Licensing
 *
 * @property-read string $key The license key.
 * @property-read string $status The license status.
 * @property-read string $url The license URL.
 * @property-read string $expires The license expiration date.
 */
class License {
	/**
	 * Item slug.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $slug;

	/**
	 * Item basename.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $basename;

	/**
	 * Plugin name.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $name;

	/**
	 * Plugin version.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $version;

	/**
	 * Item ID.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $item_id;

	/**
	 * API URL.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $api_url;

	/**
	 * Option prefix.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $opt_prefix;

	/**
	 * License option.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $option_name;

	/**
	 * License data.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $data = array(
		'key'     => '',
		'status'  => '',
		'url'     => '',
		'expires' => '',
	);

	/**
	 * Updater instance.
	 *
	 * @since 1.0.0
	 * @var Updater
	 */
	private $updater;

	/**
	 * Admin instance.
	 *
	 * @since 1.0.0
	 * @var Admin
	 */
	private $admin;

	/**
	 * License constructor.
	 *
	 * @param string $file The plugin file.
	 * @param string $name The plugin name.
	 * @param string $version The plugin version.
	 * @param string $item_id The item ID.
	 * @param string $api_url The API URL.
	 *
	 * @since 1.0.0
	 */
	public function __construct( $file, $name, $version, $item_id = null, $api_url = 'https://wpeveraccounting.com/edd-sl-api' ) {
		$this->basename    = plugin_basename( $file );
		$this->slug        = dirname( $this->basename );
		$this->name        = $name;
		$this->version     = $version;
		$this->item_id     = $item_id;
		$this->api_url     = $api_url;
		$this->opt_prefix  = preg_replace( '/[^a-zA-Z0-9]/', '_', strtolower( $this->slug ) );
		$this->option_name = $this->opt_prefix . '_license';
		$this->data        = wp_parse_args( (array) get_option( $this->option_name, array() ), $this->data );
		$this->updater     = new Updater( $this );

		if ( is_admin() && current_user_can( 'manage_options' ) ) {
			$this->admin = new Admin( $this );
		}

		// migrate legacy license. todo remove in future.
		if ( ! empty( get_option( $this->opt_prefix . '_license_key', '' ) ) ) {
			$this->data['key']    = get_option( $this->opt_prefix . '_license_key', '' );
			$this->data['status'] = get_option( $this->opt_prefix . '_license_status', '' );
			$this->data['url']    = wp_parse_url( site_url(), PHP_URL_HOST );
			$this->update( $this->data );
			delete_option( $this->opt_prefix . '_license_key' );
			delete_option( $this->opt_prefix . '_license_status' );
		}
	}

	/**
	 * Magic method to get the item properties.
	 *
	 * @param string $property Property name.
	 *
	 * @return mixed
	 */
	public function __get( $property ) {
		if ( property_exists( $this, $property ) ) {
			return $this->$property;
		} elseif ( isset( $this->data[ $property ] ) ) {
			return $this->data[ $property ];
		}

		return null;
	}

	/**
	 * Is set magic method.
	 *
	 * @param string $property Property name.
	 *
	 * @return bool
	 */
	public function __isset( $property ) {
		if ( property_exists( $this, $property ) ) {
			return isset( $this->$property );
		} elseif ( isset( $this->data[ $property ] ) ) {
			return isset( $this->data[ $property ] );
		}

		return false;
	}

	/**
	 * Get an error message.
	 *
	 * @param string $status The status.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_message( $status ) {
		switch ( $status ) {
			case '':
			case 'missing':
				$message = esc_html__( 'Your license key is missing.', 'wp-ever-accounting' );
				break;
			case 'valid':
				$message = esc_html__( 'Your license key is valid.', 'wp-ever-accounting' );
				break;
			case 'expired':
				$expires = ! empty( $this->license_data['expires'] && 'lifetime' !== $this->license_data['expires'] ) ? strtotime( $this->data['expires'] ) : 0;
				$expired = $expires > 0 && $expires > wp_date( 'U' ) && ( $expires - wp_date( 'U' ) < DAY_IN_SECONDS );
				if ( $expired ) {
					$message = sprintf(
					// translators: %s: license expiration date.
						__( 'Your license key has expired on %s.', 'wp-ever-accounting' ),
						esc_html( date_i18n( get_option( 'date_format' ), $expires ) )
					);
				} else {
					$message = __( 'Your license key has expired.', 'wp-ever-accounting' );
				}
				break;
			case 'revoked':
			case 'disabled':
				$message = esc_html__( 'Your license key has been disabled.', 'wp-ever-accounting' );
				break;
			case 'site_inactive':
				$message = esc_html__( 'Your license key is not valid for this site.', 'wp-ever-accounting' );
				break;
			case 'invalid':
			case 'invalid_item_id':
			case 'item_name_mismatch':
			case 'key_mismatch':
				$message = esc_html__( 'This appears to be an invalid license key.', 'wp-ever-accounting' );
				break;
			case 'no_activations_left':
				$message = esc_html__( 'Your license key has reached its activation limit.', 'wp-ever-accounting' );
				break;
			case 'license_not_activable':
				$message = esc_html__( 'The key you entered belongs to a bundle, please use the product specific license key.', 'wp-ever-accounting' );
				break;
			default:
				$message = esc_html__( 'Your license key is invalid.', 'wp-ever-accounting' );
				break;
		}

		return $message;
	}

	/**
	 * Determine if the license is valid.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function is_valid() {
		return 'valid' === $this->data['status'] && !empty( $this->data['key'] );
	}

	/**
	 * Is the license disabled?
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function is_disabled() {
		return 'disabled' === $this->data['status'];
	}

	/**
	 * Determine if the license is expired.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function is_expired() {
		return 'expired' === $this->data['status'];
	}

	/**
	 * Determine if the site is moved.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function is_moved() {
		$active_url = isset( $this->data['url'] ) ? $this->data['url'] : '';
		if ( empty( $active_url ) ) {
			return false;
		}

		$has_moved = wp_parse_url( site_url(), PHP_URL_HOST ) !== $active_url;
		if ( $has_moved && $this->is_valid() ) {
			$this->update(
				array(
					'status' => 'status',
					'error'  => 'site_inactive',
				)
			);
		}

		return $has_moved;
	}

	/**
	 * Save the license data.
	 *
	 * @param array $data The license data.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function update( $data ) {
		if ( is_array( $data ) && ! empty( $data ) ) {
			$this->data = wp_parse_args( $data, $this->data );
			update_option( $this->option_name, $this->data );
		}
	}

	/**
	 * Make request.
	 *
	 * @param array $params The API parameters.
	 *
	 * @return \stdClass The result object.
	 */
	public function make_request( $params = array() ) {
		if ( empty( $params ) || ! is_array( $params ) ) {
			return (object) array(
				'success'  => false,
				'response' => __( 'Invalid request.', 'wp-ever-accounting' ),
			);
		}

		$params = wp_parse_args(
			$params,
			array(
				'license'       => $this->data['key'],
				'item_id'       => $this->item_id,
				'slug'          => $this->slug,
				'url'           => home_url(),
				'wp_version'    => get_bloginfo( 'version' ),
				'php_version'   => phpversion(),
				'mysql_version' => $GLOBALS['wpdb']->db_version(),
			)
		);

		$response = wp_remote_post(
			add_query_arg( array( 'url' => rawurlencode( home_url() ) ), $this->api_url ),
			array(
				'timeout'   => 15,
				'sslverify' => true,
				'body'      => $params,
			)
		);

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			$message = is_wp_error( $response ) ? $response->get_error_message() : wp_remote_retrieve_response_message( $response );

			return (object) array(
				'success'  => false,
				'response' => $message,
			);
		}

		$result = json_decode( wp_remote_retrieve_body( $response ) );
		if ( ! is_object( $result ) ) {
			return (object) array(
				'success'  => false,
				'response' => __( 'Invalid response.', 'wp-ever-accounting' ),
			);
		}

		return $result;
	}
}
