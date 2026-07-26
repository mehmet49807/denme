<?php
$groups = [
    [
        'title' => 'Masalar',
        'items' => [
            ['href' => '/yonetici/masalar', 'label' => 'Tüm masalar', 'hint' => 'Durum ve yönetim', 'icon' => 'tables', 'color' => '#ff6a1a'],
            ['href' => '/yonetici/masalar/ekle', 'label' => 'Masa ekleme', 'hint' => 'Yeni masa + QR', 'icon' => 'add', 'color' => '#3d9a6a'],
        ],
    ],
    [
        'title' => 'Menü',
        'items' => [
            ['href' => '/yonetici/urunler', 'label' => 'Ürünler', 'hint' => 'Fiyat / satış durumu', 'icon' => 'burger', 'color' => '#f0b429'],
            ['href' => '/yonetici/urunler/ekle', 'label' => 'Ürün ekle', 'hint' => 'Yeni menü ürünü', 'icon' => 'add', 'color' => '#2bb3a3'],
        ],
    ],
    [
        'title' => 'Satış',
        'items' => [
            ['href' => '/yonetici/istatistikler', 'label' => 'Satış istatistikleri', 'hint' => 'Günlük / aylık', 'icon' => 'stats', 'color' => '#e2b457'],
            ['href' => '/yonetici/siparisler', 'label' => 'Siparişler', 'hint' => 'Tüm sipariş takibi', 'icon' => 'orders', 'color' => '#4c8dff'],
            ['href' => '/yonetici/personel-istatistik', 'label' => 'Kasa ve Garson', 'hint' => 'Personel satışları', 'icon' => 'staffstats', 'color' => '#f0b429'],
        ],
    ],
    [
        'title' => 'Personel',
        'items' => [
            ['href' => '/yonetici/personel/ekle', 'label' => 'Personel ekle', 'hint' => 'Yeni kullanıcı', 'icon' => 'staffadd', 'color' => '#2bb3a3'],
            ['href' => '/yonetici/personel', 'label' => 'Personel takip', 'hint' => 'Aktif liste', 'icon' => 'staff', 'color' => '#e0a33b'],
            ['href' => '/yonetici/personel/cikar', 'label' => 'Personel çıkarma', 'hint' => 'Pasife alma', 'icon' => 'staffremove', 'color' => '#ff7a7a'],
        ],
    ],
];
?>
<div class="panel-head">
  <div>
    <p class="eyebrow">Yönetici</p>
    <h1>Restoran kontrol</h1>
  </div>
</div>

<p class="admin-lead muted">Menüden seçin. Üstten Garson, Kasa, Mutfak ve Bar’a da geçebilirsiniz.</p>

<div class="admin-home">
  <?php foreach ($groups as $group): ?>
    <section class="admin-home-group">
      <h2 class="admin-home-title"><?= e($group['title']) ?></h2>
      <div class="admin-home-list">
        <?php foreach ($group['items'] as $item): ?>
          <a class="admin-home-link" href="<?= e(url($item['href'])) ?>">
            <?php partial('partials/menu_icon', ['icon' => $item['icon'], 'color' => $item['color']]); ?>
            <span class="admin-home-text">
              <strong><?= e($item['label']) ?></strong>
              <span class="muted small"><?= e($item['hint']) ?></span>
            </span>
            <span class="admin-home-chevron" aria-hidden="true">›</span>
          </a>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endforeach; ?>
</div>

