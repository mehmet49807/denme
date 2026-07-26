<?php
/** @var string $icon */
/** @var string $color */
$icon = $icon ?? 'all';
$color = $color ?? '#e2b457';
$safe = preg_match('/^#([0-9A-Fa-f]{6})$/', $color, $m) ? ('#' . strtolower($m[1])) : '#e2b457';
$r = hexdec(substr($safe, 1, 2));
$g = hexdec(substr($safe, 3, 2));
$b = hexdec(substr($safe, 5, 2));
$bg = "rgba({$r},{$g},{$b},0.18)";
$bd = "rgba({$r},{$g},{$b},0.4)";
?>
<span class="menu-ico" style="--ico:<?= e($safe) ?>;color:<?= e($safe) ?>;background:<?= e($bg) ?>;border-color:<?= e($bd) ?>" aria-hidden="true">
<?php
$svgOpen = '<svg class="menu-ico-svg" width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false">';
$s = 'stroke="' . e($safe) . '"';
?>
<?php if ($icon === 'waiter'): ?>
  <?= $svgOpen ?><circle cx="12" cy="8" r="3.5" <?= $s ?> stroke-width="1.8"/><path d="M5.5 19.5c1.4-3.2 3.7-4.8 6.5-4.8s5.1 1.6 6.5 4.8" <?= $s ?> stroke-width="1.8" stroke-linecap="round"/></svg>
<?php elseif ($icon === 'grill'): ?>
  <?= $svgOpen ?><path d="M8 3c0 2-1.2 3-1.2 5S8 11 8 13" <?= $s ?> stroke-width="1.8" stroke-linecap="round"/><path d="M12 2c0 2.2-1.4 3.2-1.4 5.2S12 11 12 13.2" <?= $s ?> stroke-width="1.8" stroke-linecap="round"/><path d="M16 3c0 2-1.2 3-1.2 5S16 11 16 13" <?= $s ?> stroke-width="1.8" stroke-linecap="round"/><rect x="4" y="14" width="16" height="6" rx="2" <?= $s ?> stroke-width="1.8"/></svg>
<?php elseif ($icon === 'menu'): ?>
  <?= $svgOpen ?><rect x="4" y="4" width="16" height="16" rx="3" <?= $s ?> stroke-width="1.8"/><path d="M8 9h8M8 12.5h8M8 16h5" <?= $s ?> stroke-width="1.8" stroke-linecap="round"/></svg>
<?php elseif ($icon === 'wrap'): ?>
  <?= $svgOpen ?><path d="M5 16.5c2.2 2.8 5 4 7 4s4.8-1.2 7-4c-1.2-4.8-3.8-9.5-7-12.5-3.2 3-5.8 7.7-7 12.5Z" <?= $s ?> stroke-width="1.8"/><path d="M8.5 14.5c1.2.8 2.4 1.2 3.5 1.2" <?= $s ?> stroke-width="1.8" stroke-linecap="round"/></svg>
<?php elseif ($icon === 'burger'): ?>
  <?= $svgOpen ?><path d="M5 10.5c0-3.2 3.1-5.5 7-5.5s7 2.3 7 5.5H5Z" <?= $s ?> stroke-width="1.8"/><path d="M4.5 13h15" <?= $s ?> stroke-width="2.2" stroke-linecap="round"/><path d="M5 15.5h14c0 2.8-3.1 4.5-7 4.5s-7-1.7-7-4.5Z" <?= $s ?> stroke-width="1.8"/></svg>
<?php elseif ($icon === 'sides'): ?>
  <?= $svgOpen ?><path d="M8 4l-1 10h10L16 4" <?= $s ?> stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M10 4v10M12.5 4v10M15 4v10" <?= $s ?> stroke-width="1.6" stroke-linecap="round"/><path d="M7 18h10l-1 2H8l-1-2Z" <?= $s ?> stroke-width="1.8" stroke-linejoin="round"/></svg>
<?php elseif ($icon === 'dessert'): ?>
  <?= $svgOpen ?><path d="M7 11c0-3 2.2-5.5 5-5.5s5 2.5 5 5.5H7Z" <?= $s ?> stroke-width="1.8"/><path d="M6 12.5h12l-1.2 6.2A2 2 0 0 1 14.8 20H9.2a2 2 0 0 1-2-1.3L6 12.5Z" <?= $s ?> stroke-width="1.8"/><path d="M12 5.5V3.5" <?= $s ?> stroke-width="1.8" stroke-linecap="round"/></svg>
<?php elseif ($icon === 'drinks'): ?>
  <?= $svgOpen ?><path d="M8 4h9l-1.2 14.2A2 2 0 0 1 13.8 20h-2.6a2 2 0 0 1-2-1.8L8 4Z" <?= $s ?> stroke-width="1.8"/><path d="M9 9h7.2" <?= $s ?> stroke-width="1.8" stroke-linecap="round"/><path d="M15.5 4c.8-1.2 1.8-1.8 3-2" <?= $s ?> stroke-width="1.8" stroke-linecap="round"/></svg>
<?php elseif ($icon === 'cashier'): ?>
  <?= $svgOpen ?><rect x="3.5" y="6" width="17" height="12" rx="2" <?= $s ?> stroke-width="1.8"/><path d="M7 10h4M7 13h6" <?= $s ?> stroke-width="1.8" stroke-linecap="round"/><circle cx="16" cy="12" r="1.4" fill="<?= e($safe) ?>"/></svg>
<?php elseif ($icon === 'logout'): ?>
  <?= $svgOpen ?><path d="M10 4.5H7.5A2.5 2.5 0 0 0 5 7v10a2.5 2.5 0 0 0 2.5 2.5H10" <?= $s ?> stroke-width="1.8" stroke-linecap="round"/><path d="M13 12h7" <?= $s ?> stroke-width="1.8" stroke-linecap="round"/><path d="M17.5 8.5 21 12l-3.5 3.5" <?= $s ?> stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
<?php else: ?>
  <?= $svgOpen ?><rect x="4" y="4" width="6.5" height="6.5" rx="1.5" <?= $s ?> stroke-width="1.8"/><rect x="13.5" y="4" width="6.5" height="6.5" rx="1.5" <?= $s ?> stroke-width="1.8"/><rect x="4" y="13.5" width="6.5" height="6.5" rx="1.5" <?= $s ?> stroke-width="1.8"/><rect x="13.5" y="13.5" width="6.5" height="6.5" rx="1.5" <?= $s ?> stroke-width="1.8"/></svg>
<?php endif; ?>
</span>
