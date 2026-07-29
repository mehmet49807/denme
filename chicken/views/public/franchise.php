<?php
/** @var string $title */
?>
<div class="page-shell vs-franchise">
  <p class="vs-tag">Franchise</p>
  <h1 class="page-title">Crisp &amp; Co. Bayilik</h1>
  <p class="lede">Lezzetin doğal adresi markasıyla kendi şehrinizde restoran açmak ister misiniz? Başvurunuzu inceleyelim.</p>

  <?php if ($msg = flash('success')): ?>
    <div class="alert alert-ok"><?= e($msg) ?></div>
  <?php endif; ?>
  <?php if ($msg = flash('error')): ?>
    <div class="alert alert-error"><?= e($msg) ?></div>
  <?php endif; ?>

  <section class="panel" style="margin-top:18px">
    <h2 style="margin:0 0 10px">Neden Crisp &amp; Co.?</h2>
    <ul class="franchise-list">
      <li>Izgara tavuk odaklı, net ve ölçeklenebilir menü</li>
      <li>Online sipariş, QR menü ve operasyon paneli altyapısı</li>
      <li>Marka kimliği, ürün standartları ve eğitim desteği</li>
      <li>Yerel pazara uygun franchise iş modeli</li>
    </ul>
  </section>

  <section class="panel" style="margin-top:16px" id="sartlar">
    <h2 style="margin:0 0 10px">Başvuru şartları</h2>
    <ol class="franchise-list numbered">
      <li>Türkiye’de resmi iş kurma yeterliliği ve vergi mükellefiyeti</li>
      <li>Tercihen yiyecek-içecek veya perakende operasyon tecrübesi</li>
      <li>Lokasyon için uygun yatırım bütçesi ve işletme sermayesi</li>
      <li>Marka standartlarına, menü ve görsel kimliğe tam uyum</li>
      <li>Personel eğitimi, hijyen ve servis süreçlerine bağlılık</li>
      <li>Başvuru formundaki bilgilerin doğru ve güncel olması</li>
    </ol>
    <p class="muted small" style="margin:12px 0 0">
      Başvuru, sözleşme teklifi değildir. Değerlendirme sonrası uygun görülen adaylarla görüşme planlanır.
      Yatırım tutarı, lokasyon ve dönem koşullarına göre değişkenlik gösterebilir.
    </p>
  </section>

  <section class="panel" style="margin-top:16px" id="basvuru">
    <h2 style="margin:0 0 10px">Franchise başvurusu</h2>
    <p class="muted" style="margin:0 0 14px">Formu doldurun; ekibimiz en kısa sürede dönüş yapsın.</p>

    <form method="post" action="<?= e(url('/bayilik')) ?>" class="stack" style="max-width:640px">
      <?= csrf_field() ?>
      <label>Ad Soyad
        <input name="name" required autocomplete="name" placeholder="Adınız Soyadınız" value="<?= e((string) ($_POST['name'] ?? '')) ?>">
      </label>
      <div class="franchise-grid">
        <label>Telefon
          <input name="phone" required autocomplete="tel" placeholder="05xx..." value="<?= e((string) ($_POST['phone'] ?? '')) ?>">
        </label>
        <label>E-posta
          <input type="email" name="email" required autocomplete="email" placeholder="ornek@mail.com" value="<?= e((string) ($_POST['email'] ?? '')) ?>">
        </label>
      </div>
      <div class="franchise-grid">
        <label>Şehir
          <input name="city" required autocomplete="address-level2" placeholder="İl" value="<?= e((string) ($_POST['city'] ?? '')) ?>">
        </label>
        <label>İlçe
          <input name="district" autocomplete="address-level3" placeholder="İlçe" value="<?= e((string) ($_POST['district'] ?? '')) ?>">
        </label>
      </div>
      <?php $branches = $branches ?? []; $selectedBranch = (string) ($_POST['preferred_branch_id'] ?? ''); ?>
      <?php if ($branches): ?>
        <label>Tercih edilen şube / bölge
          <select name="preferred_branch_id">
            <option value="">Seçiniz (opsiyonel)</option>
            <?php foreach ($branches as $b): ?>
              <option value="<?= (int) $b['id'] ?>" <?= $selectedBranch === (string) $b['id'] ? 'selected' : '' ?>>
                <?= e((string) $b['name']) ?> — <?= e((string) $b['city']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </label>
      <?php endif; ?>
      <label>Yaklaşık yatırım bütçesi
        <select name="budget">
          <?php
            $budgets = [
              '' => 'Seçiniz',
              '1-2M' => '1–2 milyon TL',
              '2-4M' => '2–4 milyon TL',
              '4-6M' => '4–6 milyon TL',
              '6M+' => '6 milyon TL üzeri',
            ];
            $selectedBudget = (string) ($_POST['budget'] ?? '');
          ?>
          <?php foreach ($budgets as $val => $label): ?>
            <option value="<?= e($val) ?>" <?= $selectedBudget === $val ? 'selected' : '' ?>><?= e($label) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>Sektör / işletme tecrübeniz
        <textarea name="experience" rows="3" placeholder="Örn. 5 yıl restoran işletmeciliği..."><?= e((string) ($_POST['experience'] ?? '')) ?></textarea>
      </label>
      <label>Ek mesaj
        <textarea name="message" rows="3" placeholder="Lokasyon, timing veya sorularınız..."><?= e((string) ($_POST['message'] ?? '')) ?></textarea>
      </label>

      <div class="legal-checks">
        <label class="check-line">
          <input type="checkbox" name="accept_terms" value="1" required>
          <span>Yukarıdaki franchise şartlarını okudum, kabul ediyorum.</span>
        </label>
        <label class="check-line">
          <input type="checkbox" name="accept_kvkk" value="1" required>
          <span>
            <a href="<?= e(url('/sozlesmeler/kvkk')) ?>" target="_blank" rel="noopener">KVKK Aydınlatma Metni</a>
            kapsamında kişisel verilerimin başvuru değerlendirmesi için işlenmesini kabul ediyorum.
          </span>
        </label>
      </div>

      <button class="btn btn-accent" type="submit">Başvuruyu gönder</button>
    </form>
  </section>
</div>
