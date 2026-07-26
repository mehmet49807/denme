<?php
/** @var array $waiterStats */
/** @var array $cashierStats */
/** @var string $monthLabel */
?>
<div class="panel-head">
  <div>
    <p class="eyebrow">Yönetici · Personel</p>
    <h1>Kasa ve Garson istatistikleri</h1>
  </div>
  <div class="muted"><?= e($monthLabel) ?></div>
</div>

<section class="panel" style="margin-bottom:16px">
  <h2 style="font-family:var(--font-display);margin:0 0 12px">Garson satışları</h2>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Garson</th>
          <th>Sipariş</th>
          <th>Tahsilat</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$waiterStats): ?>
          <tr><td colspan="3" class="muted">Garson kaydı yok.</td></tr>
        <?php endif; ?>
        <?php foreach ($waiterStats as $row): ?>
          <tr>
            <td><?= e($row['name']) ?></td>
            <td><?= (int) $row['order_count'] ?></td>
            <td><?= e(money((float) $row['sales_total'])) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

<section class="panel">
  <h2 style="font-family:var(--font-display);margin:0 0 12px">Kasa satışları</h2>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Kasa</th>
          <th>Sipariş</th>
          <th>Tahsilat</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$cashierStats): ?>
          <tr><td colspan="3" class="muted">Kasa kaydı yok.</td></tr>
        <?php endif; ?>
        <?php foreach ($cashierStats as $row): ?>
          <tr>
            <td><?= e($row['name']) ?></td>
            <td><?= (int) $row['order_count'] ?></td>
            <td><?= e(money((float) $row['sales_total'])) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
