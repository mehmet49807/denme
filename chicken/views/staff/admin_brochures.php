<?php
/** @var list<array{id:string,name:string,blurb:string,is_active:bool,is_selected:bool}> $themes */
/** @var string $brochureUrl */
/** @var string $qrImageUrl */
?>
<div class="panel-head">
  <div>
    <p class="eyebrow">Yönetici · Broşür</p>
    <h1>Broşür temaları</h1>
  </div>
  <div class="cta-row">
    <a class="btn btn-ghost btn-sm" href="<?= e(url('/menu/brosur')) ?>" target="_blank" rel="noopener">Canlı önizle</a>
    <a class="btn btn-accent btn-sm" href="<?= e(url('/qr')) ?>">QR Menü</a>
  </div>
</div>

<p class="muted" style="margin-top:-8px">
  Temayı seçin veya pasife alın. Pasif temalar müşteriye gösterilmez.
  Yeni temalar farklı düzenler kullanır: kart, dergi, tahta, galeri, yan menü.
  Menü içeriği ürünlerden otomatik gelir.
</p>

<div class="theme-grid" style="margin-top:20px">
  <?php foreach ($themes as $theme): ?>
    <article class="theme-card panel <?= !empty($theme['is_selected']) ? 'is-selected' : '' ?> <?= empty($theme['is_active']) ? 'is-inactive' : '' ?>">
      <div class="theme-preview theme-preview-<?= e($theme['id']) ?>" aria-hidden="true">
        <span>CHICKEN</span>
        <em><?= e($theme['name']) ?></em>
      </div>
      <div class="theme-card-body">
        <div class="meta-row">
          <strong><?= e($theme['name']) ?></strong>
          <?php if (!empty($theme['is_selected'])): ?>
            <span class="chip online">Seçili</span>
          <?php elseif (!empty($theme['is_active'])): ?>
            <span class="chip">Aktif</span>
          <?php else: ?>
            <span class="chip">Pasif</span>
          <?php endif; ?>
        </div>
        <?php if (!empty($theme['layout_label'])): ?>
          <div class="small" style="margin:4px 0 2px"><span class="chip"><?= e((string) $theme['layout_label']) ?> düzen</span></div>
        <?php endif; ?>
        <p class="muted small"><?= e($theme['blurb']) ?></p>
        <div class="cta-row" style="margin-top:12px">
          <?php if (!empty($theme['is_active']) && empty($theme['is_selected'])): ?>
            <form method="post" action="<?= e(url('/yonetici/brosurler')) ?>" style="margin:0">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="select">
              <input type="hidden" name="theme_id" value="<?= e($theme['id']) ?>">
              <button class="btn btn-accent btn-sm" type="submit">Bu temayı seç</button>
            </form>
          <?php endif; ?>
          <?php if (!empty($theme['is_selected'])): ?>
            <span class="btn btn-ghost btn-sm" style="pointer-events:none">Kullanımda</span>
          <?php endif; ?>
          <form method="post" action="<?= e(url('/yonetici/brosurler')) ?>" style="margin:0">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="toggle">
            <input type="hidden" name="theme_id" value="<?= e($theme['id']) ?>">
            <input type="hidden" name="active" value="<?= !empty($theme['is_active']) ? '0' : '1' ?>">
            <button class="btn btn-dark btn-sm" type="submit">
              <?= !empty($theme['is_active']) ? 'Pasife al' : 'Aktifleştir' ?>
            </button>
          </form>
          <a class="btn btn-ghost btn-sm" href="<?= e(url('/menu/brosur?preview=' . rawurlencode($theme['id']))) ?>" target="_blank" rel="noopener">Önizle</a>
        </div>
      </div>
    </article>
  <?php endforeach; ?>
</div>

<section class="panel" style="margin-top:20px;max-width:420px">
  <strong style="display:block;margin-bottom:10px">QR (seçili tema)</strong>
  <img class="qr-single-img" src="<?= e($qrImageUrl) ?>" alt="QR" width="200" height="200">
  <div class="small muted" style="margin-top:10px;word-break:break-all"><?= e($brochureUrl) ?></div>
</section>
