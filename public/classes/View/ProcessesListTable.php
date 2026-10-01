<?php

namespace Palasthotel\ProcessLog\View;

use Palasthotel\ProcessLog\Database;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( '\WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Tools > Process Logs: one row per process, as the table WordPress uses everywhere else.
 *
 * WP_List_Table gives the screen core's search box, pagination, sortable columns, row
 * actions and Screen Options. Core marks the class private, but reimplementing it means
 * reimplementing all of that and still not matching it.
 */
class ProcessesListTable extends \WP_List_Table {

	/**
	 * The request parameters of the filters. Their names predate this table and are
	 * kept, so bookmarked filter URLs keep working.
	 */
	const PARAM_CONTENT_TYPE = 'process_content_type';
	const PARAM_EVENT_TYPE = 'process_event_type';
	const PARAM_SEVERITY = 'process_severity';
	const PARAM_CHANGED_FIELD = 'process_changed_data_field';
	const PARAM_LEGACY_QUERY = 'process_event_query';

	const PER_PAGE_OPTION = 'process_log_per_page';

	private Database $database;
	private string $pageSlug;

	public function __construct( Database $database, string $pageSlug ) {
		$this->database = $database;
		$this->pageSlug = $pageSlug;

		parent::__construct( array(
			'singular' => 'process',
			'plural'   => 'processes',
			'ajax'     => false,
		) );
	}

	public function get_columns(): array {
		return array(
			'process' => _x( 'Process', 'list table', 'process-log' ),
			'created' => _x( 'Date', 'list table', 'process-log' ),
			'user'    => _x( 'User', 'list table', 'process-log' ),
			'entries' => _x( 'Entries', 'list table', 'process-log' ),
			'url'     => _x( 'URL', 'list table', 'process-log' ),
		);
	}

	protected function get_sortable_columns(): array {
		return array(
			'process' => array( 'id', true ),
			'created' => array( 'id', true ),
		);
	}

	protected function get_primary_column_name() {
		return 'process';
	}

	/**
	 * @param string $param
	 *
	 * @return string
	 */
	private function param( $param ) {
		return isset( $_REQUEST[ $param ] ) ? sanitize_text_field( wp_unslash( $_REQUEST[ $param ] ) ) : '';
	}

	/**
	 * @return string the search term; the old query field is still read
	 */
	public function search_term() {
		$search = $this->param( 's' );
		return '' !== $search ? $search : $this->param( self::PARAM_LEGACY_QUERY );
	}

	/**
	 * @param object $item
	 *
	 * @return string
	 */
	public function process_url( $item ) {
		return add_query_arg(
			array( 'page' => $this->pageSlug, 'process' => (int) $item->id ),
			admin_url( 'tools.php' )
		);
	}

	protected function column_process( $item ): string {
		return sprintf(
			'<strong><a class="row-title" href="%1$s">%2$s</a></strong>',
			esc_url( $this->process_url( $item ) ),
			/* translators: %d: process ID */
			esc_html( sprintf( __( 'Process #%d', 'process-log' ), (int) $item->id ) )
		);
	}

	/**
	 * Where core expects row actions: it adds the "Show more details" toggle for narrow
	 * screens here as well, so actions printed from the column would get it twice.
	 */
	protected function handle_row_actions( $item, $column_name, $primary ) {
		if ( $primary !== $column_name ) {
			return '';
		}
		return $this->row_actions( array(
			'view' => sprintf(
				'<a href="%s">%s</a>',
				esc_url( $this->process_url( $item ) ),
				esc_html__( 'View entries', 'process-log' )
			),
		) );
	}

	protected function column_created( $item ): string {
		return Format::date( $item->created );
	}

	protected function column_user( $item ): string {
		return Format::user( $item->active_user );
	}

	protected function column_entries( $item ): string {
		$types = array_map( 'esc_html', $item->event_types );
		return sprintf(
			'%1$s<br /><span class="description">%2$s</span>',
			esc_html( number_format_i18n( (int) $item->logs_count ) ),
			implode( ', ', $types )
		);
	}

	/**
	 * Shown as text, not as a link: the URL is whatever the request that caused the
	 * process asked for, and following it from wp-admin would replay that request.
	 */
	protected function column_url( $item ): string {
		return esc_html( (string) $item->location_url );
	}

	public function no_items() {
		esc_html_e( 'No processes found.', 'process-log' );
	}

	/**
	 * The filters, between the bulk actions and the pagination, where core puts its own.
	 *
	 * @param string $which
	 */
	protected function extra_tablenav( $which ) {
		if ( 'top' !== $which ) {
			return;
		}
		?>
		<div class="alignleft actions">
			<label for="filter-by-content-type" class="screen-reader-text"><?php esc_html_e( 'Filter by affected content', 'process-log' ); ?></label>
			<select name="<?php echo esc_attr( self::PARAM_CONTENT_TYPE ); ?>" id="filter-by-content-type">
				<option value=""><?php esc_html_e( 'All content', 'process-log' ); ?></option>
				<?php
				$types = array(
					'post'    => __( 'Posts', 'process-log' ),
					'user'    => __( 'Users', 'process-log' ),
					'term'    => __( 'Terms', 'process-log' ),
					'comment' => __( 'Comments', 'process-log' ),
				);
				$this->options( $types, $this->param( self::PARAM_CONTENT_TYPE ) );
				?>
			</select>

			<label for="filter-by-event-type" class="screen-reader-text"><?php esc_html_e( 'Filter by event type', 'process-log' ); ?></label>
			<select name="<?php echo esc_attr( self::PARAM_EVENT_TYPE ); ?>" id="filter-by-event-type">
				<option value=""><?php esc_html_e( 'All event types', 'process-log' ); ?></option>
				<?php
				$events = $this->database->getEventTypes();
				$this->options( array_combine( $events, $events ), $this->param( self::PARAM_EVENT_TYPE ) );
				?>
			</select>

			<label for="filter-by-severity" class="screen-reader-text"><?php esc_html_e( 'Filter by severity', 'process-log' ); ?></label>
			<select name="<?php echo esc_attr( self::PARAM_SEVERITY ); ?>" id="filter-by-severity">
				<option value=""><?php esc_html_e( 'All severities', 'process-log' ); ?></option>
				<?php
				$severities = $this->database->getSeverities();
				$this->options( array_combine( $severities, $severities ), $this->param( self::PARAM_SEVERITY ) );
				?>
			</select>

			<label for="filter-by-changed-field" class="screen-reader-text"><?php esc_html_e( 'Filter by changed field', 'process-log' ); ?></label>
			<input
				type="text"
				name="<?php echo esc_attr( self::PARAM_CHANGED_FIELD ); ?>"
				id="filter-by-changed-field"
				value="<?php echo esc_attr( $this->param( self::PARAM_CHANGED_FIELD ) ); ?>"
				placeholder="<?php esc_attr_e( 'Changed field', 'process-log' ); ?>"
			/>

			<?php submit_button( __( 'Filter', 'process-log' ), '', 'filter_action', false, array( 'id' => 'post-query-submit' ) ); ?>
		</div>
		<?php
	}

	/**
	 * @param array $options value => label
	 * @param string $selected
	 */
	private function options( array $options, $selected ) {
		foreach ( $options as $value => $label ) {
			printf(
				'<option value="%s"%s>%s</option>',
				esc_attr( $value ),
				selected( $selected, (string) $value, false ),
				esc_html( $label )
			);
		}
	}

	public function prepare_items() {
		// _column_headers is left alone: get_column_info() only consults the
		// "manage_{$screen->id}_columns" filter - and with it Screen Options' hidden
		// columns - while it is unset.
		$where = $this->database->processFilterWhere( array(
			'content_type'  => $this->param( self::PARAM_CONTENT_TYPE ),
			'event_type'    => $this->param( self::PARAM_EVENT_TYPE ),
			'severity'      => $this->param( self::PARAM_SEVERITY ),
			'changed_field' => $this->param( self::PARAM_CHANGED_FIELD ),
			'query'         => $this->search_term(),
		) );

		$order   = ( isset( $_REQUEST['order'] ) && 'asc' === strtolower( $_REQUEST['order'] ) ) ? 'ASC' : 'DESC';
		$perPage = $this->get_items_per_page( self::PER_PAGE_OPTION, 50 );
		$total   = $this->database->countProcesses( $where );

		$items = $this->database->getProcessList( $this->get_pagenum(), $perPage, $where, $order );
		foreach ( $items as $item ) {
			$item->logs_count  = $this->database->countLogs( $item->id );
			$item->event_types = $this->database->getProcessEventTypes( $item->id );
		}

		$this->items = $items;
		$this->set_pagination_args( array(
			'total_items' => $total,
			'per_page'    => $perPage,
			'total_pages' => (int) ceil( $total / $perPage ),
		) );
	}
}
