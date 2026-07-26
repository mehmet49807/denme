<div class="panel-head">
  <div>
    <p class="eyebrow">Yönetici</p>
    <h1>Restoran kontrol</h1>
  </div>
  <div class="muted"><?= e($monthLabel) ?></div>
</div>

<div class="stats">
  <div class="stat"><span class="muted">Aylık sipariş</span><strong><?= (int) ($stats['order_count'] ?? 0) ?></strong></div>
  <div class="stat"><span class="muted">Aylık satış</span><strong><?= e(money((float) ($stats['paid_total'] ?? 0))) ?></strong></div>
  <div class="stat"><span class="muted">Online satış</span><strong><?= e(money((float) ($stats['online_total'] ?? 0))) ?></strong></div>
  <div class="stat"><span class="muted">Garson satış</span><strong><?= e(money((float) ($stats['waiter_total'] ?? 0))) ?></strong></div>
</div>

<div class="order-builder">
  <section class="panel">
    <h2 style="font-family:var(--font-display);margin:0 0 12px">Garson satış istatistikleri</h2>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Garson</th>
            <th>Sipariş</th>
            <th>Aylık satış</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($waiterStats as $row): ?>
            <tr>
              <td><?= e($row['name']) ?></td>
              <td><?= (int) $row['order_count'] ?></td>
              <td><?= e(money((float) $row['sales_total'])) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>

  <section class="panel">
    <h2 style="font-family:var(--font-display);margin:0 0 12px">Personel ekle</h2>
    <form method="post" action="<?= e(url('/yonetici/personel')) ?>" class="stack">
      <?= csrf_field() ?>
      <label>Ad
        <input name="name" required>
      </label>
      <label>Kullanıcı adı
        <input name="username" required>
      </label>
      <label>Parola
        <input type="password" name="password" required minlength="6">
      </label>
      <label>Rol
        <select name="role" required>
          <option value="waiter">Garson</option>
          <option value="cashier">Kasa</option>
          <option value="admin">Yönetici</option>
        </select>
      </label>
      <button class="btn btn-primary" type="submit">Kaydet</button>
    </form>
  </section>
</div>

<section class="panel" style="margin-top:16px">
  <h2 style="font-family:var(--font-display);margin:0 0 12px">Personel takip</h2>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Ad</th>
          <th>Kullanıcı</th>
          <th>Rol</th>
          <th>Durum</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($staff as $member): ?>
          <tr>
            <td><?= e($member['name']) ?></td>
            <td><?= e($member['username']) ?></td>
            <td><?= e($member['role']) ?></td>
            <td><?= !empty($member['is_active']) ? 'Aktif' : 'Pasif' ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

<section class="panel" style="margin-top:16px">
  <h2 style="font-family:var(--font-display);margin:0 0 12px">Garson / online sipariş takibi</h2>
  <div class="order-card-list">
    <?php foreach ($orders as $order): ?>
      <article class="order-card">
        <div class="order-card-head">
          <div>
            <a class="order-code" href="<?= e(url('/garson/fis/' . (int) $order['id'])) ?>"><?= e($order['order_code']) ?></a>
            <div class="small muted"><?= e($order['created_at']) ?> · <?= e($order['waiter_name'] ?? '—') ?></div>
          </div>
          <div class="cta-row">
            <span class="chip <?= e($order['source']) ?>"><?= e(source_label($order['source'])) ?></span>
            <strong class="price"><?= e(money((float) $order['total'])) ?></strong>
          </div>
        </div>
        <div class="small muted" style="margin-bottom:8px">Durum: <?= e(status_label($order['status'])) ?></div>
        <?php partial('partials/order_note', ['order' => $order]); ?>
      </article>
    <?php endforeach; ?>
  </div>
</section>
