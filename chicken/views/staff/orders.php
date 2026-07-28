<?php
/** @var array $tables */
/** @var array $openTables */
/** @var bool $canManage */
?>
<div class="panel-head">
  <div>
    <p class="eyebrow">Siparişler</p>
    <h1>Açık masalar</h1>
  </div>
  <a class="btn btn-primary btn-sm" href="<?= e(url('/masa/ekle')) ?>">Yeni masa</a>
  <a class="btn btn-ghost btn-sm" href="<?= e(url('/garson')) ?>">Menüden ekle</a>
</div>

<?php if (!$openTables): ?>
  <div class="panel muted" style="margin-bottom:20px">Şu an açık masa yok. Aşağıdan yeni fiş açabilirsiniz.</div>
<?php endif; ?>

<div class="table-board">
  <?php foreach ($openTables as $table): ?>
    <a class="table-tile is-open" href="<?= e(url('/garson/masa/' . (int) $table['id'])) ?>">
      <div class="table-tile-top">
        <strong><?= e($table['label']) ?></strong>
        <span class="chip kitchen">Açık</span>
      </div>
      <div class="table-tile-code muted small"><?= e($table['code']) ?></div>
      <div class="table-tile-meta">
        <span><?= (int) $table['open_count'] ?> sipariş</span>
        <strong class="price"><?= e(money((float) $table['open_total'])) ?></strong>
      </div>
      <?php if (!empty($table['waiter_names'])): ?>
        <div class="muted small"><?= e(implode(', ', $table['waiter_names'])) ?></div>
      <?php endif; ?>
      <div class="muted small" style="margin-top:6px">Sadece kendi siparişinize ekleme · iptal/kapatma yok</div>
    </a>
  <?php endforeach; ?>
</div>

<div class="panel" style="margin-top:24px" data-waiter-cart data-cart-persist="waiter">
  <div class="panel-head" style="margin-bottom:12px">
    <div>
      <p class="eyebrow">Yeni fiş</p>
      <h2 style="margin:0;font-family:var(--font-display);font-size:1.35rem">Sipariş fişi gönder</h2>
    </div>
  </div>
  <form class="stack" data-waiter-form>
    <label>Masa
      <select name="table_id" required>
        <option value="">Masa seçin</option>
        <?php foreach ($tables as $table): ?>
          <option value="<?= (int) $table['id'] ?>"><?= e($table['label']) ?> (<?= e($table['code']) ?>)</option>
        <?php endforeach; ?>
      </select>
    </label>
    <div data-cart-list></div>
    <div class="meta-row">
      <span class="muted">Toplam / adet</span>
      <strong><span data-cart-total>0,00 ₺</span> · <span data-cart-count>0</span></strong>
    </div>
    <label>Sipariş notu
      <textarea name="customer_note" placeholder="Sipariş altına not yazın..."></textarea>
    </label>
    <button class="btn btn-primary" type="submit">Mutfak + Bar fişi gönder</button>
  </form>
</div>
