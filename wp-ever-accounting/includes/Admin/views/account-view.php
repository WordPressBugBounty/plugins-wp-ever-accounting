<?php
/**
 * Admin View: Account View
 *
 * @since 1.0.0
 * @package EverAccounting
 * @var $account Account Account object.
 */

use EverAccounting\Models\Account;

defined( 'ABSPATH' ) || exit;

wp_verify_nonce( '_wpnonce' );
$id       = isset( $_GET['id'] ) ? absint( wp_unslash( $_GET['id'] ) ) : 0;
$section  = isset( $_GET['section'] ) ? sanitize_text_field( wp_unslash( $_GET['section'] ) ) : '';
$account  = EAC()->accounts->get( $id );
$sections = apply_filters(
	'eac_account_view_sections',
	array(
		'overview' => array(
			'label' => __( 'Overview', 'wp-ever-accounting' ),
			'icon'  => 'admin-settings',
		),
		'payments' => array(
			'label' => __( 'Payments', 'wp-ever-accounting' ),
			'icon'  => 'money',
		),
		'expenses' => array(
			'label' => __( 'Expenses', 'wp-ever-accounting' ),
			'icon'  => 'money-alt',
		),
		'notes'    => array(
			'label' => __( 'Notes', 'wp-ever-accounting' ),
			'icon'  => 'admin-comments',
		),
	)
);

// Validate section.
$current_section = ! array_key_exists( $section, $sections ) ? current( array_keys( $sections ) ) : $section;
?>
<h1 class="wp-heading-inline">
	<?php esc_html_e( 'View Account', 'wp-ever-accounting' ); ?>
	<a href="<?php echo esc_attr( remove_query_arg( array( 'action', 'id' ) ) ); ?>" title="<?php esc_attr_e( 'Go back', 'wp-ever-accounting' ); ?>">
		<span class="dashicons dashicons-undo"></span>
	</a>
</h1>


<div class="eac-card eac-profile-header">
	<div class="eac-profile-header__avatar">
		<div class="avatar tw-flex tw-items-center tw-justify-center tw-w-16 tw-h-16 tw-rounded-full tw-bg-blue-500 tw-text-white tw-text-2xl tw-font-bold">
		<?php echo esc_html( EAC()->currencies->get_symbol( $account->currency ) ); ?>
		</div>
	</div>
	<div class="eac-profile-header__columns">
		<div class="eac-profile-header__column">
			<div class="eac-profile-header__title">
				<?php echo esc_html( $account->name ); ?>
			</div>
			<p class="small"><?php printf( '%1$s %2$s', esc_html__( 'Balance:', 'wp-ever-accounting' ), esc_html( $account->formatted_balance ) ); ?></p>
			<?php if ( $account->number ) : ?>
				<p class="small"><?php printf( '%1$s %2$s', esc_html__( 'Account #:', 'wp-ever-accounting' ), esc_html( $account->number ) ); ?></p>
			<?php endif; ?>
			<p class="small">
				<?php // translators: %s: date. ?>
				<?php printf( esc_html__( 'Since %s', 'wp-ever-accounting' ), esc_html( wp_date( eac_date_format(), strtotime( $account->date_created ) ) ) ); ?>
			</p>
		</div>
	</div>
	<a class="eac-profile-header__edit" href="<?php echo esc_url( $account->get_edit_url() ); ?>"><span class="dashicons dashicons-edit"></span></a>
</div>

<div class="eac-profile-sections">
	<ul class="eac-profile-sections__nav" role="tablist">
		<?php foreach ( $sections as $key => $section ) : ?>
			<li id="<?php echo esc_attr( $key ); ?>-nav-item" class="eac-profile-sections__nav-item <?php echo $current_section === $key ? 'is-active' : ''; ?>" role="tab" aria-controls="<?php echo esc_attr( $key ); ?>">
				<a href="<?php echo esc_url( add_query_arg( 'section', $key ) ); ?>">
					<span class="dashicons dashicons-<?php echo esc_attr( $section['icon'] ); ?>"></span>
					<span class="label">
						<?php echo esc_html( $section['label'] ); ?>
					</span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
	<div class="eac-profile-sections__content">
		<?php
		/**
		 * Fires action to display account view section.
		 *
		 * @param Account $account Account object.
		 *
		 * @since 2.0.0
		 */
		do_action( 'eac_account_profile_section_' . $current_section, $account );
		?>
	</div>
	<br class="clear">
</div>
