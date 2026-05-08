-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 07, 2026 at 09:36 PM
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
(1, 'Water', 'liters', 9985.20, 1.00, 'pieces', 1.0000, 5.00, 'other', '2026-05-08 03:30:09'),
(2, 'Coffee Beans', 'kilograms', 10.00, 1.00, 'pieces', 1.0000, 5.00, 'other', '2026-05-08 03:30:43'),
(3, 'Matcha powder', 'kilograms', 10.00, 1.00, 'pieces', 1.0000, 5.00, 'other', '2026-05-07 00:54:50'),
(4, 'Condensed Milk', 'pieces', 18.20, 500.00, 'milliliters', 1.0000, 5.00, 'other', '2026-05-07 01:15:22'),
(5, 'Blueberry Jam', 'pieces', 5.00, 1.00, 'liters', 1.0000, 2.00, 'other', '2026-05-08 02:29:50'),
(6, 'Strawberry Jam', 'pieces', 5.00, 1.00, 'liters', 1.0000, 2.00, 'other', '2026-05-08 02:32:42'),
(7, 'Biscoff Spread', 'pieces', 5.00, 1.00, 'liters', 1.0000, 2.00, 'other', '2026-05-08 02:28:06'),
(8, 'Vanilla Syrup', 'pieces', 5.00, 1.00, 'liters', 1.0000, 2.00, 'other', '2026-05-08 02:28:39'),
(9, 'French Vanilla Syrup', 'pieces', 5.00, 1.00, 'liters', 1.0000, 2.00, 'other', '2026-05-08 02:29:07'),
(10, 'Hazelnut Syrup', 'pieces', 5.00, 1.00, 'liters', 1.0000, 2.00, 'other', '2026-05-08 02:29:29'),
(11, 'Chocolate Milano Syrup', 'pieces', 5.00, 1.00, 'liters', 1.0000, 2.00, 'other', '2026-05-08 02:30:25'),
(12, 'Nutella Spread', 'pieces', 5.00, 500.00, 'milliliters', 1.0000, 2.00, 'other', '2026-05-08 02:31:06'),
(13, 'Salted Caramel Syrup', 'pieces', 5.00, 1.00, 'liters', 1.0000, 2.00, 'other', '2026-05-08 02:32:15'),
(14, 'Caramel Drizzle', 'pieces', 5.00, 1.00, 'liters', 1.0000, 2.00, 'other', '2026-05-08 02:33:13'),
(15, 'Chocolate Syrup', 'pieces', 5.00, 1.00, 'liters', 1.0000, 2.00, 'other', '2026-05-08 02:33:37'),
(16, 'Almond Milk', 'pieces', 10.00, 1.00, 'liters', 1.0000, 5.00, 'other', '2026-05-08 03:14:45');

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
  `payment_intent_id` varchar(100) DEFAULT NULL,
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

