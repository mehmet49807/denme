(() => {
  const base = (window.CHICKEN_BASE || '').replace(/\/$/, '');
  const api = (path) => base + path;
  const money = (n) =>
    new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(n);

  const staffLayout = document.querySelector('[data-staff-layout]');
  const openNav = () => {
    if (!staffLayout) return;
    staffLayout.classList.add('nav-open');
    const backdrop = staffLayout.querySelector('[data-nav-close].side-backdrop');
    if (backdrop) backdrop.hidden = false;
    document.body.style.overflow = 'hidden';
  };
  const closeNav = () => {
    if (!staffLayout) return;
    staffLayout.classList.remove('nav-open');
    const backdrop = staffLayout.querySelector('[data-nav-close].side-backdrop');
    if (backdrop) backdrop.hidden = true;
    document.body.style.overflow = '';
  };
  document.querySelectorAll('[data-nav-open]').forEach((btn) => {
    btn.addEventListener('click', openNav);
  });
  document.querySelectorAll('[data-nav-close]').forEach((btn) => {
    btn.addEventListener('click', closeNav);
  });
  document.querySelectorAll('.side nav a').forEach((link) => {
    link.addEventListener('click', closeNav);
  });

  function createCart(root) {
    if (!root) return null;
    const listEl = root.querySelector('[data-cart-list]');
    const totalEl = root.querySelector('[data-cart-total]');
    const countEl = root.querySelector('[data-cart-count]');
    const state = new Map();

    function render() {
      const items = [...state.values()];
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

      const total = items.reduce((sum, i) => sum + i.price * i.qty, 0);
      const count = items.reduce((sum, i) => sum + i.qty, 0);
      if (totalEl) totalEl.textContent = money(total);
      if (countEl) countEl.textContent = String(count);
    }

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

    document.querySelectorAll('[data-add-item]').forEach((btn) => {
      btn.addEventListener('click', () => {
        const id = Number(btn.getAttribute('data-add-item'));
        const name = btn.getAttribute('data-name') || 'Ürün';
        const price = Number(btn.getAttribute('data-price') || 0);
        const station = btn.getAttribute('data-station') || 'kitchen';
        if (!state.has(id)) state.set(id, { id, name, price, station, qty: 0 });
        state.get(id).qty += 1;
        render();
      });
    });

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
        render();
      },
      isEmpty() {
        return state.size === 0;
      },
    };
  }

  const onlineCart = createCart(document.querySelector('[data-online-cart]'));
  const waiterCart = createCart(document.querySelector('[data-waiter-cart]'));

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

  const tabs = document.querySelectorAll('[data-cat-tab]');
  const cards = document.querySelectorAll('[data-cat]');
  tabs.forEach((tab) => {
    tab.addEventListener('click', () => {
      const key = tab.getAttribute('data-cat-tab');
      tabs.forEach((t) => t.classList.toggle('active', t === tab));
      cards.forEach((card) => {
        const show = key === 'all' || card.getAttribute('data-cat') === key;
        card.style.display = show ? '' : 'none';
      });
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
