export class TouchControls {
  constructor({ joystickZone, knob, base, buttons }) {
    this.move = { x: 0, y: 0 };
    this.actions = { pass: false, shoot: false, sprint: false };
    this._keys = { up: false, down: false, left: false, right: false };
    this._active = false;
    this._origin = { x: 0, y: 0 };
    this._pointerId = null;
    this.knob = knob;
    this.base = base;
    this.maxRadius = 46;

    joystickZone.addEventListener('pointerdown', (e) => this._onDown(e), { passive: false });
    window.addEventListener('pointermove', (e) => this._onMove(e), { passive: false });
    window.addEventListener('pointerup', (e) => this._onUp(e));
    window.addEventListener('pointercancel', (e) => this._onUp(e));

    for (const [key, el] of Object.entries(buttons)) {
      const set = (v) => {
        this.actions[key] = v;
      };
      el.addEventListener('pointerdown', (e) => {
        e.preventDefault();
        set(true);
      });
      el.addEventListener('pointerup', () => set(false));
      el.addEventListener('pointerleave', () => set(false));
      el.addEventListener('pointercancel', () => set(false));
    }

    // Keyboard fallback (desktop preview)
    window.addEventListener('keydown', (e) => this._key(e, true));
    window.addEventListener('keyup', (e) => this._key(e, false));
  }

  _key(e, down) {
    const k = e.key.toLowerCase();
    if (k === 'w' || k === 'arrowup') this._keys.up = down;
    if (k === 's' || k === 'arrowdown') this._keys.down = down;
    if (k === 'a' || k === 'arrowleft') this._keys.left = down;
    if (k === 'd' || k === 'arrowright') this._keys.right = down;
    if (k === ' ') {
      e.preventDefault();
      this.actions.shoot = down;
    }
    if (k === 'j') this.actions.pass = down;
    if (k === 'shift') this.actions.sprint = down;
  }

  _onDown(e) {
    e.preventDefault();
    this._active = true;
    this._pointerId = e.pointerId;
    const rect = this.base.getBoundingClientRect();
    this._origin = {
      x: rect.left + rect.width / 2,
      y: rect.top + rect.height / 2,
    };
    this._apply(e.clientX, e.clientY);
  }

  _onMove(e) {
    if (!this._active || e.pointerId !== this._pointerId) return;
    e.preventDefault();
    this._apply(e.clientX, e.clientY);
  }

  _onUp(e) {
    if (e.pointerId !== this._pointerId) return;
    this._active = false;
    this._pointerId = null;
    this.move.x = 0;
    this.move.y = 0;
    this.knob.style.transform = 'translate(0px, 0px)';
  }

  _apply(cx, cy) {
    let dx = cx - this._origin.x;
    let dy = cy - this._origin.y;
    const len = Math.hypot(dx, dy) || 1;
    const clamped = Math.min(len, this.maxRadius);
    dx = (dx / len) * clamped;
    dy = (dy / len) * clamped;
    this.knob.style.transform = `translate(${dx}px, ${dy}px)`;
    this.move.x = dx / this.maxRadius;
    this.move.y = dy / this.maxRadius;
  }

  sample() {
    let x = this.move.x;
    let y = this.move.y;
    if (this._keys) {
      if (this._keys.left) x = -1;
      if (this._keys.right) x = 1;
      if (this._keys.up) y = -1;
      if (this._keys.down) y = 1;
    }
    const pass = this.actions.pass;
    const shoot = this.actions.shoot;
    // Edge-trigger pass/shoot so holding doesn't spam
    if (pass) this.actions.pass = false;
    if (shoot) this.actions.shoot = false;
    return {
      x,
      y,
      sprint: this.actions.sprint,
      pass,
      shoot,
    };
  }
}
