=== Process Log ===
Contributors: palasthotel, edwardbock, janaeggebrecht
Donate link: https://palasthotel.de/
Tags: log, activity log, audit, debug
Requires at least: 5.0
Tested up to: 7.1.2
Requires PHP: 7.4
Stable tag: 1.4.1
License: GPL-3.0-or-later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Logs what changes in your site, who changed it, and the value before and after.

== Description ==

Process Log records changes as they happen. Every request that changes something becomes a process, every single change a log entry within it - with the old and the new value, the user who made it and the URL it came from.

Logged out of the box:

* Post fields and post meta
* User profiles and user meta
* Comments
* Options
* Mails sent through wp_mail()
* Fatal errors
* Relations of the Content User Relations plugin

Administrators browse the log under Tools > Process Logs, in the same kind of table WordPress uses for posts and users: searchable, filterable by content type, event type, severity and changed field, with the number of rows per page under Screen Options. Each process opens on its own screen with all of its entries and their values before and after. The comment edit screen lists the entries for that comment.

Entries expire after 14 days and are cleaned up hourly; WordPress' own cron schedule and transients are not logged. Deleting the plugin removes its tables. Password hashes, password reset keys and session tokens are logged as changed, without their values, and the keys in the links of password reset, activation and confirmation mails are replaced with [redacted] in the log.

Developers can write their own entries with `process_log_write()` and switch individual watchers off with filters - see the [GitHub repository](https://github.com/palasthotel/wp-process-log).

== Installation ==

1. Install the plugin from Plugins > Add New, or upload `process-log.zip` there
1. Activate it
1. Open Tools > Process Logs

== Frequently Asked Questions ==


== Screenshots ==


== Changelog ==

= 1.4.1 =
**Bug Fixes**
* keep the one-time keys of core mails out of the mail log (74522b8)

= 1.4.0 =
**Features**
* rebuild Tools > Process Logs with WordPress' own list tables (907ecc7)
* show a comment's log entries as a core table (fb87232)

**Bug Fixes**
* let process_log_is_mail_watcher_active switch off mail logging (e134807)
* load the updated log page scripts instead of a cached copy (46ad0f9)
* make the severity filter on the log page work (63315d6)
* no longer store password hashes and reset keys in the log (ba830ed)
* remove the log tables when the plugin is deleted (0d93d4d)
* restrict the process log to administrators and escape logged values (f5fa231)
* silence the PHP 8.2 deprecation notices and run on PHP 7.4 again (28c9099)
* stop logging every rewrite of the cron option (005fead)
* stop the fatal error on sites set to a UTC offset (cf9c0e9)
* translate the log screens (a2c7d58)

= 1.3.4 =
 Bugfix: constraint table name fix

= 1.3.3 =
 Optimization: Ignore _transient and _site_transient changes by default
 Bugfix: Fix broken foreign key constraints in database

= 1.3.2 =
 Bugfix: wp_get_current_user not exists in some rare cases

= 1.3.1 =
 Bugfix: Added form input escaping

= 1.3.0 =
* Feature: wp_mail watcher

= 1.2.3 =
* Optimization: LONGTEXT database fields for object values

= 1.2.2 =
* Bugfix: wrong import path fix in public-functions.php

= 1.2.1 =
* Bugfix: PHP < 7.4 compatibility fix

= 1.2.0 =
* Feature: Related processes overview on comments edit page

= 1.1.8 =
* Release feedback fix: remove short tags

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



