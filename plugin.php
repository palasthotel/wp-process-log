<?php
/**
 * Plugin Name: Process Log
 * Plugin URI: https://palasthotel.de
 * Description: Have a look whats going on with your system.
 * Version: 1.1.2
 * Author: Palasthotel <edward.bock@palasthotel.de>
 * Author URI: https://palasthotel.de
 * Text Domain: process-log
 * Domain Path: /languages
 * Requires at least: 4.0
 * Tested up to: 5.2.2
 * License: http://www.gnu.org/licenses/gpl-3.0.html GPLv3
 *
 * @copyright Copyright (c) 2018, Palasthotel
 * @package Palasthotel\ProcessLog
 */

namespace Palasthotel\ProcessLog;

define( "PROCESS_LOG_DIR", dirname( __FILE__ ) );
define( "PROCESS_LOG_HANDLERS_DIR", dirname( __FILE__ ) . "/classes/Process/" );

/**
 * @property string url
 * @property string path
 * @property string basename
 * @property Writer writer
 * @property Database database
 * @property Watcher $watcher
 * @property MenuPage menuPage
 * @property Ajax ajax
 * @property Schedule schedule
 */
class Plugin {

	const DOMAIN = "process-log";

	/**
	 * schedules
	 */
	const SCHEDULE_CLEAN = "process_log_clean";

	/**
	 * event types
	 */
	CONST EVENT_TYPE_ERROR = "error";
	CONST EVENT_TYPE_GENERAL = "event";
	const EVENT_TYPE_CREATE = "create";
	const EVENT_TYPE_UPDATE = "update";
	const EVENT_TYPE_DELETE = "delete";
	CONST EVENT_TYPE_USER_REGISTER = "user_register";

	/**
	 * severity types
	 */
	const SEVERITY_TYPE_FATAL = "fatal";
	const SEVERITY_TYPE_INFO = "info";

	/**
	 * filters
	 */
	const FILTER_LOG_ITEM_EXPIRES = "process_log_expires";
	const FILTER_CORE_WATCHERS_ACTIVE = "process_log_core_watchers_active";
	const FILTER_IS_USER_WATCHER_ACTIVE = "process_log_is_user_watcher_active";
	const FILTER_IS_POST_WATCHER_ACTIVE = "process_log_is_post_watcher_active";
	const FILTER_IS_OPTION_WATCHER_ACTIVE = "process_log_is_option_watcher_active";
	const FILTER_IGNORE_POST_META = "process_log_ignore_post_meta";
	const FILTER_IS_CUR_WATCHER_ACTIVE = "process_log_is_content_user_relations_watcher_active";

	/**
	 * @var Plugin|null
	 */
	private static $instance = NULL;

	/**
	 * @return Plugin
	 */
	static function instance() {
		if ( self::$instance == NULL ) {
			self::$instance = new Plugin();
		}

		return self::$instance;
	}

	/**
	 * Plugin constructor.
	 */
	private function __construct() {

		load_plugin_textdomain(
			Plugin::DOMAIN,
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/languages'
		);

		$this->url      = plugin_dir_url( __FILE__ );
		$this->path     = plugin_dir_path( __FILE__ );
		$this->basename = plugin_basename( __FILE__ );

		require_once dirname( __FILE__ ) . "/vendor/autoload.php";

		$this->database = new Database();
		$this->writer   = new Writer( $this );
		$this->watcher  = new Watcher( $this );
		$this->menuPage = new MenuPage( $this );
		$this->settings = new Settings( $this );
		$this->ajax     = new Ajax( $this );
		$this->schedule = new Schedule($this);

		/**
		 * on activate or deactivate plugin
		 */
		register_activation_hook( __FILE__, array( $this, "activation" ) );
		register_deactivation_hook( __FILE__, array( $this, "deactivation" ) );
		if ( WP_DEBUG ) {
			// for development purpose
			add_action( 'init', array( $this, 'activation' ) );
		}
	}

	/**
	 * on plugin activation
	 */
	function activation() {
		// create tables
		$this->database->createTables();
		$this->schedule->start();
	}

	function deactivation(){
		$this->schedule->stop();
	}
}

Plugin::instance();

require_once dirname( __FILE__ ) . '/public-functions.php';