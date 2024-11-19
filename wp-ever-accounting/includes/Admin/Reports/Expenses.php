<?php

namespace EverAccounting\Admin\Reports;

use EverAccounting\Utilities\ReportsUtil;

defined( 'ABSPATH' ) || exit;

/**
 * Class Expenses.
 *
 * @since 1.0.0
 * @package EverAccounting\Admin\Reports
 */
class Expenses {

	/**
	 * Render the expenses report.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public static function render() {
		wp_verify_nonce( '_wpnonce' );
		$year  = ! empty( $_GET['year'] ) ? absint( $_GET['year'] ) : wp_date( 'Y' );
		$data  = ReportsUtil::get_expenses_report( $year );
		$chart = array(
			'type'     => 'line',
			'labels'   => array_keys( $data['months'] ),
			'datasets' => array(
				array(
					'label'           => __( 'Expenses', 'wp-ever-accounting' ),
					'backgroundColor' => '#f2385a',
					'borderColor'     => '#f2385a',
					'fill'            => false,
					'data'            => array_values( $data['months'] ),
				),
			),
		);
		?>
		<div class="eac-section-header">
			<h3>
				<?php echo esc_html__( 'Expenses Report', 'wp-ever-accounting' ); ?>
			</h3>
			<form class="ea-report-filters" method="get" action="">
				<input type="number" name="year" value="<?php echo esc_attr( $year ); ?>" placeholder="<?php echo esc_attr__( 'Year', 'wp-ever-accounting' ); ?>"/>
				<button type="submit" class="button">
					<?php echo esc_html__( 'Submit', 'wp-ever-accounting' ); ?>
				</button>
				<input hidden="hidden" name="page" value="eac-reports"/>
				<input hidden="hidden" name="tab" value="expenses"/>
			</form>
		</div>

		<div class="eac-card">
			<div class="eac-card__header">
				<h3 class="eac-card__title"><?php esc_html_e( 'Chart', 'wp-ever-accounting' ); ?></h3>
			</div>
			<div class="eac-card__body">
				<div class="eac-chart">
					<canvas class="eac-chart" id="eac-expenses-chart" style="height: 300px;margin-bottom: 20px;" data-datasets="<?php echo esc_attr( wp_json_encode( $chart ) ); ?>" data-currency="<?php echo esc_attr( EAC()->currencies->get_symbol( eac_base_currency() ) ); ?>"></canvas>
				</div>
			</div>
		</div>

		<div class="eac-stats stats--3">
			<div class="eac-stat">
				<div class="eac-stat__label"><?php esc_html_e( 'Total Expense', 'wp-ever-accounting' ); ?></div>
				<div class="eac-stat__value"><?php echo esc_html( eac_format_amount( $data['total_amount'] ) ); ?></div>
			</div>
			<div class="eac-stat">
				<div class="eac-stat__label"><?php esc_html_e( 'Monthly Avg.', 'wp-ever-accounting' ); ?></div>
				<div class="eac-stat__value"><?php echo esc_html( eac_format_amount( $data['month_avg'] ) ); ?></div>
			</div>
			<div class="eac-stat">
				<div class="eac-stat__label"><?php esc_html_e( 'Daily Avg.', 'wp-ever-accounting' ); ?></div>
				<div class="eac-stat__value"><?php echo esc_html( eac_format_amount( $data['daily_avg'] ) ); ?></div>
			</div>
		</div>


		<div class="eac-card">
			<div class="eac-card__header">
				<h3 class="eac-card__title"><?php esc_html_e( 'Payments by Months', 'wp-ever-accounting' ); ?></h3>
			</div>
			<div class="tw-overflow-x-auto">
				<table class="eac-table has--border">
					<thead>
					<tr>
						<th><?php esc_html_e( 'Month', 'wp-ever-accounting' ); ?></th>
						<?php foreach ( array_keys( $data['months'] ) as $label ) : ?>
							<th><?php echo esc_html( $label ); ?></th>
						<?php endforeach; ?>
					</tr>
					</thead>
					<tbody>
					<?php if ( ! empty( $data['categories'] ) ) : ?>
						<?php foreach ( $data['categories'] as $category_id => $datum ) : ?>
							<tr>
								<td>
									<?php
									$term      = EAC()->categories->get( $category_id );
									$term_name = $term && $term->name ? $term->name : '&mdash;';
									echo esc_html( $term_name );
									?>
								</td>
								<?php foreach ( $datum as $value ) : ?>
									<td><?php echo esc_html( eac_format_amount( $value ) ); ?></td>
								<?php endforeach; ?>
							</tr>
						<?php endforeach; ?>
					<?php else : ?>
						<tr>
							<td colspan="<?php echo count( $data['months'] ) + 1; ?>">
								<p>
									<?php esc_html_e( 'No data found', 'wp-ever-accounting' ); ?>
								</p>
							</td>
						</tr>
					<?php endif; ?>
					</tbody>
					<tfoot>
					<tr>
						<th><?php esc_html_e( 'Total', 'wp-ever-accounting' ); ?></th>
						<?php foreach ( $data['months'] as $value ) : ?>
							<th><?php echo esc_html( eac_format_amount( $value ) ); ?></th>
						<?php endforeach; ?>
					</tr>
					</tfoot>
				</table>
			</div>
		</div>
		<?php
	}
}
