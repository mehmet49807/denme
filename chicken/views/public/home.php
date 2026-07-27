<section class="hero">
  <div class="hero-media" aria-hidden="true"></div>
  <div class="hero-content">
    <div class="hero-brand">
      <img class="hero-logo" src="<?= e(logo_url()) ?>" alt="Crisp &amp; Co." width="168" height="168">
      <p class="eyebrow">Lezzetin doğal adresi</p>
    </div>
    <h1>CRISP &amp; CO.</h1>
    <p class="lede">Taze ızgara tavuk, net menü ve online sipariş. Üye olmadan da sipariş verebilirsiniz.</p>
    <div class="cta-home">
      <a class="btn btn-accent" href="<?= e(url('/siparis')) ?>">Online Sipariş</a>
      <a class="btn btn-ghost" href="<?= e(url('/menu')) ?>">Menüyü gör</a>
      <p class="cta-note">Üye olanlara <strong>YENI10</strong> ile %10 indirim</p>
    </div>
  </div>
</section>

<section class="section section-home-menu" id="menu">
  <h2>Öne çıkanlar</h2>
  <p class="section-intro">En sevilen ızgara ve menüler.</p>
  <div class="menu-grid">
    <?php foreach (array_slice($items, 0, 4) as $item): ?>
      <?php partial('partials/menu_item_card', ['item' => $item, 'showDescription' => false, 'showAdd' => true]); ?>
    <?php endforeach; ?>
  </div>
  <div class="cta-row" style="margin-top:28px">
    <a class="btn btn-ghost" href="<?= e(url('/menu')) ?>">Tüm menü</a>
    <a class="btn btn-accent" href="<?= e(url('/siparis')) ?>">Sepete git</a>
  </div>
</section>

<section class="section section-home-about" id="hakkimizda-ozet">
  <h2>Crisp &amp; Co. hakkında</h2>
  <p class="section-intro">Lezzetin doğal adresi — taze ızgara, hızlı servis ve şeffaf iletişim.</p>
  <div class="about-links">
    <a class="about-link" href="<?= e(url('/hakkimizda')) ?>">
      <span class="about-link-icon" aria-hidden="true">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M12 3l8 4.5v9L12 21l-8-4.5v-9L12 3z" stroke="currentColor" stroke-width="1.8"/><path d="M12 12l8-4.5M12 12v9M12 12L4 7.5" stroke="currentColor" stroke-width="1.8"/></svg>
      </span>
      <strong>Hakkımızda</strong>
      <span>Kim olduğumuz ve neyi önemsediğimiz</span>
    </a>
    <a class="about-link" href="<?= e(url('/misyon')) ?>">
      <span class="about-link-icon" aria-hidden="true">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="12" r="3" fill="currentColor"/></svg>
      </span>
      <strong>Misyonumuz</strong>
      <span>Lezzet, hız ve güvenilir hizmet</span>
    </a>
    <a class="about-link" href="<?= e(url('/musteri-memnuniyeti')) ?>">
      <span class="about-link-icon" aria-hidden="true">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M12 21s-7-4.4-7-10a4 4 0 017-2.6A4 4 0 0119 11c0 5.6-7 10-7 10z" stroke="currentColor" stroke-width="1.8"/></svg>
      </span>
      <strong>Müşteri memnuniyeti</strong>
      <span>Siparişten teslimata kadar yanınızdayız</span>
    </a>
  </div>
</section>
