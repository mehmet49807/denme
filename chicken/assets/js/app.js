(() => {
  const base = (window.CHICKEN_BASE || '').replace(/\/$/, '');
  const api = (path) => base + path;
  const money = (n) =>
    new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(n);
  const escAttr = (s) =>
    String(s || '')
      .replace(/&/g, '&amp;')
      .replace(/"/g, '&quot;')
      .replace(/</g, '&lt;');

  function csrfToken() {
    if (typeof window.CHICKEN_CSRF === 'string' && window.CHICKEN_CSRF) {
      return window.CHICKEN_CSRF;
    }
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') || '' : '';
  }

  async function postJson(path, body) {
    const payload = Object.assign({}, body || {}, { _csrf: csrfToken() });
    const res = await fetch(api(path), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': csrfToken(),
      },
      body: JSON.stringify(payload),
    });
    const data = await res.json();
    if (!data.ok) throw new Error(data.error || 'İşlem başarısız');
    return data;
  }

  const staffLayout = document.querySelector('[data-staff-layout]');
  const toggleButtons = document.querySelectorAll('[data-nav-toggle]');
  const sidePanel = document.querySelector('#staff-side');
  const navLabel = document.querySelector('[data-nav-label]');
  const isAdminArea = !!(staffLayout && staffLayout.hasAttribute('data-admin-area'));
  const isDesktopAdminNav = () =>
    isAdminArea && window.matchMedia('(min-width: 900px)').matches;

  const setNavOpen = (open) => {
    if (!staffLayout) return;
    if (isDesktopAdminNav()) {
      staffLayout.classList.remove('nav-open');
      const backdrop = staffLayout.querySelector('.side-backdrop');
      if (backdrop) backdrop.hidden = true;
      if (sidePanel) sidePanel.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = '';
      return;
    }
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
  document.querySelectorAll('.side-link, .admin-side-nav .side-cat').forEach((link) => {
    link.addEventListener('click', () => {
      if (!isDesktopAdminNav()) closeNav();
    });
  });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeNav();
  });
  if (staffLayout && staffLayout.hasAttribute('data-admin-home-nav') && !isDesktopAdminNav()) {
    openNav();
  }
  if (isDesktopAdminNav() && sidePanel) {
    sidePanel.setAttribute('aria-hidden', 'false');
  }

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
  const ONLINE_CART_KEY = 'chicken_online_cart';
  const isPublicLayout = !!document.querySelector('[data-public-layout]');

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
    document.querySelectorAll('.cart-fab').forEach((el) => {
      el.classList.toggle('has-items', count > 0);
    });
  }

  function syncOnlineBadgeFromStorage() {
    if (!isPublicLayout) return;
    const saved = readStorage(ONLINE_CART_KEY);
    const count = (saved.items || []).reduce((sum, i) => sum + Math.max(1, Number(i.qty || 1)), 0);
    updateCartBadges(count);
  }

  function createCart(root, options = {}) {
    const persistKey = options.persistKey || null;
    const bindAdd = options.bindAdd !== false;
    if (!root && !persistKey) return null;

    const listEl = root ? root.querySelector('[data-cart-list]') : null;
    const totalEl = root ? root.querySelector('[data-cart-total]') : null;
    const subtotalEl = root ? root.querySelector('[data-cart-subtotal]') : null;
    const discountEl = root ? root.querySelector('[data-cart-discount]') : null;
    const discountRow = root ? root.querySelector('[data-cart-discount-row]') : null;
    const discountInput = root ? root.querySelector('[data-discount-code]') : null;
    const countEl = root ? root.querySelector('[data-cart-count]') : null;
    const tableSelect = root ? root.querySelector('select[name="table_id"]') : null;
    const noteInput = root ? root.querySelector('textarea[name="customer_note"]') : null;
    const welcomePercent = Number(root?.getAttribute('data-welcome-percent') || 10);
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
          note: typeof item.note === 'string' ? item.note : '',
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

    function discountAmount(subtotal) {
      if (!discountInput) return 0;
      const code = String(discountInput.value || '').trim().toUpperCase();
      if (code === 'YENI10' && subtotal > 0) {
        return Math.round(subtotal * (welcomePercent / 100) * 100) / 100;
      }
      return 0;
    }

    function render() {
      const items = [...state.values()];
      if (listEl) {
        listEl.innerHTML = items.length
          ? items
              .map(
                (item) => `
          <div class="cart-line" data-id="${item.id}">
            <div class="cart-line-main">
              <div>
                <strong>${item.name}</strong>
                <div class="muted small">${money(item.price)} · ${item.station === 'bar' ? 'Bar' : 'Mutfak'}</div>
              </div>
              <div class="qty">
                <button type="button" data-dec="${item.id}">−</button>
                <span>${item.qty}</span>
                <button type="button" data-inc="${item.id}">+</button>
              </div>
            </div>
            <label class="cart-item-note">
              ${item.station === 'bar' ? 'Bar' : 'Mutfak'} notu
              <input type="text" maxlength="255" data-item-cart-note="${item.id}" value="${escAttr(item.note || '')}" placeholder="Bu ürün için not...">
            </label>
          </div>`
              )
              .join('')
          : '<p class="muted">Henüz ürün eklenmedi.</p>';
      }

      const subtotal = items.reduce((sum, i) => sum + i.price * i.qty, 0);
      const disc = discountAmount(subtotal);
      const total = Math.max(0, subtotal - disc);
      const count = items.reduce((sum, i) => sum + i.qty, 0);
      if (subtotalEl) subtotalEl.textContent = money(subtotal);
      if (discountEl) discountEl.textContent = '−' + money(disc);
      if (discountRow) discountRow.hidden = disc <= 0;
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
      root.addEventListener('input', (e) => {
        const t = e.target;
        if (!(t instanceof HTMLInputElement)) return;
        const id = t.getAttribute('data-item-cart-note');
        if (id && state.has(Number(id))) {
          state.get(Number(id)).note = t.value || '';
          persist();
        }
        if (t === discountInput || t.hasAttribute('data-discount-code')) {
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
          if (!state.has(id)) state.set(id, { id, name, price, station, qty: 0, note: '' });
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
          note: i.note || '',
        }));
      },
      clear() {
        state.clear();
        meta = { table_id: '', note: '' };
        if (tableSelect) tableSelect.value = '';
        if (noteInput) noteInput.value = '';
        if (discountInput) discountInput.value = '';
        if (listEl) listEl.innerHTML = '<p class="muted">Henüz ürün eklenmedi.</p>';
        if (subtotalEl) subtotalEl.textContent = money(0);
        if (discountEl) discountEl.textContent = '−' + money(0);
        if (discountRow) discountRow.hidden = true;
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
  const onlinePersist =
    onlineRoot?.getAttribute('data-cart-persist') ||
    (isPublicLayout ? ONLINE_CART_KEY : null);
  const onlineCart = createCart(onlineRoot, {
    persistKey: onlinePersist,
    bindAdd:
      !!onlineRoot ||
      (isPublicLayout && !!document.querySelector('[data-add-item]')),
  });
  if (isPublicLayout && !onlineCart) {
    syncOnlineBadgeFromStorage();
  }

  const waiterRoot = document.querySelector('[data-waiter-cart]');
  const isStaffWaiterUi = !!document.querySelector('[data-staff-layout]');
  const shouldPersistWaiter =
    !!waiterRoot?.getAttribute('data-cart-persist') ||
    (!waiterRoot && !!document.querySelector('[data-waiter-menu]'));
  const waiterCart = isStaffWaiterUi
    ? createCart(waiterRoot, {
        persistKey: shouldPersistWaiter ? WAITER_CART_KEY : null,
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
      const paymentPreference = String(fd.get('payment_preference') || '').trim();
      if (!paymentPreference) {
        alert('Kapıda ödeme tercihinizi seçin (nakit veya kart).');
        return;
      }
      const body = {
        customer_name: String(fd.get('customer_name') || ''),
        customer_phone: String(fd.get('customer_phone') || ''),
        customer_note: String(fd.get('customer_note') || ''),
        discount_code: String(fd.get('discount_code') || '').trim(),
        payment_preference: paymentPreference,
        items: onlineCart.payload(),
      };
      if (!body.customer_name || !body.customer_phone) {
        alert('Ad ve telefon gerekli.');
        return;
      }
      const btn = onlineForm.querySelector('button[type="submit"]');
      if (btn) btn.disabled = true;
      try {
        const data = await postJson('/api/orders', body);
        onlineCart.clear();
        window.location.href = api(`/takip?code=${encodeURIComponent(data.order.order_code)}`);
      } catch (err) {
        alert(err.message || 'Hata');
      } finally {
        if (btn) btn.disabled = false;
      }
    });
  }

  if (isPublicLayout) {
    syncOnlineBadgeFromStorage();
  }

  async function submitStaffOrder(form, cart) {
    if (!cart || cart.isEmpty()) {
      alert('Ürün seçin.');
      return;
    }
    const fd = new FormData(form);
    const tableId = Number(fd.get('table_id') || 0);
    const orderId = Number(fd.get('order_id') || 0);
    const note = String(fd.get('customer_note') || '');
    if (!orderId && !tableId) {
      alert('Masa seçin.');
      return;
    }
    const btn = form.querySelector('button[type="submit"]');
    if (btn) btn.disabled = true;
    try {
      const backPath =
        (window.location.pathname || '').replace(base, '') || '/garson';
      const body = {
        table_id: tableId,
        order_id: orderId || undefined,
        customer_note: note,
        items: cart.payload(),
        back: backPath.startsWith('/') ? backPath : `/${backPath}`,
      };
      const data = await postJson('/api/staff/orders', body);
      cart.clear();
      if (data.print_url) {
        window.location.href = data.print_url;
        return;
      }
      if (data.order && data.order.id) {
        window.location.href = api(`/garson/fis/${data.order.id}?autoprint=1`);
        return;
      }
      location.reload();
    } catch (err) {
      alert(err.message || 'Hata');
    } finally {
      if (btn) btn.disabled = false;
    }
  }

  const waiterForm = document.querySelector('[data-waiter-form]');
  if (waiterForm && waiterCart) {
    waiterForm.addEventListener('submit', (e) => {
      e.preventDefault();
      submitStaffOrder(waiterForm, waiterCart);
    });
  }

  const tableOrderForm = document.querySelector('[data-table-order-form]');
  if (tableOrderForm && waiterCart) {
    tableOrderForm.addEventListener('submit', (e) => {
      e.preventDefault();
      submitStaffOrder(tableOrderForm, waiterCart);
    });
  }

  document.querySelectorAll('[data-focus-add]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const orderId = btn.getAttribute('data-focus-add');
      const select = document.querySelector('[data-target-order]');
      const builder = document.querySelector('#order-builder');
      if (select && orderId) select.value = orderId;
      if (builder) builder.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  });

  document.querySelectorAll('[data-pay-order]').forEach((btn) => {
    btn.addEventListener('click', async () => {
      const id = btn.getAttribute('data-pay-order');
      const method = btn.getAttribute('data-method') || 'cash';
      const label = method === 'card' ? 'Kart' : 'Nakit';
      if (!confirm(`${label} ödemesi alınsın mı?`)) return;
      try {
        await postJson(`/api/orders/${id}/pay`, { payment_method: method });
        if (confirm('Ödeme alındı. Satış fişi kesilsin mi?')) {
          window.location.href = api(`/kasa/fatura/siparis/${id}`);
          return;
        }
        location.reload();
      } catch (err) {
        alert(err.message || 'Ödeme alınamadı');
      }
    });
  });

  // Event delegation — canlı yönetici paneli yeniden çizince de çalışır
  document.addEventListener('click', async (event) => {
    const btn = event.target.closest('[data-close-table]');
    if (!btn) return;
    event.preventDefault();
    const id = btn.getAttribute('data-close-table');
    const method = btn.getAttribute('data-method') || 'cash';
    const redirectAttr = btn.getAttribute('data-close-redirect');
    const label = method === 'card' ? 'Kart' : 'Nakit';
    if (!id) return;
    if (!confirm(`Masa ${label} ile kapatılsın mı? Açık siparişler ödenmiş sayılır.`)) return;
    try {
      await postJson(`/api/tables/${id}/close`, { payment_method: method });
      window.location.href = redirectAttr || api('/kasa');
    } catch (err) {
      alert(err.message || 'Masa kapatılamadı');
    }
  });

  document.querySelectorAll('[data-cancel-order]').forEach((btn) => {
    btn.addEventListener('click', async () => {
      const id = btn.getAttribute('data-cancel-order');
      if (!confirm('Sipariş iptal edilsin mi?')) return;
      try {
        await postJson(`/api/orders/${id}/cancel`, {});
        location.reload();
      } catch (err) {
        alert(err.message || 'İptal edilemedi');
      }
    });
  });

  document.querySelectorAll('[data-cancel-item]').forEach((btn) => {
    btn.addEventListener('click', async () => {
      const id = btn.getAttribute('data-cancel-item');
      if (!confirm('Bu ürün iptal edilsin mi?')) return;
      try {
        await postJson(`/api/order-items/${id}/cancel`, {});
        location.reload();
      } catch (err) {
        alert(err.message || 'Ürün iptal edilemedi');
      }
    });
  });

  document.querySelectorAll('[data-item-note-save]').forEach((btn) => {
    btn.addEventListener('click', async () => {
      const id = btn.getAttribute('data-item-note-save');
      const input = document.querySelector(`[data-item-note-input="${id}"]`);
      if (!input) return;
      btn.disabled = true;
      try {
        await postJson(`/api/order-items/${id}/note`, { note: input.value || '' });
        btn.textContent = 'Kaydedildi';
        setTimeout(() => {
          btn.textContent = 'Kaydet';
        }, 1200);
      } catch (err) {
        alert(err.message || 'Not kaydedilemedi');
      } finally {
        btn.disabled = false;
      }
    });
  });

  document.querySelectorAll('[data-status-btn]').forEach((btn) => {
    btn.addEventListener('click', async () => {
      const id = btn.getAttribute('data-order-id');
      const status = btn.getAttribute('data-status-btn');
      try {
        await postJson(`/api/orders/${id}/status`, { status });
        location.reload();
      } catch (err) {
        alert(err.message || 'Güncellenemedi');
      }
    });
  });

  // Mutfak/bar item status — event delegation (live rebuild uyumlu)
  document.addEventListener('click', async (event) => {
    const btn = event.target.closest('[data-item-status]');
    if (!btn) return;
    event.preventDefault();
    if (btn.disabled) return;
    const itemId = btn.getAttribute('data-item-id');
    const status = btn.getAttribute('data-item-status');
    try {
      btn.disabled = true;
      await postJson('/api/station/item-status', {
        item_id: Number(itemId),
        status,
      });
      const board = btn.closest('[data-station-board]');
      if (board && typeof board._refreshStation === 'function') {
        await board._refreshStation(true);
      } else {
        location.reload();
      }
    } catch (err) {
      alert(err.message || 'Güncellenemedi');
      btn.disabled = false;
    }
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
        await postJson(`/api/orders/${id}/note`, { note: input.value || '' });
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

  function moneyTr(n) {
    return (
      Number(n || 0).toLocaleString('tr-TR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      }) + ' ₺'
    );
  }

  function esc(s) {
    return String(s ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  const liveRoot = document.querySelector('[data-live-stats]');
  if (liveRoot) {
    const sourceLabel = { online: 'Online', waiter: 'Garson', cashier: 'Kasa' };
    const statusLabel = {
      pending: 'Bekliyor',
      accepted: 'Alındı',
      preparing: 'Hazırlanıyor',
      ready: 'Hazır',
      served: 'Servis edildi',
      paid: 'Ödendi',
      cancelled: 'İptal',
    };

    async function refreshLive() {
      try {
        const res = await fetch(api('/api/admin/live-stats'));
        const data = await res.json();
        if (!data.ok || !data.live) return;
        const live = data.live;
        const set = (key, val) => {
          liveRoot.querySelectorAll(`[data-stat="${key}"]`).forEach((el) => {
            el.textContent = String(val);
          });
        };
        set('order_count', live.today.order_count);
        set('paid_total', moneyTr(live.today.paid_total));
        set('open_total', moneyTr(live.today.open_total));
        set('open_table_count', (live.open_tables || []).length);
        set('pending_online', live.pending_online);
        set('kitchen_queued', live.kitchen_queued);
        set('bar_queued', live.bar_queued);

        const updated = document.querySelector('[data-live-updated]');
        if (updated) updated.textContent = 'Canlı · ' + (live.updated_at || '');

        const tablesWrap = liveRoot.querySelector('[data-open-tables]');
        if (tablesWrap) {
          const rows = live.open_tables || [];
          const closeRedirect = api('/yonetici');
          tablesWrap.innerHTML = rows.length
            ? `<table><thead><tr><th>Masa</th><th>Sipariş</th><th>Tutar</th><th>Garson</th><th>Masa kapat</th></tr></thead><tbody>${rows
                .map(
                  (t) => `<tr>
                  <td><strong>${esc(t.label)}</strong></td>
                  <td>${Number(t.open_count || 0)}</td>
                  <td>${moneyTr(t.open_total)}</td>
                  <td class="small muted">${esc((t.waiter_names || []).join(', ') || '—')}</td>
                  <td>
                    <div class="cta-row table-close-btns">
                      <button class="btn btn-sm btn-primary" type="button" data-close-table="${Number(t.id)}" data-method="cash" data-close-redirect="${esc(closeRedirect)}">Nakit kapat</button>
                      <button class="btn btn-sm btn-dark" type="button" data-close-table="${Number(t.id)}" data-method="card" data-close-redirect="${esc(closeRedirect)}">Kart kapat</button>
                    </div>
                  </td>
                </tr>`
                )
                .join('')}</tbody></table>`
            : '<p class="muted" style="margin:0">Açık masa yok.</p>';
        }

        const recentWrap = liveRoot.querySelector('[data-recent-orders]');
        if (recentWrap) {
          const rows = live.recent || [];
          recentWrap.innerHTML = rows.length
            ? `<table><thead><tr><th>Kod</th><th>Kaynak</th><th>Durum</th><th>Tutar</th></tr></thead><tbody>${rows
                .map(
                  (o) => `<tr>
                  <td><strong>${esc(o.order_code)}</strong></td>
                  <td>${esc(sourceLabel[o.source] || o.source)}</td>
                  <td>${esc(statusLabel[o.status] || o.status)}</td>
                  <td>${moneyTr(o.total)}</td>
                </tr>`
                )
                .join('')}</tbody></table>`
            : '<p class="muted" style="margin:0">Henüz sipariş yok.</p>';
        }
      } catch (_) {}
    }

    setInterval(refreshLive, 8000);
  }

  // Online order approval (cashier / admin) → mutfak/bar fişi
  document.querySelectorAll('[data-accept-online]').forEach((btn) => {
    btn.addEventListener('click', async () => {
      const id = btn.getAttribute('data-accept-online');
      if (!id) return;
      if (!confirm('Sipariş onaylansın mı? Mutfak ve bar fişi yazdırılacak.')) return;
      btn.disabled = true;
      try {
        const data = await postJson(`/api/online-orders/${id}/accept`, {});
        if (data.print_url) {
          window.location.href = data.print_url;
          return;
        }
        location.reload();
      } catch (err) {
        alert(err.message || 'Onaylanamadı');
        btn.disabled = false;
      }
    });
  });

  // XPrinter mutfak/bar fiş otomatik yazdırma
  const xpSlips = document.querySelector('[data-xp-slips]');
  if (xpSlips) {
    const runPrint = () => {
      window.print();
    };
    const printBtn = document.querySelector('[data-xp-print]');
    if (printBtn) {
      printBtn.addEventListener('click', (e) => {
        e.preventDefault();
        runPrint();
      });
    }
    if (xpSlips.getAttribute('data-autoprint') === '1') {
      const back = xpSlips.getAttribute('data-print-back') || '';
      const statusEl = document.querySelector('[data-autoprint-status]');
      const goBack = () => {
        if (back) {
          window.location.href = back;
        }
      };
      let returned = false;
      const after = () => {
        if (returned) return;
        returned = true;
        if (statusEl) statusEl.textContent = 'Yazdırma tamamlandı, geri dönülüyor…';
        setTimeout(goBack, 400);
      };
      window.addEventListener('afterprint', after);
      setTimeout(() => {
        if (statusEl) statusEl.textContent = 'Yazıcı diyaloğu açıldı…';
        runPrint();
      }, 350);
      // Bazı tarayıcılarda afterprint gelmez — güvenlik zamanlayıcısı
      setTimeout(() => {
        if (!returned && document.visibilityState === 'visible') {
          after();
        }
      }, 120000);
    }
  }

  document.querySelectorAll('[data-reject-online]').forEach((btn) => {
    btn.addEventListener('click', async () => {
      const id = btn.getAttribute('data-reject-online');
      if (!id) return;
      if (!confirm('Online sipariş reddedilsin / iptal edilsin mi?')) return;
      btn.disabled = true;
      try {
        await postJson(`/api/online-orders/${id}/reject`, {});
        location.reload();
      } catch (err) {
        alert(err.message || 'Reddedilemedi');
        btn.disabled = false;
      }
    });
  });

  const onlineBadge = document.querySelector('[data-online-badge]');
  if (onlineBadge) {
    async function refreshOnlineBadge() {
      try {
        const res = await fetch(api('/api/online-orders/pending-count'));
        const data = await res.json();
        if (!data.ok) return;
        const n = Number(data.count || 0);
        onlineBadge.textContent = String(n);
        onlineBadge.hidden = n <= 0;
        onlineBadge.classList.toggle('is-empty', n <= 0);
      } catch (_) {}
    }
    refreshOnlineBadge();
    setInterval(refreshOnlineBadge, 10000);
  }

  const sourceLabelTr = { online: 'Online', waiter: 'Garson', cashier: 'Kasa' };

  // —— Canlı mutfak / bar ——
  document.querySelectorAll('[data-station-board]').forEach((board) => {
    const station = board.getAttribute('data-station') || 'kitchen';
    let version = board.getAttribute('data-live-version') || '';
    const setUpdated = (t) => {
      document.querySelectorAll('[data-live-updated]').forEach((el) => {
        el.textContent = 'Canlı · ' + (t || '');
      });
    };
    const renderRows = (rows) => {
      if (!rows.length) {
        board.innerHTML = '<div class="panel muted" data-station-empty>Bekleyen ürün yok.</div>';
        return;
      }
      board.innerHTML = rows
        .map((row) => {
          const noteOrder = row.customer_note
            ? `<p><strong>Sipariş notu:</strong> ${esc(row.customer_note)}</p>`
            : '';
          const noteItem = row.note ? `<p>${esc(row.note)}</p>` : '';
          let actions = '';
          if (row.status === 'queued') {
            actions += `<button class="btn btn-primary btn-sm" type="button" data-item-id="${Number(row.id)}" data-item-status="preparing">Hazırla</button>`;
          }
          if (row.status === 'queued' || row.status === 'preparing') {
            actions += `<button class="btn btn-ghost btn-sm" type="button" data-item-id="${Number(row.id)}" data-item-status="ready">Hazır</button>`;
          }
          return `<article class="ticket">
            <h3>${Number(row.quantity)}× ${esc(row.item_name)}</h3>
            <p class="muted small">${esc(row.order_code)} · ${esc(row.table_label || 'Online')} · ${esc(sourceLabelTr[row.source] || row.source)}</p>
            ${noteOrder}${noteItem}
            <div class="cta-row" style="margin-top:12px">${actions}</div>
          </article>`;
        })
        .join('');
    };
    async function refreshStation(force) {
      try {
        const res = await fetch(api(`/api/station/${station}`));
        const data = await res.json();
        if (!data.ok) return;
        setUpdated(data.updated_at);
        if (!force && data.version && data.version === version) return;
        version = data.version || version;
        board.setAttribute('data-live-version', version);
        renderRows(data.rows || []);
      } catch (_) {}
    }
    board._refreshStation = refreshStation;
    board.setAttribute('data-live-bound', '1');
    setInterval(() => refreshStation(false), 5000);
    document.querySelectorAll('[data-live-refresh]').forEach((btn) => {
      btn.addEventListener('click', () => refreshStation(true));
    });
  });

  // —— Canlı masa panoları ——
  document.querySelectorAll('[data-tables-board]').forEach((board) => {
    const scope = board.getAttribute('data-tables-scope') || 'active';
    const linkBase = board.getAttribute('data-tables-link') || '/kasa/masa';
    const redirect = board.getAttribute('data-tables-redirect') || '';
    const canClose = board.getAttribute('data-can-close') === '1';
    const adminMode = board.getAttribute('data-admin-mode') === '1';
    let version = board.getAttribute('data-live-version') || '';
    const setUpdated = (t) => {
      document.querySelectorAll('[data-live-updated]').forEach((el) => {
        el.textContent = 'Canlı · ' + (t || '');
      });
    };
    const moneyLocal = (n) =>
      new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(Number(n || 0));

    const renderTables = (tables) => {
      const emptyHint = document.querySelector('[data-tables-empty]');
      if (emptyHint) emptyHint.hidden = tables.length > 0;
      if (!tables.length && scope === 'open') {
        board.innerHTML = '';
        return;
      }
      board.innerHTML = tables
        .map((t) => {
          const isOpen = !!t.is_open;
          const active = Number(t.is_active) !== 0;
          const cls = `${isOpen ? 'is-open' : 'is-free'}${!active ? ' is-inactive' : ''}`;
          const chip = !active ? 'Pasif' : isOpen ? 'Açık' : 'Boş';
          const meta = isOpen
            ? `<div class="table-tile-meta"><span>${Number(t.open_count)} sipariş</span><strong class="price">${moneyLocal(t.open_total)}</strong></div>`
            : scope === 'active'
              ? '<div class="muted small">Sipariş aç / tahsilat</div>'
              : '';
          const waiters =
            t.waiter_names && t.waiter_names.length
              ? `<div class="muted small">${esc(t.waiter_names.join(', '))}</div>`
              : '';
          const opener = t.opened_by_name
            ? `<div class="muted small">Açan: ${esc(t.opened_by_name)}</div>`
            : '';
          const closeBtns =
            canClose && isOpen && active
              ? `<div class="cta-row table-close-btns" style="margin-top:10px">
                  <button class="btn btn-sm btn-primary" type="button" data-close-table="${Number(t.id)}" data-method="cash" data-close-redirect="${esc(redirect)}">Nakit kapat</button>
                  <button class="btn btn-sm btn-dark" type="button" data-close-table="${Number(t.id)}" data-method="card" data-close-redirect="${esc(redirect)}">Kart kapat</button>
                </div>`
              : '';
          if (adminMode) {
            return `<article class="table-tile ${cls}">
              <div class="table-tile-top"><strong>${esc(t.label)}</strong><span class="chip ${isOpen ? 'kitchen' : ''}">${chip}</span></div>
              <div class="table-tile-code muted small">${esc(t.code)} · ${Number(t.seats)} kişi</div>
              ${opener}${meta}${waiters}
              <div class="cta-row" style="margin-top:12px">
                <a class="btn btn-primary btn-sm" href="${api('/yonetici/masalar/' + Number(t.id))}">Düzenle</a>
                ${
                  active
                    ? `<a class="btn btn-dark btn-sm" href="${api('/kasa/masa/' + Number(t.id))}">Kasa</a>
                       <a class="btn btn-ghost btn-sm" href="${api('/garson/masa/' + Number(t.id))}">Garson</a>`
                    : ''
                }
              </div>
              ${closeBtns}
            </article>`;
          }
          return `<article class="table-tile ${cls}">
            <a class="table-tile-link" href="${api(linkBase + '/' + Number(t.id))}">
              <div class="table-tile-top"><strong>${esc(t.label)}</strong><span class="chip ${isOpen ? 'kitchen' : ''}">${chip}</span></div>
              <div class="table-tile-code muted small">${esc(t.code)}${t.seats ? ' · ' + Number(t.seats) + ' kişi' : ''}</div>
              ${opener}${meta}${waiters}
            </a>
            ${closeBtns}
          </article>`;
        })
        .join('');

      const openCount = tables.filter((t) => t.is_open).length;
      document.querySelectorAll('[data-stat-open-tables]').forEach((el) => {
        el.textContent = String(openCount);
      });
      document.querySelectorAll('[data-stat-total-tables]').forEach((el) => {
        el.textContent = String(tables.length);
      });
    };

    async function refreshTables(force) {
      try {
        const res = await fetch(api(`/api/tables/overview?scope=${encodeURIComponent(scope)}`));
        const data = await res.json();
        if (!data.ok) return;
        setUpdated(data.updated_at);
        if (!force && data.version && data.version === version) return;
        version = data.version || version;
        board.setAttribute('data-live-version', version);
        renderTables(data.tables || []);
      } catch (_) {}
    }
    board._refreshTables = refreshTables;
    setInterval(() => refreshTables(false), 5000);
  });

  // —— WhatsApp yeni sipariş bildirimi ——
  const waSeenKey = 'chicken_wa_seen_order';
  async function pollWhatsAppPending() {
    try {
      const res = await fetch(api('/api/whatsapp/pending'));
      const data = await res.json();
      if (!data.ok || !data.enabled || !data.pending) return;
      const pending = data.pending;
      const id = Number(pending.order_id || 0);
      if (!id || !pending.url) return;
      const seen = Number(localStorage.getItem(waSeenKey) || 0);
      if (id <= seen) return;
      localStorage.setItem(waSeenKey, String(id));
      if (pending.auto_open) {
        window.open(pending.url, '_blank', 'noopener');
      }
    } catch (_) {}
  }
  if (document.querySelector('[data-online-badge], [data-online-pending-section], [data-live-stats]')) {
    pollWhatsAppPending();
    setInterval(pollWhatsAppPending, 8000);
  }
})();
