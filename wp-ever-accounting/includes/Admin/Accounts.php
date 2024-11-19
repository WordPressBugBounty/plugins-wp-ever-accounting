<?php

namespace EverAccounting\Admin;

use EverAccounting\Models\Account;
use EverAccounting\Utilities\ReportsUtil;

defined( 'ABSPATH' ) || exit;

/**
 * Class Accounts
 *
 * @since 3.0.0
 * @package EverAccounting\Admin\Banking
 */
class Accounts {

	/**
	 * Accounts constructor.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		add_filter( 'eac_banking_page_tabs', array( __CLASS__, 'register_tabs' ) );
		add_action( 'admin_post_eac_edit_account', array( __CLASS__, 'handle_edit' ) );
		add_action( 'eac_banking_page_accounts_loaded', array( __CLASS__, 'page_loaded' ) );
		add_action( 'eac_banking_page_accounts_content', array( __CLASS__, 'page_content' ) );
		add_action( 'eac_account_profile_section_overview', array( __CLASS__, 'overview_section' ) );
		add_action( 'eac_account_profile_section_payments', array( __CLASS__, 'payments_section' ) );
		add_action( 'eac_account_profile_section_expenses', array( __CLASS__, 'expenses_section' ) );
		add_action( 'eac_account_profile_section_notes', array( __CLASS__, 'account_notes' ) );
	}


	/**
	 * Register tab.
	 *
	 * @param array $tabs Tabs.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public static function register_tabs( $tabs ) {
		if ( current_user_can( 'eac_manage_account' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
			$tabs['accounts'] = __( 'Account', 'wp-ever-accounting' );
		}

		return $tabs;
	}

	/**
	 * Handle actions.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function handle_edit() {
		check_admin_referer( 'eac_edit_account' );
		if ( ! current_user_can( 'eac_manage_account' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Custom capability.
			wp_die( esc_html__( 'You do not have permission to edit accounts.', 'wp-ever-accounting' ) );
		}

		$referer = wp_get_referer();
		$data    = array(
			'id'       => isset( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : 0,
			'type'     => isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '',
			'name'     => isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '',
			'number'   => isset( $_POST['number'] ) ? sanitize_text_field( wp_unslash( $_POST['number'] ) ) : '',
			'currency' => isset( $_POST['currency'] ) ? sanitize_text_field( wp_unslash( $_POST['currency'] ) ) : '',
		);

		$account = EAC()->accounts->insert( $data );
		if ( is_wp_error( $account ) ) {
			EAC()->flash->error( $account->get_error_message() );
		} else {
			EAC()->flash->success( __( 'Account saved successfully.', 'wp-ever-accounting' ) );
			$referer = remove_query_arg( array( 'action' ), $referer );
			$referer = add_query_arg(
				array(
					'action' => 'view',
					'id'     => $account->id,
				),
				$referer
			);
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
				$id = filter_input( INPUT_GET, 'id', FILTER_VALIDATE_INT );
				if ( ! EAC()->accounts->get( $id ) ) {
					wp_die( esc_html__( 'You attempted to retrieve an account that does not exist. Perhaps it was deleted?', 'wp-ever-accounting' ) );
				}
				break;

			default:
				$screen     = get_current_screen();
				$list_table = new ListTables\Accounts();
				$list_table->prepare_items();
				$screen->add_option(
					'per_page',
					array(
						'label'   => __( 'Number of accounts per page:', 'wp-ever-accounting' ),
						'default' => 20,
						'option'  => 'eac_accounts_per_page',
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
				include __DIR__ . '/views/account-edit.php';
				break;
			case 'view':
				include __DIR__ . '/views/account-view.php';
				break;
			default:
				include __DIR__ . '/views/account-list.php';
				break;
		}
	}

	/**
	 * Account overview.
	 *
	 * @param Account $account Account object.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public static function overview_section( $account ) {
		global $wpdb;
		$start_date   = ReportsUtil::get_year_start_date();
		$end_date     = ReportsUtil::get_year_end_date();
		$transactions = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT t.amount amount, MONTH(t.payment_date) AS month, YEAR(t.payment_date) AS year, t.type
					FROM {$wpdb->prefix}ea_transactions AS t
					LEFT JOIN {$wpdb->prefix}ea_transfers AS it ON t.id = it.payment_id OR t.id = it.expense_id
					WHERE it.payment_id IS NULL
					AND it.expense_id IS NULL
					AND t.account_id = %d
					AND t.payment_date BETWEEN %s AND %s
					ORDER BY t.payment_date ASC",
				$account->id,
				$start_date,
				$end_date
			)
		);
		$stats[]      = array(
			'label' => __( 'Incoming', 'wp-ever-accounting' ),
			'value' => eac_format_amount( array_sum( wp_list_pluck( wp_list_filter( $transactions, array( 'type' => 'payment' ) ), 'amount' ) ), $account->currency ),
			'meta'  => array(
				wp_date( 'Y', strtotime( $start_date ) ),
			),
		);
		$stats[]      = array(
			'label' => __( 'Outgoing', 'wp-ever-accounting' ),
			'value' => eac_format_amount( array_sum( wp_list_pluck( wp_list_filter( $transactions, array( 'type' => 'expense' ) ), 'amount' ) ), $account->currency ),
			'meta'  => array(
				wp_date( 'Y', strtotime( $start_date ) ),
			),
		);
		$stats[]      = array(
			'label' => __( 'Balance', 'wp-ever-accounting' ),
			'value' => $account->formatted_balance,
		);
		$stats        = apply_filters( 'eac_account_overview_stats', $stats );

		$payments = ReportsUtil::annualize_data( wp_list_filter( $transactions, array( 'type' => 'payment' ) ) );
		$expenses = ReportsUtil::annualize_data( wp_list_filter( $transactions, array( 'type' => 'expense' ) ) );
		$chart    = array(
			'type'     => 'line',
			'labels'   => array_keys( $payments ),
			'datasets' => array(
				array(
					'label'           => __( 'Incoming', 'wp-ever-accounting' ),
					'backgroundColor' => '#4CAF50',
					'data'            => array_values( $payments ),
				),
				array(
					'label'           => __( 'Outgoing', 'wp-ever-accounting' ),
					'backgroundColor' => '#F44336',
					'data'            => array_values( $expenses ),
				),
			),
		);
		?>
		<h2 class="has--border"><?php echo esc_html__( 'Overview', 'wp-ever-accounting' ); ?></h2>
		<canvas class="eac-chart" id="eac-account-chart" style="height: 300px;margin-bottom: 20px;" data-datasets="<?php echo esc_attr( wp_json_encode( $chart ) ); ?>" data-currency="<?php echo esc_attr( EAC()->currencies->get_symbol( $account->currency ) ); ?>"></canvas>
		<div class="eac-stats stats--3">
			<?php foreach ( $stats as $stat ) : ?>
				<div class="eac-stat">
					<div class="eac-stat__label"><?php echo esc_html( $stat['label'] ); ?></div>
					<div class="eac-stat__value">
						<?php echo esc_html( $stat['value'] ); ?>
						<?php if ( isset( $stat['delta'] ) ) : ?>
							<?php $delta_class = $stat['delta'] > 0 ? 'is--positive' : 'is--negative'; ?>
							<div class="eac-stat__delta <?php echo esc_attr( $delta_class ); ?>">
								<?php echo esc_html( $stat['delta'] ); ?>%
							</div>
						<?php endif; ?>
					</div>
					<?php if ( isset( $stat['meta'] ) ) : ?>
						<div class="eac-stat__meta">
							<span><?php echo wp_kses_post( implode( ' </span><span> ', $stat['meta'] ) ); ?></span>
						</div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
		<h2><?php echo esc_html__( 'Account Details', 'wp-ever-accounting' ); ?></h2>
		<?php
		$attributes = array(
			array(
				'label' => __( 'Name', 'wp-ever-accounting' ),
				'value' => $account->name,
			),
			'number'   => array(
				'label' => __( 'Number', 'wp-ever-accounting' ),
				'value' => $account->number,
			),
			'currency' => array(
				'label' => __( 'Currency', 'wp-ever-accounting' ),
				'value' => $account->currency,
			),
			'created'  => array(
				'label' => __( 'Created', 'wp-ever-accounting' ),
				'value' => wp_date( eac_date_format(), strtotime( $account->date_created ) ),
			),
			array(
				'label' => __( 'Updated', 'wp-ever-accounting' ),
				'value' => wp_date( eac_date_format(), strtotime( $account->date_updated ) ),
			),
		)
		?>
		<table class="eac-table is--striped is--bordered">
			<tbody>
			<?php foreach ( $attributes as $attribute ) : ?>
				<tr>
					<th><?php echo esc_html( $attribute['label'] ); ?></th>
					<td><?php echo esc_html( empty( $attribute['value'] ) ? '&mdash;' : $attribute['value'] ); ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Account payments.
	 *
	 * @param Account $account Account object.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public static function payments_section( $account ) {
		$payments = EAC()->payments->query(
			array(
				'account_id' => $account->id,
				'orderby'    => 'date_created',
				'order'      => 'DESC',
				'limit'      => 20,
			)
		);
		?>
		<h2 class="has--border"><?php esc_html_e( 'Recent Payments', 'wp-ever-accounting' ); ?></h2>
		<table class="widefat fixed striped">
			<thead>
			<tr>
				<th><?php esc_html_e( 'Number', 'wp-ever-accounting' ); ?></th>
				<th><?php esc_html_e( 'Date', 'wp-ever-accounting' ); ?></th>
				<th><?php esc_html_e( 'Reference', 'wp-ever-accounting' ); ?></th>
				<th><?php esc_html_e( 'Amount', 'wp-ever-accounting' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php if ( $payments ) : ?>
				<?php foreach ( $payments as $payment ) : ?>
					<tr>
						<td>
							<a href="<?php echo esc_url( $payment->get_view_url() ); ?>">
								<?php echo esc_html( $payment->number ); ?>
							</a>
						<td><?php echo esc_html( $payment->payment_date ? wp_date( eac_date_format(), strtotime( $payment->payment_date ) ) : '&mdash;' ); ?></td>
						<td><?php echo esc_html( $payment->reference ? $payment->reference : '&mdash;' ); ?></td>
						<td><?php echo esc_html( $payment->formatted_amount ); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr>
					<td colspan="4"><?php esc_html_e( 'No payments found.', 'wp-ever-accounting' ); ?></td>
				</tr>
			<?php endif; ?>
		</table>
		<?php
	}

	/**
	 * Account expenses.
	 *
	 * @param Account $account Account object.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public static function expenses_section( $account ) {
		$expenses = EAC()->expenses->query(
			array(
				'account_id' => $account->id,
				'orderby'    => 'date_created',
				'order'      => 'DESC',
				'limit'      => 20,
			)
		);
		?>
		<h2 class="has--border"><?php esc_html_e( 'Recent Expenses', 'wp-ever-accounting' ); ?></h2>
		<table class="widefat fixed striped">
			<thead>
			<tr>
				<th><?php esc_html_e( 'Number', 'wp-ever-accounting' ); ?></th>
				<th><?php esc_html_e( 'Date', 'wp-ever-accounting' ); ?></th>
				<th><?php esc_html_e( 'Reference', 'wp-ever-accounting' ); ?></th>
				<th><?php esc_html_e( 'Amount', 'wp-ever-accounting' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php if ( $expenses ) : ?>
				<?php foreach ( $expenses as $expense ) : ?>
					<tr>
						<td>
							<a href="<?php echo esc_url( $expense->get_view_url() ); ?>">
								<?php echo esc_html( $expense->number ); ?>
							</a>
						</td>
						<td><?php echo esc_html( wp_date( eac_date_format(), strtotime( $expense->payment_date ) ) ); ?></td>
						<td><?php echo esc_html( $expense->reference ? $expense->reference : '&mdash;' ); ?></td>
						<td><?php echo esc_html( eac_format_amount( $expense->amount ) ); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr>
					<td colspan="3"><?php esc_html_e( 'No expenses found.', 'wp-ever-accounting' ); ?></td>
				</tr>
			<?php endif; ?>
		</table>
		<?php
	}

	/**
	 * Account notes.
	 *
	 * @param Account $account Account object.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function account_notes( $account ) {
		// bail if account does not exist.
		if ( ! $account->exists() ) {
			return;
		}

		$notes = EAC()->notes->query(
			array(
				'parent_id'   => $account->id,
				'parent_type' => 'account',
				'orderby'     => 'date_created',
				'order'       => 'DESC',
				'limit'       => 20,
			)
		);
		?>
		<h2 class="has--border"><?php esc_html_e( 'Notes', 'wp-ever-accounting' ); ?></h2>

		<div class="eac-form-field">
			<label for="eac-note"><?php esc_html_e( 'Add Note', 'wp-ever-accounting' ); ?></label>
			<textarea id="eac-note" cols="30" rows="2" placeholder="<?php esc_attr_e( 'Enter Note', 'wp-ever-accounting' ); ?>"></textarea>
		</div>
		<button id="eac-add-note" type="button" class="button tw-mb-[20px]" data-parent_id="<?php echo esc_attr( $account->id ); ?>" data-parent_type="account" data-nonce="<?php echo esc_attr( wp_create_nonce( 'eac_add_note' ) ); ?>">
			<?php esc_html_e( 'Add Note', 'wp-ever-accounting' ); ?>
		</button>

		<?php include __DIR__ . '/views/note-list.php'; ?>
		<?php
	}
}
