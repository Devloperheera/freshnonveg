-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 22, 2026 at 06:56 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `frsh_non_veg`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `email`, `phone`, `password`, `status`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Amit Kumar Sinha', 'heeralalzx2020@gmail.com', '9899050881', '$2y$12$neX/soWJ0Lql32QQfvONOO4i4FpEfaqAAly4C8l440f00lMZsJfnO', 1, NULL, '2026-10-17 00:54:32', '2026-10-17 00:58:01');

-- --------------------------------------------------------

--
-- Table structure for table `advantage_items`
--

CREATE TABLE `advantage_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `icon` varchar(32) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `advantage_items`
--

INSERT INTO `advantage_items` (`id`, `icon`, `title`, `description`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES
(1, '💪', 'ज्यादा प्रोटीन', 'Fresh meat में protein और nutrients ज्यादा होते हैं', 1, 1, '2026-10-17 04:54:05', '2026-10-17 04:54:05'),
(2, '😋', 'बेहतर स्वाद', 'Frozen नहीं, इसलिए taste असली और लाजवाब!', 2, 1, '2026-10-17 04:54:05', '2026-10-17 04:54:05'),
(3, '🩺', 'सेहत के लिए अच्छा', 'कोई chemicals नहीं, बस pure freshness!', 3, 1, '2026-10-17 04:54:05', '2026-10-17 04:54:05'),
(4, '⚡', 'तुरंत एनर्जी', 'Fresh nutrients से instant energy मिलती है', 4, 1, '2026-10-17 04:54:05', '2026-10-17 04:54:05');

-- --------------------------------------------------------

--
-- Table structure for table `banners`
--

CREATE TABLE `banners` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `banners`
--

INSERT INTO `banners` (`id`, `name`, `image`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Heera gtconnect', 'banners/nfeVOXR3WKBqJna8LC9IY1SPYsoEg0cINCUAo1nT.jpg', 1, '2026-10-17 04:18:43', '2026-10-17 04:18:43'),
(2, 'Private Cabin', 'banners/ILkv2UXTte5StQHJB6Gx9NEwvseqTvavD0Znbsa6.jpg', 1, '2026-10-17 04:19:10', '2026-10-17 04:19:10');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `weight_label` varchar(255) DEFAULT NULL,
  `preference_note` text DEFAULT NULL,
  `quantity` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `unit_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `image`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Chicken', 'categories/chicken.jpg', 1, '2026-10-17 04:03:55', '2026-10-17 04:03:55'),
(2, 'Mutton', 'categories/mutton.jpg', 1, '2026-10-17 04:03:56', '2026-10-17 04:03:56'),
(3, 'Fish', 'categories/fish.jpg', 1, '2026-10-17 04:03:57', '2026-10-17 04:03:57'),
(4, 'Prawns', 'categories/prawns.jpg', 1, '2026-10-17 04:03:58', '2026-10-17 04:03:58'),
(5, 'Crab', 'categories/crab.jpg', 1, '2026-10-17 04:03:59', '2026-10-17 04:03:59'),
(6, 'Lobster', 'categories/lobster.jpg', 1, '2026-10-17 04:04:00', '2026-10-17 04:04:00'),
(7, 'Duck', 'categories/duck.jpg', 1, '2026-10-17 04:04:00', '2026-10-17 04:04:00'),
(9, 'Eggs', 'categories/eggs.jpg', 1, '2026-10-17 04:04:01', '2026-10-17 04:04:01');

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

CREATE TABLE `complaints` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'open',
  `admin_reply` text DEFAULT NULL,
  `credited_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `reply_emailed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(40) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `discount_type` varchar(20) NOT NULL,
  `discount_value` decimal(12,2) NOT NULL,
  `min_order_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `max_discount` decimal(12,2) DEFAULT NULL,
  `usage_limit` int(10) UNSIGNED DEFAULT NULL,
  `used_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `starts_at` date DEFAULT NULL,
  `expires_at` date DEFAULT NULL,
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `delivery_locations`
--

CREATE TABLE `delivery_locations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `delivery_locations`
--

INSERT INTO `delivery_locations` (`id`, `name`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES
(1, '📍 Noida', 1, 1, '2026-10-17 04:49:36', '2026-10-17 04:49:36'),
(2, '📍 Greater Noida', 2, 1, '2026-10-17 04:49:36', '2026-10-17 04:49:36'),
(3, '📍 Greater Noida West', 3, 1, '2026-10-17 04:49:37', '2026-10-17 04:49:37'),
(4, '📍 Ghaziabad', 4, 1, '2026-10-17 04:49:37', '2026-10-17 04:49:37'),
(5, '📍 Gautam Buddha Nagar', 5, 1, '2026-10-17 04:49:37', '2026-10-17 04:49:37');

-- --------------------------------------------------------

--
-- Table structure for table `delivery_slots`
--

CREATE TABLE `delivery_slots` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `slot_key` varchar(20) NOT NULL,
  `label` varchar(80) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `delivery_slots`
--

INSERT INTO `delivery_slots` (`id`, `slot_key`, `label`, `start_time`, `end_time`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES
(1, '09:00-10:00', '09:00 AM – 10:00 AM', '09:00:00', '10:00:00', 1, 1, '2026-10-21 05:04:27', '2026-10-21 05:04:27'),
(2, '10:00-11:00', '10:00 AM – 11:00 AM', '10:00:00', '11:00:00', 2, 1, '2026-10-21 05:04:27', '2026-10-21 05:04:27'),
(3, '11:00-12:00', '11:00 AM – 12:00 PM', '11:00:00', '12:00:00', 3, 1, '2026-10-21 05:04:27', '2026-10-21 05:04:27'),
(4, '12:00-13:00', '12:00 PM – 01:00 PM', '12:00:00', '13:00:00', 4, 1, '2026-10-21 05:04:27', '2026-10-21 05:04:27'),
(5, '13:00-14:00', '01:00 PM – 02:00 PM', '13:00:00', '14:00:00', 5, 1, '2026-10-21 05:04:27', '2026-10-21 05:04:27'),
(6, '14:00-15:00', '02:00 PM – 03:00 PM', '14:00:00', '15:00:00', 6, 1, '2026-10-21 05:04:27', '2026-10-21 05:04:27'),
(7, '15:00-16:00', '03:00 PM – 04:00 PM', '15:00:00', '16:00:00', 7, 1, '2026-10-21 05:04:27', '2026-10-21 05:04:27'),
(8, '16:00-17:00', '04:00 PM – 05:00 PM', '16:00:00', '17:00:00', 8, 1, '2026-10-21 05:04:27', '2026-10-21 05:04:27'),
(9, '17:00-18:00', '05:00 PM – 06:00 PM', '17:00:00', '18:00:00', 9, 1, '2026-10-21 05:04:27', '2026-10-21 05:04:27'),
(10, '18:00-19:00', '06:00 PM – 07:00 PM', '18:00:00', '19:00:00', 10, 1, '2026-10-21 05:04:27', '2026-10-21 05:04:27'),
(11, '19:00-20:00', '07:00 PM – 08:00 PM', '19:00:00', '20:00:00', 11, 1, '2026-10-21 05:04:27', '2026-10-21 05:04:27'),
(12, '20:00-21:00', '08:00 PM – 09:00 PM', '20:00:00', '21:00:00', 12, 1, '2026-10-21 05:04:27', '2026-10-21 05:04:27');

-- --------------------------------------------------------

--
-- Table structure for table `delivery_zones`
--

CREATE TABLE `delivery_zones` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(20) NOT NULL,
  `description` text DEFAULT NULL,
  `center_lat` decimal(10,7) DEFAULT NULL,
  `center_lng` decimal(10,7) DEFAULT NULL,
  `radius_km` decimal(8,2) DEFAULT NULL,
  `center_address` varchar(255) DEFAULT NULL,
  `polygon` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`polygon`)),
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `delivery_zones`
--

INSERT INTO `delivery_zones` (`id`, `name`, `type`, `description`, `center_lat`, `center_lng`, `radius_km`, `center_address`, `polygon`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES
(6, 'Noida & Greater Noida — Pincodes', 'pincode', 'Gautam Buddha Nagar ke saare mukhya pincode', NULL, NULL, NULL, NULL, NULL, 1, 1, '2026-10-21 05:57:42', '2026-10-21 05:57:42'),
(7, 'Ghaziabad — Pincodes', 'pincode', 'Ghaziabad district ke mukhya pincode', NULL, NULL, NULL, NULL, NULL, 2, 1, '2026-10-21 05:57:42', '2026-10-21 05:57:42'),
(8, 'Noida & Greater Noida — Map Area', 'polygon', 'Geocode backup — poora GB Nagar approx boundary', NULL, NULL, NULL, NULL, '[[28.325,77.318],[28.325,77.48],[28.325,77.62],[28.325,77.705],[28.42,77.715],[28.52,77.7],[28.605,77.64],[28.62,77.5],[28.605,77.38],[28.54,77.325],[28.46,77.312],[28.38,77.312]]', 3, 1, '2026-10-21 05:57:42', '2026-10-21 05:57:42'),
(9, 'Ghaziabad — Map Area', 'polygon', 'Geocode backup — poora Ghaziabad district approx boundary', NULL, NULL, NULL, NULL, '[[28.495,77.335],[28.495,77.52],[28.495,77.72],[28.495,77.92],[28.495,78.135],[28.61,78.155],[28.73,78.145],[28.865,78.04],[28.875,77.82],[28.865,77.58],[28.78,77.4],[28.65,77.338],[28.55,77.332]]', 4, 1, '2026-10-21 05:57:42', '2026-10-21 05:57:42');

-- --------------------------------------------------------

--
-- Table structure for table `delivery_zone_pincodes`
--

CREATE TABLE `delivery_zone_pincodes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `delivery_zone_id` bigint(20) UNSIGNED NOT NULL,
  `pincode` varchar(6) NOT NULL,
  `area_name` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `delivery_zone_pincodes`
--

