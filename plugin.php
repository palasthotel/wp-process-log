<?php
/**
 * Plugin Name: Process Log
 * Plugin URI: https://palasthotel.de
 * Description: Have a look whats going on with your system.
 * Version: 1.0.1
 * Author: Palasthotel <edward.bock@palasthotel.de>
 * Author URI: https://palasthotel.de
 * Text Domain: process-log
 * Domain Path: /languages
 * Requires at least: 4.0
 * Tested up to: 4.9.8
 * License: http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 * @copyright Copyright (c) 2018, Palasthotel
 * @package Palasthotel\ProcessLog
 */

namespace Palasthotel\ProcessLog;


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

		$this->url = plugin_dir_url(__FILE__);
		$this->path = plugin_dir_path(__FILE__);
		$this->basename = plugin_basename(__FILE__);

		load_plugin_textdomain(
			Plugin::DOMAIN,
			FALSE,
			dirname( plugin_basename( __FILE__ ) ) . '/languages'
		);

		// migration
		require_once dirname(__FILE__)."/inc/migrate/migrate-init.php";
		$this->migrate = new MigrateInit();

		//base functions and classes
		require_once dirname(__FILE__)."/inc/database/db.php";
		require_once dirname(__FILE__)."/inc/database/query-conditions.php";
		require_once dirname(__FILE__)."/inc/database/query.php";

		//WP_User_Query extension
		require_once dirname(__FILE__)."/inc/wp-user-query-extension.php";
		$this->wpUserQueryExtension = new WPUserQueryExtension($this);

		// post query extension
		require_once dirname(__FILE__)."/inc/wp-post-query-extension.php";
		$this->wpPostQueryExtension = new WPPostQueryExtension($this);

		// settings page
		require_once dirname(__FILE__).'/inc/settings.php';
		$this->settings = new Settings($this);

		// adds relations to post
		require_once dirname(__FILE__)."/inc/post.php";

		// post edit meta box
		require_once dirname(__FILE__)."/inc/post-meta-box.php";
		$this->postMetaBox = new PostMetaBox($this);

		require_once dirname(__FILE__)."/inc/user-profile.php";
		$this->userProfile = new UserProfile($this);

		require_once dirname(__FILE__)."/inc/ajax.php";
		$this->ajax = new Ajax($this);

		/**
		 * type and state settings
		 */


		/**
		 * on activate or deactivate plugin
		 */
		register_activation_hook( __FILE__, array( $this, "activation" ) );
	}

	/**
	 * on plugin activation
	 */
	function activation() {
		// create tables
		Database\createTables();
	}
}
Plugin::instance();

require_once dirname(__FILE__).'/public-functions.php';