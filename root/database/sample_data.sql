-- Sample data for wedding invitation website

-- Admin user
INSERT INTO admins (username, password, email, full_name, role, is_active) 
VALUES 
('admin', '$2y$10$YourHashedPasswordHere', 'admin@example.com', 'Administrator', 'superadmin', 1);

-- Wedding data
INSERT INTO weddings (couple_name, wedding_date, venue, title, theme, is_active) 
VALUES 
('John & Jane', '2024-12-25 14:00:00', 'Grand Ballroom Hotel Indonesia', 'Pernikahan John & Jane', 'royal-elegance', 1);

-- Template settings for royal-elegance
INSERT INTO template_settings (template_name, colors, fonts, custom_css, is_active) 
VALUES 
('royal-elegance', 
 '{"primary": "#8B4513", "secondary": "#D4AF37", "accent": "#C19A6B"}',
 '{"heading": "Playfair Display", "body": "Crimson Text"}',
 '/* Custom CSS for royal elegance theme */',
 1);

-- Couple information
INSERT INTO couples (groom_name, bride_name, groom_father, groom_mother, bride_father, bride_mother, groom_bio, bride_bio, love_story) 
VALUES 
('John Smith',
 'Jane Doe',
 'Mr. Robert Smith',
 'Mrs. Sarah Smith',
 'Mr. Michael Doe',
 'Mrs. Elizabeth Doe',
 'Putra pertama dari keluarga Smith, bekerja sebagai software engineer di perusahaan teknologi terkemuka.',
 'Putri kedua dari keluarga Doe, seorang dokter spesialis anak yang penuh perhatian.',
 '[{"date": "2018-05-20", "title": "Pertemuan Pertama", "description": "Bertemu di acara seminar teknologi di Jakarta."},
   {"date": "2019-02-14", "title": "Mulai Berkencan", "description": "Kencan pertama di restoran Italia favorit."},
   {"date": "2022-08-17", "title": "Lamaran", "description": "John melamar Jane di pantai saat matahari terbenam."}]');

-- Sample events
INSERT INTO events (event_type, event_date, event_time, venue, description, maps_link, icon) 
VALUES 
('Akad Nikah', '2024-12-25', '08:00:00', 'Masjid Agung Jakarta', 'Akad nikah akan dilaksanakan dengan khidmat di Masjid Agung Jakarta', 'https://maps.google.com/?q=Masjid+Agung+Jakarta', 'fas fa-mosque'),
('Resepsi', '2024-12-25', '14:00:00', 'Grand Ballroom Hotel Indonesia', 'Resepsi pernikahan dengan tema Royal Elegance', 'https://maps.google.com/?q=Hotel+Indonesia', 'fas fa-glass-cheers'),
('Unduh Mantu', '2024-12-26', '10:00:00', 'Rumah Kedua Orang Tua', 'Acara silaturahmi dan ramah tamah keluarga besar', 'https://maps.google.com/?q=Jakarta+Selatan', 'fas fa-home');

-- Sample gallery
INSERT INTO gallery (category, image_url, thumbnail_url, caption, display_order, is_active) 
VALUES 
('prewedding', 'assets/images/gallery/prewed-1.jpg', 'assets/images/gallery/thumb/prewed-1.jpg', 'Momen romantis di taman', 1, 1),
('prewedding', 'assets/images/gallery/prewed-2.jpg', 'assets/images/gallery/thumb/prewed-2.jpg', 'Foto di pantai saat sunset', 2, 1),
('engagement', 'assets/images/gallery/engage-1.jpg', 'assets/images/gallery/thumb/engage-1.jpg', 'Momen lamaran', 3, 1),
('family', 'assets/images/gallery/family-1.jpg', 'assets/images/gallery/thumb/family-1.jpg', 'Bersama keluarga besar', 4, 1);

