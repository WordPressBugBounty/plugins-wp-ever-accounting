<?php

/**
 * Class ActionScheduler_AdminView
 *
 * @codeCoverageIgnore
 */
class ActionScheduler_AdminView extends ActionScheduler_AdminView_Deprecated {

	/**
	 * Instance.
	 *
	 * @var null|self
	 */
	private static $admin_view = null;

	/**
	 * Screen ID.
	 *
	 * @var string
	 */
	private static $screen_id = 'tools_page_action-scheduler';

	/**
	 * ActionScheduler_ListTable instance.
	 *
	 * @var ActionScheduler_ListTable
	 */
	protected $list_table;

	/**
	 * Get instance.
	 *
	 * @return ActionScheduler_AdminView
	 * @codeCoverageIgnore
	 */
	public static function instance() {

		if ( empty( self::$admin_view ) ) {
			$class            = apply_filters( 'action_scheduler_admin_view_class', 'ActionScheduler_AdminView' );
			self::$admin_view = new $class();
		}

		return self::$admin_view;
	}

	/**
	 * Initialize.
	 *
	 * @codeCoverageIgnore
	 */
	public function init() {
		if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {

			if ( class_exists( 'WooCommerce' ) ) {
				add_action( 'woocommerce_admin_status_content_action-scheduler', array( $this, 'render_admin_ui' ) );
				add_action( 'woocommerce_system_status_report', array( $this, 'system_status_report' ) );
				add_filter( 'woocommerce_admin_status_tabs', array( $this, 'register_system_status_tab' ) );
			}

			add_action( 'admin_menu', array( $this, 'register_menu' ) );
			add_action( 'admin_notices', array( $this, 'maybe_check_pastdue_actions' ) );
			add_action( 'current_screen', array( $this, 'add_help_tabs' ) );
		}
	}

	/**
	 * Print system status report.
	 */
	public function system_status_report() {
		$table = new ActionScheduler_wcSystemStatus( ActionScheduler::store() );
		$table->render();
	}

	/**
	 * Registers action-scheduler into WooCommerce > System status.
	 *
	 * @param array $tabs An associative array of tab key => label.
	 * @return array $tabs An associative array of tab key => label, including Action Scheduler's tabs
	 */
	public function register_system_status_tab( array $tabs ) {
		$tabs['action-scheduler'] = __( 'Scheduled Actions', 'wp-ever-accounting' );

		return $tabs;
	}

	/**
	 * Include Action Scheduler's administration under the Tools menu.
	 *
	 * A menu under the Tools menu is important for backward compatibility (as that's
	 * where it started), and also provides more convenient access than the WooCommerce
	 * System Status page, and for sites where WooCommerce isn't active.
	 */
	public function register_menu() {
		$hook_suffix = add_submenu_page(
			'tools.php',
			__( 'Scheduled Actions', 'wp-ever-accounting' ),
			__( 'Scheduled Actions', 'wp-ever-accounting' ),
			'manage_options',
			'action-scheduler',
			array( $this, 'render_admin_ui' )
		);
		add_action( 'load-' . $hook_suffix, array( $this, 'process_admin_ui' ) );
	}

	/**
	 * Triggers processing of any pending actions.
	 */
	public function process_admin_ui() {
		$this->get_list_table();
	}

	/**
	 * Renders the Admin UI
	 */
	public function render_admin_ui() {
		$table = $this->get_list_table();
		$table->display_page();
	}

	/**
	 * Get the admin UI object and process any requested actions.
	 *
	 * @return ActionScheduler_ListTable
	 */
	protected function get_list_table() {
		if ( null === $this->list_table ) {
			$this->list_table = new ActionScheduler_ListTable( ActionScheduler::store(), ActionScheduler::logger(), ActionScheduler::runner() );
			$this->list_table->process_actions();
		}

		return $this->list_table;
	}

