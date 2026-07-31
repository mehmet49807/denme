# Odysseus (Gönül Köprüsü)

Admin panelinden komut verip kod değişikliği yaptıran self-hosted AI workspace.

Upstream: https://github.com/odysseus-dev/odysseus

## Kurulum (sunucu)

```bash
bash scripts/odysseus/install.sh
bash scripts/odysseus/start.sh
```

Servis: `http://127.0.0.1:7000`

## Admin

`admin.gonulkoprusu.com` → **Sistem → Odysseus**

Ortam değişkenleri (`admin-panel/.env`):

```
ODYSSEUS_URL=http://127.0.0.1:7000
ODYSSEUS_USER=admin
ODYSSEUS_PASSWORD=...
ODYSSEUS_WORKSPACE=/home/gonulkop/apps/gonulkoprusu
ODYSSEUS_ENDPOINT_URL=https://api.openai.com/v1
ODYSSEUS_API_KEY=sk-...
ODYSSEUS_MODEL=gpt-4o-mini
```

Odysseus bir aracıdır; arkasında bir LLM sağlayıcı gerekir (OpenAI, Groq, Gemini, vb.).
`ODYSSEUS_ENDPOINT_URL` provider base URL olmalı (`…/v1`), `…/chat/completions` değil.

Admin komutları agent modunda `ODYSSEUS_WORKSPACE` altında dosya okur/yazar.
