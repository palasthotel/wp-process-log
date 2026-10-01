<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 10.12.18
 * Time: 16:53
 */

namespace Palasthotel\ProcessLog;

use Palasthotel\ProcessLog\View\Format;
use Palasthotel\ProcessLog\View\LogEntriesListTable;
use Palasthotel\ProcessLog\View\ProcessesListTable;

defined( 'ABSPATH' ) || exit;

/**
 * Tools > Process Logs. Server-rendered with core's list tables and admin markup; the
 * only stylesheet is the little core has no class for.
 *
 * @property Database database
 * @property Plugin plugin
 */
#[\AllowDynamicProperties]
class MenuPage {

	const SLUG = "process_logs";

	const STYLE_HANDLE = "process-log-admin";

	const CAPABILITY = "manage_options";

	/**
	 * @var ProcessesListTable|LogEntriesListTable|null
	 */
	private $table = null;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
		$this->database = $plugin->database;
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
		add_filter( 'set_screen_option_' . ProcessesListTable::PER_PAGE_OPTION, array( $this, 'save_per_page' ), 10, 3 );
		// before WordPress 5.4.2 only this generic filter existed
		add_filter( 'set-screen-option', array( $this, 'save_per_page' ), 10, 3 );
	}

	public function admin_menu() {
		$hook = add_management_page(
			__( 'Process Logs', 'process-log' ),
			__( 'Process Logs', 'process-log' ),
			self::CAPABILITY,
			self::SLUG,
			array( $this, 'render' )
		);
		if ( $hook ) {
			add_action( "load-$hook", array( $this, 'load' ) );
		}
	}

	/**
	 * Before any output: core reads a screen's column headers once, while it renders the
	 * admin header and Screen Options, and keeps them. A list table created later, in
	 * render(), would find that cache empty and print rows without cells.
	 */
	public function load() {
		if ( $this->processId() > 0 ) {
			$this->table = new LogEntriesListTable( $this->database, $this->processId() );
			return;
		}
		add_screen_option( 'per_page', array(
			'label'   => __( 'Processes per page', 'process-log' ),
			'default' => 50,
			'option'  => ProcessesListTable::PER_PAGE_OPTION,
		) );
		$this->table = new ProcessesListTable( $this->database, self::SLUG );
	}

	/**
	 * @param mixed $screen_option
	 * @param string $option
	 * @param int $value
	 *
	 * @return int
	 */
	public function save_per_page( $screen_option, $option, $value ) {
		if ( ProcessesListTable::PER_PAGE_OPTION !== $option ) {
			return $screen_option;
		}
		return max( 1, min( 999, (int) $value ) );
	}

	/**
	 * The stylesheet serves the log screen and the comment meta box.
	 *
	 * @param string $hook_suffix
	 */
	public function enqueue( $hook_suffix ) {
		if ( 'tools_page_' . self::SLUG !== $hook_suffix && 'comment.php' !== $hook_suffix ) {
			return;
		}
		wp_enqueue_style(
			self::STYLE_HANDLE,
			$this->plugin->url . "css/menu-page.css",
			array(),
			$this->assetVersion( "css/menu-page.css" )
		);
	}

	/**
	 * The assets were enqueued with version 1 forever, so browsers kept serving a cached
	 * copy after an update. The file's modification time changes with every release.
	 *
	 * @param string $file path relative to the plugin directory
	 *
	 * @return string|false
	 */
	private function assetVersion( $file ) {
		$path = $this->plugin->path . $file;
		return file_exists( $path ) ? (string) filemtime( $path ) : false;
	}

	/**
	 * @return int
	 */
	private function processId() {
		return isset( $_GET['process'] ) ? absint( $_GET['process'] ) : 0;
	}

	/**
	 * @return string
	 */
	private function overviewUrl() {
		return add_query_arg( array( 'page' => self::SLUG ), admin_url( 'tools.php' ) );
	}

	public function render() {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			return;
		}
		if ( $this->processId() > 0 ) {
			$this->renderProcess( $this->processId() );
			return;
		}
		$this->renderOverview();
	}

	private function renderOverview() {
		$table = ( $this->table instanceof ProcessesListTable ) ? $this->table : new ProcessesListTable( $this->database, self::SLUG );
		$table->prepare_items();
		$search = $table->search_term();
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Process Logs', 'process-log' ); ?></h1>
			<?php
			if ( '' !== $search ) {
				printf(
					'<span class="subtitle">%s</span>',
					/* translators: %s: search term */
					esc_html( sprintf( __( 'Search results for: %s', 'process-log' ), $search ) )
				);
			}
			?>
			<hr class="wp-header-end" />

			<form method="get" action="<?php echo esc_url( admin_url( 'tools.php' ) ); ?>">
				<input type="hidden" name="page" value="<?php echo esc_attr( self::SLUG ); ?>" />
				<?php
				$table->search_box( __( 'Search logs', 'process-log' ), 'process-log' );
				$table->display();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * @param int $process_id
	 */
	private function renderProcess( $process_id ) {
		$process = $this->database->getProcess( $process_id );
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline">
				<?php
				/* translators: %d: process ID */
				echo esc_html( sprintf( __( 'Process #%d', 'process-log' ), $process_id ) );
				?>
			</h1>
			<hr class="wp-header-end" />
			<p>
				<a href="<?php echo esc_url( $this->overviewUrl() ); ?>">
					<?php esc_html_e( '&larr; Back to Process Logs', 'process-log' ); ?>
				</a>
			</p>
			<?php
			if ( ! $process ) {
				printf(
					'<div class="notice notice-error"><p>%s</p></div>',
					esc_html__( 'This process does not exist, or it has expired.', 'process-log' )
				);
				echo '</div>';
				return;
			}

			$details = array(
				__( 'Date', 'process-log' )     => Format::date( $process->created ),
				__( 'User', 'process-log' )     => Format::user( $process->active_user ),
				__( 'URL', 'process-log' )      => esc_html( (string) $process->location_url ),
				__( 'Referrer', 'process-log' ) => esc_html( (string) $process->referer_url ),
				__( 'Host', 'process-log' )     => esc_html( (string) $process->hostname ),
			);
			?>
			<table class="widefat striped process-log-details" role="presentation">
				<tbody>
				<?php foreach ( $details as $label => $value ) : ?>
					<tr>
						<th scope="row"><?php echo esc_html( $label ); ?></th>
						<td><?php echo $value; // escaped above ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>

			<h2><?php esc_html_e( 'Entries', 'process-log' ); ?></h2>
			<?php
			$entries = ( $this->table instanceof LogEntriesListTable ) ? $this->table : new LogEntriesListTable( $this->database, $process_id );
			$entries->prepare_items();
			$entries->display();
			?>
		</div>
		<?php
	}
}
