# CI/CD Workflows

This repository uses four GitHub Actions workflows. The plugin is versioned by
[release-please](https://github.com/googleapis/release-please) based on
[conventional commits](https://www.conventionalcommits.org/):
`fix:` → patch, `feat:` → minor, `feat!:` / `BREAKING CHANGE:` → major.

Tag format: `v*` (e.g. `v1.3.5`).

---

## Overview

```
Push to main
    │
    ├──▶ [release-please.yml]
    │        Creates / updates the release PR, bumping package.json + CHANGELOG.md
    │
    │    On release PR (opened / synchronize)
    ├──▶ [update-plugin-version.yml]
    │        Syncs the Version header in public/plugin.php
    │        + readme.txt Stable tag & changelog entry
    │
    │    On PR to main
    └──▶ [pr.yml]
             php -l on 7.4 / 8.2 / 8.3 / 8.4
             pack + "is the payload clean?"
             "do the version carriers agree?"


Merge release PR  →  release-please pushes tag v1.3.5 + creates GitHub Release
    │
    └── v*  ──▶ [wordpress-svn-release.yml]
                    version check → pack → upload zip to the Release
                    → deploy to WordPress.org SVN (trunk + tags/1.3.5)
```

There is no build step: the admin screens are rendered in PHP, and the one stylesheet in
`public/css/` is plain CSS, shipped as written.

---

## `pr.yml` — PR checks

Three jobs:

- **php-lint** — `php -l` over every PHP file, on PHP 7.4, 8.2, 8.3 and 8.4.
- **pack** — runs `bin/pack.sh` and asserts the staged payload contains the plugin file,
  the classes and admin screens, the autoloader, the stylesheet, the readme, the licence
  and the translations, and none of the repository-only files. It also fails if the payload contains "Process logs - DEV", the development
  wrapper's plugin name — shipping that would put a second entry in everybody's plugin list.
- **versions** — runs `bin/version-checker.sh`, so a hand-edited version number fails in
  the pull request instead of aborting a release. Skipped on the release PR: that one
  arrives with only `package.json` bumped and gets its other carriers in a second push from
  `update-plugin-version.yml`, so checking its first commit would fail every time.

## `release-please.yml` — release PR

Runs on every push to `main`. Uses a short-lived installation token of the org-owned
"Palasthotel Release Bot" app rather than `GITHUB_TOKEN`, because the tag this job pushes
has to trigger `wordpress-svn-release.yml` — and tags pushed with `GITHUB_TOKEN` trigger
nothing.

`release-type` is `node`: the version lives in `package.json`.

## `update-plugin-version.yml` — version carriers

Runs only on the release-please PR (`startsWith(github.head_ref, 'release-please--')`). It
reads the version from `package.json` and writes it into the `Version:` header of
`public/plugin.php`, the `Stable tag:` in `public/readme.txt`, and a new `= x.y.z =`
section under `== Changelog ==`, converted from the Markdown release-please wrote into
`CHANGELOG.md`.

The development wrapper in the root is not a version carrier and nothing syncs it. It never
ships, so its header version is decoration.

It pushes with the app token, not `GITHUB_TOKEN`: a `GITHUB_TOKEN` push triggers no
workflows, which would leave the release PR without check results.

## `wordpress-svn-release.yml` — deploy

Triggered by a `v*` tag, or manually by `workflow_dispatch` with a version input.

`bin/version-checker.sh` runs first and compares the tag against the version carriers, so
a mismatch stops the run before anything is published.

`bin/pack.sh` stages `public/` in `build/process-log/`, regenerates the composer autoloader
without dev dependencies, drops `composer.json`/`composer.lock` and zips it. The zip is
attached to the GitHub Release, and the SVN commit rsyncs from the same directory — so the
release asset and the wordpress.org download are identical.

`rsync -rL`, not `cp -r`: GNU `cp` keeps symlinks while descending and BSD `cp` resolves
them, so a local rehearsal on macOS would pass while the Ubuntu runner failed.
wordpress.org discards symlinks when it builds the download, and SVN refuses a commit that
puts a symlink where it versions a regular file. This repository has some:
`public/languages/process-log-ch_CH.*` and `process-log-de_CH_informal.*` link to the
`de_DE` translation, and ship as copies of it.

`assets/` (icons for the plugin page) lives only in SVN, not in this repository. The mirror
step only runs when the repository carries the directory — without that guard, `--delete`
would empty the plugin page's media on the first release.

### Required repository configuration

| Kind | Name | Purpose |
|---|---|---|
| Variable | `RELEASE_BOT_APP_ID` | GitHub App id of the release bot |
| Variable | `SVN_REPO_URL` | `https://plugins.svn.wordpress.org/process-log/` |
| Secret | `RELEASE_BOT_PRIVATE_KEY` | private key of that app |
| Secret | `SVN_USERNAME` | wordpress.org account with commit rights |
| Secret | `SVN_PASSWORD` | its password |

### When a release fails

Do not re-push the tag. A tag event replays the workflow file **as it was at that tag**,
so a fix to the workflow cannot be picked up that way, and a tag ruleset usually refuses
to move a tag (`GH013`). Use **Run workflow** on `wordpress-svn-release.yml` instead, from
a branch that has the fix, and give it the version to deploy.
