-- ============================================================
-- MAKEUP STUDIO â€” Full Database Schema + Seed Data
-- Import via phpMyAdmin or: mysql -u root -p < makeup_studio.sql
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `makeup_studio`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `makeup_studio`;

-- -----------------------------------------------------------
-- Table: categories
-- -----------------------------------------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`       VARCHAR(100) NOT NULL,
  `slug`       VARCHAR(100) NOT NULL UNIQUE,
  `icon`       VARCHAR(100) DEFAULT NULL,
  `sort_order` TINYINT UNSIGNED DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `categories` (`name`, `slug`, `icon`, `sort_order`) VALUES
  ('Eyes', 'eyes', '👁️', 1),
  ('Face', 'face', '✨', 2),
  ('Lips', 'lips', '💋', 3);

-- -----------------------------------------------------------
-- Table: subcategories
-- -----------------------------------------------------------
DROP TABLE IF EXISTS `subcategories`;
CREATE TABLE `subcategories` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT UNSIGNED NOT NULL,
  `name`        VARCHAR(100) NOT NULL,
  `slug`        VARCHAR(100) NOT NULL UNIQUE,
  `sort_order`  TINYINT UNSIGNED DEFAULT 0,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `subcategories` (`category_id`, `name`, `slug`, `sort_order`) VALUES
  -- Eyes (category 1)
  (1, 'Eyeliner',       'eyeliner',        1),
  (1, 'Eyeshadow',      'eyeshadow',       2),
  (1, 'Mascara',        'mascara',         3),
  (1, 'Lashes',         'lashes',          4),
  -- Face (category 2)
  (2, 'Foundation',     'foundation',      1),
  (2, 'Concealer',      'concealer',       2),
  (2, 'Blush',          'blush',           3),
  (2, 'Highlighter',    'highlighter',     4),
  (2, 'Setting Spray',  'setting-spray',   5),
  (2, 'Compact Powder', 'compact-powder',  6),
  (2, 'Loose Powder',   'loose-powder',    7),
  (2, 'Contour',        'contour',         8),
  -- Lips (category 3)
  (3, 'Lip Liner',      'lip-liner',       1),
  (3, 'Lipstick',       'lipstick',        2),
  (3, 'Lip Gloss',      'lip-gloss',       3),
  (3, 'Lip Balm',       'lip-balm',        4);

-- -----------------------------------------------------------
-- Table: brands
-- -----------------------------------------------------------
DROP TABLE IF EXISTS `brands`;
CREATE TABLE `brands` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`        VARCHAR(100) NOT NULL,
  `slug`        VARCHAR(100) NOT NULL UNIQUE,
  `tagline`     VARCHAR(255) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `logo_path`   VARCHAR(255) DEFAULT NULL,
  `color`       VARCHAR(7)   DEFAULT '#c2185b',
  `sort_order`  TINYINT UNSIGNED DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `brands` (`name`, `slug`, `tagline`, `description`, `color`, `sort_order`) VALUES
  ('Maybelline',   'maybelline',   'Maybe She\'s Born With It',    'America\'s #1 cosmetics brand, trusted globally for bold, affordable beauty.',          '#ffcc00', 1),
  ('MARS',         'mars',         'Bold. Brave. Beautiful.',       'Indian cosmetics powerhouse known for vibrant pigments and long-lasting formulas.',      '#c0392b', 2),
  ('NARS',         'nars',         'Your Skin. But Better.',        'French luxury cosmetics â€” iconic packaging, cult-favourite shades and editorial looks.', '#1a1a1a', 3),
  ('Huda Beauty',  'huda-beauty',  'Life Is Short. Have Fun.',      'Dubai-born global sensation â€” from lashes to full glam, Huda does it all.',            '#d4af7a', 4);

