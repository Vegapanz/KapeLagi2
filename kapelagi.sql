-- KapeLagi portable database export
-- Intended for a fresh import into MySQL / MariaDB.

SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET time_zone = '+00:00';
SET FOREIGN_KEY_CHECKS = 0;

DROP DATABASE IF EXISTS `kapelagi`;
CREATE DATABASE `kapelagi` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `kapelagi`;

DROP TABLE IF EXISTS `product_ingredients`;
DROP TABLE IF EXISTS `ingredients`;
DROP TABLE IF EXISTS `password_resets`;
DROP TABLE IF EXISTS `admin_settings`;
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `cart`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'customer',
  `email_verified_at` datetime DEFAULT NULL,
  `google_id` varchar(191) DEFAULT NULL,
  `oauth_provider` varchar(20) DEFAULT NULL,
  `oauth_avatar_url` varchar(255) DEFAULT NULL,
  `email_verification_token` varchar(64) DEFAULT NULL,
  `email_verification_code` varchar(6) DEFAULT NULL,
  `pending_email` varchar(100) DEFAULT NULL,
  `pending_email_verification_token` varchar(64) DEFAULT NULL,
  `pending_email_verification_code` varchar(6) DEFAULT NULL,
  `terms_accepted_at` datetime DEFAULT NULL,
  `terms_version` varchar(20) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `province` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`),
  UNIQUE KEY `uq_users_google_id` (`google_id`),
  KEY `idx_users_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `category` varchar(50) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `price_16oz` decimal(10,2) DEFAULT NULL,
  `price_22oz` decimal(10,2) DEFAULT NULL,
  `stock` int NOT NULL DEFAULT 0,
  `is_archived` tinyint(1) NOT NULL DEFAULT 0,
  `archived_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_products_name` (`name`),
  KEY `idx_products_category` (`category`),
  KEY `idx_products_archived` (`is_archived`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cart` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `size` varchar(10) DEFAULT NULL,
  `quantity` int DEFAULT 1,
  `special_instructions` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cart_user_id` (`user_id`),
  KEY `idx_cart_product_id` (`product_id`),
  CONSTRAINT `fk_cart_products` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cart_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_email` varchar(100) NOT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `delivery_address` varchar(255) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `province` varchar(50) DEFAULT NULL,
  `payment_method` varchar(20) NOT NULL DEFAULT 'COD',
  `total_amount` decimal(10,2) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `is_archived` tinyint(1) NOT NULL DEFAULT 0,
  `archived_at` datetime DEFAULT NULL,
  `special_notes` text,
  `cancellation_reason` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_orders_user_id` (`user_id`),
  KEY `idx_orders_status` (`status`),
  KEY `idx_orders_created_at` (`created_at`),
  CONSTRAINT `fk_orders_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `order_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `product_id` int DEFAULT NULL,
  `product_name` varchar(100) DEFAULT NULL,
  `size` varchar(10) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `quantity` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_order_items_order_id` (`order_id`),
  KEY `idx_order_items_product_id` (`product_id`),
  CONSTRAINT `fk_order_items_orders` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_order_items_products` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `admin_settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `password_resets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `token` varchar(128) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_password_resets_email` (`email`),
  KEY `idx_password_resets_token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `ingredients` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `unit` varchar(32) DEFAULT 'units',
  `stock` decimal(10,2) NOT NULL DEFAULT 0,
  `package_size` decimal(10,2) NOT NULL DEFAULT 1,
  `package_unit` varchar(32) DEFAULT 'pieces',
  `low_stock_threshold` decimal(10,2) NOT NULL DEFAULT 5,
  `category` varchar(64) DEFAULT 'other',
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ingredients_name` (`name`),
  KEY `idx_ingredients_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `product_ingredients` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `ingredient_id` int NOT NULL,
  `size` varchar(10) NOT NULL DEFAULT '16oz',
  `stock_unit` varchar(32) DEFAULT 'unit',
  `quantity_per_unit` decimal(10,4) NOT NULL DEFAULT 0,
  `unit` varchar(32) DEFAULT 'units',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_product_ingredients_mapping` (`product_id`, `ingredient_id`, `size`),
  KEY `idx_product_ingredients_product_id` (`product_id`),
  KEY `idx_product_ingredients_ingredient_id` (`ingredient_id`),
  CONSTRAINT `fk_product_ingredients_products` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_product_ingredients_ingredients` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `admin_settings` (`setting_key`, `setting_value`) VALUES
  ('store_name', 'KapeLagi'),
  ('support_email', 'kapelagidasma@gmail.com'),
  ('timezone', 'Asia/Manila'),
  ('currency_symbol', 'P'),
  ('orders_per_page_default', '10'),
  ('analytics_range_default', '30'),
  ('notify_new_order', '1'),
  ('notify_cancelled_order', '1');

INSERT INTO `users` (
  `name`, `email`, `password`, `role`, `email_verified_at`, `terms_accepted_at`, `terms_version`, `created_at`, `updated_at`
) VALUES (
  'Admin User',
  'admin@kapelagi.com',
  '$2y$12$M9GUAFG7UEUGgxU1VHVqw.QFVekPzvTSRoS9wMoWCz4cazNgTfehO',
  'admin',
  CURRENT_TIMESTAMP,
  CURRENT_TIMESTAMP,
  'v1',
  CURRENT_TIMESTAMP,
  CURRENT_TIMESTAMP
);

INSERT INTO `products` (`name`, `description`, `category`, `image_url`, `price_16oz`, `price_22oz`, `stock`, `is_archived`, `archived_at`) VALUES
  ('Americano', 'Americano is a signature KapeLagi drink crafted for a smooth, café-style finish.', 'Coffee', 'Coffee/Americano.png', 100, 120, 0, 0, NULL),
  ('Berry Matcha', 'Berry Matcha is a signature KapeLagi drink crafted for a smooth, café-style finish.', 'Non-Coffee', 'Coffee/BerryMatcha.png', 120, 140, 0, 0, NULL),
  ('Biscoff Latte', 'Biscoff Latte is a signature KapeLagi drink crafted for a smooth, café-style finish.', 'Coffee', 'Coffee/BiscoffLatte.png', 130, 150, 0, 0, NULL),
  ('Blueberry Milk', 'Blueberry Milk is a signature KapeLagi drink crafted for a smooth, café-style finish.', 'Non-Coffee', 'Coffee/Blueberry Milk.png', 110, 130, 0, 0, NULL),
  ('Blueberry', 'Blueberry is a signature KapeLagi drink crafted for a smooth, café-style finish.', 'Fruity', 'Coffee/Blueberry.png', 110, 130, 0, 0, NULL),
  ('Blueberry Choco', 'Blueberry Choco is a signature KapeLagi drink crafted for a smooth, café-style finish.', 'Non-Coffee', 'Coffee/BlueberryChoco.png', 115, 135, 0, 0, NULL),
  ('Blueberry Espresso', 'Blueberry Espresso is a signature KapeLagi drink crafted for a smooth, café-style finish.', 'Coffee', 'Coffee/BlueberryEspresso.png', 125, 145, 0, 0, NULL),
  ('Blueberry Matcha', 'Blueberry Matcha is a signature KapeLagi drink crafted for a smooth, café-style finish.', 'Non-Coffee', 'Coffee/BlueberryMAtcha.png', 120, 140, 0, 0, NULL),
  ('Caramel Macchiato', 'Caramel Macchiato is a signature KapeLagi drink crafted for a smooth, café-style finish.', 'Coffee', 'Coffee/CaramelMacchiato.png', 120, 140, 0, 0, NULL),
  ('Choco Berry', 'Choco Berry is a signature KapeLagi drink crafted for a smooth, café-style finish.', 'Non-Coffee', 'Coffee/ChocoBerry.png', 115, 135, 0, 0, NULL),
  ('Choco Matcha', 'Choco Matcha is a signature KapeLagi drink crafted for a smooth, café-style finish.', 'Non-Coffee', 'Coffee/ChocoMatcha.png', 120, 140, 0, 0, NULL),
  ('Dirty Matcha', 'Dirty Matcha is a signature KapeLagi drink crafted for a smooth, café-style finish.', 'Coffee', 'Coffee/Dirty Matcha.png', 130, 150, 0, 0, NULL),
  ('French Vanilla', 'French Vanilla is a signature KapeLagi drink crafted for a smooth, café-style finish.', 'Coffee', 'Coffee/FrenchVanilla.png', 110, 130, 0, 0, NULL),
  ('Green Apple', 'Green Apple is a signature KapeLagi drink crafted for a smooth, café-style finish.', 'Fruity', 'Coffee/GreenApple.png', 110, 130, 0, 0, NULL),
  ('Hazelnut Latte', 'Hazelnut Latte is a signature KapeLagi drink crafted for a smooth, café-style finish.', 'Coffee', 'Coffee/HazelnutLatte.png', 120, 140, 0, 0, NULL),
  ('Icy Choco', 'Icy Choco is a signature KapeLagi drink crafted for a smooth, café-style finish.', 'Non-Coffee', 'Coffee/IcyChoco.png', 115, 135, 0, 0, NULL),
  ('Lemonade', 'Lemonade is a signature KapeLagi drink crafted for a smooth, café-style finish.', 'Fruity', 'Coffee/Lemonade.png', 100, 120, 0, 0, NULL),
  ('Lychee', 'Lychee is a signature KapeLagi drink crafted for a smooth, café-style finish.', 'Fruity', 'Coffee/Lychee.png', 110, 130, 0, 0, NULL),
  ('Matcha Latte', 'Matcha Latte is a signature KapeLagi drink crafted for a smooth, café-style finish.', 'Coffee', 'Coffee/MatchaLatte.png', 130, 150, 0, 0, NULL),
  ('Mocha Latte', 'Mocha Latte is a signature KapeLagi drink crafted for a smooth, café-style finish.', 'Coffee', 'Coffee/MochaLatte.png', 125, 145, 0, 0, NULL),
  ('Nutella Latte', 'Nutella Latte is a signature KapeLagi drink crafted for a smooth, café-style finish.', 'Coffee', 'Coffee/NutellaLatte.png', 130, 150, 0, 0, NULL),
  ('Salted Caramel Latte', 'Salted Caramel Latte is a signature KapeLagi drink crafted for a smooth, café-style finish.', 'Coffee', 'Coffee/SaltedCaramelLatte.png', 130, 150, 0, 0, NULL),
  ('Spanish Latte', 'Spanish Latte is a signature KapeLagi drink crafted for a smooth, café-style finish.', 'Coffee', 'Coffee/SpanishLatte.png', 120, 140, 0, 0, NULL),
  ('Strawberry Milk', 'Strawberry Milk is a signature KapeLagi drink crafted for a smooth, café-style finish.', 'Non-Coffee', 'Coffee/StrawberryMilk.png', 110, 130, 0, 0, NULL),
  ('Vanilla Latte', 'Vanilla Latte is a signature KapeLagi drink crafted for a smooth, café-style finish.', 'Coffee', 'Coffee/VanillaLatte.png', 110, 130, 0, 0, NULL),
  ('Vietnamese', 'Vietnamese is a signature KapeLagi drink crafted for a smooth, café-style finish.', 'Coffee', 'Coffee/Vietnamese.png', 120, 140, 0, 0, NULL);

SET FOREIGN_KEY_CHECKS = 1;