INSERT INTO `delivery_zone_pincodes` (`id`, `delivery_zone_id`, `pincode`, `area_name`, `city`, `state`, `created_at`, `updated_at`) VALUES
(56, 6, '201301', NULL, NULL, NULL, '2026-10-21 05:58:21', '2026-10-21 05:58:21'),
(57, 6, '201302', NULL, NULL, NULL, '2026-10-21 05:58:21', '2026-10-21 05:58:21'),
(58, 6, '201303', NULL, NULL, NULL, '2026-10-21 05:58:21', '2026-10-21 05:58:21'),
(59, 6, '201304', NULL, NULL, NULL, '2026-10-21 05:58:21', '2026-10-21 05:58:21'),
(60, 6, '201305', NULL, NULL, NULL, '2026-10-21 05:58:21', '2026-10-21 05:58:21'),
(61, 6, '201306', NULL, NULL, NULL, '2026-10-21 05:58:21', '2026-10-21 05:58:21'),
(62, 6, '201307', NULL, NULL, NULL, '2026-10-21 05:58:21', '2026-10-21 05:58:21'),
(63, 6, '201308', NULL, NULL, NULL, '2026-10-21 05:58:21', '2026-10-21 05:58:21'),
(64, 6, '201309', NULL, NULL, NULL, '2026-10-21 05:58:21', '2026-10-21 05:58:21'),
(65, 6, '201310', NULL, NULL, NULL, '2026-10-21 05:58:21', '2026-10-21 05:58:21'),
(66, 6, '201311', NULL, NULL, NULL, '2026-10-21 05:58:21', '2026-10-21 05:58:21'),
(67, 6, '201312', NULL, NULL, NULL, '2026-10-21 05:58:21', '2026-10-21 05:58:21'),
(68, 6, '201313', NULL, NULL, NULL, '2026-10-21 05:58:21', '2026-10-21 05:58:21'),
(69, 6, '201314', NULL, NULL, NULL, '2026-10-21 05:58:21', '2026-10-21 05:58:21'),
(70, 6, '201315', NULL, NULL, NULL, '2026-10-21 05:58:21', '2026-10-21 05:58:21'),
(71, 6, '201316', NULL, NULL, NULL, '2026-10-21 05:58:21', '2026-10-21 05:58:21'),
(72, 6, '201317', NULL, NULL, NULL, '2026-10-21 05:58:21', '2026-10-21 05:58:21'),
(73, 6, '201318', NULL, NULL, NULL, '2026-10-21 05:58:21', '2026-10-21 05:58:21'),
(74, 6, '203135', NULL, NULL, NULL, '2026-10-21 05:58:21', '2026-10-21 05:58:21'),
(75, 6, '203141', NULL, NULL, NULL, '2026-10-21 05:58:21', '2026-10-21 05:58:21'),
(76, 6, '203155', NULL, NULL, NULL, '2026-10-21 05:58:21', '2026-10-21 05:58:21'),
(77, 6, '203201', NULL, NULL, NULL, '2026-10-21 05:58:21', '2026-10-21 05:58:21'),
(78, 6, '203202', NULL, NULL, NULL, '2026-10-21 05:58:21', '2026-10-21 05:58:21'),
(79, 6, '203203', NULL, NULL, NULL, '2026-10-21 05:58:21', '2026-10-21 05:58:21'),
(80, 6, '203207', NULL, NULL, NULL, '2026-10-21 05:58:21', '2026-10-21 05:58:21'),
(81, 6, '203209', NULL, NULL, NULL, '2026-10-21 05:58:21', '2026-10-21 05:58:21'),
(82, 7, '201001', NULL, NULL, NULL, '2026-10-21 05:58:21', '2026-10-21 05:58:21'),
(83, 7, '201002', NULL, NULL, NULL, '2026-10-21 05:58:21', '2026-10-21 05:58:21'),
(84, 7, '201003', NULL, NULL, NULL, '2026-10-21 05:58:21', '2026-10-21 05:58:21'),
(85, 7, '201004', NULL, NULL, NULL, '2026-10-21 05:58:21', '2026-10-21 05:58:21'),
(86, 7, '201005', NULL, NULL, NULL, '2026-10-21 05:58:21', '2026-10-21 05:58:21'),
(87, 7, '201006', NULL, NULL, NULL, '2026-10-21 05:58:21', '2026-10-21 05:58:21'),
(88, 7, '201007', NULL, NULL, NULL, '2026-10-21 05:58:21', '2026-10-21 05:58:21'),
(89, 7, '201008', NULL, NULL, NULL, '2026-10-21 05:58:21', '2026-10-21 05:58:21'),
(90, 7, '201009', NULL, NULL, NULL, '2026-10-21 05:58:21', '2026-10-21 05:58:21'),
(91, 7, '201010', NULL, NULL, NULL, '2026-10-21 05:58:21', '2026-10-21 05:58:21'),
(92, 7, '201011', NULL, NULL, NULL, '2026-10-21 05:58:21', '2026-10-21 05:58:21'),
(93, 7, '201012', NULL, NULL, NULL, '2026-10-21 05:58:21', '2026-10-21 05:58:21'),
(94, 7, '201013', NULL, NULL, NULL, '2026-10-21 05:58:21', '2026-10-21 05:58:21'),
(95, 7, '201014', NULL, NULL, NULL, '2026-10-21 05:58:21', '2026-10-21 05:58:21'),
(96, 7, '201015', NULL, NULL, NULL, '2026-10-21 05:58:21', '2026-10-21 05:58:21'),
(97, 7, '201016', NULL, NULL, NULL, '2026-10-21 05:58:21', '2026-10-21 05:58:21'),
(98, 7, '201017', NULL, NULL, NULL, '2026-10-21 05:58:21', '2026-10-21 05:58:21'),
(99, 7, '201020', NULL, NULL, NULL, '2026-10-21 05:58:21', '2026-10-21 05:58:21'),
(100, 7, '201102', NULL, NULL, NULL, '2026-10-21 05:58:21', '2026-10-21 05:58:21'),
(101, 7, '201103', NULL, NULL, NULL, '2026-10-21 05:58:21', '2026-10-21 05:58:21'),
(102, 7, '201201', NULL, NULL, NULL, '2026-10-21 05:58:21', '2026-10-21 05:58:21'),
(103, 7, '201204', NULL, NULL, NULL, '2026-10-21 05:58:21', '2026-10-21 05:58:21'),
(104, 7, '245201', NULL, NULL, NULL, '2026-10-21 05:58:21', '2026-10-21 05:58:21'),
(105, 7, '245205', NULL, NULL, NULL, '2026-10-21 05:58:21', '2026-10-21 05:58:21'),
(106, 7, '245206', NULL, NULL, NULL, '2026-10-21 05:58:21', '2026-10-21 05:58:21'),
(107, 7, '245207', NULL, NULL, NULL, '2026-10-21 05:58:21', '2026-10-21 05:58:21'),
(108, 7, '245208', NULL, NULL, NULL, '2026-10-21 05:58:21', '2026-10-21 05:58:21'),
(109, 7, '245304', NULL, NULL, NULL, '2026-10-21 05:58:21', '2026-10-21 05:58:21');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` varchar(255) NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `faqs`
--

CREATE TABLE `faqs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `question` varchar(255) NOT NULL,
  `answer` text NOT NULL,
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `faqs`
--

INSERT INTO `faqs` (`id`, `question`, `answer`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES
(1, 'क्या सब कुछ fresh होता है?', 'हाँ, हम सीधे meat shop से ताज़ा सामान देते हैं, frozen नहीं।', 1, 1, '2026-10-17 04:35:50', '2026-10-17 04:35:50'),
(2, 'Free delivery कब मिलती है?', '₹999 या उससे ऊपर के order पर delivery free है।', 2, 1, '2026-10-17 04:35:50', '2026-10-17 04:35:50'),
(3, 'किन शहरों में delivery है?', 'Noida, Greater Noida, Greater Noida West, Ghaziabad और Gautam Buddha Nagar।', 3, 1, '2026-10-17 04:35:50', '2026-10-17 04:35:50'),
(4, 'Order कैसे करें?', 'Website से या WhatsApp पर सीधे order करें।', 4, 1, '2026-10-17 04:35:50', '2026-10-17 04:35:50');

-- --------------------------------------------------------

--
-- Table structure for table `home_sections`
--

CREATE TABLE `home_sections` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `section_key` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `home_sections`
--

INSERT INTO `home_sections` (`id`, `section_key`, `title`, `subtitle`, `created_at`, `updated_at`) VALUES
(1, 'order_way', 'Order करना है <u>आसान</u>', 'बस 4 steps में ताज़ा गोश्त आपके दरवाज़े पर 🚪', '2026-10-17 04:49:36', '2026-10-17 04:49:36'),
(2, 'delivery', 'हम <u>Deliver</u> करते हैं 🛵', 'आपके शहर में भी!', '2026-10-17 04:49:36', '2026-10-17 04:49:36'),
(3, 'advantages', 'Fresh खाने के <u>फायदे!</u>', 'ताज़ा गोश्त क्यों है बेहतर? 🤔', '2026-10-17 04:54:05', '2026-10-17 04:54:05');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` smallint(5) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_03_28_000000_create_admins_table', 2),
(5, '2019_12_14_000001_create_personal_access_tokens_table', 3),
(6, '2026_03_28_120000_extend_users_table_for_web_and_app', 3),
(7, '2026_03_28_140000_create_user_commerce_tables', 4),
(8, '2026_03_28_150000_create_banners_table', 5),
(9, '2026_03_28_160000_create_categories_table', 6),
(10, '2026_03_28_170000_create_tags_and_products_tables', 7),
(11, '2026_03_28_180000_add_product_media_tables', 8),
(12, '2026_03_28_190000_create_pages_table', 9),
(13, '2026_03_28_200000_add_content_fields_to_pages_table', 10),
(14, '2026_03_28_210000_split_page_contents_from_pages', 11),
(15, '2026_03_28_220000_create_testimonials_table', 12),
(16, '2026_03_28_230000_create_faqs_table', 13),
(17, '2026_03_29_120000_create_home_page_sections_tables', 14),
(18, '2026_03_29_140000_create_advantage_items_table', 15),
(19, '2026_03_29_180000_add_product_fields_to_cart_and_wishlist', 16),
(20, '2026_03_29_200000_add_checkout_fields_to_orders', 17),
(21, '2026_03_29_210000_add_cashfree_fields_to_orders', 18),
(22, '2026_03_29_220000_add_delivery_schedule_to_orders', 19),
(23, '2026_10_21_160200_create_delivery_slots_table', 20),
(24, '2026_10_21_161000_create_coupons_table', 21),
(25, '2026_10_21_162200_add_bill_emailed_at_to_orders', 22),
(26, '2026_10_21_163000_create_wallet_and_complaints', 23),
(27, '2026_10_21_170000_create_product_reviews_table', 24),
(28, '2026_10_21_171000_create_delivery_zones_table', 25),
(29, '2026_10_21_172000_add_cancellation_to_orders', 26),
(30, '2026_10_21_180000_add_admin_audit_to_wallet_and_complaints', 27),
(31, '2026_10_21_174300_add_admin_seen_at_to_orders', 28),
(32, '2026_10_21_175400_add_performance_indexes', 29),
(33, '2026_10_21_180500_lengthen_users_otp_column', 30);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `delivery_address` text DEFAULT NULL,
  `pincode` varchar(6) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `post_office` varchar(150) DEFAULT NULL,
  `delivery_schedule_type` varchar(20) NOT NULL DEFAULT 'scheduled',
  `delivery_date` date DEFAULT NULL,
  `delivery_time` varchar(50) DEFAULT NULL,
  `delivery_fee` decimal(12,2) NOT NULL DEFAULT 0.00,
  `payment_method` varchar(30) NOT NULL DEFAULT 'cod',
  `cashfree_payment_id` varchar(100) DEFAULT NULL,
  `customer_notes` text DEFAULT NULL,
  `coupon_id` bigint(20) UNSIGNED DEFAULT NULL,
  `coupon_code` varchar(40) DEFAULT NULL,
  `discount_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `wallet_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `order_number` varchar(40) NOT NULL,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` varchar(20) NOT NULL DEFAULT 'completed',
  `bill_emailed_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `admin_seen_at` timestamp NULL DEFAULT NULL,
  `cancel_reason` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `phone`, `delivery_address`, `pincode`, `state`, `city`, `post_office`, `delivery_schedule_type`, `delivery_date`, `delivery_time`, `delivery_fee`, `payment_method`, `cashfree_payment_id`, `customer_notes`, `coupon_id`, `coupon_code`, `discount_amount`, `wallet_amount`, `order_number`, `total`, `status`, `bill_emailed_at`, `cancelled_at`, `admin_seen_at`, `cancel_reason`, `created_at`, `updated_at`) VALUES
(1, 1, '9899050881', 'vikas nager, Jalna, Maharashtra, 431114', '431114', 'Maharashtra', 'Jalna', NULL, 'instant', NULL, 'instant', 100.00, 'cashfree', NULL, 'sdzdsds', NULL, NULL, 0.00, 0.00, 'FNV-20261021-9ALPAQ', 101.00, 'payment_failed', NULL, NULL, '2026-10-21 06:45:34', NULL, '2026-10-21 05:01:58', '2026-10-21 05:01:58'),
(2, 1, '9899050881', 'vikas nager, Jalna, Maharashtra, 431114, West Delhi, Delhi, 110059', '110059', 'Delhi', 'West Delhi', NULL, 'instant', NULL, 'instant', 100.00, 'cashfree', NULL, 'aszs', NULL, NULL, 0.00, 0.00, 'FNV-20261021-IA5KEA', 101.00, 'payment_failed', NULL, NULL, '2026-10-21 06:45:34', NULL, '2026-10-21 05:02:26', '2026-10-21 05:02:27'),
(3, 1, '9899050881', 'vikas nager, Jalna, Maharashtra, 431114, West Delhi, Delhi, 110059', '110059', 'Delhi', 'West Delhi', NULL, 'instant', NULL, 'instant', 100.00, 'cashfree', NULL, 'dsxzx', NULL, NULL, 0.00, 0.00, 'FNV-20261021-2HSJ0L', 101.00, 'awaiting_payment', NULL, NULL, '2026-10-21 06:45:34', NULL, '2026-10-21 05:09:12', '2026-10-21 05:09:12'),
(4, 1, '9899050881', 'vikas nager, Jalna, Maharashtra, 431114, West Delhi, Delhi, 110059', '110059', 'Delhi', 'West Delhi', NULL, 'instant', NULL, 'instant', 100.00, 'cashfree', NULL, 'szx', NULL, NULL, 0.00, 0.00, 'FNV-20261021-G19QWK', 101.00, 'awaiting_payment', NULL, NULL, '2026-10-21 06:45:34', NULL, '2026-10-21 05:17:43', '2026-10-21 05:17:43'),
(5, 1, '9899050881', 'H.No. 21, Gali No. 4, Nangloi Jat, Delhi - 110041', '110041', 'Delhi', 'West Delhi', 'Nangloi Jat', 'scheduled', '2026-10-22', '12:00-13:00', 0.00, 'cashfree', 'DEMO-PAY-0001', 'Ghanti bajane se pehle call karein.', NULL, NULL, 0.00, 0.00, 'FNV-DEMO-0001', 656.00, 'paid', '2026-10-21 05:49:21', NULL, '2026-10-21 06:45:34', NULL, '2026-10-21 05:49:21', '2026-10-21 06:47:00');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `weight_label` varchar(80) DEFAULT NULL,
  `preference_note` text DEFAULT NULL,
  `quantity` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `unit_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `weight_label`, `preference_note`, `quantity`, `unit_price`, `created_at`, `updated_at`) VALUES
