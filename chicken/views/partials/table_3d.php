<?php
/**
 * 3D masa kartı
 * @var array $table
 * @var string|null $href  Link varsa <a>, yoksa <article>
 * @var string $extraClass
 * @var string|null $footerHtml  Ek alt içerik (butonlar vb.)
 */
$table = $table ?? [];
$href = $href ?? null;
$extraClass = trim((string) ($extraClass ?? ''));
$footerHtml = $footerHtml ?? null;
$isOpen = !empty($table['is_open']);
$active = array_key_exists('is_active', $table) ? !empty($table['is_active']) : true;
$seats = max(2, min(12, (int) ($table['seats'] ?? 4)));
$tag = $href ? 'a' : 'article';
$classes = 'table-tile table-3d '
    . ($isOpen ? 'is-open' : 'is-free')
    . (!$active ? ' is-inactive' : '')
    . ($extraClass !== '' ? ' ' . $extraClass : '');
$statusLabel = !$active ? 'Pasif' : ($isOpen ? 'Açık' : 'Boş');
?>
<<?= $tag ?>
  class="<?= e($classes) ?>"
  <?php if ($href): ?>href="<?= e($href) ?>"<?php endif; ?>
>
  <div class="table-3d-stage" aria-hidden="true">
    <div class="table-3d-shadow"></div>
    <div class="table-3d-chairs" data-seats="<?= $seats ?>">
      <?php for ($i = 0; $i < $seats; $i++): ?>
        <span class="table-3d-chair" style="--i:<?= $i ?>;--n:<?= $seats ?>"></span>
      <?php endfor; ?>
    </div>
    <div class="table-3d-top">
      <span class="table-3d-plate"></span>
      <span class="table-3d-plate"></span>
      <?php if ($isOpen): ?>
        <span class="table-3d-dish"></span>
      <?php endif; ?>
      <strong class="table-3d-name"><?= e((string) ($table['label'] ?? 'Masa')) ?></strong>
    </div>
  </div>

  <div class="table-3d-info">
    <div class="table-tile-top">
      <strong><?= e((string) ($table['label'] ?? '')) ?></strong>
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
