<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 07.12.18
 * Time: 11:10
 */

namespace Palasthotel\ProcessLog;

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
		return $this->plugin->database->addLog(
			$log->setProcessId($this->getProcess()->getId())
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