(1, 1, 19, 'Tandoori Chicken Marinated', '500 g', NULL, 1, 1.00, '2026-10-21 05:01:58', '2026-10-21 05:01:58'),
(2, 2, 19, 'Tandoori Chicken Marinated', '500 g', NULL, 1, 1.00, '2026-10-21 05:02:26', '2026-10-21 05:02:26'),
(3, 3, 19, 'Tandoori Chicken Marinated', '500 g', NULL, 1, 1.00, '2026-10-21 05:09:12', '2026-10-21 05:09:12'),
(4, 4, 19, 'Tandoori Chicken Marinated', '500 g', NULL, 1, 1.00, '2026-10-21 05:17:43', '2026-10-21 05:17:43'),
(5, 5, 1, 'Chicken Breast Boneless', '500 g', 'Leg piece zyada', 2, 189.00, '2026-10-21 05:49:21', '2026-10-21 05:49:21'),
(6, 5, 2, 'Chicken Curry Cut', '500 g', NULL, 1, 129.00, '2026-10-21 05:49:21', '2026-10-21 05:49:21'),
(7, 5, 3, 'Chicken Drumsticks', '500 g', NULL, 1, 149.00, '2026-10-21 05:49:21', '2026-10-21 05:49:21');

-- --------------------------------------------------------

--
-- Table structure for table `order_steps`
--

CREATE TABLE `order_steps` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_steps`
--

INSERT INTO `order_steps` (`id`, `title`, `description`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES
(1, 'चुनो 🛍️', 'अपना पसंदीदा item चुनें', 1, 1, '2026-10-17 04:49:36', '2026-10-17 04:49:36'),
(2, 'कटवाओ 🔪', 'हमारे कसाई ताज़ा काटते हैं', 2, 1, '2026-10-17 04:49:36', '2026-10-17 04:49:36'),
(3, 'पैक करो 📦', 'Hygienic पैकिंग', 3, 1, '2026-10-17 04:49:36', '2026-10-17 04:49:36'),
(4, 'खाओ 😋', 'घर पर फटाफट delivery', 4, 1, '2026-10-17 04:49:36', '2026-10-17 04:49:36');

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pages`
--

INSERT INTO `pages` (`id`, `name`, `status`, `created_at`, `updated_at`) VALUES
(1, 'About Us', 1, '2026-10-17 03:47:10', '2026-10-17 03:47:10'),
(2, 'term and condition', 1, '2026-10-17 03:47:26', '2026-10-17 03:47:26'),
(3, 'Terms & Conditions', 1, '2026-10-17 04:36:56', '2026-10-17 04:36:56'),
(4, 'Privacy Policy', 1, '2026-10-17 04:36:56', '2026-10-17 04:36:56'),
(5, 'Refund Policy', 1, '2026-10-17 04:36:56', '2026-10-17 04:36:56'),
(6, 'Contact', 1, '2026-10-17 04:37:43', '2026-10-17 04:37:43'),
(7, 'Blog', 1, '2026-10-17 04:37:43', '2026-10-17 04:37:43');

-- --------------------------------------------------------

--
-- Table structure for table `page_contents`
--

CREATE TABLE `page_contents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `page_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` longtext DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `page_contents`
--

INSERT INTO `page_contents` (`id`, `page_id`, `title`, `content`, `meta_title`, `meta_description`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'About Us', NULL, NULL, NULL, 1, '2026-10-17 03:47:10', '2026-10-17 03:47:10'),
(2, 2, 'term and condition', NULL, NULL, NULL, 1, '2026-10-17 03:47:26', '2026-10-17 03:47:26'),
(3, 3, 'Terms & Conditions', '<p>Welcome to Fresh Non Veg. By using our website and placing orders, you agree to these terms.</p><p>We supply fresh non-vegetarian products subject to availability. Prices and offers may change without prior notice.</p>', 'Terms & Conditions | FreshNonVeg', 'Welcome to Fresh Non Veg. By using our website and placing orders, you agree to these terms.We supply fresh non-vegetarian products subject to availability. Prices and offers may change without prior notice.', 1, '2026-10-17 04:36:56', '2026-10-17 04:36:56'),
(4, 4, 'Privacy Policy', '<p>We respect your privacy. Personal information collected during registration or checkout is used only to process orders and improve our service.</p><p>We do not sell your data to third parties.</p>', 'Privacy Policy | FreshNonVeg', 'We respect your privacy. Personal information collected during registration or checkout is used only to process orders and improve our service.We do not sell your data to third parties.', 1, '2026-10-17 04:36:56', '2026-10-17 04:36:56'),
(5, 5, 'Refund Policy', '<p>If you receive damaged or incorrect items, contact us within 24 hours with photos. Eligible cases may receive replacement or refund as per our review.</p><p>Perishable goods must be reported immediately upon delivery.</p>', 'Refund Policy | FreshNonVeg', 'If you receive damaged or incorrect items, contact us within 24 hours with photos. Eligible cases may receive replacement or refund as per our review.Perishable goods must be reported immediately upon delivery.', 1, '2026-10-17 04:36:56', '2026-10-17 04:36:56'),
(6, 6, 'Contact Us', '<p><strong>Fresh Non Veg</strong></p><p>📍 A35, Behind HDFC Bank, Shramik Kunj, Sector 110, Noida 201304</p><p>📞 9717156362</p><p>✉️ eatfreshnonveg@gmail.com</p><p>WhatsApp ya call par order / query ke liye sampark karein.</p>', 'Contact Us | FreshNonVeg', 'Fresh Non Veg📍 A35, Behind HDFC Bank, Shramik Kunj, Sector 110, Noida 201304📞 9717156362✉️ eatfreshnonveg@gmail.comWhatsApp ya call par order / query ke liye sampark karein.', 1, '2026-10-17 04:37:43', '2026-10-17 04:37:43');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `banner_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` longtext DEFAULT NULL,
  `video_path` varchar(255) DEFAULT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_keywords` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `banner_id`, `name`, `description`, `video_path`, `is_featured`, `status`, `meta_title`, `meta_description`, `meta_keywords`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 'Chicken Breast Boneless', '<p>Soft, lean boneless chicken breast — perfect for grills, salads and healthy meals.</p>', NULL, 1, 1, 'Buy Fresh Chicken Breast Boneless Online', 'Premium boneless chicken breast, cleaned and packed fresh.', 'chicken breast, boneless chicken, fresh chicken', '2026-10-17 04:14:35', '2026-10-17 04:14:35'),
