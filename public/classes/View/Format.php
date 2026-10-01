<?php

namespace Palasthotel\ProcessLog\View;

defined( 'ABSPATH' ) || exit;

/**
 * Turns logged values into escaped markup for the admin screens. Everything in the log
 * can come from a request, so nothing leaves this class unescaped.
 */
class Format {

	/**
	 * A logged value - plain text inline, anything longer or JSON in a preformatted block.
	 *
	 * @param mixed $value
	 *
	 * @return string
	 */
	public static function value( $value ) {
		if ( null === $value || "" === $value ) {
			return '<span aria-hidden="true">—</span>';
		}
		$value = (string) $value;

		$json = json_decode( $value );
		if ( ( is_array( $json ) || is_object( $json ) ) && JSON_ERROR_NONE === json_last_error() ) {
			$value = wp_json_encode( $json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
		} else if ( false === strpos( $value, "\n" ) && strlen( $value ) <= 80 ) {
			return esc_html( $value );
		}

		return '<pre class="process-log-value">' . esc_html( $value ) . '</pre>';
	}

	/**
	 * @param int|string|null $user_id
	 *
	 * @return string the linked display name, or a note why there is none
	 */
	public static function user( $user_id ) {
		$user_id = intval( $user_id );
		if ( $user_id <= 0 ) {
			return esc_html__( 'Not logged in', 'process-log' );
		}
		$user = get_userdata( $user_id );
		if ( ! ( $user instanceof \WP_User ) ) {
			/* translators: %d: user ID */
			return esc_html( sprintf( __( 'Deleted user #%d', 'process-log' ), $user_id ) );
		}
		if ( current_user_can( 'edit_user', $user_id ) ) {
			return sprintf(
				'<a href="%s">%s</a>',
				esc_url( get_edit_user_link( $user_id ) ),
				esc_html( $user->display_name )
			);
		}
		return esc_html( $user->display_name );
	}

	/**
	 * @param string $datetime "Y-m-d H:i:s" in the site's timezone, as the plugin stores it
	 *
	 * @return string
	 */
	public static function date( $datetime ) {
		if ( empty( $datetime ) ) {
			return '';
		}
		return esc_html(
			mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $datetime )
		);
	}
}
