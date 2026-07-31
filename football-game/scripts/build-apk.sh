#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"
npm install
npm run build
npx cap sync android
cd android
./gradlew assembleDebug
OUT="$ROOT/android/app/build/outputs/apk/debug/app-debug.apk"
mkdir -p "$ROOT/releases"
cp "$OUT" "$ROOT/releases/GolArena-debug.apk"
echo "APK: $ROOT/releases/GolArena-debug.apk"
