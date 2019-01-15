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
		$page = (isset($_GET["page"]))? intval($_GET["page"]): 1;
		$logs = $this->plugin->database->getProcessesList($page);
		wp_send_json(array(
			"page" => $page,
			"list" => $logs,
			"users" => $this->getUsersOfLogs($logs),
		));

	}

	public function process_logs(){
		$pid = intval($_GET["pid"]);
		$logs = $this->plugin->database->getProcessLogs($pid);
		wp_send_json(array(
			"pid" => $pid,
			"list" => $logs,
			"posts" => $this->getPostsOfLogs($logs),
			"users" => $this->getUsersOfLogs($logs),
		));
	}

	/**
	 * @param array $logs
	 *
	 * @return array
	 */
	private function getUsersOfLogs($logs){
		$active_users = array();
		foreach ($logs as $item){
			if( intval($item->active_user) > 0 && !isset($active_users[$item->active_user]) ){
				$user = $this->getUserIf( $item->active_user );
				if($user !== false) $active_users[$item->active_user] = $user;
			}
			if( intval($item->affected_user) > 0 && !empty($item->affected_user) ){
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


}