<?php
/** @var array $order */
$orderId = (int) ($order['id'] ?? 0);
$note = (string) ($order['customer_note'] ?? '');
?>
<div class="order-note" data-order-note="<?= $orderId ?>">
  <label class="order-note-label">Sipariş notu
    <textarea
      rows="2"
      maxlength="400"
      placeholder="Sipariş altına not yazın..."
      data-note-input
    ><?= e($note) ?></textarea>
  </label>
  <div class="order-note-actions">
    <button class="btn btn-dark btn-sm" type="button" data-note-save="<?= $orderId ?>">Notu kaydet</button>
    <span class="small muted" data-note-status aria-live="polite"></span>
  </div>
</div>
