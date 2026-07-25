#!/usr/bin/env python3
"""Gönül Köprüsü web sitesi reklam videoları — 16:9 + 9:16 paket, public/ads + admin manifest."""

from __future__ import annotations

import json
import shutil
import subprocess
from pathlib import Path

from PIL import Image, ImageDraw, ImageEnhance, ImageFont, ImageOps

ROOT = Path(__file__).resolve().parents[2]
SRC = ROOT / "marketing" / "instagram"
STORIES_DIR = SRC / "stories"
OUT = ROOT / "marketing" / "website-ads"
# Deploy manifest maps web-site/public/images → images (document root)
PUBLIC = ROOT / "web-site" / "public" / "images" / "ads"
# Keep a mirror under /marketing/ads for local tooling / robots-disallowed path
PUBLIC_MIRROR = ROOT / "web-site" / "public" / "marketing" / "ads"
ART = Path("/opt/cursor/artifacts/website-ads")
LOGO = ROOT / "web-site" / "public" / "images" / "logo-320.png"

FONT_BOLD = "/usr/share/fonts/truetype/dejavu/DejaVuSerif-Bold.ttf"
FONT_REG = "/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf"

ADS = [
    {
        "id": "01-ciddi-iliski",
        "image": SRC / "instagram-gonul-koprusu-01.png",
        "title": "Ciddi ilişki arayanlar burada",
        "subtitle": "Güvenli tanışma · Evlilik odaklı",
        "cta": "Ücretsiz üye ol — gonulkoprusu.com",
        "format": "web_landscape",
        "channel": "YouTube / Display / Web",
        "voice_mp3": STORIES_DIR / "vo-01-ciddi-iliski.mp3",
    },
    {
        "id": "02-dogru-insan",
        "image": SRC / "instagram-gonulk-koprusu-02.png",
        "title": "Doğru insan, doğru yer",
        "subtitle": "Kalpten kalbe uzanan köprü",
        "cta": "Hemen kayıt ol — gonulkoprusu.com",
        "format": "web_landscape",
        "channel": "YouTube / Display / Web",
        "voice_mp3": STORIES_DIR / "vo-02-dogru-insan.mp3",
    },
    {
        "id": "03-guvenli",
        "image": SRC / "instagram-gonulkoprusu-03.png",
        "title": "Güvenli · Ciddi · Gerçek bağlar",
        "subtitle": "Moderasyonlu, ciddi üyelik",
        "cta": "Ücretsiz kayıt — gonulkoprusu.com",
        "format": "web_landscape",
        "channel": "YouTube / Display / Web",
        "voice_mp3": STORIES_DIR / "vo-03-guvenli.mp3",
    },
    {
        "id": "04-evlilik",
        "image": SRC / "instagram-gonulkoprusu-04.png",
        "title": "Evlilik hayaline bir adım",
        "subtitle": "Gönülden gönüle",
        "cta": "Şimdi başla — gonulkoprusu.com",
        "format": "web_landscape",
        "channel": "YouTube / Display / Web",
        "voice_mp3": STORIES_DIR / "vo-04-evlilik.mp3",
    },
]

VERTICAL = [
    {
        "id": "story-01-ciddi-iliski",
        "title": "Ciddi ilişki arayanlar burada",
        "subtitle": "Instagram Story / Reels (9:16)",
        "format": "story_vertical",
        "channel": "Instagram Story · Reels · TikTok",
        "mp4": STORIES_DIR / "story-01-ciddi-iliski.mp4",
        "poster": STORIES_DIR / "story-01-ciddi-iliski.png",
    },
    {
        "id": "story-02-dogru-insan",
        "title": "Doğru insan, doğru yer",
        "subtitle": "Instagram Story / Reels (9:16)",
        "format": "story_vertical",
        "channel": "Instagram Story · Reels · TikTok",
        "mp4": STORIES_DIR / "story-02-dogru-insan.mp4",
        "poster": STORIES_DIR / "story-02-dogru-insan.png",
    },
    {
        "id": "story-03-guvenli",
        "title": "Güvenli · Ciddi · Gerçek bağlar",
        "subtitle": "Instagram Story / Reels (9:16)",
        "format": "story_vertical",
        "channel": "Instagram Story · Reels · TikTok",
        "mp4": STORIES_DIR / "story-03-guvenli.mp4",
        "poster": STORIES_DIR / "story-03-guvenli.png",
    },
    {
        "id": "story-04-evlilik",
        "title": "Evlilik hayaline bir adım",
        "subtitle": "Instagram Story / Reels (9:16)",
        "format": "story_vertical",
        "channel": "Instagram Story · Reels · TikTok",
        "mp4": STORIES_DIR / "story-04-evlilik.mp4",
        "poster": STORIES_DIR / "story-04-evlilik.png",
    },
    {
        "id": "story-reel-full",
        "title": "4 hikâye birleşik reel",
        "subtitle": "Instagram Reels uzun versiyon",
        "format": "story_vertical",
        "channel": "Instagram Reels · TikTok",
        "mp4": STORIES_DIR / "story-reel-full.mp4",
        "poster": STORIES_DIR / "story-01-ciddi-iliski.png",
    },
]


