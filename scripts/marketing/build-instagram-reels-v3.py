#!/usr/bin/env python3
"""Gönül Köprüsü — Instagram Reels v3

Hedefler:
- Her Reel farklı görsel dil (benzersiz medya + renk grade)
- Logo rozeti Instagram güvenli alanında (üst chrome altında)
- Yazılar ortada güvenli bantta (alt UI / üst kullanıcı adı dışında)
- Kısa hook + TR ses
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
BRAND = ROOT / "marketing" / "instagram"
IMG = ROOT / "web-site" / "public" / "images"
OUT = ROOT / "marketing" / "reels-v3"
PUBLIC = ROOT / "web-site" / "public" / "images" / "ads"
MIRROR = ROOT / "web-site" / "public" / "marketing" / "ads"
ART = Path("/opt/cursor/artifacts/instagram-reels-v3")
LOGO = IMG / "logo-320.png"

FONT_BOLD = "/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf"
FONT_SERIF = "/usr/share/fonts/truetype/dejavu/DejaVuSerif-Bold.ttf"
VOICE = "tr-TR-EmelNeural"
W, H = 1080, 1920
FPS = 30

# Instagram Reels güvenli alan (yaklaşık)
SAFE_TOP = int(H * 0.20)      # logo — üst chrome'un altında
SAFE_TEXT_Y = int(H * 0.44)   # metin merkezi (alt UI dışında)
SAFE_BOTTOM = int(H * 0.70)   # CTA üst sınırı

CLIPS = {
    "sunset_hands": STOCK / "mixkit-939.mp4",
    "wedding": STOCK / "mixkit-40601.mp4",
    "pier": STOCK / "mixkit-4664.mp4",
    "city_walk": STOCK / "mixkit-4661.mp4",
    "campfire": STOCK / "mixkit-513.mp4",
    "car_close": STOCK / "mixkit-42511.mp4",
}

STILLS = {
    "couple1": IMG / "story-couple-01.jpg",
    "couple2": IMG / "story-couple-02.jpg",
    "couple3": IMG / "story-couple-03.jpg",
    "couple4": IMG / "story-couple-04.jpg",
    "couple5": IMG / "story-couple-05.jpg",
    "couple6": IMG / "story-couple-06.jpg",
    "brand1": BRAND / "instagram-gonul-koprusu-01.png",
    "brand2": BRAND / "instagram-gonulk-koprusu-02.png",
    "brand3": BRAND / "instagram-gonulkoprusu-03.png",
    "brand4": BRAND / "instagram-gonulkoprusu-04.png",
}

# Her Reel: benzersiz medya + grade + kısa satırlar (ekran taşmasın)
REELS = [
    {
        "id": "ig-01-dur",
        "title": "Flört yorduysa dur",
        "grade": "eq=contrast=1.08:saturation=1.15:brightness=0.02,colorbalance=rs=0.08:gs=-0.02:bs=-0.06",
        "scenes": [
            ("still", "couple5", 2.2),
            ("clip", "sunset_hands", 0.3, 2.4),
            ("still", "brand1", 2.0),
            ("clip", "city_walk", 0.4, 2.2),
        ],
        "lines": [
            (0.0, 1.8, "Flört yorduysa"),
            (1.8, 3.6, "Ciddi ilişki ara"),
            (3.6, 5.6, "Kart yok · Ücretsiz"),
            (5.6, 8.0, "Gönül Köprüsü"),
        ],
        "voice": (
            "Flört uygulamaları yorduysa dur. Ciddi ilişki arıyorsan Gönül Köprüsü. "
            "Kart bilgisi yok. Ücretsiz üye ol."
        ),
        "cta": "Ücretsiz kayıt ol",
        "accent": (225, 70, 95),
        "ig_caption": (
            "Flört yorduysa… ciddi ilişki zamanı.\n"
            "Gönül Köprüsü — güvenli tanışma, evlilik niyeti.\n"
            "Kart yok · Ücretsiz 👇\n{url}\n\n"
            "#ciddiilişki #evlilik #güvenlitanışma #gonülköprüsü #reels"
        ),
    },
    {
        "id": "ig-02-soru",
        "title": "Ciddi misin?",
        "grade": "eq=contrast=1.1:saturation=1.05:gamma=1.05,colorbalance=rs=0.04:gs=0.02:bs=-0.04",
        "scenes": [
            ("still", "couple2", 2.0),
            ("clip", "wedding", 0.2, 2.8),
            ("still", "brand2", 2.0),
            ("clip", "wedding", 3.5, 2.2),
        ],
        "lines": [
            (0.0, 1.6, "Ciddi misin?"),
            (1.6, 3.4, "Gerçekten mi?"),
            (3.4, 5.6, "O zaman buradasın"),
            (5.6, 8.0, "Ücretsiz üye ol"),
        ],
        "voice": (
            "Ciddi misin? Gerçekten mi? O zaman Gönül Köprüsü’ndesin. "
            "Güvenli tanışma, evlilik odaklı. Ücretsiz üye ol."
        ),
        "cta": "Bio’daki link",
        "accent": (212, 145, 55),
        "ig_caption": (
            "Ciddi misin? Gerçekten mi?\n"
            "Doğru yerdesin — Gönül Köprüsü.\n"
            "Ücretsiz başla 👇\n{url}\n\n"
            "#ciddiilişki #evlilik #gonülköprüsü #tanışma #reels"
        ),
    },
    {
        "id": "ig-03-guven",
        "title": "Önce güven",
        "grade": "eq=contrast=1.06:saturation=1.12,colorbalance=rs=-0.04:gs=0.04:bs=0.02",
        "scenes": [
            ("still", "couple3", 2.1),
            ("clip", "pier", 0.3, 2.5),
            ("still", "brand3", 2.0),
            ("clip", "car_close", 0.2, 2.2),
        ],
        "lines": [
            (0.0, 1.6, "Önce güven"),
            (1.6, 3.4, "Sonra sohbet"),
            (3.4, 5.6, "Profilini tamamla"),
            (5.6, 8.0, "Gönül Köprüsü"),
        ],
        "voice": (
            "Önce güven. Sonra sohbet. Gönül Köprüsü’nde profilini tamamla, "
            "şehrini seç, saygılı bir bağ kur. Ücretsiz kayıt ol."
        ),
        "cta": "Ücretsiz kayıt",
        "accent": (20, 140, 130),
        "ig_caption": (
            "Önce güven, sonra sohbet.\n"
            "Güvenli profiller · ciddi niyet.\n"
            "Ücretsiz üye ol 👇\n{url}\n\n"
            "#güvenlitanışma #ciddiilişki #gonülköprüsü #reels"
        ),
    },
    {
        "id": "ig-04-sehir",
        "title": "Şehrinde tanış",
        "grade": "eq=contrast=1.12:saturation=1.18:brightness=0.03,colorbalance=rs=-0.02:bs=0.08",
        "scenes": [
            ("still", "couple4", 2.0),
            ("clip", "city_walk", 1.5, 2.4),
            ("still", "couple6", 2.0),
            ("clip", "pier", 2.0, 2.2),
        ],
        "lines": [
            (0.0, 1.6, "Şehrinde misin?"),
            (1.6, 3.6, "Ciddi biriyle tanış"),
            (3.6, 5.8, "İstanbul · Ankara"),
            (5.8, 8.0, "Ücretsiz başla"),
        ],
        "voice": (
            "Şehrinde misin? Ciddi biriyle tanışmak istiyorsan Gönül Köprüsü. "
            "İstanbul, Ankara, İzmir ve daha fazlası. Ücretsiz başla."
        ),
        "cta": "gonulkoprusu.com",
        "accent": (55, 105, 200),
        "ig_caption": (
            "Şehrinde ciddi ilişki arayanlar burada.\n"
            "İstanbul · Ankara · İzmir…\n"
            "Ücretsiz kayıt 👇\n{url}\n\n"
            "#İstanbultanışma #ciddiilişki #gonülköprüsü #reels"
        ),
    },
    {
        "id": "ig-05-evlilik",
        "title": "Evlilik niyetiyle",
        "grade": "eq=contrast=1.05:saturation=1.1:gamma=1.08,colorbalance=rs=0.06:gs=0.01:bs=-0.05",
        "scenes": [
            ("still", "couple1", 2.2),
            ("clip", "campfire", 0.5, 2.6),
            ("still", "brand4", 2.0),
            ("clip", "wedding", 1.0, 2.2),
        ],
        "lines": [
            (0.0, 1.7, "Evlilik niyetiyle?"),
            (1.7, 3.6, "Doğru yerdesin"),
            (3.6, 5.8, "Bugün ilk adımı at"),
            (5.8, 8.0, "Ücretsiz üye ol"),
        ],
        "voice": (
            "Evlilik niyetiyle mi arıyorsun? Doğru yerdesin. "
            "Gönül Köprüsü. Bugün ücretsiz üye ol, ilk adımı at."
        ),
        "cta": "Linke dokun",
        "accent": (180, 60, 110),
        "ig_caption": (
            "Evlilik niyetiyle arıyorsan doğru yerdesin.\n"
            "Gönül Köprüsü — flört değil, ciddi bağ.\n"
            "Ücretsiz üye ol 👇\n{url}\n\n"
            "#evlilik #ciddiilişki #gonülköprüsü #reels"
        ),
    },
]


def ensure_dirs() -> None:
    for p in (OUT, PUBLIC, MIRROR, ART, OUT / "parts", OUT / "vo", OUT / "overlays"):
        p.mkdir(parents=True, exist_ok=True)


def run(cmd: list[str]) -> None:
    r = subprocess.run(cmd, capture_output=True, text=True)
    if r.returncode != 0:
        raise RuntimeError(f"cmd failed: {' '.join(cmd[:8])}…\n{r.stderr[-1500:]}")


def audio_duration(path: Path) -> float:
    r = subprocess.run(
        [
            "ffprobe", "-v", "error", "-show_entries", "format=duration",
            "-of", "default=noprint_wrappers=1:nokey=1", str(path),
        ],
        capture_output=True, text=True, check=True,
    )
    return max(6.0, float(r.stdout.strip()))


async def synth_voice(text: str, mp3: Path) -> None:
    import edge_tts
    if mp3.exists():
        mp3.unlink()
    await edge_tts.Communicate(text, VOICE, rate="-3%").save(str(mp3))


def make_logo_badge(out: Path, accent: tuple[int, int, int]) -> Path:
    """Büyük, okunaklı logo rozeti — üst güvenli alanda."""
    canvas = Image.new("RGBA", (W, H), (0, 0, 0, 0))
    draw = ImageDraw.Draw(canvas)
    badge_w, badge_h = 560, 168
    bx = (W - badge_w) // 2
    by = SAFE_TOP
    # gölge
    draw.rounded_rectangle(
        (bx + 5, by + 8, bx + badge_w + 5, by + badge_h + 8),
        radius=32,
        fill=(0, 0, 0, 80),
    )
    draw.rounded_rectangle(
        (bx, by, bx + badge_w, by + badge_h),
        radius=32,
        fill=(255, 255, 255, 245),
        outline=(*accent, 240),
        width=4,
    )
    if LOGO.is_file():
        logo = Image.open(LOGO).convert("RGBA")
        logo.thumbnail((128, 128), Image.Resampling.LANCZOS)
        lx = bx + 22
        ly = by + (badge_h - logo.height) // 2
        canvas.paste(logo, (lx, ly), logo)
        font = ImageFont.truetype(FONT_SERIF, 40)
        draw.text((bx + 170, by + 38), "Gönül Köprüsü", font=font, fill=(28, 16, 40, 255))
        small = ImageFont.truetype(FONT_BOLD, 22)
        draw.text((bx + 172, by + 96), "ciddi ilişki · güvenli tanışma", font=small, fill=(*accent, 255))
    canvas.save(out, "PNG")
    return out


def make_text_card(text: str, out: Path, accent: tuple[int, int, int], big: bool = False) -> Path:
    """Orta güvenli bantta pill metin kartı."""
    canvas = Image.new("RGBA", (W, H), (0, 0, 0, 0))
    draw = ImageDraw.Draw(canvas)
    fs = 64 if big else 52
    font = ImageFont.truetype(FONT_BOLD, fs)
    # wrap max ~16 chars-ish by measuring
    words = text.split()
    lines: list[str] = []
    cur = ""
    for w in words:
        trial = (cur + " " + w).strip()
        bb = draw.textbbox((0, 0), trial, font=font)
        if bb[2] - bb[0] > W * 0.78 and cur:
            lines.append(cur)
            cur = w
        else:
            cur = trial
    if cur:
        lines.append(cur)
    lines = lines[:2]

    pads_x, pads_y = 36, 22
    line_gap = 10
    widths = []
    heights = []
    for line in lines:
        bb = draw.textbbox((0, 0), line, font=font)
        widths.append(bb[2] - bb[0])
        heights.append(bb[3] - bb[1])
    box_w = max(widths) + pads_x * 2
    box_h = sum(heights) + line_gap * (len(lines) - 1) + pads_y * 2
    x0 = (W - box_w) // 2
    y0 = SAFE_TEXT_Y
    # soft shadow
    draw.rounded_rectangle(
        (x0 + 4, y0 + 6, x0 + box_w + 4, y0 + box_h + 6),
        radius=22,
        fill=(0, 0, 0, 70),
    )
    draw.rounded_rectangle(
        (x0, y0, x0 + box_w, y0 + box_h),
        radius=22,
        fill=(18, 12, 28, 200),
        outline=(*accent, 220),
        width=3,
    )
    y = y0 + pads_y
    for i, line in enumerate(lines):
        bb = draw.textbbox((0, 0), line, font=font)
        tw = bb[2] - bb[0]
        draw.text(((W - tw) / 2, y), line, font=font, fill=(255, 255, 255, 255))
        y += heights[i] + line_gap
    canvas.save(out, "PNG")
    return out


def make_cta_bar(cta: str, out: Path, accent: tuple[int, int, int]) -> Path:
    canvas = Image.new("RGBA", (W, H), (0, 0, 0, 0))
    draw = ImageDraw.Draw(canvas)
    font = ImageFont.truetype(FONT_BOLD, 36)
    bb = draw.textbbox((0, 0), cta, font=font)
    tw, th = bb[2] - bb[0], bb[3] - bb[1]
    pad_x, pad_y = 40, 20
    bw, bh = tw + pad_x * 2, th + pad_y * 2
    x0 = (W - bw) // 2
    y0 = SAFE_BOTTOM
    draw.rounded_rectangle((x0, y0, x0 + bw, y0 + bh), radius=bh // 2, fill=(*accent, 235))
    draw.text((x0 + pad_x, y0 + pad_y - 2), cta, font=font, fill=(255, 255, 255, 255))
    canvas.save(out, "PNG")
    return out


def make_endcard(title: str, cta: str, accent: tuple[int, int, int], out: Path) -> Path:
    img = Image.new("RGB", (W, H), (28, 14, 32))
    draw = ImageDraw.Draw(img)
    for y in range(H):
        t = y / H
        r = int(28 + accent[0] * 0.25 * t)
        g = int(14 + accent[1] * 0.18 * (1 - t))
        b = int(40 + accent[2] * 0.22 * t)
        draw.line([(0, y), (W, y)], fill=(r, g, b))
    if LOGO.is_file():
        logo = Image.open(LOGO).convert("RGBA")
        logo.thumbnail((220, 220), Image.Resampling.LANCZOS)
        img.paste(logo, ((W - logo.width) // 2, int(H * 0.22)), logo)
    brand = ImageFont.truetype(FONT_SERIF, 56)
    line = ImageFont.truetype(FONT_BOLD, 40)
    cta_f = ImageFont.truetype(FONT_BOLD, 34)

    def center(y: int, text: str, font, fill=(255, 245, 235)) -> None:
        bb = draw.textbbox((0, 0), text, font=font)
        tw = bb[2] - bb[0]
        draw.text(((W - tw) / 2, y), text, font=font, fill=fill)

    center(int(H * 0.40), "Gönül Köprüsü", brand, (255, 236, 214))
    center(int(H * 0.48), title[:36], line, (255, 255, 255))
    # CTA pill
    bb = draw.textbbox((0, 0), cta, font=cta_f)
    cw = bb[2] - bb[0] + 64
    ch = 64
    cx0 = (W - cw) // 2
    cy0 = int(H * 0.60)
    draw.rounded_rectangle((cx0, cy0, cx0 + cw, cy0 + ch), radius=32, fill=accent)
    center(cy0 + 14, cta, cta_f, (255, 255, 255))
    center(int(H * 0.72), "@gonulkoprusucom", cta_f, (255, 210, 190))
    img.save(out, "PNG", optimize=True)
    return out


def still_kenburns(src: Path, dur: float, out: Path, zoom_dir: int = 1) -> None:
    """Still → 9:16 Ken Burns video."""
    # Prepare cover still
    still = OUT / "parts" / f"_still_{out.stem}.png"
    im = Image.open(src).convert("RGB")
    im = ImageOps.fit(im, (W, H), method=Image.Resampling.LANCZOS, centering=(0.5, 0.35))
    im = ImageEnhance.Color(im).enhance(1.08)
    im.save(still, "PNG")
    z1, z2 = (1.0, 1.14) if zoom_dir > 0 else (1.14, 1.0)
    # zoompan
    frames = max(int(dur * FPS), 1)
    vf = (
        f"scale={W*2}:{H*2},"
        f"zoompan=z='{z1}+({z2-z1})*on/{frames}':x='iw/2-(iw/zoom/2)':y='ih/2-(ih/zoom/2)':"
        f"d={frames}:s={W}x{H}:fps={FPS},format=yuv420p"
    )
    run([
        "ffmpeg", "-y", "-loop", "1", "-i", str(still),
        "-vf", vf, "-t", f"{dur:.2f}", "-an",
        "-c:v", "libx264", "-preset", "veryfast", "-crf", "22", str(out),
    ])


def clip_segment(src: Path, start: float, dur: float, out: Path, grade: str) -> None:
    vf = (
        f"scale={W}:{H}:force_original_aspect_ratio=increase,"
        f"crop={W}:{H},fps={FPS},setsar=1,{grade},format=yuv420p"
    )
    run([
        "ffmpeg", "-y", "-ss", f"{start:.2f}", "-t", f"{dur:.2f}",
        "-i", str(src), "-vf", vf, "-an",
        "-c:v", "libx264", "-preset", "veryfast", "-crf", "22", str(out),
    ])


def endcard_video(png: Path, dur: float, out: Path) -> None:
    run([
        "ffmpeg", "-y", "-loop", "1", "-t", f"{dur:.2f}", "-i", str(png),
        "-vf", f"scale={W}:{H},fps={FPS},format=yuv420p",
        "-c:v", "libx264", "-tune", "stillimage", "-pix_fmt", "yuv420p", str(out),
    ])


def concat_parts(parts: list[Path], out: Path) -> None:
    lst = out.with_suffix(".txt")
    lst.write_text("".join(f"file '{p.resolve()}'\n" for p in parts), encoding="utf-8")
    run(["ffmpeg", "-y", "-f", "concat", "-safe", "0", "-i", str(lst), "-c", "copy", str(out)])


def apply_grade_to_still_video(src: Path, grade: str, out: Path) -> None:
    run([
        "ffmpeg", "-y", "-i", str(src),
        "-vf", f"{grade},format=yuv420p",
        "-c:v", "libx264", "-preset", "veryfast", "-crf", "22", "-an", str(out),
    ])


def mux_with_overlays(
    video: Path,
    audio: Path,
    logo: Path,
    text_cards: list[tuple[float, float, Path]],
    cta_card: Path,
    cta_start: float,
    total: float,
    out: Path,
) -> None:
    # Build filter: base + logo always + timed text cards + cta near end
    inputs = ["-i", str(video), "-i", str(audio), "-i", str(logo), "-i", str(cta_card)]
    for _, _, p in text_cards:
        inputs += ["-i", str(p)]

    # [0:v][2:v] overlay logo
    # then each text card
    n_text = len(text_cards)
    filter_parts = [
        f"[0:v][2:v]overlay=0:0:format=auto[vlogo]",
    ]
    last = "vlogo"
    for i, (start, end, _) in enumerate(text_cards):
        inp = 4 + i
        nxt = f"vt{i}"
        filter_parts.append(
            f"[{last}][{inp}:v]overlay=0:0:enable='between(t,{start:.2f},{end:.2f})'[{nxt}]"
        )
        last = nxt
    filter_parts.append(
        f"[{last}][3:v]overlay=0:0:enable='gte(t,{cta_start:.2f})'[vout]"
    )
    fc = ";".join(filter_parts)
    cmd = [
        "ffmpeg", "-y", *inputs,
        "-filter_complex", fc,
        "-map", "[vout]", "-map", "1:a",
        "-c:v", "libx264", "-preset", "veryfast", "-crf", "21",
        "-c:a", "aac", "-b:a", "160k",
        "-t", f"{total:.2f}", "-shortest",
        "-movflags", "+faststart", str(out),
    ]
    run(cmd)


def extract_poster(video: Path, out: Path, t: float = 1.0) -> None:
    run(["ffmpeg", "-y", "-ss", f"{t:.2f}", "-i", str(video), "-frames:v", "1", "-q:v", "2", str(out)])


def publish(src: Path) -> None:
    shutil.copy2(src, PUBLIC / src.name)
    shutil.copy2(src, MIRROR / src.name)
    shutil.copy2(src, ART / src.name)


def build_reel(ad: dict) -> dict:
    parts_dir = OUT / "parts" / ad["id"]
    if parts_dir.exists():
        shutil.rmtree(parts_dir)
    parts_dir.mkdir(parents=True)
    overlays = OUT / "overlays" / ad["id"]
    overlays.mkdir(parents=True, exist_ok=True)

    accent = tuple(ad["accent"])
    grade = ad["grade"]
    parts: list[Path] = []
    t = 0.0
    for i, scene in enumerate(ad["scenes"]):
        part = parts_dir / f"p{i}.mp4"
        if scene[0] == "still":
            _, key, dur = scene
            src = STILLS[key]
            if not src.is_file():
                raise FileNotFoundError(src)
            raw = parts_dir / f"p{i}_raw.mp4"
            still_kenburns(src, dur, raw, zoom_dir=1 if i % 2 == 0 else -1)
            apply_grade_to_still_video(raw, grade, part)
            t += dur
        else:
            _, key, start, dur = scene
            src = CLIPS[key]
            if not src.is_file():
                raise FileNotFoundError(src)
            clip_segment(src, start, dur, part, grade)
            t += dur
        parts.append(part)

    end_png = parts_dir / "end.png"
    make_endcard(ad["title"], ad["cta"], accent, end_png)
    end_mp4 = parts_dir / "end.mp4"
    end_dur = 2.8
    endcard_video(end_png, end_dur, end_mp4)
    parts.append(end_mp4)

    silent = parts_dir / "silent.mp4"
    concat_parts(parts, silent)

    vo = OUT / "vo" / f"{ad['id']}.mp3"
    vo_dur = audio_duration(vo)
    total = max(t + end_dur, vo_dur + 0.3)

    logo = make_logo_badge(overlays / "logo.png", accent)
    text_cards: list[tuple[float, float, Path]] = []
    for i, (start, end, text) in enumerate(ad["lines"]):
        card = make_text_card(text, overlays / f"t{i}.png", accent, big=(i == 0))
        text_cards.append((start, end, card))
    cta_card = make_cta_bar(ad["cta"], overlays / "cta.png", accent)
    cta_start = max(0.0, t - 0.15)

    out_mp4 = OUT / f"{ad['id']}.mp4"
    mux_with_overlays(silent, vo, logo, text_cards, cta_card, cta_start, total, out_mp4)
    poster = OUT / f"{ad['id']}.png"
    extract_poster(out_mp4, poster, t=1.1)
    still = OUT / f"{ad['id']}-still.png"
    shutil.copy2(end_png, still)

    publish(out_mp4)
    publish(poster)
    publish(still)
    return {
        "file": out_mp4.name,
        "title": ad["title"],
        "poster": poster.name,
        "kind": "realistic",
        "format": "9:16",
        "subtitle": "Reels v3 · farklı görseller",
        "channel": "Instagram Reels",
    }


async def main() -> None:
    ensure_dirs()
    missing = [k for k, p in {**CLIPS, **STILLS}.items() if not p.is_file()]
    if missing:
        raise SystemExit(f"Missing media: {missing}")

    print("== Voice ==")
    for ad in REELS:
        print("voice", ad["id"])
        await synth_voice(ad["voice"], OUT / "vo" / f"{ad['id']}.mp3")

    print("== Render ==")
    manifest_videos = []
    for ad in REELS:
        print("reel", ad["id"])
        manifest_videos.append(build_reel(ad))

    # Remove old wide variants clutter from listing preference; keep if present
    # Write captions
    reel_url = "https://gonulkoprusu.com/register?utm_source=instagram&utm_medium=reels&utm_campaign=v3"
    lines = [
        "=== Gönül Köprüsü — Instagram Reels v3 ===",
        "Her video farklı görsel + logo rozeti + güvenli alan metin",
        f"Link: {reel_url}",
        "",
    ]
    for ad in REELS:
        lines.append(f"--- {ad['id']} · {ad['title']} ---")
        lines.append(f"Dosya: {ad['id']}.mp4")
        lines.append(ad["ig_caption"].format(url=reel_url))
        lines.append("")
    cap = PUBLIC / "instagram-reels-captions.txt"
    cap.write_text("\n".join(lines).rstrip() + "\n", encoding="utf-8")
    shutil.copy2(cap, MIRROR / cap.name)
    shutil.copy2(cap, ART / cap.name)
    (ROOT / "marketing" / "instagram" / "reels-v3-captions.txt").write_text(cap.read_text(encoding="utf-8"), encoding="utf-8")

    # Refresh manifest from PUBLIC
    videos, photos = [], []
    titles = {v["file"]: v for v in manifest_videos}
    for p in sorted(PUBLIC.iterdir()):
        if not p.is_file() or p.name in {"README.txt", "manifest.json", "index.html", "instagram-reels-captions.txt", "blog-city-README.txt"}:
            continue
        if p.suffix.lower() == ".mp4":
            if not p.name.startswith("ig-"):
                continue
            meta = titles.get(p.name, {})
            videos.append({
                "file": p.name,
                "title": meta.get("title", p.stem.replace("-", " ")),
                "poster": p.name.replace(".mp4", ".png") if (PUBLIC / p.name.replace(".mp4", ".png")).exists() else "",
                "kind": "realistic",
                "format": "9:16" if "-wide" not in p.name and not p.name.startswith("ig-06") else "16:9",
                "subtitle": meta.get("subtitle", "Reels v3"),
                "channel": meta.get("channel", "Instagram Reels"),
            })
        elif p.suffix.lower() in {".png", ".jpg", ".webp"} and p.name.startswith("ig-"):
            photos.append({"file": p.name, "title": p.stem.replace("-", " "), "kind": "still"})

    payload = {
        "brand": "Gönül Köprüsü",
        "public_base": "https://gonulkoprusu.com/images/ads",
        "research_notes": [
            "Reels v3: her video farklı görsel set + renk grade",
            "Logo rozeti üst güvenli alanda; metin ortada pill içinde",
            "Alt Instagram UI bölgesine yazı basılmaz",
        ],
        "videos": videos,
        "photos": photos,
    }
    (PUBLIC / "manifest.json").write_text(json.dumps(payload, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    shutil.copy2(PUBLIC / "manifest.json", MIRROR / "manifest.json")
    shutil.copy2(PUBLIC / "manifest.json", ART / "manifest.json")
    admin_manifest = ROOT / "admin-panel" / "resources" / "data" / "ads-manifest.json"
    if admin_manifest.parent.is_dir():
        shutil.copy2(PUBLIC / "manifest.json", admin_manifest)

    readme = PUBLIC / "README.txt"
    readme.write_text(
        "Gönül Köprüsü — Instagram Reels v3\n"
        "=================================\n\n"
        "ig-01…ig-05: 9:16, farklı görseller, logo rozeti, güvenli alan metin\n"
        "Açıklama: instagram-reels-captions.txt\n"
        "Admin: Pazarlama + Reklam\n",
        encoding="utf-8",
    )
    shutil.copy2(readme, MIRROR / "README.txt")
    print("OK", len(manifest_videos), "reels →", PUBLIC)


if __name__ == "__main__":
    asyncio.run(main())
