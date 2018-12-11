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
		wp_send_json(array(
			"page" => $page,
			"list" => $this->plugin->database->getProcessesList($page),
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