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

INSERT INTO menu_items (category_id, name, description, price, station, sort_order) VALUES
((SELECT id FROM categories WHERE slug='izgara'), 'Izgara Tavuk Şiş', 'Marine edilmiş tavuk şiş, köz biber', 260.00, 'kitchen', 1),
((SELECT id FROM categories WHERE slug='izgara'), 'Acılı Kanat', '8 adet acılı ızgara kanat', 245.00, 'kitchen', 2),
((SELECT id FROM categories WHERE slug='izgara'), 'Ballı Hardallı Tavuk', 'Ballı hardal glaze, yeşillik', 275.00, 'kitchen', 3),
((SELECT id FROM categories WHERE slug='menuler'), 'Chicken Menü', 'Burger + patates + içecek', 320.00, 'kitchen', 1),
((SELECT id FROM categories WHERE slug='menuler'), 'Aile Menüsü', '2 şiş + 2 kanat + patates + 4 içecek', 890.00, 'kitchen', 2),
((SELECT id FROM categories WHERE slug='durumler'), 'Tavuk Dürüm', 'Izgara tavuk, lavaş, turşu', 210.00, 'kitchen', 1),
((SELECT id FROM categories WHERE slug='durumler'), 'Köfte Dürüm', 'Izgara köfte, sos, yeşillik', 230.00, 'kitchen', 2),
((SELECT id FROM categories WHERE slug='burgerler'), 'Chicken Burger', 'Özel sos, çıtır tavuk, turşu', 220.00, 'kitchen', 1),
((SELECT id FROM categories WHERE slug='burgerler'), 'Cheese Burger', 'Cheddar, özel sos', 240.00, 'kitchen', 2),
((SELECT id FROM categories WHERE slug='yan-urunler'), 'Çıtır Patates', 'Ev yapımı baharatlı patates', 90.00, 'kitchen', 1),
((SELECT id FROM categories WHERE slug='yan-urunler'), 'Soğan Halkası', '6 adet çıtır soğan halkası', 95.00, 'kitchen', 2),
((SELECT id FROM categories WHERE slug='yan-urunler'), 'Coleslaw', 'Taze lahana salatası', 70.00, 'kitchen', 3),
((SELECT id FROM categories WHERE slug='tatlilar'), 'Sufle', 'Sıcak çikolatalı sufle', 140.00, 'kitchen', 1),
((SELECT id FROM categories WHERE slug='tatlilar'), 'Dondurma', '2 top', 90.00, 'bar', 2),
((SELECT id FROM categories WHERE slug='tum-icecekler'), 'Ayran', '300 ml', 45.00, 'bar', 1),
((SELECT id FROM categories WHERE slug='tum-icecekler'), 'Kola', '330 ml', 55.00, 'bar', 2),
((SELECT id FROM categories WHERE slug='tum-icecekler'), 'Limonata', 'Ev yapımı', 65.00, 'bar', 3),
((SELECT id FROM categories WHERE slug='tum-icecekler'), 'Su', '0.5 L', 25.00, 'bar', 4);

INSERT INTO settings (setting_key, setting_value) VALUES
('restaurant_name', 'Chicken'),
('restaurant_tagline', 'Izgara tavuğun en iyi hali'),
('currency', 'TRY'),
('order_prefix', 'CHK')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);
