<?php
/** @var array $invoices */
$invoices = $invoices ?? [];
?>
<div class="panel-head">
  <div>
    <p class="eyebrow">Kasa</p>
    <h1>Satış faturaları</h1>
  </div>
  <div class="cta-row">
    <a class="btn btn-primary btn-sm" href="<?= e(url('/kasa/gun-sonu')) ?>">Gün sonu</a>
    <a class="btn btn-ghost btn-sm" href="<?= e(url('/kasa')) ?>">Masalar</a>
  </div>
</div>

<section class="panel">
  <?php if (!$invoices): ?>
    <p class="muted" style="margin:0">Henüz fatura yok. Ödenmiş siparişten fatura kesebilirsiniz.</p>
  <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Fatura No</th>
            <th>Tarih</th>
            <th>Alıcı</th>
            <th>Sipariş</th>
            <th>Tutar</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($invoices as $inv): ?>
            <tr>
              <td><strong><?= e((string) $inv['invoice_no']) ?></strong></td>
              <td><?= e((string) $inv['invoice_date']) ?></td>
              <td><?= e((string) $inv['buyer_name']) ?></td>
              <td><?= e((string) ($inv['order_code'] ?? '')) ?></td>
              <td><?= e(money((float) $inv['gross_total'])) ?></td>
              <td><a class="btn btn-ghost btn-sm" href="<?= e(url('/kasa/fatura/' . (int) $inv['id'])) ?>">Aç</a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</section>
