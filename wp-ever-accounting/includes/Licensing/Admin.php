<?php

namespace EverAccounting\Licensing;

defined( 'ABSPATH' ) || exit;

/**
 * Admin class.
 *
 * @since 1.0.0
 * @package EverAccounting
 */
class Admin {

	/**
	 * License instance.
	 *
	 * @since 1.0.0
	 * @var License
	 */
	protected $license;

	/**
	 * Admin constructor.
	 *
	 * @param License $license The license.
	 *
	 * @since 1.0.0
	 */
	public function __construct( $license ) {
		$this->license = $license;
		$basename      = $this->license->basename;

		add_action( 'admin_init', array( $this, 'schedule_check' ) );
		add_action( 'admin_notices', array( $this, 'license_notice' ) );
		add_action( 'plugin_action_links_' . $basename, array( $this, 'action_links' ) );
		add_action( 'after_plugin_row_' . $basename, array( $this, 'license_row' ), 10, 3 );
		add_action( 'wp_ajax_' . $basename . '_license_action', array( $this, 'handle_action' ) );
	}

	/**
	 * Schedule the license check event.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function schedule_check() {
		if ( ! wp_next_scheduled( 'eac_daily_license_check' ) ) {
			wp_schedule_event( time(), 'daily', 'eac_daily_license_check' );
		}
	}

	/**
	 * Display license notices.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function license_notice() {
		// determine if the current page is plugins.php.
		$screens = get_current_screen();
		if ( ( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === $_SERVER['REQUEST_METHOD'] )
			|| ( $screens && 'plugins' !== $screens->id )
			|| $this->license->is_valid()
			|| ! current_user_can( 'manage_options' )
			|| apply_filters( 'eac_hide_license_notices', false, $this->license ) ) {
			return;
		}

		if ( empty( $this->license->key ) ) {
			$message = sprintf(
			// translators: the extension name.
				__( 'Your license key for %1$s is missing. Please %2$s enter your license key %3$s to continue receiving updates and support.', 'wp-ever-accounting' ),
				'<strong>' . esc_html( $this->license->name ) . '</strong>',
				'<a href="' . esc_url( admin_url( 'plugins.php' ) ) . '">',
				'</a>'
			);

			printf( '<div class="notice notice-warning is-dismissible">%s</div>', wp_kses_post( wpautop( wptexturize( $message ) ) ) );
		} elseif ( $this->license->is_expired() ) {
			$message = sprintf(
			// translators: the extension name.
				__( 'Your license key for %1$s has expired. Please renew your license to continue receiving updates and support.', 'wp-ever-accounting' ),
				'<strong>' . esc_html( $this->license->name ) . '</strong>',
			);

			printf( '<div class="notice notice-warning is-dismissible">%s</div>', wp_kses_post( wpautop( wptexturize( $message ) ) ) );
		} elseif ( $this->license->is_disabled() ) {
			$message = sprintf(
			// translators: the extension name.
				__( 'You no longer have a valid license for %1$s. Please renew your license to continue receiving updates and support.', 'wp-ever-accounting' ),
				'<strong>' . esc_html( $this->license->name ) . '</strong>',
			);

			printf( '<div class="notice notice-warning is-dismissible">%s</div>', wp_kses_post( wpautop( wptexturize( $message ) ) ) );
		} elseif ( $this->license->is_moved() ) {
			$message = sprintf(
			// translators: the extension name.
				__( '%1$s - Your license key is not valid for this site. Please deactivate the license and activate it again.', 'wp-ever-accounting' ),
				'<strong>' . esc_html( $this->license->name ) . '</strong>'
			);

			printf( '<div class="notice notice-warning is-dismissible">%s</div>', wp_kses_post( wpautop( wptexturize( $message ) ) ) );
		}
	}

	/**
	 * Add plugin action links.
	 *
	 * @param array $links The plugin action links.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function action_links( $links ) {
		if ( ! current_user_can( 'manage_options' ) || ! $this->license->is_valid() ) {
			return $links;
		}
		$links['license'] = sprintf(
			'<a href="javascript:void(0);" class="license-manage-link" aria-label="%1$s">%1$s</a>',
			__( 'License', 'wp-ever-accounting' )
		);

		return $links;
	}

	/**
	 * Display plugin row.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function license_row() {
		$status  = $this->license->status;
		$screen  = get_current_screen();
		$columns = get_column_headers( $screen );
		$colspan = ! is_countable( $columns ) ? 3 : count( $columns );
		$visible = $this->license->is_valid() ? 'hidden' : 'visible';
		$action  = $this->license->basename . '_license_action';
		$nonce   = wp_create_nonce( $this->license->basename . '_license_action' );
		?>
		<tr class="license-row notice-warning notice-alt plugin-update-tr <?php echo esc_attr( $visible ); ?>" data-plugin="<?php echo esc_attr( $this->license->basename ); ?>">
			<td colspan="<?php echo esc_attr( $colspan ); ?>" class="plugin-update colspanchange">
				<div class="update-message" style="margin-top: 15px;display: flex;flex-direction: row;align-items: center;flex-wrap: wrap;gap: 10px;">
					<?php if ( $this->license->is_valid() ) : ?>
						<span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span>
						<span><?php esc_html_e( 'License is valid.', 'wp-ever-accounting' ); ?></span>
					<?php else : ?>
						<span class="dashicons dashicons-warning" style="color: #dc3232;"></span>
						<span><?php echo wp_kses_post( $this->license->get_message( $status ) ); ?></span>
					<?php endif; ?>
					<input
						class="regular-text license-key"
						type="text"
						placeholder="<?php esc_attr_e( 'Enter your license key', 'wp-ever-accounting' ); ?>"
						value="<?php echo esc_attr( $this->license->license ); ?>"
						style="width: 18em;margin-right:-10px; border-top-right-radius:0; border-bottom-right-radius:0; border-right:0;"
					>
					<button
						class="button license-button"
						data-action="<?php echo esc_attr( $action ); ?>"
						data-operation="activate"
						data-nonce="<?php echo esc_attr( $nonce ); ?>"
						style="line-height: 20px;border-top-left-radius:0; border-bottom-left-radius:0;">
						<span class="dashicons dashicons-admin-network"></span>
						<?php esc_html_e( 'Activate', 'wp-ever-accounting' ); ?>
					</button>
					<?php if ( $this->license->is_valid() ) : ?>
						<button
							class="button license-button"
							data-action="<?php echo esc_attr( $action ); ?>"
							data-operation="deactivate"
							data-nonce="<?php echo esc_attr( $nonce ); ?>"
							style="line-height: 20px;">
							<span class="dashicons dashicons-no-alt"></span>
							<?php esc_html_e( 'Deactivate', 'wp-ever-accounting' ); ?>
						</button>
					<?php endif; ?>
					<span class="spinner"></span>
					<script type="application/javascript">
						addEventListener('DOMContentLoaded', () => {
							// check if Jquery is loaded. If not load return.
							if (typeof jQuery !== 'undefined') {
								jQuery(function ($) {
									$(document.body)
										.on('click', '[data-plugin="<?php echo esc_attr( $this->license->basename ); ?>"] .license-manage-link', function (e) {
											e.preventDefault();
											const plugin = $(this).closest('tr').data('plugin');
											$(this).closest('tr').siblings('.license-row[data-plugin="' + plugin + '"]').toggle();
										})
										.on('click', '[data-plugin="<?php echo esc_attr( $this->license->basename ); ?>"] .license-button', function (e) {
											e.preventDefault();
											var $this = $(this);
											$this.closest('tr').find('.spinner').addClass('is-active');
											$this.closest('tr').find('.license-button').prop('disabled', true);
											wp.ajax.post({
												action: $this.data('action'),
												operation: $this.data('operation'),
												nonce: $this.data('nonce'),
												license_key: $this.closest('tr').find('.license-key').val(),
											}).always(function (response) {
												$this.closest('tr').find('.spinner').removeClass('is-active');
												$this.closest('tr').find('.license-button').prop('disabled', false);
												if (response && response.message) {
													alert(response.message);
												}
												if (response.reload) {
													location.reload();
												}
											})
										});
								});
							}
						});
					</script>
				</div>
			</td>
		</tr>
		<?php
	}

	/**
	 * License AJAX handler.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function handle_action() {
		check_ajax_referer( $this->license->basename . '_license_action', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to perform this action.', 'wp-ever-accounting' ) ) );
		}
		$operation = isset( $_POST['operation'] ) ? sanitize_text_field( wp_unslash( $_POST['operation'] ) ) : '';
		$license   = isset( $_POST['license_key'] ) ? sanitize_text_field( wp_unslash( $_POST['license_key'] ) ) : '';

		if ( empty( $license ) ) {
			wp_send_json_error( array( 'message' => __( 'Please enter a valid license key and try again.', 'wp-ever-accounting' ) ) );
		}

		switch ( $operation ) {
			case 'activate':
				$data            = array();
				$host            = wp_parse_url( site_url(), PHP_URL_HOST );
				$response        = $this->license->client->activate( $license, $this->license->item_id, $host );
				$is_valid        = $response->success && $response->license && 'valid' === $response->license;
				$data['url']     = $host;
				$data['status']  = ! $response->success && ! empty( $response->error ) ? sanitize_key( $response->error ) : sanitize_key( $response->license );
				$data['expires'] = ! empty( $response->expires ) ? $response->expires : '';
				$data['license'] = $is_valid ? $license : '';

				$this->license->update( $data );
				set_site_transient( 'update_plugins', null );

				wp_send_json_success(
					array(
						'message' => esc_html__( 'License key activated successfully.', 'wp-ever-accounting' ),
						'reload'  => true,
					)
				);

				break;
			case 'deactivate':
				if ( empty( $this->license->license ) ) {
					wp_send_json_error( array( 'message' => esc_html__( 'Your license key is missing.', 'wp-ever-accounting' ) ) );
				}
				$response = $this->license->client->deactivate( $license, $this->license->item_id, home_url() );
				set_site_transient( 'update_plugins', null );
				if ( $response->success ) {
					$this->license->update(
						array(
							'status'  => 'inactive',
							'expires' => ! empty( $response->expires ) ? $response->expires : '',
						)
					);

					wp_send_json_success(
						array(
							'message' => esc_html__( 'License key deactivated successfully.', 'wp-ever-accounting' ),
							'reload'  => true,
						)
					);
				}

				wp_send_json_error( array( 'message' => esc_html__( 'Failed to deactivate the license key.', 'wp-ever-accounting' ) ) );

				break;
		}

		wp_send_json_error( array( 'message' => esc_html__( 'Invalid operation.', 'wp-ever-accounting' ) ) );
		wp_die();
	}
}
