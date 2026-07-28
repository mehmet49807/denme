<section class="sg-hero">
  <div class="sg-hero-media" aria-hidden="true"></div>
  <div class="sg-hero-copy">
    <p class="sg-kicker">Lezzetin doğal adresi</p>
    <h1>
      <span>CRISP</span>
      <span class="sg-amp">&amp;</span>
      <span>CO.</span>
    </h1>
    <p class="sg-lead">Izgara tavuk. Doğal lezzet. Üye olmadan sipariş.</p>
    <a class="sg-btn" href="<?= e(url('/siparis')) ?>">Online sipariş</a>
  </div>
</section>

<section class="sg-block" id="menu">
  <div class="sg-block-head">
    <h2>Izgara seçkisi</h2>
    <a href="<?= e(url('/menu')) ?>">Tüm menü</a>
  </div>
  <div class="sg-zigzag">
    <?php foreach (array_slice($items, 0, 4) as $i => $item): ?>
      <?php
        $image = class_exists('MenuImageSync')
          ? MenuImageSync::resolve($item)
          : trim((string) ($item['image_url'] ?? ''));
        $imageSrc = $image !== '' ? asset_url($image) : '';
      ?>
      <article class="sg-dish<?= $i % 2 === 1 ? ' is-flip' : '' ?>">
        <div class="sg-dish-visual"<?= $imageSrc !== '' ? ' style="background-image:url(\'' . e($imageSrc) . '\')"' : '' ?>>
          <?php if ($imageSrc !== ''): ?>
            <img src="<?= e($imageSrc) ?>" alt="<?= e((string) $item['name']) ?>" loading="eager" width="640" height="480">
          <?php endif; ?>
        </div>
        <div class="sg-dish-copy">
          <p class="sg-dish-price"><?= e(money((float) $item['price'])) ?></p>
          <h3><?= e((string) $item['name']) ?></h3>
          <button
            class="sg-btn sg-btn-ghost"
            type="button"
            data-add-item="<?= (int) $item['id'] ?>"
            data-name="<?= e((string) $item['name']) ?>"
            data-price="<?= e((string) $item['price']) ?>"
            data-station="<?= e((string) $item['station']) ?>"
          >Sepete ekle</button>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
  <p class="sg-hint">Yeni üyelere <b>YENI10</b> ile %10 indirim</p>
</section>

<section class="sg-block sg-about" id="hakkimizda-ozet">
  <p class="sg-kicker">Marka</p>
  <h2>Crisp &amp; Co. ile tanışın</h2>
  <p class="sg-lead">Taze ızgara, hızlı servis, net iletişim.</p>
  <div class="sg-about-links">
    <a href="<?= e(url('/hakkimizda')) ?>">Hakkımızda</a>
    <a href="<?= e(url('/misyon')) ?>">Misyon</a>
    <a href="<?= e(url('/musteri-memnuniyeti')) ?>">Memnuniyet</a>
  </div>
</section>
