#!/usr/bin/env python3
"""Gönül Köprüsü — gerçekçi stok görüntülü reklam videoları (Mixkit + altyazı + ses).

Araştırma özeti (Meta/dating 2025–26):
- Reels/Stories: 9:16, ilk 2 sn hook, 15–30 sn
- UGC / gerçek görüntü > aşırı prodüksiyon
- Altyazı şart (sessiz izleme)
- Kimlik odaklı mesaj (ciddi ilişki, güven, evlilik)
"""

from __future__ import annotations

import asyncio
import json
import shutil
import subprocess
from pathlib import Path

from PIL import Image, ImageDraw, ImageEnhance, ImageFont, ImageOps

ROOT = Path(__file__).resolve().parents[2]
STOCK = ROOT / "marketing" / "stock" / "mixkit"
OUT = ROOT / "marketing" / "realistic-ads"
PUBLIC = ROOT / "web-site" / "public" / "images" / "ads"
MIRROR = ROOT / "web-site" / "public" / "marketing" / "ads"
ART = Path("/opt/cursor/artifacts/realistic-ads")
LOGO = ROOT / "web-site" / "public" / "images" / "logo-320.png"

FONT_BOLD = "/usr/share/fonts/truetype/dejavu/DejaVuSerif-Bold.ttf"
FONT_REG = "/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf"
FONT_BOLD_SANS = "/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf"
VOICE = "tr-TR-EmelNeural"

# Clip ids from Mixkit downloads
CLIPS = {
    "a": STOCK / "mixkit-939.mp4",
    "b": STOCK / "mixkit-40601.mp4",
    "c": STOCK / "mixkit-4664.mp4",
    "d": STOCK / "mixkit-4661.mp4",
    "e": STOCK / "mixkit-51657.mp4",
    "f": STOCK / "mixkit-42255.mp4",
    "g": STOCK / "mixkit-42265.mp4",
    "h": STOCK / "mixkit-42511.mp4",
}

