<section class="hero">
  <div class="hero-media" aria-hidden="true"></div>
  <div class="hero-content">
    <div class="hero-logo" aria-hidden="true">
      <img src="<?= e(url('/assets/img/logo.svg')) ?>" alt="" width="72" height="72">
    </div>
    <p class="eyebrow">Izgara &amp; hızlı servis</p>
    <h1>CHICKEN</h1>
    <p class="lede">Izgara tavuk, sıcak bar, hızlı servis. Menüden seçin, online sipariş verin, siparişinizi anlık takip edin.</p>
    <div class="cta-row cta-home">
      <div class="cta-primary-wrap">
        <a class="btn btn-accent" href="<?= e(url('/uye-ol')) ?>">Üye ol</a>
        <p class="cta-note">Yeni üyelere <strong>%10 indirim</strong></p>
      </div>
      <a class="btn btn-ghost" href="<?= e(url('/siparis')) ?>">Online Sipariş</a>
    </div>
  </div>
</section>

<section class="section" id="menu">
  <h2>Öne çıkan lezzetler</h2>
  <p class="section-intro">Mutfak ve bar ayrı fişlerle çalışır. Her siparişin benzersiz kimliği vardır.</p>
  <div class="menu-grid">
    <?php foreach (array_slice($items, 0, 6) as $item): ?>
      <?php partial('partials/menu_item_card', ['item' => $item, 'showDescription' => true]); ?>
    <?php endforeach; ?>
  </div>
</section>
