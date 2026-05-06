-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 06, 2026 at 07:43 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kapelagi`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_settings`
--

CREATE TABLE `admin_settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_settings`
--

INSERT INTO `admin_settings` (`setting_key`, `setting_value`, `updated_at`) VALUES
('analytics_range_default', '30', '2026-05-06 16:30:21'),
('currency_symbol', 'P', '2026-05-06 16:30:21'),
('notify_cancelled_order', '1', '2026-05-06 16:30:21'),
('notify_new_order', '1', '2026-05-06 16:30:21'),
('orders_per_page_default', '10', '2026-05-06 16:30:21'),
('store_name', 'KapeLagi', '2026-05-06 16:30:21'),
('support_email', 'kapelagidasma@gmail.com', '2026-05-06 16:30:21'),
('timezone', 'Asia/Manila', '2026-05-06 16:30:21');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `size` varchar(10) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `special_instructions` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ingredients`
--

CREATE TABLE `ingredients` (
  `id` int(11) NOT NULL,
  `name` varchar(191) NOT NULL,
  `unit` varchar(32) DEFAULT 'units',
  `stock` decimal(10,2) NOT NULL DEFAULT 0.00,
  `package_size` decimal(10,2) NOT NULL DEFAULT 1.00,
  `package_unit` varchar(32) DEFAULT 'pieces',
  `density_g_per_ml` decimal(10,4) NOT NULL DEFAULT 1.0000,
  `low_stock_threshold` decimal(10,2) NOT NULL DEFAULT 5.00,
  `category` varchar(64) DEFAULT 'other',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ingredients`
--

INSERT INTO `ingredients` (`id`, `name`, `unit`, `stock`, `package_size`, `package_unit`, `density_g_per_ml`, `low_stock_threshold`, `category`, `updated_at`) VALUES
(1, 'Water', 'liters', 10000.00, 1.00, 'pieces', 1.0000, 5.00, 'other', '2026-05-07 00:50:31'),
(2, 'Coffee Beans', 'kilograms', 10.00, 1.00, 'pieces', 1.0000, 5.00, 'other', '2026-05-07 00:52:28'),
(3, 'Matcha powder', 'kilograms', 10.00, 1.00, 'pieces', 1.0000, 5.00, 'other', '2026-05-07 00:54:50'),
(4, 'Condensed Milk', 'pieces', 18.20, 500.00, 'milliliters', 1.0000, 5.00, 'other', '2026-05-07 01:15:22'),
(5, 'Blueberry Syrup', 'pieces', 5.00, 1.00, 'liters', 1.0000, 2.00, 'other', '2026-05-07 01:29:31'),
(6, 'Strawberry Syrup', 'pieces', 5.00, 1.00, 'liters', 1.0000, 2.00, 'other', '2026-05-07 01:32:46');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
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
  `special_notes` text DEFAULT NULL,
  `cancellation_reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `customer_name`, `customer_email`, `customer_phone`, `delivery_address`, `city`, `province`, `payment_method`, `total_amount`, `status`, `is_archived`, `archived_at`, `special_notes`, `cancellation_reason`, `created_at`, `updated_at`) VALUES
(1, 2, 'John', 'jpmonroyo@kld.edu.ph', '09123456789', 'Rome Street, Summerwind Village 4, Burol, Dasmariñas, Cavite, Calabarzon, 4114, Philippines', 'Dasmariñas', 'Cavite', 'COD', 100.00, 'completed', 1, '2026-05-07 00:41:04', NULL, NULL, '2026-05-06 16:38:55', '2026-05-06 16:41:04'),
(2, 2, 'John', 'jpmonroyo@kld.edu.ph', '09123456789', 'Rome Street, Summerwind Village 4, Burol, Dasmariñas, Cavite, Calabarzon, 4114, Philippines', 'Dasmariñas', 'Cavite', 'COD', 100.00, 'completed', 1, '2026-05-07 00:41:04', NULL, NULL, '2026-05-06 16:39:54', '2026-05-06 16:41:04'),
(3, 2, 'John', 'jpmonroyo@kld.edu.ph', '09123456789', 'Rome Street, Summerwind Village 4, Burol, Dasmariñas, Cavite, Calabarzon, 4114, Philippines', 'Dasmariñas', 'Cavite', 'COD', 360.00, 'cancelled', 0, NULL, NULL, 'sorry hehe', '2026-05-06 17:15:21', '2026-05-06 17:15:58');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `product_name` varchar(100) DEFAULT NULL,
  `size` varchar(10) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `size`, `price`, `quantity`, `created_at`) VALUES
(1, 1, 1, 'Americano', '16oz', 100.00, 1, '2026-05-06 16:38:55'),
(2, 2, 1, 'Americano', '16oz', 100.00, 1, '2026-05-06 16:39:54'),
(3, 3, 2, 'Berry Matcha', '16oz', 120.00, 3, '2026-05-06 17:15:22');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `token` varchar(128) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(50) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `price_16oz` decimal(10,2) DEFAULT NULL,
  `price_22oz` decimal(10,2) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `is_archived` tinyint(1) NOT NULL DEFAULT 0,
  `archived_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `category`, `image_url`, `price_16oz`, `price_22oz`, `stock`, `is_archived`, `archived_at`, `created_at`) VALUES
