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

ADS = [
    {
        "id": "rx-01-hook-ciddi",
        "title": "Ciddi ilişki mi arıyorsun?",
        "subtitle": "Hook · kimlik odaklı",
        "format_primary": "9:16",
        "channel": "Instagram Reels · Stories · Meta Ads",
        "clips": [("a", 0.4, 2.8), ("c", 0.5, 3.5), ("e", 1.0, 3.2)],
        "captions": [
            (0.0, 2.4, "Ciddi ilişki mi arıyorsun?"),
            (2.4, 5.8, "Uygulama kalabalığında kaybolma."),
            (5.8, 9.5, "Gönül Köprüsü — güvenli & ciddi"),
        ],
        "voice": (
            "Ciddi ilişki mi arıyorsun? Uygulama kalabalığında kaybolma. "
            "Gönül Köprüsü. Güvenli tanışma, evlilik odaklı. Ücretsiz üye ol."
        ),
        "cta": "Ücretsiz üye ol → gonulkoprusu.com",
    },
    {
        "id": "rx-02-dogru-insan",
        "title": "Doğru insan, doğru yer",
        "subtitle": "Duygusal · reel tarzı",
        "format_primary": "9:16",
        "channel": "Instagram Reels · TikTok",
        "clips": [("f", 0.3, 3.0), ("d", 0.2, 3.2), ("b", 0.8, 3.0)],
        "captions": [
            (0.0, 2.2, "Doğru insan, doğru yer"),
            (2.2, 5.5, "Kalpten kalbe uzanan köprü"),
            (5.5, 9.2, "Hemen kayıt ol — ücretsiz"),
        ],
        "voice": (
            "Doğru insan, doğru yer. Kalpten kalbe uzanan en güzel köprü. "
            "Gönül Köprüsü. Ücretsiz üye ol."
        ),
        "cta": "Şimdi kayıt ol → gonulkoprusu.com",
    },
    {
        "id": "rx-03-guvenli",
        "title": "Güvenli · Ciddi · Gerçek",
        "subtitle": "Güven mesajı",
        "format_primary": "9:16",
        "channel": "Meta Ads · Stories",
        "clips": [("g", 0.2, 3.0), ("h", 0.2, 2.8), ("a", 2.0, 3.0)],
        "captions": [
            (0.0, 2.3, "Güvenli profiller"),
            (2.3, 5.2, "Ciddi üyelik · gerçek bağlar"),
            (5.2, 9.0, "Gönül Köprüsü’ne katıl"),
        ],
        "voice": (
            "Güvenli profiller, ciddi üyelik, gerçek bağlar. "
            "Gönül Köprüsü ile tanış. Hemen ücretsiz kayıt ol."
        ),
        "cta": "Linke dokun — ücretsiz kayıt",
    },
    {
        "id": "rx-04-evlilik",
        "title": "Evlilik hayaline bir adım",
        "subtitle": "Dönüşüm CTA",
        "format_primary": "9:16",
        "channel": "Reels · Feed",
        "clips": [("e", 0.5, 3.2), ("b", 2.0, 3.0), ("c", 1.0, 3.0)],
        "captions": [
            (0.0, 2.5, "Evlilik hayaline bir adım"),
            (2.5, 5.6, "Gönülden gönüle"),
            (5.6, 9.4, "Bugün sen köprüyü kur"),
        ],
        "voice": (
            "Evlilik hayalinize bir adım daha yaklaşın. "
            "Gönül Köprüsü. Gönülden gönüle. Hemen kayıt ol."
        ),
        "cta": "Ücretsiz başla → gonulkoprusu.com",
    },
    {
        "id": "rx-05-web-display",
        "title": "Web / YouTube Display",
        "subtitle": "16:9 display paketi",
        "format_primary": "16:9",
        "channel": "YouTube · Display · Landing",
        "clips": [("a", 0.5, 3.5), ("f", 0.3, 3.2), ("e", 0.8, 3.5)],
        "captions": [
            (0.0, 2.5, "Ciddi ilişki arayanlar burada"),
            (2.5, 5.8, "Güvenli tanışma · Evlilik odaklı"),
            (5.8, 10.0, "gonulkoprusu.com — ücretsiz üye ol"),
        ],
        "voice": (
            "Gönül Köprüsü. Ciddi ilişki arayanlar burada. "
            "Güvenli tanışma, evlilik odaklı. Ücretsiz üye ol. gonulkoprusu.com"
        ),
        "cta": "Ücretsiz üye ol — gonulkoprusu.com",
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
    # Caption style: large, centered lower-third (safe zone)
    fontsize = max(36, w // 22 if w < h else w // 32)
    y_expr = f"h*{0.62 if w < h else 0.72}"
    filters = []
    for i, (start, end, text) in enumerate(captions):
        t = escape_drawtext(text)
        filters.append(
            f"drawtext=fontfile={FONT_BOLD_SANS}:text='{t}':fontsize={fontsize}:"
            f"fontcolor=white:borderw=3:bordercolor=black@0.55:"
            f"x=(w-text_w)/2:y={y_expr}:"
            f"enable='between(t,{start:.2f},{end:.2f})'"
        )
    # subtle top brand chip
    filters.append(
        f"drawtext=fontfile={FONT_BOLD}:text='Gönül Köprüsü':fontsize={max(22, w // 40)}:"
        f"fontcolor=white@0.92:x=(w-text_w)/2:y={int(h * 0.06 if w < h else h * 0.05)}:"
        f"shadowcolor=black@0.4:shadowx=1:shadowy=1"
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
            kind = "realistic" if name.startswith("rx-") else "classic"
            videos.append(
                {
                    "file": rel,
                    "title": name.replace(".mp4", "").replace("-", " "),
                    "poster": poster,
                    "kind": kind,
                    "format": "9:16" if "story-" in name or (name.startswith("rx-") and "-wide" not in name and "web-" not in name) else "16:9",
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
    print("OK", PUBLIC, "videos", len(videos), "photos", len(photos))


if __name__ == "__main__":
    asyncio.run(main())
