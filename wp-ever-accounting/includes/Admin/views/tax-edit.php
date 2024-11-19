<?php
/**
 * Admin View: Tax Edit
 *
 * @since 1.0.0
 * @package EverAccounting
 * @var $tax Tax Tax object.
 */

use EverAccounting\Models\Tax;

defined( 'ABSPATH' ) || exit;

$id  = filter_input( INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT );
$tax = Tax::make( $id );

?>
<h1 class="wp-heading-inline">
	<?php if ( $tax->exists() ) : ?>
		<?php esc_html_e( 'Edit Rate', 'wp-ever-accounting' ); ?>
		<a href="<?php echo esc_attr( admin_url( 'admin.php?page=eac-settings&tab=taxes&section=rates&action=add' ) ); ?>" class="button button-small">
			<?php esc_html_e( 'Add New', 'wp-ever-accounting' ); ?>
		</a>
	<?php else : ?>
		<?php esc_html_e( 'Add Rate', 'wp-ever-accounting' ); ?>
	<?php endif; ?>
	<a href="<?php echo esc_attr( remove_query_arg( array( 'action', 'id' ) ) ); ?>" title="<?php esc_attr_e( 'Go back', 'wp-ever-accounting' ); ?>">
		<span class="dashicons dashicons-undo"></span>
	</a>
</h1>

<form id="eac-edit-tax" name="tax" method="post" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>">
	<div class="eac-poststuff">
		<div class="column-1">

			<div class="eac-card">
				<div class="eac-card__header">
					<h2 class="eac-card__title"><?php esc_html_e( 'Tax Attributes', 'wp-ever-accounting' ); ?></h2>
				</div>
				<div class="eac-card__body grid--fields">
					<?php
					eac_form_field(
						array(
							'id'          => 'name',
							'label'       => __( 'Name', 'wp-ever-accounting' ),
							'placeholder' => __( 'Enter tax rate name', 'wp-ever-accounting' ),
							'value'       => $tax->name,
							'required'    => true,
						)
					);
					eac_form_field(
						array(
							'data_type'   => 'decimal',
							'id'          => 'rate',
							'label'       => __( 'Rate (%)', 'wp-ever-accounting' ),
							'placeholder' => __( 'Enter tax rate', 'wp-ever-accounting' ),
							'value'       => $tax->rate,
							'required'    => true,
							'type'        => 'number',
							'attr-step'   => 'any',
						)
					);

					eac_form_field(
						array(
							'id'       => 'compound',
							'label'    => __( 'Compound', 'wp-ever-accounting' ),
							'value'    => filter_var( $tax->compound, FILTER_VALIDATE_BOOLEAN ) ? 'yes' : 'no',
							'required' => true,
							'options'  => array(
								'yes' => __( 'Yes', 'wp-ever-accounting' ),
								'no'  => __( 'No', 'wp-ever-accounting' ),
							),
							'type'     => 'select',
						)
					);
					?>
				</div>
			</div>

			<?php
			/**
			 * Fires an action to inject custom content in the main column.
			 *
			 * @param Tax $tax The tax object being edited or created.
			 *
			 * @since 2.0.0
			 */
			do_action( 'eac_tax_edit_core_content', $tax );
			?>
		</div><!-- .column-1 -->

		<div class="column-2">
			<div id="eac-tax-actions" class="eac-card">
				<div class="eac-card__header">
					<h3 class="eac-card__title"><?php esc_html_e( 'Actions', 'wp-ever-accounting' ); ?></h3>
				</div>
				<div class="eac-card__footer">
					<?php if ( $tax->exists() ) : ?>
						<a class="del del_confirm" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'action', 'delete', $tax->get_edit_url() ), 'bulk-taxes' ) ); ?>"><?php esc_html_e( 'Delete', 'wp-ever-accounting' ); ?></a>
						<button class="button button-primary"><?php esc_html_e( 'Update Tax', 'wp-ever-accounting' ); ?></button>
					<?php else : ?>
						<button class="button button-primary button-block"><?php esc_html_e( 'Add Tax', 'wp-ever-accounting' ); ?></button>
					<?php endif; ?>
				</div>
			</div><!-- .eac-card -->

			<?php
			/**
			 * Fires an action to inject custom content in the side column.
			 *
			 * @param Tax $tax The tax object being edited or created.
			 *
			 * @since 2.0.0
			 */
			do_action( 'eac_tax_edit_sidebar_content', $tax );
			?>

		</div><!-- .column-2 -->

	</div><!-- .eac-poststuff -->
	<?php wp_nonce_field( 'eac_edit_tax' ); ?>
	<input type="hidden" name="action" value="eac_edit_tax"/>
	<input type="hidden" name="id" value="<?php echo esc_attr( $tax->id ); ?>"/>
</form>
