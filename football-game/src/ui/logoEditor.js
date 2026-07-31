/** Basit takım logosu oluşturucu — şekil + renk + kısa ad. */

const SHAPES = ['circle', 'shield', 'hex', 'diamond'];

export function createLogoEditor({ canvas, onChange }) {
  const ctx = canvas.getContext('2d');
  const state = {
    shape: 'shield',
    primary: '#1b6ca8',
    secondary: '#f0c14b',
    accent: '#ffffff',
    short: 'GA',
  };

  function draw() {
    const w = canvas.width;
    const h = canvas.height;
    ctx.clearRect(0, 0, w, h);

    // pitch-ish bg
    const g = ctx.createLinearGradient(0, 0, w, h);
    g.addColorStop(0, '#0a1628');
    g.addColorStop(1, '#132f4c');
    ctx.fillStyle = g;
    ctx.fillRect(0, 0, w, h);

    const cx = w / 2;
    const cy = h / 2;
    const r = Math.min(w, h) * 0.38;

    ctx.save();
    ctx.translate(cx, cy);
    pathShape(ctx, state.shape, r);
    ctx.fillStyle = state.primary;
    ctx.fill();
    ctx.lineWidth = r * 0.08;
    ctx.strokeStyle = state.secondary;
    ctx.stroke();

    // inner accent
    ctx.beginPath();
    ctx.arc(0, 0, r * 0.55, 0, Math.PI * 2);
    ctx.strokeStyle = state.accent;
    ctx.lineWidth = r * 0.04;
    ctx.stroke();

    ctx.fillStyle = state.accent;
    ctx.font = `bold ${Math.floor(r * 0.55)}px Bebas Neue, Impact, sans-serif`;
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText(state.short.slice(0, 4), 0, 2);
    ctx.restore();

    onChange?.(getDataUrl(), { ...state });
  }

  function pathShape(c, shape, r) {
    c.beginPath();
    if (shape === 'circle') {
      c.arc(0, 0, r, 0, Math.PI * 2);
    } else if (shape === 'hex') {
      for (let i = 0; i < 6; i++) {
        const a = (Math.PI / 3) * i - Math.PI / 6;
        const x = Math.cos(a) * r;
        const y = Math.sin(a) * r;
        if (i === 0) c.moveTo(x, y);
        else c.lineTo(x, y);
      }
      c.closePath();
    } else if (shape === 'diamond') {
      c.moveTo(0, -r);
      c.lineTo(r * 0.85, 0);
      c.lineTo(0, r);
      c.lineTo(-r * 0.85, 0);
      c.closePath();
    } else {
      // shield
      c.moveTo(0, -r);
      c.lineTo(r * 0.85, -r * 0.55);
      c.lineTo(r * 0.75, r * 0.25);
      c.quadraticCurveTo(0, r * 1.15, -r * 0.75, r * 0.25);
      c.lineTo(-r * 0.85, -r * 0.55);
      c.closePath();
    }
  }

  function getDataUrl() {
    return canvas.toDataURL('image/png');
  }

  function set(partial) {
    Object.assign(state, partial);
    if (partial.short != null) state.short = String(partial.short).toUpperCase().slice(0, 4);
    draw();
  }

  function randomize() {
    const colors = [
      ['#1b6ca8', '#f0c14b', '#ffffff'],
      ['#8b1e3f', '#f5f5f5', '#ffd60a'],
      ['#0d5c3d', '#e8e8e8', '#d4a017'],
      ['#2c2c2c', '#e85d04', '#ffffff'],
      ['#13315c', '#76c893', '#ffffff'],
    ];
    const c = colors[Math.floor(Math.random() * colors.length)];
    set({
      shape: SHAPES[Math.floor(Math.random() * SHAPES.length)],
      primary: c[0],
      secondary: c[1],
      accent: c[2],
    });
  }

  draw();

  return { set, draw, getDataUrl, randomize, state, shapes: SHAPES };
}

export function makeAiLogo(short, colors) {
  const c = document.createElement('canvas');
  c.width = 256;
  c.height = 256;
  const editor = createLogoEditor({ canvas: c, onChange: () => {} });
  editor.set({
    short,
    primary: colors.primary,
    secondary: colors.secondary,
    accent: colors.accent || '#ffffff',
    shape: SHAPES[Math.abs(short.charCodeAt(0) || 0) % SHAPES.length],
  });
  return editor.getDataUrl();
}
