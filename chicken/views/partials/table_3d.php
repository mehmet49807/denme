<?php
/**
 * 3D masa kartı — izometrik kare masa + ayaklar + sandalye
 * @var array $table
 * @var string|null $href
 * @var string $extraClass
 * @var string|null $footerHtml
 */
$table = $table ?? [];
$href = $href ?? null;
$extraClass = trim((string) ($extraClass ?? ''));
$footerHtml = $footerHtml ?? null;
$isOpen = !empty($table['is_open']);
$active = array_key_exists('is_active', $table) ? !empty($table['is_active']) : true;
$seats = max(2, min(8, (int) ($table['seats'] ?? 4)));
/* Sandalye slotları: 0 üst, 1 sağ, 2 alt, 3 sol, 4-7 köşeler */
$chairMap = match (true) {
    $seats <= 2 => [0, 2],
    $seats === 3 => [0, 1, 3],
    $seats === 4 => [0, 1, 2, 3],
    $seats === 5 => [0, 1, 2, 3, 4],
    $seats === 6 => [0, 1, 2, 3, 4, 6],
    $seats === 7 => [0, 1, 2, 3, 4, 5, 6],
    default => [0, 1, 2, 3, 4, 5, 6, 7],
};
$tag = $href ? 'a' : 'article';
$classes = 'table-tile table-3d '
    . ($isOpen ? 'is-open' : 'is-free')
    . (!$active ? ' is-inactive' : '')
    . ($extraClass !== '' ? ' ' . $extraClass : '');
$statusLabel = !$active ? 'Pasif' : ($isOpen ? 'Açık' : 'Boş');
$label = (string) ($table['label'] ?? 'Masa');
?>
<<?= $tag ?>
  class="<?= e($classes) ?>"
  <?php if ($href): ?>href="<?= e($href) ?>"<?php endif; ?>
>
  <div class="table-3d-stage" aria-hidden="true">
    <div class="t3-floor"></div>
    <div class="t3-scene">
      <?php foreach ($chairMap as $slot): ?>
        <div class="t3-chair t3-chair-<?= (int) $slot ?>">
          <span class="t3-chair-back"></span>
          <span class="t3-chair-seat"></span>
          <span class="t3-chair-leg"></span>
        </div>
      <?php endforeach; ?>

      <div class="t3-table">
        <span class="t3-leg t3-leg-1"></span>
        <span class="t3-leg t3-leg-2"></span>
        <span class="t3-leg t3-leg-3"></span>
        <span class="t3-leg t3-leg-4"></span>
        <div class="t3-edge"></div>
        <div class="t3-surface">
          <span class="t3-grain"></span>
          <?php if ($isOpen): ?>
            <span class="t3-glass"></span>
            <span class="t3-plate"></span>
            <span class="t3-food"></span>
          <?php else: ?>
            <span class="t3-mat"></span>
          <?php endif; ?>
          <strong class="t3-label"><?= e($label) ?></strong>
        </div>
      </div>
    </div>
  </div>

  <div class="table-3d-info">
    <div class="table-tile-top">
      <strong><?= e($label) ?></strong>
      <span class="chip <?= $isOpen && $active ? 'kitchen' : '' ?>"><?= e($statusLabel) ?></span>
    </div>
    <div class="table-tile-code muted small">
      <?= e((string) ($table['code'] ?? '')) ?> · <?= $seats ?> kişi
    </div>
    <?php if ($isOpen): ?>
      <div class="table-tile-meta">
        <span><?= (int) ($table['open_count'] ?? 0) ?> sipariş</span>
        <strong class="price"><?= e(money((float) ($table['open_total'] ?? 0))) ?></strong>
      </div>
      <?php if (!empty($table['waiter_names']) && is_array($table['waiter_names'])): ?>
        <div class="muted small"><?= e(implode(', ', $table['waiter_names'])) ?></div>
      <?php endif; ?>
    <?php elseif ($active): ?>
      <div class="muted small">Masa boş · hazır</div>
    <?php endif; ?>
    <?php if ($footerHtml !== null): ?>
      <div class="table-3d-footer"><?= $footerHtml ?></div>
    <?php endif; ?>
  </div>
</<?= $tag ?>>
