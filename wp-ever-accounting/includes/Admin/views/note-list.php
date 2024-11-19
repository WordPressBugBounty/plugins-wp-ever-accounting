<?php
/**
 * Admin View: Note List
 *
 * @since 2.0.0
 * @package EverAccounting
 * @var Note[] $notes Notes.
 */

use EverAccounting\Models\Note;

defined( 'ABSPATH' ) || exit();

?>

<ul class="eac-notes">
	<?php if ( empty( $notes ) ) : ?>
		<li class="no-items">
			<p><?php esc_html_e( 'No notes found.', 'wp-ever-accounting' ); ?></p>
		</li>
	<?php else : ?>
		<?php foreach ( $notes as $note ) : ?>
			<?php include __DIR__ . '/note-item.php'; ?>
		<?php endforeach; ?>
	<?php endif; ?>
</ul>
