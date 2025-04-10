<?php

namespace EverAccounting\Licensing;

defined( 'ABSPATH' ) || exit;

/**
 * Handles plugin updates and version checks.
 *
 * This class manages the plugin update process, including version checking,
 * update notifications, and displaying changelogs. It integrates with WordPress
 * update system to provide seamless updates for licensed plugins.
 *
 * @since 1.0.0
 * @package EverAccounting
 */
class Updater {
	/**
	 * License instance.
	 *
	 * @since 1.0.0
	 * @var License
	 */
	protected $license;

	/**
	 * Initialize the updater.
	 *
	 * Sets up the necessary hooks and filters for handling plugin updates
	 * and version checks.
	 *
	 * @param License $license The license instance.
	 *
	 * @since 1.0.0
	 */
	public function __construct( $license ) {
		$this->license = $license;
		$basename      = $this->license->basename;
		add_filter( 'plugins_api', array( $this, 'plugins_api_filter' ), 10, 3 );
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_update' ) );
		add_action( 'after_plugin_row', array( $this, 'update_notification' ), 10, 2 );
		add_action( 'init', array( $this, 'show_changelog' ) );
		add_action( 'eac_daily_license_check', array( $this, 'update_status' ) );
		add_action( 'wp_version_check', array( $this, 'update_status' ) );
	}

	/**
	 * Filters the plugin API response for getting plugin information.
	 *
	 * @param false|object $result The result object or false.
	 * @param string       $action The type of information being requested from the Plugin Installation API.
	 * @param object       $args   Plugin API arguments.
	 *
	 * @since 1.0.0
	 * @return false|object Plugin API response.
	 */
	public function plugins_api_filter( $result, $action, $args ) {
		if ( 'plugin_information' !== $action || ! isset( $args->slug ) || $args->slug !== $this->license->slug ) {
			return $result;
		}

		$request = $this->get_latest_version();

		if ( is_object( $request ) || isset( $request->sections ) ) {
			return $request;
		}

		return $request;
	}

	/**
	 * Check for plugin updates.
	 *
	 * Checks if there's a new version of the plugin available and updates
	 * the WordPress update transient accordingly.
	 *
	 * @param object $transient The WordPress update transient object.
	 *
	 * @since 1.0.0
	 * @return object Modified update transient object.
	 */
	public function check_update( $transient ) {
		if ( ! is_object( $transient ) ) {
			$transient = new \stdClass();
		}

		// First check if plugin info already exists in the WP transient.
		if ( ! empty( $transient->response ) && ! empty( $transient->response[ $this->license->basename ] ) ) {
			return $transient;
		}

		$latest_version = $this->get_latest_version();

		if ( is_object( $latest_version ) && isset( $latest_version->new_version ) ) {
			if ( version_compare( $this->license->version, $latest_version->new_version, '<' ) ) {
				$transient->response[ $this->license->basename ]         = $latest_version;
				$transient->response[ $this->license->basename ]->plugin = $this->license->basename;
				$transient->response[ $this->license->basename ]->id     = $this->license->basename;
			} else {
				$transient->no_update[ $this->license->basename ] = $latest_version;
			}
		}

		$transient->last_checked                        = time();
		$transient->checked[ $this->license->basename ] = $this->license->version;

		return $transient;
	}

