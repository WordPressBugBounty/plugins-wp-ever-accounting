<?php

namespace EverAccounting\Admin;

use EverAccounting\Models\Category;

defined( 'ABSPATH' ) || exit;


/**
 * Categories class.
 *
 * @since 3.0.0
 * @package EverAccounting\Admin
 */
class Categories {

	/**
	 * Categories constructor.
	 */
	public function __construct() {
		add_filter( 'eac_settings_page_tabs', array( __CLASS__, 'register_tabs' ) );
		add_action( 'admin_post_eac_edit_category', array( __CLASS__, 'handle_edit' ) );
		add_action( 'eac_settings_page_categories_content', array( __CLASS__, 'render_content' ) );
	}

	/**
	 * Register tab.
	 *
	 * @param array $tabs Tabs.
	 *
	 * @since 3.0.0
	 * @return array
	 */
	public static function register_tabs( $tabs ) {
		$tabs['categories'] = __( 'Categories', 'wp-ever-accounting' );

		return $tabs;
	}

	/**
	 * Edit category.
	 *
	 * @since 3.0.0
	 * @return void
	 */
	public static function handle_edit() {
		check_admin_referer( 'eac_edit_category' );
		$referer = wp_get_referer();
		$data    = array(
			'id'          => isset( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : 0,
			'name'        => isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '',
			'type'        => isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '',
			'description' => isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '',
		);

		$item = EAC()->categories->insert( $data );
		if ( is_wp_error( $item ) ) {
			EAC()->flash->error( $item->get_error_message() );
		} else {
			EAC()->flash->success( __( 'Category saved successfully.', 'wp-ever-accounting' ) );
			$referer = add_query_arg( 'id', $item->id, $referer );
			$referer = remove_query_arg( array( 'add' ), $referer );
		}

		wp_safe_redirect( $referer );
		exit;
	}

	/**
	 * Handle actions.
	 *
	 * @since 3.0.0
	 * @return void
	 */
	public static function handle_actions() {
		if ( isset( $_POST['action'] ) && 'eac_edit_category' === $_POST['action'] && check_admin_referer( 'eac_edit_category' ) && current_user_can( 'eac_manage_category' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
			$referer = wp_get_referer();
			$data    = array(
				'id'          => isset( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : 0,
				'name'        => isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '',
				'type'        => isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '',
				'description' => isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '',
			);

			$item = EAC()->categories->insert( $data );
			if ( is_wp_error( $item ) ) {
				EAC()->flash->error( $item->get_error_message() );
			} else {
				EAC()->flash->success( __( 'Category saved successfully.', 'wp-ever-accounting' ) );
				$referer = add_query_arg( 'id', $item->id, $referer );
				$referer = remove_query_arg( array( 'add' ), $referer );
			}

			wp_safe_redirect( $referer );
			exit;
		}
	}

	/**
	 * Render content.
	 *
	 * @since 3.0.0
	 * @return void
	 */
	public static function render_content() {
		$action = filter_input( INPUT_GET, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$id     = filter_input( INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT );

		switch ( $action ) {
			case 'add':
			case 'edit':
				include __DIR__ . '/views/category-edit.php';
				break;
			default:
				global $list_table;
				$list_table = new ListTables\Categories();
				$list_table->prepare_items();
				include __DIR__ . '/views/category-list.php';
				break;
		}
	}
}
