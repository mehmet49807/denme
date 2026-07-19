#!/usr/bin/env bash
# CSS/JS birleştirme + minify (esbuild)
# Kaynak: web-site/public/css|js → *.min.css / *.min.js + sayfa bundle'ları
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
WEB_PUBLIC="$ROOT/web-site/public"
CSS_DIR="$WEB_PUBLIC/css"
JS_DIR="$WEB_PUBLIC/js"
ESBUILD=(npx --yes esbuild)

minify_css_file() {
  local src="$1"
  local out="$2"
  if [[ ! -f "$src" ]]; then
    echo "skip missing $src"
    return 0
  fi
  "${ESBUILD[@]}" "$src" --minify --outfile="$out" --log-level=warning
  echo "css $(basename "$src") $(wc -c < "$src") -> $(wc -c < "$out") bytes"
}

minify_js_file() {
  local src="$1"
  local out="$2"
  if [[ ! -f "$src" ]]; then
    echo "skip missing $src"
    return 0
  fi
  "${ESBUILD[@]}" "$src" --minify --outfile="$out" --log-level=warning --target=es2018
  echo "js  $(basename "$src") $(wc -c < "$src") -> $(wc -c < "$out") bytes"
}

bundle_css() {
  local out="$1"
  shift
  local tmp
  tmp="$(mktemp --suffix=.css)"
  : >"$tmp"
  for f in "$@"; do
    if [[ -f "$f" ]]; then
      {
        echo "/* $(basename "$f") */"
        cat "$f"
        echo
      } >>"$tmp"
    else
      echo "warn missing css in bundle: $f" >&2
    fi
  done
  "${ESBUILD[@]}" "$tmp" --minify --outfile="$out" --log-level=warning
  rm -f "$tmp"
  echo "bundle css -> $(basename "$out") ($(wc -c < "$out") bytes)"
}

bundle_js() {
  local out="$1"
  shift
  local tmp
  tmp="$(mktemp --suffix=.js)"
  : >"$tmp"
  for f in "$@"; do
    if [[ -f "$f" ]]; then
      {
        echo ";/* $(basename "$f") */"
        cat "$f"
        echo
      } >>"$tmp"
    else
      echo "warn missing js in bundle: $f" >&2
    fi
  done
  "${ESBUILD[@]}" "$tmp" --minify --outfile="$out" --log-level=warning --target=es2018
  rm -f "$tmp"
  echo "bundle js  -> $(basename "$out") ($(wc -c < "$out") bytes)"
}

mkdir -p "$CSS_DIR" "$JS_DIR"

# Hydrate from /tmp/prod-assets when local copy missing
for f in app.css; do
  if [[ ! -f "$CSS_DIR/$f" && -f "/tmp/prod-assets/css/$f" ]]; then
    cp "/tmp/prod-assets/css/$f" "$CSS_DIR/$f"
  fi
done
for f in badges.js live-sync.js page-auto-refresh.js rt-client.js feed.js stories.js locations.js profile-photo.js profile-posts.js flagged-select.js chat.js; do
  if [[ ! -f "$JS_DIR/$f" && -f "/tmp/prod-assets/js/$f" ]]; then
    cp "/tmp/prod-assets/js/$f" "$JS_DIR/$f"
  fi
done

echo "== minify individual CSS =="
for f in \
  app.css \
  feed-stories.css \
  feed-toolbar.css \
  location-search.css \
  mobile-bottom-nav.css \
  nav-icon-animations.css \
  profile-identity.css \
  profile-premium-sections.css \
  profile-settings.css \
  profile-toolbar-mobile.css \
  premium-page.css
do
  minify_css_file "$CSS_DIR/$f" "$CSS_DIR/${f%.css}.min.css"
done

echo "== app-shell CSS bundle (matches layouts/app.blade.php auth shell) =="
bundle_css "$CSS_DIR/app-shell.min.css" \
  "$CSS_DIR/profile-settings.css" \
  "$CSS_DIR/profile-identity.css" \
  "$CSS_DIR/feed-toolbar.css" \
  "$CSS_DIR/nav-icon-animations.css" \
  "$CSS_DIR/mobile-bottom-nav.css"

echo "== profile-page CSS bundle (extras beyond app-shell) =="
bundle_css "$CSS_DIR/profile-page.min.css" \
  "$CSS_DIR/profile-toolbar-mobile.css" \
  "$CSS_DIR/profile-premium-sections.css" \
  "$CSS_DIR/feed-stories.css"

echo "== user-profile CSS bundle (extras beyond app-shell) =="
bundle_css "$CSS_DIR/user-profile.min.css" \
  "$CSS_DIR/profile-premium-sections.css" \
  "$CSS_DIR/feed-stories.css"

echo "== feed-page CSS bundle =="
bundle_css "$CSS_DIR/feed-page.min.css" \
  "$CSS_DIR/feed-stories.css"

echo "== minify individual JS =="
for f in \
  badges.js \
  live-sync.js \
  page-auto-refresh.js \
  rt-client.js \
  feed.js \
  stories.js \
  locations.js \
  profile-photo.js \
  profile-posts.js \
  hobbies-picker.js \
  mobile-bottom-nav.js \
  profile-settings.js \
  flagged-select.js \
  chat.js
do
  minify_js_file "$JS_DIR/$f" "$JS_DIR/${f%.js}.min.js"
done

echo "== core JS (every authenticated page) =="
bundle_js "$JS_DIR/core.min.js" \
  "$JS_DIR/badges.js" \
  "$JS_DIR/live-sync.js" \
  "$JS_DIR/page-auto-refresh.js"

echo "== app-shell JS bundle =="
bundle_js "$JS_DIR/app-shell.min.js" \
  "$JS_DIR/profile-settings.js" \
  "$JS_DIR/hobbies-picker.js" \
  "$JS_DIR/locations.js" \
  "$JS_DIR/mobile-bottom-nav.js"

echo "== feed-page JS bundle =="
bundle_js "$JS_DIR/feed-page.min.js" \
  "$JS_DIR/feed.js" \
  "$JS_DIR/stories.js"

echo "== profile-page JS bundle =="
bundle_js "$JS_DIR/profile-page.min.js" \
  "$JS_DIR/feed.js" \
  "$JS_DIR/profile-posts.js" \
  "$JS_DIR/profile-photo.js" \
  "$JS_DIR/stories.js"

echo "== register/auth helper JS =="
bundle_js "$JS_DIR/register.min.js" \
  "$JS_DIR/hobbies-picker.js" \
  "$JS_DIR/flagged-select.js" \
  "$JS_DIR/locations.js"

echo "OK minify-web-assets"
