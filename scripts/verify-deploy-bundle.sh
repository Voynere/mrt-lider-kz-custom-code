#!/usr/bin/env bash
set -euo pipefail

ROOT="${1:?usage: verify-deploy-bundle.sh <bundle-root>}"
LABEL="${2:-bundle}"

require_file() {
	local rel="$1"
	if [ ! -f "$ROOT/$rel" ]; then
		echo "[$LABEL] ERROR: missing $rel"
		exit 1
	fi
}

require_dir() {
	local rel="$1"
	if [ ! -d "$ROOT/$rel" ]; then
		echo "[$LABEL] ERROR: missing directory $rel"
		exit 1
	fi
}

require_file "favicon.svg"
require_file "favicon.ico"
require_dir "wp-content/themes/MRT Lider"
require_file "wp-content/themes/MRT Lider/functions.php"
require_file "wp-content/themes/MRT Lider/style.css"
require_dir "wp-content/plugins/services-importer"
require_file "wp-content/plugins/services-importer/services-importer.php"

# ── PBN / SEO-spam guard: domain signatures, hidden dofollow, spam MU-plugins ──
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
if [ -f "$SCRIPT_DIR/scan-pbn-spam.sh" ]; then
	bash "$SCRIPT_DIR/scan-pbn-spam.sh" "$ROOT" || {
		echo "[$LABEL] ERROR: PBN spam guard failed"
		exit 1
	}
fi

echo "[$LABEL] OK: favicon + MRT Lider theme + services-importer bundle"
