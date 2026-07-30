#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
TARGET="${ODYSSEUS_DIR:-$ROOT/odysseus}"
HOST="${ODYSSEUS_HOST:-127.0.0.1}"
PORT="${ODYSSEUS_PORT:-7000}"
PID_FILE="${ODYSSEUS_PID_FILE:-$TARGET/odysseus.pid}"
LOG_FILE="${ODYSSEUS_LOG_FILE:-$TARGET/odysseus.log}"

if [[ ! -f "$TARGET/app.py" ]]; then
  echo "Odysseus not installed. Run: bash scripts/odysseus/install.sh" >&2
  exit 1
fi

cd "$TARGET"
# shellcheck disable=SC1091
source venv/bin/activate

if [[ -f "$PID_FILE" ]] && kill -0 "$(cat "$PID_FILE")" 2>/dev/null; then
  echo "Odysseus already running (pid $(cat "$PID_FILE")) on ${HOST}:${PORT}"
  exit 0
fi

nohup python -m uvicorn app:app --host "$HOST" --port "$PORT" >"$LOG_FILE" 2>&1 &
echo $! >"$PID_FILE"
echo "Odysseus started pid=$(cat "$PID_FILE") → http://${HOST}:${PORT}"
echo "Logs: $LOG_FILE"
