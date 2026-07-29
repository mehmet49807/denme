<?php

declare(strict_types=1);

/**
 * Static content pages: about + Turkish legal texts.
 */
final class SiteContent
{
    /** @return array{title:string,eyebrow:string,heading:string,sections:list<array{title?:string,body:string}>} */
    public static function page(string $slug): ?array
    {
        $pages = self::pages();
        return $pages[$slug] ?? null;
    }

    /** @return array<string, array{title:string,eyebrow:string,heading:string,sections:list<array{title?:string,body:string}>}> */
    private static function pages(): array
    {
        return [
            'hakkimizda' => [
                'title' => 'Hakkımızda',
                'eyebrow' => 'Crisp & Co.',
                'heading' => 'Hakkımızda',
                'sections' => [
                    [
                        'body' => "Crisp & Co.; taze ürün, dikkatli ızgara ve hızlı servis anlayışıyla çalışan bir restoran markasıdır. Mutfak ve bar ekiplerimiz siparişleri ayrı istasyonlarda hazırlar; her siparişin benzersiz bir takip kodu vardır.",
                    ],
                    [
                        'title' => 'Neler sunuyoruz?',
                        'body' => "QR menü ile masada kolay inceleme, online sipariş, anlık takip ve personel panelleriyle uçtan uca bir deneyim sunuyoruz. Amacımız lezzeti, hızı ve şeffaflığı aynı masada buluşturmak.",
                    ],
                ],
            ],
            'misyon' => [
                'title' => 'Misyonumuz',
                'eyebrow' => 'Crisp & Co.',
                'heading' => 'Misyonumuz',
                'sections' => [
                    [
                        'body' => "Misyonumuz; her misafire sıcak, taze ve güvenilir ızgara lezzetleri sunmak; sipariş sürecini anlaşılır, hızlı ve takip edilebilir kılmaktır.",
                    ],
                    [
                        'title' => 'İlkelerimiz',
                        'body' => "• Taze ürün ve dikkatli pişirme\n• Net fiyat ve şeffaf iletişim\n• Hijyen ve gıda güvenliği\n• Hızlı servis, doğru sipariş\n• Misafir geri bildirimini değerli tutmak",
                    ],
                ],
            ],
            'musteri-memnuniyeti' => [
                'title' => 'Müşteri Memnuniyeti',
                'eyebrow' => 'Crisp & Co.',
                'heading' => 'Müşteri memnuniyeti',
                'sections' => [
                    [
                        'body' => "Memnuniyetiniz bizim için siparişin tamamlanması kadar önemlidir. Ürün, süre veya servis ile ilgili taleplerinizi personelimize iletebilir; online siparişlerinizi takip koduyla anlık izleyebilirsiniz.",
                    ],
                    [
                        'title' => 'Nasıl yardımcı oluruz?',
                        'body' => "• Sipariş durumu ve değişiklik talepleri\n• Ürün / alerjen bilgilendirmesi\n• Teslimat ve paketleme geri bildirimi\n• Öneri ve şikayetlerin kayıt altına alınması\n\nGeri bildirimlerinizi restoranımıza veya online kanallarımız üzerinden iletebilirsiniz. Mümkün olan en kısa sürede dönüş yaparız.",
                    ],
                ],
            ],
            'uyelik' => [
                'title' => 'Üyelik Sözleşmesi',
                'eyebrow' => 'Sözleşmeler',
                'heading' => 'Üyelik Sözleşmesi',
                'sections' => [
                    [
                        'body' => "Bu Üyelik Sözleşmesi (“Sözleşme”), Crisp & Co. restoranının dijital üyelik hizmetlerini kullanan gerçek kişi üye (“Üye”) ile Crisp & Co. (“İşletme”) arasında akdedilmiştir. Üyelik oluşturmakla bu sözleşmenin hükümlerini kabul etmiş sayılırsınız.",
                    ],
                    [
                        'title' => '1. Konu',
                        'body' => "Sözleşme; Üye’nin www / uygulama üzerinden hesap oluşturması, sipariş vermesi, indirim kodlarından yararlanması ve ilgili hizmetleri kullanmasına ilişkin şartları düzenler.",
                    ],
                    [
                        'title' => '2. Üyelik koşulları',
                        'body' => "Üye, kayıt sırasında verdiği bilgilerin doğru ve güncel olduğunu beyan eder. 18 yaşından küçükler yasal temsilci onayı olmadan üye olamaz. Hesap güvenliğinden Üye sorumludur; parola paylaşılmamalıdır.",
                    ],
                    [
                        'title' => '3. Hizmetin kapsamı',
                        'body' => "İşletme; menü görüntüleme, online sipariş, sipariş takibi ve kampanya bilgilendirmesi gibi hizmetleri sunabilir. Hizmetler teknik bakım nedeniyle geçici olarak kesilebilir.",
                    ],
                    [
                        'title' => '4. Üyenin yükümlülükleri',
                        'body' => "Üye; mevzuata aykırı, yanıltıcı veya başkalarının haklarını ihlal eden içerik paylaşamaz. Sipariş ve ödeme süreçlerinde doğru iletişim bilgisi vermek zorundadır.",
                    ],
                    [
                        'title' => '5. Fesih',
                        'body' => "Üye hesabını kapatmayı talep edebilir. İşletme, sözleşmeye aykırılık halinde üyeliği askıya alabilir veya sonlandırabilir.",
                    ],
                    [
                        'title' => '6. Uygulanacak hukuk',
                        'body' => "Bu sözleşme Türkiye Cumhuriyeti hukukuna tabidir. Uyuşmazlıklarda İşletme’nin bulunduğu yer mahkemeleri ve icra daireleri yetkilidir.",
                    ],
                ],
            ],
            'kullanim' => [
                'title' => 'Kullanım Koşulları',
                'eyebrow' => 'Sözleşmeler',
                'heading' => 'Kullanım Koşulları',
                'sections' => [
                    [
                        'body' => "Crisp & Co. dijital platformlarını (web sitesi ve ilgili arayüzler) kullanarak aşağıdaki kullanım koşullarını kabul etmiş olursunuz.",
                    ],
                    [
                        'title' => '1. Genel',
                        'body' => "Site içeriği bilgilendirme ve sipariş amaçlıdır. Menü, fiyat ve stok bilgileri güncellenebilir. Görseller temsilidir.",
                    ],
                    [
                        'title' => '2. Yasaklı kullanımlar',
                        'body' => "Sistemi bozmaya, yetkisiz erişime, zararlı yazılım yaymaya veya üçüncü kişilerin verilerini toplamaya yönelik eylemler yasaktır.",
                    ],
                    [
                        'title' => '3. Fikri mülkiyet',
                        'body' => "Logo, metin, tasarım ve yazılım unsurları İşletme’ye aittir. İzinsiz kopyalama, çoğaltma veya ticari kullanım yapılamaz.",
                    ],
                    [
                        'title' => '4. Sorumluluk sınırı',
                        'body' => "İşletme; internet kesintisi, üçüncü taraf servis arızaları veya mücbir sebeplerden doğan gecikmelerden makul sınırlar içinde sorumlu tutulamaz.",
                    ],
                ],
            ],
            'kvkk' => [
                'title' => 'KVKK Aydınlatma Metni',
                'eyebrow' => 'Sözleşmeler',
                'heading' => 'KVKK Aydınlatma Metni',
                'sections' => [
                    [
                        'body' => "6698 sayılı Kişisel Verilerin Korunması Kanunu (“KVKK”) uyarınca veri sorumlusu sıfatıyla Crisp & Co.; kimlik, iletişim, işlem güvenliği ve sipariş verilerinizi aşağıda açıklanan çerçevede işleyebilir.",
                    ],
                    [
                        'title' => '1. İşlenen veriler',
                        'body' => "Ad-soyad, e-posta, telefon, adres, üyelik bilgileri, sipariş ve işlem kayıtları, işlem güvenliği verileri (IP, oturum), varsa pazarlama tercihleri.",
                    ],
                    [
                        'title' => '2. Amaçlar',
                        'body' => "Üyelik oluşturma, sipariş alma/iletimi, müşteri ilişkileri, faturalama süreçleri, güvenlik, yasal yükümlülükler ve açık rızanız varsa kampanya bilgilendirmesi.",
                    ],
                    [
                        'title' => '3. Hukuki sebepler',
                        'body' => "KVKK m.5/2: sözleşmenin kurulması/ifası, hukuki yükümlülük, meşru menfaat; pazarlama iletişimi için açık rıza (m.5/1).",
                    ],
                    [
                        'title' => '4. Aktarım',
                        'body' => "Verileriniz; barındırma, SMS/e-posta, ödeme ve teknik destek sağlayıcılarına, yalnızca gerekli ölçüde ve sözleşmesel güvencelerle aktarılabilir. Yasal zorunluluk halinde yetkili kurumlarla paylaşılabilir.",
                    ],
                    [
                        'title' => '5. Saklama süresi',
                        'body' => "Veriler, işleme amacının gerektirdiği süre ve ilgili mevzuattaki zamanaşımı süreleri boyunca saklanır; süre bitiminde silinir, yok edilir veya anonim hale getirilir.",
                    ],
                    [
                        'title' => '6. Haklarınız',
                        'body' => "KVKK m.11 kapsamında; verilerinizin işlenip işlenmediğini öğrenme, düzeltme, silme/yok etme talep etme, itiraz ve şikâyet haklarına sahipsiniz. Taleplerinizi İşletme iletişim kanalları üzerinden iletebilirsiniz.",
                    ],
                ],
            ],
            'acik-riza' => [
                'title' => 'Açık Rıza Metni',
                'eyebrow' => 'Sözleşmeler',
                'heading' => 'Açık Rıza Metni',
                'sections' => [
                    [
                        'body' => "KVKK kapsamında; üyelik ve sipariş süreçlerinin yürütülmesi için gerekli kişisel verilerimin işlenmesine, Aydınlatma Metni’nde belirtilen amaçlarla sınırlı olarak onay veriyorum.",
                    ],
                    [
                        'title' => 'Pazarlama iletişimi (ayrı tercih)',
                        'body' => "Kampanya, indirim ve bilgilendirme mesajlarının (SMS, e-posta, bildirim) tarafıma iletilmesine ayrıca onay vermem halinde bu iletişimler gönderilebilir. Onayımı dilediğim zaman geri alabilirim.",
                    ],
                ],
            ],
            'gizlilik' => [
                'title' => 'Gizlilik Politikası',
                'eyebrow' => 'Sözleşmeler',
                'heading' => 'Gizlilik Politikası',
                'sections' => [
                    [
                        'body' => "Bu Gizlilik Politikası; Crisp & Co.’nun dijital kanallarında toplanan bilgilerin nasıl korunduğunu açıklar. KVKK Aydınlatma Metni ile birlikte okunmalıdır.",
                    ],
                    [
                        'title' => '1. Toplanan bilgiler',
                        'body' => "Doğrudan verdiğiniz üyelik/sipariş bilgileri ve hizmetin çalışması için gerekli teknik kayıtlar (güvenlik amaçlı günlükler vb.).",
                    ],
                    [
                        'title' => '2. Çerezler',
                        'body' => "Oturum yönetimi ve temel işlevler için gerekli çerezler kullanılabilir. Zorunlu olmayan çerezler için ayrıca bilgilendirme/tercih mekanizması eklenebilir.",
                    ],
                    [
                        'title' => '3. Güvenlik',
                        'body' => "Parolalar hash’lenerek saklanır. Yetkisiz erişime karşı makul idari ve teknik tedbirler uygulanır.",
                    ],
                    [
                        'title' => '4. Güncellemeler',
                        'body' => "Politika güncellenebilir. Güncel metin bu sayfada yayımlanır.",
                    ],
                ],
            ],
            'mesafeli-satis' => [
                'title' => 'Mesafeli Satış Sözleşmesi',
                'eyebrow' => 'Sözleşmeler',
                'heading' => 'Mesafeli Satış Sözleşmesi',
                'sections' => [
                    [
                        'body' => "6502 sayılı Tüketicinin Korunması Hakkında Kanun ve Mesafeli Sözleşmeler Yönetmeliği kapsamında; online sipariş veren tüketici (“Alıcı”) ile Crisp & Co. (“Satıcı”) arasında aşağıdaki koşullar geçerlidir.",
                    ],
                    [
                        'title' => '1. Taraflar ve konu',
                        'body' => "Konu; Alıcı’nın elektronik ortamda sipariş ettiği gıda / yiyecek-içecek ürünlerinin Satıcı tarafından hazırlanması ve teslimine ilişkindir.",
                    ],
                    [
                        'title' => '2. Ürün ve bedel',
                        'body' => "Ürünlerin temel nitelikleri, vergiler dahil satış bedeli ve varsa teslimat ücreti sipariş ekranında gösterilir. Sipariş onayı ile Alıcı bedeli ödemeyi kabul eder.",
                    ],
                    [
                        'title' => '3. Teslimat',
                        'body' => "Teslimat; Alıcı’nın bildirdiği adres / teslim alma yöntemine göre yapılır. Hazır gıda niteliği nedeniyle gecikmelerde makul bilgilendirme yapılır.",
                    ],
                    [
                        'title' => '4. Cayma hakkı',
                        'body' => "Mesafeli Sözleşmeler Yönetmeliği uyarınca; çabuk bozulabilen veya son kullanma tarihi geçebilecek mallar ile tüketicinin istekleri doğrultusunda hazırlanan gıdalarda cayma hakkı istisnaları uygulanabilir. Sipariş hazırlandıktan / teslim edildikten sonra iade koşulları ürün niteliğine göre değerlendirilir.",
                    ],
                    [
                        'title' => '5. Şikayet ve itiraz',
                        'body' => "Uyuşmazlıklarda tüketici hakem heyetleri ve tüketici mahkemeleri ile Satıcı’nın bulunduğu yer mercileri yetkilidir.",
                    ],
                ],
            ],
        ];
    }
}
