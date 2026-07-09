#!/usr/bin/env bash
# GitHub Actions FTP deploy secret'larını gh CLI ile kaydeder.
# Kullanım:
#   cp .env.deploy.example .env.deploy
#   # .env.deploy içine FTP şifrelerini yazın
#   bash scripts/setup/github-deploy-setup.sh
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
ENV_FILE="${1:-$ROOT/.env.deploy}"

if [[ ! -f "$ENV_FILE" ]]; then
  echo "Hata: $ENV_FILE bulunamadı."
  echo "  cp .env.deploy.example .env.deploy"
  echo "  # ardından FTP şifrelerini doldurun"
  exit 1
fi

# shellcheck disable=SC1090
set -a
source "$ENV_FILE"
set +a

REQUIRED_VARS=(
  FTP_WEB_USER
  FTP_WEB_PASSWORD
  FTP_ADMIN_USER
  FTP_ADMIN_PASSWORD
  SETUP_CACHE_KEY
)

for name in "${REQUIRED_VARS[@]}"; do
  if [[ -z "${!name:-}" ]]; then
    echo "Hata: $ENV_FILE içinde $name boş."
    exit 1
  fi
done

if ! command -v gh >/dev/null 2>&1; then
  echo "Hata: GitHub CLI (gh) yüklü değil."
  echo "  https://cli.github.com/"
  exit 1
fi

gh auth status >/dev/null

REPO="${GITHUB_REPO:-}"
if [[ -z "$REPO" ]]; then
  REPO="$(gh repo view --json nameWithOwner -q .nameWithOwner)"
fi

echo "Repo: $REPO"
echo "GitHub Secrets kaydediliyor..."

for name in "${REQUIRED_VARS[@]}"; do
  echo "  -> $name"
  gh secret set "$name" --repo "$REPO" --body "${!name}"
done

echo ""
echo "Tamam. Secrets kaydedildi."
echo "Doğrulama: GitHub → $REPO → Settings → Secrets and variables → Actions"
