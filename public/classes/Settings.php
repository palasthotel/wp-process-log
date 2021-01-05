<?php


namespace Palasthotel\ProcessLog;


/**
 * @property Plugin plugin
 */
class Settings {

	const SLUG = "process_logs_options";

	public function __construct(Plugin $plugin) {
		$this->plugin = $plugin;
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
	}

	public function admin_menu() {
		add_options_page(
			__("Settings › Process Logs", Plugin::DOMAIN),
			__("Process Logs", Plugin::DOMAIN),
			"manage_options",
			self::SLUG,
			array( $this, 'render' )
		);
	}

	public function render(){
		echo "<div class='wrap'>";
		echo sprintf("<h2>%s</h2>", __("Process Logs", Plugin::DOMAIN));
		$logs_url = admin_url('tools.php?page='.MenuPage::SLUG);
		printf(
			"<p class='description'><a href='%s'>%s</a></p>",
			$logs_url,
			__('If you are looking for the logs table have a look here!', Plugin::DOMAIN)
		);

		echo "</div>";
	}
}