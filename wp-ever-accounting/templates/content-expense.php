<?php
/**
 * The Template for displaying expense.
 *
 * This template can be overridden by copying it to yourtheme/eac/content-expense.php
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
 * @var \EverAccounting\Models\Payment $expense Payment object.
 */

defined( 'ABSPATH' ) || exit;
$business_logo  = get_option( 'eac_business_logo', get_site_icon_url( 55 ) );
$business_phone = get_option( 'eac_business_phone' );
$business_email = get_option( 'eac_business_email', get_option( 'admin_email' ) );
$business_name  = get_option( 'eac_business_name', get_bloginfo( 'name' ) );
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
			<h1><?php esc_html_e( 'Expense Receipt', 'wp-ever-accounting' ); ?></h1>
			<p>
				#<?php echo esc_html( $expense->number ); ?>
			</p>
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
				$vendor = $expense->vendor;
				if ( $vendor ) {
					$address = eac_get_formatted_address(
						array(
							'name'       => $vendor->name,
							'company'    => $vendor->company,
							'address'    => $vendor->address,
							'city'       => $vendor->city,
							'state'      => $vendor->state,
							'postcode'   => $vendor->postcode,
							'country'    => $vendor->country,
							'tax_number' => $vendor->tax_number,
						)
					);
					echo wp_kses_post( $address );
				} else {
					echo esc_html( 'N/A' );
				}
				?>
			</p>
		</div>
	</div>
	<div class="eac-document__divider"></div>
	<div class="eac-document__summary">
		<h3><?php esc_html_e( 'Payment Summary', 'wp-ever-accounting' ); ?></h3>
		<table>
			<tbody>
			<tr>
				<th scope="row"><?php esc_html_e( 'Amount', 'wp-ever-accounting' ); ?></th>
				<td><?php echo esc_html( $expense->formatted_amount ); ?></td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Date', 'wp-ever-accounting' ); ?></th>
				<td><?php echo esc_html( $expense->payment_date ? wp_date( eac_date_format(), strtotime( $expense->payment_date ) ) : 'N/A' ); ?></td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Method', 'wp-ever-accounting' ); ?></th>
				<td><?php echo esc_html( $expense->payment_method ? $expense->payment_method_label : 'N/A' ); ?></td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Reference', 'wp-ever-accounting' ); ?></th>
				<td><?php echo esc_html( $expense->reference ? $expense->reference : 'N/A' ); ?></td>
			</tr>
			<?php if ( $expense->bill_id && $expense->bill ) : ?>
				<tr>
					<th scope="row"><?php esc_html_e( 'Bill', 'wp-ever-accounting' ); ?></th>
					<td>
						<a href="<?php echo esc_url( is_admin() ? $expense->bill->get_view_url() : $expense->bill->get_public_url() ); ?>">
							<?php echo esc_html( $expense->bill->number ); ?>
						</a>
					</td>
				</tr>
			<?php endif; ?>
			</tbody>
		</table>
	</div>

	<?php if ( $expense->notes ) : ?>
		<div class="eac-document__notes">
			<h3><?php esc_html_e( 'Notes', 'wp-ever-accounting' ); ?></h3>
			<p><?php echo wp_kses_post( $expense->notes ); ?></p>
		</div>
	<?php endif; ?>
</div>
