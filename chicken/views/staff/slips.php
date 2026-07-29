<?php
/** @var array $order */
/** @var bool $autoPrint */
/** @var string $stationFilter kitchen|bar|all */
/** @var string $backUrl */
/** @var list<int> $onlyItemIds */
$company = FiscalService::companyProfile();
$printedAt = date('d.m.Y H:i');
$autoPrint = !empty($autoPrint);
$stationFilter = in_array(($stationFilter ?? 'all'), ['kitchen', 'bar', 'all'], true)
    ? ($stationFilter ?? 'all')
    : 'all';
$backUrl = (string) ($backUrl ?? url('/siparisler'));
$backPath = (string) ($backPath ?? '/garson');
if ($backPath === '' || !str_starts_with($backPath, '/')) {
    $backPath = '/garson';
}
$paper = BrochureService::getSetting('slip_paper_width', '80') === '58' ? '58' : '80';
$onlyItemIds = is_array($onlyItemIds ?? null) ? $onlyItemIds : [];
$onlyMap = [];
foreach ($onlyItemIds as $oid) {
    $id = (int) $oid;
    if ($id > 0) {
        $onlyMap[$id] = true;
    }
}
$filterRows = static function (array $rows) use ($onlyMap): array {
    $out = [];
    foreach ($rows as $r) {
        if ((int) ($r['quantity'] ?? 0) <= 0) {
            continue;
        }
        if (($r['status'] ?? '') === 'cancelled') {
            continue;
        }
        if ($onlyMap !== [] && empty($onlyMap[(int) ($r['id'] ?? 0)])) {
            continue;
        }
        $out[] = $r;
    }
    return $out;
};
$kitchenItems = $filterRows($order['kitchen_items'] ?? []);
$barItems = $filterRows($order['bar_items'] ?? []);
if ($stationFilter === 'kitchen') {
    $barItems = [];
} elseif ($stationFilter === 'bar') {
    $kitchenItems = [];
}
$showKitchen = $kitchenItems !== [];
$showBar = $barItems !== [];
$fisQs = static function (string $station) use ($order, $onlyItemIds, $backPath): string {
    return station_slip_url((int) $order['id'], [
        'station' => $station,
        'items' => $onlyItemIds,
        'back' => $backPath,
    ]);
};
?>
<div
  class="xp-slips-page"
  data-xp-slips
  data-paper="<?= e($paper) ?>"
  <?= $autoPrint ? 'data-autoprint="1"' : '' ?>
  data-print-back="<?= e($backUrl) ?>"
>
  <div class="panel-head no-print">
    <div>
      <p class="eyebrow">XPrinter fiş</p>
      <h1><?= e($order['order_code']) ?></h1>
    </div>
    <div class="cta-row">
      <button class="btn btn-primary btn-sm" type="button" data-xp-print>Yazdır</button>
      <?php if (($order['kitchen_items'] ?? []) !== []): ?>
        <a class="btn btn-ghost btn-sm" href="<?= e($fisQs('kitchen')) ?>">Sadece mutfak</a>
      <?php endif; ?>
      <?php if (($order['bar_items'] ?? []) !== []): ?>
        <a class="btn btn-ghost btn-sm" href="<?= e($fisQs('bar')) ?>">Sadece bar</a>
      <?php endif; ?>
      <a class="btn btn-ghost btn-sm" href="<?= e($backUrl) ?>">Geri</a>
    </div>
  </div>

  <p class="muted no-print" style="margin-top:-6px">
    XPrinter / termal yazıcı uyumlu (<?= e($paper) ?>mm).
    Garson siparişinde otomatik yazdırılır; online siparişte kasa/yönetici onayından sonra yazdırılır.
  </p>

  <div class="panel no-print" style="margin-bottom:16px">
    <?php partial('partials/order_note', ['order' => $order]); ?>
  </div>

  <div class="xp-slips" data-xp-print-root>
    <?php if ($showKitchen): ?>
      <?php partial('partials/xp_station_ticket', [
          'station' => 'kitchen',
          'order' => $order,
          'items' => $kitchenItems,
          'company' => $company,
          'printedAt' => $printedAt,
      ]); ?>
    <?php endif; ?>

    <?php if ($showBar): ?>
      <?php partial('partials/xp_station_ticket', [
          'station' => 'bar',
          'order' => $order,
          'items' => $barItems,
          'company' => $company,
          'printedAt' => $printedAt,
      ]); ?>
    <?php endif; ?>

    <?php if (!$showKitchen && !$showBar): ?>
      <div class="panel muted no-print">Bu siparişte yazdırılacak mutfak/bar ürünü yok.</div>
    <?php endif; ?>
  </div>

  <?php if ($autoPrint): ?>
    <p class="no-print muted small" data-autoprint-status>Yazdırma penceresi açılıyor…</p>
  <?php endif; ?>
</div>
