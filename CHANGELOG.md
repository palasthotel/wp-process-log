# Changelog

All notable changes to this project are documented here. The format follows
[Conventional Commits](https://www.conventionalcommits.org/) and from the next release
on the file is maintained by
[release-please](https://github.com/googleapis/release-please) — do not edit it by hand.

The plugin's user-facing history lives in `public/readme.txt`, which is what shows on the
wordpress.org plugin page.

## [1.4.1](https://github.com/palasthotel/wp-process-log/compare/v1.4.0...v1.4.1) (2026-10-02)


### Bug Fixes

* keep the one-time keys of core mails out of the mail log ([74522b8](https://github.com/palasthotel/wp-process-log/commit/74522b8e375f73981ab4bbf31e161ca3e1128b3d))

## [1.4.0](https://github.com/palasthotel/wp-process-log/compare/v1.3.4...v1.4.0) (2026-10-01)


### Features

* rebuild Tools &gt; Process Logs with WordPress' own list tables ([907ecc7](https://github.com/palasthotel/wp-process-log/commit/907ecc7a849d4a1cd8d0054e0188ef632e0583d2))
* show a comment's log entries as a core table ([fb87232](https://github.com/palasthotel/wp-process-log/commit/fb87232b1c9ca753be21cd0f05ee44c3df1a5cde))


### Bug Fixes

* let process_log_is_mail_watcher_active switch off mail logging ([e134807](https://github.com/palasthotel/wp-process-log/commit/e13480704e46b6009d9bd702686107f170ebdd9c))
* load the updated log page scripts instead of a cached copy ([46ad0f9](https://github.com/palasthotel/wp-process-log/commit/46ad0f9f1b123ff91e6a018cef0669e0e276e9c6))
* make the severity filter on the log page work ([63315d6](https://github.com/palasthotel/wp-process-log/commit/63315d6e67c9f2c384050fced97b72592a05b36f))
* no longer store password hashes and reset keys in the log ([ba830ed](https://github.com/palasthotel/wp-process-log/commit/ba830ed30b4d342a585457ec792c5f32f623fe3c))
* remove the log tables when the plugin is deleted ([0d93d4d](https://github.com/palasthotel/wp-process-log/commit/0d93d4d926752eb07aa94b9f8490d9ccb9493a3b))
* restrict the process log to administrators and escape logged values ([f5fa231](https://github.com/palasthotel/wp-process-log/commit/f5fa231fa1db4d356a9f358d1a2687e60a491694))
* silence the PHP 8.2 deprecation notices and run on PHP 7.4 again ([28c9099](https://github.com/palasthotel/wp-process-log/commit/28c90991b2acff9fa5b9102d4d114a71f5369093))
* stop logging every rewrite of the cron option ([005fead](https://github.com/palasthotel/wp-process-log/commit/005fead808a282a5e8937677051d34e6fd3bc9b2))
* stop the fatal error on sites set to a UTC offset ([cf9c0e9](https://github.com/palasthotel/wp-process-log/commit/cf9c0e99006491b265d933da1f0e72a32c7daa19))
* translate the log screens ([a2c7d58](https://github.com/palasthotel/wp-process-log/commit/a2c7d5844c95de321a9c0d43cb0d6a1f4e2e807a))

## 1.3.4

* Bugfix: constraint table name fix

Earlier releases are listed in `public/readme.txt`.
