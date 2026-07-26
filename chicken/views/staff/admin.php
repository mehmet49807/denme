<div class="panel-head">
  <div>
    <p class="eyebrow">Yönetici</p>
    <h1>Restoran kontrol</h1>
  </div>
</div>

<p class="muted" style="margin:-8px 0 18px">Sol menüden masalar, satışlar, siparişler ve personel işlemlerine geçin. Üst menüden Garson / Kasa / Mutfak / Bar yetkilerine de erişebilirsiniz.</p>

<div class="admin-quick-grid">
  <a class="admin-quick" href="<?= e(url('/yonetici/masalar')) ?>">
    <?php partial('partials/menu_icon', ['icon' => 'tables', 'color' => '#ff6a1a']); ?>
    <strong>Tüm masalar</strong>
    <span class="muted small">Masa durumu ve yönetim</span>
  </a>
  <a class="admin-quick" href="<?= e(url('/yonetici/masalar/ekle')) ?>">
    <?php partial('partials/menu_icon', ['icon' => 'add', 'color' => '#3d9a6a']); ?>
    <strong>Masa ekleme</strong>
    <span class="muted small">Yeni masa + QR</span>
  </a>
  <a class="admin-quick" href="<?= e(url('/yonetici/urunler')) ?>">
    <?php partial('partials/menu_icon', ['icon' => 'burger', 'color' => '#f0b429']); ?>
    <strong>Ürünler</strong>
    <span class="muted small">Fiyat / satış durumu</span>
  </a>
  <a class="admin-quick" href="<?= e(url('/yonetici/urunler/ekle')) ?>">
    <?php partial('partials/menu_icon', ['icon' => 'add', 'color' => '#2bb3a3']); ?>
    <strong>Ürün ekle</strong>
    <span class="muted small">Yeni menü ürünü</span>
  </a>
  <a class="admin-quick" href="<?= e(url('/yonetici/istatistikler')) ?>">
    <?php partial('partials/menu_icon', ['icon' => 'stats', 'color' => '#e2b457']); ?>
    <strong>Satış istatistikleri</strong>
    <span class="muted small">Günlük / aylık satış</span>
  </a>
  <a class="admin-quick" href="<?= e(url('/yonetici/siparisler')) ?>">
    <?php partial('partials/menu_icon', ['icon' => 'orders', 'color' => '#4c8dff']); ?>
    <strong>Siparişler</strong>
    <span class="muted small">Tüm sipariş takibi</span>
  </a>
  <a class="admin-quick" href="<?= e(url('/yonetici/personel-istatistik')) ?>">
    <?php partial('partials/menu_icon', ['icon' => 'staffstats', 'color' => '#f0b429']); ?>
    <strong>Kasa & Garson</strong>
    <span class="muted small">Personel satışları</span>
  </a>
  <a class="admin-quick" href="<?= e(url('/yonetici/personel/ekle')) ?>">
    <?php partial('partials/menu_icon', ['icon' => 'staffadd', 'color' => '#2bb3a3']); ?>
    <strong>Personel ekle</strong>
    <span class="muted small">Yeni kullanıcı</span>
  </a>
  <a class="admin-quick" href="<?= e(url('/yonetici/personel')) ?>">
    <?php partial('partials/menu_icon', ['icon' => 'staff', 'color' => '#e0a33b']); ?>
    <strong>Personel takip</strong>
    <span class="muted small">Aktif personel listesi</span>
  </a>
  <a class="admin-quick" href="<?= e(url('/yonetici/personel/cikar')) ?>">
    <?php partial('partials/menu_icon', ['icon' => 'staffremove', 'color' => '#ff7a7a']); ?>
    <strong>Personel çıkarma</strong>
    <span class="muted small">Pasife alma</span>
  </a>
</div>
