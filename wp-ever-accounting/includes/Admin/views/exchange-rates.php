<?php
/**
 * Admin view: Exchange rates.
 *
 * @since 1.0.0
 * @package EverAccounting\Admin
 * @var array $rates Exchange rates.
 */

defined( 'ABSPATH' ) || exit;
?>
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
		<?php include __DIR__ . '/exchange-rate.php'; ?>
	<?php endforeach; ?>
	</tbody>
	<tfoot>
	<tr>
		<td colspan="3">
			<a href="#" class="button add" data-row="
			<?php
			ob_start();
			$rate = '';
			$code = '';
			require __DIR__ . '/exchange-rate.php';
			echo esc_attr( ob_get_clean() );
			?>
			">
				<?php esc_html_e( 'Add Exchange Rate', 'wp-ever-accounting' ); ?>
			</a>
		</td>
	</tr>
</table>
