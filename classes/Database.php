<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 29.11.18
 * Time: 09:51
 */

namespace Palasthotel\ProcessLog;

use Exception;

/**
 *
 */
class Database {

	/**
	 * @return \wpdb
	 */
	public static function wpdb() {
		global $wpdb;

		return $wpdb;
	}

	/**
	 * @return string
	 */
	public static function tablenameProcesses() {
		return self::wpdb()->prefix . "process_logs";
	}

	/**
	 * @return string
	 */
	public static function tablenameItems() {
		return self::wpdb()->prefix . "process_log_items";
	}

	/**
	 * @return array
	 */
	public function getSeverities(){
		return $this->getDistinct("severity");
	}

	/**
	 * @return array
	 */
	public function getEventTypes(){
		return $this->getDistinct("event_type");
	}

	/**
	 * @param string $col column name
	 *
	 * @return array
	 */
	private function getDistinct($col){
		return self::wpdb()->get_col("SELECT DISTINCT $col FROM ".self::tablenameItems());
	}

	/**
	 * @param int $page
	 * @param int $count
	 *
	 * @param string $where
	 *
	 * @return array
	 */
	public function getProcessList( $page = 1, $count = 50, $where = "" ) {

		$tableProcesses = self::tablenameProcesses();
		$tableItems = self::tablenameItems();
		$offset    = $count * ( $page - 1 );

		$where_in = "";
		if(!empty($where)){
			$where_in = "WHERE id IN( SELECT p.id FROM $tableProcesses as p LEFT JOIN $tableItems as i ON ( p.id = i.process_id ) $where )";
		}

		$query = "SELECT id, active_user, created, location_url, hostname FROM $tableProcesses $where_in ORDER BY id DESC LIMIT %d OFFSET %d";

		return self::wpdb()->get_results(
			self::wpdb()->prepare( $query, $count, $offset )
		);
	}

	/**
	 * @param $process_id
	 *
	 * @return string|null
	 */
	public function countLogs($process_id){
		return self::wpdb()->get_var(
			self::wpdb()->prepare(
				"SELECT count(id) from ".self::tablenameItems()." WHERE process_id = %d",
				$process_id
			)
		);
	}

	/**
	 * @param $process_id
	 *
	 * @return array|null
	 */
	public function getProcessEventTypes($process_id){
		return self::wpdb()->get_col(
			self::wpdb()->prepare(
				"SELECT DISTINCT event_type from ".self::tablenameItems()." WHERE process_id = %d",
				$process_id
			)
		);
	}

	/**
	 * @param int $pid
	 *
	 * @return array
	 */
	public function getProcessLogs( $pid ) {
		return self::wpdb()->get_results(
			self::wpdb()->prepare(
				"SELECT * FROM " . self::tablenameItems() . " WHERE process_id = %d",
				$pid
			)
		);
	}

	/**
	 * @return Process|false
	 */
	public function nextProcess() {
		$process = new Process();
		$result  = self::wpdb()->insert(
			self::tablenameProcesses(),
			$process->insertArgs()
		);
		if ( ! $result ) {
			return false;
		}
		$process->id = self::wpdb()->insert_id;

		return $process;
	}

	/**
	 * @param \Palasthotel\ProcessLog\ProcessLog $log
	 *
	 * @return false|int
	 */
	function addLog( ProcessLog $log ) {
		$args   = $log->insertArgs();
		$result = self::wpdb()->insert(
			self::tablenameItems(),
			$args
		);

		return $result;
	}


	/**
	 * create the tables if not exist
	 */
	function createTables() {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$process = self::tablenameProcesses();
		dbDelta( "CREATE TABLE IF NOT EXISTS $process
		(
		 id bigint(20) unsigned auto_increment,
		 created DATETIME DEFAULT CURRENT_TIMESTAMP,
		 
		 active_user BIGINT(20),
		 location_url varchar(255) comment 'where the event happend, url',
		 referer_url varchar(255),
		 hostname varchar(255),
		
		 primary key (id),
		 key (created),
		 key (active_user),
		 key (hostname),
		 key (location_url),
		 key (referer_url)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;" );

		$tablename = self::tablenameItems();

		dbDelta( "CREATE TABLE IF NOT EXISTS $tablename
		(
		 id bigint(20) unsigned auto_increment,
		 process_id bigint(20) unsigned,
		 created DATETIME DEFAULT CURRENT_TIMESTAMP,
		 
		 event_type varchar(100) NOT NULL,
		 active_user BIGINT(20),
		 message TEXT comment 'Message from code',
		 note TEXT comment 'Comment from user that triggered event. Comparable to git commit message',
		 comment TEXT comment 'after creation comments in backend',
		 severity varchar(100) NOT NULL,
		 link_url varchar(255) comment 'link to the result of the event',
		 location_path varchar(255) comment 'where the event happend, file system path',
		 affected_post BIGINT comment 'post id that was affected by the event',
		 affected_term BIGINT comment 'term id that was affected by the event',
		 affected_user BIGINT comment 'user id that was affected by the event',
		 affected_comment BIGINT comment 'comment id that was affected by the event',
		 expires BIGINT comment 'timestamp when to clean up this log entry',
		 
		 changed_data_field VARCHAR(255),
		 changed_data_value_old TEXT,
		 changed_data_value_new TEXT,
		 
		 variables text,
		 
		 blobdata BLOB,	
		 	 
		 primary key (id),
		 foreign key (process_id) REFERENCES $process(id) ,
		 key (created),
		 key (event_type),
		 key (active_user),
		 key (severity),
		 key (affected_post),
		 key (affected_term),
		 key (affected_user),
		 key (affected_comment),
		 key (expires),
		 key (changed_data_field)
		 
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;" );
	}


}



