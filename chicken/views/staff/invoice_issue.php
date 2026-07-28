<?php
/** @var array $order */
/** @var array|null $invoice */
$invoice = $invoice ?? null;
?>
<div class="panel-head">
  <div>
    <p class="eyebrow">Fatura kes</p>
    <h1><?= e((string) $order['order_code']) ?></h1>
  </div>
  <a class="btn btn-ghost btn-sm" href="<?= e(url('/kasa')) ?>">Geri</a>
</div>

<?php if ($invoice): ?>
  <div class="alert alert-ok">Bu sipariş için fatura kesilmiş: <a href="<?= e(url('/kasa/fatura/' . (int) $invoice['id'])) ?>"><?= e((string) $invoice['invoice_no']) ?></a></div>
<?php endif; ?>

<section class="panel" style="max-width:520px">
  <p class="muted" style="margin-top:0">
    Toplam: <strong><?= e(money((float) $order['total'])) ?></strong>
    · Ödeme: <?= e(payment_method_label($order['payment_method'] ?? null)) ?>
  </p>
  <form method="post" action="<?= e(url('/kasa/fatura/siparis/' . (int) $order['id'])) ?>" class="stack">
    <?= csrf_field() ?>
    <label>Alıcı adı
      <input name="buyer_name" maxlength="160" value="<?= e((string) ($order['customer_name'] ?? 'Nihai Tüketici')) ?>" required>
    </label>
    <label>Alıcı VKN / TCKN (opsiyonel)
      <input name="buyer_tax_id" maxlength="11" inputmode="numeric" pattern="\d{10,11}" placeholder="Kurumsal için">
    </label>
    <label>Alıcı vergi dairesi (opsiyonel)
      <input name="buyer_tax_office" maxlength="120">
    </label>
    <label>Alıcı adres (opsiyonel)
        <textarea name="buyer_address" rows="2"></textarea>
    </label>
    <button class="btn btn-primary" type="submit" <?= $invoice ? 'disabled' : '' ?>>
      <?= $invoice ? 'Fatura kesildi' : 'Fatura kes' ?>
    </button>
  </form>
</section>
