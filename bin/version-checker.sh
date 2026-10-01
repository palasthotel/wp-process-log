#!/usr/bin/env bash
# Guard for the release job: the tag being released must match every version
# carrier in the repository. Expects VERSION in the environment (without "v").
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT_DIR="$(cd "$SCRIPT_DIR/.." && pwd)"

VERSION="${VERSION:-}"
if [[ -z "$VERSION" ]]; then
  echo "ERROR: VERSION is not set (e.g. VERSION=1.3.4)" >&2
  exit 1
fi

PKG_VERSION="$(node -p "require('$ROOT_DIR/package.json').version" 2>/dev/null || true)"

README_VERSION="$(grep -E '^Stable tag:' "$ROOT_DIR/public/readme.txt" | head -n1 | sed -E 's/^Stable tag:[[:space:]]*//')"

PLUGIN_VERSION="$(grep -E '^[[:space:]]*\*?[[:space:]]*Version:[[:space:]]*[0-9]+\.[0-9]+' "$ROOT_DIR/public/plugin.php" \
  | head -n1 \
  | sed -E 's/.*Version:[[:space:]]*([0-9]+(\.[0-9]+)+).*/\1/')"

fail=0

check_eq() {
  local label="$1"
  local got="$2"
  if [[ -z "$got" ]]; then
    echo "ERROR: could not read ${label}" >&2
    fail=1
  elif [[ "$got" != "$VERSION" ]]; then
    echo "ERROR: ${label} is $got, expected $VERSION" >&2
    fail=1
  else
    echo "OK: ${label} == $VERSION"
  fi
}

check_eq "package.json version" "$PKG_VERSION"
check_eq "readme.txt Stable tag" "$README_VERSION"
check_eq "plugin.php Version" "$PLUGIN_VERSION"

if [[ "$fail" -ne 0 ]]; then
  echo "Release version check failed." >&2
  exit 1
fi

echo "All versions match ✅"