def ensure_dirs() -> None:
    OUT.mkdir(parents=True, exist_ok=True)
    PUBLIC.mkdir(parents=True, exist_ok=True)
    PUBLIC_MIRROR.mkdir(parents=True, exist_ok=True)
    ART.mkdir(parents=True, exist_ok=True)


def load_font(path: str, size: int) -> ImageFont.FreeTypeFont:
    return ImageFont.truetype(path, size=size)


def fit_cover(img: Image.Image, size: tuple[int, int]) -> Image.Image:
    return ImageOps.fit(img.convert("RGBA"), size, method=Image.Resampling.LANCZOS, centering=(0.5, 0.4))


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


def make_landscape_frame(ad: dict) -> Path:
    w, h = 1920, 1080
    base = fit_cover(Image.open(ad["image"]), (w, h))
    overlay = Image.new("RGBA", (w, h), (0, 0, 0, 0))
    od = ImageDraw.Draw(overlay)
    for i in range(w):
        t = i / w
        alpha = int(55 + 110 * abs(t - 0.5) * 2)
        od.line([(i, 0), (i, h)], fill=(18, 8, 28, min(160, alpha)))
    for i in range(420):
        alpha = int(210 * (i / 420) ** 1.25)
        y = h - 420 + i
        od.line([(0, y), (w, y)], fill=(12, 6, 18, alpha))
    for i in range(220):
        alpha = int(130 * (1 - i / 220))
        od.line([(0, i), (w, i)], fill=(30, 10, 40, alpha))

    frame = Image.alpha_composite(base, overlay)
    draw = ImageDraw.Draw(frame)

    brand_font = load_font(FONT_BOLD, 64)
    title_font = load_font(FONT_BOLD, 58)
    sub_font = load_font(FONT_REG, 34)
    cta_font = load_font(FONT_REG, 32)
    tag_font = load_font(FONT_REG, 26)

    if LOGO.is_file():
        logo = Image.open(LOGO).convert("RGBA")
        logo.thumbnail((160, 160), Image.Resampling.LANCZOS)
        logo = ImageEnhance.Brightness(logo).enhance(1.12)
        frame.alpha_composite(logo, (72, 56))
        brand_x = 72 + logo.width + 28
    else:
        brand_x = 72

    draw.text((brand_x, 78), "Gönül Köprüsü", font=brand_font, fill=(255, 236, 214, 255))
    draw.text((brand_x, 155), "CİDDİ İLİŞKİ PLATFORMU", font=tag_font, fill=(255, 210, 170, 230))

    draw.text((72, h - 310), ad["title"], font=title_font, fill=(255, 255, 255, 255))
    draw.text((72, h - 230), ad["subtitle"], font=sub_font, fill=(255, 230, 210, 235))

    cta = ad["cta"]
    cb = draw.textbbox((0, 0), cta, font=cta_font)
    cw = cb[2] - cb[0] + 56
    ch = 64
    cx0, cy0 = 72, h - 150
    draw.rounded_rectangle((cx0, cy0, cx0 + cw, cy0 + ch), radius=32, fill=(190, 38, 78, 235))
    draw.text((cx0 + 28, cy0 + 14), cta, font=cta_font, fill=(255, 255, 255, 255))

    out = OUT / f"web-{ad['id']}.png"
    frame.convert("RGB").save(out, "PNG", optimize=True)
    return out


def render_landscape_video(png: Path, mp3: Path, mp4: Path, duration: float) -> None:
    # Ken Burns on 16:9 still
    frames = max(90, int(duration * 30))
    vf = (
        f"scale=2100:1181,zoompan=z='min(1.06,1+0.00055*on)':x='iw/2-(iw/zoom/2)':"
        f"y='ih/2-(ih/zoom/2)':d={frames}:s=1920x1080:fps=30,format=yuv420p"
    )
    cmd = [
        "ffmpeg",
        "-y",
        "-loop",
        "1",
        "-i",
        str(png),
        "-i",
        str(mp3),
        "-vf",
        vf,
        "-c:v",
        "libx264",
        "-tune",
        "stillimage",
        "-pix_fmt",
        "yuv420p",
        "-c:a",
        "aac",
        "-b:a",
        "160k",
        "-shortest",
        "-t",
        f"{duration + 0.3:.2f}",
        "-movflags",
        "+faststart",
        str(mp4),
    ]
    subprocess.run(cmd, check=True, capture_output=True)


