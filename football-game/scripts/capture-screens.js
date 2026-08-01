import { chromium } from 'playwright';
import { mkdirSync } from 'fs';
import { join } from 'path';

const outDir = '/opt/cursor/artifacts/screenshots';
mkdirSync(outDir, { recursive: true });

const browser = await chromium.launch({
  headless: true,
  args: ['--use-gl=angle', '--ignore-gpu-blocklist', '--enable-webgl'],
});

const context = await browser.newContext({
  viewport: { width: 844, height: 390 },
  deviceScaleFactor: 2,
  isMobile: true,
  hasTouch: true,
  locale: 'tr-TR',
});

const page = await context.newPage();
await page.goto('http://127.0.0.1:4173/', { waitUntil: 'networkidle' });
await page.waitForTimeout(2500);

async function shot(name) {
  const path = join(outDir, name);
  await page.screenshot({ path, fullPage: false });
  console.log('saved', path);
}

await page.evaluate(() => localStorage.clear());
await page.reload({ waitUntil: 'networkidle' });
await page.waitForTimeout(3000);
await shot('01-giris.png');

await page.fill('#input-manager', 'Mehmet Uzun');
await page.click('#btn-guest');
await page.waitForSelector('#screen-team:not(.hidden)');
await page.waitForTimeout(700);
await shot('02-takim-olustur.png');

await page.fill('#input-team-name', 'Kuzey Kartal');
await page.fill('#input-team-short', 'KK');
await page.click('#btn-logo-random');
await page.waitForTimeout(400);
await shot('03-logo-editor.png');

await page.click('#btn-create-team');
await page.waitForSelector('#screen-lobby:not(.hidden)');
await page.waitForTimeout(700);
await shot('04-lobi-kadro.png');

await page.click('#btn-start');
await page.waitForSelector('#hud:not(.hidden)');
await page.waitForTimeout(2000);
await shot('05-mac.png');

await page.waitForTimeout(2500);
await shot('06-mac-oyun.png');

await browser.close();
console.log('done');
