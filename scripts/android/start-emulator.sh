#!/usr/bin/env bash
set -euo pipefail
export ANDROID_HOME="${ANDROID_HOME:-/opt/android-sdk}"
export PATH="$ANDROID_HOME/cmdline-tools/latest/bin:$ANDROID_HOME/platform-tools:$ANDROID_HOME/emulator:$PATH"
AVD_NAME="${1:-GonulPhone}"
# Cloud VMs often lack /dev/kvm — fall back to software accel.
ACCEL_ARGS=(-gpu swiftshader_indirect -no-metrics)
if [ ! -e /dev/kvm ]; then
  ACCEL_ARGS+=(-accel off)
fi
exec emulator -avd "$AVD_NAME" -no-window -no-audio -no-boot-anim "${ACCEL_ARGS[@]}"