(2, 1, NULL, 'Chicken Curry Cut', '<p>Classic curry cut with bone — ideal for home-style chicken curry and biryani.</p>', NULL, 1, 1, 'Chicken Curry Cut Fresh', 'Fresh chicken curry cut pieces for everyday cooking.', 'chicken curry cut, desi chicken', '2026-10-17 04:14:39', '2026-10-17 04:14:39'),
(3, 1, NULL, 'Chicken Drumsticks', '<p>Juicy drumsticks for fry, tandoori and party snacks.</p>', NULL, 0, 1, 'Fresh Chicken Drumsticks', 'Cleaned chicken drumsticks packed fresh.', 'chicken drumstick, leg piece', '2026-10-17 04:14:42', '2026-10-17 04:14:42'),
(4, 1, NULL, 'Chicken Wings', '<p>Party-favourite wings — great for air-fryer and hot sauce recipes.</p>', NULL, 0, 1, 'Chicken Wings Fresh Cut', 'Fresh chicken wings for snacks and starters.', 'chicken wings, snack chicken', '2026-10-17 04:14:45', '2026-10-17 04:14:45'),
(5, 2, NULL, 'Mutton Curry Cut', '<p>Tender mutton curry cut with bone — slow-cook for rich gravies.</p>', NULL, 1, 1, 'Fresh Mutton Curry Cut', 'Goat mutton curry cut, cleaned and fresh.', 'mutton curry cut, goat meat', '2026-10-17 04:14:47', '2026-10-17 04:14:47'),
(6, 2, NULL, 'Mutton Leg Pieces', '<p>Leg cuts for roasts, paya-style dishes and special weekend meals.</p>', NULL, 0, 1, 'Mutton Leg Pieces Online', 'Fresh mutton leg pieces for traditional recipes.', 'mutton leg, raan cut', '2026-10-17 04:14:51', '2026-10-17 04:14:51'),
(7, 3, NULL, 'Rohu Fish Fillet', '<p>Clean rohu fillets — less bone, quick weekday fish curry.</p>', NULL, 1, 1, 'Fresh Rohu Fish Fillet', 'Rohu fish fillet cuts, cleaned for cooking.', 'rohu fish, fish fillet', '2026-10-17 04:14:54', '2026-10-17 04:14:54'),
(8, 3, NULL, 'Basa Fish Steak', '<p>Mild basa steaks — pan-fry friendly, kid-friendly taste.</p>', NULL, 0, 1, 'Basa Fish Steak Fresh', 'Boneless basa fish steak portions.', 'basa fish, fish steak', '2026-10-17 04:14:59', '2026-10-17 04:14:59'),
(9, 3, NULL, 'Pomfret Whole Cleaned', '<p>Whole pomfret cleaned and scaled — fry or tawa masala ready.</p>', NULL, 0, 1, 'Pomfret Fish Whole Cleaned', 'Fresh pomfret fish cleaned for home cooking.', 'pomfret fish, whole fish', '2026-10-17 04:15:01', '2026-10-17 04:15:01'),
(10, 4, NULL, 'Jumbo Prawns', '<p>Large jumbo prawns — butter garlic, grill or curry.</p>', NULL, 1, 1, 'Jumbo Prawns Fresh', 'Fresh jumbo prawns with shell on.', 'jumbo prawns, seafood', '2026-10-17 04:15:04', '2026-10-17 04:15:04'),
(11, 4, NULL, 'Peeled & Deveined Prawns', '<p>Ready-to-cook peeled prawns — saves prep time in the kitchen.</p>', NULL, 0, 1, 'Peeled Deveined Prawns', 'Clean peeled and deveined prawns.', 'peeled prawns, deveined prawns', '2026-10-17 04:15:09', '2026-10-17 04:15:09'),
(12, 5, NULL, 'Fresh Blue Crab', '<p>Live-fresh blue crab — perfect for coastal-style masala crab.</p>', NULL, 0, 1, 'Fresh Blue Crab', 'Whole blue crab cleaned for cooking.', 'blue crab, crab seafood', '2026-10-17 04:15:10', '2026-10-17 04:15:10'),
(13, 6, NULL, 'Lobster Tail', '<p>Premium lobster tail portions — grill, bake or butter garlic.</p>', NULL, 1, 1, 'Lobster Tail Fresh', 'Fresh lobster tail cuts for gourmet meals.', 'lobster tail, seafood premium', '2026-10-17 04:15:15', '2026-10-17 04:15:15'),
(14, 7, NULL, 'Duck Breast Fillet', '<p>Rich duck breast fillet — restaurant-style pan sear at home.</p>', NULL, 0, 1, 'Duck Breast Fillet Fresh', 'Boneless duck breast fillet portions.', 'duck breast, duck meat', '2026-10-17 04:15:21', '2026-10-17 04:15:21'),
(15, NULL, NULL, 'Turkey Breast Slice', '<p>Lean turkey breast slices — sandwiches, salads and high-protein meals.</p>', NULL, 0, 1, 'Turkey Breast Slice Fresh', 'Fresh turkey breast slices packed hygienically.', 'turkey breast, lean meat', '2026-10-17 04:15:26', '2026-10-17 04:15:26'),
(16, 9, NULL, 'Farm Fresh Eggs (12 Pcs)', '<p>12 farm fresh eggs — daily breakfast and baking essential.</p>', NULL, 1, 1, 'Farm Fresh Eggs 12 Pack', 'Grade-A farm fresh eggs tray of 12.', 'fresh eggs, farm eggs', '2026-10-17 04:15:29', '2026-10-17 04:15:29'),
(17, NULL, NULL, 'Chicken Sausages Pack', '<p>Juicy chicken sausages — pan fry, hot dogs and quick snacks.</p>', NULL, 0, 1, 'Chicken Sausages Pack', 'Ready chicken sausages pack for quick meals.', 'chicken sausages, cold cuts', '2026-10-17 04:15:32', '2026-10-17 04:15:32'),
(18, NULL, NULL, 'Premium Mutton Keema', '<p>Fine minced mutton keema — samosa, keema matar and paratha stuffing.</p>', NULL, 1, 1, 'Premium Mutton Keema', 'Fresh minced mutton keema, double cleaned.', 'mutton keema, minced mutton', '2026-10-17 04:15:35', '2026-10-17 04:15:35'),
(19, NULL, NULL, 'Tandoori Chicken Marinated', '<p>Secret-spice tandoori marinade — oven, air fryer or tawa ready.</p>', NULL, 1, 1, 'Tandoori Chicken Marinated Ready to Cook', 'Marinated tandoori chicken ready for cooking.', 'tandoori chicken, marinated chicken', '2026-10-17 04:15:38', '2026-10-17 04:15:38'),
(20, NULL, NULL, 'Fish Fry Marinated Pack', '<p>Coastal-style fish fry marinade — shallow fry in minutes.</p>', NULL, 0, 1, 'Fish Fry Marinated Pack', 'Ready to cook marinated fish fry pack.', 'fish fry, marinated fish', '2026-10-17 04:15:40', '2026-10-17 04:15:40');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `path` varchar(255) NOT NULL,
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `path`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 1, 'products/chicken-breast-boneless/1.jpg', 0, '2026-10-17 04:14:35', '2026-10-17 04:14:35'),
(2, 1, 'products/chicken-breast-boneless/2.jpg', 1, '2026-10-17 04:14:36', '2026-10-17 04:14:36'),
(3, 1, 'products/chicken-breast-boneless/3.jpg', 2, '2026-10-17 04:14:37', '2026-10-17 04:14:37'),
(4, 1, 'products/chicken-breast-boneless/4.jpg', 3, '2026-10-17 04:14:38', '2026-10-17 04:14:38'),
(5, 1, 'products/chicken-breast-boneless/5.jpg', 4, '2026-10-17 04:14:39', '2026-10-17 04:14:39'),
(6, 2, 'products/chicken-curry-cut/1.jpg', 0, '2026-10-17 04:14:40', '2026-10-17 04:14:40'),
(7, 2, 'products/chicken-curry-cut/2.jpg', 1, '2026-10-17 04:14:40', '2026-10-17 04:14:40'),
(8, 2, 'products/chicken-curry-cut/3.jpg', 2, '2026-10-17 04:14:41', '2026-10-17 04:14:41'),
(9, 2, 'products/chicken-curry-cut/4.jpg', 3, '2026-10-17 04:14:41', '2026-10-17 04:14:41'),
(10, 2, 'products/chicken-curry-cut/5.jpg', 4, '2026-10-17 04:14:42', '2026-10-17 04:14:42'),
(11, 3, 'products/chicken-drumsticks/1.jpg', 0, '2026-10-17 04:14:42', '2026-10-17 04:14:42'),
(12, 3, 'products/chicken-drumsticks/2.jpg', 1, '2026-10-17 04:14:43', '2026-10-17 04:14:43'),
(13, 3, 'products/chicken-drumsticks/3.jpg', 2, '2026-10-17 04:14:43', '2026-10-17 04:14:43'),
(14, 3, 'products/chicken-drumsticks/4.jpg', 3, '2026-10-17 04:14:44', '2026-10-17 04:14:44'),
(15, 3, 'products/chicken-drumsticks/5.jpg', 4, '2026-10-17 04:14:44', '2026-10-17 04:14:44'),
(16, 4, 'products/chicken-wings/1.jpg', 0, '2026-10-17 04:14:45', '2026-10-17 04:14:45'),
(17, 4, 'products/chicken-wings/2.jpg', 1, '2026-10-17 04:14:45', '2026-10-17 04:14:45'),
(18, 4, 'products/chicken-wings/3.jpg', 2, '2026-10-17 04:14:46', '2026-10-17 04:14:46'),
(19, 4, 'products/chicken-wings/4.jpg', 3, '2026-10-17 04:14:47', '2026-10-17 04:14:47'),
(20, 4, 'products/chicken-wings/5.jpg', 4, '2026-10-17 04:14:47', '2026-10-17 04:14:47'),
(21, 5, 'products/mutton-curry-cut/1.jpg', 0, '2026-10-17 04:14:48', '2026-10-17 04:14:48'),
(22, 5, 'products/mutton-curry-cut/2.jpg', 1, '2026-10-17 04:14:49', '2026-10-17 04:14:49'),
(23, 5, 'products/mutton-curry-cut/3.jpg', 2, '2026-10-17 04:14:50', '2026-10-17 04:14:50'),
(24, 5, 'products/mutton-curry-cut/4.jpg', 3, '2026-10-17 04:14:51', '2026-10-17 04:14:51'),
(25, 5, 'products/mutton-curry-cut/5.jpg', 4, '2026-10-17 04:14:51', '2026-10-17 04:14:51'),
(26, 6, 'products/mutton-leg-pieces/1.jpg', 0, '2026-10-17 04:14:52', '2026-10-17 04:14:52'),
(27, 6, 'products/mutton-leg-pieces/2.jpg', 1, '2026-10-17 04:14:52', '2026-10-17 04:14:52'),
(28, 6, 'products/mutton-leg-pieces/3.jpg', 2, '2026-10-17 04:14:53', '2026-10-17 04:14:53'),
(29, 6, 'products/mutton-leg-pieces/4.jpg', 3, '2026-10-17 04:14:54', '2026-10-17 04:14:54'),
(30, 6, 'products/mutton-leg-pieces/5.jpg', 4, '2026-10-17 04:14:54', '2026-10-17 04:14:54'),
(31, 7, 'products/rohu-fish-fillet/1.jpg', 0, '2026-10-17 04:14:55', '2026-10-17 04:14:55'),
(32, 7, 'products/rohu-fish-fillet/2.jpg', 1, '2026-10-17 04:14:57', '2026-10-17 04:14:57'),
(33, 7, 'products/rohu-fish-fillet/3.jpg', 2, '2026-10-17 04:14:57', '2026-10-17 04:14:57'),
(34, 7, 'products/rohu-fish-fillet/4.jpg', 3, '2026-10-17 04:14:58', '2026-10-17 04:14:58'),
(35, 7, 'products/rohu-fish-fillet/5.jpg', 4, '2026-10-17 04:14:59', '2026-10-17 04:14:59'),
(36, 8, 'products/basa-fish-steak/1.jpg', 0, '2026-10-17 04:14:59', '2026-10-17 04:14:59'),
(37, 8, 'products/basa-fish-steak/2.jpg', 1, '2026-10-17 04:15:00', '2026-10-17 04:15:00'),
(38, 8, 'products/basa-fish-steak/3.jpg', 2, '2026-10-17 04:15:00', '2026-10-17 04:15:00'),
(39, 8, 'products/basa-fish-steak/4.jpg', 3, '2026-10-17 04:15:01', '2026-10-17 04:15:01'),
(40, 8, 'products/basa-fish-steak/5.jpg', 4, '2026-10-17 04:15:01', '2026-10-17 04:15:01'),
(41, 9, 'products/pomfret-whole-cleaned/1.jpg', 0, '2026-10-17 04:15:02', '2026-10-17 04:15:02'),
(42, 9, 'products/pomfret-whole-cleaned/2.jpg', 1, '2026-10-17 04:15:02', '2026-10-17 04:15:02'),
(43, 9, 'products/pomfret-whole-cleaned/3.jpg', 2, '2026-10-17 04:15:03', '2026-10-17 04:15:03'),
(44, 9, 'products/pomfret-whole-cleaned/4.jpg', 3, '2026-10-17 04:15:03', '2026-10-17 04:15:03'),
(45, 9, 'products/pomfret-whole-cleaned/5.jpg', 4, '2026-10-17 04:15:04', '2026-10-17 04:15:04'),
(46, 10, 'products/jumbo-prawns/1.jpg', 0, '2026-10-17 04:15:04', '2026-10-17 04:15:04'),
(47, 10, 'products/jumbo-prawns/2.jpg', 1, '2026-10-17 04:15:05', '2026-10-17 04:15:05'),
(48, 10, 'products/jumbo-prawns/3.jpg', 2, '2026-10-17 04:15:07', '2026-10-17 04:15:07'),
(49, 10, 'products/jumbo-prawns/4.jpg', 3, '2026-10-17 04:15:08', '2026-10-17 04:15:08'),
(50, 10, 'products/jumbo-prawns/5.jpg', 4, '2026-10-17 04:15:09', '2026-10-17 04:15:09'),
(51, 11, 'products/peeled-deveined-prawns/1.jpg', 0, '2026-10-17 04:15:09', '2026-10-17 04:15:09'),
(52, 11, 'products/peeled-deveined-prawns/2.jpg', 1, '2026-10-17 04:15:09', '2026-10-17 04:15:09'),
(53, 11, 'products/peeled-deveined-prawns/3.jpg', 2, '2026-10-17 04:15:10', '2026-10-17 04:15:10'),
(54, 11, 'products/peeled-deveined-prawns/4.jpg', 3, '2026-10-17 04:15:10', '2026-10-17 04:15:10'),
(55, 11, 'products/peeled-deveined-prawns/5.jpg', 4, '2026-10-17 04:15:10', '2026-10-17 04:15:10'),
(56, 12, 'products/fresh-blue-crab/1.jpg', 0, '2026-10-17 04:15:11', '2026-10-17 04:15:11'),
(57, 12, 'products/fresh-blue-crab/2.jpg', 1, '2026-10-17 04:15:12', '2026-10-17 04:15:12'),
(58, 12, 'products/fresh-blue-crab/3.jpg', 2, '2026-10-17 04:15:13', '2026-10-17 04:15:13'),
(59, 12, 'products/fresh-blue-crab/4.jpg', 3, '2026-10-17 04:15:14', '2026-10-17 04:15:14'),
(60, 12, 'products/fresh-blue-crab/5.jpg', 4, '2026-10-17 04:15:15', '2026-10-17 04:15:15'),
(61, 13, 'products/lobster-tail/1.jpg', 0, '2026-10-17 04:15:15', '2026-10-17 04:15:15'),
(62, 13, 'products/lobster-tail/2.jpg', 1, '2026-10-17 04:15:16', '2026-10-17 04:15:16'),
(63, 13, 'products/lobster-tail/3.jpg', 2, '2026-10-17 04:15:20', '2026-10-17 04:15:20'),
(64, 13, 'products/lobster-tail/4.jpg', 3, '2026-10-17 04:15:21', '2026-10-17 04:15:21'),
(65, 13, 'products/lobster-tail/5.jpg', 4, '2026-10-17 04:15:21', '2026-10-17 04:15:21'),
(66, 14, 'products/duck-breast-fillet/1.jpg', 0, '2026-10-17 04:15:22', '2026-10-17 04:15:22'),
(67, 14, 'products/duck-breast-fillet/2.jpg', 1, '2026-10-17 04:15:23', '2026-10-17 04:15:23'),
(68, 14, 'products/duck-breast-fillet/3.jpg', 2, '2026-10-17 04:15:24', '2026-10-17 04:15:24'),
(69, 14, 'products/duck-breast-fillet/4.jpg', 3, '2026-10-17 04:15:25', '2026-10-17 04:15:25'),
(70, 14, 'products/duck-breast-fillet/5.jpg', 4, '2026-10-17 04:15:26', '2026-10-17 04:15:26'),
(71, 15, 'products/turkey-breast-slice/1.jpg', 0, '2026-10-17 04:15:27', '2026-10-17 04:15:27'),
(72, 15, 'products/turkey-breast-slice/2.jpg', 1, '2026-10-17 04:15:28', '2026-10-17 04:15:28'),
(73, 15, 'products/turkey-breast-slice/3.jpg', 2, '2026-10-17 04:15:28', '2026-10-17 04:15:28'),
(74, 15, 'products/turkey-breast-slice/4.jpg', 3, '2026-10-17 04:15:29', '2026-10-17 04:15:29'),
(75, 15, 'products/turkey-breast-slice/5.jpg', 4, '2026-10-17 04:15:29', '2026-10-17 04:15:29'),
(76, 16, 'products/farm-fresh-eggs-12/1.jpg', 0, '2026-10-17 04:15:29', '2026-10-17 04:15:29'),
(77, 16, 'products/farm-fresh-eggs-12/2.jpg', 1, '2026-10-17 04:15:30', '2026-10-17 04:15:30'),
(78, 16, 'products/farm-fresh-eggs-12/3.jpg', 2, '2026-10-17 04:15:31', '2026-10-17 04:15:31'),
(79, 16, 'products/farm-fresh-eggs-12/4.jpg', 3, '2026-10-17 04:15:32', '2026-10-17 04:15:32'),
(80, 16, 'products/farm-fresh-eggs-12/5.jpg', 4, '2026-10-17 04:15:32', '2026-10-17 04:15:32'),
(81, 17, 'products/chicken-sausages-pack/1.jpg', 0, '2026-10-17 04:15:33', '2026-10-17 04:15:33'),
(82, 17, 'products/chicken-sausages-pack/2.jpg', 1, '2026-10-17 04:15:34', '2026-10-17 04:15:34'),
(83, 17, 'products/chicken-sausages-pack/3.jpg', 2, '2026-10-17 04:15:34', '2026-10-17 04:15:34'),
(84, 17, 'products/chicken-sausages-pack/4.jpg', 3, '2026-10-17 04:15:35', '2026-10-17 04:15:35'),
(85, 17, 'products/chicken-sausages-pack/5.jpg', 4, '2026-10-17 04:15:35', '2026-10-17 04:15:35'),
(86, 18, 'products/premium-mutton-keema/1.jpg', 0, '2026-10-17 04:15:36', '2026-10-17 04:15:36'),
(87, 18, 'products/premium-mutton-keema/2.jpg', 1, '2026-10-17 04:15:37', '2026-10-17 04:15:37'),
(88, 18, 'products/premium-mutton-keema/3.jpg', 2, '2026-10-17 04:15:37', '2026-10-17 04:15:37'),
(89, 18, 'products/premium-mutton-keema/4.jpg', 3, '2026-10-17 04:15:38', '2026-10-17 04:15:38'),
(90, 18, 'products/premium-mutton-keema/5.jpg', 4, '2026-10-17 04:15:38', '2026-10-17 04:15:38'),
(91, 19, 'products/tandoori-chicken-marinated/1.jpg', 0, '2026-10-17 04:15:39', '2026-10-17 04:15:39'),
(92, 19, 'products/tandoori-chicken-marinated/2.jpg', 1, '2026-10-17 04:15:39', '2026-10-17 04:15:39'),
(93, 19, 'products/tandoori-chicken-marinated/3.jpg', 2, '2026-10-17 04:15:39', '2026-10-17 04:15:39'),
(94, 19, 'products/tandoori-chicken-marinated/4.jpg', 3, '2026-10-17 04:15:40', '2026-10-17 04:15:40'),
(95, 19, 'products/tandoori-chicken-marinated/5.jpg', 4, '2026-10-17 04:15:40', '2026-10-17 04:15:40'),
(96, 20, 'products/fish-fry-marinated-pack/1.jpg', 0, '2026-10-17 04:15:41', '2026-10-17 04:15:41'),
(97, 20, 'products/fish-fry-marinated-pack/2.jpg', 1, '2026-10-17 04:15:41', '2026-10-17 04:15:41'),
(98, 20, 'products/fish-fry-marinated-pack/3.jpg', 2, '2026-10-17 04:15:41', '2026-10-17 04:15:41'),
(99, 20, 'products/fish-fry-marinated-pack/4.jpg', 3, '2026-10-17 04:15:42', '2026-10-17 04:15:42'),
(100, 20, 'products/fish-fry-marinated-pack/5.jpg', 4, '2026-10-17 04:15:42', '2026-10-17 04:15:42');

