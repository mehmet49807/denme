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
  <p class="muted">Üye olmadan sipariş verebilirsiniz. Ürünleri seçin, bilgilerinizi girin, sipariş kodunuzla takip edin.</p>
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
      Üye olmadan sipariş verebilirsiniz.
      Üye olursanız <strong><?= e($welcomeCode) ?></strong> ile %10 indirim.
      <a href="<?= e(url('/uye-ol')) ?>">Üye ol</a>
      ·
      <a href="<?= e(url('/giris')) ?>">Giriş</a>
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

    <aside class="panel cart" id="sepet" data-online-cart data-welcome-percent="10" data-cart-persist="chicken_online_cart">
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
        <fieldset class="payment-fieldset">
          <legend>Kapıda ödeme</legend>
          <p class="muted small" style="margin:0 0 10px">Teslimatta nasıl ödeyeceğinizi seçin.</p>
          <div class="payment-options">
            <label class="payment-option">
              <input type="radio" name="payment_preference" value="cash" required checked>
              <span>
                <strong>Nakit</strong>
                <small>Kapıda nakit ödeme</small>
              </span>
            </label>
            <label class="payment-option">
              <input type="radio" name="payment_preference" value="card" required>
              <span>
                <strong>Kart</strong>
                <small>Kapıda kart ile ödeme</small>
              </span>
            </label>
          </div>
        </fieldset>
        <label>Sipariş notu
          <textarea name="customer_note" placeholder="Sipariş altına not yazın..."></textarea>
        </label>
        <button class="btn btn-primary" type="submit">Siparişi Gönder</button>
      </form>
      <p class="cart-member-note">
        Üye olan müşteriler <strong>YENI10</strong> kodu ile <strong>%10 indirim</strong> kazanır.
        <?php if (!$customer): ?>
          <a href="<?= e(url('/uye-ol')) ?>">Üye ol</a>
        <?php endif; ?>
      </p>
    </aside>
  </div>
</div>
