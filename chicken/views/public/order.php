<?php
/** @var array $categories */
/** @var array $items */
/** @var array|null $customer */
/** @var string $welcomeCode */
/** @var list<array> $deliveryZones */
/** @var float|int|string $minTotal */
/** @var int $etaMinutes */
$customer = $customer ?? null;
$welcomeCode = $welcomeCode ?? 'YENI10';
$deliveryZones = $deliveryZones ?? [];
$minTotal = (float) ($minTotal ?? 0);
$etaMinutes = (int) ($etaMinutes ?? 35);
?>
<div class="page-shell">
  <p class="eyebrow">Online sipariş</p>
  <h1 class="page-title">Siparişini oluştur</h1>
  <p class="muted">Üye olmadan sipariş verebilirsiniz. Ürünleri seçin, bilgilerinizi girin, sipariş kodunuzla takip edin.</p>
  <p class="small muted" style="margin-top:6px">Fiyatlarımız KDV dahildir (%10 restoran yeme-içme hizmeti). Tahmini hazırlık ~<?= $etaMinutes ?> dk<?php if ($minTotal > 0): ?> · Min. sepet <?= e(money($minTotal)) ?><?php endif; ?>.</p>
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
        <?php if ($deliveryZones): ?>
          <label>Teslimat bölgesi
            <select name="delivery_zone" required data-delivery-zone>
              <option value="">Seçin</option>
              <?php foreach ($deliveryZones as $z): ?>
                <option
                  value="<?= e((string) $z['name']) ?>"
                  data-min="<?= e((string) ($z['min_total'] ?? 0)) ?>"
                  data-fee="<?= e((string) ($z['fee'] ?? 0)) ?>"
                >
                  <?= e((string) $z['name']) ?>
                  <?php if ((float) ($z['fee'] ?? 0) > 0): ?> (+<?= e(money((float) $z['fee'])) ?>)<?php endif; ?>
                  <?php if ((float) ($z['min_total'] ?? 0) > 0): ?> · min <?= e(money((float) $z['min_total'])) ?><?php endif; ?>
                </option>
              <?php endforeach; ?>
            </select>
          </label>
          <label>Teslimat adresi
            <textarea name="delivery_address" rows="2" required placeholder="Mahalle, sokak, kapı no"></textarea>
          </label>
        <?php endif; ?>
        <label>Sipariş notu
          <textarea name="customer_note" placeholder="Sipariş altına not yazın..."></textarea>
        </label>
        <p class="muted small" data-online-min-hint style="margin:0">
          Tahmini hazırlık ~<?= $etaMinutes ?> dk
          <?php if ($minTotal > 0): ?> · Min. sepet <?= e(money($minTotal)) ?><?php endif; ?>
        </p>
        <button class="btn btn-primary" type="submit">Siparişi Gönder</button>
      </form>
      <script>
        window.CrispOnline = {
          minTotal: <?= json_encode($minTotal) ?>,
          etaMinutes: <?= (int) $etaMinutes ?>,
          deliveryZones: <?= json_encode($deliveryZones, JSON_UNESCAPED_UNICODE) ?>
        };
      </script>
      <p class="cart-member-note">
        Üye olan müşteriler <strong>YENI10</strong> kodu ile <strong>%10 indirim</strong> kazanır.
        <?php if (!$customer): ?>
          <a href="<?= e(url('/uye-ol')) ?>">Üye ol</a>
        <?php endif; ?>
      </p>
    </aside>
  </div>
</div>
