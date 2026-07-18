#!/usr/bin/env python3
"""Gönül Köprüsü Instagram Story paketi — gerçek görseller + Türkçe seslendirme."""

from __future__ import annotations

import asyncio
import subprocess
from pathlib import Path

from PIL import Image, ImageDraw, ImageEnhance, ImageFont, ImageOps

ROOT = Path(__file__).resolve().parents[2]
SRC = ROOT / "marketing" / "instagram"
OUT = ROOT / "marketing" / "instagram" / "stories"
ART = Path("/opt/cursor/artifacts/instagram-stories")
LOGO = ROOT / "web-site" / "public" / "images" / "logo-320.png"

W, H = 1080, 1920
VOICE = "tr-TR-EmelNeural"
FONT_BOLD = "/usr/share/fonts/truetype/dejavu/DejaVuSerif-Bold.ttf"
FONT_REG = "/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf"

STORIES = [
    {
        "id": "01-ciddi-iliski",
        "image": SRC / "instagram-gonul-koprusu-01.png",
        "brand": "Gönül Köprüsü",
        "line": "Ciddi ilişki arayanlar burada",
        "cta": "Ücretsiz üye ol → gonulkoprusu.com",
        "voice": (
            "Gönül Köprüsü. Ciddi ilişki arayanlar burada. "
            "Güvenli tanışma, evlilik odaklı. Ücretsiz üye ol. gonulkoprusu.com"
        ),
    },
    {
        "id": "02-dogru-insan",
        "image": SRC / "instagram-gonulk-koprusu-02.png",
        "brand": "Gönül Köprüsü",
        "line": "Doğru insan, doğru yer",
        "cta": "Kalpten kalbe — şimdi kayıt ol",
        "voice": (
            "Doğru insan, doğru yer. Kalpten kalbe uzanan en güzel köprü. "
            "Gönül Köprüsü. Ücretsiz üye ol."
        ),
    },
    {
        "id": "03-guvenli",
        "image": SRC / "instagram-gonulkoprusu-03.png",
        "brand": "Gönül Köprüsü",
        "line": "Güvenli · Ciddi · Gerçek bağlar",
        "cta": "Linke dokun — ücretsiz kayıt",
        "voice": (
            "Gönül Köprüsü ile tanış. Güvenli profiller, ciddi üyelik, gerçek bağlar. "
            "Hemen ücretsiz kayıt ol."
        ),
    },
    {
        "id": "04-evlilik",
        "image": SRC / "instagram-gonulkoprusu-04.png",
        "brand": "Gönül Köprüsü",
        "line": "Evlilik hayaline bir adım",
        "cta": "Hemen kayıt ol → @gonulkoprusucom",
        "voice": (
            "Evlilik hayalinize bir adım daha yaklaşın. "
            "Gönül Köprüsü. Gönülden gönüle. Hemen kayıt ol."
        ),
    },
]


def ensure_dirs() -> None:
    OUT.mkdir(parents=True, exist_ok=True)
    ART.mkdir(parents=True, exist_ok=True)


def fit_cover(img: Image.Image, size: tuple[int, int]) -> Image.Image:
    return ImageOps.fit(img.convert("RGBA"), size, method=Image.Resampling.LANCZOS, centering=(0.5, 0.42))


def load_font(path: str, size: int) -> ImageFont.FreeTypeFont:
    return ImageFont.truetype(path, size=size)


def draw_centered(draw: ImageDraw.ImageDraw, y: int, text: str, font: ImageFont.ImageFont, fill: tuple[int, ...]) -> None:
    bbox = draw.textbbox((0, 0), text, font=font)
    tw = bbox[2] - bbox[0]
    draw.text(((W - tw) / 2, y), text, font=font, fill=fill)


