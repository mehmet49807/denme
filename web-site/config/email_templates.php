<?php

return [

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'destek@gonulkoprusu.com'),
        'name' => env('MAIL_FROM_NAME', 'Gönül Köprüsü'),
    ],

    'logo' => [
        'mark' => 'images/logo-mark.png',
        'version' => 'brand-v17',
        'tagline' => 'Gönülleri Birleştiren Köprü',
    ],

    'templates' => [
        'welcome' => [
            'label' => 'Hoş Geldiniz',
            'description' => 'Yeni kayıt olan kullanıcılara otomatik gönderilir.',
            'subject' => 'Gönül Köprüsü\'ne hoş geldin, {first_name}!',
            'body' => <<<'HTML'
<p style="margin:0 0 8px;font-size:13px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#7C3AED;">Hoş Geldiniz</p>
<p style="margin:0 0 18px;font-size:22px;font-weight:800;line-height:1.35;color:#1A1523;letter-spacing:-0.02em;">Merhaba {first_name}, ailemize katıldın ✨</p>
<p style="margin:0 0 16px;">Gönül Köprüsü ailesine katıldığın için teşekkür ederiz. Hesabın başarıyla oluşturuldu ve artık ciddi ilişki odaklı, güvenli tanışma deneyimine adım atabilirsin.</p>

<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:22px 0;background:linear-gradient(135deg,#FAF5FF 0%,#FFF7FB 100%);border:1px solid rgba(124,58,237,0.14);border-radius:16px;overflow:hidden;">
    <tr>
        <td style="padding:20px 22px;">
            <p style="margin:0 0 14px;font-size:13px;font-weight:700;color:#5B21B6;">İlk adımların</p>
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                <tr>
                    <td style="padding:8px 0;font-size:14px;line-height:1.5;color:#3D3550;">
                        <span style="display:inline-block;width:26px;height:26px;line-height:26px;text-align:center;border-radius:50%;background:#7C3AED;color:#fff;font-size:12px;font-weight:700;margin-right:10px;">1</span>
                        Profilini tamamla ve keşfedilmeye başla
                    </td>
                </tr>
                <tr>
                    <td style="padding:8px 0;font-size:14px;line-height:1.5;color:#3D3550;">
                        <span style="display:inline-block;width:26px;height:26px;line-height:26px;text-align:center;border-radius:50%;background:#7C3AED;color:#fff;font-size:12px;font-weight:700;margin-right:10px;">2</span>
                        İlgi alanına uygun profilleri incele
                    </td>
                </tr>
                <tr>
                    <td style="padding:8px 0;font-size:14px;line-height:1.5;color:#3D3550;">
                        <span style="display:inline-block;width:26px;height:26px;line-height:26px;text-align:center;border-radius:50%;background:#7C3AED;color:#fff;font-size:12px;font-weight:700;margin-right:10px;">3</span>
                        Güvenli mesajlaşma ile tanış
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<table role="presentation" cellspacing="0" cellpadding="0" align="center" style="margin:28px auto 24px;">
    <tr>
        <td align="center" style="border-radius:999px;background:linear-gradient(135deg,#7C3AED 0%,#DB2777 100%);box-shadow:0 10px 28px rgba(124,58,237,0.35);">
            <a href="{feed_url}" style="display:inline-block;padding:15px 36px;color:#FFFFFF;font-size:15px;font-weight:700;text-decoration:none;letter-spacing:0.01em;">Akışa Git →</a>
        </td>
    </tr>
</table>

<p style="margin:0;font-size:14px;line-height:1.65;color:#6B6478;">Güvenle tanış, kalpten bağlan.<br><strong style="color:#1A1523;">Gönül Köprüsü Ekibi</strong></p>
HTML,
        ],

        'password_reset' => [
            'label' => 'Şifre Sıfırlama',
            'description' => 'Şifremi unuttum talebinde otomatik gönderilir.',
            'subject' => 'Şifre sıfırlama talebiniz — Gönül Köprüsü',
            'body' => <<<'HTML'
<p style="margin:0 0 8px;font-size:13px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#7C3AED;">Şifre Sıfırlama</p>
<p style="margin:0 0 18px;font-size:22px;font-weight:800;line-height:1.35;color:#1A1523;letter-spacing:-0.02em;">Merhaba {first_name},</p>
<p style="margin:0 0 16px;">Hesabınız için şifre sıfırlama talebi aldık. Yeni şifrenizi belirlemek için aşağıdaki butona tıklayın. Bu bağlantı <strong>60 dakika</strong> geçerlidir.</p>

<table role="presentation" cellspacing="0" cellpadding="0" align="center" style="margin:28px auto 24px;">
    <tr>
        <td align="center" style="border-radius:999px;background:linear-gradient(135deg,#7C3AED 0%,#DB2777 100%);box-shadow:0 10px 28px rgba(124,58,237,0.35);">
            <a href="{reset_url}" style="display:inline-block;padding:15px 36px;color:#FFFFFF;font-size:15px;font-weight:700;text-decoration:none;">Şifremi Sıfırla →</a>
        </td>
    </tr>
</table>

<p style="margin:0 0 12px;font-size:14px;line-height:1.65;color:#6B6478;">Bu talebi siz yapmadıysanız bu e-postayı yok sayabilirsiniz; hesabınız güvendedir.</p>
<p style="margin:0;font-size:13px;line-height:1.6;color:#8E8799;">Buton çalışmıyorsa şu bağlantıyı tarayıcınıza yapıştırın:<br><a href="{reset_url}" style="color:#7C3AED;word-break:break-all;">{reset_url}</a></p>
HTML,
        ],

        'profile_complete' => [
            'label' => 'Profilini Tamamla',
            'description' => 'Profil fotoğrafı veya bilgileri eksik kullanıcılara.',
            'subject' => '{first_name}, profilini tamamla — daha çok kişi seni keşfetsin',
            'body' => <<<'HTML'
<p style="margin:0 0 8px;font-size:13px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#7C3AED;">Profil Önerisi</p>
<p style="margin:0 0 18px;font-size:22px;font-weight:800;line-height:1.35;color:#1A1523;letter-spacing:-0.02em;">{first_name}, profilin seni yansıtsın</p>
<p style="margin:0 0 16px;">Profilini ne kadar detaylı doldurursan, sana uygun eşleşmeler o kadar artar. Birkaç dakikalık güncelleme ile görünürlüğünü ciddi şekilde yükseltebilirsin.</p>

<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:22px 0;">
    <tr>
        <td style="padding:14px 18px;background:#FFFBEB;border:1px solid rgba(251,191,36,0.35);border-radius:14px;">
            <p style="margin:0 0 8px;font-size:13px;font-weight:700;color:#92400E;">Premium ipucu</p>
            <p style="margin:0;font-size:14px;line-height:1.6;color:#78350F;">Fotoğraflı ve açıklamalı profiller, diğer kullanıcılar tarafından <strong>3 kat daha fazla</strong> ziyaret edilir.</p>
        </td>
    </tr>
</table>

<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:0 0 22px;background:#FAFAFA;border:1px solid #EEE9F5;border-radius:16px;">
    <tr><td style="padding:14px 18px;font-size:14px;color:#3D3550;">📷 Profil fotoğrafı ekle</td></tr>
    <tr><td style="padding:14px 18px;border-top:1px solid #EEE9F5;font-size:14px;color:#3D3550;">📍 Şehir ve ilçe bilgilerini güncelle</td></tr>
    <tr><td style="padding:14px 18px;border-top:1px solid #EEE9F5;font-size:14px;color:#3D3550;">✍️ Kendini kısaca tanıt</td></tr>
</table>

<table role="presentation" cellspacing="0" cellpadding="0" align="center" style="margin:8px auto 20px;">
    <tr>
        <td align="center" style="border-radius:999px;background:linear-gradient(135deg,#7C3AED 0%,#DB2777 100%);box-shadow:0 10px 28px rgba(124,58,237,0.35);">
            <a href="{profile_url}" style="display:inline-block;padding:15px 36px;color:#FFFFFF;font-size:15px;font-weight:700;text-decoration:none;">Profilimi Düzenle →</a>
        </td>
    </tr>
</table>
HTML,
        ],

        'premium_invite' => [
            'label' => 'Premium Davet',
            'description' => 'Erkek kullanıcılara premium paket tanıtımı.',
            'subject' => '{first_name}, Premium ile daha fazla ayrıcalık kazan',
            'body' => <<<'HTML'
<p style="margin:0 0 8px;font-size:13px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#D97706;">Premium Davet</p>
<p style="margin:0 0 18px;font-size:22px;font-weight:800;line-height:1.35;color:#1A1523;letter-spacing:-0.02em;">{first_name}, ayrıcalıklı deneyime geç</p>
<p style="margin:0 0 16px;">Gönül Köprüsü Premium ile hikaye paylaşımı, öne çıkma ve daha fazla görünürlük seni bekliyor. Doğru kişiye ulaşmak için tasarlanmış premium araçları keşfet.</p>

<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:22px 0;background:linear-gradient(145deg,#1A0F2E 0%,#4C1D95 100%);border-radius:18px;overflow:hidden;">
    <tr>
        <td style="padding:24px 22px;color:#FFFFFF;">
            <p style="margin:0 0 12px;font-size:12px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:#FBBF24;">Premium Avantajlar</p>
            <p style="margin:0 0 8px;font-size:14px;line-height:1.6;">✦ Hikaye paylaşımı ve daha fazla etkileşim</p>
            <p style="margin:0 0 8px;font-size:14px;line-height:1.6;">✦ Profilde öne çıkma</p>
            <p style="margin:0;font-size:14px;line-height:1.6;">✦ Gelişmiş görünürlük ve keşif</p>
        </td>
    </tr>
</table>

<table role="presentation" cellspacing="0" cellpadding="0" align="center" style="margin:24px auto 8px;">
    <tr>
        <td align="center" style="border-radius:999px;background:linear-gradient(135deg,#FBBF24 0%,#F59E0B 100%);box-shadow:0 10px 28px rgba(245,158,11,0.35);">
            <a href="{premium_url}" style="display:inline-block;padding:15px 36px;color:#1A1523;font-size:15px;font-weight:800;text-decoration:none;">Premium Paketleri İncele →</a>
        </td>
    </tr>
</table>
HTML,
        ],

        'security_tips' => [
            'label' => 'Güvenli Tanışma İpuçları',
            'description' => 'Güvenlik ve gizlilik hatırlatması.',
            'subject' => 'Güvenli tanışma ipuçları — {first_name}',
            'body' => <<<'HTML'
<p style="margin:0 0 8px;font-size:13px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#059669;">Güvenlik</p>
<p style="margin:0 0 18px;font-size:22px;font-weight:800;line-height:1.35;color:#1A1523;letter-spacing:-0.02em;">{first_name}, güvenli tanışma rehberi</p>
<p style="margin:0 0 16px;">Gönül Köprüsü'nde güvenliğiniz bizim önceliğimizdir. Kaliteli bir tanışma deneyimi için lütfen aşağıdaki ipuçlarını göz önünde bulundurun.</p>

<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:22px 0;background:#ECFDF5;border:1px solid rgba(16,185,129,0.25);border-radius:16px;">
    <tr><td style="padding:14px 18px;font-size:14px;line-height:1.6;color:#065F46;">🛡️ Kişisel bilgilerinizi (adres, IBAN vb.) hemen paylaşmayın</td></tr>
    <tr><td style="padding:14px 18px;border-top:1px solid rgba(16,185,129,0.15);font-size:14px;line-height:1.6;color:#065F46;">🚩 Şüpheli davranışları bildirin</td></tr>
    <tr><td style="padding:14px 18px;border-top:1px solid rgba(16,185,129,0.15);font-size:14px;line-height:1.6;color:#065F46;">☕ İlk buluşmaları kalabalık ve güvenli yerlerde yapın</td></tr>
    <tr><td style="padding:14px 18px;border-top:1px solid rgba(16,185,129,0.15);font-size:14px;line-height:1.6;color:#065F46;">📋 KVKK ve gizlilik haklarınızı okuyun</td></tr>
</table>

<table role="presentation" cellspacing="0" cellpadding="0" align="center" style="margin:8px auto 20px;">
    <tr>
        <td align="center" style="border-radius:999px;background:linear-gradient(135deg,#059669 0%,#10B981 100%);box-shadow:0 10px 28px rgba(16,185,129,0.28);">
            <a href="{safe_meeting_url}" style="display:inline-block;padding:15px 36px;color:#FFFFFF;font-size:15px;font-weight:700;text-decoration:none;">Güvenli Tanışma Rehberi →</a>
        </td>
    </tr>
</table>
HTML,
        ],

        'community_rules' => [
            'label' => 'Topluluk Kuralları',
            'description' => 'Saygılı iletişim ve topluluk standartları.',
            'subject' => 'Topluluk kurallarımız — birlikte güvenli ortam',
            'body' => <<<'HTML'
<p style="margin:0 0 8px;font-size:13px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#7C3AED;">Topluluk</p>
<p style="margin:0 0 18px;font-size:22px;font-weight:800;line-height:1.35;color:#1A1523;letter-spacing:-0.02em;">Birlikte güvenli ve saygılı ortam</p>
<p style="margin:0 0 16px;">Merhaba {first_name}, Gönül Köprüsü topluluğunda herkesin saygı ve güven içinde vakit geçirmesi için kurallarımıza uyulması gerekir.</p>

<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:22px 0;border:1px solid #EEE9F5;border-radius:16px;overflow:hidden;">
    <tr>
        <td style="padding:16px 18px;background:#FAF5FF;font-size:14px;font-weight:700;color:#5B21B6;border-bottom:1px solid #EEE9F5;">Topluluk standartlarımız</td>
    </tr>
    <tr><td style="padding:14px 18px;font-size:14px;line-height:1.6;color:#3D3550;">💜 Saygılı ve ciddi iletişim</td></tr>
    <tr><td style="padding:14px 18px;border-top:1px solid #F3EEFF;font-size:14px;line-height:1.6;color:#3D3550;">🚫 Sahte profil ve spam yasaktır</td></tr>
    <tr><td style="padding:14px 18px;border-top:1px solid #F3EEFF;font-size:14px;line-height:1.6;color:#3D3550;">⚠️ Taciz ve uygunsuz içerik ban sebebidir</td></tr>
    <tr><td style="padding:14px 18px;border-top:1px solid #F3EEFF;font-size:14px;line-height:1.6;color:#3D3550;">🔞 18 yaş altı kullanım yasaktır</td></tr>
</table>

<p style="margin:0;font-size:14px;line-height:1.65;color:#6B6478;">İhlal gördüğünüzde lütfen bize bildirin:<br><a href="mailto:destek@gonulkoprusu.com" style="color:#7C3AED;font-weight:600;text-decoration:none;">destek@gonulkoprusu.com</a></p>
HTML,
        ],

        're_engagement' => [
            'label' => 'Seni Özledik',
            'description' => 'Uzun süredir giriş yapmayan kullanıcılara.',
            'subject' => '{first_name}, seni özledik — yeni profiller seni bekliyor',
            'body' => <<<'HTML'
<p style="margin:0 0 8px;font-size:13px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#DB2777;">Seni Özledik</p>
<p style="margin:0 0 18px;font-size:22px;font-weight:800;line-height:1.35;color:#1A1523;letter-spacing:-0.02em;">{first_name}, geri dönmeni bekliyoruz</p>
<p style="margin:0 0 16px;">Uzun süredir görünmüyorsun. Topluluğumuz büyümeye devam ediyor; yeni profiller ve mesajlar seni bekliyor olabilir.</p>

<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:22px 0;background:linear-gradient(135deg,#FFF1F2 0%,#FAF5FF 100%);border:1px solid rgba(219,39,119,0.18);border-radius:16px;">
    <tr>
        <td style="padding:20px 22px;text-align:center;">
            <p style="margin:0 0 6px;font-size:28px;line-height:1;">💌</p>
            <p style="margin:0;font-size:15px;line-height:1.65;color:#3D3550;">Gönülleri birleştiren köprüde yerini al.<br>Yeni eşleşmeler seni bekliyor olabilir.</p>
        </td>
    </tr>
</table>

<table role="presentation" cellspacing="0" cellpadding="0" align="center" style="margin:24px auto 8px;">
    <tr>
        <td align="center" style="border-radius:999px;background:linear-gradient(135deg,#7C3AED 0%,#DB2777 100%);box-shadow:0 10px 28px rgba(124,58,237,0.35);">
            <a href="{feed_url}" style="display:inline-block;padding:15px 36px;color:#FFFFFF;font-size:15px;font-weight:700;text-decoration:none;">Hemen Giriş Yap →</a>
        </td>
    </tr>
</table>
HTML,
        ],

        'female_welcome' => [
            'label' => 'Kadın Hoş Geldin',
            'description' => 'Yeni kayıt olan kadın kullanıcılara otomatik gönderilir.',
            'subject' => '{first_name}, Gönül Köprüsü\'nde güvenle tanış',
            'body' => <<<'HTML'
<p style="margin:0 0 8px;font-size:13px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#DB2777;">Hoş Geldin</p>
<p style="margin:0 0 18px;font-size:22px;font-weight:800;line-height:1.35;color:#1A1523;letter-spacing:-0.02em;">Merhaba {first_name}, seni aramızda görmek güzel ✨</p>
<p style="margin:0 0 16px;">Gönül Köprüsü'nde kadın üyeler için mesajlaşma, kimler baktı ve galeri ücretsizdir. Güvenli, saygılı ve ciddi ilişki odaklı bir ortamda tanışmaya başlayabilirsin.</p>

<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:22px 0;background:#FFF1F2;border:1px solid rgba(219,39,119,0.2);border-radius:16px;">
    <tr><td style="padding:14px 18px;font-size:14px;line-height:1.6;color:#831843;">💬 Mesajlaşma ve kimler baktı — kadınlarda ücretsiz</td></tr>
    <tr><td style="padding:14px 18px;border-top:1px solid rgba(219,39,119,0.12);font-size:14px;line-height:1.6;color:#831843;">🛡️ Moderasyon ve 7/24 destek</td></tr>
    <tr><td style="padding:14px 18px;border-top:1px solid rgba(219,39,119,0.12);font-size:14px;line-height:1.6;color:#831843;">✨ Profil fotoğrafı ekle, keşfedilmeyi hızlandır</td></tr>
</table>

<table role="presentation" cellspacing="0" cellpadding="0" align="center" style="margin:28px auto 24px;">
    <tr>
        <td align="center" style="border-radius:999px;background:linear-gradient(135deg,#7C3AED 0%,#DB2777 100%);box-shadow:0 10px 28px rgba(124,58,237,0.35);">
            <a href="{feed_url}" style="display:inline-block;padding:15px 36px;color:#FFFFFF;font-size:15px;font-weight:700;text-decoration:none;">Profilini Keşfet →</a>
        </td>
    </tr>
</table>

<p style="margin:0;font-size:14px;line-height:1.65;color:#6B6478;">Soruların için <a href="{support_url}" style="color:#DB2777;font-weight:600;">7/24 destek</a> sayfamızdan bize ulaşabilirsin.</p>
HTML,
        ],

        'invite_friends' => [
            'label' => 'Arkadaşını Davet Et',
            'description' => 'Mevcut üyelere WhatsApp / davet kampanyası.',
            'subject' => '{first_name}, arkadaşını davet et — ödül kazan',
            'body' => <<<'HTML'
<p style="margin:0 0 8px;font-size:13px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#0F766E;">Davet Kampanyası</p>
<p style="margin:0 0 18px;font-size:22px;font-weight:800;line-height:1.35;color:#1A1523;">Arkadaşını Gönül Köprüsü’ne davet et</p>
<p style="margin:0 0 16px;">Güvendiğin bir arkadaşını davet et. Kayıt olduğunda ödülün hesabına tanımlansın. Linkini WhatsApp ile paylaşabilirsin.</p>
<table role="presentation" cellspacing="0" cellpadding="0" align="center" style="margin:28px auto 16px;">
    <tr>
        <td align="center" style="border-radius:999px;background:linear-gradient(135deg,#0F766E 0%,#7C3AED 100%);">
            <a href="{referral_url}" style="display:inline-block;padding:15px 36px;color:#FFFFFF;font-size:15px;font-weight:700;text-decoration:none;">Davet linkimi al →</a>
        </td>
    </tr>
</table>
<p style="margin:0;font-size:14px;line-height:1.65;color:#6B6478;">Doğrudan paylaşım linkin:<br><a href="{invite_url}" style="color:#0F766E;font-weight:600;">{invite_url}</a></p>
HTML,
        ],

        'custom' => [
            'label' => 'Özel Mesaj',
            'description' => 'Konu ve içeriği kendiniz yazın.',
            'subject' => '',
            'body' => '',
        ],
    ],

];
