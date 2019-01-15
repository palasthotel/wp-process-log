<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 2019-01-15
 * Time: 11:09
 */

namespace Palasthotel\ProcessLog;


/**
 * @property Plugin plugin
 */
class SettingsPage {
	public function __construct(Plugin $plugin) {
		$this->plugin = $plugin;
		add_action('admin_menu', array($this, 'menu_pages'));
	}
	public function menu_pages() {
		add_submenu_page(
			'tools.php',
			__('Process Log', Plugin::DOMAIN),
			__('Process Log', Plugin::DOMAIN),
			'manage_options',
			'process-log',
			array(
				$this,
				"render",
			)
		);
	}

	public function render(){
		$list = $this->plugin->database->getProcessList();
		var_dump($list);
		?><p>TEST</p><?php
	}
}