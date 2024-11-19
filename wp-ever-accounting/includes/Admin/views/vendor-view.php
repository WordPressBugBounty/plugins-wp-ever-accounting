<?php
/**
 * Admin View: Vendor details.
 *
 * @since 1.0.0
 *
 * @subpackage EverAccounting/Admin/Views
 * @package EverAccounting
 * @var $vendor \EverAccounting\Models\Vendor Vendor object.
 */

use EverAccounting\Models\Vendor;

defined( 'ABSPATH' ) || exit;

wp_verify_nonce( '_wpnonce' );
$id       = isset( $_GET['id'] ) ? absint( wp_unslash( $_GET['id'] ) ) : 0;
$section  = isset( $_GET['section'] ) ? sanitize_text_field( wp_unslash( $_GET['section'] ) ) : '';
$vendor   = EAC()->vendors->get( $id );
$sections = apply_filters(
	'eac_vendor_view_sections',
	array(
		'overview' => array(
			'label' => __( 'Overview', 'wp-ever-accounting' ),
			'icon'  => 'admin-settings',
		),
		'expenses' => array(
			'label' => __( 'Expenses', 'wp-ever-accounting' ),
			'icon'  => 'money',
		),
		'bills'    => array(
			'label' => __( 'Bills', 'wp-ever-accounting' ),
			'icon'  => 'media-spreadsheet',
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
<div class="eac-section-header">
	<h1 class="wp-heading-inline">
		<?php esc_html_e( 'View Vendor', 'wp-ever-accounting' ); ?>
		<a href="<?php echo esc_attr( remove_query_arg( array( 'action', 'id' ) ) ); ?>" title="<?php esc_attr_e( 'Go back', 'wp-ever-accounting' ); ?>">
			<span class="dashicons dashicons-undo"></span>
		</a>
	</h1>
	<a href="<?php echo esc_url( $vendor->get_edit_url() ); ?>" class="page-title-action"><?php esc_html_e( 'Edit Vendor', 'wp-ever-accounting' ); ?></a>
</div>


<div class="eac-card eac-profile-header">
	<div class="eac-profile-header__avatar">
		<?php echo get_avatar( $vendor->email, 120 ); ?>
	</div>
	<div class="eac-profile-header__columns">
		<div class="eac-profile-header__column">
			<div class="eac-profile-header__title">
				<?php echo esc_html( $vendor->name ); ?>
			</div>
			<?php if ( $vendor->phone ) : ?>
				<p class="small"><a href="tel:<?php echo esc_attr( $vendor->phone ); ?>"><?php echo esc_html( $vendor->phone ); ?></a></p>
			<?php endif; ?>
			<?php if ( $vendor->email ) : ?>
				<p class="small"><a href="mailto:<?php echo esc_attr( $vendor->email ); ?>"><?php echo esc_html( $vendor->email ); ?></a></p>
			<?php endif; ?>
			<p class="small">
				<?php // translators: %s: date. ?>
				<?php printf( esc_html__( 'Since %s', 'wp-ever-accounting' ), esc_html( wp_date( get_option( 'date_format' ), strtotime( $vendor->date_created ) ) ) ); ?>
			</p>
		</div>
	</div>
	<a class="eac-profile-header__edit" href="<?php echo esc_url( $vendor->get_edit_url() ); ?>"><span class="dashicons dashicons-edit"></span></a>
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
		 * Fires action to display vendor view section.
		 *
		 * @param Vendor $vendor Vendor object.
		 *
		 * @since 2.0.0
		 */
		do_action( 'eac_vendor_profile_section_' . $current_section, $vendor );
		?>
	</div>
	<br class="clear">
</div>