(1, 'Americano', 'Americano is a signature KapeLagi drink crafted for a smooth, café-style finish.', 'Coffee', 'Coffee/Americano.png', 100.00, 120.00, 0, 0, NULL, '2026-05-06 16:30:21'),
(2, 'Berry Matcha', 'Berry Matcha is a signature KapeLagi drink crafted for a smooth, café-style finish.', 'Non-Coffee', 'Coffee/BerryMatcha.png', 120.00, 140.00, 0, 0, NULL, '2026-05-06 16:30:21'),
(3, 'Biscoff Latte', 'Biscoff Latte is a signature KapeLagi drink crafted for a smooth, café-style finish.', 'Coffee', 'Coffee/BiscoffLatte.png', 130.00, 150.00, 0, 0, NULL, '2026-05-06 16:30:21'),
(4, 'Blueberry Milk', 'Blueberry Milk is a signature KapeLagi drink crafted for a smooth, café-style finish.', 'Non-Coffee', 'Coffee/Blueberry Milk.png', 110.00, 130.00, 0, 0, NULL, '2026-05-06 16:30:21'),
(5, 'Blueberry', 'Blueberry is a signature KapeLagi drink crafted for a smooth, café-style finish.', 'Fruity', 'Coffee/Blueberry.png', 110.00, 130.00, 0, 0, NULL, '2026-05-06 16:30:21'),
(6, 'Blueberry Choco', 'Blueberry Choco is a signature KapeLagi drink crafted for a smooth, café-style finish.', 'Non-Coffee', 'Coffee/BlueberryChoco.png', 115.00, 135.00, 0, 0, NULL, '2026-05-06 16:30:21'),
(7, 'Blueberry Espresso', 'Blueberry Espresso is a signature KapeLagi drink crafted for a smooth, café-style finish.', 'Coffee', 'Coffee/BlueberryEspresso.png', 125.00, 145.00, 0, 0, NULL, '2026-05-06 16:30:21'),
(8, 'Blueberry Matcha', 'Blueberry Matcha is a signature KapeLagi drink crafted for a smooth, café-style finish.', 'Non-Coffee', 'Coffee/BlueberryMAtcha.png', 120.00, 140.00, 0, 0, NULL, '2026-05-06 16:30:21'),
(9, 'Caramel Macchiato', 'Caramel Macchiato is a signature KapeLagi drink crafted for a smooth, café-style finish.', 'Coffee', 'Coffee/CaramelMacchiato.png', 120.00, 140.00, 0, 0, NULL, '2026-05-06 16:30:21'),
(10, 'Choco Berry', 'Choco Berry is a signature KapeLagi drink crafted for a smooth, café-style finish.', 'Non-Coffee', 'Coffee/ChocoBerry.png', 115.00, 135.00, 0, 0, NULL, '2026-05-06 16:30:21'),
(11, 'Choco Matcha', 'Choco Matcha is a signature KapeLagi drink crafted for a smooth, café-style finish.', 'Non-Coffee', 'Coffee/ChocoMatcha.png', 120.00, 140.00, 0, 0, NULL, '2026-05-06 16:30:21'),
(12, 'Dirty Matcha', 'Dirty Matcha is a signature KapeLagi drink crafted for a smooth, café-style finish.', 'Coffee', 'Coffee/Dirty Matcha.png', 130.00, 150.00, 0, 0, NULL, '2026-05-06 16:30:21'),
(13, 'French Vanilla', 'French Vanilla is a signature KapeLagi drink crafted for a smooth, café-style finish.', 'Coffee', 'Coffee/FrenchVanilla.png', 110.00, 130.00, 0, 0, NULL, '2026-05-06 16:30:21'),
(14, 'Green Apple', 'Green Apple is a signature KapeLagi drink crafted for a smooth, café-style finish.', 'Fruity', 'Coffee/GreenApple.png', 110.00, 130.00, 0, 0, NULL, '2026-05-06 16:30:21'),
(15, 'Hazelnut Latte', 'Hazelnut Latte is a signature KapeLagi drink crafted for a smooth, café-style finish.', 'Coffee', 'Coffee/HazelnutLatte.png', 120.00, 140.00, 0, 0, NULL, '2026-05-06 16:30:21'),
(16, 'Icy Choco', 'Icy Choco is a signature KapeLagi drink crafted for a smooth, café-style finish.', 'Non-Coffee', 'Coffee/IcyChoco.png', 115.00, 135.00, 0, 0, NULL, '2026-05-06 16:30:21'),
(17, 'Lemonade', 'Lemonade is a signature KapeLagi drink crafted for a smooth, café-style finish.', 'Fruity', 'Coffee/Lemonade.png', 100.00, 120.00, 0, 0, NULL, '2026-05-06 16:30:21'),
(18, 'Lychee', 'Lychee is a signature KapeLagi drink crafted for a smooth, café-style finish.', 'Fruity', 'Coffee/Lychee.png', 110.00, 130.00, 0, 0, NULL, '2026-05-06 16:30:21'),
(19, 'Matcha Latte', 'Matcha Latte is a signature KapeLagi drink crafted for a smooth, café-style finish.', 'Coffee', 'Coffee/MatchaLatte.png', 130.00, 150.00, 0, 0, NULL, '2026-05-06 16:30:21'),
(20, 'Mocha Latte', 'Mocha Latte is a signature KapeLagi drink crafted for a smooth, café-style finish.', 'Coffee', 'Coffee/MochaLatte.png', 125.00, 145.00, 0, 0, NULL, '2026-05-06 16:30:21'),
(21, 'Nutella Latte', 'Nutella Latte is a signature KapeLagi drink crafted for a smooth, café-style finish.', 'Coffee', 'Coffee/NutellaLatte.png', 130.00, 150.00, 0, 0, NULL, '2026-05-06 16:30:21'),
(22, 'Salted Caramel Latte', 'Salted Caramel Latte is a signature KapeLagi drink crafted for a smooth, café-style finish.', 'Coffee', 'Coffee/SaltedCaramelLatte.png', 130.00, 150.00, 0, 0, NULL, '2026-05-06 16:30:21'),
(23, 'Spanish Latte', 'Spanish Latte is a signature KapeLagi drink crafted for a smooth, café-style finish.', 'Coffee', 'Coffee/SpanishLatte.png', 120.00, 140.00, 0, 0, NULL, '2026-05-06 16:30:21'),
(24, 'Strawberry Milk', 'Strawberry Milk is a signature KapeLagi drink crafted for a smooth, café-style finish.', 'Non-Coffee', 'Coffee/StrawberryMilk.png', 110.00, 130.00, 0, 0, NULL, '2026-05-06 16:30:21'),
(25, 'Vanilla Latte', 'Vanilla Latte is a signature KapeLagi drink crafted for a smooth, café-style finish.', 'Coffee', 'Coffee/VanillaLatte.png', 110.00, 130.00, 0, 0, NULL, '2026-05-06 16:30:21'),
(26, 'Vietnamese', 'Vietnamese is a signature KapeLagi drink crafted for a smooth, café-style finish.', 'Coffee', 'Coffee/Vietnamese.png', 120.00, 140.00, 0, 0, NULL, '2026-05-06 16:30:21');

