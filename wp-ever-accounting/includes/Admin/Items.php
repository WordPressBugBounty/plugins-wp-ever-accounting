<?php

namespace EverAccounting\Admin;

use EverAccounting\Models\Item;

defined( 'ABSPATH' ) || exit;

/**
 * Class Items
 *
 * @since 2.0.0
 * @package EverAccounting\Admin\Items
 */
class Items {

	/**
	 * Items constructor.
	 */
	public function __construct() {
		add_filter( 'eac_items_page_tabs', array( __CLASS__, 'register_tabs' ) );
		add_action( 'admin_post_eac_edit_item', array( __CLASS__, 'handle_edit' ) );
		add_action( 'eac_items_page_items_loaded', array( __CLASS__, 'page_loaded' ) );
		add_action( 'eac_items_page_items_content', array( __CLASS__, 'page_content' ) );
		add_action( 'eac_item_edit_sidebar_content', array( __CLASS__, 'item_notes' ) );
	}

	/**
	 * Register tab.
	 *
	 * @param array $tabs Tabs.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public static function register_tabs( $tabs ) {
		if ( current_user_can( 'eac_read_items' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
			$tabs['items'] = __( 'Items', 'wp-ever-accounting' );
		}

		return $tabs;
	}

	/**
	 * Handle actions.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public static function handle_edit() {
		check_admin_referer( 'eac_edit_item' );
		if ( ! current_user_can( 'eac_edit_items' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
			wp_die( esc_html__( 'You do not have permission to edit items.', 'wp-ever-accounting' ) );
		}

		$referer = wp_get_referer();
		$data    = array(
			'id'          => isset( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : 0,
			'type'        => isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '',
			'name'        => isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '',
			'description' => isset( $_POST['description'] ) ? sanitize_text_field( wp_unslash( $_POST['description'] ) ) : '',
			'unit'        => isset( $_POST['unit'] ) ? sanitize_text_field( wp_unslash( $_POST['unit'] ) ) : '',
			'price'       => isset( $_POST['price'] ) ? floatval( wp_unslash( $_POST['price'] ) ) : 0,
			'cost'        => isset( $_POST['cost'] ) ? floatval( wp_unslash( $_POST['cost'] ) ) : 0,
			'tax_ids'     => isset( $_POST['tax_ids'] ) ? array_map( 'absint', wp_unslash( $_POST['tax_ids'] ) ) : array(),
			'category_id' => isset( $_POST['category_id'] ) ? absint( wp_unslash( $_POST['category_id'] ) ) : 0,
		);

		$item = EAC()->items->insert( $data );
		if ( is_wp_error( $item ) ) {
			EAC()->flash->error( $item->get_error_message() );
		} else {
			EAC()->flash->success( __( 'Item saved successfully.', 'wp-ever-accounting' ) );
			$referer = add_query_arg( 'id', $item->id, $referer );
			$referer = remove_query_arg( array( 'add' ), $referer );
		}
		wp_safe_redirect( $referer );
		exit;
	}

	/**
	 * Handle page loaded.
	 *
	 * @param string $action Current action.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public static function page_loaded( $action ) {
		global $list_table;
		switch ( $action ) {
			case 'add':
				// Nothing to do here.
				break;

			case 'edit':
				$id = filter_input( INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT );
				if ( ! EAC()->items->get( $id ) ) {
					wp_die( esc_html__( 'You attempted to retrieve an item that does not exist. Perhaps it was deleted?', 'wp-ever-accounting' ) );
				}
				break;

			default:
				$screen     = get_current_screen();
				$list_table = new ListTables\Items();
				$list_table->prepare_items();
				$screen->add_option(
					'per_page',
					array(
						'label'   => __( 'Number of items per page:', 'wp-ever-accounting' ),
						'default' => 20,
						'option'  => 'eac_items_per_page',
					)
				);
				break;
		}
	}

	/**
	 * Handle page content.
	 *
	 * @param string $action Current action.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public static function page_content( $action ) {
		switch ( $action ) {
			case 'add':
			case 'edit':
				include __DIR__ . '/views/item-edit.php';
				break;
			default:
				include __DIR__ . '/views/item-list.php';
				break;
		}
	}

	/**
	 * Item notes.
	 *
	 * @param Item $item Item object.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function item_notes( $item ) {
		// if does not exist, return.
		if ( ! $item->exists() ) {
			return;
		}

		$notes = EAC()->notes->query(
			array(
				'parent_id'   => $item->id,
				'parent_type' => 'item',
				'orderby'     => 'date_created',
				'order'       => 'DESC',
				'limit'       => 20,
			)
		);
		?>
		<div class="eac-card">
			<div class="eac-card__header">
				<h3 class="eac-card__title"><?php esc_html_e( 'Notes', 'wp-ever-accounting' ); ?></h3>
			</div>
			<div class="eac-card__body">
				<div class="eac-form-field">
					<label for="eac-note"><?php esc_html_e( 'Add Note', 'wp-ever-accounting' ); ?></label>
					<textarea id="eac-note" cols="30" rows="2" placeholder="<?php esc_attr_e( 'Enter Note', 'wp-ever-accounting' ); ?>"></textarea>
				</div>
				<button id="eac-add-note" type="button" class="button tw-mb-[20px]" data-parent_id="<?php echo esc_attr( $item->id ); ?>" data-parent_type="item" data-nonce="<?php echo esc_attr( wp_create_nonce( 'eac_add_note' ) ); ?>">
					<?php esc_html_e( 'Add Note', 'wp-ever-accounting' ); ?>
				</button>

				<?php include __DIR__ . '/views/note-list.php'; ?>
			</div>
		</div>
		<?php
	}
}