	/**
	 * Action: admin_notices
	 *
	 * Maybe check past-due actions, and print notice.
	 *
	 * @uses $this->check_pastdue_actions()
	 */
	public function maybe_check_pastdue_actions() {

		// Filter to prevent checking actions (ex: inappropriate user).
		if ( ! apply_filters( 'action_scheduler_check_pastdue_actions', current_user_can( 'manage_options' ) ) ) {
			return;
		}

		// Get last check transient.
		$last_check = get_transient( 'action_scheduler_last_pastdue_actions_check' );

		// If transient exists, we're within interval, so bail.
		if ( ! empty( $last_check ) ) {
			return;
		}

		// Perform the check.
		$this->check_pastdue_actions();
	}

	/**
	 * Check past-due actions, and print notice.
	 */
	protected function check_pastdue_actions() {

		// Set thresholds.
		$threshold_seconds = (int) apply_filters( 'action_scheduler_pastdue_actions_seconds', DAY_IN_SECONDS );
		$threshold_min     = (int) apply_filters( 'action_scheduler_pastdue_actions_min', 1 );

		// Set fallback value for past-due actions count.
		$num_pastdue_actions = 0;

		// Allow third-parties to preempt the default check logic.
		$check = apply_filters( 'action_scheduler_pastdue_actions_check_pre', null );

		// If no third-party preempted and there are no past-due actions, return early.
		if ( ! is_null( $check ) ) {
			return;
		}

		// Scheduled actions query arguments.
		$query_args = array(
			'date'     => as_get_datetime_object( time() - $threshold_seconds ),
			'status'   => ActionScheduler_Store::STATUS_PENDING,
			'per_page' => $threshold_min,
		);

		// If no third-party preempted, run default check.
		if ( is_null( $check ) ) {
			$store               = ActionScheduler_Store::instance();
			$num_pastdue_actions = (int) $store->query_actions( $query_args, 'count' );

			// Check if past-due actions count is greater than or equal to threshold.
			$check = ( $num_pastdue_actions >= $threshold_min );
			$check = (bool) apply_filters( 'action_scheduler_pastdue_actions_check', $check, $num_pastdue_actions, $threshold_seconds, $threshold_min );
		}

		// If check failed, set transient and abort.
		if ( ! boolval( $check ) ) {
			$interval = apply_filters( 'action_scheduler_pastdue_actions_check_interval', round( $threshold_seconds / 4 ), $threshold_seconds );
			set_transient( 'action_scheduler_last_pastdue_actions_check', time(), $interval );

			return;
		}

		$actions_url = add_query_arg(
			array(
				'page'   => 'action-scheduler',
				'status' => 'past-due',
				'order'  => 'asc',
			),
			admin_url( 'tools.php' )
		);

		// Print notice.
		echo '<div class="notice notice-warning"><p>';
		printf(
			wp_kses(
				// translators: 1) is the number of affected actions, 2) is a link to an admin screen.
				_n(
					'<strong>Action Scheduler:</strong> %1$d <a href="%2$s">past-due action</a> found; something may be wrong. <a href="https://actionscheduler.org/faq/#my-site-has-past-due-actions-what-can-i-do" target="_blank">Read documentation &raquo;</a>',
					'<strong>Action Scheduler:</strong> %1$d <a href="%2$s">past-due actions</a> found; something may be wrong. <a href="https://actionscheduler.org/faq/#my-site-has-past-due-actions-what-can-i-do" target="_blank">Read documentation &raquo;</a>',
					$num_pastdue_actions,
					'wp-ever-accounting'
				),
				array(
					'strong' => array(),
					'a'      => array(
						'href'   => true,
						'target' => true,
					),
				)
			),
			absint( $num_pastdue_actions ),
			esc_attr( esc_url( $actions_url ) )
		);
		echo '</p></div>';

		// Facilitate third-parties to evaluate and print notices.
		do_action( 'action_scheduler_pastdue_actions_extra_notices', $query_args );
	}

