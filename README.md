# Process Log (WordPress-Plugin)

Process Log records what changes in a WordPress site and who changed it: post fields and
post meta, user profiles and user meta, comments, options, outgoing mails and fatal
errors. Terms are not logged yet (`TaxonomyWatcher` is a stub). Every request that changes something becomes a *process*, every single change a
*log entry* within it, with the old and the new value. Administrators browse them under
**Tools → Process Logs**; the comment edit screen shows the entries for that comment.

The screens are server-rendered with core's own admin markup: the overview and a
process' entries are `WP_List_Table`s (`public/classes/View/`), so search, filters,
pagination, sorting, row actions and Screen Options behave as on Posts or Users. The
only stylesheet covers what core has no class for - line breaks in logged values and
column widths. `WP_List_Table` is marked private by core; reimplementing it would mean
reimplementing all of that and still not matching it, which is why plugins use it as it
is.

The plugin is available on [WordPress.org](https://wordpress.org/plugins/process-log/)
(slug `process-log`; this repository is `wp-process-log`).

## Repository layout

`public/` is exactly what ships to wordpress.org; everything else is repository-only.
`plugin.php` in the root is a development wrapper that loads `public/`, so the whole
repository can be symlinked into `wp-content/plugins` during development.

Releases are cut by release-please from conventional commits and deployed to the
wordpress.org SVN by GitHub Actions — see [.github/WORKFLOWS.md](.github/WORKFLOWS.md).
Contribution rules and the local setup are in [CONTRIBUTING.md](CONTRIBUTING.md).

## Storage and retention

Logs live in two tables, `{prefix}process_logs` (one row per request) and
`{prefix}process_log_items` (one row per change). An hourly cron job deletes entries older
than 14 days, and processes left without entries with them; change the lifetime with the
`process_log_expires` filter. The job runs on WP-Cron, so on a site with
`DISABLE_WP_CRON` it needs the system cron that site uses anyway.

Transients and the `cron` option are not logged by default. WordPress rewrites `cron`
twice for every event it runs, and logging it stored the whole schedule, before and
after, hundreds of times a day. `process_log_ignore_option` switches either back on.

Deactivating the plugin keeps the log. Deleting it under Plugins runs
`public/uninstall.php`, which drops both tables on every site of a network and removes
the plugin's option, cron job and Screen Options setting.

The log holds whatever was changed — post content, option values, the full text of sent
mails. Password hashes, password reset keys and session tokens are recorded as changed
but not with their values.

## Writing your own log entries

```php
process_log_write( function ( \Palasthotel\ProcessLog\Model\ProcessLog $log ) {
	return $log
		->setEventType( 'import' )
		->setMessage( 'Imported 12 posts from the feed' )
		->setAffectedPost( $post_id );
} );
```

## Filters

| Filter | Default | Purpose |
|---|---|---|
| `process_log_core_watchers_active` | `true` | switch off all built-in watchers at once |
| `process_log_is_post_watcher_active` | `true` | posts and post meta (receives the post id) |
| `process_log_is_user_watcher_active` | `true` | users and user meta |
| `process_log_is_comment_watcher_active` | `true` | comments (receives the comment id) |
| `process_log_is_option_watcher_active` | `true` | options |
| `process_log_is_mail_watcher_active` | `true` | `wp_mail()` |
| `process_log_is_content_user_relations_watcher_active` | `true` | the Content User Relations plugin |
| `process_log_ignore_post_meta` | `true` for `_edit_lock`, `_edit_last` | skip a post meta key |
| `process_log_ignore_option` | `true` for transients and `cron` | skip an option |
| `process_log_expires` | now + 14 days | expiry timestamp of a new entry |

## License

GPL-3.0-or-later, see [LICENSE](LICENSE).