-- --------------------------------------------------------

--
-- Table structure for table `product_reviews`
--

CREATE TABLE `product_reviews` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED DEFAULT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL,
  `review` text DEFAULT NULL,
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_tag`
--

CREATE TABLE `product_tag` (
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `tag_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_tag`
--

INSERT INTO `product_tag` (`product_id`, `tag_id`) VALUES
(1, 1),
(1, 2),
(1, 6),
(2, 1),
(2, 2),
(3, 2),
(3, 4),
(4, 2),
(4, 5),
(5, 1),
(5, 2),
(5, 6),
(6, 2),
(6, 6),
(7, 1),
(7, 2),
(8, 2),
(8, 4),
(9, 2),
(9, 6),
(10, 1),
(10, 2),
(10, 3),
(11, 2),
(11, 6),
(12, 2),
(12, 3),
(13, 1),
(13, 2),
(13, 6),
(14, 2),
(14, 6),
(15, 2),
(15, 6),
(16, 1),
(16, 2),
(17, 2),
(17, 5),
(18, 1),
(18, 2),
(18, 6),
(19, 1),
(19, 2),
(19, 4),
(20, 2),
(20, 5);

-- --------------------------------------------------------

--
-- Table structure for table `product_weights`
--

CREATE TABLE `product_weights` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `weight_label` varchar(80) NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_weights`
--