# v2: kısa kesim, 1. sn içinde okunabilir hook, yumuşak şiirsel satır YOK
ADS = [
    {
        "id": "ig-01-dur",
        "title": "Flört yorduysa dur",
        "subtitle": "Pattern interrupt · Reels v2",
        "format_primary": "9:16",
        "channel": "Instagram Reels",
        "clips": [("a", 0.2, 1.8), ("c", 0.4, 2.0), ("d", 0.5, 2.0), ("b", 0.3, 1.9)],
        "captions": [
            (0.0, 1.6, "Flört yorduysa…"),
            (1.6, 3.4, "Ciddi ilişki ara."),
            (3.4, 5.6, "Kart yok. Ücretsiz."),
            (5.6, 7.8, "Gönül Köprüsü"),
        ],
        "voice": (
            "Flört uygulamaları yorduysa dur. Ciddi ilişki arıyorsan Gönül Köprüsü. "
            "Kart bilgisi yok. Ücretsiz üye ol."
        ),
        "cta": "Ücretsiz kayıt → gonulkoprusu.com",
        "ig_caption": (
            "Flört yorduysa… ciddi ilişki zamanı.\n"
            "Gönül Köprüsü’nde güvenli tanış, evlilik niyetiyle ilerle.\n"
            "Kart bilgisi yok · Ücretsiz kayıt 👇\n"
            "{url}\n\n"
            "#ciddiilişki #evlilik #güvenlitanışma #gonülköprüsü #tanışma "
            "#evliliğeğilimli #ilişki #aile #Türkçe #reels"
        ),
    },
    {
        "id": "ig-02-soru",
        "title": "Gerçekten ciddi misin?",
        "subtitle": "Soru hook · Reels v2",
        "format_primary": "9:16",
        "channel": "Instagram Reels",
        "clips": [("c", 0.2, 1.7), ("d", 0.2, 2.0), ("a", 0.6, 1.9), ("b", 0.4, 1.9)],
        "captions": [
            (0.0, 1.5, "Ciddi misin?"),
            (1.5, 3.3, "Gerçekten mi?"),
            (3.3, 5.5, "O zaman buradasın."),
            (5.5, 7.6, "Ücretsiz üye ol"),
        ],
        "voice": (
            "Ciddi misin? Gerçekten mi? O zaman Gönül Köprüsü’ndesin. "
            "Güvenli tanışma, evlilik odaklı. Ücretsiz üye ol."
        ),
        "cta": "Bio’daki linke dokun",
        "ig_caption": (
            "Ciddi misin? Gerçekten mi?\n"
            "O zaman doğru yerdesin — Gönül Köprüsü.\n"
            "Flört değil; güvenli, ciddi tanışma. Ücretsiz başla 👇\n"
            "{url}\n\n"
            "#ciddiilişki #evlilikarıyorum #güvenliuygulama #gonülköprüsü "
            "#tanışmasitesi #evlilik #reels #Türkiye"
        ),
    },
    {
        "id": "ig-03-guven",
        "title": "Güven önce gelir",
        "subtitle": "Güven mesajı · Reels v2",
        "format_primary": "9:16",
        "channel": "Instagram Reels · Stories",
        "clips": [("d", 0.15, 1.8), ("h", 0.15, 1.9), ("a", 1.5, 2.0), ("b", 0.8, 1.8)],
        "captions": [
            (0.0, 1.5, "Önce güven."),
            (1.5, 3.4, "Sonra sohbet."),
            (3.4, 5.5, "Profilini tamamla."),
            (5.5, 7.6, "Gönül Köprüsü"),
        ],
        "voice": (
            "Önce güven. Sonra sohbet. Gönül Köprüsü’nde profilini tamamla, "
            "şehrini seç, saygılı bir bağ kur. Ücretsiz kayıt ol."
        ),
        "cta": "Ücretsiz kayıt ol",
        "ig_caption": (
            "Önce güven, sonra sohbet.\n"
            "Gönül Köprüsü: güvenli profiller, ciddi niyet, gerçek bağ.\n"
            "Şehrini seç · Ücretsiz üye ol 👇\n"
            "{url}\n\n"
            "#güvenlitanışma #ciddiilişki #profil #gonülköprüsü #evlilik "
            "#güven #reels #İstanbul #Ankara #İzmir"
        ),
    },
    {
        "id": "ig-04-sehir",
        "title": "Şehrinde ciddi tanışma",
        "subtitle": "Yerel SEO · Reels v2",
        "format_primary": "9:16",
        "channel": "Instagram Reels",
        "clips": [("c", 0.3, 1.8), ("b", 1.2, 2.0), ("a", 1.0, 1.8), ("d", 0.5, 1.9)],
        "captions": [
            (0.0, 1.6, "Şehrinde misin?"),
            (1.6, 3.5, "Ciddi biriyle tanış."),
            (3.5, 5.6, "İstanbul · Ankara · İzmir"),
            (5.6, 7.7, "Ücretsiz başla"),
        ],
        "voice": (
            "Şehrinde misin? Ciddi biriyle tanışmak istiyorsan Gönül Köprüsü. "
            "İstanbul, Ankara, İzmir ve daha fazlası. Ücretsiz başla."
        ),
        "cta": "gonulkoprusu.com",
        "ig_caption": (
            "Şehrinde ciddi ilişki arayanlar burada.\n"
            "İstanbul · Ankara · İzmir · Bursa · Antalya…\n"
            "Gönül Köprüsü’nde ücretsiz kayıt ol 👇\n"
            "{url}\n\n"
            "#İstanbultanışma #Ankaratanışma #İzmirtanışma #ciddiilişki "
            "#evlilik #gonülköprüsü #şehir #reels"
        ),
    },
    {
        "id": "ig-05-evlilik",
        "title": "Evlilik niyetiyle",
        "subtitle": "Dönüşüm CTA · Reels v2",
        "format_primary": "9:16",
        "channel": "Instagram Reels · Feed",
        "clips": [("b", 0.2, 2.2), ("c", 0.8, 1.8), ("a", 0.5, 1.8), ("d", 0.8, 1.8)],
        "captions": [
            (0.0, 1.5, "Evlilik niyetiyle?"),
            (1.5, 3.4, "Doğru yerdesin."),
            (3.4, 5.5, "Bugün ilk adımı at."),
            (5.5, 7.6, "Ücretsiz üye ol"),
        ],
        "voice": (
            "Evlilik niyetiyle mi arıyorsun? Doğru yerdesin. "
            "Gönül Köprüsü. Bugün ücretsiz üye ol, ilk adımı at."
        ),
        "cta": "Ücretsiz üye ol → linkte",
        "ig_caption": (
            "Evlilik niyetiyle arıyorsan doğru yerdesin.\n"
            "Gönül Köprüsü — flört temposu değil; ciddi bağ.\n"
            "Ücretsiz üye ol, profilini tamamla 👇\n"
            "{url}\n\n"
            "#evlilik #evliliğeğilimli #ciddiilişki #gonülköprüsü "
            "#yuva #aile #güvenlitanışma #reels"
        ),
    },
    {
        "id": "ig-06-web",
        "title": "Web / YouTube Display v2",
        "subtitle": "16:9 display paketi",
        "format_primary": "16:9",
        "channel": "YouTube · Display · Landing",
        "clips": [("a", 0.3, 2.2), ("b", 0.2, 2.2), ("d", 0.6, 2.4)],
        "captions": [
            (0.0, 1.8, "Ciddi ilişki arayanlar"),
            (1.8, 4.0, "Güvenli tanışma burada"),
            (4.0, 6.5, "gonulkoprusu.com"),
        ],
        "voice": (
            "Gönül Köprüsü. Ciddi ilişki arayanlar burada. "
            "Güvenli tanışma, evlilik odaklı. Ücretsiz üye ol."
        ),
        "cta": "Ücretsiz üye ol — gonulkoprusu.com",
        "ig_caption": "",
    },
]


