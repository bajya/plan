-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 23, 2024 at 06:48 PM
-- Server version: 10.4.24-MariaDB
-- PHP Version: 8.0.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `laravel_setup_plan`
--

-- --------------------------------------------------------

--
-- Table structure for table `bus_rule_ref`
--

CREATE TABLE `bus_rule_ref` (
  `id` int(11) NOT NULL,
  `type` enum('text','file','textarea','number','email') NOT NULL DEFAULT 'text',
  `name` varchar(255) DEFAULT NULL,
  `rule_name` varchar(100) NOT NULL,
  `rule_value` text NOT NULL,
  `comment` mediumtext DEFAULT NULL,
  `sts_cd` enum('AC','IN','DL') NOT NULL DEFAULT 'AC',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `bus_rule_ref`
--

INSERT INTO `bus_rule_ref` (`id`, `type`, `name`, `rule_name`, `rule_value`, `comment`, `sts_cd`, `created_at`, `updated_at`) VALUES
(1, 'text', 'Currency', 'currency', '$', 'symbol', 'AC', '2022-04-06 01:49:27', '2022-09-10 04:13:27'),
(2, 'email', 'Support Mail', 'support_mail', 'admin@m.in', 'email', 'AC', '2022-04-06 01:49:27', '2024-07-23 16:29:07'),
(3, 'email', 'Mail Sender', 'sender_id', 'admin@m.in', 'email', 'AC', '2022-04-06 01:49:27', '2024-07-23 16:28:59'),
(4, 'textarea', 'Share Message', 'refer_share_message', '', 'message', 'AC', '2021-09-14 05:28:35', '2024-07-23 16:28:50'),
(5, 'text', 'Apple Url User', 'ios_url_user', 'https://play.google.com/store', NULL, 'AC', '2021-09-14 05:33:05', '2022-07-08 07:41:47'),
(6, 'text', 'Android Url User', 'android_url_user', 'https://play.google.com/store', NULL, 'AC', '2021-09-14 05:33:05', '2022-07-08 07:41:53'),
(7, 'text', 'Call Us', 'call_us', '+918888888888', 'call us ', 'AC', '2021-11-30 12:28:11', '2022-07-08 07:44:14'),
(8, 'text', 'Legal', 'legal', '', 'legal page', 'AC', '2021-11-30 06:58:11', '2024-07-23 16:28:41');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` longtext DEFAULT NULL,
  `image` longtext DEFAULT NULL,
  `parent_id` bigint(20) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `type` enum('category','type','strain') NOT NULL DEFAULT 'category',
  `is_defalt` enum('true','false') NOT NULL DEFAULT 'false',
  `order_no` bigint(20) NOT NULL DEFAULT 0,
  `status` enum('active','inactive','delete','out') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `image`, `parent_id`, `description`, `type`, `is_defalt`, `order_no`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Edibles', 'noimage.jpg', NULL, '', 'category', 'false', 4, 'active', '2022-10-22 14:40:01', '2022-10-22 23:21:51'),
(2, 'Gummies', 'noimage.jpg', 1, '', 'type', 'false', 1, 'active', '2022-10-22 14:40:01', '2022-10-22 15:51:51'),
(3, 'Chocolates', 'noimage.jpg', 1, '', 'type', 'false', 2, 'active', '2022-10-22 14:40:01', '2022-10-22 15:51:51'),
(4, 'Other', 'noimage.jpg', 1, '', 'type', 'false', 4, 'active', '2022-10-22 14:40:01', '2022-10-23 00:07:21'),
(5, 'Syringes', 'noimage.jpg', NULL, '', 'category', 'false', 5, 'active', '2022-10-22 14:40:01', '2022-10-22 23:21:51'),
(6, 'Distillate', 'noimage.jpg', 5, '', 'type', 'false', 32, 'active', '2022-10-22 14:40:01', '2022-10-23 00:07:21'),
(7, 'Concentrates', 'noimage.jpg', NULL, '', 'category', 'false', 6, 'active', '2022-10-22 14:40:01', '2022-10-22 15:49:11'),
(8, 'Rosin', 'noimage.jpg', 7, '', 'type', 'false', 10, 'active', '2022-10-22 14:40:01', '2022-10-22 16:48:21'),
(9, 'RSO', 'noimage.jpg', 5, '', 'type', 'false', 25, 'active', '2022-10-22 14:40:01', '2022-10-22 16:48:12'),
(10, 'Kief', 'noimage.jpg', 7, '', 'type', 'false', 8, 'active', '2022-10-22 14:40:01', '2022-10-22 16:48:12'),
(11, 'Sauce', 'noimage.jpg', 7, '', 'type', 'false', 11, 'active', '2022-10-22 14:40:01', '2022-10-22 16:48:25'),
(12, 'Resin', 'noimage.jpg', 7, '', 'type', 'false', 9, 'active', '2022-10-22 14:40:01', '2022-10-22 15:51:51'),
(13, 'Flower', 'noimage.jpg', NULL, '', 'category', 'false', 1, 'active', '2022-10-22 14:40:01', '2022-11-02 10:17:18'),
(14, 'Buds', 'noimage.jpg', 13, '', 'type', 'false', 26, 'active', '2022-10-22 14:40:01', '2022-10-22 16:48:21'),
(15, 'Gear', 'noimage.jpg', NULL, '', 'category', 'false', 9, 'active', '2022-10-22 14:40:01', '2022-11-02 10:17:03'),
(16, 'Accessories', 'noimage.jpg', 15, '', 'type', 'false', 24, 'active', '2022-10-22 14:40:01', '2022-10-22 16:48:25'),
(17, 'Pre-Rolls', 'noimage.jpg', NULL, '', 'category', 'false', 2, 'active', '2022-10-22 14:40:01', '2022-10-22 23:21:34'),
(18, 'Pack', 'noimage.jpg', 17, '', 'type', 'false', 29, 'active', '2022-10-22 14:40:01', '2022-10-22 16:48:47'),
(19, 'Single', 'noimage.jpg', 17, '', 'type', 'false', 27, 'active', '2022-10-22 14:40:01', '2022-10-22 16:48:52'),
(20, 'Oral', 'noimage.jpg', NULL, '', 'category', 'false', 7, 'active', '2022-10-22 14:40:01', '2022-10-22 15:49:11'),
(21, 'Capsule', 'noimage.jpg', 20, '', 'type', 'false', 18, 'active', '2022-10-22 14:40:01', '2022-10-22 16:00:06'),
(22, 'Topical', 'noimage.jpg', NULL, '', 'category', 'false', 8, 'active', '2022-10-22 14:40:01', '2022-11-02 10:17:03'),
(23, 'Gel', 'noimage.jpg', 22, '', 'type', 'false', 30, 'active', '2022-10-22 14:40:01', '2022-10-22 16:48:58'),
(24, 'Patch', 'noimage.jpg', 22, '', 'type', 'false', 16, 'active', '2022-10-22 14:40:01', '2022-10-22 15:51:51'),
(25, 'Other', 'noimage.jpg', 22, '', 'type', 'false', 17, 'active', '2022-10-22 14:40:02', '2022-10-22 15:51:51'),
(26, 'Lotion', 'noimage.jpg', 22, '', 'type', 'false', 28, 'active', '2022-10-22 14:40:02', '2022-10-22 16:49:02'),
(27, 'Vape', 'noimage.jpg', NULL, '', 'category', 'false', 3, 'active', '2022-10-22 14:40:02', '2022-10-24 13:51:31'),
(28, 'Cartridge', 'noimage.jpg', 27, '', 'type', 'false', 19, 'active', '2022-10-22 14:40:02', '2022-10-22 15:51:51'),
(29, 'Pods', 'noimage.jpg', 27, '', 'type', 'false', 20, 'active', '2022-10-22 14:40:02', '2022-10-22 15:51:51'),
(30, 'Other', 'noimage.jpg', 27, '', 'type', 'false', 21, 'active', '2022-10-22 14:40:02', '2022-10-22 15:51:51'),
(31, 'Small Buds', 'noimage.jpg', 13, '', 'type', 'false', 22, 'active', '2022-10-22 16:10:02', '2022-10-22 16:38:58'),
(32, 'Ground', 'noimage.jpg', 13, '', 'type', 'false', 23, 'active', '2022-10-22 16:10:02', '2022-10-22 16:39:00'),
(33, 'Crumble', 'noimage.jpg', 7, '', 'type', 'false', 6, 'active', '2022-10-22 16:10:02', '2022-10-22 16:47:50'),
(34, 'Budder', 'noimage.jpg', 7, '', 'type', 'false', 5, 'active', '2022-10-22 16:10:02', '2022-10-22 16:47:41'),
(35, 'Diamonds', 'noimage.jpg', 7, '', 'type', 'false', 7, 'active', '2022-10-22 16:10:02', '2022-10-22 16:48:01'),
(36, 'Sugar', 'noimage.jpg', 7, '', 'type', 'false', 13, 'active', '2022-10-22 16:10:02', '2022-10-22 16:48:52'),
(37, 'Other', 'noimage.jpg', 7, '', 'type', 'false', 15, 'active', '2022-10-22 16:10:02', '2022-10-22 16:49:02'),
(38, 'Shatter', 'noimage.jpg', 7, '', 'type', 'false', 12, 'active', '2022-10-22 16:10:02', '2022-10-22 16:48:47'),
(39, 'Waxes', 'noimage.jpg', 7, '', 'type', 'false', 14, 'active', '2022-10-22 16:10:02', '2022-10-22 16:48:58'),
(40, 'Disposable', 'noimage.jpg', 27, '', 'type', 'false', 31, 'active', '2022-10-22 16:20:02', '2022-10-22 16:39:22'),
(41, 'Baked Goods', 'noimage.jpg', 1, '', 'type', 'false', 3, 'active', '2022-10-22 16:20:03', '2022-10-23 00:07:07'),
(42, 'Tincture', 'noimage.jpg', 20, '', 'type', 'false', 33, 'active', '2022-10-22 16:20:03', '2022-10-22 16:39:28'),
(43, 'Merchandise', 'noimage.jpg', 15, '', 'type', 'false', 34, 'active', '2022-10-22 17:20:02', '2022-10-22 17:21:51'),
(44, 'Balm', 'noimage.jpg', 22, '', 'type', 'false', 35, 'active', '2022-10-22 23:00:02', '2022-10-22 23:06:26'),
(45, 'Sublingual', 'noimage.jpg', 20, '', 'type', 'false', 36, 'active', '2022-10-22 23:00:02', '2022-10-22 23:06:31'),
(46, 'unflavored', 'noimage.jpg', 20, '', 'type', 'false', 37, 'inactive', '2022-10-22 23:00:02', '2022-10-24 14:01:03');

-- --------------------------------------------------------

--
-- Table structure for table `cms`
--

CREATE TABLE `cms` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `type` varchar(20) NOT NULL,
  `content` text NOT NULL,
  `original_content` text NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `status` enum('active','inactive','delete') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `cms`
--

INSERT INTO `cms` (`id`, `name`, `slug`, `type`, `content`, `original_content`, `parent_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Legal', 'legal', 'textarea', '', '<p>zaxscvasdcf</p>', NULL, 'active', '2022-07-15 10:24:28', '2024-07-23 16:29:48'),
(2, 'Faqs', 'faq', 'textarea', '', '', NULL, 'delete', '2022-07-15 10:24:28', '2022-07-22 05:05:16'),
(3, 'Privacy Policy', 'privacy-policy', 'textarea', '', '<p>zaxscvasdcf</p>', NULL, 'active', '2022-07-15 10:24:28', '2024-07-23 16:29:42'),
(4, 'Terms and Conditions', 'term-and-condition', 'textarea', '', '<p>zaxscvasdcf</p>', NULL, 'active', '2022-07-15 10:24:28', '2022-09-29 08:55:13');

-- --------------------------------------------------------

--
-- Table structure for table `cms_faq_questions`
--

CREATE TABLE `cms_faq_questions` (
  `id` int(11) NOT NULL,
  `question` mediumtext NOT NULL,
  `answer` mediumtext NOT NULL,
  `status` enum('active','inactive','delete') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `type` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `cms_faq_questions`
--

INSERT INTO `cms_faq_questions` (`id`, `question`, `answer`, `status`, `created_at`, `updated_at`, `type`) VALUES
(1, 'What is this?', 'Yes', 'active', '2022-07-15 10:26:08', '2022-07-15 10:26:08', 'faq');

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `id` int(11) NOT NULL,
  `state_id` longtext DEFAULT NULL,
  `name` longtext DEFAULT NULL,
  `image` longtext DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `status` enum('active','inactive','delete','out') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `custom_logs`
--

CREATE TABLE `custom_logs` (
  `id` int(11) NOT NULL,
  `sno` bigint(20) NOT NULL DEFAULT 0,
  `title` varchar(250) NOT NULL,
  `type` enum('product','doctor','location') NOT NULL DEFAULT 'product',
  `description` longtext DEFAULT NULL,
  `status` enum('active','inactive','delete') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `dispensaries`
--

CREATE TABLE `dispensaries` (
  `id` int(11) NOT NULL,
  `brand_id` bigint(20) NOT NULL DEFAULT 0,
  `location_id` longtext DEFAULT NULL,
  `name` longtext DEFAULT NULL,
  `phone_code` longtext DEFAULT NULL,
  `phone_number` longtext DEFAULT NULL,
  `image` longtext DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `country` longtext DEFAULT NULL,
  `state` longtext DEFAULT NULL,
  `city` longtext DEFAULT NULL,
  `address` longtext DEFAULT NULL,
  `lat` longtext DEFAULT NULL,
  `lng` longtext DEFAULT NULL,
  `location_email` longtext DEFAULT NULL,
  `location_url` longtext DEFAULT NULL,
  `location_times` longtext DEFAULT NULL,
  `location_name_website` longtext DEFAULT NULL,
  `location_state_website` longtext DEFAULT NULL,
  `location_address_website` longtext DEFAULT NULL,
  `location_phone_website` longtext DEFAULT NULL,
  `location_times_website` longtext DEFAULT NULL,
  `status` enum('active','inactive','delete','out') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `doctors`
--

CREATE TABLE `doctors` (
  `id` int(11) NOT NULL,
  `brand_id` bigint(20) NOT NULL DEFAULT 0,
  `name` text DEFAULT NULL,
  `phone_code` varchar(255) NOT NULL DEFAULT '+91',
  `phone_number` text DEFAULT NULL,
  `email` text DEFAULT NULL,
  `image` text DEFAULT NULL,
  `country` text DEFAULT NULL,
  `state` text DEFAULT NULL,
  `city` text DEFAULT NULL,
  `address` text DEFAULT NULL,
  `zipcode` text DEFAULT NULL,
  `lat` text DEFAULT NULL,
  `lng` text DEFAULT NULL,
  `status` enum('active','inactive','delete','out') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feedbacks`
--

CREATE TABLE `feedbacks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `smiley` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive','delete','out') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'inactive',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `filemanagers`
--

CREATE TABLE `filemanagers` (
  `id` int(11) NOT NULL,
  `name` longtext DEFAULT NULL,
  `type` enum('product','doctor') NOT NULL DEFAULT 'product',
  `image` longtext DEFAULT NULL,
  `status` enum('active','inactive','delete','out') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `file_imports`
--

CREATE TABLE `file_imports` (
  `id` int(11) NOT NULL,
  `file` int(11) NOT NULL,
  `no_products` int(11) NOT NULL,
  `status` enum('active','inactive','delete') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `import_files`
--

CREATE TABLE `import_files` (
  `id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT 0 COMMENT '0=pending,1=completed,2=failed',
  `type` enum('product','location') NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `limitations`
--

CREATE TABLE `limitations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT 0,
  `product_id` bigint(20) NOT NULL DEFAULT 0,
  `type` enum('in','out') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'in',
  `status` enum('active','inactive','delete','out') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `from` bigint(20) NOT NULL,
  `to` bigint(20) NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_read` tinyint(4) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_resets_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2020_12_02_150823_create_messages_table', 1),
(5, '2022_07_01_105333_create_permission_tables', 2),
(6, '2016_06_01_000001_create_oauth_auth_codes_table', 3),
(7, '2016_06_01_000002_create_oauth_access_tokens_table', 3),
(8, '2016_06_01_000003_create_oauth_refresh_tokens_table', 3),
(9, '2016_06_01_000004_create_oauth_clients_table', 3),
(10, '2016_06_01_000005_create_oauth_personal_access_clients_table', 3),
(11, '2022_07_21_102551_create_c_m_s_table', 3),
(12, '2022_07_21_102606_create_f_a_q_questions_table', 3),
(13, '2019_05_03_000001_create_customer_columns', 4),
(14, '2019_05_03_000002_create_subscriptions_table', 4),
(15, '2019_05_03_000003_create_subscription_items_table', 4),
(16, '2022_09_07_103941_create_job_batches_table', 5);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\User', 1),
(2, 'App\\User', 3),
(2, 'App\\User', 12),
(2, 'App\\User', 39);

-- --------------------------------------------------------

--
-- Table structure for table `notification_users`
--

CREATE TABLE `notification_users` (
  `id` int(11) NOT NULL,
  `sender_id` bigint(20) NOT NULL DEFAULT 0,
  `receiver_id` bigint(20) NOT NULL DEFAULT 0,
  `title` mediumtext NOT NULL,
  `description` mediumtext NOT NULL,
  `notification_type` varchar(50) NOT NULL DEFAULT '',
  `is_read` int(11) NOT NULL DEFAULT 0,
  `status` enum('active','inactive','delete','out') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `oauth_access_tokens`
--

CREATE TABLE `oauth_access_tokens` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scopes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oauth_auth_codes`
--

CREATE TABLE `oauth_auth_codes` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `scopes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oauth_clients`
--

CREATE TABLE `oauth_clients` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `secret` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `redirect` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `personal_access_client` tinyint(1) NOT NULL,
  `password_client` tinyint(1) NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `oauth_clients`
--

INSERT INTO `oauth_clients` (`id`, `user_id`, `name`, `secret`, `provider`, `redirect`, `personal_access_client`, `password_client`, `revoked`, `created_at`, `updated_at`) VALUES
(1, NULL, 'Laravel Personal Access Client', 'WLRYzFENn1fH2s5Oya1WdzmjeomFZjJFVAA3a6eo', NULL, 'http://localhost', 1, 0, 0, '2022-07-26 07:22:18', '2022-07-26 07:22:18'),
(2, NULL, 'Laravel Password Grant Client', 'CeXED2tyUvC8OPmNTPHQbEqkPQEOeYWu2wbnLOIT', 'users', 'http://localhost', 0, 1, 0, '2022-07-26 07:22:18', '2022-07-26 07:22:18');

-- --------------------------------------------------------

--
-- Table structure for table `oauth_personal_access_clients`
--

CREATE TABLE `oauth_personal_access_clients` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `oauth_personal_access_clients`
--

INSERT INTO `oauth_personal_access_clients` (`id`, `client_id`, `created_at`, `updated_at`) VALUES
(1, 1, '2022-07-26 07:22:18', '2022-07-26 07:22:18');

-- --------------------------------------------------------

--
-- Table structure for table `oauth_refresh_tokens`
--

CREATE TABLE `oauth_refresh_tokens` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `access_token_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `otp`
--

CREATE TABLE `otp` (
  `id` int(11) NOT NULL,
  `user_id` bigint(20) NOT NULL DEFAULT 0,
  `email` varchar(255) DEFAULT '',
  `otp_expire_time` datetime NOT NULL DEFAULT current_timestamp(),
  `code` varchar(50) NOT NULL DEFAULT '',
  `status` enum('AC','IN','DL') NOT NULL DEFAULT 'AC',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'role-list', 'web', '2022-07-01 06:32:05', '2022-07-01 06:32:05'),
(2, 'role-create', 'web', '2022-07-01 06:32:05', '2022-07-01 06:32:05'),
(3, 'role-edit', 'web', '2022-07-01 06:32:05', '2022-07-01 06:32:05'),
(4, 'role-delete', 'web', '2022-07-01 06:32:05', '2022-07-01 06:32:05'),
(5, 'user-list', 'web', '2022-07-01 06:32:05', '2022-07-01 06:32:05'),
(6, 'user-create', 'web', '2022-07-01 06:32:05', '2022-07-01 06:32:05'),
(7, 'user-edit', 'web', '2022-07-01 06:32:05', '2022-07-01 06:32:05'),
(8, 'user-delete', 'web', '2022-07-01 06:32:05', '2022-07-01 06:32:05'),
(9, 'admin-list', 'web', '2022-07-01 06:32:05', '2022-07-01 06:32:05'),
(10, 'admin-create', 'web', '2022-07-01 06:32:05', '2022-07-01 06:32:05'),
(11, 'admin-edit', 'web', '2022-07-01 06:32:05', '2022-07-01 06:32:05'),
(12, 'admin-delete', 'web', '2022-07-01 06:32:05', '2022-07-01 06:32:05'),
(13, 'cms-list', 'web', '2022-07-01 06:32:05', '2022-07-01 06:32:05'),
(14, 'cms-edit', 'web', '2022-07-01 06:32:05', '2022-07-01 06:32:05'),
(15, 'product-list', 'web', NULL, NULL),
(16, 'product-create', 'web', NULL, NULL),
(17, 'product-edit', 'web', '2022-07-01 06:32:05', '2022-07-01 06:32:05'),
(18, 'product-delete', 'web', '2022-07-01 06:32:05', '2022-07-01 06:32:05'),
(19, 'support-list', 'web', '2022-07-01 06:32:05', '2022-07-01 06:32:05'),
(20, 'category-list', 'web', '2022-07-01 06:32:05', '2022-07-01 06:32:05'),
(21, 'category-create', 'web', '2022-07-01 06:32:05', '2022-07-01 06:32:05'),
(22, 'category-edit', 'web', '2022-07-01 06:32:05', '2022-07-01 06:32:05'),
(23, 'category-delete', 'web', '2022-07-01 06:32:05', '2022-07-01 06:32:05'),
(24, 'feedback-list', 'web', '2022-07-01 06:32:05', '2022-07-01 06:32:05'),
(25, 'dispensary-list', 'web', '2022-07-01 06:32:05', '2022-07-01 06:32:05'),
(26, 'dispensary-create', 'web', '2022-07-01 06:32:05', '2022-07-01 06:32:05'),
(27, 'dispensary-edit', 'web', '2022-07-01 06:32:05', '2022-07-01 06:32:05'),
(28, 'dispensary-delete', 'web', '2022-07-01 06:32:05', '2022-07-01 06:32:05'),
(29, 'push-list', 'web', '2022-07-01 06:32:05', '2022-07-01 06:32:05'),
(30, 'push-create', 'web', '2022-07-01 06:32:05', '2022-07-01 06:32:05'),
(31, 'plan-list', 'web', '2022-07-01 06:32:05', '2022-07-01 06:32:05'),
(32, 'plan-edit', 'web', '2022-07-01 06:32:05', '2022-07-01 06:32:05'),
(33, 'transaction-list', 'web', '2022-07-01 06:32:05', '2022-07-01 06:32:05'),
(34, 'brand-list', 'web', '2022-07-01 06:32:05', '2022-07-01 06:32:05'),
(35, 'brand-create', 'web', '2022-07-01 06:32:05', '2022-07-01 06:32:05'),
(36, 'brand-edit', 'web', '2022-07-01 06:32:05', '2022-07-01 06:32:05'),
(37, 'brand-delete', 'web', '2022-07-01 06:32:05', '2022-07-01 06:32:05'),
(38, 'type-list', 'web', '2022-07-01 06:32:05', '2022-07-01 06:32:05'),
(39, 'type-create', 'web', '2022-07-01 06:32:05', '2022-07-01 06:32:05'),
(40, 'type-edit', 'web', '2022-07-01 06:32:05', '2022-07-01 06:32:05'),
(41, 'type-delete', 'web', '2022-07-01 06:32:05', '2022-07-01 06:32:05'),
(42, 'strain-list', 'web', '2022-07-01 06:32:05', '2022-07-01 06:32:05'),
(43, 'strain-create', 'web', '2022-07-01 06:32:05', '2022-07-01 06:32:05'),
(44, 'strain-edit', 'web', '2022-07-01 06:32:05', '2022-07-01 06:32:05'),
(45, 'strain-delete', 'web', '2022-07-01 06:32:05', '2022-07-01 06:32:05'),
(46, 'filemanager-list', 'web', '2022-07-01 06:32:05', '2022-07-01 06:32:05'),
(47, 'filemanager-create', 'web', '2022-07-01 06:32:05', '2022-07-01 06:32:05'),
(48, 'filemanager-edit', 'web', '2022-07-01 06:32:05', '2022-07-01 06:32:05'),
(49, 'filemanager-delete', 'web', '2022-07-01 06:32:05', '2022-07-01 06:32:05'),
(50, 'doctor-list', 'web', '2022-07-01 06:32:05', '2022-07-01 06:32:05'),
(51, 'doctor-create', 'web', '2022-07-01 06:32:05', '2022-07-01 06:32:05'),
(52, 'doctor-edit', 'web', '2022-07-01 06:32:05', '2022-07-01 06:32:05'),
(53, 'doctor-delete', 'web', '2022-07-01 06:32:05', '2022-07-01 06:32:05'),
(54, 'state-list', 'web', '2022-07-01 06:32:05', '2022-07-01 06:32:05'),
(55, 'state-create', 'web', '2022-07-01 06:32:05', '2022-07-01 06:32:05'),
(56, 'state-edit', 'web', '2022-07-01 06:32:05', '2022-07-01 06:32:05'),
(57, 'state-delete', 'web', '2022-07-01 06:32:05', '2022-07-01 06:32:05');

-- --------------------------------------------------------

--
-- Table structure for table `plans`
--

CREATE TABLE `plans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `summary` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `duration_month` bigint(20) UNSIGNED DEFAULT 0,
  `amount` decimal(20,2) NOT NULL DEFAULT 0.00,
  `duration_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `status` enum('active','inactive','delete','out') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `plans`
--

INSERT INTO `plans` (`id`, `title`, `summary`, `photo`, `duration_month`, `amount`, `duration_text`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Monthly Subscription', NULL, '', 30, '4.00', 'Monthly', 'active', '2022-08-05 07:55:18', '2022-10-18 17:52:14');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `product_code` varchar(255) NOT NULL,
  `brand_id` bigint(20) NOT NULL DEFAULT 0,
  `product_sku` longtext DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `sub_parent_id` bigint(20) NOT NULL DEFAULT 0,
  `type_id` bigint(20) NOT NULL DEFAULT 0,
  `strain_id` bigint(20) NOT NULL DEFAULT 0,
  `sub_strain_id` bigint(20) NOT NULL DEFAULT 0,
  `amount` longtext DEFAULT NULL,
  `sub_amount` longtext DEFAULT NULL,
  `thc` longtext DEFAULT NULL,
  `cbd` longtext DEFAULT NULL,
  `dispensary_id` bigint(20) NOT NULL DEFAULT 0,
  `image` longtext DEFAULT NULL,
  `image_url` longtext DEFAULT NULL,
  `product_url` longtext DEFAULT NULL,
  `price_color_code` varchar(255) DEFAULT '#3AAA35',
  `name` longtext DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `short_description` mediumtext DEFAULT NULL,
  `qty` longtext DEFAULT NULL,
  `price` longtext DEFAULT NULL,
  `discount_price` longtext DEFAULT NULL,
  `manage_stock` longtext DEFAULT NULL COMMENT '''0'',''1''',
  `update_stock` enum('Yes','No') NOT NULL DEFAULT 'No',
  `product_out` enum('Yes','No') NOT NULL DEFAULT 'No',
  `is_featured` longtext DEFAULT NULL COMMENT '''0'',''1''',
  `status` enum('active','inactive','delete') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `product_favourites`
--

CREATE TABLE `product_favourites` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `device_id` varchar(191) DEFAULT NULL,
  `status` enum('active','inactive','delete') NOT NULL DEFAULT 'active',
  `is_user_status` enum('active','inactive','pause') NOT NULL DEFAULT 'active',
  `pause_status` enum('active','inactive') NOT NULL DEFAULT 'inactive',
  `pause_expire_time` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `product_types`
--

CREATE TABLE `product_types` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive','delete','out') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `pushs`
--

CREATE TABLE `pushs` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `title` mediumtext NOT NULL,
  `description` mediumtext NOT NULL,
  `is_send` int(11) NOT NULL DEFAULT 0,
  `status` enum('active','inactive','delete','out') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `push_user`
--

CREATE TABLE `push_user` (
  `id` int(11) NOT NULL,
  `push_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('active','inactive','out','delete') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'web', 'active', '2022-08-23 04:48:09', '2022-08-23 04:48:09'),
(2, 'User', 'web', 'active', '2022-07-01 06:42:10', '2022-07-01 06:42:10'),
(5, 'Test', 'web', 'inactive', '2022-07-05 07:38:55', '2022-07-21 04:31:50');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(2, 1),
(2, 5),
(3, 1),
(3, 5),
(4, 1),
(4, 5),
(5, 1),
(6, 1),
(7, 1),
(8, 1),
(9, 1),
(10, 1),
(11, 1),
(12, 1),
(13, 1),
(14, 1),
(15, 1),
(16, 1),
(17, 1),
(18, 1),
(19, 1),
(20, 1),
(21, 1),
(22, 1),
(23, 1),
(24, 1),
(25, 1),
(26, 1),
(27, 1),
(28, 1),
(29, 1),
(30, 1),
(31, 1),
(32, 1),
(33, 1),
(34, 1),
(35, 1),
(36, 1),
(37, 1),
(38, 1),
(39, 1),
(40, 1),
(41, 1),
(42, 1),
(43, 1),
(44, 1),
(45, 1),
(46, 1),
(47, 1),
(48, 1),
(49, 1),
(50, 1),
(51, 1),
(52, 1),
(53, 1),
(54, 1),
(55, 1),
(56, 1),
(57, 1);

-- --------------------------------------------------------

--
-- Table structure for table `states`
--

CREATE TABLE `states` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `image` varchar(250) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive','delete','out') NOT NULL DEFAULT 'active',
  `is_allow` enum('true','false') NOT NULL DEFAULT 'false',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `strains`
--

CREATE TABLE `strains` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `image` varchar(250) NOT NULL,
  `brand_id` bigint(20) DEFAULT NULL,
  `dispensary_id` bigint(20) DEFAULT NULL,
  `order_no` bigint(20) NOT NULL DEFAULT 0,
  `description` text NOT NULL,
  `status` enum('active','inactive','delete','out') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stripe_id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stripe_status` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stripe_price` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `trial_ends_at` timestamp NULL DEFAULT NULL,
  `ends_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subscription_items`
--

CREATE TABLE `subscription_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `subscription_id` bigint(20) UNSIGNED NOT NULL,
  `stripe_id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stripe_product` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stripe_price` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supports`
--

CREATE TABLE `supports` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive','delete','out') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'inactive',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` bigint(20) NOT NULL DEFAULT 0,
  `item_id` bigint(20) NOT NULL DEFAULT 0,
  `payment_method` varchar(255) DEFAULT '',
  `transaction_type` enum('plan','add','withdrwal') NOT NULL DEFAULT 'plan',
  `txn_id` varchar(255) DEFAULT '',
  `before_wallet_amount` decimal(20,2) NOT NULL DEFAULT 0.00,
  `after_wallet_amount` decimal(20,2) NOT NULL DEFAULT 0.00,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `title` varchar(255) DEFAULT '',
  `message` text DEFAULT NULL,
  `status` enum('active','inactive','delete','out') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `first_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mobile` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `plan_id` int(11) NOT NULL DEFAULT 0,
  `plan_expire_time` date DEFAULT NULL,
  `pause_expire_time` date DEFAULT NULL,
  `notification` enum('true','false') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'true',
  `email_alert` enum('true','false') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'true',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `google_id` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `google_signin` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `facebook_id` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `facebook_signin` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `apple_id` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `apple_signin` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `is_verified` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `subscription_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive','out','delete') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `is_admin` enum('Yes','No') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'No',
  `api_token` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `stripe_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pm_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pm_last_four` varchar(4) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `trial_ends_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `avatar`, `email`, `first_name`, `last_name`, `phone_code`, `mobile`, `plan_id`, `plan_expire_time`, `pause_expire_time`, `notification`, `email_alert`, `email_verified_at`, `password`, `remember_token`, `google_id`, `google_signin`, `facebook_id`, `facebook_signin`, `apple_id`, `apple_signin`, `is_verified`, `subscription_id`, `status`, `is_admin`, `api_token`, `created_at`, `updated_at`, `stripe_id`, `pm_type`, `pm_last_four`, `trial_ends_at`) VALUES
(1, 'Admin', 'noimage.jpg', 'info@mailinator.com', NULL, NULL, NULL, '+918888888888', 0, NULL, NULL, 'true', 'true', NULL, '$2y$10$zjM5hukcaKzALJySlMdeseBxvc1zE1KPZlo.NBNpwZ6Gq9BrfsAWC', 'RYgIfrFAK2phnAra3XtmHJBb0phgbnKckynWhpb8q8WI9L9efMf2h423gLt3', NULL, '0', NULL, '0', NULL, '0', '0', NULL, 'active', 'Yes', NULL, '2022-07-01 03:17:45', '2022-09-27 08:52:39', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_devices`
--

CREATE TABLE `user_devices` (
  `id` int(11) NOT NULL,
  `user_id` bigint(20) NOT NULL DEFAULT 0,
  `device_id` varchar(255) DEFAULT '',
  `device_token` tinytext DEFAULT NULL,
  `device_type` enum('android','ios') NOT NULL DEFAULT 'android',
  `device_sdk` tinytext DEFAULT NULL,
  `device_manufacture` tinytext DEFAULT NULL,
  `device_brand` tinytext DEFAULT NULL,
  `device_user` tinytext DEFAULT NULL,
  `device_base` tinytext DEFAULT NULL,
  `device_incremental` tinytext DEFAULT NULL,
  `device_board` tinytext DEFAULT NULL,
  `device_host` tinytext DEFAULT NULL,
  `device_finger` tinytext DEFAULT NULL,
  `device_version` tinytext DEFAULT NULL,
  `device_name` text DEFAULT NULL,
  `status` enum('active','inactive','delete','out') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `user_plans`
--

CREATE TABLE `user_plans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT 0,
  `plan_id` bigint(20) UNSIGNED DEFAULT 0,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(20,2) NOT NULL DEFAULT 0.00,
  `duration_month` bigint(20) NOT NULL DEFAULT 30,
  `duration_text` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Monthly',
  `plan_expire_time` date DEFAULT NULL,
  `status` enum('active','old') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bus_rule_ref`
--
ALTER TABLE `bus_rule_ref`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `cms`
--
ALTER TABLE `cms`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cms_faq_questions`
--
ALTER TABLE `cms_faq_questions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `custom_logs`
--
ALTER TABLE `custom_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `dispensaries`
--
ALTER TABLE `dispensaries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `doctors`
--
ALTER TABLE `doctors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `feedbacks`
--
ALTER TABLE `feedbacks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `filemanagers`
--
ALTER TABLE `filemanagers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `file_imports`
--
ALTER TABLE `file_imports`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `import_files`
--
ALTER TABLE `import_files`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `limitations`
--
ALTER TABLE `limitations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `notification_users`
--
ALTER TABLE `notification_users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `oauth_access_tokens`
--
ALTER TABLE `oauth_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_access_tokens_user_id_index` (`user_id`);

--
-- Indexes for table `oauth_auth_codes`
--
ALTER TABLE `oauth_auth_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_auth_codes_user_id_index` (`user_id`);

--
-- Indexes for table `oauth_clients`
--
ALTER TABLE `oauth_clients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_clients_user_id_index` (`user_id`);

--
-- Indexes for table `oauth_personal_access_clients`
--
ALTER TABLE `oauth_personal_access_clients`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `oauth_refresh_tokens`
--
ALTER TABLE `oauth_refresh_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_refresh_tokens_access_token_id_index` (`access_token_id`);

--
-- Indexes for table `otp`
--
ALTER TABLE `otp`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `plans`
--
ALTER TABLE `plans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subcategory_id` (`parent_id`) USING BTREE;

--
-- Indexes for table `product_favourites`
--
ALTER TABLE `product_favourites`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_types`
--
ALTER TABLE `product_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pushs`
--
ALTER TABLE `pushs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `push_user`
--
ALTER TABLE `push_user`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `states`
--
ALTER TABLE `states`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `strains`
--
ALTER TABLE `strains`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`brand_id`);

--
-- Indexes for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `subscriptions_stripe_id_unique` (`stripe_id`),
  ADD KEY `subscriptions_user_id_stripe_status_index` (`user_id`,`stripe_status`);

--
-- Indexes for table `subscription_items`
--
ALTER TABLE `subscription_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `subscription_items_subscription_id_stripe_price_unique` (`subscription_id`,`stripe_price`),
  ADD UNIQUE KEY `subscription_items_stripe_id_unique` (`stripe_id`);

--
-- Indexes for table `supports`
--
ALTER TABLE `supports`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_stripe_id_index` (`stripe_id`);

--
-- Indexes for table `user_devices`
--
ALTER TABLE `user_devices`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_plans`
--
ALTER TABLE `user_plans`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bus_rule_ref`
--
ALTER TABLE `bus_rule_ref`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `cms`
--
ALTER TABLE `cms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `cms_faq_questions`
--
ALTER TABLE `cms_faq_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `custom_logs`
--
ALTER TABLE `custom_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dispensaries`
--
ALTER TABLE `dispensaries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `doctors`
--
ALTER TABLE `doctors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `feedbacks`
--
ALTER TABLE `feedbacks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `filemanagers`
--
ALTER TABLE `filemanagers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `file_imports`
--
ALTER TABLE `file_imports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `import_files`
--
ALTER TABLE `import_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `limitations`
--
ALTER TABLE `limitations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `notification_users`
--
ALTER TABLE `notification_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oauth_clients`
--
ALTER TABLE `oauth_clients`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `oauth_personal_access_clients`
--
ALTER TABLE `oauth_personal_access_clients`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `otp`
--
ALTER TABLE `otp`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `plans`
--
ALTER TABLE `plans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_favourites`
--
ALTER TABLE `product_favourites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_types`
--
ALTER TABLE `product_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pushs`
--
ALTER TABLE `pushs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `push_user`
--
ALTER TABLE `push_user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `states`
--
ALTER TABLE `states`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `strains`
--
ALTER TABLE `strains`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subscription_items`
--
ALTER TABLE `subscription_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `supports`
--
ALTER TABLE `supports`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `user_devices`
--
ALTER TABLE `user_devices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_plans`
--
ALTER TABLE `user_plans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_3` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
