#!/usr/bin/env bash
# Fail-closed PBN / SEO-spam gate for mrt-lider.kz.
# Usage: scan-pbn-spam.sh [root]        (default: repository root)
# Exit 0 = clean. Covers the payload shapes seen in the 2026-08..09 campaign.
set -euo pipefail

ROOT="$(cd "${1:-$(dirname "$0")/..}" && pwd)"
PHP_BIN="$(command -v php || true)"

PBN_PATTERN='pixiupbn|pbn-snip|class="blogroll"|serve\.php\?domain=|PBN_SECRET_KEY|bot@update\.io|chr\([0-9]{3}\)\.chr\([0-9]{3}\)'

fail() {
	echo "PBN spam rejected: $*" >&2
	exit 1
}

SCAN_DIRS=()
for rel in \
	"wp-content/themes/MRT Lider" \
	wp-content/mu-plugins \
	wp-content/plugins/services-importer \
	scripts; do
	if [[ -d "$ROOT/$rel" ]]; then
		SCAN_DIRS+=("$ROOT/$rel")
	fi
done
((${#SCAN_DIRS[@]} > 0)) || fail "no scan roots under $ROOT"

HITS="$(
	grep -RIn --exclude-dir=node_modules --exclude-dir=.git \
		--exclude='scan-pbn-spam.sh' \
		--include='*.php' --include='*.js' --include='*.css' --include='*.html' \
		-E "$PBN_PATTERN" "${SCAN_DIRS[@]}" 2>/dev/null || true
)"
[[ -z "$HITS" ]] || fail $'signature hit\n'"$HITS"

# ── hidden dofollow ────────────────────────────────────
while IFS= read -r -d '' f; do
	[[ "$(basename "$f")" == "scan-pbn-spam.sh" ]] && continue
	if grep -qiE 'display:[[:space:]]*none' "$f" \
		&& grep -qiE 'rel=[\"'\'']?dofollow' "$f"; then
		fail "hidden dofollow in ${f#"$ROOT"/}"
	fi
done < <(find "${SCAN_DIRS[@]}" -type f \( -name '*.php' -o -name '*.js' -o -name '*.html' \) -print0 2>/dev/null)

# ── spam MU-plugins ────────────────────────────────────
for known in pbn-snip.php perf-cache.php performance-cache.php; do
	[[ ! -f "$ROOT/wp-content/mu-plugins/$known" ]] || fail "spam MU-plugin must not exist: $known"
done

# ── base64 payload in a workflow ───────────────────────
WF_DIR="$ROOT/.github/workflows"
if [[ -d "$WF_DIR" ]]; then
	while IFS= read -r wf; do
		if grep -qE 'base64' "$wf" && grep -qE '"[A-Za-z0-9+/]{80,}={0,2}"' "$wf"; then
			fail "workflow carries an inline base64 payload: ${wf#"$ROOT"/}"
		fi
	done < <(find "$WF_DIR" -type f -name '*.yml' 2>/dev/null)
fi

if [[ -n "$PHP_BIN" ]]; then
	while IFS= read -r -d '' f; do
		out="$("$PHP_BIN" -l "$f" 2>&1)" || fail "php -l ${f#"$ROOT"/}: $out"
	done < <(find "${SCAN_DIRS[@]}" -type f -name '*.php' -print0 2>/dev/null)
fi

echo "OK: no PBN spam markers, no spam MU-plugins, no base64 workflow payload"
exit 0
