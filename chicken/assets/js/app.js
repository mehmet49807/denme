(() => {
  const base = (window.CHICKEN_BASE || '').replace(/\/$/, '');
  const api = (path) => base + path;
  const money = (n) =>
    new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(n);

  const staffLayout = document.querySelector('[data-staff-layout]');
  const toggleButtons = document.querySelectorAll('[data-nav-toggle]');
  const sidePanel = document.querySelector('#staff-side');
  const navLabel = document.querySelector('[data-nav-label]');

  const setNavOpen = (open) => {
    if (!staffLayout) return;
    staffLayout.classList.toggle('nav-open', open);
    const backdrop = staffLayout.querySelector('.side-backdrop');
    if (backdrop) backdrop.hidden = !open;
    if (sidePanel) sidePanel.setAttribute('aria-hidden', open ? 'false' : 'true');
    toggleButtons.forEach((btn) => {
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
      btn.classList.toggle('is-open', open);
    });
    if (navLabel) navLabel.textContent = open ? 'Kapat' : 'Menü';
    document.body.style.overflow = open ? 'hidden' : '';
  };
  const openNav = () => setNavOpen(true);
  const closeNav = () => setNavOpen(false);
  const toggleNav = () => setNavOpen(!(staffLayout && staffLayout.classList.contains('nav-open')));

  toggleButtons.forEach((btn) => btn.addEventListener('click', toggleNav));
  document.querySelectorAll('[data-nav-close]').forEach((btn) => {
    btn.addEventListener('click', closeNav);
  });
  document.querySelectorAll('.side-link').forEach((link) => {
    link.addEventListener('click', closeNav);
  });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeNav();
  });

  const applyCategoryFilter = (key) => {
    const cards = document.querySelectorAll('[data-cat]');
    const hasMenu = cards.length > 0;
    if (!hasMenu) {
      const target = api('/garson') + (key && key !== 'all' ? `?cat=${encodeURIComponent(key)}` : '');
      window.location.href = target;
      return;
    }
    document.querySelectorAll('[data-cat-tab]').forEach((t) => {
      t.classList.toggle('active', t.getAttribute('data-cat-tab') === key);
    });
    cards.forEach((card) => {
      const show = key === 'all' || card.getAttribute('data-cat') === key;
      card.style.display = show ? '' : 'none';
    });
    closeNav();
  };

  document.querySelectorAll('[data-cat-tab]').forEach((tab) => {
    tab.addEventListener('click', () => {
      applyCategoryFilter(tab.getAttribute('data-cat-tab') || 'all');
    });
  });

  // Restore category filter from query string on garson page
  try {
    const params = new URLSearchParams(window.location.search);
    const cat = params.get('cat');
    if (cat) applyCategoryFilter(cat);
  } catch (_) {}

  const WAITER_CART_KEY = 'chicken_waiter_cart_v1';

  function readStorage(key) {
    try {
      const raw = localStorage.getItem(key);
      if (!raw) return { items: [], table_id: '', note: '' };
      const data = JSON.parse(raw);
      return {
        items: Array.isArray(data.items) ? data.items : [],
        table_id: data.table_id != null ? String(data.table_id) : '',
        note: typeof data.note === 'string' ? data.note : '',
      };
    } catch (_) {
      return { items: [], table_id: '', note: '' };
    }
  }

  function writeStorage(key, payload) {
    try {
      localStorage.setItem(key, JSON.stringify(payload));
    } catch (_) {}
  }

  function updateCartBadges(count) {
    document.querySelectorAll('[data-cart-badge]').forEach((el) => {
      el.textContent = String(count);
      el.hidden = count <= 0;
      el.classList.toggle('is-empty', count <= 0);
    });
  }

  function createCart(root, options = {}) {
    const persistKey = options.persistKey || null;
    const bindAdd = options.bindAdd !== false;
    if (!root && !persistKey) return null;

    const listEl = root ? root.querySelector('[data-cart-list]') : null;
    const totalEl = root ? root.querySelector('[data-cart-total]') : null;
    const countEl = root ? root.querySelector('[data-cart-count]') : null;
    const tableSelect = root ? root.querySelector('select[name="table_id"]') : null;
    const noteInput = root ? root.querySelector('textarea[name="customer_note"]') : null;
    const state = new Map();
    let meta = { table_id: '', note: '' };

    if (persistKey) {
      const saved = readStorage(persistKey);
      meta.table_id = saved.table_id;
      meta.note = saved.note;
      saved.items.forEach((item) => {
        if (!item || !item.id) return;
        state.set(Number(item.id), {
          id: Number(item.id),
          name: String(item.name || 'Ürün'),
          price: Number(item.price || 0),
          station: item.station === 'bar' ? 'bar' : 'kitchen',
          qty: Math.max(1, Number(item.qty || 1)),
        });
      });
      if (tableSelect && meta.table_id) tableSelect.value = meta.table_id;
      if (noteInput && meta.note) noteInput.value = meta.note;
    }

    function persist() {
      if (!persistKey) return;
      writeStorage(persistKey, {
        items: [...state.values()],
        table_id: tableSelect ? String(tableSelect.value || '') : meta.table_id,
        note: noteInput ? String(noteInput.value || '') : meta.note,
      });
    }

    function render() {
      const items = [...state.values()];
      if (listEl) {
        listEl.innerHTML = items.length
          ? items
              .map(
                (item) => `
          <div class="cart-line" data-id="${item.id}">
            <div>
              <strong>${item.name}</strong>
              <div class="muted small">${money(item.price)} · ${item.station === 'bar' ? 'Bar' : 'Mutfak'}</div>
            </div>
            <div class="qty">
              <button type="button" data-dec="${item.id}">−</button>
              <span>${item.qty}</span>
              <button type="button" data-inc="${item.id}">+</button>
            </div>
          </div>`
              )
              .join('')
          : '<p class="muted">Henüz ürün eklenmedi.</p>';
      }

      const total = items.reduce((sum, i) => sum + i.price * i.qty, 0);
      const count = items.reduce((sum, i) => sum + i.qty, 0);
      if (totalEl) totalEl.textContent = money(total);
      if (countEl) countEl.textContent = String(count);
      if (persistKey) updateCartBadges(count);
      persist();
    }

    if (root) {
      root.addEventListener('click', (e) => {
        const t = e.target;
        if (!(t instanceof HTMLElement)) return;
        const inc = t.getAttribute('data-inc');
        const dec = t.getAttribute('data-dec');
        if (inc && state.has(Number(inc))) {
          state.get(Number(inc)).qty += 1;
          render();
        }
        if (dec && state.has(Number(dec))) {
          const item = state.get(Number(dec));
          item.qty -= 1;
          if (item.qty <= 0) state.delete(Number(dec));
          render();
        }
      });
      if (tableSelect) {
        tableSelect.addEventListener('change', () => {
          meta.table_id = String(tableSelect.value || '');
          persist();
        });
      }
      if (noteInput) {
        noteInput.addEventListener('input', () => {
          meta.note = String(noteInput.value || '');
          persist();
        });
      }
    }

    if (bindAdd) {
      document.querySelectorAll('[data-add-item]').forEach((btn) => {
        btn.addEventListener('click', () => {
          const id = Number(btn.getAttribute('data-add-item'));
          const name = btn.getAttribute('data-name') || 'Ürün';
          const price = Number(btn.getAttribute('data-price') || 0);
          const station = btn.getAttribute('data-station') || 'kitchen';
          if (!state.has(id)) state.set(id, { id, name, price, station, qty: 0 });
          state.get(id).qty += 1;
          render();
          btn.classList.add('is-added');
          setTimeout(() => btn.classList.remove('is-added'), 350);
        });
      });
    }

    render();
    return {
      payload() {
        return [...state.values()].map((i) => ({
          menu_item_id: i.id,
          quantity: i.qty,
        }));
      },
      clear() {
        state.clear();
        meta = { table_id: '', note: '' };
        if (tableSelect) tableSelect.value = '';
        if (noteInput) noteInput.value = '';
        if (listEl) listEl.innerHTML = '<p class="muted">Henüz ürün eklenmedi.</p>';
        if (totalEl) totalEl.textContent = money(0);
        if (countEl) countEl.textContent = '0';
        if (persistKey) {
          try {
            localStorage.removeItem(persistKey);
          } catch (_) {}
          updateCartBadges(0);
        }
      },
      isEmpty() {
        return state.size === 0;
      },
    };
  }

  const onlineRoot = document.querySelector('[data-online-cart]');
  const onlineCart = createCart(onlineRoot, { bindAdd: !!onlineRoot });
  const waiterRoot = document.querySelector('[data-waiter-cart]');
  const isStaffWaiterUi = !!document.querySelector('[data-staff-layout]');
  const waiterCart = isStaffWaiterUi
    ? createCart(waiterRoot, {
        persistKey: WAITER_CART_KEY,
        bindAdd: !onlineRoot && !!document.querySelector('[data-add-item]'),
      })
    : null;

  const onlineForm = document.querySelector('[data-online-form]');
  if (onlineForm && onlineCart) {
    onlineForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      if (onlineCart.isEmpty()) {
        alert('Lütfen en az bir ürün seçin.');
        return;
      }
      const fd = new FormData(onlineForm);
      const body = {
        customer_name: String(fd.get('customer_name') || ''),
        customer_phone: String(fd.get('customer_phone') || ''),
        customer_note: String(fd.get('customer_note') || ''),
        items: onlineCart.payload(),
      };
      if (!body.customer_name || !body.customer_phone) {
        alert('Ad ve telefon gerekli.');
        return;
      }
      const btn = onlineForm.querySelector('button[type="submit"]');
      if (btn) btn.disabled = true;
      try {
        const res = await fetch(api('/api/orders'), {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(body),
        });
        const data = await res.json();
        if (!data.ok) throw new Error(data.error || 'Sipariş alınamadı');
        onlineCart.clear();
        window.location.href = api(`/takip?code=${encodeURIComponent(data.order.order_code)}`);
      } catch (err) {
        alert(err.message || 'Hata');
      } finally {
        if (btn) btn.disabled = false;
      }
    });
  }

  const waiterForm = document.querySelector('[data-waiter-form]');
  if (waiterForm && waiterCart) {
    waiterForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      if (waiterCart.isEmpty()) {
        alert('Ürün seçin.');
        return;
      }
      const tableId = Number(new FormData(waiterForm).get('table_id') || 0);
      const note = String(new FormData(waiterForm).get('customer_note') || '');
      if (!tableId) {
        alert('Masa seçin.');
        return;
      }
      const btn = waiterForm.querySelector('button[type="submit"]');
      if (btn) btn.disabled = true;
      try {
        const res = await fetch(api('/api/staff/orders'), {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            table_id: tableId,
            customer_note: note,
            items: waiterCart.payload(),
          }),
        });
        const data = await res.json();
        if (!data.ok) throw new Error(data.error || 'Sipariş gönderilemedi');
        waiterCart.clear();
        window.location.href = api(`/garson/fis/${data.order.id}`);
      } catch (err) {
        alert(err.message || 'Hata');
      } finally {
        if (btn) btn.disabled = false;
      }
    });
  }

  document.querySelectorAll('[data-status-btn]').forEach((btn) => {
    btn.addEventListener('click', async () => {
      const id = btn.getAttribute('data-order-id');
      const status = btn.getAttribute('data-status-btn');
      const res = await fetch(api(`/api/orders/${id}/status`), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ status }),
      });
      const data = await res.json();
      if (!data.ok) {
        alert(data.error || 'Güncellenemedi');
        return;
      }
      location.reload();
    });
  });

  document.querySelectorAll('[data-item-status]').forEach((btn) => {
    btn.addEventListener('click', async () => {
      const itemId = btn.getAttribute('data-item-id');
      const status = btn.getAttribute('data-item-status');
      const res = await fetch(api('/api/station/item-status'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ item_id: Number(itemId), status }),
      });
      const data = await res.json();
      if (!data.ok) {
        alert(data.error || 'Güncellenemedi');
        return;
      }
      location.reload();
    });
  });

  document.querySelectorAll('[data-note-save]').forEach((btn) => {
    btn.addEventListener('click', async () => {
      const id = btn.getAttribute('data-note-save');
      const box = btn.closest('[data-order-note]');
      const input = box ? box.querySelector('[data-note-input]') : null;
      const statusEl = box ? box.querySelector('[data-note-status]') : null;
      if (!input) return;
      btn.disabled = true;
      if (statusEl) statusEl.textContent = 'Kaydediliyor...';
      try {
        const res = await fetch(api(`/api/orders/${id}/note`), {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ note: input.value || '' }),
        });
        const data = await res.json();
        if (!data.ok) throw new Error(data.error || 'Not kaydedilemedi');
        if (statusEl) statusEl.textContent = 'Kaydedildi';
        setTimeout(() => {
          if (statusEl) statusEl.textContent = '';
        }, 1800);
      } catch (err) {
        if (statusEl) statusEl.textContent = '';
        alert(err.message || 'Not kaydedilemedi');
      } finally {
        btn.disabled = false;
      }
    });
  });

  const trackCode = document.body.getAttribute('data-track-code');
  if (trackCode) {
    setInterval(async () => {
      try {
        const res = await fetch(api(`/api/orders/${encodeURIComponent(trackCode)}`));
        const data = await res.json();
        if (!data.ok) return;
        const el = document.querySelector('[data-live-status]');
        if (el) el.textContent = data.order.status_label;
      } catch (_) {}
    }, 8000);
  }
})();
