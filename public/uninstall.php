<?php
/**
 * Runs when the plugin is deleted under Plugins - not on deactivation. Removes the log
 * tables and everything else the plugin stored, on every site of a network.
 *
 * @package Palasthotel\ProcessLog
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

$process_log_uninstall_site = function () {
	global $wpdb;

	// The items reference the processes by foreign key, so they go first. The
	// _deprecated table is what update_2 leaves behind if it was interrupted.
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}process_log_items_deprecated" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}process_log_items" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}process_logs" );

	delete_option( 'process-log_version' );
	wp_clear_scheduled_hook( 'process_log_clean' );
};

if ( is_multisite() ) {
	foreach ( get_sites( array( 'fields' => 'ids', 'number' => 0 ) ) as $process_log_site_id ) {
		switch_to_blog( $process_log_site_id );
		$process_log_uninstall_site();
		restore_current_blog();
	}
} else {
	$process_log_uninstall_site();
}

// Screen Options of the log screen, stored per user and shared by the whole network
delete_metadata( 'user', 0, 'process_log_per_page', '', true );
