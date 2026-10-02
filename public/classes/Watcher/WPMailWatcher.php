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
#[\AllowDynamicProperties]
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
		if ( ! $this->isActive() ) {
			return $attrs;
		}
		$logged = $attrs;
		if ( is_array( $logged ) && isset( $logged['message'] ) && is_string( $logged['message'] ) ) {
			$logged['message'] = self::redact( $logged['message'] );
		}
		$log = ProcessLog::build()
		          ->setEventType(Plugin::EVENT_WP_MAIL)
		          ->setMessage(json_encode($logged));
		$this->writer->addLog($log);
		return $attrs;
	}

	/**
	 * The one-time secrets core mails carry in their links: the password reset and
	 * new user key (wp-login.php?action=rp&key=), the signup activation key
	 * (wp-activate.php?key=), the personal data request key (confirm_key=) and the
	 * e-mail change hashes (newuseremail=, adminhash=). Whoever reads the log could
	 * otherwise use them - on a multisite network a site administrator could reset
	 * the password of a super admin. Only the copy in the log is changed; the mail
	 * itself is sent as it was.
	 *
	 * @param string $message
	 *
	 * @return string
	 */
	public static function redact( string $message ): string {
		return (string) preg_replace(
			'/([?&](?:amp;|#038;)?(?:key|confirm_key|newuseremail|adminhash)=)[^&\s"\'<>]+/i',
			'$1[redacted]',
			$message
		);
	}

}
