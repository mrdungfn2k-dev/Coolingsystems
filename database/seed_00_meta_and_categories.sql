BEGIN TRANSACTION;
CREATE TABLE categories (
  id          INTEGER PRIMARY KEY AUTOINCREMENT,
  parent_id   INTEGER REFERENCES categories(id) ON DELETE SET NULL,
  name        TEXT    NOT NULL,
  slug        TEXT    NOT NULL UNIQUE,
  icon        TEXT,
  sort_order  INTEGER NOT NULL DEFAULT 100,
  is_featured INTEGER NOT NULL DEFAULT 0,
  product_count INTEGER NOT NULL DEFAULT 0
, is_active INTEGER DEFAULT 1);
INSERT INTO "categories" ("id", "parent_id", "name", "slug", "icon", "sort_order", "is_featured", "product_count", "is_active") VALUES (11, NULL, 'Két Nước', 'ket-nuoc', NULL, 2, 0, 0, 0);
INSERT INTO "categories" ("id", "parent_id", "name", "slug", "icon", "sort_order", "is_featured", "product_count", "is_active") VALUES (21, NULL, 'Dàn Sưởi Điều Hòa', 'dan-suoi', NULL, 3, 0, 104, 1);
INSERT INTO "categories" ("id", "parent_id", "name", "slug", "icon", "sort_order", "is_featured", "product_count", "is_active") VALUES (22, NULL, 'Lốc Điều Hòa', 'loc-dieu-hoa', NULL, 4, 0, 655, 1);
INSERT INTO "categories" ("id", "parent_id", "name", "slug", "icon", "sort_order", "is_featured", "product_count", "is_active") VALUES (23, NULL, 'Van Tiết Lưu', 'van-tiet-luu', NULL, 6, 0, 194, 1);
INSERT INTO "categories" ("id", "parent_id", "name", "slug", "icon", "sort_order", "is_featured", "product_count", "is_active") VALUES (24, NULL, 'Van Đuôi Lốc', 'van-duoi-loc', NULL, 7, 0, 140, 1);
INSERT INTO "categories" ("id", "parent_id", "name", "slug", "icon", "sort_order", "is_featured", "product_count", "is_active") VALUES (25, NULL, 'Motor, Quạt Dàn Nóng', 'motor-quat-dan-nong', NULL, 8, 0, 166, 1);
INSERT INTO "categories" ("id", "parent_id", "name", "slug", "icon", "sort_order", "is_featured", "product_count", "is_active") VALUES (26, NULL, 'Motor, Quạt Dàn Lạnh', 'motor-quat-dan-lanh', NULL, 9, 0, 414, 1);
INSERT INTO "categories" ("id", "parent_id", "name", "slug", "icon", "sort_order", "is_featured", "product_count", "is_active") VALUES (27, NULL, 'Phin Lọc Ga', 'phin-loc-ga', NULL, 10, 0, 34, 1);
INSERT INTO "categories" ("id", "parent_id", "name", "slug", "icon", "sort_order", "is_featured", "product_count", "is_active") VALUES (31, 11, 'Ket Nuoc Con', 'ket-nuoc-con', NULL, 1, 0, 0, 0);
INSERT INTO "categories" ("id", "parent_id", "name", "slug", "icon", "sort_order", "is_featured", "product_count", "is_active") VALUES (41, NULL, 'Ống dẫn gas điều hòa', 'ong-dan-gas-dieu-hoa', NULL, 12, 0, 121, 1);
INSERT INTO "categories" ("id", "parent_id", "name", "slug", "icon", "sort_order", "is_featured", "product_count", "is_active") VALUES (42, NULL, 'Mô tơ quạt gió điều hòa', 'mo-to-quat-gio-dieu-hoa', NULL, 13, 0, 0, 1);
INSERT INTO "categories" ("id", "parent_id", "name", "slug", "icon", "sort_order", "is_featured", "product_count", "is_active") VALUES (43, NULL, 'Dàn nóng điều hòa', 'dan-nong-dieu-hoa', NULL, 14, 0, 382, 1);
INSERT INTO "categories" ("id", "parent_id", "name", "slug", "icon", "sort_order", "is_featured", "product_count", "is_active") VALUES (44, NULL, 'Cảm biến áp suất gas', 'cam-bien-ap-suat-gas', NULL, 15, 0, 87, 1);
INSERT INTO "categories" ("id", "parent_id", "name", "slug", "icon", "sort_order", "is_featured", "product_count", "is_active") VALUES (45, NULL, 'Van tiết lưu điều hòa', 'van-tiet-luu-dieu-hoa', NULL, 16, 0, 0, 1);
INSERT INTO "categories" ("id", "parent_id", "name", "slug", "icon", "sort_order", "is_featured", "product_count", "is_active") VALUES (46, NULL, 'Dàn lạnh điều hòa', 'dan-lanh-dieu-hoa', NULL, 17, 0, 538, 1);
INSERT INTO "categories" ("id", "parent_id", "name", "slug", "icon", "sort_order", "is_featured", "product_count", "is_active") VALUES (47, NULL, 'Lọc Gió Điều Hòa', 'loc-gio-dieu-hoa', NULL, 11, 0, 0, 1);
INSERT INTO "categories" ("id", "parent_id", "name", "slug", "icon", "sort_order", "is_featured", "product_count", "is_active") VALUES (48, NULL, 'Điều Hòa Điện & Phụ kiện', 'dieu-hoa-dien-phu-kien', '', 1, 1, 0, 1);
INSERT INTO "categories" ("id", "parent_id", "name", "slug", "icon", "sort_order", "is_featured", "product_count", "is_active") VALUES (49, NULL, 'Bộ đầu lốc điều hòa', 'bo-dau-loc-dieu-hoa', '', 5, 0, 0, 1);
CREATE TABLE brands (
  id          INTEGER PRIMARY KEY AUTOINCREMENT,
  name        TEXT    NOT NULL UNIQUE,
  slug        TEXT    NOT NULL UNIQUE,
  sort_order  INTEGER NOT NULL DEFAULT 100,
  product_count INTEGER NOT NULL DEFAULT 0
, image TEXT, is_active INTEGER DEFAULT 1);
INSERT INTO "brands" ("id", "name", "slug", "sort_order", "product_count", "image", "is_active") VALUES (1, 'Hyundai', 'hyundai', 1, 16, 'brand_1785953251.jfif', 1);
INSERT INTO "brands" ("id", "name", "slug", "sort_order", "product_count", "image", "is_active") VALUES (2, 'KIA', 'kia', 2, 8, 'brand_1785954188.jfif', 1);
INSERT INTO "brands" ("id", "name", "slug", "sort_order", "product_count", "image", "is_active") VALUES (3, 'Toyota', 'toyota', 3, 5, 'brand_1785953307.jfif', 1);
INSERT INTO "brands" ("id", "name", "slug", "sort_order", "product_count", "image", "is_active") VALUES (4, 'Mazda', 'mazda', 4, 1, 'brand_1785953333.jfif', 1);
INSERT INTO "brands" ("id", "name", "slug", "sort_order", "product_count", "image", "is_active") VALUES (5, 'Chevrolet', 'chevrolet', 5, 0, 'brand_1785141653.jfif', 1);
INSERT INTO "brands" ("id", "name", "slug", "sort_order", "product_count", "image", "is_active") VALUES (6, 'Daewoo', 'daewoo', 6, 0, 'brand_1785953346.jfif', 1);
INSERT INTO "brands" ("id", "name", "slug", "sort_order", "product_count", "image", "is_active") VALUES (7, 'Honda', 'honda', 7, 4, 'brand_1785953361.jfif', 1);
INSERT INTO "brands" ("id", "name", "slug", "sort_order", "product_count", "image", "is_active") VALUES (8, 'Ford', 'ford', 8, 0, 'brand_1785140518.jfif', 1);
INSERT INTO "brands" ("id", "name", "slug", "sort_order", "product_count", "image", "is_active") VALUES (9, 'Mitsubishi', 'mitsubishi', 9, 0, 'brand_1785140573.png', 1);
INSERT INTO "brands" ("id", "name", "slug", "sort_order", "product_count", "image", "is_active") VALUES (10, 'Nissan', 'nissan', 10, 0, 'brand_1785140759.jfif', 1);
INSERT INTO "brands" ("id", "name", "slug", "sort_order", "product_count", "image", "is_active") VALUES (11, 'Suzuki', 'suzuki', 23, 0, 'brand_1785954150.jfif', 1);
INSERT INTO "brands" ("id", "name", "slug", "sort_order", "product_count", "image", "is_active") VALUES (12, 'VinFast', 'vinfast', 24, 0, 'brand_1785140859.jfif', 1);
INSERT INTO "brands" ("id", "name", "slug", "sort_order", "product_count", "image", "is_active") VALUES (19, 'MG', 'mg', 11, 0, 'brand_1785140716.jfif', 1);
INSERT INTO "brands" ("id", "name", "slug", "sort_order", "product_count", "image", "is_active") VALUES (20, 'Mercedes-Benz', 'mercedes-benz', 12, 0, 'brand_1785954086.jfif', 1);
INSERT INTO "brands" ("id", "name", "slug", "sort_order", "product_count", "image", "is_active") VALUES (21, 'BMW', 'bmw', 13, 0, 'brand_1785140681.jfif', 1);
INSERT INTO "brands" ("id", "name", "slug", "sort_order", "product_count", "image", "is_active") VALUES (22, 'Audi', 'audi', 14, 0, 'brand_1785953372.avif', 1);
INSERT INTO "brands" ("id", "name", "slug", "sort_order", "product_count", "image", "is_active") VALUES (23, 'Volkswagen', 'volkswagen', 15, 0, 'brand_1785954129.jfif', 1);
INSERT INTO "brands" ("id", "name", "slug", "sort_order", "product_count", "image", "is_active") VALUES (24, 'Porsche', 'porsche', 16, 0, 'brand_1785954109.jfif', 1);
INSERT INTO "brands" ("id", "name", "slug", "sort_order", "product_count", "image", "is_active") VALUES (25, 'Volvo', 'volvo', 17, 0, 'brand_1785954142.jfif', 1);
INSERT INTO "brands" ("id", "name", "slug", "sort_order", "product_count", "image", "is_active") VALUES (26, 'Peugeot', 'peugeot', 18, 0, 'brand_1785954097.jfif', 1);
INSERT INTO "brands" ("id", "name", "slug", "sort_order", "product_count", "image", "is_active") VALUES (27, 'Land Rover', 'land-rover', 19, 0, 'brand_1785953992.jfif', 1);
INSERT INTO "brands" ("id", "name", "slug", "sort_order", "product_count", "image", "is_active") VALUES (28, 'Isuzu', 'isuzu', 20, 0, 'brand_1785140688.png', 1);
INSERT INTO "brands" ("id", "name", "slug", "sort_order", "product_count", "image", "is_active") VALUES (29, 'Subaru', 'subaru', 21, 0, 'brand_1785140815.jfif', 1);
INSERT INTO "brands" ("id", "name", "slug", "sort_order", "product_count", "image", "is_active") VALUES (30, 'Lexus', 'lexus', 22, 0, 'brand_1785954069.jfif', 1);
CREATE TABLE product_brands (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    slug TEXT NOT NULL,
    logo TEXT,
    description TEXT,
    sort_order INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT (datetime('now'))
);
INSERT INTO "product_brands" ("id", "name", "slug", "logo", "description", "sort_order", "created_at") VALUES (1, 'INTERNATIONAL', 'international', '', '', 11, '2026-07-11 07:29:03');
INSERT INTO "product_brands" ("id", "name", "slug", "logo", "description", "sort_order", "created_at") VALUES (2, 'DENSO', 'denso', 'pb_6a588ef9ef9fd.png', '', 12, '2026-07-16 07:57:45');
INSERT INTO "product_brands" ("id", "name", "slug", "logo", "description", "sort_order", "created_at") VALUES (3, 'SANDEN', 'sanden', 'pb_6a588f0e5e052.png', '', 13, '2026-07-16 07:58:06');
INSERT INTO "product_brands" ("id", "name", "slug", "logo", "description", "sort_order", "created_at") VALUES (4, 'VALEO', 'valeo', 'pb_6a588f1d384d2.png', '', 1, '2026-07-16 07:58:21');
INSERT INTO "product_brands" ("id", "name", "slug", "logo", "description", "sort_order", "created_at") VALUES (5, 'HANON', 'hanon', 'pb_6a588f2c6fd96.png', '', 2, '2026-07-16 07:58:36');
INSERT INTO "product_brands" ("id", "name", "slug", "logo", "description", "sort_order", "created_at") VALUES (6, 'DOOWON', 'doowon', 'pb_6a588f37e5a11.png', '', 3, '2026-07-16 07:58:47');
INSERT INTO "product_brands" ("id", "name", "slug", "logo", "description", "sort_order", "created_at") VALUES (7, 'MAHLE', 'mahle', 'pb_6a588f4e09df6.png', '', 4, '2026-07-16 07:59:10');
INSERT INTO "product_brands" ("id", "name", "slug", "logo", "description", "sort_order", "created_at") VALUES (8, 'MAGNETI MARELLI', 'magneti-marelli', '', '', 5, '2026-07-16 07:59:21');
INSERT INTO "product_brands" ("id", "name", "slug", "logo", "description", "sort_order", "created_at") VALUES (9, 'BEHR', 'behr', 'pb_6a588fa0043e8.png', '', 6, '2026-07-16 08:00:32');
INSERT INTO "product_brands" ("id", "name", "slug", "logo", "description", "sort_order", "created_at") VALUES (10, 'HCC', 'hcc', '', '', 7, '2026-07-16 08:00:56');
INSERT INTO "product_brands" ("id", "name", "slug", "logo", "description", "sort_order", "created_at") VALUES (11, 'KEIHIN', 'keihin', '', '', 8, '2026-07-16 08:01:25');
INSERT INTO "product_brands" ("id", "name", "slug", "logo", "description", "sort_order", "created_at") VALUES (12, 'FUJIKOKI', 'fujikoki', 'pb_6a588fe0ac01b.png', '', 9, '2026-07-16 08:01:36');
INSERT INTO "product_brands" ("id", "name", "slug", "logo", "description", "sort_order", "created_at") VALUES (13, 'KPRUI', 'kprui', 'pb_6a588ff066d0f.png', '', 10, '2026-07-16 08:01:52');
CREATE TABLE system_config (
  key         TEXT PRIMARY KEY,
  value       TEXT,
  updated_at  TEXT NOT NULL DEFAULT (strftime('%Y-%m-%dT%H:%M:%fZ','now'))
);
INSERT INTO "system_config" ("key", "value", "updated_at") VALUES ('commission_rate', '5', '2026-05-10T15:31:55.816Z');
INSERT INTO "system_config" ("key", "value", "updated_at") VALUES ('cod_max_amount', '10000000', '2026-05-10T15:31:55.816Z');
INSERT INTO "system_config" ("key", "value", "updated_at") VALUES ('withdrawal_min', '100000', '2026-05-10T15:31:55.816Z');
INSERT INTO "system_config" ("key", "value", "updated_at") VALUES ('withdrawal_max', '500000000', '2026-05-10T15:31:55.816Z');
INSERT INTO "system_config" ("key", "value", "updated_at") VALUES ('order_payment_timeout_min', '30', '2026-05-10T15:31:55.816Z');
INSERT INTO "system_config" ("key", "value", "updated_at") VALUES ('order_bank_transfer_timeout_hour', '24', '2026-05-10T15:31:55.816Z');
INSERT INTO "system_config" ("key", "value", "updated_at") VALUES ('auto_complete_days', '7', '2026-05-10T15:31:55.816Z');
INSERT INTO "system_config" ("key", "value", "updated_at") VALUES ('free_shipping_threshold', '0', '2026-05-30 08:45:03');
INSERT INTO "system_config" ("key", "value", "updated_at") VALUES ('payment_qr_image', 'qr_1779273077.jpg', '2026-05-20T10:31:17.204Z');
INSERT INTO "system_config" ("key", "value", "updated_at") VALUES ('payment_bank_name', 'MB Bank', '2026-06-08T11:20:55.834Z');
INSERT INTO "system_config" ("key", "value", "updated_at") VALUES ('payment_account_name', 'NGUYEN VAN A', '2026-06-08T11:20:55.841Z');
INSERT INTO "system_config" ("key", "value", "updated_at") VALUES ('payment_account_number', '2501092004', '2026-06-08T11:20:55.842Z');
INSERT INTO "system_config" ("key", "value", "updated_at") VALUES ('social_youtube', '', '2026-05-19T01:52:04.536Z');
INSERT INTO "system_config" ("key", "value", "updated_at") VALUES ('payment_account_holder', '', '2026-05-19T01:52:04.536Z');
INSERT INTO "system_config" ("key", "value", "updated_at") VALUES ('contact_hotline', '0703 070 361', '2026-05-19T01:52:04.536Z');
INSERT INTO "system_config" ("key", "value", "updated_at") VALUES ('contact_email', 'cskh@cooling.vn', '2026-06-08 11:20:51');
INSERT INTO "system_config" ("key", "value", "updated_at") VALUES ('contact_address', '123 Truong Chinh, Dong Da, Hải Phòng', '2026-06-08 11:20:51');
INSERT INTO "system_config" ("key", "value", "updated_at") VALUES ('site_phone', '0704070418', '2026-08-11 03:42:58');
INSERT INTO "system_config" ("key", "value", "updated_at") VALUES ('site_logo', 'logo_1780242378.jpg', '2026-05-31 15:46:18');
INSERT INTO "system_config" ("key", "value", "updated_at") VALUES ('default_tax_rate', '0', '2026-05-19 09:45:08');
INSERT INTO "system_config" ("key", "value", "updated_at") VALUES ('default_shipping_fee', '0', '2026-05-30 08:45:03');
INSERT INTO "system_config" ("key", "value", "updated_at") VALUES ('discount_quantity_threshold', '0', '2026-06-06 08:11:00');
INSERT INTO "system_config" ("key", "value", "updated_at") VALUES ('discount_quantity_percent', '0', '2026-06-06 08:11:00');
INSERT INTO "system_config" ("key", "value", "updated_at") VALUES ('social_facebook', '', '2026-05-28T09:52:58.294Z');
INSERT INTO "system_config" ("key", "value", "updated_at") VALUES ('social_tiktok', '', '2026-05-28T09:52:58.292Z');
INSERT INTO "system_config" ("key", "value", "updated_at") VALUES ('social_whatsapp', '', '2026-05-28T09:52:58.288Z');
INSERT INTO "system_config" ("key", "value", "updated_at") VALUES ('social_zalo', 'https://zalo.me/0865856585', '2026-05-22T18:53:48.194Z');
INSERT INTO "system_config" ("key", "value", "updated_at") VALUES ('contact_hours', 'Thu 2 - Thu 7: 08:00 - 18:00. Chu nhat: 09:00 - 16:00.', '2026-06-08 11:20:51');
INSERT INTO "system_config" ("key", "value", "updated_at") VALUES ('hotline', '0947796471', '2026-05-24T11:19:35.778Z');
INSERT INTO "system_config" ("key", "value", "updated_at") VALUES ('payment_transfer_prefix', 'alo', '2026-06-08T11:20:55.844Z');
INSERT INTO "system_config" ("key", "value", "updated_at") VALUES ('newsletter_title', 'Đăng ký nhận ưu đãi', '2026-06-01T09:51:18.956Z');
INSERT INTO "system_config" ("key", "value", "updated_at") VALUES ('newsletter_subtitle', 'Voucher 100K cho đơn đầu tiên — cập nhật khuyến mãi mỗi tuần', '2026-06-01T09:51:18.962Z');
INSERT INTO "system_config" ("key", "value", "updated_at") VALUES ('newsletter_voucher_amount', '100000', '2026-06-01T09:51:18.964Z');
INSERT INTO "system_config" ("key", "value", "updated_at") VALUES ('newsletter_voucher_code', 'UUDAI100K', '2026-06-01T09:51:18.965Z');
INSERT INTO "system_config" ("key", "value", "updated_at") VALUES ('newsletter_btn_text', 'Đăng ký nhận tin', '2026-06-01T09:51:18.967Z');
INSERT INTO "system_config" ("key", "value", "updated_at") VALUES ('company_name', 'COOLING PARTS SERVICE', '2026-06-08 11:20:53');
INSERT INTO "system_config" ("key", "value", "updated_at") VALUES ('shipping_origin_province', 'Hà Nội', '2026-06-06 06:52:42');
INSERT INTO "system_config" ("key", "value", "updated_at") VALUES ('shipping_rates', '[{"zone":"noi_tinh","base_weight":1000,"base_price":15000,"step_weight":500,"step_price":2500},{"zone":"noi_mien","base_weight":1000,"base_price":20000,"step_weight":500,"step_price":4000},{"zone":"can_mien","base_weight":1000,"base_price":28000,"step_weight":500,"step_price":6000},{"zone":"lien_mien","base_weight":1000,"base_price":32000,"step_weight":500,"step_price":8000}]', '2026-06-06 06:52:42');
INSERT INTO "system_config" ("key", "value", "updated_at") VALUES ('home_banners', '[{"img":"hb_promo2.png","link":"/products","title":"Giao hàng toàn quốc","active":1},{"img":"hb_promo1.png","link":"/products","title":"Phụ tùng chính hãng","active":1},{"img":"hb_promo3.png","link":"/promotions","title":"Khuyến mãi đặc biệt","active":1}]', '2026-06-10 13:14:59');
INSERT INTO "system_config" ("key", "value", "updated_at") VALUES ('hotline_list', '[{"label":"CSKH & Dịch vụ","phone":"0704.0704.18"},{"label":"Kĩ thuật & Bảo Hành","phone":"0704.0704.18"},{"label":"Bán Buôn","phone":"0705.0705.26"},{"label":"Bán Buôn","phone":"0705.0705.28"},{"label":"Bán lẻ","phone":"0705.0705.26"}]', '2026-08-11 03:42:58');
CREATE TABLE "users" (
  id              INTEGER PRIMARY KEY AUTOINCREMENT,
  role            TEXT    NOT NULL CHECK (role IN ('customer','partner','admin','staff')),
  email           TEXT    NOT NULL UNIQUE,
  phone           TEXT,
  password_hash   TEXT    NOT NULL,
  full_name       TEXT    NOT NULL,
  avatar          TEXT,
  status          TEXT    NOT NULL DEFAULT 'active' CHECK (status IN ('active','locked','pending')),
  review_strikes  INTEGER NOT NULL DEFAULT 0,
  review_blocked_until TEXT,
  created_at      TEXT    NOT NULL DEFAULT (strftime('%Y-%m-%dT%H:%M:%fZ','now')),
  updated_at      TEXT    NOT NULL DEFAULT (strftime('%Y-%m-%dT%H:%M:%fZ','now'))
, email_verified INTEGER DEFAULT 0, email_token TEXT, otp_code TEXT, otp_expires TEXT, address TEXT, suspended_until TEXT, notes TEXT, verify_token TEXT, reset_token TEXT, reset_expires TEXT, is_superadmin INTEGER DEFAULT 0, is_verified_garage INTEGER DEFAULT 0, agency_tier_id INTEGER DEFAULT 1, custom_commission_rate REAL DEFAULT 0, referral_code TEXT, referred_by_agent_id INTEGER, garage_name TEXT);
INSERT INTO "users" ("id", "role", "email", "phone", "password_hash", "full_name", "avatar", "status", "review_strikes", "review_blocked_until", "created_at", "updated_at", "email_verified", "email_token", "otp_code", "otp_expires", "address", "suspended_until", "notes", "verify_token", "reset_token", "reset_expires", "is_superadmin", "is_verified_garage", "agency_tier_id", "custom_commission_rate", "referral_code", "referred_by_agent_id", "garage_name") VALUES (1, 'admin', 'admin@cooling.vn', '0865856585', '$2y$10$Fh/ljtzIp7XXsIH5OLzWvefb4oztq0gPcqah83eDzFfEqoXc0K52i', 'Admin Cooling', NULL, 'active', 0, NULL, '2026-05-10T15:31:55.970Z', '2026-06-05 14:34:05', 1, NULL, '272131', '2026-05-20 16:10:57', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 1, 0.0, NULL, NULL, NULL);
INSERT INTO "users" ("id", "role", "email", "phone", "password_hash", "full_name", "avatar", "status", "review_strikes", "review_blocked_until", "created_at", "updated_at", "email_verified", "email_token", "otp_code", "otp_expires", "address", "suspended_until", "notes", "verify_token", "reset_token", "reset_expires", "is_superadmin", "is_verified_garage", "agency_tier_id", "custom_commission_rate", "referral_code", "referred_by_agent_id", "garage_name") VALUES (5, 'partner', 'pmc@partner.vn', '0986103595', '$2a$08$Xc4tMCcvHK2uMPmGvB2nvenEWfdE88ST9ybb5T8sbsSYwJjiZoVKW', 'PMC Vietnam Co.,Ltd', NULL, 'active', 0, NULL, '2026-05-10T15:31:56.083Z', '2026-05-18T04:31:05.950Z', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 1, 0.0, NULL, NULL, NULL);
INSERT INTO "users" ("id", "role", "email", "phone", "password_hash", "full_name", "avatar", "status", "review_strikes", "review_blocked_until", "created_at", "updated_at", "email_verified", "email_token", "otp_code", "otp_expires", "address", "suspended_until", "notes", "verify_token", "reset_token", "reset_expires", "is_superadmin", "is_verified_garage", "agency_tier_id", "custom_commission_rate", "referral_code", "referred_by_agent_id", "garage_name") VALUES (125, 'admin', 'superadmin@coolingsystem.vn', '', '$2y$10$x0/JsfbXFjHxf2QWm5PCPOiQoCWx7FoeYeF7RPSoIZBVpCdXSmZ5i', 'Super Admin', NULL, 'active', 0, NULL, '2026-06-05T14:48:04.513Z', '2026-06-05T14:48:04.513Z', 1, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, 1, 0, 1, 0.0, NULL, NULL, NULL);
INSERT INTO "users" ("id", "role", "email", "phone", "password_hash", "full_name", "avatar", "status", "review_strikes", "review_blocked_until", "created_at", "updated_at", "email_verified", "email_token", "otp_code", "otp_expires", "address", "suspended_until", "notes", "verify_token", "reset_token", "reset_expires", "is_superadmin", "is_verified_garage", "agency_tier_id", "custom_commission_rate", "referral_code", "referred_by_agent_id", "garage_name") VALUES (127, 'staff', 'rbac-test-warehouse-20260719@coolingsystems.vn', NULL, '$2y$10$f.OaHC/37iI4u4Qn4l/FfOCznN2tZ9gbuyt2yWAjnFMuC9JzDRVG2', 'Tài khoản kiểm thử RBAC - Kho', NULL, 'active', 0, NULL, '2026-07-18 17:20:29', '2026-07-18 17:20:29', 1, NULL, NULL, NULL, NULL, NULL, 'Tài khoản kiểm thử RBAC nội bộ.', NULL, NULL, NULL, 0, 0, 1, 0.0, NULL, NULL, NULL);
INSERT INTO "users" ("id", "role", "email", "phone", "password_hash", "full_name", "avatar", "status", "review_strikes", "review_blocked_until", "created_at", "updated_at", "email_verified", "email_token", "otp_code", "otp_expires", "address", "suspended_until", "notes", "verify_token", "reset_token", "reset_expires", "is_superadmin", "is_verified_garage", "agency_tier_id", "custom_commission_rate", "referral_code", "referred_by_agent_id", "garage_name") VALUES (128, 'staff', 'rbac-test-technician-phase58@coolingsystems.vn', NULL, '$2y$10$mCVGDs1CsvdZca5asW1RaOHpo1IPSRJYt2usA//yccGeH//w94EKK', 'Tài khoản kỹ thuật kiểm thử', NULL, 'active', 0, NULL, '2026-07-19 02:01:11', '2026-07-19 02:01:11', 1, NULL, NULL, NULL, NULL, NULL, 'Tài khoản kiểm thử nội bộ cho giai đoạn hiệu suất kỹ thuật P113.', NULL, NULL, NULL, 0, 0, 1, 0.0, NULL, NULL, NULL);
INSERT INTO "users" ("id", "role", "email", "phone", "password_hash", "full_name", "avatar", "status", "review_strikes", "review_blocked_until", "created_at", "updated_at", "email_verified", "email_token", "otp_code", "otp_expires", "address", "suspended_until", "notes", "verify_token", "reset_token", "reset_expires", "is_superadmin", "is_verified_garage", "agency_tier_id", "custom_commission_rate", "referral_code", "referred_by_agent_id", "garage_name") VALUES (130, 'customer', 'mrdungfn2k@gmail.com', '0947795471', '$2y$10$alx4vmF7obe4pejhTy2t0.fG/JgRnc590ud.0Aa7HkHS1dZ4ZDw3.', 'Mạnh Dũng', NULL, 'active', 0, NULL, '2026-07-22T05:48:30.665Z', '2026-07-22T05:48:30.665Z', 0, NULL, NULL, NULL, 'Kỳ đồng', NULL, NULL, '7252673b5033cf20a364adffd0f9449d3a9e5708f929bd0b70eb70e69434451f', NULL, NULL, 0, 0, 1, 0.0, NULL, NULL, NULL);
INSERT INTO "users" ("id", "role", "email", "phone", "password_hash", "full_name", "avatar", "status", "review_strikes", "review_blocked_until", "created_at", "updated_at", "email_verified", "email_token", "otp_code", "otp_expires", "address", "suspended_until", "notes", "verify_token", "reset_token", "reset_expires", "is_superadmin", "is_verified_garage", "agency_tier_id", "custom_commission_rate", "referral_code", "referred_by_agent_id", "garage_name") VALUES (131, 'customer', 'guest_17848666708766@guest.local', '0705070528', '$2y$10$lLcbSa0bOh8ZpdYSEgasEeG4bMUB1ITDYScDZTarQYNv8MS0EKDvC', 'Đỗ Minh Trang', NULL, 'active', 0, NULL, '2026-07-24 11:17:50', '2026-07-24T04:17:50.421Z', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 1, 0.0, NULL, NULL, NULL);
INSERT INTO "users" ("id", "role", "email", "phone", "password_hash", "full_name", "avatar", "status", "review_strikes", "review_blocked_until", "created_at", "updated_at", "email_verified", "email_token", "otp_code", "otp_expires", "address", "suspended_until", "notes", "verify_token", "reset_token", "reset_expires", "is_superadmin", "is_verified_garage", "agency_tier_id", "custom_commission_rate", "referral_code", "referred_by_agent_id", "garage_name") VALUES (132, 'partner', 'dailytest@coolingsystems.vn', '0909123456', 'hash_test', 'Đại Lý Ô Tô Hà Nội', NULL, 'active', 0, NULL, '2026-08-05T02:51:52.493Z', '2026-08-05T02:51:52.493Z', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, 3, 0.0, 'AGENT-0088', NULL, 'Đại Lý Phụ Tùng Hà Nội');
INSERT INTO "users" ("id", "role", "email", "phone", "password_hash", "full_name", "avatar", "status", "review_strikes", "review_blocked_until", "created_at", "updated_at", "email_verified", "email_token", "otp_code", "otp_expires", "address", "suspended_until", "notes", "verify_token", "reset_token", "reset_expires", "is_superadmin", "is_verified_garage", "agency_tier_id", "custom_commission_rate", "referral_code", "referred_by_agent_id", "garage_name") VALUES (133, 'partner', 'garatest@coolingsystems.vn', '0988777666', 'hash_test', 'Gara Sửa Chữa Xe Đức', NULL, 'active', 0, NULL, '2026-08-05T02:51:52.496Z', '2026-08-05T02:51:52.496Z', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, 1, 0.0, NULL, 132, 'Gara Sửa Chữa Xe Đức');
INSERT INTO "users" ("id", "role", "email", "phone", "password_hash", "full_name", "avatar", "status", "review_strikes", "review_blocked_until", "created_at", "updated_at", "email_verified", "email_token", "otp_code", "otp_expires", "address", "suspended_until", "notes", "verify_token", "reset_token", "reset_expires", "is_superadmin", "is_verified_garage", "agency_tier_id", "custom_commission_rate", "referral_code", "referred_by_agent_id", "garage_name") VALUES (134, 'customer', 'zalo_0936253625@miniapp.coolingsystems.vn', '0936253625', '$2y$10$G0t6FKFdV1ktWssONXNRJe3iWoIXzL/yFLRs1IPP00XvOXpkqQ9tO', 'Nguyen anh', NULL, 'active', 0, NULL, '2026-08-06T07:04:01.990Z', '2026-08-06T07:04:01.990Z', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 1, 0.0, NULL, NULL, NULL);
CREATE TABLE agency_tiers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    tier_name TEXT NOT NULL,
    commission_percent REAL DEFAULT 5.0,
    min_monthly_sales REAL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
INSERT INTO "agency_tiers" ("id", "tier_name", "commission_percent", "min_monthly_sales", "created_at") VALUES (1, 'Đại lý Chuẩn', 5.0, 0.0, '2026-08-05 02:49:30');
INSERT INTO "agency_tiers" ("id", "tier_name", "commission_percent", "min_monthly_sales", "created_at") VALUES (2, 'Đại lý Đồng', 7.0, 50000000.0, '2026-08-05 02:49:30');
INSERT INTO "agency_tiers" ("id", "tier_name", "commission_percent", "min_monthly_sales", "created_at") VALUES (3, 'Đại lý Bạc', 10.0, 150000000.0, '2026-08-05 02:49:30');
INSERT INTO "agency_tiers" ("id", "tier_name", "commission_percent", "min_monthly_sales", "created_at") VALUES (4, 'Đại lý Vàng', 12.0, 300000000.0, '2026-08-05 02:49:30');
COMMIT;
