<?php
/** @var array $user */
/** @var array $staffOptions */
/** @var string $backUrl */
/** @var string $formAction */
/** @var string $roleLabel */
/** @var bool $canPickOpener */
$staffOptions = $staffOptions ?? [];
$backUrl = $backUrl ?? url('/yonetici/masalar');
$formAction = $formAction ?? url('/masa/ekle');
$roleLabel = $roleLabel ?? 'Yönetici';
$canPickOpener = (bool) ($canPickOpener ?? false);
$currentId = (int) ($user['id'] ?? 0);
$currentName = (string) ($user['name'] ?? '');
?>
<div class="panel-head">
  <div>
    <p class="eyebrow"><?= e($roleLabel) ?> · Masalar</p>
    <h1>Yeni masa ekle</h1>
  </div>
  <a class="btn btn-ghost btn-sm" href="<?= e($backUrl) ?>">Geri</a>
</div>

<section class="panel table-add-panel">
  <form method="post" action="<?= e($formAction) ?>" class="stack table-add-form">
    <?= csrf_field() ?>
    <label>Masa No
      <input
        name="masa_no"
        required
        maxlength="20"
        inputmode="numeric"
        placeholder="Örn: 12"
        autocomplete="off"
      >
    </label>
    <label>Kaç kişi
      <input type="number" name="seats" min="1" max="50" value="4" required>
    </label>
    <label>Masa açan kişi
      <?php if ($canPickOpener && $staffOptions): ?>
        <select name="opened_by_staff_id" required>
          <option value="">Seçin</option>
          <?php foreach ($staffOptions as $staff): ?>
            <?php
              $roleTr = match ($staff['role']) {
                  'waiter' => 'Garson',
                  'cashier' => 'Kasa',
                  'admin' => 'Yönetici',
                  default => (string) $staff['role'],
              };
            ?>
            <option
              value="<?= (int) $staff['id'] ?>"
              <?= (int) $staff['id'] === $currentId ? 'selected' : '' ?>
            >
              <?= e($staff['name']) ?> (<?= e($roleTr) ?>)
            </option>
          <?php endforeach; ?>
        </select>
      <?php else: ?>
        <input type="text" value="<?= e($currentName) ?>" readonly>
        <input type="hidden" name="opened_by_staff_id" value="<?= $currentId ?>">
        <p class="muted small" style="margin:6px 0 0">Garson masayı kendi adı ile açar.</p>
      <?php endif; ?>
    </label>
    <button class="btn btn-primary" type="submit">Masayı kaydet</button>
  </form>
</section>
