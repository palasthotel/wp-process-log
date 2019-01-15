<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 29.11.18
 * Time: 09:45
 */

/**
 * @return \Palasthotel\ProcessLog\Plugin
 */
function process_log_get_plugin(){
	return \Palasthotel\ProcessLog\Plugin::instance();
}

/**
 * @param callable $fn
 *
 * @throws \Exception
 */
function process_log_write(callable $fn){
	$log = $fn(\Palasthotel\ProcessLog\ProcessLog::build());
	if(!($log instanceof \Palasthotel\ProcessLog\ProcessLog)) throw new Exception("Must be an instance of ProcessLog.");
	process_log_get_plugin()->writer->addLog($log);
}