# Odysseus (Gönül Köprüsü)

Self-hosted AI workspace. İki kullanım birlikte:

1. **Bağımsız** — `https://odysseus.gonulkoprusu.com` arayüzünden kullan; model key’lerini Settings’te tanımla.
2. **Admin tetik** — `admin.gonulkoprusu.com` → **Sistem → Odysseus** komut köprüsü; Settings’teki modeli kullanır.

Upstream: https://github.com/odysseus-dev/odysseus

## Kurulum (sunucu)

```bash
bash scripts/odysseus/install.sh
bash scripts/odysseus/start.sh
```

Servis (iç): `http://127.0.0.1:7000`  
Genel arayüz: `https://odysseus.gonulkoprusu.com` (reverse proxy → 7000)

## Model / API key

Model API key’leri **admin .env’ye yazılmaz**.  
Odysseus UI → **Settings → Model endpoint** (OpenAI / Groq / Gemini vb.).

## Admin .env

```
ODYSSEUS_URL=http://127.0.0.1:7000
ODYSSEUS_PUBLIC_URL=https://odysseus.gonulkoprusu.com
ODYSSEUS_USER=admin
ODYSSEUS_PASSWORD=...
ODYSSEUS_WORKSPACE=/home/gonulkop/apps/gonulkoprusu
# İsteğe bağlı tercih (Settings’teki kayıtlar)
ODYSSEUS_ENDPOINT_ID=
ODYSSEUS_MODEL=
ODYSSEUS_TIMEOUT=300
```

Admin komutları agent modunda `ODYSSEUS_WORKSPACE` altında dosya okur/yazar.
