# Chicken Restoran Sistemi

`https://chicken.gonulkoprusu.com` için müşteri, garson, kasa ve yönetici panelli restoran uygulaması.

## Özellikler

### Müşteri
- Markalı web sitesi
- QR kod menü (masa bazlı)
- Online sipariş
- Sipariş takip (`CHK-...` kodu ile)

### Personel (Garson)
- Kullanıcı adı / parola girişi
- Masa seçerek sipariş
- Mutfak ve bar için ayrı fişler
- Her sipariş için benzersiz ID

### Kasa
- Online + garson siparişleri
- Durum güncelleme ve fiş takibi

### Yönetici
- Personel listesi / ekleme
- Garson sipariş takibi
- Aylık restoran satışları
- Aylık garson satış istatistikleri

## Kurulum

1. `chicken/` klasörünü FTP ile `/chicken.gonulkoprusu.com` köküne yükleyin.
2. Tarayıcıdan `/install` açın.
3. MySQL bilgilerini girin.
4. Kurulum bitince `install.php` dosyasını silin.

### Varsayılan girişler (kurulum sonrası)

| Rol | Kullanıcı | Parola |
|-----|-----------|--------|
| Yönetici | `admin` | `Admin123!` |
| Kasa | `kasa` | `Kasa123!` |
| Garson | `garson1` | `Garson123!` |

## Yerel önizleme

```bash
cd chicken
php -S 127.0.0.1:8080
```

Veritabanı için `config/config.local.php` oluşturun (`config.example.php` örnek).