-- -----------------------------------------------------------
-- Table: products
-- -----------------------------------------------------------
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `subcategory_id` INT UNSIGNED NOT NULL,
  `brand_id`       INT UNSIGNED NOT NULL,
  `name`           VARCHAR(255) NOT NULL,
  `slug`           VARCHAR(255) NOT NULL UNIQUE,
  `description`    TEXT DEFAULT NULL,
  `price`          DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `original_price` DECIMAL(10,2) DEFAULT NULL,
  `stock`          INT UNSIGNED DEFAULT 50,
  `image_path`     VARCHAR(255) DEFAULT 'assets/images/product-placeholder.jpg',
  `is_featured`    TINYINT(1) DEFAULT 0,
  `is_new`         TINYINT(1) DEFAULT 0,
  `is_bestseller`  TINYINT(1) DEFAULT 0,
  `created_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- FOREIGN KEYS
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_sub`   FOREIGN KEY (`subcategory_id`) REFERENCES `subcategories`(`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_products_brand` FOREIGN KEY (`brand_id`)       REFERENCES `brands`(`id`)        ON DELETE CASCADE;

-- === EYES > EYELINER (subcat 1) ===
INSERT INTO `products` (`subcategory_id`,`brand_id`,`name`,`slug`,`description`,`price`,`original_price`,`is_featured`,`is_new`,`is_bestseller`) VALUES
(1,1,'Maybelline Hyper Precise All Day Liner','maybelline-hyper-precise-liner','Ultra-fine felt tip delivers a sharp, precise line that lasts all day without smudging.',850.00,999.00,1,0,1),
(1,3,'NARS Eyeliner Stylo','nars-eyeliner-stylo','Creamy, waterproof pencil glides on effortlessly for intense colour in one stroke.',1850.00,NULL,0,1,0),
(1,4,'Huda Beauty Life Liner Dual Liner','huda-life-liner-dual','Double-ended precision felt tip + smudge brush for flawless cat eyes.',2200.00,2500.00,1,0,0);

-- === EYES > EYESHADOW (subcat 2) ===
INSERT INTO `products` (`subcategory_id`,`brand_id`,`name`,`slug`,`description`,`price`,`original_price`,`is_featured`,`is_new`,`is_bestseller`) VALUES
(2,4,'Huda Beauty Rose Gold Remastered Palette','huda-rose-gold-palette','18 iconic rose-gold shades â€” mattes, shimmers & foils for every occasion.',6500.00,7200.00,1,0,1),
(2,3,'NARS Eyeshadow Palette â€” Habanera','nars-habanera-palette','10 neutral-to-bold pops of color in a sophisticated editorial palette.',5200.00,NULL,0,0,0),
(2,2,'MARS 12-Shade Shimmer Palette','mars-shimmer-palette','Highly pigmented, buttery blendable shades that last 12 hours.',650.00,800.00,0,1,0);

-- === EYES > MASCARA (subcat 3) ===
INSERT INTO `products` (`subcategory_id`,`brand_id`,`name`,`slug`,`description`,`price`,`original_price`,`is_featured`,`is_new`,`is_bestseller`) VALUES
(3,1,'Maybelline Sky High Mascara','maybelline-sky-high-mascara','Buildable, telescoping formula lifts every lash to sky-high lengths without clumping.',950.00,1100.00,1,0,1),
(3,2,'MARS Volume Boost Mascara','mars-volume-boost-mascara','Fibre-infused formula gives 3Ã— volume and intense blackness in one coat.',450.00,550.00,0,1,0),
(3,4,'Huda Beauty Legit Lashes Mascara','huda-legit-lashes-mascara','Award-winning mascara for dramatic length, volume and zero flaking.',2800.00,NULL,0,0,1);

-- === EYES > LASHES (subcat 4) ===
INSERT INTO `products` (`subcategory_id`,`brand_id`,`name`,`slug`,`description`,`price`,`original_price`,`is_featured`,`is_new`,`is_bestseller`) VALUES
(4,4,'Huda Beauty Classic Lash â€” Samantha','huda-samantha-lash','Wispy, fluttery lashes that add drama without looking overdone. Reusable 25Ã— times.',1800.00,2000.00,1,0,1),
(4,1,'Maybelline Eye Studio Lashes','maybelline-eye-studio-lashes','Natural-looking false lashes with an invisible band for a seamless finish.',650.00,NULL,0,1,0),
(4,2,'MARS Strip Lash â€” Glam','mars-strip-lash-glam','Full, voluminous strip lash with flexible band for easy all-day wear.',380.00,450.00,0,0,0);

-- === FACE > FOUNDATION (subcat 5) ===
INSERT INTO `products` (`subcategory_id`,`brand_id`,`name`,`slug`,`description`,`price`,`original_price`,`is_featured`,`is_new`,`is_bestseller`) VALUES
(5,1,'Maybelline Fit Me Matte + Poreless Foundation','maybelline-fit-me-foundation','Lightweight formula with a natural matte finish â€” controls shine for up to 12 hours.',900.00,1050.00,1,0,1),
(5,3,'NARS Natural Radiant Longwear Foundation','nars-natural-radiant-foundation','Buildable, skin-like coverage with a healthy glow that lasts 16+ hours.',4500.00,NULL,0,0,1),
(5,4,'Huda Beauty #FauxFilter Foundation','huda-faux-filter-foundation','Full-coverage, photo-filter finish in 140 shades for every skin tone.',5500.00,6000.00,1,1,0);

-- === FACE > CONCEALER (subcat 6) ===
INSERT INTO `products` (`subcategory_id`,`brand_id`,`name`,`slug`,`description`,`price`,`original_price`,`is_featured`,`is_new`,`is_bestseller`) VALUES
(6,1,'Maybelline Fit Me Concealer','maybelline-fit-me-concealer','Lightweight, buildable coverage blends seamlessly to conceal dark circles and blemishes.',600.00,750.00,0,0,1),
(6,3,'NARS Radiant Creamy Concealer','nars-radiant-creamy-concealer','Cult-favourite creamy concealer â€” brightens, covers and doesn\'t crease.',3200.00,NULL,1,0,1),
(6,2,'MARS HD Concealer','mars-hd-concealer','High-definition formula for flawless, transfer-proof coverage all day.',420.00,500.00,0,1,0);

-- === FACE > BLUSH (subcat 7) ===
INSERT INTO `products` (`subcategory_id`,`brand_id`,`name`,`slug`,`description`,`price`,`original_price`,`is_featured`,`is_new`,`is_bestseller`) VALUES
(7,3,'NARS Blush â€” Orgasm','nars-blush-orgasm','Iconic golden-peach blush with a golden shimmer. The world\'s best-selling blush.',3500.00,NULL,1,0,1),
(7,4,'Huda Beauty Cheeky Tint Blush Stick','huda-cheeky-tint-blush','Blendable cream-to-powder blush stick in vivid, fade-proof shades.',2800.00,3200.00,1,1,0),
(7,2,'MARS Blusher Powder','mars-blusher-powder','Buildable, silky blush with a finely milled texture for a natural rosy glow.',380.00,450.00,0,0,0);

-- === FACE > HIGHLIGHTER (subcat 8) ===
INSERT INTO `products` (`subcategory_id`,`brand_id`,`name`,`slug`,`description`,`price`,`original_price`,`is_featured`,`is_new`,`is_bestseller`) VALUES
(8,4,'Huda Beauty 3D Highlighter Palette â€” Pink Sands','huda-3d-highlighter-pink','Three complementary highlighter shades for a glowing, dimensional finish.',5200.00,6000.00,1,0,1),
(8,1,'Maybelline Master Chrome Highlighter','maybelline-master-chrome','Molten-metallic highlighting powder for mirror-like, blinding shimmer.',900.00,1100.00,0,0,1),
(8,3,'NARS Hot Voodoo Highlighting Powder','nars-hot-voodoo-highlighter','Silky, light-reflecting powder for a luminous, ethereal glow.',4200.00,NULL,1,1,0);

-- === FACE > SETTING SPRAY (subcat 9) ===
INSERT INTO `products` (`subcategory_id`,`brand_id`,`name`,`slug`,`description`,`price`,`original_price`,`is_featured`,`is_new`,`is_bestseller`) VALUES
(9,4,'Huda Beauty Easy Bake Setting Spray','huda-easy-bake-setting-spray','Lightweight mist that locks makeup in place up to 24 hours in any condition.',3200.00,3500.00,1,1,0),
(9,1,'Maybelline Lifter Mist Setting Spray','maybelline-lifter-mist','Hydrating setting spray with hyaluronic acid for a fresh, glowing finish.',850.00,950.00,0,0,1),
(9,2,'MARS Long Lasting Setting Spray','mars-long-lasting-setting-spray','Lightweight, non-sticky setting spray for all-day makeup lock.',480.00,550.00,0,1,0);

-- === FACE > COMPACT POWDER (subcat 10) ===
INSERT INTO `products` (`subcategory_id`,`brand_id`,`name`,`slug`,`description`,`price`,`original_price`,`is_featured`,`is_new`,`is_bestseller`) VALUES
(10,1,'Maybelline Fit Me Pressed Powder','maybelline-fit-me-pressed-powder','Finely milled pressed powder for a smooth, natural finish with no flashback.',700.00,850.00,0,0,1),
(10,3,'NARS Light Reflecting Pressed Setting Powder','nars-light-reflecting-powder','Translucent, light-diffusing powder that reduces the appearance of fine lines.',4800.00,NULL,1,0,1),
(10,4,'Huda Beauty Easy Bake Powder â€” Mini','huda-easy-bake-mini','Travel-sized loose-bake powder in a pressed form for on-the-go touch-ups.',2500.00,2800.00,1,1,0);

-- === FACE > LOOSE POWDER (subcat 11) ===
INSERT INTO `products` (`subcategory_id`,`brand_id`,`name`,`slug`,`description`,`price`,`original_price`,`is_featured`,`is_new`,`is_bestseller`) VALUES
(11,4,'Huda Beauty Easy Bake Loose Baking & Setting Powder','huda-easy-bake-loose','Viral powder that bakes under-eye concealer for a flawless, crease-free finish.',4200.00,4800.00,1,0,1),
(11,3,'NARS Translucent Crystal Powder','nars-translucent-crystal-powder','Ultra-light powder that diffuses light to minimise imperfections.',5000.00,NULL,0,0,0),
(11,2,'MARS Banana Loose Powder','mars-banana-loose-powder','Yellow-toned correcting powder for brightening and baking concealer.',480.00,550.00,0,1,0);

-- === FACE > CONTOUR (subcat 12) ===
INSERT INTO `products` (`subcategory_id`,`brand_id`,`name`,`slug`,`description`,`price`,`original_price`,`is_featured`,`is_new`,`is_bestseller`) VALUES
(12,4,'Huda Beauty Tantour Contour & Bronzer Cream','huda-tantour-cream','Buildable cream-to-powder contour that melts into skin for a natural shadow effect.',5500.00,6200.00,1,1,1),
(12,3,'NARS Laguna Bronzer','nars-laguna-bronzer','Iconic sun-kissed bronzer for sculpting and adding warmth â€” a perennial bestseller.',4500.00,NULL,1,0,1),
(12,2,'MARS Contouring Kit','mars-contouring-kit','All-in-one powder contour + highlighter duo for defined cheekbones and face sculpting.',680.00,800.00,0,0,0);

-- === LIPS > LIP LINER (subcat 13) ===
INSERT INTO `products` (`subcategory_id`,`brand_id`,`name`,`slug`,`description`,`price`,`original_price`,`is_featured`,`is_new`,`is_bestseller`) VALUES
(13,1,'Maybelline Color Sensational Lip Liner','maybelline-lip-liner-cs','Creamy, ultra-smooth lip liner for precise definition that lasts all day.',450.00,550.00,0,0,1),
(13,4,'Huda Beauty Lip Contour 2.0','huda-lip-contour-2','Long-wear matte lip liner in 21 shades â€” doesn\'t bleed, doesn\'t budge.',2200.00,2500.00,1,0,1),
(13,3,'NARS Precision Lip Liner','nars-precision-lip-liner','Sharpenable liner for crisp lines and all-day color that won\'t feather.',2800.00,NULL,0,1,0);

-- === LIPS > LIPSTICK (subcat 14) ===
INSERT INTO `products` (`subcategory_id`,`brand_id`,`name`,`slug`,`description`,`price`,`original_price`,`is_featured`,`is_new`,`is_bestseller`) VALUES
(14,1,'Maybelline SuperStay Matte Ink Liquid Lipstick','maybelline-superstay-matte-ink','Liquid to matte formula that stays for 16 hours â€” no transfer, no fading.',950.00,1100.00,1,0,1),
(14,3,'NARS Audacious Lipstick','nars-audacious-lipstick','Luxuriously hydrating bullet lipstick in 40 iconic shades for editorial-perfect lips.',4200.00,NULL,1,0,1),
(14,4,'Huda Beauty Power Bullet Matte Lipstick','huda-power-bullet-lipstick','12-hour matte lipstick with a velvet finish â€” ultra-pigmented in one swipe.',3200.00,3600.00,0,1,0);

-- === LIPS > LIP GLOSS (subcat 15) ===
INSERT INTO `products` (`subcategory_id`,`brand_id`,`name`,`slug`,`description`,`price`,`original_price`,`is_featured`,`is_new`,`is_bestseller`) VALUES
(15,1,'Maybelline Lifter Gloss','maybelline-lifter-gloss','Plumping lip gloss with hyaluronic acid for hydrated, fuller-looking lips.',800.00,950.00,0,0,1),
(15,4,'Huda Beauty Faux Filler Lip Gloss','huda-faux-filler-gloss','Non-sticky, high-shine gloss that plumps and accentuates lip shape.',3500.00,4000.00,1,1,0),
(15,2,'MARS Crystal Lip Gloss','mars-crystal-lip-gloss','Ultra-shiny, non-sticky gloss in a range of sheer to bold shades.',350.00,420.00,0,1,0);

-- === LIPS > LIP BALM (subcat 16) ===
INSERT INTO `products` (`subcategory_id`,`brand_id`,`name`,`slug`,`description`,`price`,`original_price`,`is_featured`,`is_new`,`is_bestseller`) VALUES
(16,1,'Maybelline Baby Lips Lip Balm','maybelline-baby-lips','Tinted moisturising lip balm with 8-hour moisture for soft, smooth lips.',350.00,420.00,0,0,1),
(16,4,'Huda Beauty Lip Blush Balm','huda-lip-blush-balm','Hydrating, tinted balm that gives a natural flush of colour with long-lasting moisture.',2800.00,3200.00,1,1,0),
(16,3,'NARS Afterglow Lip Balm','nars-afterglow-lip-balm','Sheer, shimmery balm loaded with nourishing oils for a healthy, luminous pout.',2500.00,NULL,0,0,0);

-- -----------------------------------------------------------
-- Table: users
-- -----------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`          VARCHAR(150) NOT NULL,
  `email`         VARCHAR(200) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role`          ENUM('customer','admin') DEFAULT 'customer',
  `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default admin (password: Admin@123)
INSERT INTO `users` (`name`,`email`,`password_hash`,`role`) VALUES
  ('Admin','admin@makeupstudio.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','admin');

-- -----------------------------------------------------------
-- Table: orders
-- -----------------------------------------------------------
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id`        INT UNSIGNED DEFAULT NULL,
  `guest_name`     VARCHAR(150) DEFAULT NULL,
  `guest_email`    VARCHAR(200) DEFAULT NULL,
  `phone`          VARCHAR(20)  DEFAULT NULL,
  `address`        TEXT DEFAULT NULL,
  `city`           VARCHAR(100) DEFAULT NULL,
  `payment_method` VARCHAR(50)  DEFAULT 'cod',
  `subtotal`       DECIMAL(10,2) DEFAULT 0.00,
  `shipping`       DECIMAL(10,2) DEFAULT 0.00,
  `total`          DECIMAL(10,2) DEFAULT 0.00,
  `status`         ENUM('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `notes`          TEXT DEFAULT NULL,
  `created_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------
-- Table: order_items
-- -----------------------------------------------------------
DROP TABLE IF EXISTS `order_items`;
CREATE TABLE `order_items` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_id`   INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `quantity`   INT UNSIGNED DEFAULT 1,
  `price`      DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (`order_id`)   REFERENCES `orders`(`id`)   ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------
-- Table: cart_sessions
-- -----------------------------------------------------------
DROP TABLE IF EXISTS `cart_sessions`;
CREATE TABLE `cart_sessions` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `session_id` VARCHAR(128) NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `quantity`   INT UNSIGNED DEFAULT 1,
  `added_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  INDEX `idx_session` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;

-- Update product images to brand-specific placeholders
UPDATE products p JOIN brands b ON p.brand_id = b.id
SET p.image_path = CASE b.slug
    WHEN 'maybelline'  THEN 'assets/images/maybelline-placeholder.svg'
    WHEN 'mars'        THEN 'assets/images/mars-placeholder.svg'
    WHEN 'nars'        THEN 'assets/images/nars-placeholder.svg'
    WHEN 'huda-beauty' THEN 'assets/images/huda-placeholder.svg'
    ELSE 'assets/images/product-placeholder.svg'
END;
