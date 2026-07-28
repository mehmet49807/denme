<section class="hero hero-day">
  <div class="hero-media" aria-hidden="true"></div>
  <div class="hero-content">
    <p class="hero-kicker">Lezzetin doğal adresi</p>
    <h1 class="hero-title">
      <span class="hero-title-main">Crisp</span>
      <span class="hero-title-and">&amp;</span>
      <span class="hero-title-main">Co.</span>
    </h1>
    <p class="lede">Izgara tavuk, doğal lezzet — üye olmadan sipariş verin.</p>
    <div class="cta-home">
      <a class="btn btn-accent" href="<?= e(url('/siparis')) ?>">Online sipariş</a>
    </div>
  </div>
</section>

<section class="section section-home-menu" id="menu">
  <header class="section-head">
    <div>
      <p class="eyebrow">Mutfak</p>
      <h2>Bugünün ızgarası</h2>
    </div>
    <a class="text-link" href="<?= e(url('/menu')) ?>">Tüm menü →</a>
  </header>
  <div class="menu-rail">
    <?php foreach (array_slice($items, 0, 4) as $item): ?>
      <?php partial('partials/menu_item_card', ['item' => $item, 'showDescription' => false, 'showAdd' => true]); ?>
    <?php endforeach; ?>
  </div>
  <p class="section-foot-note">Yeni üyelere <strong>YENI10</strong> ile %10 indirim</p>
</section>

<section class="section section-home-about" id="hakkimizda-ozet">
  <header class="section-head">
    <div>
      <p class="eyebrow">Marka</p>
      <h2>Neden Crisp &amp; Co.</h2>
    </div>
  </header>
  <ol class="story-list">
    <li>
      <a href="<?= e(url('/hakkimizda')) ?>">
        <span class="story-num">01</span>
        <span class="story-copy">
          <strong>Hakkımızda</strong>
          <span>Kim olduğumuz ve neyi önemsediğimiz</span>
        </span>
        <span class="story-go" aria-hidden="true">→</span>
      </a>
    </li>
    <li>
      <a href="<?= e(url('/misyon')) ?>">
        <span class="story-num">02</span>
        <span class="story-copy">
          <strong>Misyonumuz</strong>
          <span>Lezzet, hız ve güvenilir hizmet</span>
        </span>
        <span class="story-go" aria-hidden="true">→</span>
      </a>
    </li>
    <li>
      <a href="<?= e(url('/musteri-memnuniyeti')) ?>">
        <span class="story-num">03</span>
        <span class="story-copy">
          <strong>Müşteri memnuniyeti</strong>
          <span>Siparişten teslimata kadar yanınızdayız</span>
        </span>
        <span class="story-go" aria-hidden="true">→</span>
      </a>
    </li>
  </ol>
</section>
