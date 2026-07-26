<div class="panel-head">
  <div>
    <p class="eyebrow">QR menü</p>
    <h1>Masa QR kodları</h1>
  </div>
</div>
<p class="muted" style="margin-top:-8px">Her masa için özel menü bağlantısı. Yazdırıp masaya yapıştırabilirsiniz.</p>

<div class="qr-grid" style="margin-top:20px">
  <div class="qr-card">
    <strong>Genel Menü</strong>
    <img alt="QR menü" src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=<?= urlencode($menuUrl) ?>">
    <div class="small muted"><?= e($menuUrl) ?></div>
  </div>
  <?php foreach ($tables as $table): ?>
    <?php $tableUrl = $base . '/menu?t=' . urlencode($table['qr_token']); ?>
    <div class="qr-card">
      <strong><?= e($table['label']) ?></strong>
      <img alt="QR <?= e($table['label']) ?>" src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=<?= urlencode($tableUrl) ?>">
      <div class="small muted"><?= e($table['code']) ?> · <?= (int) $table['seats'] ?> kişilik</div>
    </div>
  <?php endforeach; ?>
</div>
