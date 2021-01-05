<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 11.12.18
 * Time: 09:02
 */

namespace Palasthotel\ProcessLog;


/**
 * @property string ajaxurl
 * @property Plugin plugin
 */
class Ajax {
	const AJAX_ACTION = "process_logs";
	public function __construct(Plugin $plugin) {
		$this->plugin = $plugin;
		$this->ajaxurl = admin_url( 'admin-ajax.php' );
		add_action('wp_ajax_processes_list', array($this, 'processes_list'));
		add_action('wp_ajax_process_logs', array($this, 'process_logs'));
	}

	public function processes_list(){
		$page = (isset($_REQUEST["page"]))? intval($_REQUEST["page"]): 1;

		$where = array();

		if(isset($_REQUEST["process_content_type"]) && !empty($_REQUEST["process_content_type"]) ){
			$type = sanitize_text_field($_REQUEST["process_content_type"]);
			if(in_array($type, array("post", "user", "comment", "term"))){
				$where[] = " affected_$type IS NOT NULL ";
			}
		}
		if(isset($_REQUEST["process_event_type"]) && !empty($_REQUEST["process_event_type"]) ){
			$type = sanitize_text_field($_REQUEST["process_event_type"]);
			$where[] = " event_type = '$type' ";
		}
		if(isset($_REQUEST["process_severity_type"]) && !empty($_REQUEST["process_severity_type"]) ){
			$type = sanitize_text_field($_REQUEST["process_severity_type"]);
			$where[] = " severity = '$type' ";
		}

		if(isset($_REQUEST["process_changed_data_field"]) && !empty($_REQUEST["process_changed_data_field"])){
			$field = sanitize_text_field($_REQUEST["process_changed_data_field"]);
			$where[] = " changed_data_field = '$field' ";
		}

		if(isset($_REQUEST["process_event_query"]) && !empty($_REQUEST["process_event_query"]) ){
			$q = sanitize_text_field($_REQUEST["process_event_query"]);

			$parts = array();

			if(intval($q)."" === $q){
				$parts[] = " p.active_user = $q ";
				$parts[] = " i.active_user = $q ";
				$parts[] = " i.affected_post = $q ";
				$parts[] = " i.affected_term = $q ";
				$parts[] = " i.affected_user = $q ";
				$parts[] = " i.affected_comment = $q ";

			}

			$parts[] = " location_url LIKE '%$q%' ";
			$parts[] = " referer_url LIKE '%$q%' ";
			$parts[] = " hostname LIKE '%$q%' ";
			$parts[] = " event_type LIKE '%$q%' ";
			$parts[] = " note LIKE '%$q%' ";
			$parts[] = " event_type LIKE '%$q%' ";
			$parts[] = " message LIKE '%$q%' ";
			$parts[] = " comment LIKE '%$q%' ";
			$parts[] = " severity LIKE '%$q%' ";
			$parts[] = " message LIKE '%$q%' ";
			$parts[] = " link_url LIKE '%$q%' ";
			$parts[] = " location_path LIKE '%$q%' ";
			$parts[] = " changed_data_field LIKE '%$q%' ";
			$parts[] = " changed_data_value_old LIKE '%$q%' ";
			$parts[] = " changed_data_value_new LIKE '%$q%' ";
			$parts[] = " variables LIKE '%$q%' ";

			$where[] = " ( ".join(" OR ", $parts )." ) ";

		}

		$where_param = "";
		if(count($where)>0){
			$where_param = "WHERE ".implode(" AND ", $where);
		}

		$database = $this->plugin->database;

		$logs = $database->getProcessList($page, 50, $where_param);
		wp_send_json(array(
			"page" => $page,
			"list" => array_map(function($process) use ( $database ) {
				$process->logs_count = $database->countLogs($process->id);
				$process->event_types = $database->getProcessEventTypes($process->id);
				return $process;
			}, $logs),
			"users" => $this->getUsersOfLogs($logs),
		));

	}

	public function process_logs(){
		$pid = intval($_REQUEST["pid"]);
		$logs = $this->plugin->database->getProcessLogs($pid);
		foreach ($logs as $i => $log){
			foreach ($log as $key => $value){
				$obj = json_decode($value);
				if($obj !== null && json_last_error() == JSON_ERROR_NONE){
					$logs[$i]->{$key} = json_encode($obj, JSON_PRETTY_PRINT);
				}
			}
		}
		wp_send_json(
			array(
				"pid" => $pid,
				"list" => $logs,
				"posts" => $this->getPostsOfLogs($logs),
				"comments" => $this->getCommentsOfLogs($logs),
				"users" => $this->getUsersOfLogs($logs),
			)
		);
	}

	/**
	 * @param array $logs
	 *
	 * @return array
	 */
	private function getUsersOfLogs($logs){
		$active_users = array();
		foreach ($logs as $item){
			if( isset($item->active_user) && intval($item->active_user) > 0 && !isset($active_users[$item->active_user]) ){
				$user = $this->getUserIf( $item->active_user );
				if($user !== false) $active_users[$item->active_user] = $user;
			}
			if( isset($item->affected_user) && !empty($item->affected_user)  && intval($item->affected_user) > 0  && !isset($active_users[$item->affected_user])){
				$user = $this->getUserIf($item->affected_user);
				if($user !== false) $active_users[$item->affected_user] = $user;
			}
		}
		return $active_users;
	}

	/**
	 * @param $user_id
	 *
	 * @return array|bool
	 */
	private function getUserIf($user_id){
		$user = get_user_by("ID", $user_id);
		if($user instanceof \WP_User){
			return array(
				"ID" => $user->ID,
				"display_name" => $user->display_name,
				"edit_link" => get_edit_user_link($user->ID),
			);
		}
		return false;
	}

	/**
	 * @param array $logs
	 *
	 * @return array
	 */
	private function getPostsOfLogs($logs){
		$affected_posts = array();
		foreach ($logs as $item) {

			if(isset($affected_posts[$item->affected_post])) continue;

			if ( ! empty( $item->affected_post ) && intval( $item->affected_post ) > 0 ) {
				$post = get_post( $item->affected_post );
				if ( $post instanceof \WP_Post ) {
					$affected_posts[ $item->affected_post ] = array(
						"ID"         => $post->ID,
						"post_title" => $post->post_title,
						"edit_link"  => get_edit_post_link( $post->ID )
					);
				} else {
					$affected_posts[ $item->affected_post ] = array(
						"ID"         => $item->affected_post,
						"post_title" => "Not found",
						"edit_link"  => NULL,
					);
				}
			}
		}
		return $affected_posts;
	}

	/**
	 * @param array $logs
	 *
	 * @return array
	 */
	private function getCommentsOfLogs($logs){
		$affected_comments = array();
		foreach ($logs as $item) {

			if(isset($affected_comments[$item->affected_comment])) continue;

			if ( ! empty( $item->affected_comment ) && intval( $item->affected_comment ) > 0 ) {
				$comment = get_comment( $item->affected_comment );
				if ( $comment instanceof \WP_Comment ) {
					$affected_comments[ $item->affected_comment ] = array(
						"ID"         => $comment->comment_ID,
						"edit_link" => get_edit_comment_link($comment),
					);
				} else {
					$affected_comments[ $item->affected_comment ] = array(
						"ID"         => $item->affected_comment,
						"edit_link"  => NULL,
					);
				}
			}
		}
		return $affected_comments;
	}


}
