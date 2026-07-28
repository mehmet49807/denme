<?php /** @var array $tables */ ?>
<div class="panel-head">
  <div>
    <p class="eyebrow">Yönetici · Masalar</p>
    <h1>Tüm masalar</h1>
  </div>
  <a class="btn btn-primary btn-sm" href="<?= e(url('/masa/ekle')) ?>">Yeni masa</a>
</div>

<div class="table-board">
  <?php foreach ($tables as $table): ?>
    <?php
      $isOpen = !empty($table['is_open']);
      $active = !empty($table['is_active']);
    ?>
    <article class="table-tile <?= $isOpen ? 'is-open' : 'is-free' ?><?= !$active ? ' is-inactive' : '' ?>">
      <div class="table-tile-top">
        <strong><?= e($table['label']) ?></strong>
        <span class="chip <?= $isOpen ? 'kitchen' : '' ?>">
          <?= !$active ? 'Pasif' : ($isOpen ? 'Açık' : 'Boş') ?>
        </span>
      </div>
      <div class="table-tile-code muted small"><?= e($table['code']) ?> · <?= (int) $table['seats'] ?> kişi</div>
      <?php if (!empty($table['opened_by_name'])): ?>
        <div class="muted small">Açan: <?= e((string) $table['opened_by_name']) ?></div>
      <?php endif; ?>
      <?php if ($isOpen): ?>
        <div class="table-tile-meta">
          <span><?= (int) $table['open_count'] ?> sipariş</span>
          <strong class="price"><?= e(money((float) $table['open_total'])) ?></strong>
        </div>
      <?php endif; ?>
      <div class="cta-row" style="margin-top:12px">
        <a class="btn btn-primary btn-sm" href="<?= e(url('/yonetici/masalar/' . (int) $table['id'])) ?>">Düzenle</a>
        <?php if ($active): ?>
          <a class="btn btn-dark btn-sm" href="<?= e(url('/kasa/masa/' . (int) $table['id'])) ?>">Kasa</a>
          <a class="btn btn-ghost btn-sm" href="<?= e(url('/garson/masa/' . (int) $table['id'])) ?>">Garson</a>
        <?php endif; ?>
      </div>
      <?php if ($isOpen && $active): ?>
        <?php partial('partials/table_close_buttons', [
            'tableId' => (int) $table['id'],
            'redirect' => url('/yonetici/masalar'),
        ]); ?>
      <?php endif; ?>
      <form method="post" action="<?= e(url('/yonetici/masalar/durum')) ?>" style="margin-top:10px">
        <?= csrf_field() ?>
        <input type="hidden" name="table_id" value="<?= (int) $table['id'] ?>">
        <input type="hidden" name="is_active" value="<?= $active ? '0' : '1' ?>">
        <button class="btn btn-ghost btn-sm" type="submit" style="width:100%">
          <?= $active ? 'Pasife al' : 'Aktifleştir' ?>
        </button>
      </form>
    </article>
  <?php endforeach; ?>
</div>