def make_story_frame(story: dict) -> Path:
    base = fit_cover(Image.open(story["image"]), (W, H))
    # Soft bottom vignette for readable CTA
    overlay = Image.new("RGBA", (W, H), (0, 0, 0, 0))
    od = ImageDraw.Draw(overlay)
    for i in range(520):
        alpha = int(190 * (i / 520) ** 1.4)
        y = H - 520 + i
        od.line([(0, y), (W, y)], fill=(20, 8, 14, alpha))
    # Top brand bar
    for i in range(280):
        alpha = int(140 * (1 - i / 280))
        od.line([(0, i), (W, i)], fill=(40, 10, 20, alpha))
    frame = Image.alpha_composite(base, overlay)
    draw = ImageDraw.Draw(frame)

    brand_font = load_font(FONT_BOLD, 72)
    line_font = load_font(FONT_BOLD, 48)
    cta_font = load_font(FONT_REG, 34)
    handle_font = load_font(FONT_REG, 28)

    # Logo chip
    if LOGO.is_file():
        logo = Image.open(LOGO).convert("RGBA")
        logo.thumbnail((220, 220), Image.Resampling.LANCZOS)
        # brighten slightly for dark overlay
        logo = ImageEnhance.Brightness(logo).enhance(1.15)
        lx = (W - logo.width) // 2
        frame.alpha_composite(logo, (lx, 70))
        brand_y = 70 + logo.height + 18
    else:
        brand_y = 120

    draw_centered(draw, brand_y, story["brand"], brand_font, (255, 236, 214, 255))
    draw_centered(draw, brand_y + 88, "CİDDİ İLİŞKİ PLATFORMU", handle_font, (255, 210, 170, 230))

    # Bottom copy
    draw_centered(draw, H - 340, story["line"], line_font, (255, 255, 255, 255))
    # CTA pill
    cta = story["cta"]
    cb = draw.textbbox((0, 0), cta, font=cta_font)
    cw = cb[2] - cb[0] + 72
    ch = 70
    cx0 = (W - cw) // 2
    cy0 = H - 230
    draw.rounded_rectangle((cx0, cy0, cx0 + cw, cy0 + ch), radius=36, fill=(190, 38, 78, 235))
    draw_centered(draw, cy0 + 16, cta, cta_font, (255, 255, 255, 255))
    draw_centered(draw, H - 120, "@gonulkoprusucom", handle_font, (255, 230, 210, 220))

    out = OUT / f"story-{story['id']}.png"
    frame.convert("RGB").save(out, "PNG", optimize=True)
    frame.convert("RGB").save(ART / out.name, "PNG", optimize=True)
    return out


async def synth_voice(text: str, mp3: Path) -> None:
    import edge_tts

    communicate = edge_tts.Communicate(text, VOICE, rate="-5%")
    await communicate.save(str(mp3))


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


def render_video(png: Path, mp3: Path, mp4: Path, duration: float) -> None:
    # Gentle zoom (Ken Burns) over still
    vf = (
        f"scale=1200:2133,zoompan=z='min(1.08,1+0.0008*on)':x='iw/2-(iw/zoom/2)':"
        f"y='ih/2-(ih/zoom/2)':d={int(duration * 30)}:s={W}x{H}:fps=30,"
        f"format=yuv420p"
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
        "-c:a",
        "aac",
        "-b:a",
        "192k",
        "-shortest",
        "-t",
        f"{duration + 0.35:.2f}",
        "-movflags",
        "+faststart",
        str(mp4),
    ]
    subprocess.run(cmd, check=True, capture_output=True)


def concat_videos(parts: list[Path], out: Path) -> None:
    lst = OUT / "concat.txt"
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


async def main() -> None:
    ensure_dirs()
    videos: list[Path] = []
    readme_lines = [
        "Gönül Köprüsü — Instagram Story Paketi",
        "=====================================",
        "",
        "Boyut: 1080×1920 (9:16)",
        "Ses: Türkçe (edge-tts Emel)",
        "Görseller: marketing/instagram gerçek marka görselleri + logo",
        "",
        "Story link sticker:",
        "  https://gonulkoprusu.com/register?utm_source=instagram&utm_medium=story&utm_campaign=weekly",
        "",
        "Dosyalar:",
    ]

    for story in STORIES:
        print("frame", story["id"])
        png = make_story_frame(story)
        mp3 = OUT / f"vo-{story['id']}.mp3"
        print("voice", story["id"])
        await synth_voice(story["voice"], mp3)
        dur = audio_duration(mp3)
        mp4 = OUT / f"story-{story['id']}.mp4"
        print("video", story["id"], f"{dur:.1f}s")
        render_video(png, mp3, mp4, dur)
        art_mp4 = ART / mp4.name
        art_mp4.write_bytes(mp4.read_bytes())
        (ART / mp3.name).write_bytes(mp3.read_bytes())
        videos.append(mp4)
        readme_lines.append(f"- {png.name} / {mp4.name} — “{story['line']}”")
        readme_lines.append(f"  Ses metni: {story['voice']}")
        readme_lines.append("")

    combined = OUT / "story-reel-full.mp4"
    print("concat")
    concat_videos(videos, combined)
    (ART / combined.name).write_bytes(combined.read_bytes())
    readme_lines += [
        f"- {combined.name} — 4 story birleşik reel",
        "",
        "Yayın notu:",
        "1) Instagram uygulamasında Story ekle → bu MP4’lerden birini yükle",
        "2) Link sticker: yukarıdaki UTM’li kayıt linki",
        "3) Marka adı karede hero; seslendirme Türkçe",
    ]
    (OUT / "README.txt").write_text("\n".join(readme_lines) + "\n", encoding="utf-8")
    (ART / "README.txt").write_text("\n".join(readme_lines) + "\n", encoding="utf-8")
    print("OK", OUT)


if __name__ == "__main__":
    asyncio.run(main())
