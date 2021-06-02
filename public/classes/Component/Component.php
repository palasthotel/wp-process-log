<?php


namespace Palasthotel\ProcessLog\Component;

/**
 * Class Component
 *
 * @property \Palasthotel\ProcessLog\Plugin plugin
 */
abstract class Component {
	/**
	 * _Component constructor.
	 *
	 * @param \Palasthotel\ProcessLog\Plugin $plugin
	 */
	public function __construct(\Palasthotel\ProcessLog\Plugin $plugin) {
		$this->plugin = $plugin;
		$this->onCreate();
	}

	/**
	 * overwrite this method in component implementations
	 */
	abstract function onCreate();
}