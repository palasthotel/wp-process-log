<?php


namespace Palasthotel\ProcessLog\Component;

use wpdb;

/**
 * @version 0.1.0
 * @property wpdb wpdb
 */
abstract class Database {

	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
		$this->init();
	}

	/**
	 * initialize table names and other properties
	 */
	abstract function init();
	
	public function createTables(){
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	}
}