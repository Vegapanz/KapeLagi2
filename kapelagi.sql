-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 04, 2026 at 08:25 PM
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `product_id`, `size`, `quantity`, `special_instructions`, `created_at`) VALUES
(22, 6, 11, '16oz', 1, '', '2026-04-30 14:23:32');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `customer_name`, `customer_email`, `customer_phone`, `delivery_address`, `city`, `province`, `payment_method`, `total_amount`, `status`, `is_archived`, `archived_at`, `special_notes`, `cancellation_reason`, `created_at`, `updated_at`) VALUES
(3, 8, 'John Patrick Monroyo', 'jpmonroyo@kld.edu.ph', '1231231231', 'adsfsafsafsafsf', 'Dasmariñas', 'Cavite', 'COD', 100.00, 'processing', 1, '2026-05-04 00:37:24', NULL, NULL, '2026-04-30 15:01:51', '2026-05-03 16:37:24'),
(5, 9, 'dctohehe dctohehhe', 'dctohehe@gmail.com', '09123456789', 'adsfsafsafsafsf', 'Dasmariñas', '', 'COD', 260.00, 'pending', 1, '2026-05-04 00:37:24', NULL, NULL, '2026-04-30 17:19:35', '2026-05-03 16:37:24'),
(6, 9, 'dctohehe dctohehhe', 'dctohehe@gmail.com', '09123456789', 'adadasdasd', 'Dasmariñas', '', 'GCASH', 130.00, 'pending', 1, '2026-05-04 00:37:24', NULL, NULL, '2026-04-30 17:29:37', '2026-05-03 16:37:24'),
(7, 8, 'John Patrick Monroyo', 'jpmonroyo@kld.edu.ph', '09123456789', 'kldlakjdslkasdjlkads', 'Dasmariñas', '', 'COD', 260.00, 'cancelled', 1, '2026-05-04 00:37:24', NULL, 'the address is too far', '2026-05-03 16:06:47', '2026-05-03 16:37:24'),
(8, 8, 'John Patrick Monroyo', 'jpmonroyo@kld.edu.ph', '09123456789', 'dasdasdasdasdasd', 'Dasmariñas', '', 'COD', 100.00, 'cancelled', 1, '2026-05-04 00:37:24', NULL, 'hehe', '2026-05-03 16:12:13', '2026-05-03 16:37:24'),
(9, 8, 'John Patrick Monroyo', 'jpmonroyo@kld.edu.ph', '09123456789', 'asdsadasdsadasd', 'Dasmariñas', '', 'COD', 130.00, 'completed', 1, '2026-05-04 00:39:40', NULL, NULL, '2026-05-03 16:39:05', '2026-05-03 16:39:40'),
(10, 8, 'John Patrick Monroyo', 'jpmonroyo@kld.edu.ph', '09123456789', 'adfsadfsafas', 'Dasmariñas', '', 'COD', 130.00, 'pending', 0, NULL, NULL, NULL, '2026-05-03 16:43:46', '2026-05-03 16:43:46'),
(11, 10, 'Justine Kamantigue', 'justinekamantigue10@gmail.com', '09108723273', 'sta cristina 1 blf 9 lot 4', 'Dasmariñas', '', 'COD', 390.00, 'cancelled', 0, NULL, NULL, 'ayoko', '2026-05-04 01:21:38', '2026-05-04 01:22:27'),
(12, 12, 'mj', 'alconcepcion@kld.edu.ph', '09123456789', 'area e', 'Dasmariñas', '', 'COD', 300.00, 'completed', 0, NULL, NULL, NULL, '2026-05-04 05:46:56', '2026-05-04 05:52:34'),
(13, 12, 'mj', 'alconcepcion@kld.edu.ph', '09123456789', 'area e, burol', 'Dasmariñas', '', 'COD', 125.00, 'cancelled', 0, NULL, NULL, 'no ice', '2026-05-04 05:53:14', '2026-05-04 05:53:55'),
(14, 8, 'John Patrick Monroyo', 'jpmonroyo@kld.edu.ph', '09123456789', 'adfsadfsafas', 'Dasmariñas', '', 'COD', 385.00, 'pending', 0, NULL, NULL, NULL, '2026-05-04 16:26:22', '2026-05-04 16:26:22'),
(15, 8, 'John Patrick Monroyo', 'jpmonroyo@kld.edu.ph', '09123456789', 'Aguinaldo Highway, Zone 4, Poblacion, Dasmariñas, Cavite, Calabarzon, 4114, Philippines', '', '', 'COD', 130.00, 'pending', 0, NULL, NULL, NULL, '2026-05-04 17:27:45', '2026-05-04 17:27:45'),
(16, 8, 'John Patrick Monroyo', 'jpmonroyo@kld.edu.ph', '09123456789', 'Washington Street, Summerwind Village 4, Burol, Dasmariñas, Cavite, Calabarzon, 4114, Philippines', '', '', 'COD', 125.00, 'pending', 0, NULL, NULL, NULL, '2026-05-04 17:34:42', '2026-05-04 17:34:42'),
(17, 8, 'John Patrick Monroyo', 'jpmonroyo@kld.edu.ph', '09123456789', 'Albatross Street, Southcrest Village, San Agustin 2, San Agustin, Dasmariñas, Cavite, Calabarzon, 4114, Philippines', '', '', 'COD', 130.00, 'pending', 0, NULL, NULL, NULL, '2026-05-04 17:37:44', '2026-05-04 17:37:44'),
(18, 8, 'John Patrick Monroyo', 'jpmonroyo@kld.edu.ph', '09123456789', 'Zone 3, Poblacion, Dasmariñas, Cavite, Calabarzon, 4114, Philippines', '', '', 'COD', 130.00, 'pending', 0, NULL, NULL, NULL, '2026-05-04 17:41:32', '2026-05-04 17:41:32'),
(19, 8, 'John Patrick Monroyo', 'jpmonroyo@kld.edu.ph', '09123456789', 'Estanislao Carungcong Road, Pasong Bayog, Burol, Dasmariñas, Cavite, Calabarzon, 4114, Philippines', '', '', 'COD', 130.00, 'pending', 0, NULL, NULL, NULL, '2026-05-04 17:42:22', '2026-05-04 17:42:22'),
(20, 8, 'John Patrick Monroyo', 'jpmonroyo@kld.edu.ph', '09123456789', 'Arkansas Street, San Agustin 2, Burol, Dasmariñas, Cavite, Calabarzon, 4114, Philippines', 'Dasmariñas', 'Cavite', 'COD', 130.00, 'pending', 0, NULL, NULL, NULL, '2026-05-04 17:47:13', '2026-05-04 17:47:13');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `size`, `price`, `quantity`, `created_at`) VALUES
(4, 3, 2, 'Americano', '16oz', 100.00, 1, '2026-04-30 15:01:51'),
(6, 5, 8, 'Biscoff Latte', '16oz', 130.00, 2, '2026-04-30 17:19:35'),
(7, 6, 8, 'Biscoff Latte', '16oz', 130.00, 1, '2026-04-30 17:29:37'),
(8, 7, 8, 'Biscoff Latte', '16oz', 130.00, 2, '2026-05-03 16:06:47'),
(9, 8, 2, 'Americano', '16oz', 100.00, 1, '2026-05-03 16:12:13'),
(10, 9, 8, 'Biscoff Latte', '16oz', 130.00, 1, '2026-05-03 16:39:05'),
(11, 10, 8, 'Biscoff Latte', '16oz', 130.00, 1, '2026-05-03 16:43:46'),
(12, 11, 8, 'Biscoff Latte', '16oz', 130.00, 1, '2026-05-04 01:21:38'),
(13, 11, 16, 'French Vanilla', '22oz', 130.00, 2, '2026-05-04 01:21:38'),
(14, 12, 8, 'Biscoff Latte', '22oz', 150.00, 1, '2026-05-04 05:46:56'),
(15, 12, 15, 'Dirty Matcha', '22oz', 150.00, 1, '2026-05-04 05:46:56'),
(16, 13, 11, 'Blueberry Espresso', '16oz', 125.00, 1, '2026-05-04 05:53:14'),
(17, 14, 8, 'Biscoff Latte', '16oz', 130.00, 2, '2026-05-04 16:26:22'),
(18, 14, 11, 'Blueberry Espresso', '16oz', 125.00, 1, '2026-05-04 16:26:22'),
(19, 15, 8, 'Biscoff Latte', '16oz', 130.00, 1, '2026-05-04 17:27:45'),
(20, 16, 11, 'Blueberry Espresso', '16oz', 125.00, 1, '2026-05-04 17:34:42'),
(21, 17, 8, 'Biscoff Latte', '16oz', 130.00, 1, '2026-05-04 17:37:44'),
(22, 18, 8, 'Biscoff Latte', '16oz', 130.00, 1, '2026-05-04 17:41:32'),
(23, 19, 8, 'Biscoff Latte', '16oz', 130.00, 1, '2026-05-04 17:42:22'),
(24, 20, 8, 'Biscoff Latte', '16oz', 130.00, 1, '2026-05-04 17:47:13');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `category`, `image_url`, `price_16oz`, `price_22oz`, `stock`, `is_archived`, `archived_at`, `created_at`) VALUES
(1, 'Spanish Latte', 'A sweet, creamy espresso-based drink made by combining espresso with both regular milk and sweetened condensed milk', 'Coffee', NULL, 120.00, 120.00, 200, 0, NULL, '2026-04-14 16:41:17'),
(2, 'Americano', 'A classic espresso stretched with hot water for a smooth, full-bodied flavor', 'Coffee', NULL, 100.00, 100.00, 200, 0, NULL, '2026-04-14 16:41:17'),
(3, 'Blueberry Milk', 'Smooth blueberry flavor combined with creamy milk', 'Non-Coffee', NULL, 110.00, 110.00, 200, 0, NULL, '2026-04-14 16:41:17'),
(4, 'Matcha Latte', 'Vibrant green tea powder whisked with hot milk for a refreshing drink', 'Coffee', NULL, 130.00, 130.00, 200, 0, NULL, '2026-04-14 16:41:17'),
(5, 'Caramel Macchiato', 'Rich caramel sauce layered with espresso and velvety steamed milk', 'Coffee', NULL, 120.00, 120.00, 200, 0, NULL, '2026-04-14 16:41:17'),
(6, 'Vanilla Latte', 'Smooth vanilla flavor combined with espresso and creamy milk', 'Coffee', NULL, 110.00, 110.00, 200, 0, NULL, '2026-04-14 16:41:17'),
(7, 'Berry Matcha', 'A bright berry and matcha blend with a smooth finish', 'Non-Coffee', 'Coffee/BerryMatcha.png', 120.00, 140.00, 200, 0, NULL, '2026-04-26 07:10:32'),
(8, 'Biscoff Latte', 'A rich latte with spiced Biscoff sweetness', 'Coffee', 'Coffee/BiscoffLatte.png', 130.00, 150.00, 200, 0, NULL, '2026-04-26 07:10:32'),
(9, 'Blueberry', 'A fresh blueberry drink with a crisp fruity profile', 'Fruity', 'Coffee/Blueberry.png', 110.00, 130.00, 200, 0, NULL, '2026-04-26 07:10:32'),
(10, 'Blueberry Choco', 'Blueberry sweetness paired with a chocolate finish', 'Non-Coffee', 'Coffee/BlueberryChoco.png', 115.00, 135.00, 200, 0, NULL, '2026-04-26 07:10:32'),
(11, 'Blueberry Espresso', 'Fruit and espresso in a bold layered drink', 'Coffee', 'Coffee/BlueberryEspresso.png', 125.00, 145.00, 200, 0, NULL, '2026-04-26 07:10:32'),
(12, 'Blueberry Matcha', 'Matcha with a sweet blueberry twist', 'Non-Coffee', 'Coffee/BlueberryMAtcha.png', 120.00, 140.00, 200, 0, NULL, '2026-04-26 07:10:32'),
(13, 'Choco Berry', 'A chocolate and berry combination with a sweet tang', 'Non-Coffee', 'Coffee/ChocoBerry.png', 115.00, 135.00, 200, 0, NULL, '2026-04-26 07:10:32'),
(14, 'Choco Matcha', 'Chocolate meets matcha in a creamy blended drink', 'Non-Coffee', 'Coffee/ChocoMatcha.png', 120.00, 140.00, 200, 0, NULL, '2026-04-26 07:10:32'),
(15, 'Dirty Matcha', 'Matcha layered with espresso for a bolder sip', 'Coffee', 'Coffee/Dirty Matcha.png', 130.00, 150.00, 200, 0, NULL, '2026-04-26 07:10:32'),
(16, 'French Vanilla', 'Smooth vanilla and coffee with a soft dessert note', 'Coffee', 'Coffee/FrenchVanilla.png', 110.00, 130.00, 200, 0, NULL, '2026-04-26 07:10:32'),
(17, 'Green Apple', 'Crisp green apple flavor with a bright finish', 'Fruity', 'Coffee/GreenApple.png', 110.00, 130.00, 200, 0, NULL, '2026-04-26 07:10:32'),
(18, 'Hazelnut Latte', 'Warm hazelnut sweetness blended into a latte', 'Coffee', 'Coffee/HazelnutLatte.png', 120.00, 140.00, 200, 0, NULL, '2026-04-26 07:10:32'),
(19, 'Icy Choco', 'A chilled chocolate drink with a refreshing finish', 'Non-Coffee', 'Coffee/IcyChoco.png', 115.00, 135.00, 200, 0, NULL, '2026-04-26 07:10:32'),
(20, 'Lemonade', 'A clean citrus drink with a bright, tangy kick', 'Fruity', 'Coffee/Lemonade.png', 100.00, 120.00, 200, 0, NULL, '2026-04-26 07:10:32'),
(21, 'Lychee', 'Sweet lychee flavor with a fragrant fruit profile', 'Fruity', 'Coffee/Lychee.png', 110.00, 130.00, 200, 0, NULL, '2026-04-26 07:10:32'),
(22, 'Mocha Latte', 'Coffee and chocolate blended into a smooth latte', 'Coffee', 'Coffee/MochaLatte.png', 125.00, 145.00, 200, 0, NULL, '2026-04-26 07:10:32'),
(23, 'Nutella Latte', 'A creamy latte with rich Nutella-inspired flavor', 'Coffee', 'Coffee/NutellaLatte.png', 130.00, 150.00, 200, 0, NULL, '2026-04-26 07:10:32'),
(24, 'Salted Caramel Latte', 'Sweet caramel balanced with a light salted finish', 'Coffee', 'Coffee/SaltedCaramelLatte.png', 130.00, 150.00, 200, 0, NULL, '2026-04-26 07:10:33'),
(25, 'Strawberry Milk', 'Creamy strawberry milk with a soft dessert-like flavor', 'Non-Coffee', 'Coffee/StrawberryMilk.png', 110.00, 130.00, 200, 0, NULL, '2026-04-26 07:10:33'),
(26, 'Vietnamese', 'A bold Vietnamese-style coffee with a rich finish', 'Coffee', 'Coffee/Vietnamese.png', 120.00, 140.00, 200, 0, NULL, '2026-04-26 07:10:33');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email_verified_at` datetime DEFAULT NULL,
  `google_id` varchar(191) DEFAULT NULL,
  `oauth_provider` varchar(20) DEFAULT NULL,
  `oauth_avatar_url` varchar(255) DEFAULT NULL,
  `role` varchar(20) DEFAULT 'customer',
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `email_verified_at`, `google_id`, `oauth_provider`, `oauth_avatar_url`, `role`, `email_verification_token`, `email_verification_code`, `pending_email`, `pending_email_verification_token`, `pending_email_verification_code`, `terms_accepted_at`, `terms_version`, `phone`, `address`, `city`, `province`, `created_at`, `updated_at`) VALUES
(6, 'John', 'newaccarcanelegends@gmail.com', '$2y$10$HdRnxojOxL4vxXfeUj5jbekaWSoCaclV.mxF86.I3P4/Ol5bgQ//O', '2026-04-29 00:42:54', NULL, NULL, NULL, 'customer', NULL, NULL, 'tiktikboy007@gmail.com', 'a91cc20be9382caa242541966209efd97387bc03cd9f730e359fc576dd484d71', '766769', '2026-04-29 00:42:54', 'v1.0', '09123456789', '', '', '', '2026-04-28 16:42:54', '2026-04-30 16:17:19'),
(7, 'Admin User', 'admin@kapelagi.com', '$2y$10$5jrd8S3qjFM5U/JftdsRs.gpw5svK39SjSzYp0Kjg4OfDdXqW05rS', '2026-04-28 20:11:24', NULL, NULL, NULL, 'admin', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-28 12:11:24', '2026-04-28 12:11:24'),
(8, 'John Patrick Monroyo', 'jpmonroyo@kld.edu.ph', '$2y$10$rCU0YMQGCmj72AzZ8vZj4uMXJI18NXz17POCSFRqLxREnPnBSm6Jm', '2026-04-30 22:52:59', '114263499582382083487', 'google', 'https://lh3.googleusercontent.com/a/ACg8ocIfiJwcTlIb6jGvb3UOOvncLZcJt4nBfLjnqdEZxOb6-kGX0Pgp=s96-c', 'customer', NULL, NULL, NULL, NULL, NULL, '2026-04-30 22:52:59', 'google-oauth', '09123456789', 'blk 213 lot 21, Arkansas Street, San Agustin 2, Burol, Dasmariñas, Cavite, Calabarzon, 4114, Philippines', 'Dasmariñas', 'Cavite', '2026-04-30 14:52:59', '2026-05-04 17:47:13'),
(9, 'dctohehe dctohehhe', 'dctohehe@gmail.com', '$2y$10$0wjkhy3Mv6XDWmy.Hqc/MeV0zHklXNc.k3a3GUEWL10R8J3PCoFge', '2026-05-01 00:31:48', '103189660823423816270', 'google', 'https://lh3.googleusercontent.com/a/ACg8ocIT1hWxfZoylpTOq_jQJi04l-V1tfquO5IO_zFqkh7OHZqVOA=s96-c', 'customer', NULL, NULL, NULL, NULL, NULL, '2026-05-01 00:31:48', 'google-oauth', '09123456789', NULL, NULL, NULL, '2026-04-30 16:31:48', '2026-04-30 17:09:35'),
(10, 'Justine Kamantigue', 'justinekamantigue10@gmail.com', '$2y$10$inboyPTdowi.fQyrm0FOYeLVw8kvpA/Qn7UVnd8eFvMVGiH7eRB6K', '2026-05-04 09:13:30', '112792813485259416081', 'google', 'https://lh3.googleusercontent.com/a/ACg8ocK1hm0K7Unf_IzbO5NEjEgAe_ViUMkOZPsLn-24uwffc3OtLg=s96-c', 'customer', NULL, NULL, NULL, NULL, NULL, '2026-05-04 09:13:30', 'google-oauth', '09108723273', 'sta cristina 1 blf 9 lot 4', 'Dasmariñas', NULL, '2026-05-04 01:13:30', '2026-05-04 10:30:07'),
(11, 'lyk', 'lykemaeconcepcion@gmail.com', '$2y$10$pCioUpxihOt/dV85jazfEeFGNsw7AUrVh/vSwwd0y3FYgwHkVZ.vS', '2026-05-04 09:28:14', NULL, NULL, NULL, 'customer', NULL, NULL, NULL, NULL, NULL, '2026-05-04 09:28:14', 'v1.0', NULL, NULL, NULL, NULL, '2026-05-04 01:28:14', '2026-05-04 01:28:14'),
(12, 'mj', 'alconcepcion@kld.edu.ph', '$2y$10$ltdJ.T7bHo84SiSrusOz0uVVMJrDPZc6W64MxLaGfpYEUrCYmQfyK', '2026-05-04 13:40:38', NULL, NULL, NULL, 'customer', NULL, NULL, NULL, NULL, NULL, '2026-05-04 13:40:38', 'v1.0', '09123456789', 'area e, burol', 'Dasmariñas', NULL, '2026-05-04 05:40:38', '2026-05-04 05:53:14');

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
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email` (`email`),
  ADD KEY `token` (`token`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
