<?php
/**
 * Admin View: Item Edit
 *
 * @package EverAccounting
 * @var string $action Current action.
 */

use EverAccounting\Models\Item;

defined( 'ABSPATH' ) || exit;

$id   = filter_input( INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT );
$item = Item::make( $id );
?>

<h1 class="wp-heading-inline">
	<?php if ( $item->exists() ) : ?>
		<?php esc_html_e( 'Edit Item', 'wp-ever-accounting' ); ?>
		<a href="<?php echo esc_attr( admin_url( 'admin.php?page=eac-items&action=add' ) ); ?>" class="button button-small">
			<?php esc_html_e( 'Add New', 'wp-ever-accounting' ); ?>
		</a>
	<?php else : ?>
		<?php esc_html_e( 'Add Item', 'wp-ever-accounting' ); ?>
	<?php endif; ?>
	<a href="<?php echo esc_attr( remove_query_arg( array( 'action', 'id' ) ) ); ?>" title="<?php esc_attr_e( 'Go back', 'wp-ever-accounting' ); ?>">
		<span class="dashicons dashicons-undo"></span>
	</a>
</h1>

<form id="eac-edit-item" name="item" method="post" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>">
	<div class="eac-poststuff">

		<div class="column-1">
			<div id="eac-item-data" class="eac-card">
				<div class="eac-card__header">
					<h3 class="eac-card__title"><?php esc_html_e( 'Item Attributes', 'wp-ever-accounting' ); ?></h3>
				</div>
				<div class="eac-card__body grid--fields">
					<?php
					eac_form_field(
						array(
							'label'       => __( 'Name', 'wp-ever-accounting' ),
							'type'        => 'text',
							'name'        => 'name',
							'value'       => $item->name,
							'placeholder' => __( 'Laptop', 'wp-ever-accounting' ),
							'required'    => true,
						)
					);
					eac_form_field(
						array(
							'type'     => 'select',
							'name'     => 'type',
							'required' => true,
							'default'  => 'product',
							'label'    => __( 'Type', 'wp-ever-accounting' ),
							'value'    => $item->type,
							'options'  => EAC()->items->get_types(),
							'tooltip'  => __( 'Select the item type: Standard for regular products eligible for discounts, or Fee for extra charges that do not support discounts.', 'wp-ever-accounting' ),
						)
					);
					eac_form_field(
						array(
							'type'          => 'text',
							'name'          => 'price',
							'label'         => __( 'Price', 'wp-ever-accounting' ),
							'value'         => $item->price,
							'placeholder'   => __( '10.00', 'wp-ever-accounting' ),
							/* translators: %s: currency symbol */
							'tooltip'       => sprintf( __( 'Enter the price of the item in %s.', 'wp-ever-accounting' ), eac_base_currency() ),
							'class'         => 'eac_amount',
							'data-currency' => eac_base_currency(),
						)
					);
					eac_form_field(
						array(
							'type'          => 'text',
							'name'          => 'cost',
							'label'         => __( 'Cost', 'wp-ever-accounting' ),
							'value'         => $item->cost,
							'placeholder'   => __( '8.00', 'wp-ever-accounting' ),
							/* translators: %s: currency symbol */
							'tooltip'       => sprintf( __( 'Enter the cost of the item in %s.', 'wp-ever-accounting' ), eac_base_currency() ),
							'class'         => 'eac_amount',
							'data-currency' => eac_base_currency(),
						)
					);
					eac_form_field(
						array(
							'type'             => 'select',
							'name'             => 'category_id',
							'label'            => __( 'Category', 'wp-ever-accounting' ),
							'value'            => $item->category_id,
							'options'          => array( $item->category ),
							'option_label'     => 'formatted_name',
							'option_value'     => 'id',
							'data-placeholder' => __( 'Select item category', 'wp-ever-accounting' ),
							'class'            => 'eac_select2',
							'data-action'      => 'eac_json_search',
							'data-type'        => 'category',
							'data-subtype'     => 'item',
							'suffix'           => sprintf(
								'<a class="addon" href="%s" target="_blank" title="%s"><span class="dashicons dashicons-plus"></span></a>',
								esc_url( 'admin.php?page=eac-settings&tab=categories&action=add' ),
								__( 'Add Category', 'wp-ever-accounting' )
							),
						)
					);
					eac_form_field(
						array(
							'type'        => 'select',
							'name'        => 'unit',
							'label'       => __( 'Unit', 'wp-ever-accounting' ),
							'value'       => $item->unit,
							'options'     => EAC()->items->get_units(),
							'placeholder' => __( 'Select unit', 'wp-ever-accounting' ),
							'class'       => 'eac-select2',
						)
					);
					// tax_ids.
					eac_form_field(
						array(
							'type'         => 'select',
							'multiple'     => true,
							'name'         => 'tax_ids',
							'label'        => __( 'Taxes', 'wp-ever-accounting' ),
							'value'        => $item->tax_ids,
							'options'      => $item->taxes,
							'option_label' => 'formatted_name',
							'option_value' => 'id',
							'class'        => 'eac_select2',
							'data-action'  => 'eac_json_search',
							'data-type'    => 'tax',
							'tooltip'      => __( 'The selected tax rates will be applied to this item.', 'wp-ever-accounting' ),
							'suffix'       => sprintf(
								'<a class="addon" href="%s" target="_blank" title="%s"><span class="dashicons dashicons-plus"></span></a>',
								esc_url( 'admin.php?page=eac-settings&tab=taxes&section=rates&action=add' ),
								__( 'Add Tax', 'wp-ever-accounting' )
							),
						)
					);

					eac_form_field(
						array(
							'type'          => 'textarea',
							'name'          => 'description',
							'label'         => __( 'Description', 'wp-ever-accounting' ),
							'value'         => $item->description,
							'wrapper_class' => 'is--full',
						)
					);
					?>
				</div>
			</div><!-- .eac-card -->

			<?php
			/**
			 * Fires action to inject custom content in the main column.
			 *
			 * @param Item $item Customer object.
			 *
			 * @since 1.0.0
			 */
			do_action( 'eac_item_edit_core_content', $item );
			?>
		</div><!-- .column-1 -->

		<div class="column-2">
			<div id="eac-item-actions" class="eac-card">
				<div class="eac-card__header">
					<h3 class="eac-card__title"><?php esc_html_e( 'Actions', 'wp-ever-accounting' ); ?></h3>
				</div>
				<?php if ( has_action( 'eac_item_edit_misc_actions' ) ) : ?>
					<div class="eac-card__body">
						<?php
						/**
						 * Fires action to inject custom fields.
						 *
						 * @param Item $item Item object.
						 *
						 * @since 2.0.0
						 */
						do_action( 'eac_item_edit_misc_actions', $item );
						?>
					</div>
				<?php endif; ?>
				<div class="eac-card__footer">
					<?php if ( $item->exists() ) : ?>
						<a class="del del_confirm" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'action', 'delete', $item->get_edit_url() ), 'bulk-items' ) ); ?>"><?php esc_html_e( 'Delete', 'wp-ever-accounting' ); ?></a>
						<button class="button button-primary"><?php esc_html_e( 'Update', 'wp-ever-accounting' ); ?></button>
					<?php else : ?>
						<button class="button button-primary button-block"><?php esc_html_e( 'Save', 'wp-ever-accounting' ); ?></button>
					<?php endif; ?>
				</div>
			</div><!-- .eac-card -->

			<?php
			/**
			 * Fires action to inject custom content in the side column.
			 *
			 * @param Item $item Customer object.
			 *
			 * @since 1.0.0
			 */
			do_action( 'eac_item_edit_sidebar_content', $item );
			?>

		</div><!-- .column-2 -->

	</div><!-- .eac-poststuff -->
	<?php wp_nonce_field( 'eac_edit_item' ); ?>
	<input type="hidden" name="action" value="eac_edit_item"/>
	<input type="hidden" name="id" value="<?php echo esc_attr( $item->id ); ?>"/>
</form>
