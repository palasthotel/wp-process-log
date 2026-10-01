<?php

namespace Palasthotel\ProcessLog\View;

use Palasthotel\ProcessLog\Database;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( '\WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * The entries of a single process, on the process' own screen.
 */
class LogEntriesListTable extends \WP_List_Table {

	private Database $database;
	private int $processId;

	public function __construct( Database $database, int $processId ) {
		$this->database  = $database;
		$this->processId = $processId;

		parent::__construct( array(
			'singular' => 'entry',
			'plural'   => 'entries',
			'ajax'     => false,
		) );
	}

	public function get_columns(): array {
		return array(
			'entry'    => _x( 'Entry', 'list table', 'process-log' ),
			'event'    => _x( 'Event', 'list table', 'process-log' ),
			'field'    => _x( 'Changed field', 'list table', 'process-log' ),
			'old'      => _x( 'Before', 'list table', 'process-log' ),
			'new'      => _x( 'After', 'list table', 'process-log' ),
			'affected' => _x( 'Affected', 'list table', 'process-log' ),
			'expires'  => _x( 'Deleted', 'list table', 'process-log' ),
		);
	}

	protected function get_primary_column_name() {
		return 'entry';
	}

	protected function column_entry( $item ): string {
		$html = sprintf(
			'<strong>%s</strong>',
			/* translators: %d: entry ID */
			esc_html( sprintf( __( 'Entry #%d', 'process-log' ), (int) $item->id ) )
		);
		if ( ! empty( $item->message ) ) {
			$html .= '<br />' . Format::value( $item->message );
		}
		foreach ( array( 'note', 'comment', 'location_path' ) as $field ) {
			if ( ! empty( $item->{$field} ) ) {
				$html .= '<br /><span class="description">' . esc_html( $item->{$field} ) . '</span>';
			}
		}
		if ( ! empty( $item->variables ) ) {
			$html .= Format::value( $item->variables );
		}

		return $html;
	}

	/**
	 * Where core expects row actions, next to its own "Show more details" toggle.
	 */
	protected function handle_row_actions( $item, $column_name, $primary ) {
		if ( $primary !== $column_name ) {
			return '';
		}
		$actions = array();
		$link    = (string) $item->link_url;
		if ( preg_match( '#^https?://#i', $link ) ) {
			$actions['open'] = sprintf(
				'<a href="%s">%s</a>',
				esc_url( $link ),
				esc_html__( 'Open', 'process-log' )
			);
		}
		// without actions row_actions() prints nothing, not even core's toggle
		return empty( $actions ) ? parent::handle_row_actions( $item, $column_name, $primary ) : $this->row_actions( $actions );
	}

	protected function column_event( $item ): string {
		return sprintf(
			'%1$s<br /><span class="description">%2$s</span>',
			esc_html( $item->event_type ),
			esc_html( $item->severity )
		);
	}

	protected function column_field( $item ): string {
		return empty( $item->changed_data_field ) ? '' : '<code>' . esc_html( $item->changed_data_field ) . '</code>';
	}

	protected function column_old( $item ): string {
		return empty( $item->changed_data_field ) ? '' : Format::value( $item->changed_data_value_old );
	}

	protected function column_new( $item ): string {
		return empty( $item->changed_data_field ) ? '' : Format::value( $item->changed_data_value_new );
	}

	protected function column_affected( $item ): string {
		$lines = array();

		if ( ! empty( $item->affected_post ) ) {
			$post  = get_post( (int) $item->affected_post );
			$title = ( $post instanceof \WP_Post ) ? get_the_title( $post ) : '';
			if ( '' !== $title ) {
				/* translators: %s: post title */
				$label = sprintf( __( 'Post: %s', 'process-log' ), $title );
			} else {
				/* translators: %d: post ID */
				$label = sprintf( __( 'Post #%d', 'process-log' ), (int) $item->affected_post );
			}
			$edit  = ( $post instanceof \WP_Post ) ? get_edit_post_link( $post->ID, 'raw' ) : '';
			$lines[] = $edit
				? sprintf( '<a href="%s">%s</a>', esc_url( $edit ), esc_html( $label ) )
				: esc_html( $label );
		}
		if ( ! empty( $item->affected_user ) ) {
			/* translators: %s: user name, already linked */
			$lines[] = sprintf( esc_html__( 'User: %s', 'process-log' ), Format::user( $item->affected_user ) );
		}
		if ( ! empty( $item->affected_comment ) ) {
			$comment = get_comment( (int) $item->affected_comment );
			/* translators: %d: comment ID */
			$label = sprintf( __( 'Comment #%d', 'process-log' ), (int) $item->affected_comment );
			$lines[] = ( $comment instanceof \WP_Comment && current_user_can( 'edit_comment', $comment->comment_ID ) )
				? sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'comment.php?action=editcomment&c=' . (int) $comment->comment_ID ) ), esc_html( $label ) )
				: esc_html( $label );
		}
		if ( ! empty( $item->affected_term ) ) {
			/* translators: %d: term ID */
			$lines[] = esc_html( sprintf( __( 'Term #%d', 'process-log' ), (int) $item->affected_term ) );
		}

		return implode( '<br />', $lines );
	}

	protected function column_expires( $item ): string {
		$expires = (int) $item->expires;
		if ( $expires <= 0 ) {
			return '';
		}
		return sprintf(
			'<time datetime="%1$s" title="%2$s">%3$s</time>',
			esc_attr( gmdate( 'c', $expires ) ),
			esc_attr( get_date_from_gmt( gmdate( 'Y-m-d H:i:s', $expires ), get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) ),
			/* translators: %s: time span, e.g. "13 days" */
			esc_html( sprintf( __( 'in %s', 'process-log' ), human_time_diff( time(), $expires ) ) )
		);
	}

	public function no_items() {
		esc_html_e( 'This process has no entries.', 'process-log' );
	}

	/**
	 * A process has a handful of entries, so there is nothing to page through or act on.
	 *
	 * @param string $which
	 */
	protected function display_tablenav( $which ) {
	}

	public function prepare_items() {
		$this->items = $this->database->getProcessLogs( $this->processId );
	}
}
