-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 16, 2026 at 04:40 PM
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
-- Database: `sales_crm_central`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
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
(4, '2026_10_12_194334_create_personal_access_tokens_table', 1),
(5, '2026_10_14_000001_create_tenants_table', 1),
(6, '2026_10_14_000002_create_super_admins_table', 1),
(7, '2026_10_16_000001_add_logo_url_to_tenants_table', 2);

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
('FzxFTp2TfpNuYKejyhMHNgHArrryC8ELK2omwnVY', NULL, '127.0.0.1', '', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoib3hjVzNaeUZLNG5NR2JrTmlHS0xiaUxwS3lsUXg2ODBlbll4UE1KeCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDM6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hZG1pbi91c2Vycy1kaXJlY3RvcnkiO3M6NToicm91dGUiO3M6MjE6ImFkbWluLnVzZXJzX2RpcmVjdG9yeSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1792159139),
('JyhnbbFLpzbCRMJtIHjTUtwLGmYWgEXqf0WzI3ad', NULL, '127.0.0.1', 'node', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoidm9VVFJLZElXdGc3TUs5UUpyRUdLVEJPdGwxWml4cGpBdWFHaG5mWiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDM6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hZG1pbi91c2Vycy1kaXJlY3RvcnkiO3M6NToicm91dGUiO3M6MjE6ImFkbWluLnVzZXJzX2RpcmVjdG9yeSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1792159157),
('P3qw3KT9uzGtECrF5pA6hk9bdCcFybpAFxuBDtil', NULL, '127.0.0.1', 'curl/8.21.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiSFdyajkxcTNtcDNQZ3duaXQ2bWZYNnFFb0xtd3JCSkdTTDdUbXpZbiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDM6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi91c2Vycy1kaXJlY3RvcnkiO3M6NToicm91dGUiO3M6MjE6ImFkbWluLnVzZXJzX2RpcmVjdG9yeSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1792161392),
('YhrxGsJikBH0Wm1v5TPaFCWGjlwUMQcvzJ59gZqS', NULL, '127.0.0.1', 'node', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiSWx5NkEzRjgzbFQxdzJIM29ZSVVhZEVBVkl5SzZQMDZ2MWdvanFVdiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1792159261),
('zbhIvrEMh4wYSbjwU7ncwetb2tpLB1XVS85mDSmD', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoid3Rmd2UwWVdNNXZOSjVTdGVmek9vNlgzd2ZtT3pZRmJuNjVxa3I2VCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDM6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hZG1pbi91c2Vycy1kaXJlY3RvcnkiO3M6NToicm91dGUiO3M6MjE6ImFkbWluLnVzZXJzX2RpcmVjdG9yeSI7fX0=', 1792161555);

-- --------------------------------------------------------

--
-- Table structure for table `super_admins`
--

CREATE TABLE `super_admins` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `super_admins`
--

INSERT INTO `super_admins` (`id`, `name`, `email`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Super Administrator', 'admin@dealsync.com', '$2y$04$cfLOUo553Btn1y45gh50PuWcmrzUSV01sWAJQ7eMrCi4VC6NuoYQu', NULL, '2026-10-14 09:41:08', '2026-10-16 09:05:11');

-- --------------------------------------------------------

--
-- Table structure for table `tenants`
--

CREATE TABLE `tenants` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `logo_url` longtext DEFAULT NULL,
  `legal_name` varchar(200) DEFAULT NULL,
  `domain` varchar(255) NOT NULL,
  `db_host` varchar(255) NOT NULL DEFAULT '127.0.0.1',
  `db_port` int(10) UNSIGNED NOT NULL DEFAULT 3306,
  `db_name` varchar(255) NOT NULL,
  `db_username` varchar(255) NOT NULL DEFAULT 'root',
  `db_password` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `plan_tier` varchar(50) NOT NULL DEFAULT 'enterprise',
  `max_seats` int(10) UNSIGNED NOT NULL DEFAULT 15,
  `branding_theme` varchar(50) NOT NULL DEFAULT 'apple',
  `accent_color` varchar(30) NOT NULL DEFAULT '#4F46E5',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tenants`
--

INSERT INTO `tenants` (`id`, `name`, `logo_url`, `legal_name`, `domain`, `db_host`, `db_port`, `db_name`, `db_username`, `db_password`, `is_active`, `plan_tier`, `max_seats`, `branding_theme`, `accent_color`, `created_at`, `updated_at`) VALUES
(2, 'DealSync Global Corp', 'https://example.com/branding-logo.png', 'DealSync Worldwide Operations Inc.', 'localhost', '127.0.0.1', 3306, 'crm_dealsync_central_demo_49shr', 'root', NULL, 1, 'enterprise', 53, 'apple', '#10B981', '2026-10-14 09:40:12', '2026-10-16 09:09:15'),
(19, 'test', NULL, 'Test', 'test.local', '127.0.0.1', 3306, 'crm_test_fldlr', 'root', NULL, 1, 'enterprise', 15, 'apple', '#4f46e5', '2026-10-15 15:27:43', '2026-10-15 15:27:43');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

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
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

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
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `super_admins`
--
ALTER TABLE `super_admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `super_admins_email_unique` (`email`);

--
-- Indexes for table `tenants`
--
ALTER TABLE `tenants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tenants_domain_unique` (`domain`),
  ADD UNIQUE KEY `tenants_db_name_unique` (`db_name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `super_admins`
--
ALTER TABLE `super_admins`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tenants`
--
ALTER TABLE `tenants`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
