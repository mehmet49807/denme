<div class="panel-head">
  <div>
    <p class="eyebrow">Garson paneli</p>
    <h1>Masa siparişi</h1>
  </div>
  <div class="cta-row">
    <button class="btn btn-ghost btn-sm" type="button" data-nav-toggle>Kategoriler</button>
    <a class="btn btn-primary btn-sm" href="<?= e(url('/siparisler')) ?>">
      Siparişler <span class="chip" data-cart-badge hidden>0</span>
    </a>
  </div>
</div>

<div class="menu-grid menu-grid-compact" data-waiter-menu>
  <?php foreach ($items as $item): ?>
    <?php partial('partials/menu_item_card', ['item' => $item, 'showAdd' => true]); ?>
  <?php endforeach; ?>
</div>
