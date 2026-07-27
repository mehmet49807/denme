<?php
/** @var array $live */
$live = $live ?? [];
$today = $live['today'] ?? [];
$openTables = $live['open_tables'] ?? [];
$pendingOnline = (int) ($live['pending_online'] ?? 0);
$kitchenQueued = (int) ($live['kitchen_queued'] ?? 0);
$barQueued = (int) ($live['bar_queued'] ?? 0);
$recent = $live['recent'] ?? [];
?>
<div class="panel-head">
  <div>
    <p class="eyebrow">Yönetici</p>
    <h1>Restoran kontrol</h1>
  </div>
  <div class="cta-row">
    <span class="chip live-chip" data-live-updated>Canlı</span>
    <a class="btn btn-accent btn-sm" href="<?= e(url('/online-siparisler')) ?>">Online siparişler</a>
    <a class="btn btn-ghost btn-sm" href="<?= e(url('/qr')) ?>">QR Menü</a>
    <a class="btn btn-ghost btn-sm" href="<?= e(url('/yonetici/brosurler')) ?>">Broşürler</a>
  </div>
</div>

<section class="panel qr-embed" style="margin-bottom:18px">
  <div class="meta-row" style="margin-bottom:12px">
    <h2 class="live-title">QR Menü</h2>
    <a class="btn btn-ghost btn-sm" href="<?= e(url('/yonetici/brosurler')) ?>">Tema düzenle</a>
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
      <p class="muted" style="margin:0 0 10px">Müşteri okutunca seçili broşür teması + güncel menü açılır.</p>
      <div class="cta-row">
        <a class="btn btn-accent btn-sm" href="<?= e(url('/qr')) ?>">QR sayfası</a>
        <a class="btn btn-ghost btn-sm" href="<?= e(url('/menu/brosur')) ?>" target="_blank" rel="noopener">Broşürü aç</a>
      </div>
    </div>
  </div>
</section>

<div class="live-stats" data-live-stats>
  <div class="stats">
    <div class="stat">
      <span class="muted">Bugün sipariş</span>
      <strong data-stat="order_count"><?= (int) ($today['order_count'] ?? 0) ?></strong>
    </div>
    <div class="stat">
      <span class="muted">Bugün tahsilat</span>
      <strong data-stat="paid_total"><?= e(money((float) ($today['paid_total'] ?? 0))) ?></strong>
    </div>
    <div class="stat">
      <span class="muted">Açık tutar</span>
      <strong data-stat="open_total"><?= e(money((float) ($today['open_total'] ?? 0))) ?></strong>
    </div>
    <div class="stat">
      <span class="muted">Açık masa</span>
      <strong data-stat="open_table_count"><?= count($openTables) ?></strong>
    </div>
    <div class="stat">
      <span class="muted">Online bekleyen</span>
      <strong data-stat="pending_online"><?= $pendingOnline ?></strong>
    </div>
    <div class="stat">
      <span class="muted">Mutfak / Bar sırada</span>
      <strong><span data-stat="kitchen_queued"><?= $kitchenQueued ?></span> / <span data-stat="bar_queued"><?= $barQueued ?></span></strong>
    </div>
  </div>

  <div class="live-grid">
    <section class="panel">
      <div class="meta-row" style="margin-bottom:12px">
        <h2 class="live-title">Açık masalar</h2>
        <a class="btn btn-ghost btn-sm" href="<?= e(url('/yonetici/masalar')) ?>">Masalar</a>
      </div>
      <div class="table-wrap" data-open-tables>
        <?php if (!$openTables): ?>
          <p class="muted" style="margin:0">Açık masa yok.</p>
        <?php else: ?>
          <table>
            <thead>
              <tr>
                <th>Masa</th>
                <th>Sipariş</th>
                <th>Tutar</th>
                <th>Garson</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($openTables as $t): ?>
                <tr>
                  <td><strong><?= e((string) ($t['label'] ?? '')) ?></strong></td>
                  <td><?= (int) ($t['open_count'] ?? 0) ?></td>
                  <td><?= e(money((float) ($t['open_total'] ?? 0))) ?></td>
                  <td class="small muted"><?= e(implode(', ', $t['waiter_names'] ?? []) ?: '—') ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </section>

    <section class="panel">
      <div class="meta-row" style="margin-bottom:12px">
        <h2 class="live-title">Son siparişler</h2>
        <a class="btn btn-ghost btn-sm" href="<?= e(url('/yonetici/siparisler')) ?>">Tümü</a>
      </div>
      <div class="table-wrap" data-recent-orders>
        <?php if (!$recent): ?>
          <p class="muted" style="margin:0">Henüz sipariş yok.</p>
        <?php else: ?>
          <table>
            <thead>
              <tr>
                <th>Kod</th>
                <th>Kaynak</th>
                <th>Durum</th>
                <th>Tutar</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recent as $o): ?>
                <tr>
                  <td><strong><?= e((string) $o['order_code']) ?></strong></td>
                  <td><?= e(source_label((string) $o['source'])) ?></td>
                  <td><?= e(status_label((string) $o['status'])) ?></td>
                  <td><?= e(money((float) $o['total'])) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </section>
  </div>
</div>

<button class="btn btn-primary admin-open-menu" type="button" data-nav-toggle style="margin-top:18px">
  <span class="menu-icon" aria-hidden="true">☰</span>
  Menüyü aç
</button>
