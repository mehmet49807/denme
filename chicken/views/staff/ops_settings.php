<?php
/** @var array $qz */
/** @var array $zones */
/** @var list<array> $loginLogs */
/** @var list<array> $stockAlerts */
$qz = $qz ?? [];
$zones = $zones ?? [];
$zonesText = '';
foreach ($zones as $z) {
    $zonesText .= ($z['name'] ?? '') . '|' . (float) ($z['min_total'] ?? 0) . '|' . (float) ($z['fee'] ?? 0) . "\n";
}
?>
<div class="panel-head">
  <div>
    <p class="eyebrow">Yönetici · Operasyon</p>
    <h1>Operasyon ayarları</h1>
  </div>
</div>

<section class="panel" style="max-width:720px;margin-bottom:18px">
  <form method="post" action="<?= e(url('/yonetici/operasyon')) ?>" class="stack">
    <?= csrf_field() ?>
    <p class="eyebrow" style="margin:0">Yazıcı / istasyon</p>
    <label style="display:flex;gap:10px;align-items:flex-start">
      <input type="checkbox" name="qz_enabled" value="1" <?= !empty($qz['enabled']) ? 'checked' : '' ?> style="margin-top:4px">
      <span>QZ Tray ile sessiz yazdır (mutfak/bar ayrı yazıcı)</span>
    </label>
    <label>Mutfak yazıcı adı (QZ)
      <input name="qz_printer_kitchen" value="<?= e((string) ($qz['printer_kitchen'] ?? '')) ?>" placeholder="Kitchen Printer">
    </label>
    <label>Bar yazıcı adı (QZ)
      <input name="qz_printer_bar" value="<?= e((string) ($qz['printer_bar'] ?? '')) ?>" placeholder="Bar Printer">
    </label>
    <label>Bekleme uyarısı (dk)
      <input type="number" min="5" max="120" name="station_wait_alert_minutes" value="<?= (int) ($waitAlert ?? 15) ?>">
    </label>
    <label>Fiş geçmişi adedi
      <input type="number" min="5" max="100" name="slip_history_limit" value="<?= (int) ($historyLimit ?? 30) ?>">
    </label>

    <hr style="border:0;border-top:1px solid var(--line)">
    <p class="eyebrow" style="margin:0">Çok şube</p>
    <label>Bu POS şubesi <span class="muted small">(menü fiyatları bu şubeye göre)</span>
      <select name="pos_branch_id">
        <option value="0">Varsayılan fiyat (şube yok)</option>
        <?php foreach (($branches ?? []) as $b): ?>
          <option value="<?= (int) $b['id'] ?>" <?= (int) ($posBranchId ?? 0) === (int) $b['id'] ? 'selected' : '' ?>>
            <?= e((string) $b['name']) ?><?= !empty($b['city']) ? ' · ' . e((string) $b['city']) : '' ?>
          </option>
        <?php endforeach; ?>
      </select>
    </label>
    <p class="muted small" style="margin:0">Şube fiyatlarını ürün düzenleme ekranından girin.</p>

    <hr style="border:0;border-top:1px solid var(--line)">
    <p class="eyebrow" style="margin:0">Online / teslimat</p>
    <label>Tahmini hazırlık süresi (dk)
      <input type="number" min="10" max="180" name="online_eta_minutes" value="<?= (int) ($eta ?? 35) ?>">
    </label>
    <label>Minimum sepet (₺)
      <input type="number" min="0" step="0.01" name="online_min_total" value="<?= e((string) ($minTotal ?? '0')) ?>">
    </label>
    <label>Teslimat bölgeleri <span class="muted small">(satır: Bölge|MinTutar|Ücret)</span>
      <textarea name="delivery_zones" rows="4" placeholder="Lara|250|30&#10;Konyaaltı|300|40"><?= e(trim($zonesText)) ?></textarea>
    </label>
    <label style="display:flex;gap:10px;align-items:flex-start">
      <input type="checkbox" name="whatsapp_customer_status" value="1" <?= !empty($waCustomer) ? 'checked' : '' ?> style="margin-top:4px">
      <span>Online sipariş durumu müşteriye WhatsApp (Cloud API)</span>
    </label>

    <button class="btn btn-primary" type="submit">Kaydet</button>
  </form>
</section>

<?php if ($stockAlerts): ?>
<section class="panel" style="margin-bottom:18px">
  <h2 style="margin:0 0 12px;font-family:var(--font-display)">Stok uyarıları</h2>
  <ul class="station-item-list">
    <?php foreach ($stockAlerts as $row): ?>
      <li>
        <strong><?= e((string) $row['name']) ?></strong>
        <span class="muted small"> — stok <?= e((string) $row['stock_qty']) ?> / eşik <?= e((string) $row['stock_alert_qty']) ?></span>
      </li>
    <?php endforeach; ?>
  </ul>
</section>
<?php endif; ?>

<section class="panel">
  <h2 style="margin:0 0 12px;font-family:var(--font-display)">Giriş / çıkış logu</h2>
  <?php if (!$loginLogs): ?>
    <p class="muted" style="margin:0">Henüz kayıt yok.</p>
  <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr><th>Zaman</th><th>Personel</th><th>Rol</th><th>Olay</th><th>IP</th></tr>
        </thead>
        <tbody>
          <?php foreach ($loginLogs as $log): ?>
            <tr>
              <td class="small"><?= e((string) ($log['created_at'] ?? '')) ?></td>
              <td><?= e((string) ($log['staff_name'] ?? $log['username'] ?? '')) ?></td>
              <td><?= e(role_label((string) ($log['role'] ?? ''))) ?></td>
              <td><?= ($log['event_type'] ?? '') === 'logout' ? 'Çıkış' : 'Giriş' ?></td>
              <td class="small muted"><?= e((string) ($log['ip_address'] ?? '')) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</section>
