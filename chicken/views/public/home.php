<section class="hero">
  <div class="hero-media" aria-hidden="true"></div>
  <div class="hero-content">
    <p class="eyebrow">Chicken Grill House</p>
    <h1>CHICKEN</h1>
    <p class="lede">Izgara tavuk, sıcak bar, hızlı servis. QR menüden bakın, online sipariş verin, siparişinizi anlık takip edin.</p>
    <div class="cta-row">
      <a class="btn btn-primary" href="<?= e(url('/menu')) ?>">QR Menüyü Aç</a>
      <a class="btn btn-ghost" href="<?= e(url('/siparis')) ?>">Online Sipariş</a>
    </div>
  </div>
</section>

<section class="section" id="menu">
  <h2>Öne çıkan lezzetler</h2>
  <p class="section-intro">Mutfak ve bar ayrı fişlerle çalışır. Her siparişin benzersiz kimliği vardır.</p>
  <div class="menu-grid">
    <?php foreach (array_slice($items, 0, 6) as $item): ?>
      <article class="menu-item">
        <div class="meta-row">
          <span class="chip <?= e($item['station']) ?>"><?= e(station_label($item['station'])) ?></span>
          <span class="price"><?= e(money((float) $item['price'])) ?></span>
        </div>
        <h3><?= e($item['name']) ?></h3>
        <p><?= e($item['description'] ?? '') ?></p>
      </article>
    <?php endforeach; ?>
  </div>
</section>
