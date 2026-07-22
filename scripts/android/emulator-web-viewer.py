#!/usr/bin/env python3
"""Minimal Android emulator web viewer via adb screencap (no KVM/VNC needed)."""
from __future__ import annotations

import os
import subprocess
import threading
import time
from http.server import BaseHTTPRequestHandler, ThreadingHTTPServer
from urllib.parse import parse_qs, urlparse

ADB = os.environ.get("ADB", "adb")
DEVICE = os.environ.get("ANDROID_SERIAL", "emulator-5554")
PORT = int(os.environ.get("VIEWER_PORT", "8088"))
HOST = os.environ.get("VIEWER_HOST", "0.0.0.0")

_lock = threading.Lock()
_latest = b""
_latest_ts = 0.0


def adb(*args: str, timeout: float = 20) -> bytes:
    cmd = [ADB, "-s", DEVICE, *args]
    return subprocess.check_output(cmd, stderr=subprocess.DEVNULL, timeout=timeout)


def capturer() -> None:
    global _latest, _latest_ts
    while True:
        try:
            png = adb("exec-out", "screencap", "-p", timeout=45)
            if png.startswith(b"\x89PNG"):
                with _lock:
                    _latest = png
                    _latest_ts = time.time()
        except Exception:
            time.sleep(1.5)
            continue
        time.sleep(0.8)


HTML = """<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Gönül Köprüsü Emülatör</title>
  <style>
    :root { color-scheme: dark; }
    body { margin:0; font-family: system-ui, sans-serif; background:#111; color:#eee; }
    header { padding:12px 16px; background:#1c1c1c; border-bottom:1px solid #333;
      display:flex; gap:12px; align-items:center; flex-wrap:wrap; }
    h1 { font-size:16px; margin:0; font-weight:600; }
    .meta { opacity:.7; font-size:12px; }
    main { display:flex; justify-content:center; padding:16px; }
    #screen {
      max-width:min(420px, 100%);
      width:100%;
      border-radius:18px;
      border:1px solid #333;
      background:#000;
      cursor:crosshair;
      touch-action:none;
    }
    button { background:#2a2a2a; color:#fff; border:1px solid #444; border-radius:8px;
      padding:8px 12px; cursor:pointer; }
    button:hover { background:#3a3a3a; }
  </style>
</head>
<body>
  <header>
    <h1>Emülatör canlı görüntü</h1>
    <span class="meta" id="status">bağlanıyor…</span>
    <button type="button" id="back">Geri</button>
    <button type="button" id="home">Home</button>
    <button type="button" id="reload">Yenile</button>
  </header>
  <main>
    <img id="screen" alt="emulator" draggable="false" />
  </main>
  <script>
    const img = document.getElementById('screen');
    const status = document.getElementById('status');
    function refresh() {
      const u = '/screen.png?ts=' + Date.now();
      const probe = new Image();
      probe.onload = () => { img.src = u; status.textContent = 'canlı · tıklayarak dokun'; };
      probe.onerror = () => { status.textContent = 'görüntü alınamadı'; };
      probe.src = u;
    }
    setInterval(refresh, 1200);
    refresh();
    function send(path) {
      fetch(path, { method: 'POST' }).catch(() => {});
    }
    img.addEventListener('click', (e) => {
      const r = img.getBoundingClientRect();
      const x = (e.clientX - r.left) / r.width;
      const y = (e.clientY - r.top) / r.height;
      send('/tap?x=' + x.toFixed(4) + '&y=' + y.toFixed(4));
    });
    document.getElementById('back').onclick = () => send('/key?code=4');
    document.getElementById('home').onclick = () => send('/key?code=3');
    document.getElementById('reload').onclick = refresh;
  </script>
</body>
</html>
"""


class Handler(BaseHTTPRequestHandler):
    def log_message(self, fmt, *args):
        return

    def _ok(self, body: bytes, content_type: str) -> None:
        self.send_response(200)
        self.send_header("Content-Type", content_type)
        self.send_header("Content-Length", str(len(body)))
        self.send_header("Cache-Control", "no-store")
        self.end_headers()
        self.wfile.write(body)

    def do_GET(self) -> None:
        path = urlparse(self.path).path
        if path in ("/", "/index.html"):
            self._ok(HTML.encode(), "text/html; charset=utf-8")
            return
        if path == "/screen.png":
            with _lock:
                data = _latest
            if not data:
                self.send_error(503, "no frame yet")
                return
            self._ok(data, "image/png")
            return
        if path == "/health":
            with _lock:
                age = time.time() - _latest_ts if _latest_ts else -1
            self._ok(f'{{"ok":true,"age_s":{age:.1f}}}'.encode(), "application/json")
            return
        self.send_error(404)

    def do_POST(self) -> None:
        parsed = urlparse(self.path)
        qs = parse_qs(parsed.query)
        try:
            if parsed.path == "/tap":
                x = float(qs.get("x", ["0"])[0])
                y = float(qs.get("y", ["0"])[0])
                # Device physical size 1080x2400 for GonulPhone
                out = adb("shell", "wm", "size").decode(errors="ignore")
                # Physical size: 1080x2400
                w, h = 1080, 2400
                if "Physical size:" in out:
                    dim = out.split("Physical size:")[-1].strip().split()[0]
                    w, h = [int(p) for p in dim.split("x")]
                ax, ay = int(x * w), int(y * h)
                adb("shell", "input", "tap", str(ax), str(ay))
                self._ok(b'{"ok":true}', "application/json")
                return
            if parsed.path == "/key":
                code = qs.get("code", ["4"])[0]
                adb("shell", "input", "keyevent", code)
                self._ok(b'{"ok":true}', "application/json")
                return
        except Exception as exc:
            self.send_error(500, str(exc))
            return
        self.send_error(404)


def main() -> None:
    threading.Thread(target=capturer, daemon=True).start()
    httpd = ThreadingHTTPServer((HOST, PORT), Handler)
    print(f"Emulator web viewer on http://{HOST}:{PORT}/", flush=True)
    httpd.serve_forever()


if __name__ == "__main__":
    main()
