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
 * Tested up to: 5.0.3
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
 */
class Plugin {

	const DOMAIN = "process-log";

	/**
	 * filters
	 */
	const FILTER_CORE_WATCHERS_ACTIVE = "process_log_core_watchers_active";
	const FILTER_IS_USER_WATCHER_ACTIVE = "process_log_is_user_watcher_active";
	const FILTER_IS_POST_WATCHER_ACTIVE = "process_log_is_post_watcher_active";
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
		$this->ajax     = new Ajax( $this );

		/**
		 * on activate or deactivate plugin
		 */
		register_activation_hook( __FILE__, array( $this, "activation" ) );
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
	}
}

Plugin::instance();

require_once dirname( __FILE__ ) . '/public-functions.php';