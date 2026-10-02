# CI/CD Workflows

The four workflows in `.github/workflows/` call the shared ones in
[palasthotel/github-workflows](https://github.com/palasthotel/github-workflows). How
they work, every input and what to do when a deploy fails is described there, in
[docs/wp-plugin.md](https://github.com/palasthotel/github-workflows/blob/main/docs/wp-plugin.md).

What is specific to this plugin:

| | |
|---|---|
| wordpress.org slug | `process-log` |
| version file | `package.json` (`release-type: node`) - keep it, release-please and the scripts read the version there. Removing it once cut 1.4.0 with 1.3.4 in every version carrier |
| build step | none - the admin screens are rendered in PHP, and `public/css/menu-page.css` is plain CSS |
| composer | `public/composer.json` only maps the autoloader; the pack regenerates `vendor/` without dev dependencies and drops `composer.json`/`composer.lock` from the payload |
| `required-files` | the admin screens, the stylesheet, the autoloader and the translations, so a payload without them fails the PR |
| `assets/` | not in the repository; the plugin page's icons live only in SVN and are left alone |
| symlinks | `public/languages/process-log-ch_CH.*` and `-de_CH_informal.*` link to `de_DE`; the pack resolves them into files |
