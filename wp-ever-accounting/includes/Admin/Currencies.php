<?php

namespace EverAccounting\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Class Currencies
 *
 * @since 1.0.0
 * @package EverAccounting\Admin
 */
class Currencies {

	/**
	 * Currencies constructor.
	 */
	public function __construct() {
		add_action( 'eac_settings_field_exchange_rates', array( __CLASS__, 'exchange_rates_field' ) );
	}

	/**
	 * Exchange rates field.
	 *
	 * @param array $value Field arguments.
	 *
	 * @since 1.0.0
	 */
	public static function exchange_rates_field( $value ) {
		$currencies = eac_get_currencies();
		unset( $currencies[ eac_base_currency() ] );
		$rates = get_option( 'eac_exchange_rates', array() );
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label
					for="<?php echo esc_attr( $value['name'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
			</th>
			<td class="forminp forminp-<?php echo esc_attr( $value['type'] ); ?>">
				<?php include __DIR__ . '/views/exchange-rates.php'; ?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Exchange rates field.
	 *
	 * @param array $value Field arguments.
	 *
	 * @since 1.0.0
	 */
	public static function exchange_rates_field_v1( $value ) {
		$currencies = eac_get_currencies();
		unset( $currencies[ eac_base_currency() ] );
		$rates = get_option( 'eac_exchange_rates', array() );
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label
					for="<?php echo esc_attr( $value['name'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
			</th>
			<td class="forminp forminp-<?php echo esc_attr( $value['type'] ); ?>">
				<table class="eac-exchange-rates">
					<thead>
					<tr>
						<th class="currency"><?php esc_html_e( 'Currency', 'wp-ever-accounting' ); ?></th>
						<th class="rate"><?php esc_html_e( 'Rate', 'wp-ever-accounting' ); ?></th>
						<td class="actions" width="1%"></td>
					</tr>
					</thead>
					<tbody>
					<?php foreach ( $rates as $code => $rate ) : ?>
						<?php include __DIR__ . '/views/exchange-rate-row.php'; ?>
					<?php endforeach; ?>
					</tbody>
					<tfoot>
					<tr>
						<td colspan="3">
							<a href="#" class="button add" data-row="
								<?php
								ob_start();
								$rate = 1;
								$code = '';
								include __DIR__ . '/views/exchange-rate-row.php';
								echo esc_attr( ob_get_clean() );
								?>
								">
								<?php esc_html_e( 'Add Exchange Rate', 'wp-ever-accounting' ); ?>
							</a>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<?php
	}
}