-- Sample bank accounts
INSERT INTO bank_accounts (bank_name, account_number, account_name, qris_code, is_active) 
VALUES 
('BCA', '1234567890', 'John Smith', '00020101021126660014ID.CO.QRIS.WWW0118936009110012345678900215ID1234567890303UMI5204581253033605802ID5914JOHN SMITH6007JAKARTA61051234562070703A016304F5A0', 1),
('Mandiri', '0987654321', 'Jane Doe', '00020101021126660014ID.CO.QRIS.WWW011893600911009876543210215ID09876543210303UMI5204581253033605802ID5914JANE DOE6007JAKARTA61051234562070703A016304F5A0', 1);

-- Sample e-wallets
INSERT INTO ewallets (wallet_name, account_number, account_name, qr_code, is_active) 
VALUES 
('GoPay', '081234567890', 'John Smith', 'assets/images/qr/gopay.jpg', 1),
('OVO', '081234567890', 'Jane Doe', 'assets/images/qr/ovo.jpg', 1),
('Dana', '081234567890', 'John Smith', 'assets/images/qr/dana.jpg', 1);

-- Sample gift registry
INSERT INTO gift_registry (item_name, description, price, store_link, image_url, is_reserved) 
VALUES 
('Set Peralatan Masak', 'Set peralatan masak premium dengan 10 item', 1500000, 'https://tokopedia.com/link-peralatan-masak', 'assets/images/gifts/cooking-set.jpg', 0),
('Blender Multifungsi', 'Blender dengan 8 fungsi dan kapasitas besar', 850000, 'https://shopee.com/link-blender', 'assets/images/gifts/blender.jpg', 0),
('Set Peralatan Makan', 'Set peralatan makan untuk 6 orang', 1200000, 'https://blibli.com/link-tableware', 'assets/images/gifts/tableware.jpg', 1);

-- Sample guests (for testing)
INSERT INTO guests (name, phone, email, invitation_code, guest_group, attendance_status, guest_count, message, created_at) 
VALUES 
('Budi Santoso', '081234567891', 'budi@example.com', 'FAM001', 'Family', 'confirmed', 2, 'Selamat untuk kalian berdua! Semoga langgeng selalu.', NOW()),
('Siti Nurhaliza', '081234567892', 'siti@example.com', 'FRD001', 'Friends', 'pending', 1, NULL, NOW()),
('Andi Wijaya', '081234567893', 'andi@example.com', 'COL001', 'Colleagues', 'confirmed', 1, 'Congratulations! Happy for both of you.', NOW()),
('Dewi Lestari', '081234567894', 'dewi@example.com', 'FRD002', 'Friends', 'confirmed', 3, 'Doa terbaik untuk pernikahan kalian. Bahagia selalu!', NOW()),
('Rudi Hartono', '081234567895', 'rudi@example.com', 'FAM002', 'Family', 'declined', 0, 'Maaf tidak bisa hadir. Semoga acara lancar.', NOW());

-- Sample messages from guests
INSERT INTO messages (guest_id, name, relation, message, privacy, like_count, created_at) 
VALUES 
(1, 'Budi Santoso', 'family', 'Selamat menempuh hidup baru! Semoga menjadi keluarga yang sakinah, mawaddah, warahmah.', 'public', 5, NOW()),
(3, 'Andi Wijaya', 'colleague', 'Congratulations to both of you! Wishing you a lifetime of love and happiness.', 'public', 3, NOW()),
(4, 'Dewi Lestari', 'friend', 'Akhirnya sampai juga di hari bahagia ini. Semoga menjadi pasangan yang saling melengkapi.', 'public', 7, NOW());

-- Sample payments
INSERT INTO payments (guest_id, transaction_id, amount, payment_method, payment_status, notes, created_at) 
VALUES 
(1, 'TRX001', 500000, 'BCA', 'success', 'Transfer via mobile banking', NOW()),
(3, 'TRX002', 750000, 'GoPay', 'success', 'QRIS payment', NOW()),
(4, 'TRX003', 1000000, 'Mandiri', 'success', 'Transfer via ATM', NOW());

