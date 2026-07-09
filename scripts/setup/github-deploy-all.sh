#!/usr/bin/env bash
# Tek komut: secrets + merge + deploy izleme
#   cp .env.deploy.example .env.deploy  # şifreleri doldurun
#   bash scripts/setup/github-deploy-all.sh
set -euo pipefail
DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
bash "$DIR/github-deploy-setup.sh"
bash "$DIR/github-merge-deploy.sh"
