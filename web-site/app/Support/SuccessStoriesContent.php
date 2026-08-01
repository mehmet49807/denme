<?php

namespace App\Support;

/**
 * Ana sayfa slayt + /basari-hikayeleri ortak içerik ve görseller.
 */
final class SuccessStoriesContent
{
    /**
     * @return list<array{
     *   names: string,
     *   city: string,
     *   quote: string,
     *   note: string,
     *   body: string,
     *   image: string,
     *   image_alt: string
     * }>
     */
    public static function all(): array
    {
        return [
            [
                'names' => 'Ayşe & Mehmet',
                'city' => 'İstanbul · Kadıköy',
                'quote' => 'Ciddi niyet arıyorduk; burada buluştuk.',
                'note' => 'Güvenli sohbetten ilk buluşmaya, sonra nişan.',
                'body' => 'İstanbul’da kalabalık uygulamalar yormuştu. Gönül Köprüsü’nde profilleri okuyup birkaç hafta güvenli sohbet ettiler. İlk buluşmayı Kadıköy’de halka açık bir kafede yaptılar; bugün nişanlılar.',
                'image' => 'story-couple-01',
                'image_alt' => 'Ayşe ve Mehmet — gelin ve damat, İstanbul',
            ],
            [
                'names' => 'Elif & Can',
                'city' => 'Ankara · Çankaya',
                'quote' => 'Başkentte sakin ve saygılı bir ortam.',
                'note' => 'Evlilik niyetiyle başladılar; bağları güçlendi.',
                'body' => 'Ankara’da evlilik odaklı bir platform arıyorlardı. Şehir filtresi ve tamamlanmış profiller sayesinde ortak hobileri olan biriyle tanıştılar. Mesajlaşma sonrası yüz yüze görüşüp ilişkiyi ilerlettiler.',
                'image' => 'story-couple-02',
                'image_alt' => 'Elif ve Can — mutlu çift, Ankara',
            ],
            [
                'names' => 'Zeynep & Emre',
                'city' => 'İzmir · Karşıyaka',
                'quote' => 'Flört değil, gerçek bağ istedik.',
                'note' => 'Saygılı mesajlaşma, güvenli ilk adım, ortak yol.',
                'body' => 'İzmir’de yüzeysel eşleşmelerden sıkılmışlardı. Gönül Köprüsü’nde niyetlerini net yazdılar; Karşıyaka ve Bornova çevresinde güvenli tanışma adımlarını izleyerek birliktelik kurdular.',
                'image' => 'story-couple-03',
                'image_alt' => 'Zeynep ve Emre — mutlu çift, İzmir',
            ],
            [
                'names' => 'Selin & Burak',
                'city' => 'Bursa',
                'quote' => 'Aynı şehirde olmak her şeyi kolaylaştırdı.',
                'note' => 'Birkaç görüşmeden sonra aileleriyle tanıştılar.',
                'body' => 'Bursa tanışma sayfasından kayıt oldular. Konum yakınlığı sayesinde birkaç görüşmeden sonra aileleriyle tanışma aşamasına geçtiler.',
                'image' => 'story-couple-04',
                'image_alt' => 'Selin ve Burak — gelin ve damat, Bursa',
            ],
            [
                'names' => 'Deniz & Kerem',
                'city' => 'Antalya',
                'quote' => 'Güvenlik ayarları sayesinde rahat ettik.',
                'note' => 'Moderasyonlu ortamda ciddi ilişki aradılar.',
                'body' => 'Antalya’da turizm temposunda ciddi ilişki aramak zordu. Engelleme ve moderasyon sayesinde spam’siz bir deneyim yaşadıklarını anlatıyorlar.',
                'image' => 'story-couple-05',
                'image_alt' => 'Deniz ve Kerem — mutlu çift, Antalya',
            ],
            [
                'names' => 'Merve & Onur',
                'city' => 'Adana',
                'quote' => 'Ücretsiz kayıtla başladık, hikâyemiz devam ediyor.',
                'note' => 'Gündüz, kalabalık bir mekânda ilk buluşma.',
                'body' => 'Adana’dan ücretsiz üye olup profilini tamamlayan Merve, kısa sürede ortak değerlere sahip biriyle sohbet etmeye başladı. İlk buluşmayı gündüz ve kalabalık bir mekânda yaptılar.',
                'image' => 'story-couple-06',
                'image_alt' => 'Merve ve Onur — nişanlı çift, Adana',
            ],
            [
                'names' => 'İrem & Yusuf',
                'city' => 'Konya',
                'quote' => 'Ailevi değerlere uygun bir ortam arıyorduk.',
                'note' => 'Net beklentiler, sakin sohbet, güvenli adım.',
                'body' => 'Konya’da sakin ve ciddi niyetli bir platform istediler. Profil biyografilerinde beklentilerini net yazıp birkaç hafta sohbet ettikten sonra yüz yüze görüştüler.',
                'image' => 'story-couple-01',
                'image_alt' => 'İrem ve Yusuf — gelin ve damat, Konya',
            ],
            [
                'names' => 'Buse & Tolga',
                'city' => 'Gaziantep',
                'quote' => 'Güneydoğu’da güvenli tanışma mümkünmüş.',
                'note' => 'Saygılı sohbet, moderasyon, güven.',
                'body' => 'Gaziantep şehir sayfasından keşfettiler. Moderasyon ve şikayet araçları sayesinde saygılı bir sohbet ortamı bulduklarını söylüyorlar.',
                'image' => 'story-couple-02',
                'image_alt' => 'Buse ve Tolga — mutlu çift, Gaziantep',
            ],
            [
                'names' => 'Ceren & Hakan',
                'city' => 'Eskişehir',
                'quote' => 'Üniversite şehrinde bile ciddi bağ kuruldu.',
                'note' => 'Ortak hobiler, güvenli ilk buluşma.',
                'body' => 'Eskişehir’de hem öğrenci hem profesyonel üyeler var. Ortak kitap ve yürüyüş hobisiyle başlayan sohbetleri, güvenli ilk buluşmayla devam etti.',
                'image' => 'story-couple-03',
                'image_alt' => 'Ceren ve Hakan — mutlu çift, Eskişehir',
            ],
            [
                'names' => 'Gizem & Oğuz',
                'city' => 'Samsun',
                'quote' => 'Karadeniz’de ücretsiz başladık.',
                'note' => 'Fotoğraflı profiller, konum filtresi.',
                'body' => 'Samsun tanışma sayfasından kayıt oldular. Konum filtresi ve tamamlanmış fotoğraflı profiller sayesinde kısa sürede ortak noktada buluştular.',
                'image' => 'story-couple-05',
                'image_alt' => 'Gizem ve Oğuz — mutlu çift, Samsun',
            ],
            [
                'names' => 'Melis & Arda',
                'city' => 'Trabzon',
                'quote' => 'Davet linkiyle arkadaşımız da katıldı.',
                'note' => 'Davet ödülüyle büyüyen topluluk.',
                'body' => 'Trabzon’dan üye oldular; davet ödülü sayesinde yakın bir arkadaşlarını da platforma getirdiler. Topluluk büyüdükçe eşleşme kalitesi arttı diyorlar.',
                'image' => 'story-couple-06',
                'image_alt' => 'Melis ve Arda — mutlu çift, Trabzon',
            ],
            [
                'names' => 'Naz & Emir',
                'city' => 'Kayseri',
                'quote' => 'Ciddi ilişki niyetimizi baştan yazdık.',
                'note' => 'Güvenli tanışma rehberiyle ilk adım.',
                'body' => 'Kayseri’de evlilik odaklı arayışlarını net belirttiler. Güvenli tanışma rehberindeki adımları izleyerek ilk buluşmayı gündüz yaptılar.',
                'image' => 'story-couple-04',
                'image_alt' => 'Naz ve Emir — gelin ve damat, Kayseri',
            ],
        ];
    }

    /** Ana sayfa slaytında gösterilecek ilk N hikâye. */
    public static function forHome(int $limit = 6): array
    {
        return array_slice(self::all(), 0, max(1, $limit));
    }
}
