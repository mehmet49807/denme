<?php
/**
 * XPrinter / ESC-POS uyumlu istasyon fişi (58–80mm).
 *
 * @var string $station kitchen|bar
 * @var array $order
 * @var list<array> $items
 * @var array $company
 * @var string $printedAt
 */
$station = ($station ?? '') === 'bar' ? 'bar' : 'kitchen';
$title = $station === 'bar' ? 'BAR FISI' : 'MUTFAK FISI';
$companyName = (string) ($company['title'] ?? 'Crisp & Co.');
$items = $items ?? [];
?>
<section class="xp-ticket" data-xp-station="<?= e($station) ?>">
  <div class="xp-ticket-inner">
    <div class="xp-center xp-brand"><?= e(mb_strtoupper($companyName)) ?></div>
    <div class="xp-center xp-title"><?= e($title) ?></div>
    <div class="xp-sep">--------------------------------</div>
    <div class="xp-row"><span>Siparis</span><strong><?= e((string) $order['order_code']) ?></strong></div>
    <div class="xp-row"><span>Masa</span><strong><?= e((string) ($order['table_label'] ?? 'PAKET/ONLINE')) ?></strong></div>
    <div class="xp-row"><span>Kaynak</span><strong><?= e(source_label((string) ($order['source'] ?? ''))) ?></strong></div>
    <?php if (!empty($order['waiter_name'])): ?>
      <div class="xp-row"><span>Garson</span><strong><?= e((string) $order['waiter_name']) ?></strong></div>
    <?php endif; ?>
    <div class="xp-row"><span>Saat</span><strong><?= e($printedAt) ?></strong></div>
    <?php if (!empty($order['customer_name']) && ($order['source'] ?? '') === 'online'): ?>
      <div class="xp-row"><span>Musteri</span><strong><?= e((string) $order['customer_name']) ?></strong></div>
    <?php endif; ?>
    <div class="xp-sep">--------------------------------</div>
    <?php if (!empty($order['customer_note'])): ?>
      <div class="xp-note">NOT: <?= e((string) $order['customer_note']) ?></div>
      <div class="xp-sep">--------------------------------</div>
    <?php endif; ?>
    <?php if ($items): ?>
      <?php foreach ($items as $item): ?>
        <div class="xp-item">
          <div class="xp-item-main">
            <strong><?= (int) ($item['quantity'] ?? 1) ?>x <?= e((string) ($item['item_name'] ?? '')) ?></strong>
          </div>
          <?php if (!empty($item['note'])): ?>
            <div class="xp-item-note">* <?= e((string) $item['note']) ?></div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="xp-center">Urun yok</div>
    <?php endif; ?>
    <div class="xp-sep">--------------------------------</div>
    <div class="xp-center xp-foot"><?= e($title) ?> / <?= e((string) $order['order_code']) ?></div>
    <div class="xp-cut" aria-hidden="true">.</div>
  </div>
</section>
