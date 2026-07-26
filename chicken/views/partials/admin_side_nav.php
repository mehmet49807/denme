<?php
$groups = [
    [
        'title' => 'Masalar',
        'items' => [
            ['href' => '/yonetici/masalar', 'label' => 'Tüm masalar', 'icon' => 'tables', 'color' => '#ff6a1a'],
            ['href' => '/yonetici/masalar/ekle', 'label' => 'Masa ekleme', 'icon' => 'add', 'color' => '#3d9a6a'],
        ],
    ],
    [
        'title' => 'Menü',
        'items' => [
            ['href' => '/yonetici/urunler', 'label' => 'Ürünler', 'icon' => 'burger', 'color' => '#f0b429'],
            ['href' => '/yonetici/urunler/ekle', 'label' => 'Ürün ekle', 'icon' => 'add', 'color' => '#2bb3a3'],
        ],
    ],
    [
        'title' => 'Satış',
        'items' => [
            ['href' => '/yonetici/istatistikler', 'label' => 'Satış istatistikleri', 'icon' => 'stats', 'color' => '#e2b457'],
            ['href' => '/yonetici/siparisler', 'label' => 'Siparişler', 'icon' => 'orders', 'color' => '#4c8dff'],
            ['href' => '/yonetici/personel-istatistik', 'label' => 'Kasa ve Garson', 'icon' => 'staffstats', 'color' => '#f0b429'],
        ],
    ],
    [
        'title' => 'Personel',
        'items' => [
            ['href' => '/yonetici/personel/ekle', 'label' => 'Personel ekle', 'icon' => 'staffadd', 'color' => '#2bb3a3'],
            ['href' => '/yonetici/personel', 'label' => 'Personel takip', 'icon' => 'staff', 'color' => '#e0a33b'],
            ['href' => '/yonetici/personel/cikar', 'label' => 'Personel çıkarma', 'icon' => 'staffremove', 'color' => '#ff7a7a'],
        ],
    ],
];
?>
<nav class="side-cats admin-side-nav" data-admin-nav>
  <?php foreach ($groups as $group): ?>
    <p class="side-group-title"><?= e($group['title']) ?></p>
    <?php foreach ($group['items'] as $item): ?>
      <a
        class="side-cat <?= admin_nav_active($item['href']) ? 'active' : '' ?>"
        href="<?= e(url($item['href'])) ?>"
      >
        <?php partial('partials/menu_icon', ['icon' => $item['icon'], 'color' => $item['color']]); ?>
        <span><?= e($item['label']) ?></span>
      </a>
    <?php endforeach; ?>
  <?php endforeach; ?>
</nav>
