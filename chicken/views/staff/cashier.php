<?php
/** @var array $tables */
/** @var array $orders */
/** @var array $onlineOpen */
$openTables = count(array_filter($tables, static fn(array $t): bool => !empty($t['is_open'])));
$paidSum = array_sum(array_map(
    static fn(array $o): float => $o['status'] === 'paid' ? (float) $o['total'] : 0.0,
    $orders
));
?>
<div class="panel-head">
  <div>
    <p class="eyebrow">Kasa alanı</p>
    <h1>Masalar</h1>
  </div>
  <div class="cta-row">
    <a class="btn btn-accent btn-sm" href="<?= e(url('/online-siparisler')) ?>">
      Online siparişler
      <?php if (!empty($pendingOnlineCount)): ?>
        <span class="nav-badge" style="margin-left:6px"><?= (int) $pendingOnlineCount ?></span>
      <?php endif; ?>
    </a>
    <a class="btn btn-ghost btn-sm" href="<?= e(url('/qr')) ?>">QR Menü</a>
  </div>
</div>

<section class="panel qr-embed" style="margin-bottom:18px">
  <div class="meta-row" style="margin-bottom:12px">
    <h2 class="live-title" style="font-family:var(--font-display);font-size:1.15rem;margin:0">QR Menü</h2>
    <span class="chip">Görüntüleme</span>
  </div>
  <div class="qr-embed-row">
    <img
      class="qr-single-img"
      src="<?= e(BrochureService::qrImageUrl(null, 160)) ?>"
      alt="QR Menü"
      width="140"
      height="140"
    >
    <div>
      <p class="muted" style="margin:0 0 10px">Yalnızca görüntüleme — yazdırıp kullanabilirsiniz. Tema düzenlemesi yöneticide.</p>
      <div class="cta-row">
        <a class="btn btn-accent btn-sm" href="<?= e(url('/qr')) ?>">QR’ı aç</a>
        <a class="btn btn-ghost btn-sm" href="<?= e(url('/menu/brosur')) ?>" target="_blank" rel="noopener">Broşür</a>
      </div>
    </div>
  </div>
</section>

<div class="stats">
  <div class="stat"><span class="muted">Açık masa</span><strong><?= (int) $openTables ?></strong></div>
  <div class="stat"><span class="muted">Toplam masa</span><strong><?= count($tables) ?></strong></div>
  <div class="stat"><span class="muted">Online açık</span><strong><?= count($onlineOpen) ?></strong></div>
  <div class="stat"><span class="muted">Bugün tahsilat</span><strong><?= e(money($paidSum)) ?></strong></div>
</div>

<div class="table-board table-board-3d">
  <?php foreach ($tables as $table): ?>
    <?php
      partial('partials/table_3d', [
          'table' => $table,
          'href' => url('/kasa/masa/' . (int) $table['id']),
      ]);
    ?>
  <?php endforeach; ?>
</div>

<?php if ($onlineOpen): ?>
  <div class="panel" style="margin-top:24px">
    <h2 style="font-family:var(--font-display);margin:0 0 12px">Online açık siparişler</h2>
    <div class="order-card-list">
      <?php foreach ($onlineOpen as $order): ?>
        <article class="order-card">
          <div class="order-card-head">
            <div>
              <div class="order-code"><?= e($order['order_code']) ?></div>
              <div class="small muted">
                <?= e($order['customer_name'] ?? '—') ?>
                · <?= e(status_label($order['status'])) ?>
                <?php if (!empty($order['payment_preference'])): ?>
                  · <?= e(payment_preference_label((string) $order['payment_preference'])) ?>
                <?php endif; ?>
              </div>
            </div>
            <strong class="price"><?= e(money((float) $order['total'])) ?></strong>
          </div>
          <div class="cta-row">
            <button class="btn btn-primary btn-sm" type="button" data-pay-order="<?= (int) $order['id'] ?>" data-method="cash">Nakit</button>
            <button class="btn btn-dark btn-sm" type="button" data-pay-order="<?= (int) $order['id'] ?>" data-method="card">Kart</button>
            <button class="btn btn-ghost btn-sm" type="button" data-cancel-order="<?= (int) $order['id'] ?>">İptal</button>
            <a class="btn btn-ghost btn-sm" href="<?= e(url('/garson/fis/' . (int) $order['id'])) ?>">Fiş</a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
<?php endif; ?>