INSERT INTO `product_weights` (`id`, `product_id`, `weight_label`, `price`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 1, '500 g', 189.00, 0, '2026-10-17 04:14:35', '2026-10-17 04:14:35'),
(2, 1, '1 kg', 359.00, 1, '2026-10-17 04:14:35', '2026-10-17 04:14:35'),
(3, 2, '500 g', 129.00, 0, '2026-10-17 04:14:39', '2026-10-17 04:14:39'),
(4, 2, '1 kg', 249.00, 1, '2026-10-17 04:14:39', '2026-10-17 04:14:39'),
(5, 3, '500 g', 149.00, 0, '2026-10-17 04:14:42', '2026-10-17 04:14:42'),
(6, 3, '1 kg', 279.00, 1, '2026-10-17 04:14:42', '2026-10-17 04:14:42'),
(7, 4, '500 g', 169.00, 0, '2026-10-17 04:14:45', '2026-10-17 04:14:45'),
(8, 4, '1 kg', 319.00, 1, '2026-10-17 04:14:45', '2026-10-17 04:14:45'),
(9, 5, '500 g', 449.00, 0, '2026-10-17 04:14:47', '2026-10-17 04:14:47'),
(10, 5, '1 kg', 879.00, 1, '2026-10-17 04:14:47', '2026-10-17 04:14:47'),
(11, 6, '500 g', 499.00, 0, '2026-10-17 04:14:51', '2026-10-17 04:14:51'),
(12, 6, '1 kg', 969.00, 1, '2026-10-17 04:14:51', '2026-10-17 04:14:51'),
(13, 7, '500 g', 279.00, 0, '2026-10-17 04:14:54', '2026-10-17 04:14:54'),
(14, 7, '1 kg', 539.00, 1, '2026-10-17 04:14:54', '2026-10-17 04:14:54'),
(15, 8, '500 g', 299.00, 0, '2026-10-17 04:14:59', '2026-10-17 04:14:59'),
(16, 8, '1 kg', 569.00, 1, '2026-10-17 04:14:59', '2026-10-17 04:14:59'),
(17, 9, '500 g', 449.00, 0, '2026-10-17 04:15:01', '2026-10-17 04:15:01'),
(18, 9, '1 kg', 869.00, 1, '2026-10-17 04:15:01', '2026-10-17 04:15:01'),
(19, 10, '250 g', 349.00, 0, '2026-10-17 04:15:04', '2026-10-17 04:15:04'),
(20, 10, '500 g', 679.00, 1, '2026-10-17 04:15:04', '2026-10-17 04:15:04'),
(21, 11, '250 g', 399.00, 0, '2026-10-17 04:15:09', '2026-10-17 04:15:09'),
(22, 11, '500 g', 769.00, 1, '2026-10-17 04:15:09', '2026-10-17 04:15:09'),
(23, 12, '500 g', 499.00, 0, '2026-10-17 04:15:11', '2026-10-17 04:15:11'),
(24, 12, '1 kg', 949.00, 1, '2026-10-17 04:15:11', '2026-10-17 04:15:11'),
(25, 13, '250 g', 899.00, 0, '2026-10-17 04:15:15', '2026-10-17 04:15:15'),
(26, 13, '500 g', 1749.00, 1, '2026-10-17 04:15:15', '2026-10-17 04:15:15'),
(27, 14, '400 g', 549.00, 0, '2026-10-17 04:15:21', '2026-10-17 04:15:21'),
(28, 14, '800 g', 1049.00, 1, '2026-10-17 04:15:21', '2026-10-17 04:15:21'),
(29, 15, '500 g', 429.00, 0, '2026-10-17 04:15:26', '2026-10-17 04:15:26'),
(30, 15, '1 kg', 829.00, 1, '2026-10-17 04:15:26', '2026-10-17 04:15:26'),
(31, 16, '12 pcs', 89.00, 0, '2026-10-17 04:15:29', '2026-10-17 04:15:29'),
(32, 16, '30 pcs', 219.00, 1, '2026-10-17 04:15:29', '2026-10-17 04:15:29'),
(33, 17, '250 g', 149.00, 0, '2026-10-17 04:15:32', '2026-10-17 04:15:32'),
(34, 17, '500 g', 279.00, 1, '2026-10-17 04:15:32', '2026-10-17 04:15:32'),
(35, 18, '500 g', 399.00, 0, '2026-10-17 04:15:35', '2026-10-17 04:15:35'),
(36, 18, '1 kg', 779.00, 1, '2026-10-17 04:15:35', '2026-10-17 04:15:35'),
(37, 19, '500 g', 1.00, 0, '2026-10-17 04:15:38', '2026-10-21 04:57:14'),
(38, 19, '1 kg', 1.00, 1, '2026-10-17 04:15:38', '2026-10-21 04:57:14'),
(39, 20, '500 g', 299.00, 0, '2026-10-17 04:15:40', '2026-10-17 04:15:40'),
(40, 20, '1 kg', 569.00, 1, '2026-10-17 04:15:40', '2026-10-17 04:15:40');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('5sxHSs2IgCu2ZQJK3XvuqqwnctRdUj0mDQOhc0V2', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.9711', 'ZXlKcGRpSTZJbm81VldKQlZtZHZhRzV4VlhwTWVXOTBUWE5WSzJjOVBTSXNJblpoYkhWbElqb2lRWGNyT1RoVGQwTkJhWEppZVVGWFJHMXpNV0U0YmpKR1JYUklVRk1yU0RWeVQwVkNXRTlzTlhVMWFUQTRhM0EyTUZGM1RWSjBWa05EVFhSMkx6SjNVSHB2YUVSVU0yWlZZakZTY2twWUswb3lSa1U1VUdWNFFVdG1ORmhCUTFSU1kxTllTa1ZUTTB4UFQyaGhWMHN5WjNaR2NqaDJabUZNTkd3NWMyMVJaRGx6Yld4VFZHaFRlbVZyUlUxalpHeHlOa1ZTWlhoRE9FUnJkMmRTUkZwdmNYRlNXV3N4Vlc1RmJrSllWR2R0YUcxVmNuVm5lamhtWkRKWU5td3ZXamRxYVhZMlVtbGtUalZSU0RFMksyeDBkVzFqT0VwMUx6Y3JRVUZMT1N0RWJra3JRamMyUkRKUE5GWk5TVDBpTENKdFlXTWlPaUl3WTJKbU4yUm1ZalZtWXpVMU9XUTBaak14WVRFMFptWmpOamhpWmpJNVpUQTVNMlk0TWpjNU1EazVOV0UyTW1JeFpUQmpOR1UzT1Rkak4yTmxZVGN6SWl3aWRHRm5Jam9pSW4wPQ==', 1792587110),
('5TjXa9h1azW6vrRjeNH6484XfN6FKEye9JCTMwmz', NULL, '127.0.0.1', 'Symfony', 'eyJsb2dpbl9hZG1pbl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjoxLCJfdG9rZW4iOiJjbDg0Vkt6Nm1XemJBbERpVFF3TjlOR3Rhck9BTVlYSzNWd3dlU3lyIiwicGFzc3dvcmRfaGFzaF9hZG1pbiI6IjkwMjQ0M2RjNzQ4NzJlYThmNzdmZTA5MDgxYzhmNjJkNGVhZWJjM2U4ZGZlMWViYTA4YTUwMjg3MWU0MGQ3ZGUiLCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvbG9jYWxob3N0OjgwMDBcL0ZyZXNoTm9uVmVnQWRtaW5cL2Jhbm5lcnNcL2NyZWF0ZSIsInJvdXRlIjoiYWRtaW4uYmFubmVycy5jcmVhdGUifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1792580624),
('5xindSQbekn4lFQQ090rLqRoUqutAgJHtu2QPYk8', 1, '127.0.0.1', 'Symfony', 'eyJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6MSwiX3Rva2VuIjoiVmd1V0JLUVBrazdwcFdVQWR2Ym5ZOG53ZEpCMVN5d2xzMEs2TE1lbyIsInBhc3N3b3JkX2hhc2hfd2ViIjoiYTNlZDIzZjBmNzI0YWNiZTdkNGEzNmI1NjNiYzc0ZTAyNTNlODgyNDAyODNmYjcwMWQyNWM2MjQ1M2NhYzk1YSIsIl9wcmV2aW91cyI6eyJ1cmwiOiJodHRwOlwvXC9sb2NhbGhvc3RcL2Rhc2hib2FyZFwvb3JkZXJzIiwicm91dGUiOiJkYXNoYm9hcmQub3JkZXJzIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=', 1792581578),
('8k7JdEVB4OGajoXer2mXebzomqYu49fpOxCslMZv', NULL, '127.0.0.1', 'Symfony', 'eyJfdG9rZW4iOiJwTllQeGZ0WUdQSnhvTXplajlodkxKZFRJWkFMQmlkaHhlRWVTNkdiIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdFwvRnJlc2hOb25WZWdBZG1pblwvZGFzaGJvYXJkIiwicm91dGUiOiJhZG1pbi5kYXNoYm9hcmQifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJsb2dpbl9hZG1pbl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjoxLCJwYXNzd29yZF9oYXNoX2FkbWluIjoiOTAyNDQzZGM3NDg3MmVhOGY3N2ZlMDkwODFjOGY2MmQ0ZWFlYmMzZThkZmUxZWJhMDhhNTAyODcxZTQwZDdkZSJ9', 1792581824),
('9lcXFNWs32BcT6Eg4DR8nxFrvuoxYnEfpfwONdMN', NULL, '127.0.0.1', 'curl/8.21.0', 'eyJfdG9rZW4iOiJtZHdMajlXNnRTU2hYMmhyV2duQ0UzOWNWTWZXUXVXaElVcWVTYnR4IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDAwXC9GcmVzaE5vblZlZ0FkbWluXC9kYXNoYm9hcmQiLCJyb3V0ZSI6ImFkbWluLmRhc2hib2FyZCJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1792585052),
('A4RIXwHKkhH46T4fJaLttlCHGB8hvqoYR7ydnRsQ', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.14.27 Chrome/144.0.7559.236 Electron/40.10.3 Safari/537.36', 'ZXlKcGRpSTZJakZ0VlhSTUwwdDZRM3BrVldSek5IZHRRa3hTYUdjOVBTSXNJblpoYkhWbElqb2lSWE14Y1VVd1dETmhRakZpUVdReFdTOWhlVWxWY1RGdFdtaHFSakJ6UkhkVFRFbDBORGQwTDJGYU5XZGtNVkpQVm1kWmRWVkNWa0p1ZUVkQlZtTXlablExV0cxVWIxSkdiR05UUlhWTVVGUXlZMll4TVhkMWFYbFJRaTgwWkVwSFdFMTZhV2huYTAxQllqWnFOekl6Y3psakt6WlpPVlZzTjA1R1kxUjRXaXRwVWpaRWMxUkpSbEV3VTJ4VFQwbG9kVTgxYVc0ellsVnFRbUYwVGtWb0x6VjRSMXBRYVd0YWEydE5kREJrUlU1aWRrWlBjbEpYUTJONU9XOXpjalJHTnpoV1N6aHNUakV3Wm5GbU9URXJNV0pTZVdsV2R6MDlJaXdpYldGaklqb2lZVEJrTldNd01EaGxZamxqTUdKbE1ERmxOVGRrWTJRd05EYzVPR1JqTW1Vd01EaG1PREZqTURBNVlUazRaR0ZtTnpVM05EQTFaR1EwWmpNeFl6QTBPQ0lzSW5SaFp5STZJaUo5', 1792587089),
('acGnfQxvTtKwq3lVn7Z3Rz4CIVUuZTShLipcesr7', NULL, '127.0.0.1', 'Symfony', 'eyJsb2dpbl9hZG1pbl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjoxLCJfdG9rZW4iOiJTU1VVeU1rT2xnRmtmUDV0R3E4cW9lWUFPYjhzOEFkblFldDZZN015IiwicGFzc3dvcmRfaGFzaF9hZG1pbiI6IjkwMjQ0M2RjNzQ4NzJlYThmNzdmZTA5MDgxYzhmNjJkNGVhZWJjM2U4ZGZlMWViYTA4YTUwMjg3MWU0MGQ3ZGUiLCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvbG9jYWxob3N0XC9GcmVzaE5vblZlZ0FkbWluXC91c2Vyc1wvMSIsInJvdXRlIjoiYWRtaW4udXNlcnMuc2hvdyJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1792582643),
('ANHLfZBIKza1H7IdbJwWkxU2hb8AxobaopGGpnH5', 1, '127.0.0.1', 'Symfony', 'eyJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6MSwiX3Rva2VuIjoibWpldThseEgwSVBhMGlMd0Fhc2lVblpXNDRpTlhvd1J3OXV5anlrQSIsInBhc3N3b3JkX2hhc2hfd2ViIjoiYTNlZDIzZjBmNzI0YWNiZTdkNGEzNmI1NjNiYzc0ZTAyNTNlODgyNDAyODNmYjcwMWQyNWM2MjQ1M2NhYzk1YSIsIl9wcmV2aW91cyI6eyJ1cmwiOiJodHRwOlwvXC9sb2NhbGhvc3RcL2Rhc2hib2FyZCIsInJvdXRlIjoiZGFzaGJvYXJkIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=', 1792580478),
('bdrPWXCoSN1hvHwWNytmTcppYdAwYweS766Lony8', NULL, '127.0.0.1', 'Symfony', 'eyJsb2dpbl9hZG1pbl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjoxLCJfdG9rZW4iOiJPMDZWUzFjWkc2ZWJZSnB0dVhBZU03NUFuUU1tV2lmcjBaaFJ6SWRsIiwicGFzc3dvcmRfaGFzaF9hZG1pbiI6IjkwMjQ0M2RjNzQ4NzJlYThmNzdmZTA5MDgxYzhmNjJkNGVhZWJjM2U4ZGZlMWViYTA4YTUwMjg3MWU0MGQ3ZGUiLCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvbG9jYWxob3N0OjgwMDBcL0ZyZXNoTm9uVmVnQWRtaW5cL29yZGVyc1wvNVwvYmlsbC5wZGYiLCJyb3V0ZSI6ImFkbWluLm9yZGVycy5iaWxsLnBkZiJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1792584277),
('BEBVf6ENOOgpWjO0OMHImOGZfw8Lz5ovcbTnQnsx', NULL, '127.0.0.1', 'Symfony', 'ZXlKcGRpSTZJbU5QY0RCT1IydGtZVmRuWWxKTVIycHpOM1pxTUVFOVBTSXNJblpoYkhWbElqb2lUVUZPWkRFd1puaDBhMlJEZDNkck9WTjNSVWg2V0ZaeFFuZDNSVWhDTVhkMWFHcG9ZMGRTTDNacVdXOUtRMFJDVkU0cmMweEJNbWRXVGpOSFZXTXpjbWRIZWtsUFl6RjFObTlYUTNOQmVua3ZkM0JWVWxoc1EyMDFPSGR3WWxBNWMydG1lVTVGTUdoeFdGUmFOREJwVG5SSVVYSldNa2x2TWtadU0zZFZReTlsZDNRelJtdE9NRFF6TkVNME1rVTNjbFJ2WjBSWVVsaEtTMWR6VW5Ga2IxSnRVbUZ6VlZOVlpIUnhaMmM1Tm1kbE5HSkRPVVUxY0U5ME4wSkNOVmhHZFc1eVNrVk5aSGRKTUVJeVdpdFNaRUkxTXl0NVVUMDlJaXdpYldGaklqb2lNVEEyT0Rnek1XWTJaamhoWXpnMk5HVTRNV00zTm1Oa016SmhaVFJrT0RnMFlUVXlNRE16TldJM09UaGpaV1ZrWVRCbFlqRmpZMkV4WXpRMU9XTm1OeUlzSW5SaFp5STZJaUo5', 1792586163),
('h1SUWl4mVaQiUjRNnONgg1SiziyrtocJSNM6YAP7', NULL, '127.0.0.1', 'Symfony', 'eyJfdG9rZW4iOiJxbHF0T2pmVTN4WkZ4RW0zWDJRZFRnUTFJTzZnYTNGNFdmR28zZUhiIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdCIsInJvdXRlIjoiaG9tZSJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1792581850),
('J8l1O4guXL0lBW17nAyu249HwjsyDQOusxaLUAhr', NULL, '127.0.0.1', 'Symfony', 'eyJsb2dpbl9hZG1pbl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjoxLCJfdG9rZW4iOiJLa2VsV0JMT1JvTk9KclU5Z2hwa29Hc1Q2djJldW1WR2NmR1h5d09nIiwicGFzc3dvcmRfaGFzaF9hZG1pbiI6IjkwMjQ0M2RjNzQ4NzJlYThmNzdmZTA5MDgxYzhmNjJkNGVhZWJjM2U4ZGZlMWViYTA4YTUwMjg3MWU0MGQ3ZGUiLCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvbG9jYWxob3N0OjgwMDBcL0ZyZXNoTm9uVmVnQWRtaW5cL3VzZXJzXC8xIiwicm91dGUiOiJhZG1pbi51c2Vycy5zaG93In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=', 1792582672),
('kJ7vzkGB0B7KqPclfsib63JjW2IinBFcSaWlTS7z', NULL, '127.0.0.1', 'Symfony', 'eyJsb2dpbl9hZG1pbl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjoxLCJfdG9rZW4iOiJkN1IxS3Z4RWVPclhBbWVEa25aTzAzbEY2dTJNaUtldWlubnplZDdzIiwicGFzc3dvcmRfaGFzaF9hZG1pbiI6IjkwMjQ0M2RjNzQ4NzJlYThmNzdmZTA5MDgxYzhmNjJkNGVhZWJjM2U4ZGZlMWViYTA4YTUwMjg3MWU0MGQ3ZGUiLCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvbG9jYWxob3N0OjgwMDBcL0ZyZXNoTm9uVmVnQWRtaW5cL3Byb2R1Y3QtcmV2aWV3cyIsInJvdXRlIjoiYWRtaW4ucHJvZHVjdC1yZXZpZXdzLmluZGV4In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=', 1792584128),
('MyGCJAB7YabmqO1AB0mC2qtRw2kSFiLzcE7Mgbnq', NULL, '127.0.0.1', 'Symfony', 'eyJsb2dpbl9hZG1pbl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjoxLCJfdG9rZW4iOiJxa1NjWExHRDNOSWF0dzNHNVNGWWhoWXM5WXhTNGI2VFk5NDhsb1NlIiwicGFzc3dvcmRfaGFzaF9hZG1pbiI6IjkwMjQ0M2RjNzQ4NzJlYThmNzdmZTA5MDgxYzhmNjJkNGVhZWJjM2U4ZGZlMWViYTA4YTUwMjg3MWU0MGQ3ZGUiLCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvbG9jYWxob3N0OjgwMDBcL0ZyZXNoTm9uVmVnQWRtaW5cL3dhbGxldCIsInJvdXRlIjoiYWRtaW4ud2FsbGV0LmluZGV4In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=', 1792583994),
('myrQGNYXIo0bjsjdF0hACxrHXoa6XEbdGFI5AqX4', NULL, '127.0.0.1', 'Symfony', 'eyJsb2dpbl9hZG1pbl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjoxLCJfdG9rZW4iOiJtQzhKTXhMY0hSdElWWEdGUUM5SkpNMVZCN0QzUnNIRGhmMHlQRmVqIiwicGFzc3dvcmRfaGFzaF9hZG1pbiI6IjkwMjQ0M2RjNzQ4NzJlYThmNzdmZTA5MDgxYzhmNjJkNGVhZWJjM2U4ZGZlMWViYTA4YTUwMjg3MWU0MGQ3ZGUiLCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvbG9jYWxob3N0OjgwMDBcL0ZyZXNoTm9uVmVnQWRtaW5cL2Rhc2hib2FyZCIsInJvdXRlIjoiYWRtaW4uZGFzaGJvYXJkIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=', 1792584687),
('nptizag7G7wbUERsr7RmRZ5Jyfo7ybmXuJIHD5D4', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJCZ01DYk5lYjJpUGZKeWFuOEtlWG1URXdWQkdENFZ4NlRFNzMyNjZJIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDAwXC9GcmVzaE5vblZlZ0FkbWluXC9kYXNoYm9hcmQiLCJyb3V0ZSI6ImFkbWluLmRhc2hib2FyZCJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX0sImxvZ2luX2FkbWluXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiOjEsInBhc3N3b3JkX2hhc2hfYWRtaW4iOiI5MDI0NDNkYzc0ODcyZWE4Zjc3ZmUwOTA4MWM4ZjYyZDRlYWViYzNlOGRmZTFlYmEwOGE1MDI4NzFlNDBkN2RlIn0=', 1792585609),
('NY6qu0fQP9sGtd4PRA6u1Zf7rGcoITC0M1WhmF0V', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.9711', 'ZXlKcGRpSTZJa2Q0UVd4d2JWUmtkSE5MYjJGQ1NXSkhiSFZaTDJjOVBTSXNJblpoYkhWbElqb2lLM2R2VjNCQ2VYTTRaVmRGWWtwM04xTklWVTlTVlRWMVdWQlZabWhVVEd0c0wwNU9jWEpYTDFoeE9VMVpaekYzTDFWVFdXWjRkRFF2ZFVOemVuRm5WRmhyYUhaT1NXcEZMMU0xYjFGalJEaEhSamRFY1VFeFZqSlRLMjlPTVZOTldESlZiQzg1VDNOV1NXdENlR1o2V1ZwaWVVRmhPVEp5TTNwYVFXRkhZVWw2YUVSNWFraDZOWEJUV0dKeFQzZGFOMjVzZFhOcFVWZDZlamczVTFodmNFMHdNVTlTUmpORWVFUlpaek5vWlVGVFJTdENkRkpLUW10bGNDOHphRmxsY201S2QxUm9ZVmQyUWt0dU4wRldibUpJT1ZCSFVUMDlJaXdpYldGaklqb2lNamMzWVRsaE9UQTRaVE5rTmpJME5tWTNOelZqTVRJd05UazJNak13WXpSbE0yTXlaR1F6WkRZd1lqWmhZbVJpT1dFMVlXTmxPV1V3WldRMU9URTRZaUlzSW5SaFp5STZJaUo5', 1792587109),
('PlbTeYXDMjPDJ8qjCK1NrocDSRpzzs0xyQcLcf61', NULL, '127.0.0.1', 'Symfony', 'eyJsb2dpbl9hZG1pbl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjoxLCJfdG9rZW4iOiJvS0J3aEJiNnBzZzJQQkhjYm81bW5IeHFRUVdiMUVkN0VuemdzUTZBIiwicGFzc3dvcmRfaGFzaF9hZG1pbiI6IjkwMjQ0M2RjNzQ4NzJlYThmNzdmZTA5MDgxYzhmNjJkNGVhZWJjM2U4ZGZlMWViYTA4YTUwMjg3MWU0MGQ3ZGUiLCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvbG9jYWxob3N0OjgwMDBcL0ZyZXNoTm9uVmVnQWRtaW5cL29yZGVyc1wvMyIsInJvdXRlIjoiYWRtaW4ub3JkZXJzLnNob3cifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1792582612),
('Qk69HMhL8YIJpNqotJGVdSJVNjrtD6Qy4oltzr7E', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJTWVVoM2w1QVZLVU9YSVFoU1hHZVpYb2VvVE9BN0ZxUWxZSkV5TnExIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDAwIiwicm91dGUiOiJob21lIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfSwibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiOjEsInBhc3N3b3JkX2hhc2hfd2ViIjoiYTNlZDIzZjBmNzI0YWNiZTdkNGEzNmI1NjNiYzc0ZTAyNTNlODgyNDAyODNmYjcwMWQyNWM2MjQ1M2NhYzk1YSJ9', 1792584522),
('R3fSOUbtq9XI1K56ZLOU2CsFVVIY4e6Vqm7yfGzr', NULL, '127.0.0.1', 'Symfony', 'eyJsb2dpbl9hZG1pbl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjoxLCJfdG9rZW4iOiJ0RVd0c3RBVmg1R2N6YTlEUFB3blFFVUhwWU9qU216QWF3ZXBUMlJ6IiwicGFzc3dvcmRfaGFzaF9hZG1pbiI6IjkwMjQ0M2RjNzQ4NzJlYThmNzdmZTA5MDgxYzhmNjJkNGVhZWJjM2U4ZGZlMWViYTA4YTUwMjg3MWU0MGQ3ZGUiLCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvbG9jYWxob3N0OjgwMDBcL0ZyZXNoTm9uVmVnQWRtaW5cL29yZGVycyIsInJvdXRlIjoiYWRtaW4ub3JkZXJzLmluZGV4In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=', 1792583305),
('SIHGNKNurdrB9XgzdT202qpwvUsIkaaxWa8iWFTj', NULL, '127.0.0.1', 'Symfony', 'eyJsb2dpbl9hZG1pbl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjoxLCJfdG9rZW4iOiJMakEybWtiMVFqRTg2MWY2VDBIdUo1cUdhRTcxbnR3Z0o4elZQYktnIiwicGFzc3dvcmRfaGFzaF9hZG1pbiI6IjkwMjQ0M2RjNzQ4NzJlYThmNzdmZTA5MDgxYzhmNjJkNGVhZWJjM2U4ZGZlMWViYTA4YTUwMjg3MWU0MGQ3ZGUiLCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvbG9jYWxob3N0OjgwMDBcL0ZyZXNoTm9uVmVnQWRtaW5cL2Jhbm5lcnMiLCJyb3V0ZSI6ImFkbWluLmJhbm5lcnMuaW5kZXgifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1792580746),
('snEcE8n2LSX1ZMpcWrQ1avvaU8rxg3wffEqV9Az4', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.14.27 Chrome/144.0.7559.236 Electron/40.10.3 Safari/537.36', 'ZXlKcGRpSTZJa05MZVVoSk5WWlhNa2hSVDFndk9XNVZUV1Z0TlZFOVBTSXNJblpoYkhWbElqb2laemR2ZUZWamFEZGFjemhRVFhoRFZYQmFVbE5OYVZKVldqbGxaVU5YT1ZkQ2RucE5ZMVZXVldVeE5sbHBPV1ZIVkVsYU4xbGtUMlpWV0ZKbk9HRTVTM2t2Tm05eE5XWnVkRGhOTlRFMWJEbDBkV0ZEY0dWdVdrb3pibEZ3TWxZNWFGRkRlbUZPYzJ4YVYwc3pjMGQzYW1JNWVWZEdTbWgwV1dVeWFrSkRNMUZUWlhjNU9XY3ZNblp3VGtwT1NWZHlVMUV5WVhrM2VEbEVabHBqVDJsNlpuZGlOQ3RxY1hKUU1rRndUVzkzTlV4VFEyRTVaVlpJYjFOTGMxZDBkRmt4ZHpNNFVsRnBNRWx6ZDFZM01ETkdUMlJLTnpOVFVUMDlJaXdpYldGaklqb2lNbVF3WW1JME1EQTRaakZsTWpWaVlUSm1OREEzTnpZMllXTTNZalZtTVdObU1qRXpaR0k0T1dabU9UY3paV1ZoT1RFMk16QTNPRFZpTlRWbE9XUXdZaUlzSW5SaFp5STZJaUo5', 1792587089),
('TWWfPrCJlwdbsySFx0JOiSTjomukrcoxgH4mnnIY', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.14.27 Chrome/144.0.7559.236 Electron/40.10.3 Safari/537.36', 'ZXlKcGRpSTZJa2gwVmtKVE4yWjVNamsyV1RkNGRHTlhWM0pKVFZFOVBTSXNJblpoYkhWbElqb2lhbXROVVRGSlFUVjFWSEJ6VG0xU1VFWTNkRVV3VjIxS0x6Tm5jRGt2UXpkd1NFUllkRkV4Ukdjd01HaHBRVmRVWkhjMkwxaElaek0wYVRKdmFreE9SMWRQTWk4dmQwUTNNM0V2TW1wekt6ZHNXVVJUUkcxaU16bFlaVXM0ZUcxcGF6Uk1WbmxFYlVwNWNtZFFibGd5YVdnMVdHaEpUMnhNY1d3dmRsVm5aVmRIY2sxRFVHbHNPWGRJSzJOdVRqSkllaXRGYW1KMWRVNXdVRmhIWlZkTGNtZGhiRTlpTlRJdmQzVXlNbUk1V1ZkaWFGcDRZVGRyTUVVeWJHeGhMMHhITjNGSlJYRjNlWEJJWTFSTVIzaEZaV0pOWVZWeVFUMDlJaXdpYldGaklqb2lZekpqTXpjNE1ESmxZVEkyTXpJek5qa3daRFkxTURoaU1HSTJOV1ptWXpGaU9EQmpNRFJpWmpNNFpHRTFZVGM0WkdJd01UTXlNalZpTVRBd1lXVmtZU0lzSW5SaFp5STZJaUo5', 1792587090),
('wPDv3Rih1PzcL7WEv2EScxYHgp2Al9uM6W89v8pX', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.9711', 'ZXlKcGRpSTZJbGxLV1hoMVFYVTFURTF4VTNRd01UbEpVRmxrWmxFOVBTSXNJblpoYkhWbElqb2lkVU5EU2xGdlZVZFlNM1E0VVdadmR6bHBSM3BYZGpacVZWcFlObUoyWTNsdlpHNXRUV2xXZWs1SmRuWnNUbUowWlZGTFkyOHhlVEZIV0ROamFrczROVUp2Ym5aeVZERnZjekJEZWxBMWMyVk9lRkl3WlVoSksyRnJSamgwVWxSaFNHNURhbk5VU1cxSFZIVk1UMFp6ZUZWVVNtcDRXRlJQTjA4M2RVaHBiVnBGYWtwVkswbzFUbGxVWmpCQ1EyWldWM3BZUVhKYVYzbEtaV3hVTUhwV2FUSnphV1V6T1dwSk1tc3JNSFZxTmxWNWNFVjZPV0U0WjJnNGVURjBXRGxaVUVrNFluSmxNVGg1WjBNdlMyTTJlRUYxZGxCYWRUSlBUbkJ0WTBwdloyTXliemx3Y3pseWVuZEhPRDBpTENKdFlXTWlPaUpqTkRjNU56bGhZbUUxWWpaak5UbG1Oek5oTVdZeU16TTFNRFpoWTJRM1pHRmhZMlU0T1RkbU1tSmhNbU01TlRkaE9UZG1ZbVF6TXpRMk56RXhaR05rSWl3aWRHRm5Jam9pSW4wPQ==', 1792587002),
('XCuDXethwMl73wIrB6VCeHF5GEjAO9BiWXAE4JtI', NULL, '127.0.0.1', 'Symfony', 'eyJsb2dpbl9hZG1pbl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjoxLCJfdG9rZW4iOiJOYjVQTnhEN3JTR1Z3eFZXSHVFT3YxSDNGdHB0MUtJQmxEaTZlUDgzIiwicGFzc3dvcmRfaGFzaF9hZG1pbiI6IjkwMjQ0M2RjNzQ4NzJlYThmNzdmZTA5MDgxYzhmNjJkNGVhZWJjM2U4ZGZlMWViYTA4YTUwMjg3MWU0MGQ3ZGUiLCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvbG9jYWxob3N0OjgwMDBcL0ZyZXNoTm9uVmVnQWRtaW5cL3dhbGxldCIsInJvdXRlIjoiYWRtaW4ud2FsbGV0LmluZGV4In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=', 1792584515);

-- --------------------------------------------------------

--
-- Table structure for table `site_stats`
--

CREATE TABLE `site_stats` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `value` int(10) UNSIGNED NOT NULL,
  `label` varchar(255) NOT NULL,
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `site_stats`
--

INSERT INTO `site_stats` (`id`, `value`, `label`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES
(1, 5000, 'Happy Customers 😍', 1, 1, '2026-10-17 04:49:36', '2026-10-17 04:49:36'),
(2, 25, 'Items Fresh Daily 🥩', 2, 1, '2026-10-17 04:49:36', '2026-10-17 04:49:36'),
(3, 5, 'Cities Covered 📍', 3, 1, '2026-10-17 04:49:36', '2026-10-17 04:49:36'),
(4, 100, '% Fresh, 0% Frozen ❄️', 4, 1, '2026-10-17 04:49:36', '2026-10-17 04:49:36');

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tags`
--

INSERT INTO `tags` (`id`, `name`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Bestseller', 1, '2026-10-17 03:27:29', '2026-10-17 03:27:29'),
(2, 'Fresh', 1, '2026-10-17 03:27:29', '2026-10-17 03:27:29'),
(3, 'Jumbo', 1, '2026-10-17 03:27:29', '2026-10-17 03:27:29'),
(4, 'New', 1, '2026-10-17 03:27:29', '2026-10-17 03:27:29'),
(5, 'Sale', 1, '2026-10-17 03:27:29', '2026-10-17 03:27:29'),
(6, 'Premium', 1, '2026-10-17 03:27:29', '2026-10-17 03:27:29');

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `star_rating` tinyint(3) UNSIGNED NOT NULL DEFAULT 5,
  `short_description` text NOT NULL,
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `testimonials`
--

INSERT INTO `testimonials` (`id`, `name`, `star_rating`, `short_description`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Rahul, Noida', 5, 'Mutton एकदम fresh था, बाजार से बेहतर!', 1, '2026-10-17 04:46:20', '2026-10-17 04:46:20'),
(2, 'Priya, Greater Noida', 5, 'Prawns jumbo size ke the, delivery bhi fast!', 1, '2026-10-17 04:46:20', '2026-10-17 04:46:20'),
(3, 'Amit, Ghaziabad', 5, 'Frozen नहीं, सच में ताज़ा चिकन मिला।', 1, '2026-10-17 04:46:20', '2026-10-17 04:46:20');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `wallet_balance` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `google_id` varchar(255) DEFAULT NULL,
  `otp` varchar(255) DEFAULT NULL,
  `otp_expires_at` timestamp NULL DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `registration_source` varchar(20) DEFAULT NULL,
  `web_last_login_at` timestamp NULL DEFAULT NULL,
  `app_installed_at` timestamp NULL DEFAULT NULL,
  `fcm_token` text DEFAULT NULL,
  `fcm_token_updated_at` timestamp NULL DEFAULT NULL,
  `device_type` varchar(30) DEFAULT NULL,
  `device_id` varchar(255) DEFAULT NULL,
  `device_name` varchar(255) DEFAULT NULL,
  `device_brand` varchar(80) DEFAULT NULL,
  `device_model` varchar(80) DEFAULT NULL,
  `os_version` varchar(40) DEFAULT NULL,
  `app_version` varchar(30) DEFAULT NULL,
  `app_build_number` varchar(30) DEFAULT NULL,
  `timezone` varchar(64) DEFAULT NULL,
  `locale` varchar(20) DEFAULT NULL,
  `login_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL,
  `is_logged_in` tinyint(1) NOT NULL DEFAULT 0,
  `last_active_at` timestamp NULL DEFAULT NULL,
  `last_logout_at` timestamp NULL DEFAULT NULL,
  `is_app_installed` tinyint(1) NOT NULL DEFAULT 0,
  `app_uninstalled_at` timestamp NULL DEFAULT NULL,
  `notifications_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `notifications_disabled_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `profile_image`, `latitude`, `longitude`, `address`, `wallet_balance`, `status`, `google_id`, `otp`, `otp_expires_at`, `email_verified_at`, `password`, `remember_token`, `registration_source`, `web_last_login_at`, `app_installed_at`, `fcm_token`, `fcm_token_updated_at`, `device_type`, `device_id`, `device_name`, `device_brand`, `device_model`, `os_version`, `app_version`, `app_build_number`, `timezone`, `locale`, `login_count`, `last_login_at`, `last_login_ip`, `is_logged_in`, `last_active_at`, `last_logout_at`, `is_app_installed`, `app_uninstalled_at`, `notifications_enabled`, `notifications_disabled_at`, `created_at`, `updated_at`) VALUES
(1, 'Heera', 'heeralalzx2020@gmail.com', '9899050881', NULL, NULL, NULL, 'vikas nager, Jalna, Maharashtra, 431114', 0.00, 1, NULL, NULL, NULL, NULL, '$2y$12$EKTAcF3TxzLYXv.DS6Kjw.PX/LH9iHVbht3Bvjt0zqvSy.bWvpI66', 'BhYsYI9Qm1r39RnlFYMOFK3sA3xk5wKtvx0dHD9F5ZO4iMKyG97YpPLifeZq', 'web', '2026-10-21 04:17:24', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, '2026-10-21 04:17:24', '127.0.0.1', 1, '2026-10-21 04:17:24', '2026-10-17 02:11:36', 0, NULL, 1, NULL, '2026-10-17 02:10:03', '2026-10-21 05:01:58');

-- --------------------------------------------------------

--
-- Table structure for table `wallet_transactions`
--

CREATE TABLE `wallet_transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(20) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `balance_after` decimal(12,2) NOT NULL,
  `description` varchar(255) NOT NULL,
  `complaint_id` bigint(20) UNSIGNED DEFAULT NULL,
  `order_id` bigint(20) UNSIGNED DEFAULT NULL,
  `admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wishlist_items`
--

CREATE TABLE `wishlist_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wishlist_items`
--

INSERT INTO `wishlist_items` (`id`, `user_id`, `product_id`, `product_name`, `created_at`, `updated_at`) VALUES
(1, 1, 19, 'Tandoori Chicken Marinated', '2026-10-21 04:18:48', '2026-10-21 04:18:48');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admins_email_unique` (`email`);

--
-- Indexes for table `advantage_items`
--
ALTER TABLE `advantage_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `banners`
--
ALTER TABLE `banners`
  ADD PRIMARY KEY (`id`),
  ADD KEY `banners_status_index` (`status`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cart_items_user_id_index` (`user_id`),
  ADD KEY `cart_items_product_id_foreign` (`product_id`),
  ADD KEY `cart_items_updated_at_index` (`updated_at`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categories_status_index` (`status`);

--
-- Indexes for table `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`id`),
  ADD KEY `complaints_user_id_foreign` (`user_id`),
  ADD KEY `complaints_order_id_foreign` (`order_id`),
  ADD KEY `complaints_status_created_at_index` (`status`,`created_at`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `coupons_code_unique` (`code`);

--
-- Indexes for table `delivery_locations`
--
ALTER TABLE `delivery_locations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `delivery_slots`
--
ALTER TABLE `delivery_slots`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `delivery_slots_slot_key_unique` (`slot_key`);

--
-- Indexes for table `delivery_zones`
--
ALTER TABLE `delivery_zones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `delivery_zones_status_sort_order_index` (`status`,`sort_order`);

--
-- Indexes for table `delivery_zone_pincodes`
--
ALTER TABLE `delivery_zone_pincodes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `delivery_zone_pincodes_delivery_zone_id_pincode_unique` (`delivery_zone_id`,`pincode`),
  ADD KEY `delivery_zone_pincodes_pincode_index` (`pincode`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  ADD KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`);

--
-- Indexes for table `faqs`
--
ALTER TABLE `faqs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `home_sections`
--
ALTER TABLE `home_sections`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `home_sections_section_key_unique` (`section_key`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `orders_order_number_unique` (`order_number`),
  ADD KEY `orders_user_id_status_index` (`user_id`,`status`),
  ADD KEY `orders_coupon_id_foreign` (`coupon_id`),
  ADD KEY `orders_status_created_at_index` (`status`,`created_at`),
  ADD KEY `orders_admin_seen_at_index` (`admin_seen_at`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_items_order_id_foreign` (`order_id`),
  ADD KEY `order_items_product_id_foreign` (`product_id`);

--
-- Indexes for table `order_steps`
--
ALTER TABLE `order_steps`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `page_contents`
--
ALTER TABLE `page_contents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `page_contents_page_id_foreign` (`page_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `products_banner_id_foreign` (`banner_id`),
  ADD KEY `products_status_featured_index` (`status`,`is_featured`),
  ADD KEY `products_category_status_index` (`category_id`,`status`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_images_product_id_sort_order_index` (`product_id`,`sort_order`);

--
-- Indexes for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_reviews_user_id_product_id_unique` (`user_id`,`product_id`),
  ADD KEY `product_reviews_order_id_foreign` (`order_id`),
  ADD KEY `product_reviews_product_id_status_index` (`product_id`,`status`);

--
-- Indexes for table `product_tag`
--
ALTER TABLE `product_tag`
  ADD PRIMARY KEY (`product_id`,`tag_id`),
  ADD KEY `product_tag_tag_id_foreign` (`tag_id`);

--
-- Indexes for table `product_weights`
--
ALTER TABLE `product_weights`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_weights_product_id_foreign` (`product_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `site_stats`
--
ALTER TABLE `site_stats`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tags_name_unique` (`name`);

--
-- Indexes for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_phone_unique` (`phone`),
  ADD UNIQUE KEY `users_google_id_unique` (`google_id`),
  ADD KEY `users_status_index` (`status`);

--
-- Indexes for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `wallet_transactions_complaint_id_foreign` (`complaint_id`),
  ADD KEY `wallet_transactions_order_id_foreign` (`order_id`),
  ADD KEY `wallet_transactions_admin_id_foreign` (`admin_id`),
  ADD KEY `wallet_transactions_user_type_index` (`user_id`,`type`);

--
-- Indexes for table `wishlist_items`
--
ALTER TABLE `wishlist_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `wishlist_items_user_id_index` (`user_id`),
  ADD KEY `wishlist_items_product_id_foreign` (`product_id`),
  ADD KEY `wishlist_items_created_at_index` (`created_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `advantage_items`
--
ALTER TABLE `advantage_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `banners`
--
ALTER TABLE `banners`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `delivery_locations`
--
ALTER TABLE `delivery_locations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `delivery_slots`
--
ALTER TABLE `delivery_slots`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `delivery_zones`
--
ALTER TABLE `delivery_zones`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `delivery_zone_pincodes`
--
ALTER TABLE `delivery_zone_pincodes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=110;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `faqs`
--
ALTER TABLE `faqs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `home_sections`
--
ALTER TABLE `home_sections`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `order_steps`
--
ALTER TABLE `order_steps`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `pages`
--
ALTER TABLE `pages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `page_contents`
--
ALTER TABLE `page_contents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT for table `product_reviews`
--
ALTER TABLE `product_reviews`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `product_weights`
--
ALTER TABLE `product_weights`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `site_stats`
--
ALTER TABLE `site_stats`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `wishlist_items`
--
ALTER TABLE `wishlist_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `cart_items_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `complaints`
--
ALTER TABLE `complaints`
  ADD CONSTRAINT `complaints_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `complaints_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `delivery_zone_pincodes`
--
ALTER TABLE `delivery_zone_pincodes`
  ADD CONSTRAINT `delivery_zone_pincodes_delivery_zone_id_foreign` FOREIGN KEY (`delivery_zone_id`) REFERENCES `delivery_zones` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_coupon_id_foreign` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `page_contents`
--
ALTER TABLE `page_contents`
  ADD CONSTRAINT `page_contents_page_id_foreign` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_banner_id_foreign` FOREIGN KEY (`banner_id`) REFERENCES `banners` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD CONSTRAINT `product_reviews_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `product_reviews_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_tag`
--
ALTER TABLE `product_tag`
  ADD CONSTRAINT `product_tag_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_tag_tag_id_foreign` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_weights`
--
ALTER TABLE `product_weights`
  ADD CONSTRAINT `product_weights_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  ADD CONSTRAINT `wallet_transactions_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `wallet_transactions_complaint_id_foreign` FOREIGN KEY (`complaint_id`) REFERENCES `complaints` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `wallet_transactions_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `wallet_transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlist_items`
--
ALTER TABLE `wishlist_items`
  ADD CONSTRAINT `wishlist_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `wishlist_items_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
