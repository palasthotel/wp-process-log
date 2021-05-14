=== Process Log ===
Contributors: palasthotel, edwardbock
Donate link: http://palasthotel.de/
Tags: debug, log
Requires at least: 4.0
Tested up to: 5.7.2
Stable tag: 1.1.7
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl

Loggin system.

== Description ==

Logging system.

* WP_Post metas
* WP_User profiles
* Taxonomies
* Comments
* Content User Relations

== Installation ==

1. Upload `process-log.zip` to the `/wp-content/plugins/` directory
1. Extract the Plugin to a `process-log` Folder
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==


== Screenshots ==


== Changelog ==

= 1.1.7 =
* Bugfix: menu-page.js null check fix

= 1.1.6 =
* Feature: Implemented comments watcher

= 1.1.5 =
* Bugfix: Undefined index in $_SERVER when using wp cli.

= 1.1.4 =
* Bugfix: Undefined file in $trace warning.

= 1.1.3 =
* Feature: options watcher
* Feature: settings page

= 1.1.2 =
* Feature: process log filter params in url
* Optimization: log datetime in wordpress timezone

= 1.1.1 =
* Feature: schedule for cleaning expired logs
* Filter: process_log_expires

= 1.1.0 =
* Feature: new ErrorWatcher that adds fatal errors to protocol
* Feature: Added filter for changed data field
* Filter: ignore post meta value filter "process_log_ignore_post_meta"
* Bugfix: sometimes get_post_meta_by_id not exists in PostWatcher fix

= 1.0.0 =
* Support: WP_Post meta value changes
* Support: WP_User profile changes
* Support: Taxonomy changes
* Support: Comment changes
* Support: Content User Relations

== Upgrade Notice ==


== Arbitrary section ==