-- --------------------------------------------------------

--
-- Table structure for table `product_ingredients`
--

CREATE TABLE `product_ingredients` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `ingredient_id` int(11) NOT NULL,
  `size` varchar(10) NOT NULL DEFAULT '16oz',
  `stock_unit` varchar(32) DEFAULT 'unit',
  `quantity_per_unit` decimal(10,4) NOT NULL DEFAULT 0.0000,
  `unit` varchar(32) DEFAULT 'units',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_ingredients`
--

INSERT INTO `product_ingredients` (`id`, `product_id`, `ingredient_id`, `size`, `stock_unit`, `quantity_per_unit`, `unit`, `created_at`, `updated_at`) VALUES
(5, 1, 1, '16oz', 'unit', 500.0000, 'milliliters', '2026-05-07 00:53:13', '2026-05-07 00:53:13'),
(6, 1, 2, '16oz', 'unit', 20.0000, 'grams', '2026-05-07 00:53:14', '2026-05-07 00:53:14'),
(7, 1, 1, '22oz', 'unit', 600.0000, 'milliliters', '2026-05-07 00:53:51', '2026-05-07 00:53:51'),
(8, 1, 2, '22oz', 'unit', 25.0000, 'grams', '2026-05-07 00:53:51', '2026-05-07 00:53:51'),
(20, 2, 4, '16oz', 'unit', 50.0000, 'grams', '2026-05-07 01:24:22', '2026-05-07 01:24:22'),
(21, 2, 3, '16oz', 'unit', 20.0000, 'grams', '2026-05-07 01:24:22', '2026-05-07 01:24:22'),
(22, 2, 1, '16oz', 'unit', 500.0000, 'milliliters', '2026-05-07 01:24:22', '2026-05-07 01:24:22'),
(23, 2, 4, '22oz', 'unit', 60.0000, 'grams', '2026-05-07 01:25:21', '2026-05-07 01:25:21'),
(24, 2, 3, '22oz', 'unit', 30.0000, 'grams', '2026-05-07 01:25:21', '2026-05-07 01:25:21'),
(25, 2, 1, '22oz', 'unit', 600.0000, 'milliliters', '2026-05-07 01:25:21', '2026-05-07 01:25:21');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
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
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `email_verified_at`, `google_id`, `oauth_provider`, `oauth_avatar_url`, `email_verification_token`, `email_verification_code`, `pending_email`, `pending_email_verification_token`, `pending_email_verification_code`, `terms_accepted_at`, `terms_version`, `phone`, `address`, `city`, `province`, `created_at`, `updated_at`) VALUES
(1, 'Admin User', 'admin@kapelagi.com', '$2y$12$M9GUAFG7UEUGgxU1VHVqw.QFVekPzvTSRoS9wMoWCz4cazNgTfehO', 'admin', '2026-05-06 16:30:21', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-06 16:30:21', 'v1', NULL, NULL, NULL, NULL, '2026-05-06 16:30:21', '2026-05-06 16:30:21'),
(2, 'John', 'jpmonroyo@kld.edu.ph', '$2y$10$bcH69b.86pONYMSQKMyviOj73/4rlct2qxpm6uRj3zcuucj/jseJS', 'customer', '2026-05-07 00:36:58', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-07 00:36:58', 'v1.0', '09123456789', 'asdasd adsadasd, Rome Street, Summerwind Village 4, Burol, Dasmariñas, Cavite, Calabarzon, 4114, Philippines', 'Dasmariñas', 'Cavite', '2026-05-06 16:36:58', '2026-05-06 17:15:22');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_settings`
--
ALTER TABLE `admin_settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cart_user_id` (`user_id`),
  ADD KEY `idx_cart_product_id` (`product_id`);

