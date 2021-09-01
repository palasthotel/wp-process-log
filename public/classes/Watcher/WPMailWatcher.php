<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 07.12.18
 * Time: 16:37
 */

namespace Palasthotel\ProcessLog\Watcher;


use Palasthotel\ProcessLog\Model\ProcessLog;
use Palasthotel\ProcessLog\Plugin;
use Palasthotel\ProcessLog\Writer;

/**
 * @property Writer writer
 */
class WPMailWatcher {

	/**
	 * User constructor.
	 *
	 * @param Plugin $plugin
	 */
	public function __construct( Plugin $plugin ) {
		$this->writer = $plugin->writer;
		add_filter('wp_mail', [$this, "pre_wp_mail"], 99);

	}

	/**
	 * @return boolean
	 */
	public function isActive() {
		return apply_filters( Plugin::FILTER_IS_MAIL_WATCHER_ACTIVE, true );
	}

	public function pre_wp_mail($attrs){
		$log = ProcessLog::build()
		          ->setEventType(Plugin::EVENT_WP_MAIL)
		          ->setMessage(json_encode($attrs));
		$this->writer->addLog($log);
		return $attrs;
	}

}
