<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 10.12.18
 * Time: 16:53
 */

namespace Palasthotel\ProcessLog;


/**
 * @property Database database
 * @property Plugin plugin
 */
class MenuPage {

	const SLUG = "process_logs";

	const API_HANDLE = "process-log-api";

	const APP_HANDLE = "process-log-app";

	const STYLE_HANDLE = "process-log-app-style";


	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
		$this->database = $plugin->database;
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
	}

	public function admin_menu() {
		add_management_page(
			__("Process Logs", Plugin::DOMAIN),
			__("Process Logs", Plugin::DOMAIN),
			"manage_options",
			self::SLUG,
			array( $this, 'render' )
		);
	}

	public function render() {

		wp_enqueue_script(
			self::API_HANDLE,
			$this->plugin->url . "/js/api.js",
			array( "jquery" ),
			1,
			true
		);
		wp_localize_script(
			self::API_HANDLE,
			"ProcessLogAPI",
			array(
				'ajaxurl' => $this->plugin->ajax->ajaxurl,
			)
		);
		wp_enqueue_script(
			self::APP_HANDLE,
			$this->plugin->url . "/js/menu-page.js",
			array( self::API_HANDLE, "jquery" ),
			1,
			true
		);
		wp_localize_script(
			self::APP_HANDLE,
			"ProcessLogApp",
			array(
				'base_url' => admin_url("tools.php?page=process_logs"),
				'selectors' => array(
					"root" => "#process-log-table-body",
					"button_load_more" => "#process-log-load-more",
					"filters_form" => "#process-filters",
				),
				'i18n'    => array(
					"affected_user" => __( "Affected user", Plugin::DOMAIN ),
					"affected_post" => __( "Affected post", Plugin::DOMAIN ),
					"affected_term" => __( "Affected term", Plugin::DOMAIN ),
					"affected_comment" => __( "Affected comment", Plugin::DOMAIN ),
					"load_more_loading" => __("Loading more logs", Plugin::DOMAIN),
					"load_more_loading_again" => __("Give me a second... I'm on it", Plugin::DOMAIN),
					"load_more_done" => __("No more logs to load 🏖", Plugin::DOMAIN),
				),
			)
		);
		wp_enqueue_style(
			self::STYLE_HANDLE,
			$this->plugin->url . "/css/menu-page.css"
		);

		?>
		<div class="wrap process-log">
			<h2><?php _e("Process logs", Plugin::DOMAIN); ?></h2>

			<form id="process-filters" method="GET">
				<label>
					<?php _e("Effected content", Plugin::DOMAIN) ?>
					<select name="process_content_type">
						<option value=""><?php _ex("All", "select content", Plugin::DOMAIN); ?></option>
						<?php
						$_type = (isset($_GET["process_content_type"]))? sanitize_text_field($_GET["process_content_type"]) : "";
						foreach (array("post", "user", "term", "comment") as $type){
							$selected = ($_type === $type)? "selected":"";
							echo "<option value='".esc_attr($type)."' $selected>$type</option>";
						}
						?>
					</select>
				</label>
				<label>
					<?php _e("Event type", Plugin::DOMAIN); ?>
					<select name="process_event_type">
						<option value=""><?php _ex("All", "select event type filter", Plugin::DOMAIN) ?></option>
						<?php
						$_type = (isset($_GET["process_event_type"]))? sanitize_text_field($_GET["process_event_type"]): "";
						foreach ($this->database->getEventTypes() as $type){
							$selected = ($_type === $type)? "selected":"";
							echo "<option value='".esc_attr($type)."' $selected>$type</option>";
						}
						?>
					</select>
				</label>
				<label>
					<?php _e("Changed field", Plugin::DOMAIN); ?>
					<input
							name="process_changed_data_field"
							type="text"
							value="<?php echo (isset($_GET["process_changed_data_field"]))? esc_attr(sanitize_text_field($_GET["process_changed_data_field"])): ""; ?>"
					/>
				</label>
				<label>
					<?php _e("Severity", Plugin::DOMAIN) ?>
					<select name="process_severity">
						<option value=""><?php _ex("All", "select severity", Plugin::DOMAIN); ?></option>
						<?php
						$_type = (isset($_GET["process_severity"]))? sanitize_text_field($_GET["process_severity"]): "";
						foreach ($this->database->getSeverities() as $type){
							$selected = ($_type === $type)? "selected":"";
							echo "<option value='".esc_attr($type)."' $selected>$type</option>";
						}
						?>
					</select>
				</label>
				<label>
					<?php _e("Query", Plugin::DOMAIN) ?> <input name="process_event_query" value="<?php
					echo (isset($_GET["process_event_query"]))? esc_attr(sanitize_text_field($_GET["process_event_query"])) : "";
					?>" />
				</label>

				<button class="button-primary"><?php _e("Filter", Plugin::DOMAIN) ?></button>
			</form>

			<table class="widefat">
				<thead>
				<tr>
					<th scope="col" title="Process ID">
						<?php _e("PID", Plugin::DOMAIN); ?>
					</th>
					<th scope="col">
						<?php _e( 'Created', Plugin::DOMAIN ); ?>
					</th>
					<th scope="col">
						<?php _e("Active User", Plugin::DOMAIN); ?>
					</th>
					<th scope="col"><?php
						_e("Logs", Plugin::DOMAIN); ?>
					</th>
					<th scope="col">
						<?php _e( 'URL', Plugin::DOMAIN ); ?>
					</th>
				</tr>
				</thead>
				<tbody id="process-log-table-body"></tbody>
			</table>
			<button id="process-log-load-more" class="button button-primary"><?php _e("Load more", Plugin::DOMAIN); ?></button>
		</div>
		<?php
	}
}