def ensure_dirs() -> None:
    for p in (OUT, PUBLIC, MIRROR, ART, OUT / "parts", OUT / "vo"):
        p.mkdir(parents=True, exist_ok=True)


def run(cmd: list[str]) -> None:
    r = subprocess.run(cmd, capture_output=True, text=True)
    if r.returncode != 0:
        raise RuntimeError(f"cmd failed: {' '.join(cmd[:6])}…\n{r.stderr[-1200:]}")


def audio_duration(path: Path) -> float:
    r = subprocess.run(
        [
            "ffprobe",
            "-v",
            "error",
            "-show_entries",
            "format=duration",
            "-of",
            "default=noprint_wrappers=1:nokey=1",
            str(path),
        ],
        capture_output=True,
        text=True,
        check=True,
    )
    return max(4.0, float(r.stdout.strip()))


async def synth_voice(text: str, mp3: Path) -> None:
    import edge_tts

    if mp3.exists() and mp3.stat().st_size > 1000:
        return
    communicate = edge_tts.Communicate(text, VOICE, rate="-4%")
    await communicate.save(str(mp3))


def escape_drawtext(text: str) -> str:
    return (
        text.replace("\\", "\\\\")
        .replace(":", "\\:")
        .replace("'", "\u2019")
        .replace("%", "\\%")
    )


