<?php
/**
 * Masa kapatma butonları — yalnızca kasa / yönetici.
 * @var int $tableId
 * @var string|null $redirect
 * @var bool $wrap
 * @var string|null $label
 */
$tableId = (int) ($tableId ?? 0);
$redirect = $redirect ?? url('/kasa');
$wrap = (bool) ($wrap ?? true);
$label = $label ?? 'Masa kapat';
if ($tableId <= 0) {
    return;
}
?>
<?php if ($wrap): ?>
  <div class="table-close-actions" style="margin-top:10px">
    <div class="table-close-label"><?= e($label) ?></div>
    <div class="cta-row table-close-btns">
<?php endif; ?>
  <button
    class="btn btn-sm btn-primary"
    type="button"
    data-close-table="<?= $tableId ?>"
    data-method="cash"
    data-close-redirect="<?= e($redirect) ?>"
  >Nakit kapat</button>
  <button
    class="btn btn-sm btn-dark"
    type="button"
    data-close-table="<?= $tableId ?>"
    data-method="card"
    data-close-redirect="<?= e($redirect) ?>"
  >Kart kapat</button>
<?php if ($wrap): ?>
    </div>
  </div>
<?php endif; ?>