INSERT INTO `orders` (`id`, `user_id`, `customer_name`, `customer_email`, `customer_phone`, `delivery_address`, `city`, `province`, `payment_method`, `payment_intent_id`, `total_amount`, `status`, `is_archived`, `archived_at`, `special_notes`, `cancellation_reason`, `created_at`, `updated_at`) VALUES
(1, 2, 'John', 'jpmonroyo@kld.edu.ph', '09123456789', 'Rome Street, Summerwind Village 4, Burol, Dasmariñas, Cavite, Calabarzon, 4114, Philippines', 'Dasmariñas', 'Cavite', 'COD', NULL, 100.00, 'completed', 1, '2026-05-07 00:41:04', NULL, NULL, '2026-05-06 16:38:55', '2026-05-06 16:41:04'),
(2, 2, 'John', 'jpmonroyo@kld.edu.ph', '09123456789', 'Rome Street, Summerwind Village 4, Burol, Dasmariñas, Cavite, Calabarzon, 4114, Philippines', 'Dasmariñas', 'Cavite', 'COD', NULL, 100.00, 'completed', 1, '2026-05-07 00:41:04', NULL, NULL, '2026-05-06 16:39:54', '2026-05-06 16:41:04'),
(3, 2, 'John', 'jpmonroyo@kld.edu.ph', '09123456789', 'Rome Street, Summerwind Village 4, Burol, Dasmariñas, Cavite, Calabarzon, 4114, Philippines', 'Dasmariñas', 'Cavite', 'COD', NULL, 360.00, 'cancelled', 0, NULL, NULL, 'sorry hehe', '2026-05-06 17:15:21', '2026-05-06 17:15:58'),
(4, 3, 'Justine Kamantigue', 'justinekamantigue10@gmail.com', '09123456789', 'Santa Cruz 2, Bagong Bayan, Dasmariñas, Cavite, Calabarzon, 4115, Philippines', 'Dasmariñas', 'Cavite', 'COD', NULL, 100.00, 'pending', 0, NULL, NULL, NULL, '2026-05-07 17:15:39', '2026-05-07 17:15:39'),
(5, 3, 'Justine Kamantigue', 'justinekamantigue10@gmail.com', '09123456789', 'Santa Cruz 2, Bagong Bayan, Dasmariñas, Cavite, Calabarzon, 4115, Philippines', 'Dasmariñas', 'Cavite', 'COD', NULL, 100.00, 'pending', 0, NULL, NULL, NULL, '2026-05-07 17:16:53', '2026-05-07 17:16:53'),
(6, 3, 'Justine Kamantigue', 'justinekamantigue10@gmail.com', '09123456789', 'Santa Cruz 2, Bagong Bayan, Dasmariñas, Cavite, Calabarzon, 4115, Philippines', 'Dasmariñas', 'Cavite', 'GCASH', 'pi_P3vjapAR7DwKVH4kTXsSmz5G', 100.00, 'pending', 0, NULL, NULL, NULL, '2026-05-07 17:18:45', '2026-05-07 17:18:45'),
(7, 3, 'Justine Kamantigue', 'justinekamantigue10@gmail.com', '09123456789', 'Santa Cruz 2, Bagong Bayan, Dasmariñas, Cavite, Calabarzon, 4115, Philippines', 'Dasmariñas', 'Cavite', 'GCASH', 'src_BRS6Mh2M3UMWSa5Aq7svsk4Z', 100.00, 'pending', 0, NULL, NULL, NULL, '2026-05-07 17:25:12', '2026-05-07 17:25:13'),
(8, 3, 'Justine Kamantigue', 'justinekamantigue10@gmail.com', '09123456789', 'Athens Street, Summerwind Village 4, Burol, Dasmariñas, Cavite, Calabarzon, 4114, Philippines', 'Dasmariñas', 'Cavite', 'GCASH', 'src_mk7TBXsFqC2r5HbBYgYqxqQU', 100.00, 'processing', 0, NULL, NULL, NULL, '2026-05-07 17:29:23', '2026-05-07 17:40:07'),
(9, 3, 'Justine Kamantigue', 'justinekamantigue10@gmail.com', '09123456789', 'Athens Street, Summerwind Village 4, Burol, Dasmariñas, Cavite, Calabarzon, 4114, Philippines', 'Dasmariñas', 'Cavite', 'GCASH', 'src_oRAEbxfank2wUzRPca85gm5z', 100.00, 'pending', 0, NULL, NULL, NULL, '2026-05-07 17:41:03', '2026-05-07 17:41:04'),
(10, 3, 'Justine Kamantigue', 'justinekamantigue10@gmail.com', '09123456789', 'Athens Street, Summerwind Village 4, Burol, Dasmariñas, Cavite, Calabarzon, 4114, Philippines', 'Dasmariñas', 'Cavite', 'COD', NULL, 100.00, 'pending', 0, NULL, NULL, NULL, '2026-05-07 17:43:22', '2026-05-07 17:43:22'),
(11, 3, 'Justine Kamantigue', 'justinekamantigue10@gmail.com', '09123456789', 'Athens Street, Summerwind Village 4, Burol, Dasmariñas, Cavite, Calabarzon, 4114, Philippines', 'Dasmariñas', 'Cavite', 'GCASH', 'src_gLR9WfxM3AMCXb4XwpvUyfVC', 100.00, 'pending', 0, NULL, NULL, NULL, '2026-05-07 17:43:28', '2026-05-07 17:43:28'),
(12, 3, 'Justine Kamantigue', 'justinekamantigue10@gmail.com', '09123456789', 'Athens Street, Summerwind Village 4, Burol, Dasmariñas, Cavite, Calabarzon, 4114, Philippines', 'Dasmariñas', 'Cavite', 'GCASH', 'src_qZvjfWmJ2Wa3JdyNwGjJbLbC', 100.00, 'pending', 0, NULL, NULL, NULL, '2026-05-07 17:43:49', '2026-05-07 17:43:49'),
(13, 3, 'Justine Kamantigue', 'justinekamantigue10@gmail.com', '09123456789', 'Athens Street, Summerwind Village 4, Burol, Dasmariñas, Cavite, Calabarzon, 4114, Philippines', 'Dasmariñas', 'Cavite', 'GCASH', 'src_hcv2WbzFu4hRbvfQFZYWfthy', 100.00, 'processing', 0, NULL, NULL, NULL, '2026-05-07 17:49:28', '2026-05-07 17:49:32'),
(14, 3, 'Justine Kamantigue', 'justinekamantigue10@gmail.com', '09123456789', 'Poinsettia Street, Via Verde, San Agustin 2, San Agustin, Dasmariñas, Cavite, Calabarzon, 4114, Philippines', 'Dasmariñas', 'Cavite', 'COD', NULL, 100.00, 'pending', 0, NULL, NULL, NULL, '2026-05-07 17:55:52', '2026-05-07 17:55:52'),
(15, 3, 'Justine Kamantigue', 'justinekamantigue10@gmail.com', '09123456789', 'Santa Maria, Bagong Bayan, Dasmariñas, Cavite, Calabarzon, 4115, Philippines', 'Dasmariñas', 'Cavite', 'COD', NULL, 100.00, 'pending', 0, NULL, NULL, NULL, '2026-05-07 17:58:23', '2026-05-07 17:58:23'),
(16, 3, 'Justine Kamantigue', 'justinekamantigue10@gmail.com', '09123456789', 'Bagong Bayan, Dasmariñas, Cavite, Calabarzon, 4115, Philippines', 'Dasmariñas', 'Cavite', 'COD', NULL, 100.00, 'pending', 0, NULL, NULL, NULL, '2026-05-07 18:02:05', '2026-05-07 18:02:05'),
(17, 3, 'Justine Kamantigue', 'justinekamantigue10@gmail.com', '09123456789', 'Berlin Street, Summerwind Village 4, Burol, Dasmariñas, Cavite, Calabarzon, 4114, Philippines', 'Dasmariñas', 'Cavite', 'COD', NULL, 100.00, 'pending', 0, NULL, NULL, NULL, '2026-05-07 18:09:20', '2026-05-07 18:09:20'),
(18, 3, 'Justine Kamantigue', 'justinekamantigue10@gmail.com', '09123456789', 'Apricot Drive, Vineyard, San Agustin 1, San Agustin, Dasmariñas, Cavite, Calabarzon, 4115, Philippines', 'Dasmariñas', 'Cavite', 'COD', NULL, 200.00, 'pending', 0, NULL, NULL, NULL, '2026-05-07 18:12:39', '2026-05-07 18:12:39'),
(19, 3, 'Justine Kamantigue', 'justinekamantigue10@gmail.com', '09123456789', 'Santa Maria, Bagong Bayan, Dasmariñas, Cavite, Calabarzon, 4115, Philippines', 'Dasmariñas', 'Cavite', 'GCASH', 'src_MMPqwVZFpqx5JNsLSMx87RCX', 100.00, 'processing', 0, NULL, NULL, NULL, '2026-05-07 18:13:11', '2026-05-07 18:13:17'),
(20, 3, 'Justine Kamantigue', 'justinekamantigue10@gmail.com', '09123456789', 'Bagong Bayan, Dasmariñas, Cavite, Calabarzon, 4115, Philippines', 'Dasmariñas', 'Cavite', 'COD', NULL, 100.00, 'pending', 0, NULL, NULL, NULL, '2026-05-07 18:24:09', '2026-05-07 18:24:09'),
(21, 3, 'Justine Kamantigue', 'justinekamantigue10@gmail.com', '09123456789', 'Santa Cruz 1, Bagong Bayan, Dasmariñas, Cavite, Calabarzon, 4115, Philippines', 'Dasmariñas', 'Cavite', 'COD', NULL, 100.00, 'pending', 0, NULL, NULL, NULL, '2026-05-07 18:24:18', '2026-05-07 18:24:18'),
(22, 3, 'Justine Kamantigue', 'justinekamantigue10@gmail.com', '09123456789', 'Bagong Bayan, Dasmariñas, Cavite, Calabarzon, 4115, Philippines', 'Dasmariñas', 'Cavite', 'GCASH', 'src_34kAEUqJukJY62dnJPNgTcGk', 100.00, 'processing', 0, NULL, NULL, NULL, '2026-05-07 18:24:41', '2026-05-07 18:24:47'),
(23, 3, 'Justine Kamantigue', 'justinekamantigue10@gmail.com', '09123456789', 'Santa Maria Promenade Way, Burol, Dasmariñas, Cavite, Calabarzon, 4114, Philippines', 'Dasmariñas', 'Cavite', 'GCASH', 'src_3HwG5kkrs6rpd7tP7nXkrMEh', 2400.00, 'processing', 0, NULL, NULL, NULL, '2026-05-07 19:30:09', '2026-05-07 19:30:13');

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
(3, 3, 2, 'Berry Matcha', '16oz', 120.00, 3, '2026-05-06 17:15:22'),
(4, 4, 1, 'Americano', '16oz', 100.00, 1, '2026-05-07 17:15:39'),
(5, 5, 1, 'Americano', '16oz', 100.00, 1, '2026-05-07 17:16:53'),
(6, 6, 1, 'Americano', '16oz', 100.00, 1, '2026-05-07 17:18:45'),
(7, 7, 1, 'Americano', '16oz', 100.00, 1, '2026-05-07 17:25:13'),
(8, 8, 1, 'Americano', '16oz', 100.00, 1, '2026-05-07 17:29:23'),
(9, 9, 1, 'Americano', '16oz', 100.00, 1, '2026-05-07 17:41:03'),
(10, 10, 1, 'Americano', '16oz', 100.00, 1, '2026-05-07 17:43:22'),
(11, 11, 1, 'Americano', '16oz', 100.00, 1, '2026-05-07 17:43:28'),
(12, 12, 1, 'Americano', '16oz', 100.00, 1, '2026-05-07 17:43:49'),
(13, 13, 1, 'Americano', '16oz', 100.00, 1, '2026-05-07 17:49:28'),
(14, 14, 1, 'Americano', '16oz', 100.00, 1, '2026-05-07 17:55:52'),
(15, 15, 1, 'Americano', '16oz', 100.00, 1, '2026-05-07 17:58:23'),
(16, 16, 1, 'Americano', '16oz', 100.00, 1, '2026-05-07 18:02:05'),
(17, 17, 1, 'Americano', '16oz', 100.00, 1, '2026-05-07 18:09:20'),
(18, 18, 1, 'Americano', '16oz', 100.00, 2, '2026-05-07 18:12:39'),
(19, 19, 1, 'Americano', '16oz', 100.00, 1, '2026-05-07 18:13:11'),
(20, 20, 1, 'Americano', '16oz', 100.00, 1, '2026-05-07 18:24:09'),
(21, 21, 1, 'Americano', '16oz', 100.00, 1, '2026-05-07 18:24:18'),
(22, 22, 1, 'Americano', '16oz', 100.00, 1, '2026-05-07 18:24:41'),
(23, 23, 1, 'Americano', '16oz', 100.00, 24, '2026-05-07 19:30:09');

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
(26, 3, 7, '16oz', 'unit', 50.0000, 'milliliters', '2026-05-08 02:34:50', '2026-05-08 02:34:50'),
(27, 3, 2, '16oz', 'unit', 150.0000, 'grams', '2026-05-08 02:34:50', '2026-05-08 02:34:50'),
(28, 3, 1, '16oz', 'unit', 200.0000, 'milliliters', '2026-05-08 02:34:50', '2026-05-08 02:34:50'),
(33, 3, 7, '22oz', 'unit', 50.0000, 'milliliters', '2026-05-08 02:38:14', '2026-05-08 02:38:14'),
(34, 3, 2, '22oz', 'unit', 250.0000, 'grams', '2026-05-08 02:38:15', '2026-05-08 02:38:15'),
(35, 3, 1, '22oz', 'unit', 250.0000, 'milliliters', '2026-05-08 02:38:15', '2026-05-08 02:38:15'),
(36, 1, 2, '22oz', 'unit', 250.0000, 'grams', '2026-05-08 02:38:33', '2026-05-08 02:38:33'),
(37, 1, 1, '22oz', 'unit', 250.0000, 'milliliters', '2026-05-08 02:38:33', '2026-05-08 02:38:33'),
(38, 1, 2, '16oz', 'unit', 200.0000, 'grams', '2026-05-08 02:38:43', '2026-05-08 02:38:43'),
(39, 1, 1, '16oz', 'unit', 200.0000, 'milliliters', '2026-05-08 02:38:43', '2026-05-08 02:38:43'),
(40, 2, 4, '16oz', 'unit', 50.0000, 'grams', '2026-05-08 02:38:51', '2026-05-08 02:38:51'),
(41, 2, 3, '16oz', 'unit', 20.0000, 'grams', '2026-05-08 02:38:51', '2026-05-08 02:38:51'),
(42, 2, 1, '16oz', 'unit', 200.0000, 'milliliters', '2026-05-08 02:38:51', '2026-05-08 02:38:51'),
(43, 2, 4, '22oz', 'unit', 60.0000, 'grams', '2026-05-08 02:38:57', '2026-05-08 02:38:57'),
(44, 2, 3, '22oz', 'unit', 30.0000, 'grams', '2026-05-08 02:38:57', '2026-05-08 02:38:57'),
(45, 2, 1, '22oz', 'unit', 250.0000, 'milliliters', '2026-05-08 02:38:57', '2026-05-08 02:38:57'),
(46, 5, 1, '16oz', 'unit', 200.0000, 'milliliters', '2026-05-08 03:11:05', '2026-05-08 03:11:05'),
(47, 5, 8, '16oz', 'unit', 50.0000, 'milliliters', '2026-05-08 03:11:06', '2026-05-08 03:11:06'),
(48, 5, 5, '16oz', 'unit', 20.0000, 'milliliters', '2026-05-08 03:11:06', '2026-05-08 03:11:06'),
(49, 5, 5, '22oz', 'unit', 25.0000, 'milliliters', '2026-05-08 03:12:44', '2026-05-08 03:12:44'),
(50, 5, 8, '22oz', 'unit', 50.0000, 'milliliters', '2026-05-08 03:12:44', '2026-05-08 03:12:44'),
(51, 5, 1, '22oz', 'unit', 250.0000, 'milliliters', '2026-05-08 03:12:44', '2026-05-08 03:12:44'),
(52, 6, 5, '16oz', 'unit', 20.0000, 'milliliters', '2026-05-08 03:14:18', '2026-05-08 03:14:18'),
(53, 6, 4, '16oz', 'unit', 20.0000, 'milliliters', '2026-05-08 03:14:18', '2026-05-08 03:14:18'),
(54, 6, 1, '16oz', 'unit', 200.0000, 'milliliters', '2026-05-08 03:14:18', '2026-05-08 03:14:18'),
(55, 6, 15, '16oz', 'unit', 20.0000, 'milliliters', '2026-05-08 03:14:18', '2026-05-08 03:14:18'),
(56, 6, 5, '22oz', 'unit', 25.0000, 'milliliters', '2026-05-08 03:15:48', '2026-05-08 03:15:48'),
(57, 6, 15, '22oz', 'unit', 25.0000, 'milliliters', '2026-05-08 03:15:48', '2026-05-08 03:15:48'),
(58, 6, 1, '22oz', 'unit', 250.0000, 'milliliters', '2026-05-08 03:15:48', '2026-05-08 03:15:48'),
(59, 6, 4, '22oz', 'unit', 25.0000, 'milliliters', '2026-05-08 03:15:48', '2026-05-08 03:15:48'),
(60, 7, 16, '16oz', 'unit', 200.0000, 'milliliters', '2026-05-08 03:22:33', '2026-05-08 03:22:33'),
(61, 7, 5, '16oz', 'unit', 20.0000, 'milliliters', '2026-05-08 03:22:33', '2026-05-08 03:22:33'),
(62, 7, 2, '16oz', 'unit', 150.0000, 'grams', '2026-05-08 03:22:33', '2026-05-08 03:22:33'),
(63, 7, 16, '22oz', 'unit', 250.0000, 'milliliters', '2026-05-08 03:23:17', '2026-05-08 03:23:17'),
(64, 7, 2, '22oz', 'unit', 200.0000, 'grams', '2026-05-08 03:23:18', '2026-05-08 03:23:18'),
(65, 7, 5, '22oz', 'unit', 20.0000, 'milliliters', '2026-05-08 03:23:18', '2026-05-08 03:23:18'),
(66, 8, 16, '16oz', 'unit', 200.0000, 'milliliters', '2026-05-08 03:25:00', '2026-05-08 03:25:00'),
(67, 8, 3, '16oz', 'unit', 150.0000, 'grams', '2026-05-08 03:25:00', '2026-05-08 03:25:00'),
(68, 8, 4, '16oz', 'unit', 50.0000, 'milliliters', '2026-05-08 03:25:00', '2026-05-08 03:25:00'),
(69, 8, 5, '16oz', 'unit', 20.0000, 'milliliters', '2026-05-08 03:25:00', '2026-05-08 03:25:00'),
(70, 8, 16, '22oz', 'unit', 250.0000, 'milliliters', '2026-05-08 03:25:43', '2026-05-08 03:25:43'),
(71, 8, 5, '22oz', 'unit', 20.0000, 'milliliters', '2026-05-08 03:25:44', '2026-05-08 03:25:44'),
(72, 8, 4, '22oz', 'unit', 50.0000, 'milliliters', '2026-05-08 03:25:44', '2026-05-08 03:25:44'),
(73, 8, 3, '22oz', 'unit', 200.0000, 'grams', '2026-05-08 03:25:44', '2026-05-08 03:25:44'),
(74, 4, 16, '16oz', 'unit', 250.0000, 'milliliters', '2026-05-08 03:27:24', '2026-05-08 03:27:24'),
(75, 4, 5, '16oz', 'unit', 20.0000, 'milliliters', '2026-05-08 03:27:24', '2026-05-08 03:27:24'),
(76, 4, 4, '16oz', 'unit', 50.0000, 'milliliters', '2026-05-08 03:27:24', '2026-05-08 03:27:24'),
(77, 4, 16, '22oz', 'unit', 300.0000, 'milliliters', '2026-05-08 03:28:11', '2026-05-08 03:28:11'),
(78, 4, 5, '22oz', 'unit', 25.0000, 'milliliters', '2026-05-08 03:28:11', '2026-05-08 03:28:11'),
(79, 4, 4, '22oz', 'unit', 60.0000, 'milliliters', '2026-05-08 03:28:11', '2026-05-08 03:28:11'),
(80, 9, 14, '16oz', 'unit', 20.0000, 'milliliters', '2026-05-08 03:29:11', '2026-05-08 03:29:11'),
(81, 9, 2, '16oz', 'unit', 150.0000, 'grams', '2026-05-08 03:29:12', '2026-05-08 03:29:12'),
(82, 9, 16, '16oz', 'unit', 200.0000, 'milliliters', '2026-05-08 03:29:12', '2026-05-08 03:29:12'),
(83, 9, 4, '16oz', 'unit', 50.0000, 'milliliters', '2026-05-08 03:29:12', '2026-05-08 03:29:12');

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
(2, 'John', 'jpmonroyo@kld.edu.ph', '$2y$10$bcH69b.86pONYMSQKMyviOj73/4rlct2qxpm6uRj3zcuucj/jseJS', 'customer', '2026-05-07 00:36:58', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-07 00:36:58', 'v1.0', '09123456789', 'asdasd adsadasd, Rome Street, Summerwind Village 4, Burol, Dasmariñas, Cavite, Calabarzon, 4114, Philippines', 'Dasmariñas', 'Cavite', '2026-05-06 16:36:58', '2026-05-06 17:15:22'),
(3, 'Justine Kamantigue', 'justinekamantigue10@gmail.com', '$2y$10$wLDX5E.9DW3U7t04PAUu6.bmFfnXtqWySlvLEZ51efLJzPeoqEetq', 'customer', '2026-05-08 01:09:24', '112792813485259416081', 'google', 'https://lh3.googleusercontent.com/a/ACg8ocK1hm0K7Unf_IzbO5NEjEgAe_ViUMkOZPsLn-24uwffc3OtLg=s96-c', NULL, NULL, NULL, NULL, NULL, '2026-05-08 01:09:24', 'google-oauth', '09123456789', 'Santa Maria Promenade Way, Burol, Dasmariñas, Cavite, Calabarzon, 4114, Philippines', 'Dasmariñas', 'Cavite', '2026-05-07 17:09:24', '2026-05-07 19:30:09');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `ingredients`
--
ALTER TABLE `ingredients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