	/**
	 * Provide more information about the screen and its data in the help tab.
	 */
	public function add_help_tabs() {
		$screen = get_current_screen();

		if ( ! $screen || self::$screen_id !== $screen->id ) {
			return;
		}

		$as_version       = ActionScheduler_Versions::instance()->latest_version();
		$as_source        = ActionScheduler_SystemInformation::active_source();
		$as_source_path   = ActionScheduler_SystemInformation::active_source_path();
		$as_source_markup = sprintf( '<code>%s</code>', esc_html( $as_source_path ) );

		if ( ! empty( $as_source ) ) {
			$as_source_markup = sprintf(
				'%s: <abbr title="%s">%s</abbr>',
				ucfirst( $as_source['type'] ),
				esc_attr( $as_source_path ),
				esc_html( $as_source['name'] )
			);
		}

		$screen->add_help_tab(
			array(
				'id'      => 'action_scheduler_about',
				'title'   => __( 'About', 'wp-ever-accounting' ),
				'content' =>
					// translators: %s is the Action Scheduler version.
					'<h2>' . sprintf( __( 'About Action Scheduler %s', 'wp-ever-accounting' ), $as_version ) . '</h2>' .
					'<p>' .
						__( 'Action Scheduler is a scalable, traceable job queue for background processing large sets of actions. Action Scheduler works by triggering an action hook to run at some time in the future. Scheduled actions can also be scheduled to run on a recurring schedule.', 'wp-ever-accounting' ) .
					'</p>' .
					'<h3>' . esc_html__( 'Source', 'wp-ever-accounting' ) . '</h3>' .
					'<p>' .
						esc_html__( 'Action Scheduler is currently being loaded from the following location. This can be useful when debugging, or if requested by the support team.', 'wp-ever-accounting' ) .
					'</p>' .
					'<p>' . $as_source_markup . '</p>' .
					'<h3>' . esc_html__( 'WP CLI', 'wp-ever-accounting' ) . '</h3>' .
					'<p>' .
						sprintf(
							/* translators: %1$s is WP CLI command (not translatable) */
							esc_html__( 'WP CLI commands are available: execute %1$s for a list of available commands.', 'wp-ever-accounting' ),
							'<code>wp help action-scheduler</code>'
						) .
					'</p>',
			)
		);

		$screen->add_help_tab(
			array(
				'id'      => 'action_scheduler_columns',
				'title'   => __( 'Columns', 'wp-ever-accounting' ),
				'content' =>
					'<h2>' . __( 'Scheduled Action Columns', 'wp-ever-accounting' ) . '</h2>' .
					'<ul>' .
					sprintf( '<li><strong>%1$s</strong>: %2$s</li>', __( 'Hook', 'wp-ever-accounting' ), __( 'Name of the action hook that will be triggered.', 'wp-ever-accounting' ) ) .
					sprintf( '<li><strong>%1$s</strong>: %2$s</li>', __( 'Status', 'wp-ever-accounting' ), __( 'Action statuses are Pending, Complete, Canceled, Failed', 'wp-ever-accounting' ) ) .
					sprintf( '<li><strong>%1$s</strong>: %2$s</li>', __( 'Arguments', 'wp-ever-accounting' ), __( 'Optional data array passed to the action hook.', 'wp-ever-accounting' ) ) .
					sprintf( '<li><strong>%1$s</strong>: %2$s</li>', __( 'Group', 'wp-ever-accounting' ), __( 'Optional action group.', 'wp-ever-accounting' ) ) .
					sprintf( '<li><strong>%1$s</strong>: %2$s</li>', __( 'Recurrence', 'wp-ever-accounting' ), __( 'The action\'s schedule frequency.', 'wp-ever-accounting' ) ) .
					sprintf( '<li><strong>%1$s</strong>: %2$s</li>', __( 'Scheduled', 'wp-ever-accounting' ), __( 'The date/time the action is/was scheduled to run.', 'wp-ever-accounting' ) ) .
					sprintf( '<li><strong>%1$s</strong>: %2$s</li>', __( 'Log', 'wp-ever-accounting' ), __( 'Activity log for the action.', 'wp-ever-accounting' ) ) .
					'</ul>',
			)
		);
	}
}
