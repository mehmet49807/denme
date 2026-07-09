#!/usr/bin/env bash
# Deploy PR'ını merge eder ve ilk GitHub Actions deploy'unu izler.
# Önce: bash scripts/setup/github-deploy-setup.sh
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
ENV_FILE="$ROOT/.env.deploy"

if [[ -f "$ENV_FILE" ]]; then
  # shellcheck disable=SC1090
  set -a
  source "$ENV_FILE"
  set +a
fi

REPO="${GITHUB_REPO:-mehmet49807/denme}"
BRANCH="${DEPLOY_BRANCH:-cursor/github-auto-deploy-9613}"
BASE="${DEPLOY_BASE:-master}"

if ! command -v gh >/dev/null 2>&1; then
  echo "Hata: gh CLI gerekli."
  exit 1
fi

gh auth status >/dev/null

echo "Repo: $REPO"
echo "PR: $BRANCH → $BASE"

PR_NUMBER="$(gh pr list --repo "$REPO" --head "$BRANCH" --base "$BASE" --json number -q '.[0].number' 2>/dev/null || true)"

if [[ -z "$PR_NUMBER" ]]; then
  echo "Açık PR yok; oluşturuluyor..."
  gh pr create --repo "$REPO" \
    --base "$BASE" \
    --head "$BRANCH" \
    --title "GitHub Actions otomatik FTP deploy" \
    --body "$(cat <<'EOF'
## Özet

`master` dalına push yapıldığında web + admin FTP deploy çalışır.

## Kurulum

Secrets script ile eklenmiş olmalı:
`bash scripts/setup/github-deploy-setup.sh`

## Dal stratejisi

- `cursor/*` → geliştirme / PR
- `master` → canlı deploy (otomatik)
EOF
)"
  PR_NUMBER="$(gh pr list --repo "$REPO" --head "$BRANCH" --json number -q '.[0].number')"
fi

echo "PR #$PR_NUMBER merge ediliyor..."
gh pr merge "$PR_NUMBER" --repo "$REPO" --merge

echo "Deploy workflow bekleniyor..."
sleep 5
RUN_ID="$(gh run list --repo "$REPO" --workflow deploy.yml --branch "$BASE" --limit 1 --json databaseId -q '.[0].databaseId')"

if [[ -n "$RUN_ID" && "$RUN_ID" != "null" ]]; then
  gh run watch "$RUN_ID" --repo "$REPO" --exit-status
  echo "Deploy tamamlandı."
else
  echo "Workflow henüz başlamadı. GitHub → Actions → Deploy to cPanel kontrol edin."
fi
