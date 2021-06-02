<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 07.12.18
 * Time: 11:10
 */

namespace Palasthotel\ProcessLog;

use Palasthotel\ProcessLog\Model\Process;
use Palasthotel\ProcessLog\Model\ProcessLog;

/**
 * @property Plugin plugin
 */
class Writer {

	/**
	 * @var Process|null
	 */
	private $process = null;

	/**
	 * Writer constructor.
	 *
	 * @param Plugin $plugin
	 */
	public function __construct(Plugin $plugin) {
		$this->plugin = $plugin;
		add_action('shutdown', array($this, 'shutdown'));
	}

	/**
	 * @return Process
	 */
	private function getProcess(){
		if($this->process == null){
			$this->process = $this->plugin->database->nextProcess();
		}
		return $this->process;
	}

	/**
	 * @param ProcessLog $log
	 *
	 * @return false|int
	 */
	public function addLog(ProcessLog $log){
		$p = $this->getProcess();
		if(! ($p instanceof Process)){
			\error_log("Process-log: Could not get process instance");
			return false;
		}
		return $this->plugin->database->addLog(
			$log->setProcessId($p->getId())
		);
	}

	/**
	 *
	 */
	public function shutdown(){
		if($this->process != null){
			
		}
	}

}