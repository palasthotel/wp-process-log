<?php


namespace Palasthotel\ProcessLog\View;


use Palasthotel\ProcessLog\Component\Component;
use Palasthotel\ProcessLog\Model\QueryArgs;
use Palasthotel\ProcessLog\Plugin;

class CommentMetaBoxView extends Component {

	function onCreate() {
		add_action('add_meta_boxes', function($post_type){
			if("comment" === $post_type){
				add_meta_box(
					"process-logs",
					__("Process logs", Plugin::DOMAIN),
					[$this, 'render'],
					"comment",
					"normal"
				);
			}
		});
	}


	function render($comment){
		?>
		<style>
			.process-log__processes + .process-log__processes{
				border-top: 1px solid black;
			}
            .process-log__processes {
	            padding: 10px 0;
            }
            .process-log__processes--header{
                background: #efefef;
                padding: 8px;
            }
            .process-log__logs{
                padding: 8px;
                border: 1px solid #efefef;
            }
            .process-log__changes td:nth-child(2){
                width: 40px;
                text-align: center;
            }
		</style>
		<?php

		$args = new QueryArgs();
		$args->affectedComment = $comment->comment_ID;
		$processes = $this->plugin->database->queryLogs($args);

		echo "<ul class='process-log__processes'>";
		foreach ($processes as $process){
			echo "<li>";
			echo "<div class='process-log__processes--header'>";
			echo $process[0]->created;
			echo " by user ";
			$user_id = $process[0]->active_user;
			$user = get_userdata($user_id);
			if($user instanceof \WP_User){
				$url = get_edit_profile_url($user_id);
				echo "<a href='$url'>$user->display_name</a>";
			} else {
			    echo "(cannot find user $user_id)";
			}

			echo "</div>";
			echo "<ul class='process-log__logs'>";
			foreach ($process as $log){
				echo "<li>";
				echo "<div><strong>$log->event_type:</strong> $log->changed_data_field</div>";
				?>
                <table class="process-log__changes">
                    <tr>
                        <td><?php echo $log->changed_data_value_old; ?></td>
                        <td>→</td>
                        <td><?php echo $log->changed_data_value_new; ?></td>
                    </tr>
                </table>
                <?php
				echo "</li>";
			}
			echo "</ul>";
			echo "</li>";
		}
		echo "</ul>";

	}
}