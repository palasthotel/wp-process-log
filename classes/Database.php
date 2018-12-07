<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 29.11.18
 * Time: 09:51
 */

namespace Palasthotel\ProcessLog;


/**
 *
 */
class Database{

	/**
	 * @return int
	 */
	public function getNextProcessId(){
		global $wpdb;
		$tablename = $this->tablename();
		$id = $wpdb->get_var(
			"SELECT max(process_id) FROM $tablename GROUP BY process_id ORDER BY process_id DESC LIMIT 1"
		);
		return intval($id)+1;
	}

	public function tablename(){
		global $wpdb;
		return $wpdb->prefix."process_log";
	}


	/**
	 * @param \Palasthotel\ProcessLog\ProcessLog $log
	 *
	 * @return false|int
	 */
	function addLog(ProcessLog $log){
		global $wpdb;
		return $wpdb->insert(
			$this->tablename(),
			$log->insertArgs()
		);
	}

	/**
	 * create the tables if not exist
	 */
	function createTables(){
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		$tablename = $this->tablename();
		dbDelta("CREATE TABLE IF NOT EXISTS $tablename
		(
		 id bigint(20) unsigned auto_increment,
		 process_id bigint(20) unsigned,
		 created DATETIME DEFAULT CURRENT_TIMESTAMP,
		 
		 type varchar(100) NOT NULL,
		 active_user BIGINT,
		 message TEXT comment 'Message from code',
		 note TEXT comment 'Comment from user that triggered event. Comparable to git commit message',
		 comment TEXT comment 'after creation comments in backend',
		 severity varchar(100) NOT NULL,
		 link_url varchar(255) comment 'link to the result of the event',
		 location_url varchar(255) comment 'where the event happend, url',
		 location_path varchar(255) comment 'where the event happend, file system path',
		 referer_url varchar(255),
		 hostname varchar(255),
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
		 key (process_id),
		 key (created),
		 key (type),
		 key (active_user),
		 key (severity),
		 key (hostname),
		 key (affected_post),
		 key (affected_term),
		 key (affected_user),
		 key (affected_comment),
		 key (expires),
		 key (changed_data_field)
		 
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
	}



}



