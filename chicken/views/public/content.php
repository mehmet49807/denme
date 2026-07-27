<?php
/** @var string $title */
/** @var string $eyebrow */
/** @var string $heading */
/** @var array<int, array{title?:string,body:string}> $sections */
$eyebrow = $eyebrow ?? '';
$heading = $heading ?? ($title ?? '');
$sections = $sections ?? [];
?>
<div class="page-shell content-page">
  <?php if ($eyebrow !== ''): ?>
    <p class="eyebrow"><?= e($eyebrow) ?></p>
  <?php endif; ?>
  <h1 class="page-title"><?= e($heading) ?></h1>
  <div class="content-prose">
    <?php foreach ($sections as $section): ?>
      <?php if (!empty($section['title'])): ?>
        <h2><?= e((string) $section['title']) ?></h2>
      <?php endif; ?>
      <p><?= nl2br(e((string) ($section['body'] ?? ''))) ?></p>
    <?php endforeach; ?>
  </div>
  <div class="cta-row" style="margin-top:28px">
    <a class="btn btn-ghost" href="<?= e(url('/')) ?>">Ana sayfa</a>
    <a class="btn btn-accent" href="<?= e(url('/uye-ol')) ?>">Üye ol</a>
  </div>
</div>
