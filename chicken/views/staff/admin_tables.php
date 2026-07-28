<?php /** @var array $tables */ ?>
<div class="panel-head">
  <div>
    <p class="eyebrow">Yönetici · Masalar</p>
    <h1>Tüm masalar</h1>
  </div>
  <a class="btn btn-primary btn-sm" href="<?= e(url('/yonetici/masalar/ekle')) ?>">Masa ekle</a>
</div>

<div class="table-board table-board-3d">
  <?php foreach ($tables as $table): ?>
    <?php
      $active = !empty($table['is_active']);
      ob_start();
    ?>
      <div class="cta-row" style="margin-top:12px">
        <a class="btn btn-primary btn-sm" href="<?= e(url('/yonetici/masalar/' . (int) $table['id'])) ?>">Düzenle</a>
        <?php if ($active): ?>
          <a class="btn btn-dark btn-sm" href="<?= e(url('/kasa/masa/' . (int) $table['id'])) ?>">Kasa</a>
          <a class="btn btn-ghost btn-sm" href="<?= e(url('/garson/masa/' . (int) $table['id'])) ?>">Garson</a>
        <?php endif; ?>
      </div>
      <form method="post" action="<?= e(url('/yonetici/masalar/durum')) ?>" style="margin-top:10px">
        <?= csrf_field() ?>
        <input type="hidden" name="table_id" value="<?= (int) $table['id'] ?>">
        <input type="hidden" name="is_active" value="<?= $active ? '0' : '1' ?>">
        <button class="btn btn-ghost btn-sm" type="submit" style="width:100%">
          <?= $active ? 'Pasife al' : 'Aktifleştir' ?>
        </button>
      </form>
    <?php
      $footer = ob_get_clean();
      partial('partials/table_3d', [
          'table' => $table,
          'footerHtml' => $footer,
      ]);
    ?>
  <?php endforeach; ?>
</div>