	/**
	 * Display update notification in plugin list.
	 *
	 * Shows an update notification for the plugin in the WordPress admin
	 * plugins list when a new version is available.
	 *
	 * @param string $basename The plugin's basename (plugin-folder/plugin-file.php).
	 * @param array  $plugin   Array of plugin data.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function update_notification( $basename, $plugin ) {
		if ( is_network_admin() || ! is_multisite() || ! current_user_can( 'activate_plugins' ) || $this->license->basename !== $basename ) {
			return;
		}

		$latest_version = $this->get_latest_version();
		if ( ! isset( $latest_version->new_version ) || version_compare( $plugin['Version'], $latest_version->new_version, '>=' ) ) {
			return;
		}

		$changelog_link = '';
		if ( ! empty( $latest_version->sections['changelog'] ) ) {
			$changelog_link = add_query_arg(
				array(
					'action'    => 'view_plugin_changelog',
					'plugin'    => rawurlencode( $basename ),
					'slug'      => rawurlencode( $this->license->slug ),
					'TB_iframe' => 'true',
					'width'     => 77,
					'height'    => 911,
				),
				self_admin_url( 'index.php' )
			);
		}

		$update_link = add_query_arg(
			array(
				'action' => 'upgrade-plugin',
				'plugin' => rawurlencode( $basename ),
			),
			self_admin_url( 'update.php' )
		);

		$message = sprintf(
			// translators: %1$s: plugin name.
			esc_html__( 'There is a new version of %1$s available.', 'wp-ever-accounting' ),
			esc_html( $this->license->name )
		);

		if ( ! current_user_can( 'update_plugins' ) ) {
			$message .= ' ';
			$message .= esc_html__( 'Contact your network administrator to install the update.', 'wp-ever-accounting' );
		} elseif ( empty( $latest_version->package ) && ! empty( $changelog_link ) ) {
			$message .= ' ';
			$message .= sprintf(
				// translators: %1$s: opening anchor tag, %2$s: new version number, %3$s: closing anchor tag.
				__( '%1$sView version %2$s details%3$s.', 'wp-ever-accounting' ),
				'<a target="_blank" class="thickbox open-plugin-details-modal" href="' . esc_url( wp_nonce_url( $changelog_link, 'view_plugin_changelog' ) ) . '">',
				esc_html( $latest_version->new_version ),
				'</a>'
			);
		} elseif ( ! empty( $changelog_link ) ) {
			$message .= ' ';
			$message .= sprintf(
				// translators: %1$s: opening anchor tag, %2$s: new version number, %3$s: closing anchor tag, %4$s: opening anchor tag, %5$s: closing anchor tag.
				__( '%1$sView version %2$s details%3$s or %4$supdate now%5$s.', 'wp-ever-accounting' ),
				'<a target="_blank" class="thickbox open-plugin-details-modal" href="' . esc_url( wp_nonce_url( $changelog_link, 'view_plugin_changelog' ) ) . '">',
				esc_html( $latest_version->new_version ),
				'</a>',
				'<a target="_blank" class="update-link" href="' . esc_url( wp_nonce_url( $update_link, 'upgrade-plugin_' . $basename ) ) . '">',
				'</a>'
			);
		} else {
			$message .= sprintf(
				// translators: %1$s: opening anchor tag, %2$s: closing anchor tag.
				__( '%1$sUpdate now%2$s.', 'wp-ever-accounting' ),
				'<a target="_blank" class="update-link" href="' . esc_url( wp_nonce_url( $update_link, 'upgrade-plugin_' . $basename ) ) . '">',
				'</a>'
			);
		}

		$screen         = get_current_screen();
		$columns        = get_column_headers( $screen );
		$colspan        = ! is_countable( $columns ) ? 3 : count( $columns );
		$active_plugins = array_merge( (array) get_option( 'active_plugins', array() ), (array) get_site_option( 'active_sitewide_plugins', array() ) );
		$active_status  = in_array( $basename, $active_plugins, true ) ? 'active' : 'inactive';
		?>
		<tr class="plugin-update-tr <?php echo esc_attr( $active_status ); ?>" data-slug="<?php echo esc_attr( dirname( $basename ) ); ?>" data-plugin="<?php echo esc_attr( $basename ); ?>">
			<td colspan="<?php echo esc_attr( $colspan ); ?>" class="plugin-update colspanchange">
				<div class="update-message notice inline notice-warning notice-alt">
					<?php echo wp_kses_post( wpautop( wptexturize( $message ) ) ); ?>
					<?php do_action( "in_plugin_update_message-{$basename}", $plugin, $plugin ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores ?>
				</div>
			</td>
		</tr>
		<?php
	}

	/**
	 * Display the changelog for the plugin.
	 *
	 * Shows the changelog in a thickbox when viewing plugin details.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function show_changelog() {
		if ( ! isset( $_GET['action'] ) || 'view_plugin_changelog' !== $_GET['action'] ) {
			return;
		}

		if ( empty( $_GET['plugin'] ) || $this->license->basename !== $_GET['plugin'] ) {
			return;
		}

		check_admin_referer( 'view_plugin_changelog' );

		if ( ! current_user_can( 'update_plugins' ) ) {
			wp_die( esc_html__( 'You do not have permission to install plugin updates', 'wp-ever-accounting' ), esc_html__( 'Error', 'wp-ever-accounting' ), array( 'response' => 403 ) );
		}

		$version_info = $this->get_latest_version();
		if ( isset( $version_info->sections ) ) {
			if ( ! empty( $version_info->sections['changelog'] ) ) {
				echo '<div style="background:#fff;padding:10px;">' . wp_kses_post( $version_info->sections['changelog'] ) . '</div>';
			}
		}

		exit();
	}

	/**
	 * Update the license status.
	 *
	 * Checks and updates the license status by making a request to the licensing server.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function update_status() {
		if ( empty( $this->license ) ) {
			return;
		}
		$data = $this->license->make_request( array( 'edd_action' => 'check_license' ) );
		if ( ! is_wp_error( $data ) && isset( $data->license ) ) {
			$this->license->update( array( 'status' => $data->license ) );
		}
	}

	/**
	 * Get the latest version information.
	 *
	 * Retrieves the latest version information from the licensing server,
	 * including changelog and update package URL if the license is valid.
	 *
	 * @param bool $force Whether to force a fresh request to the server.
	 *
	 * @since 1.0.0
	 * @return \stdClass The version information object.
	 */
	private function get_latest_version( $force = true ) {
		$data = get_transient( $this->license->opt_prefix . '_latest_version', array() );

		if ( $force || false === $data ) {
			$api_params = array(
				'edd_action' => 'get_version',
				'license'    => $this->license->license,
				'item_id'    => $this->license->item_id,
				'slug'       => $this->license->slug,
				'is_ssl'     => is_ssl(),
				'fields'     => array(
					'reviews' => false,
					'banners' => array(),
					'icons'   => array(),
				),
			);

			$data = $this->license->make_request( $api_params );
			if ( ! is_wp_error( $data ) && is_object( $data ) && isset( $data->new_version ) ) {
				foreach ( get_object_vars( $data ) as $prop => $value ) {
					$data->$prop = maybe_unserialize( $value );
				}
				$data->name = $this->license->name;
				$data->slug = $this->license->slug;
				foreach ( array( 'sections', 'banners', 'icons' ) as $prop ) {
					if ( ! empty( $request->$prop ) ) {
						$data->$prop = (array) $request->$prop;
					}
				}

				set_transient( $this->license->opt_prefix . '_latest_version', $data, 3 * HOUR_IN_SECONDS );
			}
		}

		// We will reset the package if the license is not valid.
		if ( ! $this->license->is_valid() ) {
			$data->package               = '';
			$data->sections['changelog'] = sprintf(
				// translators: %s: plugin name.
				esc_html__( 'Please activate your license key for %s to get the latest updates and changelog.', 'wp-ever-accounting' ),
				$this->license->name
			);
		}

		return $data;
	}
}
