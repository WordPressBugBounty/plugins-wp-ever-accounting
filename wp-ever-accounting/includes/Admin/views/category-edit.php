<?php
/**
 * Admin View: Category Edit
 *
 * @since 1.0.0
 * @package EverAccounting
 * @var $category Category Category object.
 */

use EverAccounting\Models\Category;

defined( 'ABSPATH' ) || exit;

$id       = filter_input( INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT );
$category = Category::make( $id );

?>
<h1 class="wp-heading-inline">
	<?php if ( $category->exists() ) : ?>
		<?php esc_html_e( 'Edit Category', 'wp-ever-accounting' ); ?>
		<a href="<?php echo esc_attr( admin_url( 'admin.php?page=eac-settings&tab=categories&action=add' ) ); ?>" class="button button-small">
			<?php esc_html_e( 'Add New', 'wp-ever-accounting' ); ?>
		</a>
	<?php else : ?>
		<?php esc_html_e( 'Add Category', 'wp-ever-accounting' ); ?>
	<?php endif; ?>
	<a href="<?php echo esc_attr( remove_query_arg( array( 'action', 'id' ) ) ); ?>" title="<?php esc_attr_e( 'Go back', 'wp-ever-accounting' ); ?>">
		<span class="dashicons dashicons-undo"></span>
	</a>
</h1>

<form id="eac-edit-category" name="category" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<div class="eac-poststuff">
		<div class="column-1">

			<div class="eac-card">
				<div class="eac-card__header">
					<h2 class="eac-card__title"><?php esc_html_e( 'Category Attributes', 'wp-ever-accounting' ); ?></h2>
				</div>

				<div class="eac-card__body grid--fields">
					<?php
					eac_form_field(
						array(
							'id'          => 'name',
							'label'       => __( 'Name', 'wp-ever-accounting' ),
							'placeholder' => __( 'Enter category name', 'wp-ever-accounting' ),
							'value'       => $category->name,
							'required'    => true,
						)
					);
					eac_form_field(
						array(
							'id'          => 'type',
							'type'        => 'select',
							'label'       => __( 'Type', 'wp-ever-accounting' ),
							'placeholder' => __( 'Select category type', 'wp-ever-accounting' ),
							'value'       => $category->type,
							'default'     => isset( $_GET['type'] ) ? sanitize_text_field( wp_unslash( $_GET['type'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verification is not required here.
							'options'     => EAC()->categories->get_types(),
							'required'    => true,
						)
					);
					eac_form_field(
						array(
							'id'            => 'description',
							'label'         => __( 'Description', 'wp-ever-accounting' ),
							'placeholder'   => __( 'Enter category description', 'wp-ever-accounting' ),
							'value'         => $category->description,
							'type'          => 'textarea',
							'wrapper_class' => 'is--full',
						)
					);
					?>
				</div>
			</div>

			<?php
			/**
			 * Fires an action to inject custom content in the main column.
			 *
			 * @param Category $category The category object being edited or created.
			 *
			 * @since 2.0.0
			 */
			do_action( 'eac_category_edit_core_content', $category );
			?>
		</div><!-- .column-1 -->

		<div class="column-2">
			<div id="eac-category-actions" class="eac-card">
				<div class="eac-card__header">
					<h3 class="eac-card__title"><?php esc_html_e( 'Actions', 'wp-ever-accounting' ); ?></h3>
				</div>
				<div class="eac-card__footer">
					<?php if ( $category->exists() ) : ?>
						<a class="del del_confirm" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'action', 'delete', $category->get_edit_url() ), 'bulk-categories' ) ); ?>"><?php esc_html_e( 'Delete', 'wp-ever-accounting' ); ?></a>
						<button class="button button-primary"><?php esc_html_e( 'Update Category', 'wp-ever-accounting' ); ?></button>
					<?php else : ?>
						<button class="button button-primary button-block"><?php esc_html_e( 'Add Category', 'wp-ever-accounting' ); ?></button>
					<?php endif; ?>
				</div>
			</div><!-- .eac-card -->

			<?php
			/**
			 * Fires an action to inject custom content in the side column.
			 *
			 * @param Category $category The category object being edited or created.
			 *
			 * @since 2.0.0
			 */
			do_action( 'eac_category_edit_sidebar_content', $category );
			?>

		</div><!-- .column-2 -->

	</div><!-- .eac-poststuff -->
	<?php wp_nonce_field( 'eac_edit_category' ); ?>
	<input type="hidden" name="action" value="eac_edit_category"/>
	<input type="hidden" name="id" value="<?php echo esc_attr( $category->id ); ?>"/>
</form>
