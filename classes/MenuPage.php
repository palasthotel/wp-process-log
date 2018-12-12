<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 10.12.18
 * Time: 16:53
 */

namespace Palasthotel\ProcessLog;


class MenuPage {

	const API_HANDLE = "process-log-api";

	const APP_HANDLE = "process-log-app";

	const STYLE_HANDLE = "process-log-app-style";


	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
	}

	public function admin_menu() {
		add_management_page(
			"Process Logs",
			"Process Logs",
			"manage_options",
			"process_logs",
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
				'selectors' => array(
					"root" => "#process-logs-table-body",
				),
				'i18n'    => array(
					"affected_user" => __( "Affected user", Plugin::DOMAIN ),
					"affected_post" => __( "Affected post", Plugin::DOMAIN ),
					"affected_term" => __( "Affected term", Plugin::DOMAIN ),
					"affected_comment" => __( "Affected comment", Plugin::DOMAIN ),
				),
			)
		);
		wp_enqueue_style(
			self::STYLE_HANDLE,
			$this->plugin->url . "/css/menu-page.css"
		);

		?>
		<div class="wrap process-log">
			<h2>Process logs</h2>
			<table class="widefat">
				<thead>
				<tr>
					<th scope="col" title="Process ID">PID</th>
					<th scope="col">
						<?php _e( 'Created', Plugin::DOMAIN ); ?>
					</th>
					<th scope="col">Active User</th>
					<th scope="col">Logs</th>
					<th scope="col"><?php _e( 'Location URL', Plugin::DOMAIN ); ?></th>
				</tr>
				</thead>
				<tbody id="process-logs-table-body">

				</tbody>
				<?php
				$list = $this->plugin->database->getProcessesList( 1 );
				$list = array();
				foreach ( $list as $process ) {
					?>
					<tr class="process-log__row">
						<td id="process-<?php echo $process->id; ?>">
							<a href="#process-<?php echo $process->id; ?>">
								+ <?php echo $process->created; ?>
							</a>
						</td>
						<td><?php
							if ( $process->active_user == 0 ) {
								echo "Annonymous";
							} else {
								$user = get_user_by( "ID", $process->active_user );
								$link = get_edit_user_link( $process->active_user );
								echo "<a href='$link'>" . $user->display_name . "</a>";
							}
							?></td>
						<td><?php echo $process->logs_count; ?></td>
						<td><?php echo $process->location_url; ?></td>
					</tr>
					<tr>
						<td colspan="3">
							<i>Load process logs via JS</i>
						</td>
					</tr>
					<?php
				}
				?>

			</table>
		</div>
		<?php
	}
}