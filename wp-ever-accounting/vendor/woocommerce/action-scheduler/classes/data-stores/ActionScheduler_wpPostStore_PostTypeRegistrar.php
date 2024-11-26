<?php

/**
 * Class ActionScheduler_wpPostStore_PostTypeRegistrar
 *
 * @codeCoverageIgnore
 */
class ActionScheduler_wpPostStore_PostTypeRegistrar {
	/**
	 * Registrar.
	 */
	public function register() {
		register_post_type( ActionScheduler_wpPostStore::POST_TYPE, $this->post_type_args() );
	}

	/**
	 * Build the args array for the post type definition
	 *
	 * @return array
	 */
	protected function post_type_args() {
		$args = array(
			'label'        => __( 'Scheduled Actions', 'wp-ever-accounting' ),
			'description'  => __( 'Scheduled actions are hooks triggered on a certain date and time.', 'wp-ever-accounting' ),
			'public'       => false,
			'map_meta_cap' => true,
			'hierarchical' => false,
			'supports'     => array( 'title', 'editor', 'comments' ),
			'rewrite'      => false,
			'query_var'    => false,
			'can_export'   => true,
			'ep_mask'      => EP_NONE,
			'labels'       => array(
				'name'               => __( 'Scheduled Actions', 'wp-ever-accounting' ),
				'singular_name'      => __( 'Scheduled Action', 'wp-ever-accounting' ),
				'menu_name'          => _x( 'Scheduled Actions', 'Admin menu name', 'wp-ever-accounting' ),
				'add_new'            => __( 'Add', 'wp-ever-accounting' ),
				'add_new_item'       => __( 'Add New Scheduled Action', 'wp-ever-accounting' ),
				'edit'               => __( 'Edit', 'wp-ever-accounting' ),
				'edit_item'          => __( 'Edit Scheduled Action', 'wp-ever-accounting' ),
				'new_item'           => __( 'New Scheduled Action', 'wp-ever-accounting' ),
				'view'               => __( 'View Action', 'wp-ever-accounting' ),
				'view_item'          => __( 'View Action', 'wp-ever-accounting' ),
				'search_items'       => __( 'Search Scheduled Actions', 'wp-ever-accounting' ),
				'not_found'          => __( 'No actions found', 'wp-ever-accounting' ),
				'not_found_in_trash' => __( 'No actions found in trash', 'wp-ever-accounting' ),
			),
		);

		$args = apply_filters( 'action_scheduler_post_type_args', $args );
		return $args;
	}
}
