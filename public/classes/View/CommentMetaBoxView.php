<?php


namespace Palasthotel\ProcessLog\View;


use Palasthotel\ProcessLog\Component\Component;
use Palasthotel\ProcessLog\MenuPage;
use Palasthotel\ProcessLog\Model\QueryArgs;

defined( 'ABSPATH' ) || exit;

#[\AllowDynamicProperties]
class CommentMetaBoxView extends Component {

	function onCreate() {
		add_action('add_meta_boxes', function($post_type){
			if("comment" === $post_type){
				add_meta_box(
					"process-logs",
					__( 'Process logs', 'process-log' ),
					[$this, 'render'],
					"comment",
					"normal"
				);
			}
		});
	}

	/**
	 * The entries of this comment, in the table markup core uses for its own lists.
	 *
	 * @param \WP_Comment $comment
	 */
	function render($comment){
		$args = new QueryArgs();
		$args->affectedComment = intval( $comment->comment_ID );
		$processes = $this->plugin->database->queryLogs($args);

		if ( empty( $processes ) ) {
			echo '<p>' . esc_html__( 'Nothing has been logged for this comment.', 'process-log' ) . '</p>';
			return;
		}

		$canSeeLog = current_user_can( MenuPage::CAPABILITY );
		?>
		<table class="widefat striped">
			<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'Date', 'process-log' ); ?></th>
				<th scope="col"><?php esc_html_e( 'User', 'process-log' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Event', 'process-log' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Changed field', 'process-log' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Before', 'process-log' ); ?></th>
				<th scope="col"><?php esc_html_e( 'After', 'process-log' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ( $processes as $process ) : ?>
				<?php foreach ( $process as $log ) : ?>
					<tr>
						<td>
							<?php
							$date = Format::date( $log->created );
							if ( $canSeeLog ) {
								printf(
									'<a href="%s">%s</a>',
									esc_url( add_query_arg( array( 'page' => MenuPage::SLUG, 'process' => (int) $log->process_id ), admin_url( 'tools.php' ) ) ),
									$date // escaped by Format::date()
								);
							} else {
								echo $date; // escaped by Format::date()
							}
							?>
						</td>
						<td><?php echo Format::user( $log->active_user ); // escaped by Format::user() ?></td>
						<td><?php echo esc_html( $log->event_type ); ?></td>
						<td><?php echo empty( $log->changed_data_field ) ? '' : '<code>' . esc_html( $log->changed_data_field ) . '</code>'; ?></td>
						<td><?php echo empty( $log->changed_data_field ) ? '' : Format::value( $log->changed_data_value_old ); // escaped by Format::value() ?></td>
						<td><?php echo empty( $log->changed_data_field ) ? '' : Format::value( $log->changed_data_value_new ); // escaped by Format::value() ?></td>
					</tr>
				<?php endforeach; ?>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php
	}
}
