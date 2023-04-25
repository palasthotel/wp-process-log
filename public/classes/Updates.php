<?php

namespace Palasthotel\ProcessLog;

use Palasthotel\ProcessLog\Component\Update;

class Updates extends Update {

	public function __construct() {
		add_action('admin_init', function(){
			$this->checkUpdates();
		});
	}

	function getVersion(): int {
		return 2;
	}

	function setCurrentVersion( int $version ) {
		update_option(Plugin::DOMAIN."_version", $version);
	}

	function getCurrentVersion(): int {
		return intval(get_option(Plugin::DOMAIN."_version", "0"));
	}

	function update_1(){
		global $wpdb;
		$table = Plugin::instance()->database->tableLogItems;
		$wpdb->query("ALTER TABLE $table CHANGE changed_data_value_old changed_data_value_old LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT ''" );
		$wpdb->query("ALTER TABLE $table CHANGE changed_data_value_new changed_data_value_new LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT ''" );
		$wpdb->query("ALTER TABLE $table CHANGE variables variables LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT ''" );
	}

	function update_2(){
		global $wpdb;
		$table = Plugin::instance()->database->tableLogItems;
		$logsTable = Plugin::instance()->database->tableLogs;
		$tableDeprecated = $table."_deprecated";
		$wpdb->query("RENAME TABLE $table to $tableDeprecated;");
		Plugin::instance()->database->createTables();
		$wpdb->query("INSERT INTO $table SELECT * FROM $tableDeprecated WHERE process_id IN (SELECT id FROM $logsTable);");
		$wpdb->query("DROP TABLE $tableDeprecated");
	}

}
