<section class="hero">
  <div class="hero-media" aria-hidden="true"></div>
  <div class="hero-content">
    <h1>CHICKEN</h1>
    <p class="lede">Izgara tavuk, hızlı servis. Menüden seçin, online sipariş verin.</p>
    <div class="cta-home">
      <a class="btn btn-accent" href="<?= e(url('/uye-ol')) ?>">Üye ol</a>
      <p class="cta-note">Yeni üyelere %10 indirim</p>
      <a class="btn btn-ghost" href="<?= e(url('/siparis')) ?>">Online Sipariş</a>
    </div>
  </div>
</section>

<section class="section section-home-menu" id="menu">
  <h2>Öne çıkanlar</h2>
  <p class="section-intro">En sevilen ızgara ve menüler.</p>
  <div class="menu-grid">
    <?php foreach (array_slice($items, 0, 4) as $item): ?>
      <?php partial('partials/menu_item_card', ['item' => $item, 'showDescription' => false]); ?>
    <?php endforeach; ?>
  </div>
  <div class="cta-row" style="margin-top:28px">
    <a class="btn btn-ghost" href="<?= e(url('/menu')) ?>">Tüm menü</a>
  </div>
</section>
