<?php
/** @var array $categories */
/** @var array $items */
/** @var array|null $customer */
/** @var string $welcomeCode */
$customer = $customer ?? null;
$welcomeCode = $welcomeCode ?? 'YENI10';
?>
<div class="page-shell">
  <p class="eyebrow">Online sipariş</p>
  <h1 class="page-title">Siparişini oluştur</h1>
  <p class="muted">Ürünleri seç, bilgilerini gir, sipariş kodunla takip et.</p>
  <?php if ($msg = flash('success')): ?>
    <div class="alert alert-ok" style="margin-top:14px"><?= e($msg) ?></div>
  <?php endif; ?>
  <?php if ($customer && empty($customer['welcome_discount_used'])): ?>
    <div class="promo-banner" style="margin-top:14px">
      Hoş geldin indirimin hazır — sepete kod yaz:
      <strong><?= e($welcomeCode) ?></strong> (%10)
    </div>
  <?php elseif (!$customer): ?>
    <div class="promo-banner" style="margin-top:14px">
      Yeni üyelere %10 indirim.
      <a href="<?= e(url('/uye-ol')) ?>">Üye ol</a>
      veya
      <a href="<?= e(url('/giris')) ?>">giriş yap</a>
      · Kod: <strong><?= e($welcomeCode) ?></strong>
    </div>
  <?php endif; ?>

  <div class="tabs" style="margin-top:22px">
    <button class="tab active" type="button" data-cat-tab="all">Tümü</button>
    <?php foreach ($categories as $cat): ?>
      <button class="tab" type="button" data-cat-tab="<?= e($cat['slug']) ?>"><?= e($cat['name']) ?></button>
    <?php endforeach; ?>
  </div>

  <div class="order-builder">
    <div class="menu-grid">
      <?php foreach ($items as $item): ?>
        <?php partial('partials/menu_item_card', ['item' => $item, 'showDescription' => true, 'showAdd' => true]); ?>
      <?php endforeach; ?>
    </div>

    <aside class="panel cart" data-online-cart data-welcome-percent="10">
      <div class="meta-row">
        <h2 style="margin:0;font-family:var(--font-display)">Sepet</h2>
        <span class="chip" data-cart-count>0</span>
      </div>
      <div data-cart-list style="margin:14px 0"></div>
      <div class="meta-row" style="margin-bottom:6px">
        <span class="muted">Ara toplam</span>
        <strong data-cart-subtotal>0,00 ₺</strong>
      </div>
      <div class="meta-row" style="margin-bottom:6px" data-cart-discount-row hidden>
        <span class="muted">İndirim</span>
        <strong data-cart-discount>−0,00 ₺</strong>
      </div>
      <div class="meta-row" style="margin-bottom:14px">
        <span class="muted">Toplam</span>
        <strong data-cart-total>0,00 ₺</strong>
      </div>
      <form class="stack" data-online-form>
        <label>İndirim kodu
          <input name="discount_code" data-discount-code placeholder="Örn. YENI10" autocomplete="off"
            value="<?= $customer && empty($customer['welcome_discount_used']) ? e($welcomeCode) : '' ?>">
        </label>
        <label>Ad Soyad
          <input name="customer_name" required placeholder="Adınız"
            value="<?= e((string) ($customer['name'] ?? '')) ?>">
        </label>
        <label>Telefon
          <input name="customer_phone" required placeholder="05xx..."
            value="<?= e((string) ($customer['phone'] ?? '')) ?>">
        </label>
        <label>Sipariş notu
          <textarea name="customer_note" placeholder="Sipariş altına not yazın..."></textarea>
        </label>
        <button class="btn btn-primary" type="submit">Siparişi Gönder</button>
      </form>
    </aside>
  </div>
</div>
