#!/usr/bin/env bash
# Restores the Brushed template _include/ layout expected by index.html.
# Safe to run repeatedly (idempotent).
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

mkdir -p _include/css/fancybox _include/js _include/php \
  _include/img/slider-images _include/img/work/thumbs _include/img/work/full _include/img/profile

link_if_missing() {
  local target="$1"
  local link="$2"
  if [[ -L "$link" ]]; then
    return 0
  fi
  if [[ -e "$link" ]]; then
    return 0
  fi
  ln -s "$target" "$link"
}

# CSS
link_if_missing "../../bootstrap.min.css" "_include/css/bootstrap.min.css"
link_if_missing "../../main.css" "_include/css/main.css"
link_if_missing "../../supersized.css" "_include/css/supersized.css"
link_if_missing "../../supersized.shutter.css" "_include/css/supersized.shutter.css"
link_if_missing "../../jquery.fancybox.css" "_include/css/fancybox/jquery.fancybox.css"
link_if_missing "../../fonts.css" "_include/css/fonts.css"
link_if_missing "../../shortcodes.css" "_include/css/shortcodes.css"
link_if_missing "../../bootstrap-responsive.min.css" "_include/css/bootstrap-responsive.min.css"
link_if_missing "../../responsive.css" "_include/css/responsive.css"

# JS
for js in modernizr.js bootstrap.min.js supersized.3.2.7.min.js waypoints.js \
  waypoints-sticky.js jquery.isotope.js jquery.fancybox.pack.js jquery.fancybox-media.js \
  jquery.tweet.js plugins.js main.js placeholder.js; do
  link_if_missing "../../$js" "_include/js/$js"
done

# PHP contact handler
link_if_missing "../../contact.php" "_include/php/contact.php"

# Placeholder JPEGs (repo ships without image assets)
MIN_JPEG="$ROOT/scripts/minimal.jpg"
if [[ ! -f "$MIN_JPEG" ]]; then
  python3 "$ROOT/scripts/create-minimal-jpeg.py" "$MIN_JPEG"
fi

for img in \
  _include/img/slider-images/image01.jpg \
  _include/img/slider-images/image02.jpg \
  _include/img/slider-images/image03.jpg \
  _include/img/slider-images/image04.jpg \
  _include/img/profile/profile-01.jpg \
  _include/img/profile/profile-02.jpg \
  _include/img/profile/profile-03.jpg \
  _include/img/work/thumbs/image-01.jpg \
  _include/img/work/thumbs/image-02.jpg \
  _include/img/work/thumbs/image-03.jpg \
  _include/img/work/thumbs/image-04.jpg \
  _include/img/work/thumbs/image-05.jpg \
  _include/img/work/thumbs/image-06.jpg \
  _include/img/work/thumbs/image-07.jpg \
  _include/img/work/thumbs/image-08.jpg \
  _include/img/work/thumbs/image-09.jpg \
  _include/img/work/full/image-01-full.jpg \
  _include/img/work/full/image-02-full.jpg \
  _include/img/work/full/image-03-full.jpg \
  _include/img/work/full/image-04-full.jpg \
  _include/img/work/full/image-05-full.jpg \
  _include/img/work/full/image-06-full.jpg \
  _include/img/work/full/image-07-full.jpg; do
  if [[ ! -e "$img" ]]; then
    cp "$MIN_JPEG" "$img"
  fi
done

echo "Dev layout ready under $ROOT/_include"
