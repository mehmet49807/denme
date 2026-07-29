INSERT INTO staff (name, username, password_hash, role, pin, is_active) VALUES
('Yönetici', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '0000', 1),
('Kasa', 'kasa', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cashier', '1111', 1),
('Garson Ayşe', 'garson1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'waiter', '2222', 1),
('Garson Mehmet', 'garson2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'waiter', '3333', 1)
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO dining_tables (code, label, seats, qr_token) VALUES
('M1', 'Masa 1', 4, 'table-m1-chicken'),
('M2', 'Masa 2', 4, 'table-m2-chicken'),
('M3', 'Masa 3', 4, 'table-m3-chicken'),
('M4', 'Masa 4', 6, 'table-m4-chicken'),
('M5', 'Masa 5', 2, 'table-m5-chicken'),
('M6', 'Masa 6', 4, 'table-m6-chicken'),
('M7', 'Masa 7', 8, 'table-m7-chicken'),
('M8', 'Masa 8', 4, 'table-m8-chicken')
ON DUPLICATE KEY UPDATE label = VALUES(label);

INSERT INTO categories (name, slug, sort_order) VALUES
('Izgara', 'izgara', 1),
('Menüler', 'menuler', 2),
('Dürümler', 'durumler', 3),
('Burgerler', 'burgerler', 4),
('Yan ürünler', 'yan-urunler', 5),
('Tatlılar', 'tatlilar', 6),
('Tüm İçecekler', 'tum-icecekler', 7)
ON DUPLICATE KEY UPDATE name = VALUES(name), sort_order = VALUES(sort_order), is_active = 1;

-- Fiyatlar KDV dahildir. Restoran yeme-içme hizmeti KDV %10 (alkollü içecek %20).
INSERT INTO menu_items (category_id, name, description, price, vat_rate, station, image_url, sort_order) VALUES
((SELECT id FROM categories WHERE slug='izgara'), 'Izgara Tavuk Şiş', 'Marine edilmiş tavuk şiş, köz biber', 420.00, 10.00, 'kitchen', '/assets/img/menu/izgara-tavuk-sis.jpg', 1),
((SELECT id FROM categories WHERE slug='izgara'), 'Acılı Kanat', '8 adet acılı ızgara kanat', 390.00, 10.00, 'kitchen', '/assets/img/menu/acili-kanat.jpg', 2),
((SELECT id FROM categories WHERE slug='izgara'), 'Ballı Hardallı Tavuk', 'Ballı hardal glaze, yeşillik', 445.00, 10.00, 'kitchen', '/assets/img/menu/balli-hardalli-tavuk.jpg', 3),
((SELECT id FROM categories WHERE slug='menuler'), 'Chicken Menü', 'Burger + patates + içecek', 520.00, 10.00, 'kitchen', '/assets/img/menu/chicken-menu.jpg', 1),
((SELECT id FROM categories WHERE slug='menuler'), 'Aile Menüsü', '2 şiş + 2 kanat + patates + 4 içecek', 1450.00, 10.00, 'kitchen', '/assets/img/menu/aile-menusu.jpg', 2),
((SELECT id FROM categories WHERE slug='durumler'), 'Tavuk Dürüm', 'Izgara tavuk, lavaş, turşu', 280.00, 10.00, 'kitchen', '/assets/img/menu/tavuk-durum.jpg', 1),
((SELECT id FROM categories WHERE slug='durumler'), 'Köfte Dürüm', 'Izgara köfte, sos, yeşillik', 310.00, 10.00, 'kitchen', '/assets/img/menu/kofte-durum.jpg', 2),
((SELECT id FROM categories WHERE slug='durumler'), 'Kaşarlı Tavuk Dürüm', 'Izgara tavuk, kaşar, lavaş', 320.00, 10.00, 'kitchen', '/assets/img/menu/kasarli-tavuk-durum.jpg', 3),
((SELECT id FROM categories WHERE slug='durumler'), 'Et Dürüm', 'Izgara et, sos, yeşillik', 380.00, 10.00, 'kitchen', '/assets/img/menu/et-durum.jpg', 4),
((SELECT id FROM categories WHERE slug='durumler'), 'Karışık Dürüm', 'Tavuk + köfte, özel sos', 350.00, 10.00, 'kitchen', '/assets/img/menu/karisik-durum.jpg', 5),
((SELECT id FROM categories WHERE slug='burgerler'), 'Chicken Burger', 'Özel sos, çıtır tavuk, turşu', 310.00, 10.00, 'kitchen', '/assets/img/menu/chicken-burger.jpg', 1),
((SELECT id FROM categories WHERE slug='burgerler'), 'Cheese Burger', 'Cheddar, özel sos', 340.00, 10.00, 'kitchen', '/assets/img/menu/cheese-burger.jpg', 2),
((SELECT id FROM categories WHERE slug='burgerler'), 'Double Chicken Burger', 'Çift kat çıtır tavuk, özel sos', 420.00, 10.00, 'kitchen', '/assets/img/menu/double-chicken-burger.jpg', 3),
((SELECT id FROM categories WHERE slug='burgerler'), 'BBQ Burger', 'BBQ sos, çıtır soğan', 360.00, 10.00, 'kitchen', '/assets/img/menu/bbq-burger.jpg', 4),
((SELECT id FROM categories WHERE slug='burgerler'), 'Acılı Burger', 'Acılı sos, jalapeno, çıtır tavuk', 355.00, 10.00, 'kitchen', '/assets/img/menu/acili-burger.jpg', 5),
((SELECT id FROM categories WHERE slug='yan-urunler'), 'Çıtır Patates', 'Ev yapımı baharatlı patates', 140.00, 10.00, 'kitchen', '/assets/img/menu/citir-patates.jpg', 1),
((SELECT id FROM categories WHERE slug='yan-urunler'), 'Soğan Halkası', '6 adet çıtır soğan halkası', 150.00, 10.00, 'kitchen', '/assets/img/menu/sogan-halkasi.jpg', 2),
((SELECT id FROM categories WHERE slug='yan-urunler'), 'Coleslaw', 'Taze lahana salatası', 110.00, 10.00, 'kitchen', '/assets/img/menu/coleslaw.jpg', 3),
((SELECT id FROM categories WHERE slug='tatlilar'), 'Sufle', 'Sıcak çikolatalı sufle', 190.00, 10.00, 'kitchen', '/assets/img/menu/sufle.jpg', 1),
((SELECT id FROM categories WHERE slug='tatlilar'), 'Dondurma', '2 top', 130.00, 10.00, 'bar', '/assets/img/menu/dondurma.jpg', 2),
((SELECT id FROM categories WHERE slug='tatlilar'), 'Fırın Sütlaç', 'Fırında pişmiş klasik sütlaç', 160.00, 10.00, 'kitchen', '/assets/img/menu/firin-sutlac.jpg', 3),
((SELECT id FROM categories WHERE slug='tatlilar'), 'Muzlu Supangle', 'Muzlu çikolatalı supangle', 170.00, 10.00, 'kitchen', '/assets/img/menu/muzlu-supangle.jpg', 4),
((SELECT id FROM categories WHERE slug='tatlilar'), 'Baklava', 'Antep fıstıklı baklava', 210.00, 10.00, 'kitchen', '/assets/img/menu/baklava.jpg', 5),
((SELECT id FROM categories WHERE slug='tatlilar'), 'Cheesecake', 'New York usulü cheesecake', 200.00, 10.00, 'kitchen', '/assets/img/menu/cheesecake.jpg', 6),
((SELECT id FROM categories WHERE slug='tatlilar'), 'San Sebastian', 'Yanık cheesecake dilimi', 220.00, 10.00, 'kitchen', '/assets/img/menu/san-sebastian.jpg', 7),
((SELECT id FROM categories WHERE slug='tum-icecekler'), 'Ayran', '300 ml', 60.00, 10.00, 'bar', '/assets/img/menu/ayran.jpg', 1),
((SELECT id FROM categories WHERE slug='tum-icecekler'), 'Kola', '330 ml', 75.00, 10.00, 'bar', '/assets/img/menu/kola.jpg', 2),
((SELECT id FROM categories WHERE slug='tum-icecekler'), 'Fanta', '330 ml', 75.00, 10.00, 'bar', '/assets/img/menu/fanta.jpg', 3),
((SELECT id FROM categories WHERE slug='tum-icecekler'), 'Sprite', '330 ml', 75.00, 10.00, 'bar', '/assets/img/menu/sprite.jpg', 4),
((SELECT id FROM categories WHERE slug='tum-icecekler'), 'Soda', '200 ml', 50.00, 10.00, 'bar', '/assets/img/menu/soda.jpg', 5),
((SELECT id FROM categories WHERE slug='tum-icecekler'), 'Şalgam Acılı', 'Acılı şalgam suyu', 65.00, 10.00, 'bar', '/assets/img/menu/salgam-acili.jpg', 6),
((SELECT id FROM categories WHERE slug='tum-icecekler'), 'Şalgam Acısız', 'Acısız şalgam suyu', 65.00, 10.00, 'bar', '/assets/img/menu/salgam-acisiz.jpg', 7),
((SELECT id FROM categories WHERE slug='tum-icecekler'), 'Limonata', 'Ev yapımı', 90.00, 10.00, 'bar', '/assets/img/menu/limonata.jpg', 8),
((SELECT id FROM categories WHERE slug='tum-icecekler'), 'Su', '0.5 L', 35.00, 10.00, 'bar', '/assets/img/menu/su.jpg', 9);


INSERT INTO settings (setting_key, setting_value) VALUES
('restaurant_name', 'Crisp & Co.'),
('restaurant_tagline', 'Izgara tavuğun en iyi hali'),
('currency', 'TRY'),
('order_prefix', 'CHK'),
('fiscal_company_title', 'Crisp & Co.'),
('fiscal_vkn', ''),
('fiscal_tax_office', ''),
('fiscal_address', ''),
('fiscal_city', 'Antalya'),
('fiscal_phone', ''),
('fiscal_vat_rate', '10')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

INSERT INTO discount_codes (code, label, percent, is_active) VALUES
('YENI10', 'Yeni üye %10 indirim', 10.00, 1)
ON DUPLICATE KEY UPDATE label = VALUES(label), percent = VALUES(percent), is_active = 1;
