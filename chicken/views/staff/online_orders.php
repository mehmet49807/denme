<?php
/** @var list<array> $pending */
/** @var list<array> $active */
$pending = $pending ?? [];
$active = $active ?? [];
?>
<div class="panel-head">
  <div>
    <p class="eyebrow">Online sipariş</p>
    <h1>Online Siparişler</h1>
  </div>
  <div class="cta-row">
    <span class="live-chip" data-live-updated>Canlı</span>
    <span class="chip <?= $pending ? 'kitchen' : '' ?>" data-pending-count><?= count($pending) ?> bekleyen</span>
    <button class="btn btn-ghost btn-sm" type="button" onclick="location.reload()">Yenile</button>
  </div>
</div>

<p class="muted" style="margin-top:-8px">
  Onayladığınız sipariş mutfak ve bar fişine düşer. Onaylanmadan istasyonlara gitmez.
  <?php if (!empty($whatsappEnabled)): ?>
    WhatsApp bildirimi açık — yeni siparişte otomatik hatırlatma gelir.
  <?php endif; ?>
</p>

<section class="panel" style="margin-top:18px" data-online-pending-section>
  <h2 class="live-title" style="margin:0 0 14px">Onay bekleyen</h2>
  <?php if (!$pending): ?>
    <p class="muted" style="margin:0">Bekleyen online sipariş yok.</p>
  <?php else: ?>
    <div class="order-card-list" data-online-pending-list>
      <?php foreach ($pending as $order): ?>
        <article class="order-card online-pending-card" data-order-id="<?= (int) $order['id'] ?>">
          <div class="order-card-head">
            <div>
              <div class="order-code"><?= e((string) $order['order_code']) ?></div>
              <div class="small muted">
                <?= e((string) ($order['customer_name'] ?? '—')) ?>
                · <?= e((string) ($order['customer_phone'] ?? '')) ?>
                · <?= e((string) ($order['created_at'] ?? '')) ?>
              </div>
            </div>
            <strong class="price"><?= e(money((float) $order['total'])) ?></strong>
          </div>
          <?php if (!empty($order['discount_amount']) && (float) $order['discount_amount'] > 0): ?>
            <div class="small muted" style="margin-bottom:8px">
              İndirim <?= e((string) ($order['discount_code'] ?? '')) ?>:
              −<?= e(money((float) $order['discount_amount'])) ?>
            </div>
          <?php endif; ?>
          <?php if (!empty($order['payment_preference'])): ?>
            <div class="small" style="margin-bottom:8px">
              <strong>Kapıda ödeme:</strong>
              <?= e(payment_preference_label((string) $order['payment_preference'])) ?>
            </div>
          <?php endif; ?>
          <?php if (!empty($order['customer_note'])): ?>
            <p style="margin:0 0 10px"><strong>Not:</strong> <?= e((string) $order['customer_note']) ?></p>
          <?php endif; ?>
          <ul class="online-item-list">
            <?php foreach (($order['active_items'] ?? $order['items'] ?? []) as $line): ?>
              <?php if (($line['status'] ?? '') === 'cancelled') {
                  continue;
              } ?>
              <li>
                <span><?= (int) $line['quantity'] ?>× <?= e((string) $line['item_name']) ?></span>
                <span class="chip <?= e((string) $line['station']) ?>"><?= e(station_label((string) $line['station'])) ?></span>
              </li>
            <?php endforeach; ?>
          </ul>
          <div class="cta-row" style="margin-top:12px">
            <button class="btn btn-accent btn-sm" type="button" data-accept-online="<?= (int) $order['id'] ?>">Onayla · Fiş yazdır</button>
            <button class="btn btn-dark btn-sm" type="button" data-reject-online="<?= (int) $order['id'] ?>">Reddet</button>
            <?php if (!empty($order['whatsapp_url'])): ?>
              <a class="btn btn-ghost btn-sm" href="<?= e((string) $order['whatsapp_url']) ?>" target="_blank" rel="noopener">WhatsApp</a>
            <?php endif; ?>
            <a class="btn btn-ghost btn-sm" href="<?= e(url('/garson/fis/' . (int) $order['id'])) ?>">Fiş önizle</a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<section class="panel" style="margin-top:18px">
  <h2 class="live-title" style="margin:0 0 14px">Hazırlanan / açık online</h2>
  <?php if (!$active): ?>
    <p class="muted" style="margin:0">Onaylanmış açık online sipariş yok.</p>
  <?php else: ?>
    <div class="order-card-list">
      <?php foreach ($active as $order): ?>
        <article class="order-card">
          <div class="order-card-head">
            <div>
              <div class="order-code"><?= e((string) $order['order_code']) ?></div>
              <div class="small muted">
                <?= e((string) ($order['customer_name'] ?? '—')) ?>
                · <?= e(status_label((string) $order['status'])) ?>
              </div>
            </div>
            <strong class="price"><?= e(money((float) $order['total'])) ?></strong>
          </div>
          <div class="cta-row">
            <?php if (Auth::role() === 'cashier' || Auth::role() === 'admin'): ?>
              <button class="btn btn-primary btn-sm" type="button" data-pay-order="<?= (int) $order['id'] ?>" data-method="cash">Nakit</button>
              <button class="btn btn-dark btn-sm" type="button" data-pay-order="<?= (int) $order['id'] ?>" data-method="card">Kart</button>
            <?php endif; ?>
            <a class="btn btn-ghost btn-sm" href="<?= e(url('/garson/fis/' . (int) $order['id'])) ?>">Fiş</a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
