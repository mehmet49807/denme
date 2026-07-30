#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
TARGET="${ODYSSEUS_DIR:-$ROOT/odysseus}"
REPO_URL="${ODYSSEUS_REPO:-https://github.com/odysseus-dev/odysseus.git}"

echo "==> Odysseus install → $TARGET"

if [[ ! -f "$TARGET/app.py" ]]; then
  rm -rf "$TARGET"
  git clone --depth 1 "$REPO_URL" "$TARGET"
fi

cd "$TARGET"

if [[ ! -d venv ]]; then
  python3 -m venv venv
fi

# shellcheck disable=SC1091
source venv/bin/activate
pip install --upgrade pip
pip install -r requirements.txt

if [[ ! -f .env ]]; then
  cp .env.example .env
  {
    echo ""
    echo "# Gönül Köprüsü defaults"
    echo "APP_BIND=127.0.0.1"
    echo "APP_PORT=7000"
    echo "AUTH_ENABLED=true"
  } >> .env
fi

python setup.py || true

echo "==> Odysseus installed. Start with: bash scripts/odysseus/start.sh"
