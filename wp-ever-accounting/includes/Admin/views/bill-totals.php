<?php
/**
 * Admin View: Bill Totals
 *
 * @since 2.0.0
 * @package EverAccounting
 *
 * @var Bill  $bill Bill object.
 * @var array $columns Columns.
 */

use EverAccounting\Models\Bill;

defined( 'ABSPATH' ) || exit;
?>

<tr>
	<td class="col-label" colspan="<?php echo count( $columns ) - 1; ?>"><?php esc_html_e( 'Discount', 'wp-ever-accounting' ); ?></td>
	<td class="col-amount">
		<div class="eac-input-group">
			<select name="discount_type" id="discount_type" class="addon">
				<?php
				foreach (
					array(
						'fixed'      => '($)',
						'percentage' => '(%)',
					) as $key => $label
				) :
					?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $bill->discount_type ); ?>>
						<?php echo esc_html( $label ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<input type="number" name="discount_value" id="discount_value" placeholder="0" style="text-align: right;width: auto;" value="<?php echo esc_attr( $bill->discount_value ); ?>"/>
		</div>
	</td>
</tr>

<tr>
	<td class="col-label" colspan="<?php echo count( $columns ) - 1; ?>"><?php esc_html_e( 'Subtotal', 'wp-ever-accounting' ); ?></td>
	<td class="col-amount"><?php echo esc_html( $bill->formatted_subtotal ); ?></td>
</tr>

<tr>
	<td class="col-label" colspan="<?php echo count( $columns ) - 1; ?>">
		<?php esc_html_e( 'Discount', 'wp-ever-accounting' ); ?>
	</td>
	<td class="col-amount">
		<?php echo esc_html( $bill->formatted_discount ); ?>
	</td>
</tr>

<?php if ( $bill->is_taxed() ) : ?>
	<?php if ( 'single' === get_option( 'eac_tax_total_display' ) ) : ?>
		<tr>
			<td class="col-label" colspan="<?php echo count( $columns ) - 1; ?>">
				<?php esc_html_e( 'Tax', 'wp-ever-accounting' ); ?>
			</td>
			<td class="col-amount">
				<?php echo esc_html( $bill->formatted_tax ); ?>
			</td>
		</tr>
	<?php else : ?>
		<?php foreach ( $bill->get_itemized_taxes() as $tax ) : ?>
			<tr>
				<td class="col-label" colspan="<?php echo count( $columns ) - 1; ?>">
					<?php echo esc_html( $tax->formatted_name ); ?>
				</td>
				<td class="col-amount">
					<?php echo esc_html( eac_format_amount( $tax->amount, $bill->currency ) ); ?>
				</td>
			</tr>
		<?php endforeach; ?>
	<?php endif; ?>
<?php endif; ?>

<tr>
	<td class="col-label" colspan="<?php echo count( $columns ) - 1; ?>">
		<?php esc_html_e( 'Total', 'wp-ever-accounting' ); ?>
	</td>
	<td class="col-amount">
		<?php echo esc_html( $bill->formatted_total ); ?>
	</td>
</tr>
