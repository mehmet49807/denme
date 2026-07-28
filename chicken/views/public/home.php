<section class="bp-hero">
  <div class="bp-hero-bg" aria-hidden="true"></div>
  <div class="bp-hero-inner">
    <img class="bp-hero-logo" src="<?= e(logo_url()) ?>" alt="Crisp &amp; Co." width="96" height="96">
    <p class="bp-hero-tag">Lezzetin doğal adresi</p>
    <h1 class="bp-hero-name">CRISP<br>&amp; CO.</h1>
    <p class="bp-hero-line">Izgara tavuk · doğal lezzet · üye olmadan sipariş</p>
    <a class="bp-cta" href="<?= e(url('/siparis')) ?>">Online sipariş</a>
  </div>
</section>

<section class="bp-section" id="menu">
  <div class="bp-section-top">
    <div>
      <p class="bp-label">Menü</p>
      <h2>Öne çıkanlar</h2>
    </div>
    <a class="bp-more" href="<?= e(url('/menu')) ?>">Tüm menü</a>
  </div>
  <div class="bp-fare">
    <?php foreach (array_slice($items, 0, 5) as $item): ?>
      <?php partial('partials/menu_item_card', ['item' => $item, 'showDescription' => false, 'showAdd' => true]); ?>
    <?php endforeach; ?>
  </div>
  <p class="bp-note">Yeni üyelere <b>YENI10</b> · %10 indirim</p>
</section>

<section class="bp-section bp-section-alt" id="hakkimizda-ozet">
  <p class="bp-label">Marka</p>
  <h2>Crisp &amp; Co.</h2>
  <p class="bp-lead">Taze ızgara, hızlı servis, şeffaf iletişim.</p>
  <div class="bp-strip">
    <a href="<?= e(url('/hakkimizda')) ?>">Hakkımızda</a>
    <a href="<?= e(url('/misyon')) ?>">Misyon</a>
    <a href="<?= e(url('/musteri-memnuniyeti')) ?>">Memnuniyet</a>
  </div>
</section>
