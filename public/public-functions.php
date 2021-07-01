<?php

use Palasthotel\ProcessLog\Model\ProcessLog;
use Palasthotel\ProcessLog\Plugin;

/**
 * @return Plugin
 */
function process_log_get_plugin(){
	return Plugin::instance();
}

/**
 * @param callable $fn
 *
 * @throws Exception
 */
function process_log_write(callable $fn){
	$log = $fn(ProcessLog::build());
	if(!($log instanceof ProcessLog)) throw new Exception("Must be an instance of ProcessLog.");
	process_log_get_plugin()->writer->addLog($log);
}
