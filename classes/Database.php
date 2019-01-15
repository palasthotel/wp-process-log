<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 29.11.18
 * Time: 09:51
 */

namespace Palasthotel\ProcessLog;


use mysql_xdevapi\Exception;

/**
 *
 */
class Database {

	/**
	 * @return \QM_DB|\wpdb
	 */
	public static function wpdb() {
		global $wpdb;
		return $wpdb;
	}

	/**
	 * @return array
	 */
	public function getProcessList( $count = 10, $page = 1 ) {

		$fields = array("process_id", "active_user", );
		$tablename = $this->tablenameProcesses();
		$offset    = $count * ( $page - 1 );

		return self::wpdb()->get_results( self::wpdb()->prepare(
			"SELECT id, created, finished, location_url, hostname FROM $tablename ORDER BY process_id DESC LIMIT %d, %d", array($offset, $count)
		) );
	}

	/**
	 * @return Process
	 */
	public function nextProcess(){
		$process = new Process();
		$result = self::wpdb()->insert(
			$this->tablenameProcesses(),
			$process->insertArgs()
		);
		if(!$result){
			throw new Exception("Could not start new process");
		}
		$process->id = self::wpdb()->insert_id;
		return $process;
	}

	/**
	 * @return string
	 */
	public function tablenameProcesses(){
		return self::wpdb()->prefix . "process_logs";
	}

	/**
	 * @return string
	 */
	public function tablenameItems() {
		return self::wpdb()->prefix . "process_log_items";
	}

	/**
	 * @param \Palasthotel\ProcessLog\ProcessLog $log
	 *
	 * @return false|int
	 */
	function startProcess(ProcessLog $log){
		return self::wpdb()->insert(
			$this->tablenameProcesses(),
			$log->insertArgs()
		);
	}

	/**
	 * @param \Palasthotel\ProcessLog\ProcessLog $log
	 *
	 * @return false|int
	 */
	function addLog( ProcessLog $log ) {
		return self::wpdb()->insert(
			$this->tablenameItems(),
			$log->insertArgs()
		);
	}

	/**
	 * create the tables if not exist
	 */
	function createTables() {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$process = $this->tablenameProcesses();
		dbDelta( "CREATE TABLE IF NOT EXISTS $process
		(
		 id bigint(20) unsigned auto_increment,
		 created DATETIME DEFAULT CURRENT_TIMESTAMP,
		 finished DATETIME DEFAULT NULL,
	
		 location_url varchar(255) comment 'where the event happend, url',
		 referer_url varchar(255),
		 hostname varchar(255),
		
		 primary key (id),
		 key (created),
		 key (hostname),
		 key (location_url),
		 key (referer_url)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;" );

		$tablename = $this->tablenameItems();
		dbDelta( "CREATE TABLE IF NOT EXISTS $tablename
		(
		 id bigint(20) unsigned auto_increment,
		 process_id bigint(20) unsigned,
		 created DATETIME DEFAULT CURRENT_TIMESTAMP,
		 
		 event_type varchar(100) NOT NULL,
		 active_user BIGINT,
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



