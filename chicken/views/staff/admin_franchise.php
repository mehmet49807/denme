<?php
/** @var list<array> $applications */
/** @var array $counts */
/** @var string $filter */
$applications = $applications ?? [];
$counts = $counts ?? ['all' => 0, 'new' => 0, 'reviewing' => 0, 'approved' => 0, 'rejected' => 0];
$filter = $filter ?? '';
?>
<div class="panel-head">
  <div>
    <p class="eyebrow">Yönetici · Franchise</p>
    <h1>Franchise başvuruları</h1>
  </div>
  <div class="cta-row">
    <a class="btn btn-ghost btn-sm" href="<?= e(url('/yonetici/franchise/subeler')) ?>">Şubeler</a>
    <a class="btn btn-ghost btn-sm" href="<?= e(url('/yonetici/franchise/whatsapp')) ?>">WhatsApp</a>
    <a class="btn btn-ghost btn-sm" href="<?= e(url('/bayilik')) ?>" target="_blank" rel="noopener">Kamu sayfası</a>
  </div>
</div>

<?php if ($msg = flash('success')): ?>
  <div class="alert alert-ok"><?= e($msg) ?></div>
<?php endif; ?>
<?php if ($msg = flash('error')): ?>
  <div class="alert alert-error"><?= e($msg) ?></div>
<?php endif; ?>

<?php
  $branchId = (int) ($branchId ?? 0);
  $branches = $branches ?? [];
?>
<div class="chips" style="margin:8px 0 16px">
  <a class="chip <?= $filter === '' ? 'active' : '' ?>" href="<?= e(url('/yonetici/franchise' . ($branchId ? '?sube=' . $branchId : ''))) ?>">Tümü (<?= (int) $counts['all'] ?>)</a>
  <a class="chip <?= $filter === 'new' ? 'active' : '' ?>" href="<?= e(url('/yonetici/franchise?durum=new' . ($branchId ? '&sube=' . $branchId : ''))) ?>">Yeni (<?= (int) $counts['new'] ?>)</a>
  <a class="chip <?= $filter === 'reviewing' ? 'active' : '' ?>" href="<?= e(url('/yonetici/franchise?durum=reviewing' . ($branchId ? '&sube=' . $branchId : ''))) ?>">İnceleniyor (<?= (int) $counts['reviewing'] ?>)</a>
  <a class="chip <?= $filter === 'approved' ? 'active' : '' ?>" href="<?= e(url('/yonetici/franchise?durum=approved' . ($branchId ? '&sube=' . $branchId : ''))) ?>">Onaylı (<?= (int) $counts['approved'] ?>)</a>
  <a class="chip <?= $filter === 'rejected' ? 'active' : '' ?>" href="<?= e(url('/yonetici/franchise?durum=rejected' . ($branchId ? '&sube=' . $branchId : ''))) ?>">Red (<?= (int) $counts['rejected'] ?>)</a>
</div>

<?php if ($branches): ?>
  <form method="get" action="<?= e(url('/yonetici/franchise')) ?>" class="cta-row" style="margin:0 0 16px;align-items:center">
    <?php if ($filter !== ''): ?><input type="hidden" name="durum" value="<?= e($filter) ?>"><?php endif; ?>
    <label class="small muted" style="margin:0">Şube filtresi
      <select name="sube" onchange="this.form.submit()">
        <option value="0">Tüm şubeler</option>
        <?php foreach ($branches as $b): ?>
          <option value="<?= (int) $b['id'] ?>" <?= $branchId === (int) $b['id'] ? 'selected' : '' ?>>
            <?= e((string) $b['name']) ?> (<?= e((string) $b['city']) ?>)
          </option>
        <?php endforeach; ?>
      </select>
    </label>
  </form>
<?php endif; ?>

<section class="panel">
  <?php if (!$applications): ?>
    <p class="muted" style="margin:0">Bu filtrede başvuru yok.</p>
  <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Aday</th>
            <th>İletişim</th>
            <th>Şehir</th>
            <th>Şube</th>
            <th>Bütçe</th>
            <th>Durum</th>
            <th>Tarih</th>
            <th>İşlem</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($applications as $row): ?>
            <?php
              $id = (int) $row['id'];
              $status = (string) $row['status'];
            ?>
            <tr>
              <td><?= $id ?></td>
              <td>
                <strong><?= e((string) $row['full_name']) ?></strong>
                <?php if (!empty($row['experience'])): ?>
                  <div class="small muted"><?= e(mb_strimwidth((string) $row['experience'], 0, 80, '…')) ?></div>
                <?php endif; ?>
              </td>
              <td class="small">
                <div><a href="tel:<?= e((string) $row['phone']) ?>"><?= e((string) $row['phone']) ?></a></div>
                <div><a href="mailto:<?= e((string) $row['email']) ?>"><?= e((string) $row['email']) ?></a></div>
              </td>
              <td>
                <?= e((string) $row['city']) ?>
                <?php if (!empty($row['district'])): ?>
                  <div class="small muted"><?= e((string) $row['district']) ?></div>
                <?php endif; ?>
              </td>
              <td class="small">
                <?= e((string) ($row['branch_name'] ?? '—')) ?>
                <?php if (!empty($row['branch_city'])): ?>
                  <div class="muted"><?= e((string) $row['branch_city']) ?></div>
                <?php endif; ?>
              </td>
              <td class="small"><?= e((string) ($row['budget_range'] ?: '—')) ?></td>
              <td>
                <span class="chip <?= $status === 'new' ? 'kitchen' : ($status === 'approved' ? 'online' : '') ?>">
                  <?= e(FranchiseService::statusLabel($status)) ?>
                </span>
              </td>
              <td class="small muted"><?= e((string) ($row['created_at'] ?? '')) ?></td>
              <td style="min-width:220px">
                <details>
                  <summary class="btn btn-ghost btn-sm" style="cursor:pointer;list-style:none">Detay / güncelle</summary>
                  <div class="stack" style="margin-top:10px;padding:10px;border:1px solid var(--line);border-radius:12px;background:#fff">
                    <?php if (!empty($row['message'])): ?>
                      <p class="small" style="margin:0"><strong>Mesaj:</strong> <?= e((string) $row['message']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($row['experience'])): ?>
                      <p class="small" style="margin:0"><strong>Tecrübe:</strong> <?= e((string) $row['experience']) ?></p>
                    <?php endif; ?>
                    <form method="post" action="<?= e(url('/yonetici/franchise/' . $id . '/durum')) ?>" class="stack">
                      <?= csrf_field() ?>
                      <label>Durum
                        <select name="status">
                          <?php foreach (FranchiseService::STATUSES as $st): ?>
                            <option value="<?= e($st) ?>" <?= $status === $st ? 'selected' : '' ?>>
                              <?= e(FranchiseService::statusLabel($st)) ?>
                            </option>
                          <?php endforeach; ?>
                        </select>
                      </label>
                      <label>Yönetici notu
                        <textarea name="admin_note" rows="2" placeholder="İç not..."><?= e((string) ($row['admin_note'] ?? '')) ?></textarea>
                      </label>
                      <button class="btn btn-accent btn-sm" type="submit">Kaydet</button>
                    </form>
                  </div>
                </details>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</section>
