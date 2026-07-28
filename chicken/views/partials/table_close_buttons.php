<?php
/**
 * Masa kapatma butonları — yalnızca kasa/yönetici.
 * @var int $tableId
 * @var string|null $redirect
 * @var bool $wrap
 */
$tableId = (int) ($tableId ?? 0);
$redirect = $redirect ?? url('/kasa');
$wrap = (bool) ($wrap ?? true);
?>
<?php if ($wrap): ?><div class="cta-row table-close-actions" style="margin-top:10px"><?php endif; ?>
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
<?php if ($wrap): ?></div><?php endif; ?>
