<?php
/** @var array $invoices */
/** @var bool $canBrowseDates */
/** @var string $filterDate */
$invoices = $invoices ?? [];
$canBrowseDates = (bool) ($canBrowseDates ?? false);
$filterDate = (string) ($filterDate ?? date('Y-m-d'));
?>
<div class="panel-head">
  <div>
    <p class="eyebrow"><?= $canBrowseDates ? 'Yönetici' : 'Kasa' ?></p>
    <h1>Satış faturaları<?= $canBrowseDates ? '' : ' (bugün)' ?></h1>
  </div>
  <div class="cta-row">
    <a class="btn btn-primary btn-sm" href="<?= e(url('/kasa/gun-sonu')) ?>">Gün sonu</a>
    <?php if ($canBrowseDates): ?>
      <a class="btn btn-ghost btn-sm" href="<?= e(url('/kasa/fatura-ayarlar')) ?>">Firma / KDV</a>
    <?php endif; ?>
    <a class="btn btn-ghost btn-sm" href="<?= e(url('/kasa')) ?>">Masalar</a>
  </div>
</div>

<?php if ($canBrowseDates): ?>
  <section class="panel no-print" style="margin-bottom:16px;max-width:520px">
    <form method="get" action="<?= e(url('/kasa/faturalar')) ?>" class="cta-row" style="align-items:end;flex-wrap:wrap">
      <label style="flex:1;min-width:180px">Tarih filtresi
        <input type="date" name="date" value="<?= e($filterDate) ?>">
      </label>
      <button class="btn btn-dark btn-sm" type="submit">Filtrele</button>
      <a class="btn btn-ghost btn-sm" href="<?= e(url('/kasa/faturalar')) ?>">Tümü</a>
    </form>
  </section>
<?php endif; ?>

<section class="panel">
  <?php if (!$invoices): ?>
    <p class="muted" style="margin:0">
      <?= $canBrowseDates ? 'Kayıtlı fatura yok.' : 'Bugün kesilmiş fatura yok.' ?>
    </p>
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
