<?php
/** @var string $station */
/** @var list<array> $rows */
$stationKey = $station === 'bar' ? 'bar' : 'kitchen';
$back = $stationKey === 'bar' ? url('/bar') : url('/mutfak');
$titleStation = $stationKey === 'bar' ? 'Bar' : 'Mutfak';
$fmt = static function (?string $dt): string {
    if (!$dt) {
        return '—';
    }
    $ts = strtotime($dt);
    return $ts ? date('d.m H:i', $ts) : $dt;
};
?>
<div class="panel-head">
  <div>
    <p class="eyebrow">Fiş geçmişi</p>
    <h1><?= e($titleStation) ?> · Son fişler</h1>
  </div>
  <div class="cta-row">
    <a class="btn btn-ghost btn-sm" href="<?= e($back) ?>">Panele dön</a>
  </div>
</div>

<section class="panel">
  <?php if (!$rows): ?>
    <p class="muted" style="margin:0">Henüz gönderilmiş fiş yok.</p>
  <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Sipariş</th>
            <th>Masa</th>
            <th>Kaynak</th>
            <th>Gönderim</th>
            <th>Alındı</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $row): ?>
            <tr>
              <td><strong><?= e((string) $row['order_code']) ?></strong></td>
              <td><?= e((string) ($row['table_label'] ?? 'Online')) ?></td>
              <td><?= e((string) ($row['source_label'] ?? '')) ?></td>
              <td class="small"><?= e($fmt($row['slip_sent_at'] ?? null)) ?></td>
              <td class="small"><?= e($fmt($row['slip_acked_at'] ?? null)) ?></td>
              <td>
                <div class="cta-row" style="gap:6px">
                  <a class="btn btn-ghost btn-sm" href="<?= e((string) $row['fis_url']) ?>">Gör</a>
                  <a class="btn btn-primary btn-sm" href="<?= e((string) $row['reprint_url']) ?>">Yeniden bas</a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</section>