--
-- Indexes for table `ingredients`
--
ALTER TABLE `ingredients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ingredients_name` (`name`),
  ADD KEY `idx_ingredients_category` (`category`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_orders_user_id` (`user_id`),
  ADD KEY `idx_orders_status` (`status`),
  ADD KEY `idx_orders_created_at` (`created_at`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_items_order_id` (`order_id`),
  ADD KEY `idx_order_items_product_id` (`product_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_password_resets_email` (`email`),
  ADD KEY `idx_password_resets_token` (`token`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_products_name` (`name`),
  ADD KEY `idx_products_category` (`category`),
  ADD KEY `idx_products_archived` (`is_archived`);

--
-- Indexes for table `product_ingredients`
--
ALTER TABLE `product_ingredients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_product_ingredients_mapping` (`product_id`,`ingredient_id`,`size`),
  ADD KEY `idx_product_ingredients_product_id` (`product_id`),
  ADD KEY `idx_product_ingredients_ingredient_id` (`ingredient_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_users_email` (`email`),
  ADD UNIQUE KEY `uq_users_google_id` (`google_id`),
  ADD KEY `idx_users_role` (`role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `ingredients`
--
ALTER TABLE `ingredients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `product_ingredients`
--
ALTER TABLE `product_ingredients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `fk_cart_products` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cart_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_items_orders` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_order_items_products` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_ingredients`
--
ALTER TABLE `product_ingredients`
  ADD CONSTRAINT `fk_product_ingredients_ingredients` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_product_ingredients_products` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
