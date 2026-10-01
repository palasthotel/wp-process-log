#!/bin/sh
# Stages public/ in build/process-log/ - exactly what is deployed to WordPress.org -
# and zips it to process-log.zip in the project root.
#
# The build directory is left in place on purpose: the release workflow rsyncs from
# it into the SVN checkout, so the zip and the SVN trunk are byte-identical.
set -e

PLUGIN_SLUG="process-log"
SCRIPT_DIR=$(cd "$(dirname "$0")" && pwd)
PROJECT_PATH=$(cd "$SCRIPT_DIR/.." && pwd)
BUILD_PATH="$PROJECT_PATH/build"
DEST_PATH="$BUILD_PATH/$PLUGIN_SLUG"

if [ ! -f "$PROJECT_PATH/public/plugin.php" ]; then
  echo "public/plugin.php is missing - is this the right directory?" >&2
  exit 1
fi

# public/css/ is compiled from src/styles/ and is not in the repository.
if [ ! -f "$PROJECT_PATH/public/css/menu-page.css" ]; then
  echo "public/css/ is missing - run \"npm ci && npm run build\" first." >&2
  exit 1
fi

echo "Generating build directory..."
rm -rf "$BUILD_PATH"
mkdir -p "$DEST_PATH"

echo "Syncing files..."
# -L resolves symlinks into real files. wordpress.org discards symlinks when it builds
# the download, and SVN refuses to put one where it versions a regular file.
rsync -rL "$PROJECT_PATH/public/" "$DEST_PATH/"

# The autoloader is regenerated without dev dependencies; composer.json and the lock
# file are not read at runtime, so they stay out of the payload.
cd "$DEST_PATH"
composer install --no-dev --no-interaction --quiet
composer dump-autoload --no-dev --optimize --quiet
rm -f composer.json composer.lock

echo "Generating zip file..."
cd "$BUILD_PATH"
zip -q -r "${PLUGIN_SLUG}.zip" "$PLUGIN_SLUG/"
mv "${PLUGIN_SLUG}.zip" "$PROJECT_PATH/"

echo "${PLUGIN_SLUG}.zip file generated!"
echo "Build done!"