-- WhatsApp templates
INSERT INTO whatsapp_templates (template_name, template_content, variables, is_active) 
VALUES 
('invitation', 'Assalamualaikum warahmatullahi wabarakatuh

*INVITATION WEDDING*

Kepada Yth. {name}

Kami mengundang Bapak/Ibu/Saudara/i untuk menghadiri acara pernikahan:

*{groom_name} & {bride_name}*

📅 {date}
⏰ {time}
📍 {venue}

Mohon konfirmasi kehadiran melalui link: {rsvp_link}

Detail acara: {wedding_link}

Merupakan suatu kehormatan dan kebahagiaan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir dan memberikan doa restu.

Hormat kami,
Keluarga Besar {groom_name} & {bride_name}',
 '["name", "groom_name", "bride_name", "date", "time", "venue", "rsvp_link", "wedding_link"]', 1),

('payment_confirmation', 'Halo {name}

Terima kasih telah mengirimkan hadiah untuk pernikahan kami.

📋 *Detail Pembayaran*
ID Transaksi: {transaction_id}
Tanggal: {date}
Jumlah: Rp {amount}
Metode: {payment_method}

Status: ✅ Berhasil

Terima kasih atas doa dan hadiahnya. Kami sangat menghargai kebaikan Anda.

Salam hangat,
{groom_name} & {bride_name}',
 '["name", "transaction_id", "date", "amount", "payment_method", "groom_name", "bride_name"]', 1),

('rsvp_reminder', 'Halo {name}

Mengingatkan untuk konfirmasi kehadiran di pernikahan kami:

📅 {date}
⏰ {time}
📍 {venue}

Mohon konfirmasi melalui: {rsvp_link}

Terima kasih,
{groom_name} & {bride_name}',
 '["name", "date", "time", "venue", "rsvp_link", "groom_name", "bride_name"]', 1);

-- PWA settings
INSERT INTO pwa_settings (app_name, short_name, theme_color, background_color, display, orientation, is_active) 
VALUES 
('Undangan Digital', 'Wedding', '#8B4513', '#F8F4EC', 'standalone', 'portrait', 1);

-- Site settings
INSERT INTO site_settings (setting_key, setting_value, setting_type) 
VALUES 
('site_title', 'Undangan Digital Pernikahan', 'general'),
('site_description', 'Website undangan digital pernikahan dengan fitur lengkap', 'general'),
('site_keywords', 'undangan digital, wedding, pernikahan, invitation', 'general'),
('whatsapp_api_key', 'your_whatsapp_api_key_here', 'api'),
('whatsapp_sender_number', '6281234567890', 'api'),
('qris_api_key', 'your_qris_api_key_here', 'api'),
('qris_merchant_id', 'your_merchant_id', 'api'),
('enable_whatsapp', '1', 'feature'),
('enable_qris', '1', 'feature'),
('enable_pwa', '1', 'feature'),
('guest_password', '123456', 'security');

-- Analytics sample data
INSERT INTO analytics (page_views, unique_visitors, date_recorded) 
VALUES 
(150, 45, CURDATE() - INTERVAL 1 DAY),
(230, 67, CURDATE() - INTERVAL 2 DAY),
(180, 52, CURDATE() - INTERVAL 3 DAY);

-- Sample logs
INSERT INTO logs (log_type, description, ip_address, user_agent, created_at) 
VALUES 
('login', 'Admin login successful', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', NOW()),
('payment', 'Payment received from Budi Santoso', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', NOW()),
('rsvp', 'RSVP submitted by Andi Wijaya', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', NOW());

-- Create sample notification
INSERT INTO notifications (title, message, notification_type, is_read, created_at) 
VALUES 
('Pembayaran Baru', 'Pembayaran Rp 500,000 dari Budi Santoso', 'payment', 0, NOW()),
('RSVP Baru', 'Andi Wijaya mengkonfirmasi kehadiran', 'rsvp', 0, NOW()),
('Pesan Baru', 'Dewi Lestari mengirimkan ucapan', 'message', 0, NOW());
