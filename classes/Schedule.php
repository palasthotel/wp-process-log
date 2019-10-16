<?php


namespace Palasthotel\ProcessLog;


/**
 * @property Plugin plugin
 */
class Schedule {

	public function __construct(Plugin $plugin) {
		$this->plugin = $plugin;
		add_action( 'admin_init', array( $this, 'start' ) );
		add_action(Plugin::SCHEDULE_CLEAN, array($this, 'execute'));
	}

	/**
	 * @return false|int
	 */
	public function isScheduled() {
		return wp_next_scheduled( Plugin::SCHEDULE_CLEAN );
	}

	/**
	 * start scheduled event
	 */
	public function start() {
		if ( ! $this->isScheduled() ) {
			wp_schedule_event( time(), 'hourly', Plugin::SCHEDULE_CLEAN );
		}
	}

	/**
	 * stop scheduled action
	 */
	public function stop() {
		wp_clear_scheduled_hook( Plugin::SCHEDULE_CLEAN );
	}

	public function execute(){
		$this->plugin->database->clean();
	}
}