<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 11.12.18
 * Time: 09:02
 */

namespace Palasthotel\ProcessLog;


/**
 * @property string|void ajaxurl
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
		$list = $this->plugin->database->getProcessesList($page);
		$active_users = array();
		foreach ($list as $item){
			if(!isset($active_users[$item->active_user]) && intval($item->active_user) > 0){
				$user = get_user_by("ID", $item->active_user);
				if($user instanceof \WP_User){
					$active_users[$item->active_user] = array(
						"ID" => $user->ID,
						"display_name" => $user->display_name,
						"edit_link" => get_edit_user_link($user->ID),
					);
				}

			}
		}
		wp_send_json(array(
			"page" => $page,
			"list" => $list,
			"users" => $active_users,
		));

	}

	public function process_logs(){
		$pid = intval($_GET["pid"]);
		wp_send_json(array(
			"pid" => $pid,
			"list" => $this->plugin->database->getProcessLogs($pid),
		));
	}


}