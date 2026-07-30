#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
TARGET="${ODYSSEUS_DIR:-$ROOT/odysseus}"
PID_FILE="${ODYSSEUS_PID_FILE:-$TARGET/odysseus.pid}"

if [[ -f "$PID_FILE" ]]; then
  pid="$(cat "$PID_FILE")"
  if kill -0 "$pid" 2>/dev/null; then
    kill "$pid" || true
    echo "Stopped Odysseus pid=$pid"
  fi
  rm -f "$PID_FILE"
else
  echo "No pid file; nothing to stop."
fi
