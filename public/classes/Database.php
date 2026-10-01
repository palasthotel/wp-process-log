<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 29.11.18
 * Time: 09:51
 */

namespace Palasthotel\ProcessLog;

use Palasthotel\ProcessLog\Model\Process;
use Palasthotel\ProcessLog\Model\ProcessLog;
use Palasthotel\ProcessLog\Model\QueryArgs;

/**
 * @property string $tableLogs
 * @property string $tableLogItems
 */
#[\AllowDynamicProperties]
class Database extends Component\Database {

	public function init() {
		$this->tableLogs = $this->wpdb->prefix . "process_logs";
		$this->tableLogItems = $this->wpdb->prefix . "process_log_items";
	}

	public function queryLogs(QueryArgs $args): array{

		$where = [];
		if(is_int($args->affectedComment)){
			$where[] = "affected_comment = $args->affectedComment";
		}

		if(count($where) > 0){
			$where = "WHERE ".implode(" AND ", $where);
		}

		$query = "SELECT * FROM $this->tableLogs as p
    		LEFT JOIN $this->tableLogItems as i
        	ON (p.id = i.process_id) $where
			ORDER BY p.created DESC, i.created DESC";

		$results = $this->wpdb->get_results($query);
		$processes = [];

		foreach ($results as $result){
			if(!isset($processes[$result->process_id])){
				$processes[$result->process_id] = [];
			}
			$processes[$result->process_id][] = $result;
		}

		return array_values($processes);
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
	 * @return array
	 */
	public function getChangedDataFields(){
		return $this->getDistinct("changed_data_field");
	}

	/**
	 * @param string $col column name
	 *
	 * @return array
	 */
	private function getDistinct($col){
		return $this->wpdb->get_col(
			"SELECT DISTINCT $col FROM $this->tableLogItems"
		);
	}

	/**
	 * Builds the WHERE clause for getProcessList() and countProcesses() from the filters
	 * of the log screen. Every value goes through prepare().
	 *
	 * @param array $filters content_type, event_type, severity, changed_field, query
	 *
	 * @return string an empty string or "WHERE ..."
	 */
	public function processFilterWhere( array $filters ) {
		$wpdb  = $this->wpdb;
		$where = array();

		$type = $filters["content_type"] ?? "";
		if ( in_array( $type, array( "post", "user", "comment", "term" ), true ) ) {
			$where[] = " affected_$type IS NOT NULL ";
		}
		if ( ! empty( $filters["event_type"] ) ) {
			$where[] = $wpdb->prepare( " event_type = %s ", $filters["event_type"] );
		}
		if ( ! empty( $filters["severity"] ) ) {
			$where[] = $wpdb->prepare( " severity = %s ", $filters["severity"] );
		}
		if ( ! empty( $filters["changed_field"] ) ) {
			$where[] = $wpdb->prepare( " changed_data_field = %s ", $filters["changed_field"] );
		}

		$q = $filters["query"] ?? "";
		if ( "" !== $q ) {
			$parts = array();

			if ( intval( $q ) . "" === $q ) {
				$id      = intval( $q );
				$parts[] = " p.active_user = $id ";
				$parts[] = " i.active_user = $id ";
				$parts[] = " i.affected_post = $id ";
				$parts[] = " i.affected_term = $id ";
				$parts[] = " i.affected_user = $id ";
				$parts[] = " i.affected_comment = $id ";
			}

			$like    = "%" . $wpdb->esc_like( $q ) . "%";
			$columns = array(
				"location_url",
				"referer_url",
				"hostname",
				"event_type",
				"note",
				"message",
				"comment",
				"severity",
				"link_url",
				"location_path",
				"changed_data_field",
				"changed_data_value_old",
				"changed_data_value_new",
				"variables",
			);
			foreach ( $columns as $column ) {
				$parts[] = $wpdb->prepare( " $column LIKE %s ", $like );
			}

			$where[] = " ( " . join( " OR ", $parts ) . " ) ";
		}

		return count( $where ) > 0 ? "WHERE " . implode( " AND ", $where ) : "";
	}

	/**
	 * @param string $where from processFilterWhere()
	 *
	 * @return string
	 */
	private function processWhereIn( $where ) {
		if ( empty( $where ) ) {
			return "";
		}
		return "WHERE id IN( SELECT p.id FROM $this->tableLogs as p LEFT JOIN $this->tableLogItems as i ON ( p.id = i.process_id ) $where )";
	}

	/**
	 * @param int $page
	 * @param int $count
	 *
	 * @param string $where from processFilterWhere()
	 *
	 * @param string $order ASC or DESC
	 *
	 * @return array
	 */
	public function getProcessList( $page = 1, $count = 50, $where = "", $order = "DESC" ) {

		$offset = $count * ( $page - 1 );
		$order  = ( "ASC" === $order ) ? "ASC" : "DESC";

		$query = "SELECT id, active_user, created, location_url, referer_url, hostname FROM $this->tableLogs " . $this->processWhereIn( $where ) . " ORDER BY id $order LIMIT %d OFFSET %d";

		return $this->wpdb->get_results(
			$this->wpdb->prepare( $query, $count, $offset )
		);
	}

	/**
	 * @param string $where from processFilterWhere()
	 *
	 * @return int
	 */
	public function countProcesses( $where = "" ) {
		return (int) $this->wpdb->get_var(
			"SELECT COUNT(id) FROM $this->tableLogs " . $this->processWhereIn( $where )
		);
	}

	/**
	 * @param int $process_id
	 *
	 * @return object|null
	 */
	public function getProcess( $process_id ) {
		return $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT * FROM $this->tableLogs WHERE id = %d",
				$process_id
			)
		);
	}

	/**
	 * @param $process_id
	 *
	 * @return string|null
	 */
	public function countLogs($process_id){
		return $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT count(id) from $this->tableLogItems WHERE process_id = %d",
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
		return $this->wpdb->get_col(
			$this->wpdb->prepare(
				"SELECT DISTINCT event_type from $this->tableLogItems WHERE process_id = %d",
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
		return $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT * FROM $this->tableLogItems WHERE process_id = %d ORDER BY id",
				$pid
			)
		);
	}

	/**
	 * @return Process|false
	 */
	public function nextProcess() {
		$process = new Process();
		$result  = $this->wpdb->insert(
			$this->tableLogs,
			$process->insertArgs()
		);
		if ( ! $result ) {
			error_log("wpdb last_error:".$this->wpdb->last_error."\n");
			error_log("Process-log: Cannot insert process");
			return false;
		}
		$process->id = $this->wpdb->insert_id;

		return $process;
	}

	/**
	 * @param ProcessLog $log
	 *
	 * @return false|int
	 */
	function addLog( ProcessLog $log ) {
		$args   = $log->insertArgs();
		$result = $this->wpdb->insert(
			$this->tableLogItems,
			$args
		);

		if(!$result){
			\error_log($this->wpdb->last_error, 4);
			\error_log("Process-log: Cannot add log to process");
		}

		return $result;
	}

	public function clean(){

		// clean expired log items
		$this->wpdb->query("DELETE FROM $this->tableLogItems WHERE expires < unix_timestamp()");

		// clean empty processes
		$sub = "SELECT p.id FROM $this->tableLogs as p ";
		$sub.= "LEFT JOIN $this->tableLogItems as i ON (p.id = i.process_id) ";
		$sub.= "WHERE i.process_id IS NULL";

		// wrap it (important step!)
		$sub = "SELECT * FROM ( $sub ) as tmp";

		$this->wpdb->query("DELETE FROM $this->tableLogs WHERE id IN ( $sub )");

	}

	/**
	 * create the tables if not exist
	 */
	public function createTables() {
		parent::createTables();

		$prefix = $this->wpdb->prefix;
		$table = $this->tableLogs;

		\dbDelta( "CREATE TABLE IF NOT EXISTS $table
		(
		 id bigint(20) unsigned auto_increment,
		 created DATETIME NOT NULL,

		 active_user BIGINT(20),
		 location_url varchar(190) comment 'where the event happend, url',
		 referer_url varchar(190),
		 hostname varchar(190),

		 primary key (id),
		 key (created),
		 key (active_user),
		 key (hostname),
		 key (location_url),
		 key (referer_url)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;" );



		\dbDelta( "CREATE TABLE IF NOT EXISTS $this->tableLogItems
		(
		 id bigint(20) unsigned auto_increment,
		 process_id bigint(20) unsigned,
		 created DATETIME NOT NULL,

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

		 changed_data_field VARCHAR(100),
		 changed_data_value_old LONGTEXT,
		 changed_data_value_new LONGTEXT,

		 variables LONGTEXT,

		 blobdata BLOB,

		 primary key (id),
		 key (created),
		 key (event_type),
		 key (active_user),
		 key (severity),
		 key (affected_post),
		 key (affected_term),
		 key (affected_user),
		 key (affected_comment),
		 key (expires),
		 key (changed_data_field),
    
    	 CONSTRAINT `{$prefix}process_logs_ref` FOREIGN KEY (`process_id`) REFERENCES `$table` (`id`) ON DELETE CASCADE

		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;" );
	}


}



