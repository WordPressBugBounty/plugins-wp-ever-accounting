<?php

namespace EverAccounting\Admin;

use EverAccounting\Utilities\NumberUtil;
use EverAccounting\Utilities\ReportsUtil;

defined( 'ABSPATH' ) || exit;

/**
 * Class Dashboard
 *
 * @since 1.0.0
 * @package EverAccounting\Admin
 */
class Dashboard {

	/**
	 * Dashboard constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'eac_dashboard_page_content', array( __CLASS__, 'page_content' ) );
		add_action( 'eac_dashboard_overview_widgets', array( __CLASS__, 'overview_widget' ) );
		add_filter( 'eac_dashboard_overview_stats', array( __CLASS__, 'overview_stats' ) );
		add_action( 'eac_dashboard_widgets', array( __CLASS__, 'recent_payments' ) );
		add_action( 'eac_dashboard_widgets', array( __CLASS__, 'recent_expenses' ) );
		add_action( 'eac_dashboard_widgets', array( __CLASS__, 'recent_invoices' ) );
		add_action( 'eac_dashboard_widgets', array( __CLASS__, 'top_items' ) );
		add_action( 'eac_dashboard_widgets', array( __CLASS__, 'top_customers' ) );
		add_action( 'eac_dashboard_widgets', array( __CLASS__, 'top_vendors' ) );
	}

	/**
	 * Page content.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function render_page() {
		include __DIR__ . '/views/dashboard.php';
	}

	/**
	 * Overview widget.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function overview_widget() {
		if ( ! current_user_can( 'eac_manage_report' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Reason: This is a custom capability.
			return;
		}
		$report   = ReportsUtil::get_profits_report( wp_date( 'Y' ), true );
		$profits  = array_sum( $report['profits'] );
		$payments = array_sum( $report['payments'] );
		$delta    = $profits > 0 && $payments > 0 ? ( $profits / $payments ) * 100 : 0;
		$stats    = apply_filters(
			'eac_dashboard_overview_stats',
			array(
				array(
					'label' => __( 'Incoming', 'wp-ever-accounting' ),
					'value' => eac_format_amount( array_sum( $report['payments'] ) ),
				),
				array(
					'label' => __( 'Outgoing', 'wp-ever-accounting' ),
					'value' => eac_format_amount( array_sum( $report['expenses'] ) ),
				),
				array(
					'label' => __( 'Profit', 'wp-ever-accounting' ),
					'value' => eac_format_amount( array_sum( $report['profits'] ) ),
					'delta' => number_format( $delta, 2 ),
				),
			)
		);
		$datasets = array(
			'labels'   => array_keys( $report['payments'] ),
			'type'     => 'line',
			'datasets' => array(
				array(
					'backgroundColor' => '#3644ff',
					'borderColor'     => '#3644ff',
					'data'            => array_values( $report['payments'] ),
					'fill'            => false,
					'label'           => __( 'Sales', 'wp-ever-accounting' ),
					'type'            => 'line',
				),
				array(
					'label'           => __( 'Expenses', 'wp-ever-accounting' ),
					'backgroundColor' => '#f2385a',
					'borderColor'     => '#f2385a',
					'type'            => 'line',
					'fill'            => false,
					'data'            => array_values( $report['expenses'] ),
				),
				array(
					'label'           => __( 'Profit/Loss', 'wp-ever-accounting' ),
					'backgroundColor' => '#00d48f',
					'borderColor'     => '#00d48f',
					'type'            => 'line',
					'fill'            => false,
					'data'            => array_values( $report['profits'] ),
				),
			),
		);
		?>
		<div class="eac-card is--widget">
			<div class="eac-card__header">
				<?php esc_html_e( 'Overview', 'wp-ever-accounting' ); ?>
			</div>
			<div class="eac-card__body">
				<canvas class="eac-chart" style="min-height: 300px;" data-datasets="<?php echo esc_attr( wp_json_encode( $datasets ) ); ?>" data-currency="<?php echo esc_attr( EAC()->currencies->get_symbol( eac_base_currency() ) ); ?>"></canvas>
			</div>
		</div>
		<div class="eac-stats stats--3">
			<?php foreach ( $stats as $stat ) : ?>
				<div class="eac-stat">
					<div class="eac-stat__label"><?php echo esc_html( $stat['label'] ); ?></div>
					<div class="eac-stat__value">
						<?php echo esc_html( $stat['value'] ); ?>
					</div>
					<?php if ( isset( $stat['meta'] ) ) : ?>
						<div class="eac-stat__meta">
							<span><?php echo wp_kses_post( implode( ' </span><span> ', $stat['meta'] ) ); ?></span>
						</div>
					<?php endif; ?>
					<?php if ( isset( $stat['delta'] ) ) : ?>
						<?php $delta_class = $stat['delta'] > 0 ? 'is--positive' : 'is--negative'; ?>
						<div class="eac-stat__delta <?php echo esc_attr( $delta_class ); ?>" title="<?php esc_html_e( 'Percentage of profit', 'wp-ever-accounting' ); ?>">
							<?php echo esc_html( $stat['delta'] ); ?>%
						</div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Overview stats.
	 *
	 * @param array $stats Stats array.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public static function overview_stats( $stats ) {
		global $wpdb;

		$documents = $wpdb->get_row(
			"SELECT
				SUM(CASE WHEN d.type = 'invoice' THEN (d.total / d.exchange_rate) ELSE 0 END) -
				SUM(CASE WHEN d.type = 'invoice' THEN COALESCE(t.amount / t.exchange_rate, 0) ELSE 0 END) AS receivable,

				SUM(CASE WHEN d.type = 'bill' THEN (d.total / d.exchange_rate) ELSE 0 END) -
				SUM(CASE WHEN d.type = 'bill' THEN COALESCE(t.amount / t.exchange_rate, 0) ELSE 0 END) AS payable
			FROM
        		{$wpdb->prefix}ea_documents d
        	LEFT JOIN
        		 {$wpdb->prefix}ea_transactions t ON d.id = t.document_id
        	WHERE
        	    d.status IN ( 'received', 'sent', 'overdue', 'partial');"
		);

		$stats[] = array(
			'label' => __( 'Receivable', 'wp-ever-accounting' ),
			'value' => eac_format_amount( $documents->receivable ),
		);

		$stats[] = array(
			'label' => __( 'Payable', 'wp-ever-accounting' ),
			'value' => eac_format_amount( $documents->payable ),
		);

		$stats[] = array(
			'label' => __( 'Upcoming', 'wp-ever-accounting' ),
			'value' => eac_format_amount( $documents->receivable - $documents->payable ),
		);

		return $stats;
	}

	/**
	 * Recent payments widget.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function recent_payments() {
		$payments = EAC()->payments->query(
			array(
				'limit'   => 5,
				'orderby' => 'payment_date',
				'order'   => 'DESC',
			)
		);

		?>
		<div class="eac-card is--widget">
			<div class="eac-card__header">
				<?php esc_html_e( 'Recent Payments', 'wp-ever-accounting' ); ?>
				<?php if ( ! empty( $payments ) ) : ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=eac-sales&tab=payments' ) ); ?>"><?php esc_html_e( 'View all', 'wp-ever-accounting' ); ?></a>
				<?php endif; ?>
			</div>
			<?php if ( ! empty( $payments ) ) : ?>
			<table class="eac-table">
				<thead>
				<tr>
					<th><?php esc_html_e( 'Payment #', 'wp-ever-accounting' ); ?></th>
					<th><?php esc_html_e( 'Date', 'wp-ever-accounting' ); ?></th>
					<th><?php esc_html_e( 'Amount', 'wp-ever-accounting' ); ?></th>
				</tr>
				</thead>
				<tbody>
					<?php foreach ( $payments as $payment ) : ?>
						<tr>
							<td><a href="<?php echo esc_url( $payment->get_view_url() ); ?>"><?php echo esc_html( $payment->number ); ?></a></td>
							<td><?php echo esc_html( date_i18n( eac_date_format(), strtotime( $payment->date ) ) ); ?></td>
							<td><?php echo esc_html( $payment->formatted_amount ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<?php else : ?>
				<div class="eac-card__body">
					<p class="empty"><?php esc_html_e( 'No payments found.', 'wp-ever-accounting' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Recent expenses widget.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function recent_expenses() {
		$expenses = EAC()->expenses->query(
			array(
				'limit'   => 5,
				'orderby' => 'payment_date',
				'order'   => 'DESC',
			)
		);
		?>
		<div class="eac-card is--widget">
			<div class="eac-card__header">
				<?php esc_html_e( 'Recent Expenses', 'wp-ever-accounting' ); ?>
				<?php if ( ! empty( $expenses ) ) : ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=eac-purchases&tab=expenses' ) ); ?>"><?php esc_html_e( 'View all', 'wp-ever-accounting' ); ?></a>
				<?php endif; ?>
			</div>
			<?php if ( ! empty( $expenses ) ) : ?>
				<table class="eac-table">
					<thead>
					<tr>
						<th><?php esc_html_e( 'Expense #', 'wp-ever-accounting' ); ?></th>
						<th><?php esc_html_e( 'Date', 'wp-ever-accounting' ); ?></th>
						<th><?php esc_html_e( 'Amount', 'wp-ever-accounting' ); ?></th>
					</tr>
					</thead>
					<tbody>
					<?php foreach ( $expenses as $expense ) : ?>
						<tr>
							<td><a href="<?php echo esc_url( $expense->get_view_url() ); ?>"><?php echo esc_html( $expense->number ); ?></a></td>
							<td><?php echo esc_html( date_i18n( eac_date_format(), strtotime( $expense->payment_date ) ) ); ?></td>
							<td><?php echo esc_html( $expense->formatted_amount ); ?></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			<?php else : ?>
				<div class="eac-card__body">
					<p class="empty"><?php esc_html_e( 'No expenses found.', 'wp-ever-accounting' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Recent invoices widget.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function recent_invoices() {
		$invoices = EAC()->invoices->query(
			array(
				'limit'   => 5,
				'orderby' => 'date',
				'order'   => 'DESC',
			)
		);

		?>
		<div class="eac-card is--widget">
			<div class="eac-card__header">
				<?php esc_html_e( 'Recent Invoices', 'wp-ever-accounting' ); ?>
				<?php if ( ! empty( $invoices ) ) : ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=eac-sales&tab=invoices' ) ); ?>"><?php esc_html_e( 'View all', 'wp-ever-accounting' ); ?></a>
				<?php endif; ?>
			</div>
			<?php if ( ! empty( $invoices ) ) : ?>
			<table class="eac-table">
				<thead>
				<tr>
					<th><?php esc_html_e( 'Invoice #', 'wp-ever-accounting' ); ?></th>
					<th><?php esc_html_e( 'Date', 'wp-ever-accounting' ); ?></th>
					<th><?php esc_html_e( 'Amount', 'wp-ever-accounting' ); ?></th>
				</tr>
				</thead>
				<tbody>
					<?php foreach ( $invoices as $invoice ) : ?>
						<tr>
							<td><a href="<?php echo esc_url( $invoice->get_view_url() ); ?>"><?php echo esc_html( $invoice->number ); ?></a></td>
							<td><?php echo esc_html( date_i18n( eac_date_format(), strtotime( $invoice->issue_date ) ) ); ?></td>
							<td><?php echo esc_html( $invoice->formatted_total ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<?php else : ?>
				<div class="eac-card__body">
					<p class="empty"><?php esc_html_e( 'No invoices found.', 'wp-ever-accounting' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Top items widget.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function top_items() {
		global $wpdb;
		// we will query documents table where type is invoice and status is paid. then we will get the items from document items table.
		$item_ids = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT di.item_id, SUM(di.subtotal / d.exchange_rate) AS total_sales
				 FROM {$wpdb->prefix}ea_document_items AS di
         	     JOIN {$wpdb->prefix}ea_documents AS d ON di.document_id = d.id
                 WHERE d.type = %s AND d.status = %s
                 GROUP BY di.item_id
                 ORDER BY total_sales DESC
         		LIMIT 5",
				'invoice',
				'paid'
			)
		);

		$items = array();
		foreach ( $item_ids as $item_id ) {
			$item = EAC()->items->get( $item_id->item_id );
			if ( $item ) {
				$item->total_sales = $item_id->total_sales;
				$items[]           = $item;
			}
		}

		?>
		<div class="eac-card is--widget">
			<div class="eac-card__header">
				<?php esc_html_e( 'Top Items', 'wp-ever-accounting' ); ?>
				<?php if ( ! empty( $items ) ) : ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=eac-items' ) ); ?>"><?php esc_html_e( 'View all', 'wp-ever-accounting' ); ?></a>
				<?php endif; ?>
			</div>
			<?php if ( ! empty( $items ) ) : ?>
			<table class="eac-table">
				<thead>
				<tr>
					<th><?php esc_html_e( 'Item', 'wp-ever-accounting' ); ?></th>
					<th><?php esc_html_e( 'Total Sales', 'wp-ever-accounting' ); ?></th>
				</tr>
				</thead>
				<tbody>
					<?php foreach ( $items as $item ) : ?>
						<tr>
							<td>
								<a href="<?php echo esc_url( $item->get_view_url() ); ?>">
									<?php echo esc_html( $item->name ); ?>
								</a>
							</td>
							<td><?php echo esc_html( eac_format_amount( $item->total_sales ) ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<?php else : ?>
				<div class="eac-card__body">
					<p class="empty"><?php esc_html_e( 'No data found.', 'wp-ever-accounting' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Top customers' widget.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function top_customers() {
		global $wpdb;
		// we will query documents table where type is invoice and status is paid. then we will get the items from document items table.
		$payments = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT contact_id, SUM(amount / exchange_rate) AS amount
				 FROM {$wpdb->prefix}ea_transactions
				 WHERE type = 'payment'
				 GROUP BY contact_id
				 ORDER BY amount DESC LIMIT %d",
				5
			)
		);

		$customers = array();
		foreach ( $payments as $payment ) {
			$customer = EAC()->customers->get( $payment->contact_id );
			if ( $customer ) {
				$customer->amount = $payment->amount;
				$customers[]      = $customer;
			}
		}
		?>
		<div class="eac-card is--widget">
			<div class="eac-card__header">
				<?php esc_html_e( 'Top Customers', 'wp-ever-accounting' ); ?>
				<?php if ( ! empty( $customers ) ) : ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=eac-sales&tab=customers' ) ); ?>"><?php esc_html_e( 'View all', 'wp-ever-accounting' ); ?></a>
				<?php endif; ?>
			</div>
			<?php if ( ! empty( $customers ) ) : ?>
			<table class="eac-table">
				<thead>
				<tr>
					<th><?php esc_html_e( 'Customer', 'wp-ever-accounting' ); ?></th>
					<th><?php esc_html_e( 'Amount', 'wp-ever-accounting' ); ?></th>
				</tr>
				</thead>
				<tbody>
					<?php foreach ( $customers as $customer ) : ?>
						<tr>
							<td>
								<a href="<?php echo esc_url( $customer->get_view_url() ); ?>">
									<?php echo esc_html( $customer->formatted_name ); ?>
								</a>
							</td>
							<td><?php echo esc_html( eac_format_amount( $customer->amount ) ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<?php else : ?>
				<div class="eac-card__body">
					<p class="empty"><?php esc_html_e( 'No data found.', 'wp-ever-accounting' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Top vendors widget.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function top_vendors() {
		global $wpdb;
		// we will query documents table where type is bill and status is paid. then we will get the items from document items table.
		$expenses = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT contact_id, SUM(amount / exchange_rate) AS amount
				 FROM {$wpdb->prefix}ea_transactions
				 WHERE type = 'expense'
				 GROUP BY contact_id
				 ORDER BY amount DESC LIMIT %d",
				5
			)
		);

		$vendors = array();
		foreach ( $expenses as $expense ) {
			$vendor = EAC()->vendors->get( $expense->contact_id );
			if ( $vendor ) {
				$vendor->amount = $expense->amount;
				$vendors[]      = $vendor;
			}
		}
		?>
		<div class="eac-card is--widget">
			<div class="eac-card__header">
				<?php esc_html_e( 'Top Vendors', 'wp-ever-accounting' ); ?>
				<?php if ( ! empty( $vendors ) ) : ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=eac-purchases&tab=vendors' ) ); ?>"><?php esc_html_e( 'View all', 'wp-ever-accounting' ); ?></a>
				<?php endif; ?>
			</div>
			<?php if ( ! empty( $vendors ) ) : ?>
			<table class="eac-table">
				<thead>
				<tr>
					<th><?php esc_html_e( 'Vendor', 'wp-ever-accounting' ); ?></th>
					<th><?php esc_html_e( 'Amount', 'wp-ever-accounting' ); ?></th>
				</tr>
				</thead>
				<tbody>
					<?php foreach ( $vendors as $vendor ) : ?>
						<tr>
							<td>
								<a href="<?php echo esc_url( $vendor->get_view_url() ); ?>">
									<?php echo esc_html( $vendor->formatted_name ); ?>
								</a>
							</td>
							<td><?php echo esc_html( eac_format_amount( $vendor->amount ) ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<?php else : ?>
				<div class="eac-card__body">
					<p class="empty"><?php esc_html_e( 'No data found.', 'wp-ever-accounting' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}
}
