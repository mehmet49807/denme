<?php /** @var array $tables */ ?>
<div class="panel-head">
  <div>
    <p class="eyebrow">Yönetici · Masalar</p>
    <h1>Tüm masalar</h1>
  </div>
  <a class="btn btn-primary btn-sm" href="<?= e(url('/yonetici/masalar/ekle')) ?>">Masa ekle</a>
</div>

<div class="table-board">
  <?php foreach ($tables as $table): ?>
    <?php $isOpen = !empty($table['is_open']); ?>
    <article class="table-tile <?= $isOpen ? 'is-open' : 'is-free' ?>">
      <div class="table-tile-top">
        <strong><?= e($table['label']) ?></strong>
        <span class="chip <?= $isOpen ? 'kitchen' : '' ?>"><?= $isOpen ? 'Açık' : 'Boş' ?></span>
      </div>
      <div class="table-tile-code muted small"><?= e($table['code']) ?> · <?= (int) $table['seats'] ?> kişi</div>
      <?php if ($isOpen): ?>
        <div class="table-tile-meta">
          <span><?= (int) $table['open_count'] ?> sipariş</span>
          <strong class="price"><?= e(money((float) $table['open_total'])) ?></strong>
        </div>
      <?php endif; ?>
      <div class="cta-row" style="margin-top:12px">
        <a class="btn btn-dark btn-sm" href="<?= e(url('/kasa/masa/' . (int) $table['id'])) ?>">Kasa</a>
        <a class="btn btn-ghost btn-sm" href="<?= e(url('/garson/masa/' . (int) $table['id'])) ?>">Garson</a>
        <a class="btn btn-ghost btn-sm" href="<?= e(url('/qr')) ?>">QR</a>
      </div>
      <?php if (empty($table['is_active'])): ?>
        <div class="muted small" style="margin-top:8px">Pasif masa</div>
      <?php endif; ?>
    </article>
  <?php endforeach; ?>
</div>
