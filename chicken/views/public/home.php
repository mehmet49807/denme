<section class="vs-hero">
  <div class="vs-hero-media" role="img" aria-label="Crisp &amp; Co. restoran salonu"></div>
  <div class="vs-hero-shade" aria-hidden="true"></div>
  <div class="vs-hero-copy">
    <img class="vs-hero-logo" src="<?= e(logo_url()) ?>" alt="" width="148" height="148">
    <p class="vs-tag">Lezzetin doğal adresi</p>
    <h1>Crisp &amp; Co.</h1>
    <p class="vs-sub">Modern ızgara restoranı — menüden seçin, üye olmadan sipariş verin.</p>
    <a class="vs-cta" href="<?= e(url('/siparis')) ?>">Online sipariş</a>
  </div>
</section>

<section class="vs-section vs-home-start" id="menu">
  <div class="vs-section-head">
    <div>
      <p class="vs-tag">Menü</p>
      <h2>Öne çıkanlar</h2>
    </div>
    <a class="vs-link" href="<?= e(url('/menu')) ?>">Tüm menü</a>
  </div>
  <div class="vs-cards">
    <?php foreach (array_slice($items, 0, 4) as $item): ?>
      <?php
        $image = class_exists('MenuImageSync')
          ? MenuImageSync::resolve($item)
          : trim((string) ($item['image_url'] ?? ''));
        $imageSrc = $image !== '' ? asset_url($image) : '';
      ?>
      <article class="vs-card">
        <div class="vs-card-media"<?= $imageSrc !== '' ? ' style="background-image:url(\'' . e($imageSrc) . '\')"' : '' ?>>
          <?php if ($imageSrc !== ''): ?>
            <img src="<?= e($imageSrc) ?>" alt="<?= e((string) $item['name']) ?>" loading="eager" width="480" height="360">
          <?php endif; ?>
        </div>
        <div class="vs-card-body">
          <h3><?= e((string) $item['name']) ?></h3>
          <div class="vs-card-row">
            <span><?= e(money((float) $item['price'])) ?> <small class="muted">KDV dahil</small></span>
            <button
              class="vs-cta vs-cta-sm"
              type="button"
              data-add-item="<?= (int) $item['id'] ?>"
              data-name="<?= e((string) $item['name']) ?>"
              data-price="<?= e((string) $item['price']) ?>"
              data-station="<?= e((string) $item['station']) ?>"
            >Ekle</button>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
  <div class="vs-home-actions">
    <a class="vs-cta" href="<?= e(url('/siparis')) ?>">Sepete git</a>
    <p class="vs-note">Yeni üyelere <b>YENI10</b> ile %10 indirim</p>
  </div>
</section>

<section class="vs-section vs-section-soft" id="hakkimizda-ozet">
  <p class="vs-tag">Marka</p>
  <h2>Crisp &amp; Co.</h2>
  <p class="vs-sub vs-sub-dark">Taze ızgara, hızlı servis, net iletişim.</p>
  <div class="vs-links">
    <a href="<?= e(url('/hakkimizda')) ?>">Hakkımızda</a>
    <a href="<?= e(url('/misyon')) ?>">Misyon</a>
    <a href="<?= e(url('/musteri-memnuniyeti')) ?>">Memnuniyet</a>
  </div>
</section>
