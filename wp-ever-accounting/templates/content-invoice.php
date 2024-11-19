<?php
/**
 * The Template for displaying an invoice.
 *
 * This template can be overridden by copying it to yourtheme/eac/content-invoice.php
 *
 * HOWEVER, on occasion EverAccounting will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://wpeveraccounting.com/docs/
 * @package EverAccounting\Templates
 * @version 1.0.0
 *
 * @var \EverAccounting\Models\Invoice $invoice The invoice object.
 */

defined( 'ABSPATH' ) || exit;

$business_logo  = get_option( 'eac_business_logo', get_site_icon_url( 55 ) );
$business_phone = get_option( 'eac_business_phone' );
$business_email = get_option( 'eac_business_email', get_option( 'admin_email' ) );
$business_name  = get_option( 'eac_business_name', get_bloginfo( 'name' ) );
$columns        = EAC()->invoices->get_columns();
if ( ! $invoice->is_taxed() ) {
	unset( $columns['tax'] );
}
?>
<div class="eac-document">
	<div class="eac-document__header">
		<?php if ( $business_logo && filter_var( $business_logo, FILTER_VALIDATE_URL ) ) : ?>
			<div class="eac-document__logo">
				<img src="<?php echo esc_url( $business_logo ); ?>" alt="<?php esc_attr_e( 'Logo', 'wp-ever-accounting' ); ?>"/>
			</div>
		<?php endif; ?>
		<div class="eac-document__info">
			<?php if ( ! empty( $business_name ) ) : ?>
				<h2><?php echo esc_html( $business_name ); ?></h2>
			<?php endif; ?>
			<?php if ( ! empty( $business_phone ) ) : ?>
				<p><?php echo esc_html( $business_phone ); ?></p>
			<?php endif; ?>
			<?php if ( ! empty( $business_email ) ) : ?>
				<p><?php echo esc_html( $business_email ); ?></p>
			<?php endif; ?>
			<p>
				<?php echo esc_html( site_url() ); ?>
			</p>
		</div>
		<div class="eac-document__title">
			<h1><?php esc_html_e( 'Invoice', 'wp-ever-accounting' ); ?></h1>
			<p>
				<strong><?php esc_html_e( 'Invoice:', 'wp-ever-accounting' ); ?></strong>
				<?php echo esc_html( $invoice->number ); ?>
			</p>
			<?php if ( $invoice->order_number ) : ?>
				<p>
					<strong><?php esc_html_e( 'Order:', 'wp-ever-accounting' ); ?></strong>
					<?php echo esc_html( $invoice->order_number ); ?>
				</p>
			<?php endif; ?>
			<?php if ( $invoice->issue_date ) : ?>
				<p>
					<strong><?php esc_html_e( 'Issue:', 'wp-ever-accounting' ); ?></strong>
					<?php echo esc_html( wp_date( eac_date_format(), strtotime( $invoice->issue_date ) ) ); ?>
				</p>
			<?php endif; ?>
			<?php if ( $invoice->due_date ) : ?>
				<p>
					<strong><?php esc_html_e( 'Due:', 'wp-ever-accounting' ); ?></strong>
					<?php echo esc_html( wp_date( eac_date_format(), strtotime( $invoice->due_date ) ) ); ?>
				</p>
			<?php endif; ?>
		</div>
	</div>
	<div class="eac-document__divider"></div>
	<div class="eac-document__billings">
		<div class="eac-document__billing">
			<h3><?php esc_html_e( 'From', 'wp-ever-accounting' ); ?></h3>
			<p>
				<?php
				$address = eac_get_formatted_address(
					array(
						'name'       => get_option( 'eac_business_name', get_bloginfo( 'name' ) ),
						'address'    => get_option( 'eac_business_address' ),
						'city'       => get_option( 'eac_business_city' ),
						'state'      => get_option( 'eac_business_state' ),
						'postcode'   => get_option( 'eac_business_postcode' ),
						'country'    => get_option( 'eac_business_country' ),
						'email'      => get_option( 'eac_business_email' ),
						'phone'      => get_option( 'eac_business_phone' ),
						'tax_number' => get_option( 'eac_business_tax_number' ),
					)
				);

				echo wp_kses_post( $address );
				?>
			</p>
		</div>
		<div class="eac-document__billing">
			<h3><?php esc_html_e( 'To', 'wp-ever-accounting' ); ?></h3>
			<p>
				<?php
				$address = eac_get_formatted_address(
					array(
						'name'       => $invoice->contact_name,
						'company'    => $invoice->contact_company,
						'address'    => $invoice->contact_address,
						'city'       => $invoice->contact_city,
						'state'      => $invoice->contact_state,
						'postcode'   => $invoice->contact_postcode,
						'country'    => $invoice->contact_country,
						'email'      => $invoice->contact_email,
						'phone'      => $invoice->contact_phone,
						'tax_number' => $invoice->contact_tax_number,
					)
				);
				echo wp_kses_post( $address );
				?>
			</p>
		</div>
	</div>
	<div class="eac-document__divider"></div>
	<div class="eac-document__items">
		<table>
			<thead>
			<tr>
				<?php foreach ( $columns as $column_key => $column ) : ?>
					<th class="col-<?php echo esc_attr( $column_key ); ?>">
						<?php echo esc_html( $column ); ?>
					</th>
				<?php endforeach; ?>
			</tr>
			</thead>
			<tbody>
			<?php if ( $invoice->items ) : ?>
				<?php foreach ( $invoice->items as $item ) : ?>
					<tr>
						<?php foreach ( $columns as $column_key => $column ) : ?>
							<td class="col-<?php echo esc_attr( $column_key ); ?>">
								<?php
								switch ( $column_key ) {
									case 'item':
										echo esc_html( $item->name );
										if ( $item->description ) {
											echo '<span class="small">' . esc_html( $item->description ) . '</span>';
										}
										break;
									case 'quantity':
										echo esc_html( $item->quantity );
										if ( $item->unit ) {
											echo ' x ' . esc_html( $item->unit );
										}
										break;
									case 'price':
										echo esc_html( eac_format_amount( $item->price, $invoice->currency ) );
										break;
									case 'tax':
										echo esc_html( eac_format_amount( $item->tax, $invoice->currency ) );
										break;
									case 'subtotal':
										echo esc_html( eac_format_amount( $item->subtotal, $invoice->currency ) );
										break;
								}
								?>
							</td>
						<?php endforeach; ?>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr>
					<td colspan="<?php echo esc_attr( count( $columns ) ); ?>">
						<?php esc_html_e( 'No items found.', 'wp-ever-accounting' ); ?>
					</td>
				</tr>
			<?php endif; ?>
			</tbody>

			<tfoot>
			<tr>
				<td colspan="<?php echo esc_attr( count( $columns ) - 1 ); ?>" class="col-label">
					<?php esc_html_e( 'Subtotal', 'wp-ever-accounting' ); ?>
				</td>
				<td class="col-amount">
					<?php echo esc_html( eac_format_amount( $invoice->subtotal, $invoice->currency ) ); ?>
				</td>
			</tr>
			<?php if ( $invoice->discount > 0 ) : ?>
				<tr>
					<td colspan="<?php echo esc_attr( count( $columns ) - 1 ); ?>" class="col-label">
						<?php esc_html_e( 'Discount', 'wp-ever-accounting' ); ?>
					</td>
					<td class="col-amount">
						<?php echo esc_html( eac_format_amount( $invoice->discount, $invoice->currency ) ); ?>
					</td>
				</tr>
			<?php endif; ?>
			<?php if ( $invoice->is_taxed() ) : ?>
				<?php if ( 'single' === get_option( 'eac_tax_total_display' ) ) : ?>
					<tr>
						<td colspan="<?php echo esc_attr( count( $columns ) - 1 ); ?>" class="col-label">
							<?php esc_html_e( 'Tax', 'wp-ever-accounting' ); ?>
						</td>
						<td class="col-amount">
							<?php echo esc_html( eac_format_amount( $invoice->tax, $invoice->currency ) ); ?>
						</td>
					</tr>
				<?php else : ?>
					<?php foreach ( $invoice->get_itemized_taxes() as $tax ) : ?>
						<tr>
							<td colspan="<?php echo esc_attr( count( $columns ) - 1 ); ?>" class="col-label">
								<?php echo esc_html( $tax->formatted_name ); ?>
							</td>
							<td class="col-amount">
								<?php echo esc_html( eac_format_amount( $tax->amount, $invoice->currency ) ); ?>
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
					<?php echo esc_html( $invoice->formatted_total ); ?>
				</td>
			</tr>
			<?php if ( $invoice->get_due_amount() > 0 ) : ?>
				<tr>
					<td class="col-label" colspan="<?php echo count( $columns ) - 1; ?>">
						<?php esc_html_e( 'Due', 'wp-ever-accounting' ); ?>
					</td>
					<td class="col-amount col-amount--due">
						<?php echo esc_html( eac_format_amount( $invoice->get_due_amount(), $invoice->currency ) ); ?>
					</td>
				</tr>
			<?php endif; ?>
			</tfoot>
		</table>
	</div>

	<?php if ( $invoice->note ) : ?>
		<div class="eac-document__note">
			<h3><?php esc_html_e( 'Notes', 'wp-ever-accounting' ); ?></h3>
			<?php echo wp_kses_post( wpautop( $invoice->note ) ); ?>
		</div>
	<?php endif; ?>
	<?php if ( $invoice->payments ) : ?>
		<div class="eac-document__divider"></div>
		<div class="eac-document__payments">
			<h3><?php esc_html_e( 'Payments', 'wp-ever-accounting' ); ?></h3>
			<table>
				<thead>
				<tr>
					<th><?php esc_html_e( 'Payment #', 'wp-ever-accounting' ); ?></th>
					<th><?php esc_html_e( 'Date', 'wp-ever-accounting' ); ?></th>
					<th><?php esc_html_e( 'Method', 'wp-ever-accounting' ); ?></th>
					<th><?php esc_html_e( 'Amount', 'wp-ever-accounting' ); ?></th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ( $invoice->payments as $payment ) : ?>
					<tr>
						<td><?php echo esc_html( $payment->number ); ?></td>
						<td><?php echo esc_html( $payment->payment_date ? wp_date( get_option( 'date_format' ), strtotime( $payment->payment_date ) ) : 'N/A' ); ?></td>
						<td><?php echo esc_html( $payment->payment_method_label ? $payment->payment_method_label : 'N/A' ); ?></td>
						<td><?php echo esc_html( eac_format_amount( eac_convert_currency( $payment->amount, $payment->currency, $invoice->currency ), $invoice->currency ) ); ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php endif; ?>
	<?php if ( ! empty( $invoice->terms ) ) : ?>
		<div class="eac-document__footer">
			<?php echo wp_kses_post( wpautop( $invoice->terms ) ); ?>
		</div>
	<?php endif; ?>
</div>
