<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 07.12.18
 * Time: 11:10
 */

namespace Palasthotel\ProcessLog;

class Writer {

	private $process_id = null;

	/**
	 * Writer constructor.
	 *
	 * @param \Palasthotel\ProcessLog\Plugin $plugin
	 */
	public function __construct(Plugin $plugin) {
		$this->plugin = $plugin;
	}

	/**
	 * @return int
	 */
	public function getProcessId(){
		if($this->process_id == null){
			$this->process_id = $this->plugin->database->getNextProcessId();
		}
		return $this->process_id;
	}

	/**
	 * @param ProcessLog $log
	 *
	 * @return false|int
	 */
	public function addLog(ProcessLog $log){
		return $this->plugin->database->addLog(
			$log->setProcessId($this->getProcessId())
		);
	}

}