<?php
/**
 * Plugin Name: Process Log
 * Plugin URI: https://palasthotel.de
 * Description: Have a look whats going on with your system.
 * Version: 1.0.0
 * Author: Palasthotel <edward.bock@palasthotel.de>
 * Author URI: https://palasthotel.de
 * Text Domain: process-log
 * Domain Path: /languages
 * Requires at least: 4.0
 * Tested up to: 4.9.8
 * License: http://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @copyright Copyright (c) 2018, Palasthotel
 * @package Palasthotel\ProcessLog
 */

namespace Palasthotel\ProcessLog;


/**
 * @property string url
 * @property string path
 * @property string basename
 * @property Writer writer
 * @property Database database
 * @property Process process
 */
class Plugin {

	const DOMAIN = "process-log";

	/**
	 * @var Plugin|null
	 */
	private static $instance = null;
	/**
	 * @return Plugin
	 */
	static function instance(){
		if(self::$instance == null) self::$instance = new Plugin();
		return self::$instance;
	}

	/**
	 * Plugin constructor.
	 */
	private function __construct() {

		load_plugin_textdomain(
			Plugin::DOMAIN,
			FALSE,
			dirname( plugin_basename( __FILE__ ) ) . '/languages'
		);

		$this->url = plugin_dir_url(__FILE__);
		$this->path = plugin_dir_path(__FILE__);
		$this->basename = plugin_basename(__FILE__);

		require_once dirname(__FILE__)."/vendor/autoload.php";

		$this->database = new Database();
		$this->writer = new Writer($this);
		$this->process = new Process($this);

		/**
		 * on activate or deactivate plugin
		 */
		register_activation_hook( __FILE__, array( $this, "activation" ) );
		if(WP_DEBUG){
			add_action('init', array($this, 'activation'));
		}
	}

	/**
	 * on plugin activation
	 */
	function activation() {
		// create tables
		$this->database->createTables();
	}
}
Plugin::instance();

require_once dirname(__FILE__).'/public-functions.php';