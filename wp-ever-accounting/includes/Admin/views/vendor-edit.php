<?php
/**
 * Edit vendor view.
 *
 * @package EverAccounting
 * @var $item \EverAccounting\Models\Item
 */

use EverAccounting\Models\Vendor;

defined( 'ABSPATH' ) || exit;

$id     = filter_input( INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT );
$vendor = Vendor::make( $id );
?>

<h1 class="wp-heading-inline">
	<?php if ( $vendor->exists() ) : ?>
		<?php esc_html_e( 'Edit Vendor', 'wp-ever-accounting' ); ?>
		<a href="<?php echo esc_attr( admin_url( 'admin.php?page=eac-purchases&tab=vendors&action=add' ) ); ?>" class="button button-small">
			<?php esc_html_e( 'Add New', 'wp-ever-accounting' ); ?>
		</a>
	<?php else : ?>
		<?php esc_html_e( 'Add Vendor', 'wp-ever-accounting' ); ?>
	<?php endif; ?>
	<a href="<?php echo esc_attr( remove_query_arg( array( 'action', 'id' ) ) ); ?>" title="<?php esc_attr_e( 'Go back', 'wp-ever-accounting' ); ?>">
		<span class="dashicons dashicons-undo"></span>
	</a>
</h1>

<form id="eac-vendor-form" name="vendor" method="post" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>">
	<div class="eac-poststuff">
		<div class="column-1">

			<!--Vendor basic details-->
			<div class="eac-card">
				<div class="eac-card__header">
					<h2 class="eac-card__title"><?php esc_html_e( 'Basic Details', 'wp-ever-accounting' ); ?></h2>
				</div>
				<div class="eac-card__body grid--fields">
					<?php
					eac_form_field(
						array(
							'id'          => 'name',
							'label'       => __( 'Name', 'wp-ever-accounting' ),
							'placeholder' => __( 'John Doe', 'wp-ever-accounting' ),
							'value'       => $vendor->name,
							'required'    => true,
						)
					);
					eac_form_field(
						array(
							'id'           => 'currency',
							'type'         => 'select',
							'label'        => __( 'Currency Code', 'wp-ever-accounting' ),
							'value'        => $vendor->currency,
							'default'      => eac_base_currency(),
							'required'     => true,
							'class'        => 'eac_select2',
							'options'      => eac_get_currencies(),
							'option_value' => 'code',
							'option_label' => 'formatted_name',
						)
					);
					eac_form_field(
						array(
							'id'          => 'email',
							'type'        => 'email',
							'label'       => __( 'Email', 'wp-ever-accounting' ),
							'placeholder' => __( 'john@company.com', 'wp-ever-accounting' ),
							'value'       => $vendor->email,
						)
					);
					eac_form_field(
						array(
							'id'          => 'phone',
							'label'       => __( 'Phone', 'wp-ever-accounting' ),
							'placeholder' => __( '+1 123 456 7890', 'wp-ever-accounting' ),
							'value'       => $vendor->phone,
						)
					);
					?>
				</div>
			</div>

			<!--Vendor Business details-->
			<div class="eac-card">
				<div class="eac-card__header">
					<h2 class="eac-card__title"><?php esc_html_e( 'Business Details', 'wp-ever-accounting' ); ?></h2>
				</div>
				<div class="eac-card__body grid--fields">
					<?php
					eac_form_field(
						array(
							'id'          => 'company',
							'label'       => __( 'Company', 'wp-ever-accounting' ),
							'placeholder' => __( 'XYZ Inc.', 'wp-ever-accounting' ),
							'value'       => $vendor->company,
						)
					);
					eac_form_field(
						array(
							'id'          => 'website',
							'type'        => 'url',
							'label'       => __( 'Website', 'wp-ever-accounting' ),
							'placeholder' => __( 'https://example.com', 'wp-ever-accounting' ),
							'value'       => $vendor->website,
						)
					);
					eac_form_field(
						array(
							'id'          => 'tax_number',
							'label'       => __( 'Tax Number', 'wp-ever-accounting' ),
							'placeholder' => __( '123456789', 'wp-ever-accounting' ),
							'value'       => $vendor->tax_number,
						)
					);
					?>
				</div>
			</div>

			<!--Vendor Address details-->
			<div class="eac-card">
				<div class="eac-card__header">
					<h2 class="eac-card__title"><?php esc_html_e( 'Address Details', 'wp-ever-accounting' ); ?></h2>
				</div>
				<div class="eac-card__body grid--fields">
					<?php
					eac_form_field(
						array(
							'id'          => 'address',
							'label'       => __( 'Address', 'wp-ever-accounting' ),
							'placeholder' => __( '123 Main St', 'wp-ever-accounting' ),
							'value'       => $vendor->address,
						)
					);
					eac_form_field(
						array(
							'id'          => 'city',
							'label'       => __( 'City', 'wp-ever-accounting' ),
							'placeholder' => __( 'New York', 'wp-ever-accounting' ),
							'value'       => $vendor->city,
						)
					);
					eac_form_field(
						array(
							'id'          => 'state',
							'label'       => __( 'State', 'wp-ever-accounting' ),
							'placeholder' => __( 'NY', 'wp-ever-accounting' ),
							'value'       => $vendor->state,
						)
					);
					eac_form_field(
						array(
							'id'          => 'postcode',
							'label'       => __( 'Postal Code', 'wp-ever-accounting' ),
							'placeholder' => __( '10001', 'wp-ever-accounting' ),
							'value'       => $vendor->postcode,
						)
					);
					eac_form_field(
						array(
							'type'        => 'select',
							'id'          => 'country',
							'label'       => __( 'Country', 'wp-ever-accounting' ),
							'options'     => \EverAccounting\Utilities\I18nUtil::get_countries(),
							'value'       => $vendor->country,
							'class'       => 'eac-select2',
							'placeholder' => __( 'Select Country', 'wp-ever-accounting' ),
						)
					);
					?>
				</div>
			</div>

			<?php
			/**
			 * Fires action to inject custom content in the main column.
			 *
			 * @param Vendor $vendor Vendor object.
			 *
			 * @since 1.0.0
			 */
			do_action( 'eac_vendor_edit_core_content', $vendor );
			?>
		</div><!-- .column-1 -->

		<div class="column-2">
			<div class="eac-card">
				<div class="eac-card__header">
					<h2 class="eac-card__title"><?php esc_html_e( 'Actions', 'wp-ever-accounting' ); ?></h2>
					<?php if ( $vendor->exists() ) : ?>
						<a href="<?php echo esc_url( $vendor->get_view_url() ); ?>">
							<?php esc_html_e( 'View', 'wp-ever-accounting' ); ?>
						</a>
					<?php endif; ?>
				</div>
				<?php if ( has_action( 'eac_vendor_misc_actions' ) ) : ?>
					<div class="eac-card__body">
						<?php
						/**
						 * Fires to add custom actions.
						 *
						 * @param Vendor $vendor Vendor object.
						 *
						 * @since 2.0.0
						 */
						do_action( 'eac_vendor_misc_actions', $vendor );
						?>
					</div>
				<?php endif; ?>
				<div class="eac-card__footer">
					<?php if ( $vendor->exists() ) : ?>
						<a class="eac_confirm_delete del" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'action', 'delete', admin_url( 'admin.php?page=eac-purchases&tab=vendors&id=' . $vendor->id ) ), 'bulk-vendors' ) ); ?>"><?php esc_html_e( 'Delete', 'wp-ever-accounting' ); ?></a>
						<button class="button button-primary"><?php esc_html_e( 'Update Vendor', 'wp-ever-accounting' ); ?></button>
					<?php else : ?>
						<button class="button button-primary tw-w-[100%]"><?php esc_html_e( 'Add Vendor', 'wp-ever-accounting' ); ?></button>
					<?php endif; ?>
				</div>
			</div>

			<?php
			/**
			 * Fires action to inject custom content in the side column.
			 *
			 * @param Vendor $vendor Vendor object.
			 *
			 * @since 1.0.0
			 */
			do_action( 'eac_vendor_edit_sidebar_content', $vendor );
			?>

		</div><!-- .column-2 -->

	</div><!-- .eac-poststuff -->

	<?php wp_nonce_field( 'eac_edit_vendor' ); ?>
	<input type="hidden" name="action" value="eac_edit_vendor"/>
	<input type="hidden" name="id" value="<?php echo esc_attr( $vendor->id ); ?>"/>
</form>