def concat_videos(parts: list[Path], out: Path) -> None:
    lst = OUT / "concat-web.txt"
    lst.write_text("".join(f"file '{p.resolve()}'\n" for p in parts), encoding="utf-8")
    subprocess.run(
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
        ],
        check=True,
        capture_output=True,
    )


def publish(src: Path, name: str | None = None) -> Path:
    dest = PUBLIC / (name or src.name)
    shutil.copy2(src, dest)
    mirror = PUBLIC_MIRROR / dest.name
    shutil.copy2(src, mirror)
    art = ART / dest.name
    shutil.copy2(src, art)
    return dest


def main() -> None:
    ensure_dirs()
    manifest: list[dict] = []
    landscape_videos: list[Path] = []

    print("== Landscape web ads ==")
    for ad in ADS:
        print("frame", ad["id"])
        png = make_landscape_frame(ad)
        mp3 = ad["voice_mp3"]
        if not mp3.is_file():
            raise FileNotFoundError(mp3)
        dur = audio_duration(mp3)
        mp4 = OUT / f"web-{ad['id']}.mp4"
        print("video", ad["id"], f"{dur:.1f}s")
        render_landscape_video(png, mp3, mp4, dur)
        publish(png)
        publish(mp4)
        landscape_videos.append(mp4)
        manifest.append(
            {
                "id": f"web-{ad['id']}",
                "title": ad["title"],
                "subtitle": ad["subtitle"],
                "format": "16:9",
                "channel": ad["channel"],
                "duration_hint": f"{dur:.0f}s",
                "video": f"web-{ad['id']}.mp4",
                "poster": f"web-{ad['id']}.png",
                "cta_url": "https://gonulkoprusu.com/kampanya?utm_source=ads&utm_medium=video&utm_campaign=web",
            }
        )

    combined = OUT / "web-reel-full.mp4"
    print("concat landscape")
    concat_videos(landscape_videos, combined)
    publish(combined)
    publish(OUT / "web-01-ciddi-iliski.png", "web-reel-full.png")
    manifest.append(
        {
            "id": "web-reel-full",
            "title": "Web reklam birleşik reel",
            "subtitle": "4 reklam · 16:9",
            "format": "16:9",
            "channel": "YouTube · Landing · Site içi",
            "duration_hint": "birleşik",
            "video": "web-reel-full.mp4",
            "poster": "web-reel-full.png",
            "cta_url": "https://gonulkoprusu.com/kampanya?utm_source=ads&utm_medium=video&utm_campaign=web",
        }
    )

    print("== Publish vertical stories ==")
    for item in VERTICAL:
        if not item["mp4"].is_file():
            print("skip missing", item["mp4"])
            continue
        publish(item["mp4"], f"{item['id']}.mp4")
        if item["poster"].is_file():
            publish(item["poster"], f"{item['id']}.png")
        manifest.append(
            {
                "id": item["id"],
                "title": item["title"],
                "subtitle": item["subtitle"],
                "format": "9:16",
                "channel": item["channel"],
                "duration_hint": "story",
                "video": f"{item['id']}.mp4",
                "poster": f"{item['id']}.png",
                "cta_url": "https://gonulkoprusu.com/register?utm_source=instagram&utm_medium=story&utm_campaign=weekly",
            }
        )

    manifest_path = PUBLIC / "manifest.json"
    payload = {
        "brand": "Gönül Köprüsü",
        "updated": "auto",
        "notes": "Admin Pazarlama → Reklam Videoları. İndirip Meta/Google/Instagram’a yükleyin.",
        "videos": manifest,
    }
    manifest_path.write_text(json.dumps(payload, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    (ART / "manifest.json").write_text(manifest_path.read_text(encoding="utf-8"), encoding="utf-8")

    readme = OUT / "README.txt"
    lines = [
        "Gönül Köprüsü — Web Sitesi Reklam Videoları",
        "==========================================",
        "",
        "16:9 (1920×1080): YouTube Ads, Display, site içi, landing",
        "9:16 (1080×1920): Instagram Story / Reels / TikTok",
        "",
        "Canlı klasör: web-site/public/images/ads/ → https://gonulkoprusu.com/images/ads/",
        "Ayna: web-site/public/marketing/ads/",
        "Admin: Pazarlama menüsü → Reklam Videoları",
        "",
    ]
    for v in manifest:
        lines.append(f"- {v['video']} — {v['title']} ({v['format']})")
    readme.write_text("\n".join(lines) + "\n", encoding="utf-8")
    publish(readme, "README.txt")
    print("OK", PUBLIC)


if __name__ == "__main__":
    main()
