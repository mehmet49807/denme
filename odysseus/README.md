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
ODYSSEUS_WORKSPACE=/path/to/repo
ODYSSEUS_MODEL=
ODYSSEUS_ENDPOINT_URL=https://openrouter.ai/api/v1/chat/completions
ODYSSEUS_API_KEY=
```

Model endpoint Odysseus Settings içinde de tanımlanabilir; admin komutları agent modunda `ODYSSEUS_WORKSPACE` altında dosya okur/yazar.