def make_endcard(ad: dict, size: tuple[int, int], out: Path) -> Path:
    w, h = size
    img = Image.new("RGB", (w, h), (28, 12, 24))
    # gradient-ish bands
    draw = ImageDraw.Draw(img, "RGBA")
    for y in range(h):
        t = y / h
        r = int(28 + 40 * t)
        g = int(12 + 10 * (1 - t))
        b = int(40 + 30 * t)
        draw.line([(0, y), (w, y)], fill=(r, g, b, 255))

    if LOGO.is_file():
        logo = Image.open(LOGO).convert("RGBA")
        logo.thumbnail((int(w * 0.22), int(w * 0.22)), Image.Resampling.LANCZOS)
        logo = ImageEnhance.Brightness(logo).enhance(1.12)
        img.paste(logo, ((w - logo.width) // 2, int(h * 0.18)), logo)

    brand = ImageFont.truetype(FONT_BOLD, size=max(36, w // 18))
    line = ImageFont.truetype(FONT_BOLD_SANS, size=max(28, w // 28))
    cta_f = ImageFont.truetype(FONT_REG, size=max(22, w // 36))

    def center(y: int, text: str, font, fill=(255, 240, 230, 255)) -> None:
        bb = draw.textbbox((0, 0), text, font=font)
        tw = bb[2] - bb[0]
        draw.text(((w - tw) / 2, y), text, font=font, fill=fill)

    center(int(h * 0.42), "Gönül Köprüsü", brand, (255, 236, 214, 255))
    center(int(h * 0.52), ad["title"][:42], line, (255, 255, 255, 255))
    # CTA pill
    cta = ad["cta"]
    cb = draw.textbbox((0, 0), cta, font=cta_f)
    cw = cb[2] - cb[0] + 56
    ch = max(48, h // 22)
    cx0 = (w - cw) // 2
    cy0 = int(h * 0.68)
    draw.rounded_rectangle((cx0, cy0, cx0 + cw, cy0 + ch), radius=ch // 2, fill=(190, 38, 78, 235))
    center(cy0 + ch // 5, cta, cta_f, (255, 255, 255, 255))
    center(int(h * 0.82), "@gonulkoprusucom", cta_f, (255, 210, 180, 220))

    out.parent.mkdir(parents=True, exist_ok=True)
    img.save(out, "PNG", optimize=True)
    return out


def clip_segment(src: Path, start: float, dur: float, size: tuple[int, int], out: Path) -> None:
    w, h = size
    # cover crop to target aspect
    vf = (
        f"scale={w}:{h}:force_original_aspect_ratio=increase,"
        f"crop={w}:{h},fps=30,setsar=1,"
        f"eq=contrast=1.05:saturation=1.08,"
        f"format=yuv420p"
    )
    run(
        [
            "ffmpeg",
            "-y",
            "-ss",
            f"{start:.2f}",
            "-t",
            f"{dur:.2f}",
            "-i",
            str(src),
            "-vf",
            vf,
            "-an",
            "-c:v",
            "libx264",
            "-preset",
            "veryfast",
            "-crf",
            "23",
            str(out),
        ]
    )


def endcard_video(png: Path, dur: float, size: tuple[int, int], out: Path) -> None:
    w, h = size
    run(
        [
            "ffmpeg",
            "-y",
            "-loop",
            "1",
            "-t",
            f"{dur:.2f}",
            "-i",
            str(png),
            "-vf",
            f"scale={w}:{h},fps=30,format=yuv420p",
            "-c:v",
            "libx264",
            "-tune",
            "stillimage",
            "-pix_fmt",
            "yuv420p",
            str(out),
        ]
    )


def concat_parts(parts: list[Path], out: Path) -> None:
    lst = out.with_suffix(".txt")
    lst.write_text("".join(f"file '{p.resolve()}'\n" for p in parts), encoding="utf-8")
    run(
        [
            "ffmpeg",
            "-y",
            "-f",
            "concat",
            "-safe",
            "0",
            "-i",
            str(lst),
            "-c",
            "copy",
            str(out),
        ]
    )


def burn_captions_and_mux(
    video: Path,
    audio: Path,
    captions: list[tuple[float, float, str]],
    size: tuple[int, int],
    out: Path,
    total_dur: float,
) -> None:
    w, h = size
    # Büyük, kısa hook metni — ilk 2 sn’de okunabilir (Reels retention)
    base = max(42, w // 16 if w < h else w // 28)
    y_expr = f"h*{0.58 if w < h else 0.70}"
    filters = []
    for i, (start, end, text) in enumerate(captions):
        t = escape_drawtext(text)
        # İlk satır (hook) daha büyük
        fs = int(base * (1.22 if i == 0 else 1.0))
        bw = 4 if i == 0 else 3
        filters.append(
            f"drawtext=fontfile={FONT_BOLD_SANS}:text='{t}':fontsize={fs}:"
            f"fontcolor=white:borderw={bw}:bordercolor=black@0.65:"
            f"x=(w-text_w)/2:y={y_expr}:"
            f"enable='between(t,{start:.2f},{end:.2f})'"
        )
    # Üstte küçük marka — hero metni ezmesin
    filters.append(
        f"drawtext=fontfile={FONT_BOLD}:text='Gönül Köprüsü':fontsize={max(20, w // 42)}:"
        f"fontcolor=white@0.88:x=(w-text_w)/2:y={int(h * 0.055 if w < h else h * 0.045)}:"
        f"shadowcolor=black@0.45:shadowx=1:shadowy=1"
    )
    vf = ",".join(filters)
    run(
        [
            "ffmpeg",
            "-y",
            "-i",
            str(video),
            "-i",
            str(audio),
            "-vf",
            vf,
            "-c:v",
            "libx264",
            "-preset",
            "veryfast",
            "-crf",
            "22",
            "-c:a",
            "aac",
            "-b:a",
            "160k",
            "-shortest",
            "-t",
            f"{total_dur:.2f}",
            "-movflags",
            "+faststart",
            str(out),
        ]
    )


def extract_poster(video: Path, out: Path, t: float = 1.2) -> None:
    run(
        [
            "ffmpeg",
            "-y",
            "-ss",
            f"{t:.2f}",
            "-i",
            str(video),
            "-frames:v",
            "1",
            "-q:v",
            "2",
            str(out),
        ]
    )


def publish(src: Path, name: str | None = None) -> Path:
    dest = PUBLIC / (name or src.name)
    shutil.copy2(src, dest)
    shutil.copy2(src, MIRROR / dest.name)
    shutil.copy2(src, ART / dest.name)
    return dest


def build_ad(ad: dict, size: tuple[int, int], suffix: str) -> dict:
    w, h = size
    parts_dir = OUT / "parts" / f"{ad['id']}{suffix}"
    parts_dir.mkdir(parents=True, exist_ok=True)
    parts: list[Path] = []
    t = 0.0
    # rescale caption times relative to assembled footage before endcard
    for i, (key, start, dur) in enumerate(ad["clips"]):
        src = CLIPS[key]
        if not src.is_file():
            raise FileNotFoundError(src)
        part = parts_dir / f"p{i}.mp4"
        clip_segment(src, start, dur, size, part)
        parts.append(part)
        t += dur

    end_png = parts_dir / "end.png"
    make_endcard(ad, size, end_png)
    end_mp4 = parts_dir / "end.mp4"
    end_dur = 3.2
    endcard_video(end_png, end_dur, size, end_mp4)
    parts.append(end_mp4)

    silent = parts_dir / "silent.mp4"
    concat_parts(parts, silent)

    vo = OUT / "vo" / f"{ad['id']}.mp3"
    # voice already synthesized in main
    vo_dur = audio_duration(vo)
    total = max(t + end_dur, vo_dur + 0.4)

    # shift captions: keep relative to clip timeline
    caps = list(ad["captions"])
    caps.append((t, t + end_dur - 0.2, ad["cta"][:48]))

    out_mp4 = OUT / f"{ad['id']}{suffix}.mp4"
    burn_captions_and_mux(silent, vo, caps, size, out_mp4, total)
    poster = OUT / f"{ad['id']}{suffix}.png"
    extract_poster(out_mp4, poster, t=1.0)
    # also publish endcard as still creative
    still = OUT / f"{ad['id']}{suffix}-still.png"
    shutil.copy2(end_png, still)

    publish(out_mp4)
    publish(poster)
    publish(still)
    return {
        "id": f"{ad['id']}{suffix}".lstrip("-") if False else f"{ad['id']}{suffix}",
        "title": ad["title"],
        "subtitle": ad["subtitle"],
        "format": f"{w}:{h}".replace("1080:1920", "9:16").replace("1920:1080", "16:9")
        if False
        else ("9:16" if h > w else "16:9"),
        "channel": ad["channel"],
        "video": out_mp4.name,
        "poster": poster.name,
        "still": still.name,
        "duration_hint": f"{total:.0f}s",
        "kind": "realistic",
        "cta_url": "https://gonulkoprusu.com/kampanya?utm_source=ads&utm_medium=video&utm_campaign=realistic",
    }


async def main() -> None:
    ensure_dirs()
    missing = [k for k, p in CLIPS.items() if not p.is_file()]
    if missing:
        raise SystemExit(f"Missing stock clips: {missing}")

    print("== Voice ==")
    for ad in ADS:
        vo = OUT / "vo" / f"{ad['id']}.mp3"
        print("voice", ad["id"])
        await synth_voice(ad["voice"], vo)

    manifest_videos: list[dict] = []
    print("== Render ==")
    for ad in ADS:
        if ad["format_primary"] == "16:9":
            print("16:9", ad["id"])
            manifest_videos.append(build_ad(ad, (1920, 1080), ""))
        else:
            print("9:16", ad["id"])
            manifest_videos.append(build_ad(ad, (1080, 1920), ""))
            # also produce landscape variant for web
            print("16:9 variant", ad["id"])
            manifest_videos.append(build_ad(ad, (1920, 1080), "-wide"))

    # Merge existing classic ads into index by scanning PUBLIC
    # Write research-backed README
    readme = PUBLIC / "README.txt"
    lines = [
        "Gönül Köprüsü — Reklam Medya Paketi",
        "=================================",
        "",
        "Kaynak: Mixkit ücretsiz stok görüntü (ticari kullanım) + marka end-card + TR ses",
        "Araştırma: Meta Reels 9:16, ilk 2 sn hook, altyazı, UGC/gerçek görüntü",
        "",
        "Gerçekçi (rx-*):",
    ]
    for v in manifest_videos:
        lines.append(f"- {v['video']} / {v['poster']} — {v['title']} ({v['format']})")
    lines += [
        "",
        "Klasik (web-* / story-*): önceki Ken Burns paketleri",
        "Admin: Reklam menüsü → tüm video + fotoğraflar",
        "Canlı: https://gonulkoprusu.com/images/ads/",
        "",
        "Mixkit attribution: https://mixkit.co (Free Stock Video)",
    ]
    readme.write_text("\n".join(lines) + "\n", encoding="utf-8")
    shutil.copy2(readme, MIRROR / "README.txt")
    shutil.copy2(readme, ART / "README.txt")

    # Full gallery manifest for admin
    videos = []
    photos = []
    for p in sorted(PUBLIC.iterdir()):
        if not p.is_file():
            continue
        name = p.name
        if name in {"README.txt", "manifest.json", "index.html"}:
            continue
        rel = name
        if name.endswith(".mp4"):
            poster = name.replace(".mp4", ".png")
            if not (PUBLIC / poster).exists():
                poster = ""
            kind = "realistic" if name.startswith(("rx-", "ig-")) else "classic"
            is_vertical = (
                "story-" in name
                or (
                    name.startswith(("rx-", "ig-"))
                    and "-wide" not in name
                    and "web-" not in name
                    and not name.startswith("ig-06-web")
                )
            )
            videos.append(
                {
                    "file": rel,
                    "title": name.replace(".mp4", "").replace("-", " "),
                    "poster": poster,
                    "kind": kind,
                    "format": "9:16" if is_vertical else "16:9",
                }
            )
        elif name.lower().endswith((".png", ".jpg", ".jpeg", ".webp")):
            photos.append({"file": rel, "title": name.replace("-", " "), "kind": "still"})

    # Prefer richer metadata for rx videos
    by_file = {v["video"]: v for v in manifest_videos}
    for v in videos:
        if v["file"] in by_file:
            meta = by_file[v["file"]]
            v.update(
                {
                    "title": meta["title"],
                    "subtitle": meta.get("subtitle", ""),
                    "format": meta["format"],
                    "channel": meta.get("channel", ""),
                    "kind": "realistic",
                    "poster": meta["poster"],
                }
            )

    payload = {
        "brand": "Gönül Köprüsü",
        "public_base": "https://gonulkoprusu.com/images/ads",
        "research_notes": [
            "Meta Reels/Stories: 9:16 zorunlu; hook ilk 2 sn",
            "Dating: kimlik odaklı mesaj (ciddi ilişki) > jenerik 'yakınında tanış'",
            "UGC / gerçek görüntü, altyazı, 15–30 sn ideal",
        ],
        "videos": videos,
        "photos": photos,
        "realistic": manifest_videos,
    }
    (PUBLIC / "manifest.json").write_text(json.dumps(payload, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    shutil.copy2(PUBLIC / "manifest.json", MIRROR / "manifest.json")
    shutil.copy2(PUBLIC / "manifest.json", ART / "manifest.json")

    # Simple index
    rows_v = "".join(f"<li><a href=\"{v['file']}\">{v['title']}</a> ({v.get('format','')})</li>" for v in videos)
    rows_p = "".join(f"<li><a href=\"{p['file']}\">{p['title']}</a></li>" for p in photos[:40])
    (PUBLIC / "index.html").write_text(
        f"""<!doctype html><html lang="tr"><head><meta charset="utf-8">
<meta name="robots" content="noindex,nofollow">
<title>Gönül Köprüsü — Reklam Medya</title>
<style>body{{font-family:Georgia,serif;background:#1a0f18;color:#fff8f2;padding:2rem}}a{{color:#ffb4c8}}</style>
</head><body><h1>Reklam Medya</h1><h2>Videolar</h2><ul>{rows_v}</ul><h2>Fotoğraflar</h2><ul>{rows_p}</ul></body></html>
""",
        encoding="utf-8",
    )
    shutil.copy2(PUBLIC / "index.html", MIRROR / "index.html")

    # Instagram Reels açıklama + hashtag paketi (kopyala-yapıştır)
    reel_url = "https://gonulkoprusu.com/register?utm_source=instagram&utm_medium=reels&utm_campaign=v2"
    cap_lines = [
        "=== Gönül Köprüsü — Instagram Reels v2 ===",
        "Yayın: her Reel’e aşağıdaki açıklamayı yapıştır + linki bio’ya koy",
        f"Varsayılan link: {reel_url}",
        "",
    ]
    for ad in ADS:
        ig = (ad.get("ig_caption") or "").strip()
        if not ig:
            continue
        cap_lines.append(f"--- {ad['id']} · {ad['title']} ---")
        cap_lines.append(f"Dosya: {ad['id']}.mp4 (9:16)")
        cap_lines.append(ig.format(url=reel_url))
        cap_lines.append("")
    cap_path = PUBLIC / "instagram-reels-captions.txt"
    cap_path.write_text("\n".join(cap_lines).rstrip() + "\n", encoding="utf-8")
    shutil.copy2(cap_path, MIRROR / cap_path.name)
    shutil.copy2(cap_path, ART / cap_path.name)
    (ROOT / "marketing" / "instagram" / "reels-v2-captions.txt").write_text(
        cap_path.read_text(encoding="utf-8"), encoding="utf-8"
    )

    print("OK", PUBLIC, "videos", len(videos), "photos", len(photos))


if __name__ == "__main__":
    asyncio.run(main())
