-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 19, 2026 at 05:33 PM
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
-- Database: `crm_dealsync_central_demo_49shr`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'Customer',
  `industry` varchar(100) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `billing_address` text DEFAULT NULL,
  `shipping_address` text DEFAULT NULL,
  `payment_terms` varchar(60) DEFAULT NULL,
  `credit_limit` decimal(15,2) NOT NULL DEFAULT 0.00,
  `annual_revenue` decimal(15,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(10) NOT NULL DEFAULT 'USD',
  `assigned_to` varchar(150) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `custom_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_data`)),
  `owner_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `activities`
--

CREATE TABLE `activities` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `entity_type` varchar(60) NOT NULL,
  `entity_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(40) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `details` text DEFAULT NULL,
  `performed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `activity_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `approval_actions`
--

CREATE TABLE `approval_actions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `request_id` bigint(20) UNSIGNED NOT NULL,
  `step_number` int(10) UNSIGNED NOT NULL,
  `action` varchar(30) NOT NULL,
  `actor_id` bigint(20) UNSIGNED DEFAULT NULL,
  `actor_name` varchar(150) NOT NULL,
  `comments` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `approval_processes`
--

CREATE TABLE `approval_processes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `module` varchar(60) NOT NULL,
  `trigger_type` varchar(30) NOT NULL DEFAULT 'manual',
  `criteria_field` varchar(100) DEFAULT NULL,
  `criteria_operator` varchar(30) DEFAULT NULL,
  `criteria_value` text DEFAULT NULL,
  `notification_channel` varchar(30) NOT NULL DEFAULT 'both',
  `approval_actions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`approval_actions`)),
  `rejection_actions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`rejection_actions`)),
  `enable_auto_expiry` tinyint(1) NOT NULL DEFAULT 0,
  `auto_expiry_hours` int(10) UNSIGNED NOT NULL DEFAULT 72,
  `expiry_actions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`expiry_actions`)),
  `lock_record_until_approved` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `approval_requests`
--

CREATE TABLE `approval_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `process_id` bigint(20) UNSIGNED NOT NULL,
  `module` varchar(60) NOT NULL,
  `record_id` varchar(60) NOT NULL,
  `current_step` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `total_steps` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `expires_at` datetime DEFAULT NULL,
  `submitted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `submitter_name` varchar(150) NOT NULL,
  `submission_notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `approval_steps`
--

CREATE TABLE `approval_steps` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `process_id` bigint(20) UNSIGNED NOT NULL,
  `step_number` int(10) UNSIGNED NOT NULL,
  `step_name` varchar(100) NOT NULL,
  `approver_type` varchar(30) NOT NULL DEFAULT 'role',
  `approver_id` varchar(60) DEFAULT NULL,
  `approver_name` varchar(150) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(60) NOT NULL,
  `entity_type` varchar(60) NOT NULL,
  `entity_id` varchar(60) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `company_profile`
--

CREATE TABLE `company_profile` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `legal_name` varchar(150) DEFAULT NULL,
  `tax_id` varchar(60) DEFAULT NULL,
  `registration_number` varchar(60) DEFAULT NULL,
  `industry` varchar(100) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `phone` varchar(40) DEFAULT NULL,
  `billing_email` varchar(150) DEFAULT NULL,
  `support_email` varchar(150) DEFAULT NULL,
  `address_street` text DEFAULT NULL,
  `address_city` varchar(100) DEFAULT NULL,
  `address_state` varchar(100) DEFAULT NULL,
  `address_postal_code` varchar(30) DEFAULT NULL,
  `address_country` varchar(10) NOT NULL DEFAULT 'US',
  `currency_code` varchar(10) NOT NULL DEFAULT 'USD',
  `currency_symbol` varchar(10) NOT NULL DEFAULT '$',
  `currency_position` varchar(20) NOT NULL DEFAULT 'prefix',
  `time_zone` varchar(60) NOT NULL DEFAULT 'America/New_York',
  `date_format` varchar(30) NOT NULL DEFAULT 'YYYY-MM-DD',
  `time_format` varchar(10) NOT NULL DEFAULT '12h',
  `number_format` varchar(30) NOT NULL DEFAULT 'comma_dot',
  `fiscal_year_start_month` smallint(5) UNSIGNED NOT NULL DEFAULT 1,
  `working_days` varchar(100) NOT NULL DEFAULT 'Mon,Tue,Wed,Thu,Fri',
  `working_hours_start` varchar(10) NOT NULL DEFAULT '09:00',
  `working_hours_end` varchar(10) NOT NULL DEFAULT '18:00',
  `allow_self_password_change` tinyint(1) NOT NULL DEFAULT 1,
  `email_provider` varchar(40) NOT NULL DEFAULT 'custom',
  `smtp_host` varchar(150) DEFAULT NULL,
  `smtp_port` int(10) UNSIGNED NOT NULL DEFAULT 587,
  `smtp_user` varchar(150) DEFAULT NULL,
  `smtp_pass` text DEFAULT NULL,
  `smtp_secure` tinyint(1) NOT NULL DEFAULT 0,
  `smtp_status` varchar(30) NOT NULL DEFAULT 'unconfigured',
  `smtp_last_tested_at` timestamp NULL DEFAULT NULL,
  `email_from_name` varchar(150) DEFAULT NULL,
  `email_from_address` varchar(150) DEFAULT NULL,
  `email_reply_to` varchar(150) DEFAULT NULL,
  `sender_identity_policy` varchar(40) NOT NULL DEFAULT 'org_default',
  `enforce_legal_footer` tinyint(1) NOT NULL DEFAULT 1,
  `legal_footer_html` longtext DEFAULT NULL,
  `logo_url` text DEFAULT NULL,
  `branding_theme` varchar(30) NOT NULL DEFAULT 'apple',
  `accent_color` varchar(20) NOT NULL DEFAULT '#4F46E5',
  `page_bg_color` varchar(20) NOT NULL DEFAULT '#fafafa',
  `font_color` varchar(20) NOT NULL DEFAULT '#09090b',
  `header_bg_color` varchar(20) NOT NULL DEFAULT '#ffffff',
  `sidebar_bg_color` varchar(20) NOT NULL DEFAULT '#ffffff',
  `plan_tier` varchar(30) NOT NULL DEFAULT 'enterprise',
  `max_seats` int(10) UNSIGNED NOT NULL DEFAULT 15,
  `max_storage_mb` int(10) UNSIGNED NOT NULL DEFAULT 10240,
  `max_records` int(10) UNSIGNED NOT NULL DEFAULT 100000,
  `session_timeout_minutes` int(10) UNSIGNED NOT NULL DEFAULT 60,
  `password_min_length` int(10) UNSIGNED NOT NULL DEFAULT 8,
  `password_require_special` tinyint(1) NOT NULL DEFAULT 1,
  `password_require_numbers` tinyint(1) NOT NULL DEFAULT 1,
  `password_require_uppercase` tinyint(1) NOT NULL DEFAULT 1,
  `password_expiry_days` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `max_failed_login_attempts` int(10) UNSIGNED NOT NULL DEFAULT 5,
  `lockout_duration_minutes` int(10) UNSIGNED NOT NULL DEFAULT 30,
  `two_factor_policy` varchar(30) NOT NULL DEFAULT 'optional',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `table_bg_color` varchar(20) DEFAULT '#ffffff',
  `table_border_color` varchar(20) DEFAULT '#e2e8f0',
  `appearance_settings` longtext DEFAULT NULL,
  `inbound_protocol` varchar(40) DEFAULT 'graph',
  `inbound_frequency` varchar(20) DEFAULT '5m',
  `inbound_auto_tag_leads` tinyint(1) NOT NULL DEFAULT 1,
  `inbound_sync_enabled` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `company_profile`
--

INSERT INTO `company_profile` (`id`, `name`, `legal_name`, `tax_id`, `registration_number`, `industry`, `website`, `phone`, `billing_email`, `support_email`, `address_street`, `address_city`, `address_state`, `address_postal_code`, `address_country`, `currency_code`, `currency_symbol`, `currency_position`, `time_zone`, `date_format`, `time_format`, `number_format`, `fiscal_year_start_month`, `working_days`, `working_hours_start`, `working_hours_end`, `allow_self_password_change`, `email_provider`, `smtp_host`, `smtp_port`, `smtp_user`, `smtp_pass`, `smtp_secure`, `smtp_status`, `smtp_last_tested_at`, `email_from_name`, `email_from_address`, `email_reply_to`, `sender_identity_policy`, `enforce_legal_footer`, `legal_footer_html`, `logo_url`, `branding_theme`, `accent_color`, `page_bg_color`, `font_color`, `header_bg_color`, `sidebar_bg_color`, `plan_tier`, `max_seats`, `max_storage_mb`, `max_records`, `session_timeout_minutes`, `password_min_length`, `password_require_special`, `password_require_numbers`, `password_require_uppercase`, `password_expiry_days`, `max_failed_login_attempts`, `lockout_duration_minutes`, `two_factor_policy`, `created_at`, `updated_at`, `table_bg_color`, `table_border_color`, `appearance_settings`, `inbound_protocol`, `inbound_frequency`, `inbound_auto_tag_leads`, `inbound_sync_enabled`) VALUES
(1, 'DealSync Global Corp', 'DealSync Worldwide Operations Inc.', 'US-EIN-112233445', 'CR-09871234', 'Financial Technology & Cloud', 'https://fintech.example.com', '+1 (800) 555-0199', 'billing@dealsync.example.com', 'support@dealsync.example.com', '500 Silicon Vista Blvd', 'San Francisco', 'CA', '94107', 'US', 'USD', '$', 'prefix', 'America/Los_Angeles', 'YYYY-MM-DD', '24h', 'comma_dot', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00', '18:00', 0, 'sendgrid', 'smtp.sendgrid.net', 587, 'apikey_updated', 'SG.secret_key_123', 0, 'verified', '2026-10-19 08:55:07', 'Enterprise Deal Notifications', 'deals@dealsync.com', 'support@dealsync.example.com', 'org_default', 1, '<div style=\"font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif; font-size: 11px; line-height: 1.5; color: #64748b; padding-top: 14px; border-top: 1px solid #e2e8f0; margin-top: 20px;\">\n  <p style=\"margin: 0 0 4px 0; color: #334155; font-weight: 600;\">© 2026 Updated Legal Name Inc. &bull; Tax/VAT ID: US-EIN-987654321</p>\n  <p style=\"margin: 0 0 6px 0;\">100 Technology Park, Suite 400, San Francisco, CA 94107, United States</p>\n  <p style=\"margin: 0; color: #94a3b8;\">\n    You are receiving this communication regarding your account or transaction with DealSync Central Demo. \n    <a href=\"{{unsubscribe_link}}\" style=\"color: #4f46e5; text-decoration: underline;\">Manage email preferences or unsubscribe</a>.\n  </p>\n</div>', NULL, 'apple', '#10B981', '#fafafa', '#1e293b', '#ffffff', '#ffffff', 'enterprise', 103, 10240, 100000, 120, 12, 1, 1, 1, 90, 3, 60, 'admin_enforced', '2026-10-14 11:49:55', '2026-10-19 08:59:15', '#ffffff', '#e2e8f0', '{\"page\":{\"mode\":\"color\",\"color\":\"#fafafa\",\"imageUrl\":\"https://images.unsplash.com/photo-custom-wallpaper?w=1200\",\"opacity\":85,\"brightness\":90,\"contrast\":100,\"softness\":0,\"fit\":\"cover\"},\"table\":{\"mode\":\"color\",\"color\":\"#ffffff\",\"imageUrl\":\"https://images.unsplash.com/photo-custom-wallpaper?w=1200\",\"opacity\":90,\"brightness\":100,\"contrast\":100,\"softness\":0,\"fit\":\"cover\"},\"header\":{\"mode\":\"color\",\"color\":\"#ffffff\",\"imageUrl\":\"\",\"opacity\":100,\"brightness\":100,\"contrast\":100,\"softness\":0,\"fit\":\"cover\"},\"sidebar\":{\"mode\":\"color\",\"color\":\"#ffffff\",\"imageUrl\":\"\",\"opacity\":100,\"brightness\":100,\"contrast\":100,\"softness\":0,\"fit\":\"cover\"}}', 'graph', '5m', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `account_id` bigint(20) UNSIGNED DEFAULT NULL,
  `account_name` varchar(150) DEFAULT NULL,
  `first_name` varchar(80) NOT NULL,
  `last_name` varchar(80) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `contact_role` varchar(60) NOT NULL DEFAULT 'Decision Maker',
  `email_opt_out` tinyint(1) NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `custom_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_data`)),
  `assigned_to` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `custom_buttons`
--

CREATE TABLE `custom_buttons` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `entity_type` varchar(60) NOT NULL,
  `name` varchar(100) NOT NULL,
  `label` varchar(150) NOT NULL,
  `icon` varchar(50) NOT NULL DEFAULT 'Mail',
  `style_variant` varchar(30) NOT NULL DEFAULT 'secondary',
  `action_type` varchar(50) NOT NULL,
  `action_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`action_config`)),
  `placement` varchar(40) NOT NULL DEFAULT 'record_header',
  `visibility_conditions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`visibility_conditions`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `custom_buttons`
--

INSERT INTO `custom_buttons` (`id`, `entity_type`, `name`, `label`, `icon`, `style_variant`, `action_type`, `action_config`, `placement`, `visibility_conditions`, `is_active`, `display_order`, `created_at`, `updated_at`) VALUES
(1, 'leads', 'send_welcome_kit', 'Send Welcome Kit', 'Mail', 'indigo', 'send_email', '{\"template_id\":\"tmpl_welcome\",\"execution_mode\":\"composer\",\"default_recipient_field\":\"email\"}', 'record_header', '[]', 1, 1, '2026-10-16 13:02:54', '2026-10-16 13:02:54'),
(2, 'leads', 'qualify_lead', 'Qualify Lead', 'CheckCircle', 'success', 'workflow_trigger', '{\"trigger_type\":\"field_update\",\"field_updates\":{\"status\":\"Qualified\"},\"success_message\":\"Lead has been successfully qualified!\"}', 'record_header', '[{\"field\":\"status\",\"operator\":\"not_equals\",\"value\":\"Qualified\"}]', 1, 2, '2026-10-16 13:02:54', '2026-10-16 13:02:54'),
(3, 'leads', 'verify_profile', 'Verify Profile', 'Globe', 'primary', 'url_redirect', '{\"url_template\":\"https:\\/\\/www.google.com\\/search?q={{company}}+{{first_name}}+{{last_name}}\",\"open_target\":\"_blank\"}', 'record_header', '[]', 1, 3, '2026-10-16 13:02:54', '2026-10-16 13:02:54'),
(4, 'opportunities', 'send_proposal', 'Send Proposal', 'Send', 'primary', 'send_email', '{\"template_id\":\"tmpl_quote\",\"execution_mode\":\"composer\",\"default_recipient_field\":\"email\"}', 'record_header', '[]', 1, 1, '2026-10-16 13:02:54', '2026-10-16 13:02:54'),
(5, 'opportunities', 'weight_dims_cargo', 'Weight & Dimensions', 'Box', 'purple', 'quick_action', '{\"component\":\"add_weight_dims\",\"default_weight_unit\":\"LBS\",\"default_dim_unit\":\"INCH\",\"description\":\"Opens Weight & Dimensions table specification studio\"}', 'record_header', '[]', 1, 2, '2026-10-16 13:02:54', '2026-10-16 13:02:54'),
(6, 'opportunities', 'mark_closed_won', 'Mark as Closed Won', 'CheckCircle', 'success', 'workflow_trigger', '{\"trigger_type\":\"field_update\",\"field_updates\":{\"stage\":\"Closed Won\"},\"success_message\":\"Opportunity marked as Closed Won!\"}', 'record_header', '[{\"field\":\"stage\",\"operator\":\"not_equals\",\"value\":\"Closed Won\"}]', 1, 3, '2026-10-16 13:02:54', '2026-10-16 13:02:54'),
(7, 'shipping', 'track_freight', 'Track Freight', 'ExternalLink', 'indigo', 'url_redirect', '{\"url_template\":\"https:\\/\\/www.google.com\\/search?q={{carrier}}+tracking+{{tracking_number}}\",\"open_target\":\"_blank\"}', 'record_header', '[]', 1, 1, '2026-10-16 13:02:54', '2026-10-16 13:02:54'),
(8, 'shipping', 'cargo_specifications', 'Cargo Specifications', 'Package', 'amber', 'quick_action', '{\"component\":\"add_weight_dims\",\"default_weight_unit\":\"KG\",\"default_dim_unit\":\"CM\",\"description\":\"Opens freight dimensions and packing manifest\"}', 'record_header', '[]', 1, 2, '2026-10-16 13:02:54', '2026-10-16 13:02:54'),
(9, 'purchase_deals', 'po_cargo_specs', 'PO Cargo Specs', 'Scale', 'indigo', 'quick_action', '{\"component\":\"add_weight_dims\",\"default_weight_unit\":\"KG\",\"default_dim_unit\":\"CM\"}', 'record_header', '[]', 1, 1, '2026-10-16 13:02:54', '2026-10-16 13:02:54'),
(10, 'purchase_deals', 'approve_po', 'Approve PO', 'CheckCircle', 'success', 'workflow_trigger', '{\"trigger_type\":\"field_update\",\"field_updates\":{\"stage\":\"Approved\"},\"success_message\":\"Purchase order approved!\"}', 'record_header', '[{\"field\":\"stage\",\"operator\":\"not_equals\",\"value\":\"Approved\"}]', 1, 2, '2026-10-16 13:02:54', '2026-10-16 13:02:54');

-- --------------------------------------------------------

--
-- Table structure for table `custom_fields`
--

CREATE TABLE `custom_fields` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `module` varchar(60) NOT NULL,
  `field_name` varchar(100) NOT NULL,
  `field_label` varchar(150) NOT NULL,
  `data_type` varchar(50) NOT NULL,
  `options` text DEFAULT NULL,
  `is_required` tinyint(1) NOT NULL DEFAULT 0,
  `default_value` text DEFAULT NULL,
  `help_text` text DEFAULT NULL,
  `lookup_module` varchar(60) DEFAULT NULL,
  `lookup_display_field` varchar(100) DEFAULT NULL,
  `formula_mode` varchar(30) DEFAULT NULL,
  `formula_source_field` varchar(100) DEFAULT NULL,
  `formula_target_field` varchar(100) DEFAULT NULL,
  `formula_expression` text DEFAULT NULL,
  `formula_return_type` varchar(30) DEFAULT NULL,
  `global_picklist_id` bigint(20) UNSIGNED DEFAULT NULL,
  `is_multiselect` tinyint(1) NOT NULL DEFAULT 0,
  `autonumber_prefix` varchar(30) DEFAULT NULL,
  `autonumber_suffix` varchar(30) DEFAULT NULL,
  `autonumber_padding` int(10) UNSIGNED NOT NULL DEFAULT 4,
  `autonumber_start_num` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `autonumber_next_val` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `rollup_child_module` varchar(60) DEFAULT NULL,
  `rollup_relationship_field` varchar(100) DEFAULT NULL,
  `rollup_type` varchar(30) DEFAULT NULL,
  `rollup_target_field` varchar(100) DEFAULT NULL,
  `rollup_filter_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`rollup_filter_json`)),
  `rating_max` int(10) UNSIGNED NOT NULL DEFAULT 5,
  `rating_icon` varchar(30) NOT NULL DEFAULT 'star',
  `percentage_precision` int(10) UNSIGNED NOT NULL DEFAULT 2,
  `percentage_min` decimal(8,2) DEFAULT NULL,
  `percentage_max` decimal(8,2) DEFAULT NULL,
  `radio_layout` varchar(30) NOT NULL DEFAULT 'horizontal',
  `masked_pattern` varchar(30) NOT NULL DEFAULT 'last4',
  `color_presets` text DEFAULT NULL,
  `rich_text_height` int(10) UNSIGNED NOT NULL DEFAULT 160,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `custom_fields`
--

INSERT INTO `custom_fields` (`id`, `module`, `field_name`, `field_label`, `data_type`, `options`, `is_required`, `default_value`, `help_text`, `lookup_module`, `lookup_display_field`, `formula_mode`, `formula_source_field`, `formula_target_field`, `formula_expression`, `formula_return_type`, `global_picklist_id`, `is_multiselect`, `autonumber_prefix`, `autonumber_suffix`, `autonumber_padding`, `autonumber_start_num`, `autonumber_next_val`, `rollup_child_module`, `rollup_relationship_field`, `rollup_type`, `rollup_target_field`, `rollup_filter_json`, `rating_max`, `rating_icon`, `percentage_precision`, `percentage_min`, `percentage_max`, `radio_layout`, `masked_pattern`, `color_presets`, `rich_text_height`, `created_at`, `updated_at`) VALUES
(1, 'leads', 'cf_industry_subsector', 'Industry Sub-Sector', 'Dropdown', 'Enterprise SaaS, FinTech & Banking, HealthTech, AI / Automation, Logistics & Cargo', 0, NULL, 'Granular vertical industry categorization for targeted qualification', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 4, 1, 1, NULL, NULL, NULL, NULL, NULL, 5, 'star', 2, NULL, NULL, 'horizontal', 'last4', NULL, 160, '2026-10-16 11:17:52', '2026-10-16 11:17:52'),
(2, 'leads', 'cf_procurement_fit_score', 'Procurement Fit Score', 'Rating', NULL, 0, NULL, 'Visual 1 to 5 propensity rating based on vendor eligibility', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 4, 1, 1, NULL, NULL, NULL, NULL, NULL, 5, 'star', 2, NULL, NULL, 'horizontal', 'last4', NULL, 160, '2026-10-16 11:17:52', '2026-10-16 11:17:52'),
(3, 'leads', 'cf_tax_id_masked', 'Tax Identification (EIN)', 'MaskedText', NULL, 0, NULL, 'Encrypted corporate tax identifier showing only the last 4 digits', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 4, 1, 1, NULL, NULL, NULL, NULL, NULL, 5, 'star', 2, NULL, NULL, 'horizontal', 'last4', NULL, 160, '2026-10-16 11:17:52', '2026-10-16 11:17:52'),
(4, 'accounts', 'cf_annual_contract_value', 'Annual Contract Value (ACV)', 'Currency', NULL, 0, NULL, 'Committed recurring contract spend in USD across active subscriptions', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 4, 1, 1, NULL, NULL, NULL, NULL, NULL, 5, 'star', 2, NULL, NULL, 'horizontal', 'last4', NULL, 160, '2026-10-16 11:17:52', '2026-10-16 11:17:52'),
(5, 'accounts', 'cf_procurement_sla_tier', 'Procurement SLA Tier', 'Dropdown', 'Platinum Priority (4h), Gold Standard (8h), Silver (24h), Bronze Standard', 1, NULL, 'Operational service level response agreement with client or vendor', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 4, 1, 1, NULL, NULL, NULL, NULL, NULL, 5, 'star', 2, NULL, NULL, 'horizontal', 'last4', NULL, 160, '2026-10-16 11:17:52', '2026-10-16 11:17:52'),
(6, 'accounts', 'cf_open_deals_count', 'Active Pipeline Deals Count', 'RollupSummary', NULL, 0, NULL, 'Dynamic rollup counting open opportunities belonging to this account', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 4, 1, 1, 'opportunities', 'account_id', 'COUNT', NULL, NULL, 5, 'star', 2, NULL, NULL, 'horizontal', 'last4', NULL, 160, '2026-10-16 11:17:52', '2026-10-16 11:17:52'),
(7, 'opportunities', 'cf_deal_registration_code', 'Deal Registration Code', 'AutoNumber', NULL, 0, NULL, 'Unique auto-generated enterprise deal registration code', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'OPP-REG-', '', 5, 1001, 1, NULL, NULL, NULL, NULL, NULL, 5, 'star', 2, NULL, NULL, 'horizontal', 'last4', NULL, 160, '2026-10-16 11:17:52', '2026-10-16 11:17:52'),
(8, 'opportunities', 'cf_margin_percentage', 'Target Gross Margin %', 'Percentage', NULL, 0, NULL, 'Calculated margin percentage targeted on opportunity closing', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 4, 1, 1, NULL, NULL, NULL, NULL, NULL, 5, 'star', 2, 0.00, 100.00, 'horizontal', 'last4', NULL, 160, '2026-10-16 11:17:52', '2026-10-16 11:17:52'),
(9, 'opportunities', 'cf_priority_status_color', 'Executive Priority Color', 'ColorPicker', NULL, 0, NULL, 'Color-coded visual indicator for executive radar prioritization', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 4, 1, 1, NULL, NULL, NULL, NULL, NULL, 5, 'star', 2, NULL, NULL, 'horizontal', 'last4', '#3b82f6,#10b981,#f59e0b,#ef4444,#8b5cf6,#06b6d4', 160, '2026-10-16 11:17:52', '2026-10-16 11:17:52'),
(10, 'opportunities', 'cf_special_terms_notes', 'Special Contract Rider Terms', 'RichText', NULL, 0, NULL, 'WYSIWYG formatted legal and commercial exceptions or non-standard terms', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 4, 1, 1, NULL, NULL, NULL, NULL, NULL, 5, 'star', 2, NULL, NULL, 'horizontal', 'last4', NULL, 140, '2026-10-16 11:17:52', '2026-10-16 11:17:52');

-- --------------------------------------------------------

--
-- Table structure for table `custom_field_values`
--

CREATE TABLE `custom_field_values` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `module` varchar(60) NOT NULL,
  `record_id` varchar(60) NOT NULL,
  `custom_field_id` bigint(20) UNSIGNED NOT NULL,
  `value` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `custom_objects`
--

CREATE TABLE `custom_objects` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `api_name` varchar(100) NOT NULL,
  `label` varchar(150) NOT NULL,
  `plural_label` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `primary_field` varchar(100) NOT NULL DEFAULT 'name',
  `icon` varchar(50) NOT NULL DEFAULT 'Box',
  `created_by` varchar(100) NOT NULL DEFAULT 'System Admin',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `custom_objects`
--

INSERT INTO `custom_objects` (`id`, `api_name`, `label`, `plural_label`, `description`, `primary_field`, `icon`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Asset__c', 'IT Hardware Asset', 'Hardware Assets', 'Enterprise computing hardware, field laptops, network routers, and cloud server nodes.', 'name', 'HardDrive', 'System Admin', '2026-10-16 11:57:19', '2026-10-16 11:57:19'),
(2, 'Warranty__c', 'Service Warranty', 'Service Warranties', 'OEM service contracts, extended coverage, and guaranteed hardware replacement SLAs.', 'name', 'Shield', 'System Admin', '2026-10-16 11:57:19', '2026-10-16 11:57:19'),
(3, 'Inspection__c', 'Safety Inspection', 'Safety Inspections', 'Quarterly datacenter compliance, electrical redundancy, and environmental audits.', 'name', 'Wrench', 'System Admin', '2026-10-16 11:57:19', '2026-10-16 11:57:19');

-- --------------------------------------------------------

--
-- Table structure for table `custom_object_records`
--

CREATE TABLE `custom_object_records` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `custom_object_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(200) NOT NULL,
  `attributes_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`attributes_json`)),
  `created_by` varchar(100) NOT NULL DEFAULT 'System Admin',
  `updated_by` varchar(100) NOT NULL DEFAULT 'System Admin',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `custom_object_records`
--

INSERT INTO `custom_object_records` (`id`, `custom_object_id`, `name`, `attributes_json`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 1, 'MacBook Pro 16\" M3 Max (Dev Fleet #042)', '{\"notes\":\"Assigned to Senior Lead Architect. Serial: MBP-992384-US. 64GB Unified RAM, 2TB NVMe.\",\"department\":\"Engineering Core\",\"serial_number\":\"MBP-992384-US\",\"status\":\"Active Deployed\"}', 'System Admin', 'System Admin', '2026-10-16 11:57:19', '2026-10-16 11:57:19'),
(2, 1, 'Dell PowerEdge R750 Rack Server', '{\"notes\":\"Primary production database replica in Ashburn DC-2. Dual Xeon Platinum 8380.\",\"department\":\"Infrastructure & Ops\",\"serial_number\":\"SRV-DL-88319\",\"status\":\"In Production\"}', 'System Admin', 'System Admin', '2026-10-16 11:57:19', '2026-10-16 11:57:19'),
(3, 1, 'Cisco Meraki MX95 Security Gateway', '{\"notes\":\"HQ core branch firewall & site-to-site SD-WAN concentrator. Throughput: 2 Gbps.\",\"department\":\"NetOps Security\",\"serial_number\":\"CSCO-MK-00129\",\"status\":\"Active Deployed\"}', 'System Admin', 'System Admin', '2026-10-16 11:57:19', '2026-10-16 11:57:19'),
(4, 2, 'Dell ProSupport Plus 3-Year 24/7 NBD', '{\"notes\":\"Enterprise SLA covering mission-critical server hardware with 4-hour onsite response.\",\"sla_tier\":\"Platinum 24\\/7\",\"coverage_type\":\"Hardware + Onsite Dispatch\"}', 'System Admin', 'System Admin', '2026-10-16 11:57:19', '2026-10-16 11:57:19'),
(5, 2, 'AppleCare+ for Enterprise (Tier 1)', '{\"notes\":\"Next-day onsite repairs, priority 24\\/7 IT helpdesk, and up to 2% buffer unit replacement.\",\"sla_tier\":\"Tier 1 Enterprise\",\"coverage_type\":\"Accidental Damage + Rapid Swap\"}', 'System Admin', 'System Admin', '2026-10-16 11:57:19', '2026-10-16 11:57:19'),
(6, 3, 'Q3 Data Center Environmental & Cooling Audit', '{\"notes\":\"All HVAC containment cooling zones passed ASHRAE TC 9.9 thermal regulations with zero hotspot alerts.\",\"passed\":\"Yes\",\"auditor\":\"Bureau Veritas\"}', 'System Admin', 'System Admin', '2026-10-16 11:57:19', '2026-10-16 11:57:19');

-- --------------------------------------------------------

--
-- Table structure for table `dashboard_layouts`
--

CREATE TABLE `dashboard_layouts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `profile_id` bigint(20) UNSIGNED NOT NULL,
  `widgets` longtext NOT NULL,
  `sections` longtext NOT NULL,
  `updated_by` varchar(100) NOT NULL DEFAULT 'System Admin',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `dashboard_layouts`
--

INSERT INTO `dashboard_layouts` (`id`, `profile_id`, `widgets`, `sections`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 1, '[{\"id\":\"widget-arr-test\",\"type\":\"kpi\",\"title\":\"Annual Recurring Revenue (ARR)\",\"colSpan\":4,\"height\":\"compact\",\"palette\":\"violet\",\"sectionId\":\"sec-revenue\"},{\"id\":\"widget-trajectory-test\",\"type\":\"area\",\"title\":\"ARR Multi-Trajectory Forecast\",\"colSpan\":10,\"height\":\"standard\",\"palette\":\"cyan\",\"sectionId\":\"sec-pipeline\"}]', '[{\"id\":\"sec-revenue\",\"title\":\"Executive Revenue & Quota Attainment\",\"subtitle\":\"Primary revenue run-rate and targets\",\"collapsed\":false},{\"id\":\"sec-pipeline\",\"title\":\"Pipeline Health & Telemetry\",\"subtitle\":\"Active opportunities and velocity\",\"collapsed\":false}]', 'System Administrator', '2026-10-14 11:32:27', '2026-10-14 11:32:27');

-- --------------------------------------------------------

--
-- Table structure for table `data_operation_logs`
--

CREATE TABLE `data_operation_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `operation_type` varchar(50) NOT NULL,
  `module` varchar(60) NOT NULL,
  `records_affected` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `summary` text NOT NULL,
  `criteria_json` longtext DEFAULT NULL,
  `performed_by_id` bigint(20) UNSIGNED DEFAULT NULL,
  `performed_by_name` varchar(150) DEFAULT NULL,
  `is_reverted` tinyint(1) NOT NULL DEFAULT 0,
  `reverted_at` timestamp NULL DEFAULT NULL,
  `reverted_by_name` varchar(150) DEFAULT NULL,
  `success_details_json` longtext DEFAULT NULL,
  `error_details_json` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `data_operation_snapshots`
--

CREATE TABLE `data_operation_snapshots` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `operation_id` bigint(20) UNSIGNED NOT NULL,
  `operation_type` varchar(50) NOT NULL,
  `module` varchar(60) NOT NULL,
  `record_id` varchar(60) NOT NULL,
  `previous_state_json` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_governance_rights`
--

CREATE TABLE `document_governance_rights` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `profile_id` bigint(20) UNSIGNED NOT NULL,
  `can_generate_from_opportunity` tinyint(1) NOT NULL DEFAULT 1,
  `opportunity_required_stage` varchar(60) NOT NULL DEFAULT 'Closed Won',
  `opportunity_condition_json` text DEFAULT NULL,
  `can_generate_from_deal` tinyint(1) NOT NULL DEFAULT 1,
  `deal_required_stage` varchar(60) NOT NULL DEFAULT 'Closed Won',
  `deal_condition_json` text DEFAULT NULL,
  `can_direct_create_in_ledger` tinyint(1) NOT NULL DEFAULT 0,
  `allowed_ledger_types` varchar(40) NOT NULL DEFAULT 'all',
  `ledger_record_scope` varchar(30) NOT NULL DEFAULT 'all',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_line_items`
--

CREATE TABLE `document_line_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `document_id` bigint(20) UNSIGNED NOT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `item_type` varchar(30) NOT NULL DEFAULT 'product',
  `sku` varchar(100) DEFAULT NULL,
  `description` text NOT NULL,
  `hsn_sac_code` varchar(50) DEFAULT NULL,
  `quantity` decimal(12,4) NOT NULL DEFAULT 1.0000,
  `uom` varchar(30) NOT NULL DEFAULT 'pcs',
  `unit_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `discount_rate` decimal(8,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tax_rate` decimal(8,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `custom_fields_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields_json`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_snapshots`
--

CREATE TABLE `document_snapshots` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `document_id` bigint(20) UNSIGNED NOT NULL,
  `template_snapshot_json` longtext NOT NULL,
  `document_data_json` longtext NOT NULL,
  `line_items_json` longtext NOT NULL,
  `rendered_html` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_templates`
--

CREATE TABLE `document_templates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `document_type` varchar(30) NOT NULL,
  `description` text DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `paper_size` varchar(20) NOT NULL DEFAULT 'A4',
  `orientation` varchar(20) NOT NULL DEFAULT 'portrait',
  `margin_size` varchar(20) NOT NULL DEFAULT 'standard',
  `font_family` varchar(100) NOT NULL DEFAULT 'Inter, sans-serif',
  `primary_color` varchar(30) NOT NULL DEFAULT '#0f172a',
  `accent_color` varchar(30) NOT NULL DEFAULT '#2563eb',
  `table_theme` varchar(30) NOT NULL DEFAULT 'zebra',
  `layout_style` varchar(40) NOT NULL DEFAULT 'modern_teal',
  `logo_url` text DEFAULT NULL,
  `logo_position` varchar(20) NOT NULL DEFAULT 'left',
  `logo_height` int(10) UNSIGNED NOT NULL DEFAULT 48,
  `company_name` varchar(150) DEFAULT NULL,
  `company_address` text DEFAULT NULL,
  `company_tax_id` varchar(60) DEFAULT NULL,
  `company_phone` varchar(40) DEFAULT NULL,
  `company_email` varchar(150) DEFAULT NULL,
  `company_website` varchar(255) DEFAULT NULL,
  `document_title` varchar(150) NOT NULL,
  `watermark_config` longtext DEFAULT NULL,
  `columns_config` longtext DEFAULT NULL,
  `dynamic_fields_config` longtext DEFAULT NULL,
  `payment_terms_default` varchar(60) NOT NULL DEFAULT 'Net 30',
  `bank_details` text DEFAULT NULL,
  `qr_code_url` text DEFAULT NULL,
  `notes_default` text DEFAULT NULL,
  `terms_conditions` text DEFAULT NULL,
  `show_signatory` tinyint(1) NOT NULL DEFAULT 1,
  `signatory_title` varchar(100) NOT NULL DEFAULT 'Authorized Signatory',
  `signatory_name` varchar(150) DEFAULT NULL,
  `signature_image_url` text DEFAULT NULL,
  `show_shipping_address` tinyint(1) NOT NULL DEFAULT 1,
  `agreement_pages` longtext DEFAULT NULL,
  `save_to_files_destination` varchar(40) NOT NULL DEFAULT 'files_public',
  `allow_direct_email` tinyint(1) NOT NULL DEFAULT 1,
  `requires_approval` tinyint(1) NOT NULL DEFAULT 0,
  `approval_process_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by` varchar(100) NOT NULL DEFAULT 'System Admin',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `duplicate_exception_domains`
--

CREATE TABLE `duplicate_exception_domains` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `domain` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `created_by` varchar(100) NOT NULL DEFAULT 'System Admin',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `duplicate_record_sets`
--

CREATE TABLE `duplicate_record_sets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `rule_id` bigint(20) UNSIGNED NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'Pending',
  `source_module` varchar(60) NOT NULL,
  `target_module` varchar(60) NOT NULL,
  `primary_record_id` varchar(60) NOT NULL,
  `secondary_record_id` varchar(60) NOT NULL,
  `match_score` int(10) UNSIGNED NOT NULL DEFAULT 100,
  `match_details_json` longtext DEFAULT NULL,
  `detected_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `merged_at` timestamp NULL DEFAULT NULL,
  `merged_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `duplicate_rules`
--

CREATE TABLE `duplicate_rules` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `source_module` varchar(60) NOT NULL,
  `target_module` varchar(60) NOT NULL,
  `match_logic` varchar(10) NOT NULL DEFAULT 'OR',
  `action` varchar(30) NOT NULL DEFAULT 'Alert',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `auto_merge_strategy` varchar(40) NOT NULL DEFAULT 'manual_only',
  `created_by` varchar(100) NOT NULL DEFAULT 'System Admin',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `duplicate_rule_conditions`
--

CREATE TABLE `duplicate_rule_conditions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `rule_id` bigint(20) UNSIGNED NOT NULL,
  `source_field` varchar(100) NOT NULL,
  `target_field` varchar(100) NOT NULL,
  `match_type` varchar(30) NOT NULL DEFAULT 'Exact',
  `fuzzy_algorithm` varchar(40) DEFAULT NULL,
  `fuzzy_threshold` int(10) UNSIGNED NOT NULL DEFAULT 80,
  `normalization` varchar(40) NOT NULL DEFAULT 'none',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_rules`
--

CREATE TABLE `email_rules` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `module` varchar(60) NOT NULL,
  `email_template_id` bigint(20) UNSIGNED NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `trigger_type` varchar(30) NOT NULL DEFAULT 'scheduled',
  `date_field` varchar(60) DEFAULT NULL,
  `offset_direction` varchar(20) NOT NULL DEFAULT 'before',
  `offset_value` int(11) NOT NULL DEFAULT 0,
  `offset_unit` varchar(20) NOT NULL DEFAULT 'hours',
  `criteria_field` varchar(60) DEFAULT NULL,
  `criteria_operator` varchar(30) NOT NULL DEFAULT 'equals',
  `criteria_value` text DEFAULT NULL,
  `recipient_type` varchar(40) NOT NULL DEFAULT 'record_owner',
  `recipient_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`recipient_ids`)),
  `recipient_profile_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`recipient_profile_ids`)),
  `cc_emails` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_rule_executions`
--

CREATE TABLE `email_rule_executions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `rule_id` bigint(20) UNSIGNED NOT NULL,
  `module` varchar(60) NOT NULL,
  `record_id` varchar(60) NOT NULL,
  `executed_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_templates`
--

CREATE TABLE `email_templates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `category` varchar(60) NOT NULL DEFAULT 'general',
  `entity_type` varchar(60) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `body_html` longtext NOT NULL,
  `body_text` text DEFAULT NULL,
  `attached_file_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`attached_file_ids`)),
  `dynamic_file_sources` varchar(60) NOT NULL DEFAULT 'none',
  `dynamic_file_keywords` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`dynamic_file_keywords`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `field_history`
--

CREATE TABLE `field_history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `entity_type` varchar(60) NOT NULL,
  `entity_id` varchar(60) NOT NULL,
  `field_name` varchar(100) NOT NULL,
  `field_label` varchar(150) NOT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `changed_by_id` bigint(20) UNSIGNED DEFAULT NULL,
  `changed_by_name` varchar(150) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `field_history_configs`
--

CREATE TABLE `field_history_configs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `module` varchar(60) NOT NULL,
  `tracked_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`tracked_fields`)),
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `field_history_configs`
--

INSERT INTO `field_history_configs` (`id`, `module`, `tracked_fields`, `enabled`, `created_at`, `updated_at`) VALUES
(1, 'leads', '[\"first_name\",\"status\",\"email\"]', 1, '2026-10-16 12:15:13', '2026-10-16 12:15:13');

-- --------------------------------------------------------

--
-- Table structure for table `field_permissions`
--

CREATE TABLE `field_permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `profile_id` bigint(20) UNSIGNED NOT NULL,
  `module` varchar(60) NOT NULL,
  `field_name` varchar(100) NOT NULL,
  `permission` varchar(30) NOT NULL DEFAULT 'Read/Write',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `field_permissions`
--

INSERT INTO `field_permissions` (`id`, `profile_id`, `module`, `field_name`, `permission`, `created_at`, `updated_at`) VALUES
(7, 2, 'accounts', 'credit_limit', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(8, 2, 'accounts', 'annual_revenue', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(9, 2, 'purchase_deals', 'amount', 'Hidden', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(10, 2, 'wires', 'amount', 'Hidden', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(11, 3, 'leads', 'first_name', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(12, 3, 'leads', 'last_name', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(13, 3, 'leads', 'company', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(14, 3, 'leads', 'title', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(15, 3, 'leads', 'email', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(16, 3, 'leads', 'phone', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(17, 3, 'leads', 'status', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(18, 3, 'leads', 'score', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(19, 3, 'leads', 'source', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(20, 3, 'leads', 'assigned_to', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(21, 3, 'accounts', 'name', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(22, 3, 'accounts', 'type', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(23, 3, 'accounts', 'industry', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(24, 3, 'accounts', 'website', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(25, 3, 'accounts', 'phone', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(26, 3, 'accounts', 'billing_address', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(27, 3, 'accounts', 'shipping_address', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(28, 3, 'accounts', 'payment_terms', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(29, 3, 'accounts', 'credit_limit', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(30, 3, 'accounts', 'annual_revenue', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(31, 3, 'contacts', 'first_name', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(32, 3, 'contacts', 'last_name', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(33, 3, 'contacts', 'account_id', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(34, 3, 'contacts', 'title', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(35, 3, 'contacts', 'department', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(36, 3, 'contacts', 'email', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(37, 3, 'contacts', 'phone', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(38, 3, 'contacts', 'contact_role', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(39, 3, 'contacts', 'is_primary', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(40, 3, 'opportunities', 'name', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(41, 3, 'opportunities', 'account_id', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(42, 3, 'opportunities', 'purchase_deal_id', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(43, 3, 'opportunities', 'stage', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(44, 3, 'opportunities', 'amount', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(45, 3, 'opportunities', 'probability', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(46, 3, 'opportunities', 'expected_close_date', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(47, 3, 'purchase_deals', 'po_number', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(48, 3, 'purchase_deals', 'title', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(49, 3, 'purchase_deals', 'vendor_id', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(50, 3, 'purchase_deals', 'amount', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(51, 3, 'purchase_deals', 'stage', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(52, 3, 'purchase_deals', 'expected_delivery_date', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(53, 3, 'purchase_deals', 'payment_terms', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(54, 3, 'shipping', 'tracking_number', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(55, 3, 'shipping', 'carrier', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(56, 3, 'shipping', 'type', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(57, 3, 'shipping', 'status', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(58, 3, 'shipping', 'account_id', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(59, 3, 'shipping', 'shipping_origin', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(60, 3, 'shipping', 'destination_address', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(61, 3, 'shipping', 'estimated_delivery_date', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(62, 3, 'shipping', 'notes', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(63, 3, 'payments', 'invoice_number', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(64, 3, 'payments', 'account_id', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(65, 3, 'payments', 'type', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(66, 3, 'payments', 'amount', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(67, 3, 'payments', 'paid_amount', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(68, 3, 'payments', 'due_date', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(69, 3, 'payments', 'status', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(70, 3, 'wires', 'utr_reference', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(71, 3, 'wires', 'remitter_name', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(72, 3, 'wires', 'sending_bank', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(73, 3, 'wires', 'receiving_bank_account', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(74, 3, 'wires', 'amount', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(75, 3, 'wires', 'date_received', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(76, 3, 'wires', 'status', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(77, 3, 'documents', 'title', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(78, 3, 'documents', 'category', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(79, 3, 'documents', 'file_url', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(80, 3, 'documents', 'status', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(81, 3, 'tasks', 'subject', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(82, 3, 'tasks', 'priority', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(83, 3, 'tasks', 'status', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(84, 3, 'tasks', 'assigned_to', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(85, 3, 'tasks', 'due_date', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(86, 3, 'tasks', 'comments', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(87, 3, 'tasks', 'related_entity_name', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(88, 3, 'tasks', 'reminder_set', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(89, 3, 'tasks', 'is_recurring', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(90, 3, 'users', 'full_name', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(91, 3, 'users', 'email', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(92, 3, 'users', 'role_id', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(93, 3, 'users', 'profile_id', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(94, 3, 'users', 'status', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(95, 3, 'users', 'phone', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(96, 3, 'users', 'avatar', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(97, 4, 'accounts', 'credit_limit', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(98, 4, 'accounts', 'annual_revenue', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(99, 4, 'purchase_deals', 'amount', 'Hidden', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(100, 4, 'wires', 'amount', 'Hidden', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(101, 5, 'opportunities', 'amount', 'Hidden', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(102, 5, 'opportunities', 'probability', 'Hidden', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(103, 5, 'wires', 'amount', 'Hidden', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(104, 6, 'leads', 'first_name', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(105, 6, 'leads', 'last_name', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(106, 6, 'leads', 'company', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(107, 6, 'leads', 'title', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(108, 6, 'leads', 'email', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(109, 6, 'leads', 'phone', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(110, 6, 'leads', 'status', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(111, 6, 'leads', 'score', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(112, 6, 'leads', 'source', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(113, 6, 'leads', 'assigned_to', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(114, 6, 'accounts', 'name', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(115, 6, 'accounts', 'type', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(116, 6, 'accounts', 'industry', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(117, 6, 'accounts', 'website', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(118, 6, 'accounts', 'phone', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(119, 6, 'accounts', 'billing_address', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(120, 6, 'accounts', 'shipping_address', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(121, 6, 'accounts', 'payment_terms', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(122, 6, 'accounts', 'credit_limit', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(123, 6, 'accounts', 'annual_revenue', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(124, 6, 'contacts', 'first_name', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(125, 6, 'contacts', 'last_name', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(126, 6, 'contacts', 'account_id', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(127, 6, 'contacts', 'title', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(128, 6, 'contacts', 'department', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(129, 6, 'contacts', 'email', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(130, 6, 'contacts', 'phone', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(131, 6, 'contacts', 'contact_role', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(132, 6, 'contacts', 'is_primary', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(133, 6, 'opportunities', 'name', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(134, 6, 'opportunities', 'account_id', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(135, 6, 'opportunities', 'purchase_deal_id', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(136, 6, 'opportunities', 'stage', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(137, 6, 'opportunities', 'amount', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(138, 6, 'opportunities', 'probability', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(139, 6, 'opportunities', 'expected_close_date', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(140, 6, 'purchase_deals', 'po_number', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(141, 6, 'purchase_deals', 'title', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(142, 6, 'purchase_deals', 'vendor_id', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(143, 6, 'purchase_deals', 'amount', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(144, 6, 'purchase_deals', 'stage', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(145, 6, 'purchase_deals', 'expected_delivery_date', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(146, 6, 'purchase_deals', 'payment_terms', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(147, 6, 'shipping', 'tracking_number', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(148, 6, 'shipping', 'carrier', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(149, 6, 'shipping', 'type', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(150, 6, 'shipping', 'status', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(151, 6, 'shipping', 'account_id', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(152, 6, 'shipping', 'shipping_origin', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(153, 6, 'shipping', 'destination_address', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(154, 6, 'shipping', 'estimated_delivery_date', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(155, 6, 'shipping', 'notes', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(156, 6, 'payments', 'invoice_number', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(157, 6, 'payments', 'account_id', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(158, 6, 'payments', 'type', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(159, 6, 'payments', 'amount', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(160, 6, 'payments', 'paid_amount', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(161, 6, 'payments', 'due_date', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(162, 6, 'payments', 'status', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(163, 6, 'wires', 'utr_reference', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(164, 6, 'wires', 'remitter_name', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(165, 6, 'wires', 'sending_bank', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(166, 6, 'wires', 'receiving_bank_account', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(167, 6, 'wires', 'amount', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(168, 6, 'wires', 'date_received', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(169, 6, 'wires', 'status', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(170, 6, 'documents', 'title', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(171, 6, 'documents', 'category', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(172, 6, 'documents', 'file_url', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(173, 6, 'documents', 'status', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(174, 6, 'tasks', 'subject', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(175, 6, 'tasks', 'priority', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(176, 6, 'tasks', 'status', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(177, 6, 'tasks', 'assigned_to', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(178, 6, 'tasks', 'due_date', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(179, 6, 'tasks', 'comments', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(180, 6, 'tasks', 'related_entity_name', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(181, 6, 'tasks', 'reminder_set', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(182, 6, 'tasks', 'is_recurring', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(183, 6, 'users', 'full_name', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(184, 6, 'users', 'email', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(185, 6, 'users', 'role_id', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(186, 6, 'users', 'profile_id', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(187, 6, 'users', 'status', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(188, 6, 'users', 'phone', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42'),
(189, 6, 'users', 'avatar', 'Read-Only', '2026-10-14 14:38:42', '2026-10-14 14:38:42');

-- --------------------------------------------------------

--
-- Table structure for table `files`
--

CREATE TABLE `files` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `mime_type` varchar(100) NOT NULL,
  `created_by_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by_name` varchar(150) NOT NULL DEFAULT 'System Admin',
  `latest_version_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `file_links`
--

CREATE TABLE `file_links` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `file_id` bigint(20) UNSIGNED NOT NULL,
  `entity_type` varchar(60) NOT NULL,
  `entity_id` varchar(60) NOT NULL,
  `visibility` varchar(30) NOT NULL DEFAULT 'all',
  `created_by_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `file_versions`
--

CREATE TABLE `file_versions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `file_id` bigint(20) UNSIGNED NOT NULL,
  `version_number` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `storage_key` varchar(255) NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `size_bytes` bigint(20) UNSIGNED NOT NULL,
  `checksum` varchar(100) DEFAULT NULL,
  `uploaded_by_id` bigint(20) UNSIGNED DEFAULT NULL,
  `uploaded_by_name` varchar(150) NOT NULL DEFAULT 'System Admin',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `generated_documents`
--

CREATE TABLE `generated_documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `template_id` bigint(20) UNSIGNED NOT NULL,
  `document_number` varchar(100) NOT NULL,
  `document_type` varchar(30) NOT NULL,
  `status` varchar(40) NOT NULL DEFAULT 'draft',
  `is_locked` tinyint(1) NOT NULL DEFAULT 0,
  `account_id` bigint(20) UNSIGNED DEFAULT NULL,
  `contact_id` bigint(20) UNSIGNED DEFAULT NULL,
  `related_entity_type` varchar(60) DEFAULT NULL,
  `related_entity_id` bigint(20) UNSIGNED DEFAULT NULL,
  `file_id` bigint(20) UNSIGNED DEFAULT NULL,
  `file_visibility` varchar(30) NOT NULL DEFAULT 'all',
  `issue_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `expected_delivery_date` date DEFAULT NULL,
  `payment_terms` varchar(60) DEFAULT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'USD',
  `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `discount_type` varchar(20) NOT NULL DEFAULT 'percentage',
  `discount_rate` decimal(8,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tax_breakdown_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tax_breakdown_json`)),
  `shipping_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `adjustment_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `grand_total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `paid_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `balance_due` decimal(15,2) NOT NULL DEFAULT 0.00,
  `amount_in_words` varchar(255) DEFAULT NULL,
  `party_details_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`party_details_json`)),
  `notes` text DEFAULT NULL,
  `terms_conditions` text DEFAULT NULL,
  `bank_details` text DEFAULT NULL,
  `created_by_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by_name` varchar(150) NOT NULL DEFAULT 'System Admin',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `global_picklists`
--

CREATE TABLE `global_picklists` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `api_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `values_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`values_json`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `global_picklists`
--

INSERT INTO `global_picklists` (`id`, `name`, `api_name`, `description`, `values_json`, `created_at`, `updated_at`) VALUES
(1, 'Lead Statuses', 'lead_statuses', 'Lifecycle statuses for inbound leads', '[\"New\",\"Contacted\",\"Qualified\",\"Unqualified\",\"Converted\"]', '2026-10-14 09:40:19', '2026-10-14 09:40:19'),
(2, 'Opportunity Stages', 'opportunity_stages', 'Sales funnel pipeline stages', '[\"Prospecting\",\"Qualification\",\"Needs Analysis\",\"Proposal\\/Quote\",\"Negotiation\\/Review\",\"Closed Won\",\"Closed Lost\"]', '2026-10-14 09:40:19', '2026-10-14 09:40:19'),
(3, 'Purchase Deal Stages', 'purchase_deal_stages', 'Procurement stages for supplier orders', '[\"Draft\",\"PO Issued\",\"Supplier Confirmed\",\"In Production\",\"Quality Check\",\"Shipped\",\"Delivered\",\"Closed\"]', '2026-10-14 09:40:19', '2026-10-14 09:40:19'),
(4, 'Shipment Statuses', 'shipment_statuses', 'Logistics tracking states', '[\"Booked\",\"Dispatched\",\"In Transit\",\"Customs Clearance\",\"Delivered\",\"Exception\"]', '2026-10-14 09:40:19', '2026-10-14 09:40:19'),
(5, 'Payment Statuses', 'payment_statuses', 'Financial invoice lifecycle stages', '[\"Draft\",\"Issued\",\"Partially Paid\",\"Paid\",\"Overdue\",\"Cancelled\"]', '2026-10-14 09:40:19', '2026-10-14 09:40:19'),
(6, 'Wire Reconciliation Statuses', 'wire_statuses', 'Bank transfer reconciliation tracking', '[\"Pending\",\"Reconciled\",\"Disputed\",\"Unmatched\"]', '2026-10-14 09:40:20', '2026-10-14 09:40:20');

-- --------------------------------------------------------

--
-- Table structure for table `global_search_settings`
--

CREATE TABLE `global_search_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `enabled_modules_json` longtext NOT NULL,
  `searchable_fields_json` longtext NOT NULL,
  `duplicate_precheck_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `mask_format` varchar(30) NOT NULL DEFAULT 'masked',
  `allow_request_access` tinyint(1) NOT NULL DEFAULT 1,
  `enable_create_precheck` tinyint(1) NOT NULL DEFAULT 1,
  `dropdown_limit` int(10) UNSIGNED NOT NULL DEFAULT 6,
  `full_view_limit` int(10) UNSIGNED NOT NULL DEFAULT 50,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leads`
--

CREATE TABLE `leads` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `first_name` varchar(80) NOT NULL,
  `last_name` varchar(80) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `company` varchar(150) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `source` varchar(80) NOT NULL DEFAULT 'Website',
  `score` int(10) UNSIGNED NOT NULL DEFAULT 50,
  `status` varchar(60) NOT NULL DEFAULT 'New',
  `lead_value` varchar(30) DEFAULT NULL,
  `rating` varchar(30) NOT NULL DEFAULT 'Warm',
  `notes` text DEFAULT NULL,
  `email_opt_out` tinyint(1) NOT NULL DEFAULT 0,
  `is_converted` tinyint(1) NOT NULL DEFAULT 0,
  `converted_account_id` bigint(20) UNSIGNED DEFAULT NULL,
  `converted_contact_id` bigint(20) UNSIGNED DEFAULT NULL,
  `converted_opportunity_id` bigint(20) UNSIGNED DEFAULT NULL,
  `assigned_to` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `custom_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_data`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `leads`
--

INSERT INTO `leads` (`id`, `first_name`, `last_name`, `email`, `phone`, `company`, `title`, `source`, `score`, `status`, `lead_value`, `rating`, `notes`, `email_opt_out`, `is_converted`, `converted_account_id`, `converted_contact_id`, `converted_opportunity_id`, `assigned_to`, `created_by`, `updated_by`, `custom_data`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Test', 'Lead 1792000147', 'lead.1792000147@example.com', NULL, 'Acme Corp', NULL, 'Website', 50, 'New', NULL, 'Warm', NULL, 0, 0, NULL, NULL, NULL, 34, NULL, NULL, NULL, '2026-10-14 12:19:07', '2026-10-19 08:32:44', NULL),
(2, 'Test', 'Lead 1792000208', 'lead.1792000208@example.com', NULL, 'Acme Corp', NULL, 'Website', 50, 'New', NULL, 'Warm', NULL, 0, 0, NULL, NULL, NULL, 34, NULL, NULL, NULL, '2026-10-14 12:20:08', '2026-10-19 08:32:44', NULL),
(3, 'Test', 'Lead 1792000233', 'lead.1792000233@example.com', NULL, 'Acme Corp', NULL, 'Website', 50, 'New', NULL, 'Warm', NULL, 0, 0, NULL, NULL, NULL, 34, NULL, NULL, NULL, '2026-10-14 12:20:33', '2026-10-19 08:32:44', NULL),
(4, 'Test', 'Lead 1792000287', 'lead.1792000287@example.com', NULL, 'Acme Corp', NULL, 'Website', 50, 'New', NULL, 'Warm', NULL, 0, 0, NULL, NULL, NULL, 34, NULL, NULL, NULL, '2026-10-14 12:21:27', '2026-10-19 08:32:44', NULL),
(5, 'Test', 'Lead 1792001603', 'lead.1792001603@example.com', NULL, 'Acme Corp', NULL, 'Website', 50, 'New', NULL, 'Warm', NULL, 0, 0, NULL, NULL, NULL, 34, NULL, NULL, NULL, '2026-10-14 12:43:23', '2026-10-19 08:32:44', NULL),
(6, 'Test', 'Lead 1792004112', 'lead.1792004112@example.com', NULL, 'Acme Corp', NULL, 'Website', 50, 'New', NULL, 'Warm', NULL, 0, 0, NULL, NULL, NULL, 34, NULL, NULL, NULL, '2026-10-14 13:25:12', '2026-10-19 08:32:44', NULL),
(7, 'Test', 'Lead 1792004285', 'lead.1792004285@example.com', NULL, 'Acme Corp', NULL, 'Website', 50, 'New', NULL, 'Warm', NULL, 0, 0, NULL, NULL, NULL, 34, NULL, NULL, NULL, '2026-10-14 13:28:05', '2026-10-19 08:32:44', NULL),
(8, 'Test', 'Lead 1792004375', 'lead.1792004375@example.com', NULL, 'Acme Corp', NULL, 'Website', 50, 'New', NULL, 'Warm', NULL, 0, 0, NULL, NULL, NULL, 34, NULL, NULL, NULL, '2026-10-14 13:29:35', '2026-10-19 08:32:44', NULL),
(9, 'Test', 'Lead 1792005937', 'lead.1792005937@example.com', NULL, 'Acme Corp', NULL, 'Website', 50, 'New', NULL, 'Warm', NULL, 0, 0, NULL, NULL, NULL, 34, NULL, NULL, NULL, '2026-10-14 13:55:37', '2026-10-19 08:32:44', NULL),
(10, 'Test', 'Lead 1792007395', 'lead.1792007395@example.com', NULL, 'Acme Corp', NULL, 'Website', 50, 'New', NULL, 'Warm', NULL, 0, 0, NULL, NULL, NULL, 34, NULL, NULL, NULL, '2026-10-14 14:19:55', '2026-10-19 08:32:44', NULL),
(11, 'Test', 'Lead 1792008527', 'lead.1792008527@example.com', NULL, 'Acme Corp', NULL, 'Website', 50, 'New', NULL, 'Warm', NULL, 0, 0, NULL, NULL, NULL, 34, NULL, NULL, NULL, '2026-10-14 14:38:47', '2026-10-19 08:32:44', NULL),
(12, 'Test', 'Lead 1792156082', 'lead.1792156082@example.com', NULL, 'Acme Corp', NULL, 'Website', 50, 'New', NULL, 'Warm', NULL, 0, 0, NULL, NULL, NULL, 34, NULL, NULL, NULL, '2026-10-16 07:38:02', '2026-10-19 08:32:44', NULL),
(13, 'Test', 'Lead 1792157059', 'lead.1792157059@example.com', NULL, 'Acme Corp', NULL, 'Website', 50, 'New', NULL, 'Warm', NULL, 0, 0, NULL, NULL, NULL, 34, NULL, NULL, NULL, '2026-10-16 07:54:19', '2026-10-19 08:32:44', NULL),
(14, 'Test', 'Lead 1792158350', 'lead.1792158350@example.com', NULL, 'Acme Corp', NULL, 'Website', 50, 'New', NULL, 'Warm', NULL, 0, 0, NULL, NULL, NULL, 34, NULL, NULL, NULL, '2026-10-16 08:15:50', '2026-10-19 08:32:44', NULL),
(15, 'Test', 'Lead 1792158938', 'lead.1792158938@example.com', NULL, 'Acme Corp', NULL, 'Website', 50, 'New', NULL, 'Warm', NULL, 0, 0, NULL, NULL, NULL, 34, NULL, NULL, NULL, '2026-10-16 08:25:38', '2026-10-19 08:32:44', NULL),
(16, 'Test', 'Lead 1792158952', 'lead.1792158952@example.com', NULL, 'Acme Corp', NULL, 'Website', 50, 'New', NULL, 'Warm', NULL, 0, 0, NULL, NULL, NULL, 34, NULL, NULL, NULL, '2026-10-16 08:25:52', '2026-10-19 08:32:44', NULL),
(17, 'Test', 'Lead 1792161299', 'lead.1792161299@example.com', NULL, 'Acme Corp', NULL, 'Website', 50, 'New', NULL, 'Warm', NULL, 0, 0, NULL, NULL, NULL, 34, NULL, NULL, NULL, '2026-10-16 09:04:59', '2026-10-19 08:32:44', NULL),
(18, 'Test', 'Lead 1792161316', 'lead.1792161316@example.com', NULL, 'Acme Corp', NULL, 'Website', 50, 'New', NULL, 'Warm', NULL, 0, 0, NULL, NULL, NULL, 34, NULL, NULL, NULL, '2026-10-16 09:05:16', '2026-10-19 08:32:44', NULL),
(19, 'Test', 'Lead 1792162768', 'lead.1792162768@example.com', NULL, 'Acme Corp', NULL, 'Website', 50, 'New', NULL, 'Warm', NULL, 0, 0, NULL, NULL, NULL, 34, NULL, NULL, NULL, '2026-10-16 09:29:28', '2026-10-19 08:32:44', NULL),
(20, 'Test', 'Lead 1792164103', 'lead.1792164103@example.com', NULL, 'Acme Corp', NULL, 'Website', 50, 'New', NULL, 'Warm', NULL, 0, 0, NULL, NULL, NULL, 34, NULL, NULL, NULL, '2026-10-16 09:51:43', '2026-10-19 08:32:44', NULL),
(21, 'Test', 'Lead 1792164691', 'lead.1792164691@example.com', NULL, 'Acme Corp', NULL, 'Website', 50, 'New', NULL, 'Warm', NULL, 0, 0, NULL, NULL, NULL, 34, NULL, NULL, NULL, '2026-10-16 10:01:31', '2026-10-19 08:32:44', NULL),
(22, 'Test', 'Lead 1792165180', 'lead.1792165180@example.com', NULL, 'Acme Corp', NULL, 'Website', 50, 'New', NULL, 'Warm', NULL, 0, 0, NULL, NULL, NULL, 34, NULL, NULL, NULL, '2026-10-16 10:09:40', '2026-10-19 08:32:44', NULL),
(23, 'Test', 'Lead 1792165611', 'lead.1792165611@example.com', NULL, 'Acme Corp', NULL, 'Website', 50, 'New', NULL, 'Warm', NULL, 0, 0, NULL, NULL, NULL, 34, NULL, NULL, NULL, '2026-10-16 10:16:51', '2026-10-19 08:32:44', NULL),
(24, 'Test', 'Lead 1792169193', 'lead.1792169193@example.com', NULL, 'Acme Corp', NULL, 'Website', 50, 'New', NULL, 'Warm', NULL, 0, 0, NULL, NULL, NULL, 34, NULL, NULL, NULL, '2026-10-16 11:16:33', '2026-10-19 08:32:44', NULL),
(25, 'Test', 'Lead 1792169287', 'lead.1792169287@example.com', NULL, 'Acme Corp', NULL, 'Website', 50, 'New', NULL, 'Warm', NULL, 0, 0, NULL, NULL, NULL, 34, NULL, NULL, NULL, '2026-10-16 11:18:07', '2026-10-19 08:32:44', NULL),
(26, 'Test', 'Lead 1792170719', 'lead.1792170719@example.com', NULL, 'Acme Corp', NULL, 'Website', 50, 'New', NULL, 'Warm', NULL, 0, 0, NULL, NULL, NULL, 34, NULL, NULL, NULL, '2026-10-16 11:41:59', '2026-10-19 08:32:44', NULL),
(27, 'Test', 'Lead 1792171188', 'lead.1792171188@example.com', NULL, 'Acme Corp', NULL, 'Website', 50, 'New', NULL, 'Warm', NULL, 0, 0, NULL, NULL, NULL, 34, NULL, NULL, NULL, '2026-10-16 11:49:48', '2026-10-19 08:32:44', NULL),
(28, 'Test', 'Lead 1792171713', 'lead.1792171713@example.com', NULL, 'Acme Corp', NULL, 'Website', 50, 'New', NULL, 'Warm', NULL, 0, 0, NULL, NULL, NULL, 34, NULL, NULL, NULL, '2026-10-16 11:58:33', '2026-10-19 08:32:44', NULL),
(29, 'Test', 'Lead 1792172868', 'lead.1792172868@example.com', NULL, 'Acme Corp', NULL, 'Website', 50, 'New', NULL, 'Warm', NULL, 0, 0, NULL, NULL, NULL, 34, NULL, NULL, NULL, '2026-10-16 12:17:48', '2026-10-19 08:32:44', NULL),
(30, 'Test', 'Lead 1792173845', 'lead.1792173845@example.com', NULL, 'Acme Corp', NULL, 'Website', 50, 'New', NULL, 'Warm', NULL, 0, 0, NULL, NULL, NULL, 34, NULL, NULL, NULL, '2026-10-16 12:34:05', '2026-10-19 08:32:44', NULL),
(31, 'Test', 'Lead 1792415266', 'lead.1792415266@example.com', NULL, 'Acme Corp', NULL, 'Website', 50, 'New', NULL, 'Warm', NULL, 0, 0, NULL, NULL, NULL, 34, NULL, NULL, NULL, '2026-10-19 07:37:46', '2026-10-19 08:32:44', NULL),
(32, 'Test', 'Lead 1792416628', 'lead.1792416628@example.com', NULL, 'Acme Corp', NULL, 'Website', 50, 'New', NULL, 'Warm', NULL, 0, 0, NULL, NULL, NULL, 34, NULL, NULL, NULL, '2026-10-19 08:00:28', '2026-10-19 08:32:44', NULL),
(33, 'Test', 'Lead 1792416654', 'lead.1792416654@example.com', NULL, 'Acme Corp', NULL, 'Website', 50, 'New', NULL, 'Warm', NULL, 0, 0, NULL, NULL, NULL, 34, NULL, NULL, NULL, '2026-10-19 08:00:54', '2026-10-19 08:32:44', NULL),
(34, 'Test', 'Lead 1792417472', 'lead.1792417472@example.com', NULL, 'Acme Corp', NULL, 'Website', 50, 'New', NULL, 'Warm', NULL, 0, 0, NULL, NULL, NULL, 34, NULL, NULL, NULL, '2026-10-19 08:14:32', '2026-10-19 08:32:44', NULL),
(35, 'Test', 'Lead 1792418514', 'lead.1792418514@example.com', NULL, 'Acme Corp', NULL, 'Website', 50, 'New', NULL, 'Warm', NULL, 0, 0, NULL, NULL, NULL, 34, NULL, NULL, NULL, '2026-10-19 08:31:54', '2026-10-19 08:32:44', NULL),
(36, 'Test', 'Lead 1792418529', 'lead.1792418529@example.com', NULL, 'Acme Corp', NULL, 'Website', 50, 'New', NULL, 'Warm', NULL, 0, 0, NULL, NULL, NULL, 34, NULL, NULL, NULL, '2026-10-19 08:32:09', '2026-10-19 08:32:44', NULL),
(37, 'Test', 'Lead 1792418564', 'lead.1792418564@example.com', NULL, 'Acme Corp', NULL, 'Website', 50, 'New', NULL, 'Warm', NULL, 0, 0, NULL, NULL, NULL, 34, NULL, NULL, NULL, '2026-10-19 08:32:44', '2026-10-19 08:32:44', NULL),
(38, 'Test', 'Lead 1792418597', 'lead.1792418597@example.com', NULL, 'Acme Corp', NULL, 'Website', 50, 'New', NULL, 'Warm', NULL, 0, 0, NULL, NULL, NULL, 34, NULL, NULL, NULL, '2026-10-19 08:33:17', '2026-10-19 08:33:17', NULL),
(39, 'Test', 'Lead 1792419195', 'lead.1792419195@example.com', NULL, 'Acme Corp', NULL, 'Website', 50, 'New', NULL, 'Warm', NULL, 0, 0, NULL, NULL, NULL, 34, NULL, NULL, NULL, '2026-10-19 08:43:15', '2026-10-19 08:43:15', NULL),
(40, 'Test', 'Lead 1792420119', 'lead.1792420119@example.com', NULL, 'Acme Corp', NULL, 'Website', 50, 'New', NULL, 'Warm', NULL, 0, 0, NULL, NULL, NULL, 34, NULL, NULL, NULL, '2026-10-19 08:58:39', '2026-10-19 08:58:39', NULL);

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
(1, '2026_10_14_000001_create_roles_table', 1),
(2, '2026_10_14_000002_create_profiles_table', 1),
(3, '2026_10_14_000003_create_tenant_users_table', 1),
(4, '2026_10_14_000004_create_permissions_and_security_table', 1),
(5, '2026_10_14_000005_create_global_picklists_table', 1),
(6, '2026_10_14_000006_create_custom_fields_table', 1),
(7, '2026_10_14_000007_create_accounts_table', 1),
(8, '2026_10_14_000008_create_contacts_table', 1),
(9, '2026_10_14_000009_create_purchase_deals_table', 1),
(10, '2026_10_14_000010_create_opportunities_table', 1),
(11, '2026_10_14_000011_create_leads_table', 1),
(12, '2026_10_14_000012_create_shipments_table', 1),
(13, '2026_10_14_000013_create_payments_table', 1),
(14, '2026_10_14_000014_create_wires_received_table', 1),
(15, '2026_10_14_000015_create_tasks_table', 1),
(16, '2026_10_14_000016_create_activities_table', 1),
(17, '2026_10_14_000017_create_custom_field_values_table', 1),
(18, '2026_10_14_000018_create_custom_objects_table', 1),
(19, '2026_10_14_000019_create_approval_workflows_table', 1),
(20, '2026_10_14_000020_create_notifications_and_email_rules_table', 1),
(21, '2026_10_14_000021_create_files_system_table', 1),
(22, '2026_10_14_000022_create_sharing_and_governance_table', 1),
(23, '2026_10_14_000023_create_layouts_and_settings_table', 1),
(24, '2026_10_14_000024_create_documents_and_flows_table', 1),
(25, '2026_10_14_000025_create_global_search_and_data_operations_table', 1),
(26, '2026_10_14_000026_create_crm_migration_engine_table', 1),
(27, '2026_10_14_000027_create_tenant_operations_and_subscriptions_table', 1),
(28, '2026_10_16_000002_add_bulk_email_settings_to_profiles_table', 2),
(29, '2026_10_19_000001_add_manager_id_to_tenant_users_table', 3);

-- --------------------------------------------------------

--
-- Table structure for table `migration_batches`
--

CREATE TABLE `migration_batches` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `source_crm` varchar(40) NOT NULL,
  `batch_name` varchar(150) NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'completed',
  `total_records` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `successful_records` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `failed_records` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `entity_counts_json` longtext NOT NULL,
  `stage_mappings_json` longtext DEFAULT NULL,
  `success_details_json` longtext DEFAULT NULL,
  `performed_by_id` bigint(20) UNSIGNED DEFAULT NULL,
  `performed_by_name` varchar(150) NOT NULL DEFAULT 'System Admin',
  `reverted_at` timestamp NULL DEFAULT NULL,
  `reverted_by_name` varchar(150) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migration_error_logs`
--

CREATE TABLE `migration_error_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `migration_batch_id` bigint(20) UNSIGNED NOT NULL,
  `entity_type` varchar(60) NOT NULL,
  `row_index` int(10) UNSIGNED NOT NULL,
  `legacy_id` varchar(150) DEFAULT NULL,
  `raw_data_json` longtext NOT NULL,
  `failure_reason` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migration_id_mappings`
--

CREATE TABLE `migration_id_mappings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `migration_batch_id` bigint(20) UNSIGNED NOT NULL,
  `source_crm` varchar(40) NOT NULL,
  `entity_type` varchar(60) NOT NULL,
  `legacy_id` varchar(150) NOT NULL,
  `dealsync_id` varchar(150) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migration_records`
--

CREATE TABLE `migration_records` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `migration_batch_id` bigint(20) UNSIGNED NOT NULL,
  `entity_type` varchar(60) NOT NULL,
  `record_id` varchar(60) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification_rules`
--

CREATE TABLE `notification_rules` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `operation` varchar(40) NOT NULL,
  `module` varchar(60) NOT NULL,
  `type` varchar(30) NOT NULL DEFAULT 'success',
  `title` varchar(150) NOT NULL,
  `message_template` text NOT NULL,
  `actions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`actions`)),
  `duration_ms` int(10) UNSIGNED NOT NULL DEFAULT 5000,
  `position` varchar(30) NOT NULL DEFAULT 'top-right',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `trigger_type` varchar(30) NOT NULL DEFAULT 'immediate',
  `date_field` varchar(60) DEFAULT NULL,
  `offset_direction` varchar(20) DEFAULT NULL,
  `offset_value` int(11) NOT NULL DEFAULT 0,
  `offset_unit` varchar(20) NOT NULL DEFAULT 'hours',
  `criteria_field` varchar(60) DEFAULT NULL,
  `criteria_operator` varchar(30) DEFAULT NULL,
  `criteria_value` text DEFAULT NULL,
  `recipient_type` varchar(40) NOT NULL DEFAULT 'record_owner',
  `recipient_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`recipient_ids`)),
  `recipient_profile_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`recipient_profile_ids`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification_rule_executions`
--

CREATE TABLE `notification_rule_executions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `rule_id` bigint(20) UNSIGNED NOT NULL,
  `module` varchar(60) NOT NULL,
  `record_id` varchar(60) NOT NULL,
  `executed_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `opportunities`
--

CREATE TABLE `opportunities` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `account_id` bigint(20) UNSIGNED NOT NULL,
  `purchase_deal_id` bigint(20) UNSIGNED NOT NULL,
  `contact_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(200) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'USD',
  `stage` varchar(60) NOT NULL,
  `probability` int(10) UNSIGNED NOT NULL DEFAULT 50,
  `expected_close_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `custom_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_data`)),
  `assigned_to` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `page_layouts`
--

CREATE TABLE `page_layouts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `module` varchar(60) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `layout_json` longtext NOT NULL,
  `created_by` varchar(100) NOT NULL DEFAULT 'System Admin',
  `updated_by` varchar(100) NOT NULL DEFAULT 'System Admin',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `page_layouts`
--

INSERT INTO `page_layouts` (`id`, `module`, `name`, `description`, `is_default`, `layout_json`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 'leads', 'Leads Default Layout', 'Standard enterprise production layout with 2-column detail sections, related lists, and activity tracking.', 1, '{\"sections\":[{\"id\":\"sec-primary\",\"title\":\"Leads Information\",\"columns\":2,\"collapsible\":true,\"defaultCollapsed\":false,\"fields\":[{\"name\":\"first_name\",\"label\":\"First Name\",\"type\":\"Text\",\"required\":true},{\"name\":\"last_name\",\"label\":\"Last Name\",\"type\":\"Text\",\"required\":true},{\"name\":\"company\",\"label\":\"Company Name\",\"type\":\"Text\",\"required\":true},{\"name\":\"title\",\"label\":\"Job Title\",\"type\":\"Text\"},{\"name\":\"email\",\"label\":\"Email Address\",\"type\":\"Email\",\"required\":true},{\"name\":\"phone\",\"label\":\"Phone Number\",\"type\":\"Phone\"},{\"name\":\"status\",\"label\":\"Status\",\"type\":\"Dropdown\",\"required\":true},{\"name\":\"score\",\"label\":\"Lead Score\",\"type\":\"Number\"},{\"name\":\"source\",\"label\":\"Lead Source\",\"type\":\"Dropdown\"},{\"name\":\"assigned_to\",\"label\":\"Assigned To\",\"type\":\"Text\"}]},{\"id\":\"sec-system-info\",\"title\":\"System Information\",\"columns\":2,\"collapsible\":true,\"defaultCollapsed\":false,\"fields\":[{\"name\":\"id\",\"label\":\"Record ID\",\"type\":\"Text\",\"readOnly\":true},{\"name\":\"created_at\",\"label\":\"Created Date\",\"type\":\"Date\",\"readOnly\":true},{\"name\":\"updated_at\",\"label\":\"Last Modified Date\",\"type\":\"Date\",\"readOnly\":true},{\"name\":\"created_by\",\"label\":\"Created By\",\"type\":\"Text\",\"readOnly\":true}]}],\"relatedLists\":[{\"relation\":\"contacts\",\"label\":\"Contacts\",\"enabled\":true,\"location\":\"right_sidebar\",\"columns\":[\"first_name\",\"last_name\",\"email\",\"phone\",\"title\"]},{\"relation\":\"opportunities\",\"label\":\"Opportunities\",\"enabled\":true,\"location\":\"right_sidebar\",\"columns\":[\"name\",\"stage\",\"amount\",\"probability\",\"expected_close_date\"]},{\"relation\":\"files\",\"label\":\"Notes & Attachments\",\"enabled\":true,\"location\":\"right_sidebar\",\"columns\":[\"file_name\",\"file_size\",\"created_at\"]}],\"components\":{\"layoutStyle\":\"two_column\",\"sidebarOrder\":[\"files\",\"activity\",\"related\",\"approvals\",\"field_history\",\"emails\"],\"filesLocation\":\"right_sidebar\",\"activityLocation\":\"right_sidebar\",\"relatedLocation\":\"right_sidebar\",\"showApprovalHistory\":true,\"showFieldHistory\":true,\"showEmails\":true,\"emailsVisibilityPolicy\":\"everyone\"},\"activitySettings\":{\"allowedTypes\":[\"Call\",\"Task\",\"Meeting\",\"Note\"],\"defaultType\":\"Call\"},\"dependentFields\":[],\"validationRules\":[],\"showSystemInfo\":true,\"headerConfig\":{\"primary_field\":\"first_name\",\"show_status_badge\":true}}', 'System Admin', 'System Admin', '2026-10-16 12:15:13', '2026-10-19 08:55:17'),
(2, 'leads', 'Updated Test Layout', 'Updated description', 0, '{\"sections\":[{\"id\":\"sec_test_1\",\"title\":\"Renamed Section\",\"columns\":1,\"isCollapsed\":true,\"fields\":[{\"field\":\"email\",\"required\":true,\"readOnly\":false}]}],\"highlights\":[\"email\"],\"quickActions\":[\"send_email\"]}', 'System Admin', 'System Admin', '2026-10-16 12:15:13', '2026-10-19 08:55:17');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `account_id` bigint(20) UNSIGNED NOT NULL,
  `opportunity_id` bigint(20) UNSIGNED DEFAULT NULL,
  `purchase_deal_id` bigint(20) UNSIGNED DEFAULT NULL,
  `invoice_number` varchar(80) NOT NULL,
  `type` varchar(30) NOT NULL DEFAULT 'Receivable',
  `amount` decimal(15,2) NOT NULL,
  `paid_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(10) NOT NULL DEFAULT 'USD',
  `status` varchar(40) NOT NULL DEFAULT 'Draft',
  `issue_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `custom_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_data`)),
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `private_folder_shares`
--

CREATE TABLE `private_folder_shares` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `entity_type` varchar(60) NOT NULL,
  `entity_id` varchar(60) NOT NULL,
  `share_type` varchar(30) NOT NULL,
  `share_target_id` varchar(60) NOT NULL,
  `share_target_name` varchar(150) DEFAULT NULL,
  `granted_by_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `process_flows`
--

CREATE TABLE `process_flows` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(60) NOT NULL DEFAULT 'general',
  `entity_type` varchar(60) NOT NULL,
  `trigger_type` varchar(40) NOT NULL,
  `trigger_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`trigger_config`)),
  `nodes` longtext NOT NULL,
  `edges` longtext NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'active',
  `version` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `created_by` varchar(100) NOT NULL DEFAULT 'System Admin',
  `updated_by` varchar(100) NOT NULL DEFAULT 'System Admin',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `process_flow_executions`
--

CREATE TABLE `process_flow_executions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `flow_id` bigint(20) UNSIGNED NOT NULL,
  `entity_type` varchar(60) NOT NULL,
  `record_id` varchar(60) DEFAULT NULL,
  `trigger_type` varchar(40) NOT NULL,
  `status` varchar(30) NOT NULL,
  `current_node_id` varchar(100) DEFAULT NULL,
  `state_data` longtext DEFAULT NULL,
  `logs` longtext DEFAULT NULL,
  `executed_by` varchar(100) NOT NULL DEFAULT 'System Admin',
  `started_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `profiles`
--

CREATE TABLE `profiles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_whitelist` text DEFAULT NULL,
  `login_time_start` varchar(10) DEFAULT NULL,
  `login_time_end` varchar(10) DEFAULT NULL,
  `login_days` varchar(100) DEFAULT NULL,
  `surprise_me_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `bulk_email_daily_limit` int(11) NOT NULL DEFAULT 500,
  `can_send_bulk_email` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `profiles`
--

INSERT INTO `profiles` (`id`, `name`, `description`, `ip_whitelist`, `login_time_start`, `login_time_end`, `login_days`, `surprise_me_enabled`, `is_admin`, `created_at`, `updated_at`, `bulk_email_daily_limit`, `can_send_bulk_email`) VALUES
(1, 'System Administrator', 'Full access to all CRM objects, security, and administrative settings', NULL, NULL, NULL, NULL, 1, 0, '2026-10-14 09:40:19', '2026-10-14 09:40:19', 500, 0),
(2, 'Standard User', 'Standard CRM operational permissions', NULL, NULL, NULL, NULL, 1, 0, '2026-10-14 09:40:19', '2026-10-14 09:40:19', 500, 0),
(3, 'Read Only User', 'View-only access across CRM modules', NULL, NULL, NULL, NULL, 0, 0, '2026-10-14 09:40:19', '2026-10-14 09:40:19', 500, 0),
(4, 'Standard Sales Representative', 'Field and inside sales execution, opportunities, and quota tracking', NULL, NULL, NULL, NULL, 1, 0, '2026-10-14 12:19:05', '2026-10-14 12:19:05', 500, 0),
(5, 'Procurement Specialist', 'Vendor, purchase contracts, and supply chain telemetry', NULL, NULL, NULL, NULL, 1, 0, '2026-10-14 12:19:05', '2026-10-14 12:19:05', 500, 0),
(6, 'Read Only Profile', 'Auditor and read-only executive observation workspace', NULL, NULL, NULL, NULL, 1, 0, '2026-10-14 12:19:05', '2026-10-14 12:19:05', 500, 0),
(8, 'System Administrators', NULL, NULL, NULL, NULL, NULL, 1, 1, '2026-10-14 13:24:49', '2026-10-14 13:44:19', 500, 0);

-- --------------------------------------------------------

--
-- Table structure for table `profile_layout_assignments`
--

CREATE TABLE `profile_layout_assignments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `profile_id` bigint(20) UNSIGNED NOT NULL,
  `module` varchar(60) NOT NULL,
  `layout_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `profile_permissions`
--

CREATE TABLE `profile_permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `profile_id` bigint(20) UNSIGNED NOT NULL,
  `module` varchar(60) NOT NULL,
  `can_create` tinyint(1) NOT NULL DEFAULT 1,
  `can_read` tinyint(1) NOT NULL DEFAULT 1,
  `can_update` tinyint(1) NOT NULL DEFAULT 1,
  `can_delete` tinyint(1) NOT NULL DEFAULT 0,
  `can_transfer` tinyint(1) NOT NULL DEFAULT 0,
  `record_scope` varchar(30) NOT NULL DEFAULT 'All',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `profile_permissions`
--

INSERT INTO `profile_permissions` (`id`, `profile_id`, `module`, `can_create`, `can_read`, `can_update`, `can_delete`, `can_transfer`, `record_scope`, `created_at`, `updated_at`) VALUES
(2, 2, 'leads', 1, 1, 1, 0, 0, 'Team', '2026-10-14 09:40:19', '2026-10-14 09:40:19'),
(3, 3, 'leads', 0, 1, 0, 0, 0, 'All', '2026-10-14 09:40:19', '2026-10-14 09:40:19'),
(5, 2, 'accounts', 1, 1, 1, 0, 0, 'Team', '2026-10-14 09:40:19', '2026-10-14 09:40:19'),
(6, 3, 'accounts', 0, 1, 0, 0, 0, 'All', '2026-10-14 09:40:19', '2026-10-14 09:40:19'),
(8, 2, 'contacts', 1, 1, 1, 0, 0, 'Team', '2026-10-14 09:40:19', '2026-10-14 09:40:19'),
(9, 3, 'contacts', 0, 1, 0, 0, 0, 'All', '2026-10-14 09:40:19', '2026-10-14 09:40:19'),
(11, 2, 'purchase_deals', 1, 1, 1, 0, 0, 'Team', '2026-10-14 09:40:19', '2026-10-14 09:40:19'),
(12, 3, 'purchase_deals', 0, 1, 0, 0, 0, 'All', '2026-10-14 09:40:19', '2026-10-14 09:40:19'),
(14, 2, 'opportunities', 1, 1, 1, 0, 0, 'Team', '2026-10-14 09:40:19', '2026-10-14 09:40:19'),
(15, 3, 'opportunities', 0, 1, 0, 0, 0, 'All', '2026-10-14 09:40:19', '2026-10-14 09:40:19'),
(17, 2, 'shipments', 1, 1, 1, 0, 0, 'Team', '2026-10-14 09:40:19', '2026-10-14 09:40:19'),
(18, 3, 'shipments', 0, 1, 0, 0, 0, 'All', '2026-10-14 09:40:19', '2026-10-14 09:40:19'),
(20, 2, 'payments', 1, 1, 1, 0, 0, 'Team', '2026-10-14 09:40:19', '2026-10-14 09:40:19'),
(21, 3, 'payments', 0, 1, 0, 0, 0, 'All', '2026-10-14 09:40:19', '2026-10-14 09:40:19'),
(23, 2, 'wires_received', 1, 1, 1, 0, 0, 'Team', '2026-10-14 09:40:19', '2026-10-14 09:40:19'),
(24, 3, 'wires_received', 0, 1, 0, 0, 0, 'All', '2026-10-14 09:40:19', '2026-10-14 09:40:19'),
(26, 2, 'tasks', 1, 1, 1, 0, 0, 'Team', '2026-10-14 09:40:19', '2026-10-14 09:40:19'),
(27, 3, 'tasks', 0, 1, 0, 0, 0, 'All', '2026-10-14 09:40:19', '2026-10-14 09:40:19'),
(1001, 1, 'leads', 1, 1, 1, 1, 1, 'All', '2026-10-19 08:55:20', '2026-10-19 08:55:20'),
(1002, 1, 'accounts', 1, 1, 1, 1, 1, 'All', '2026-10-19 08:55:20', '2026-10-19 08:55:20'),
(1003, 1, 'contacts', 1, 1, 1, 1, 1, 'All', '2026-10-19 08:55:20', '2026-10-19 08:55:20'),
(1004, 1, 'opportunities', 1, 1, 1, 1, 1, 'All', '2026-10-19 08:55:20', '2026-10-19 08:55:20'),
(1005, 1, 'purchase_deals', 1, 1, 1, 1, 1, 'All', '2026-10-19 08:55:20', '2026-10-19 08:55:20'),
(1006, 1, 'shipping', 1, 1, 1, 1, 1, 'All', '2026-10-19 08:55:20', '2026-10-19 08:55:20'),
(1007, 1, 'payments', 1, 1, 1, 1, 1, 'All', '2026-10-19 08:55:20', '2026-10-19 08:55:20'),
(1008, 1, 'wires', 1, 1, 1, 1, 1, 'All', '2026-10-19 08:55:20', '2026-10-19 08:55:20'),
(1009, 1, 'documents', 1, 1, 1, 1, 1, 'All', '2026-10-19 08:55:20', '2026-10-19 08:55:20'),
(1010, 1, 'tasks', 1, 1, 1, 1, 1, 'All', '2026-10-19 08:55:20', '2026-10-19 08:55:20'),
(1011, 1, 'users', 1, 1, 1, 1, 1, 'All', '2026-10-19 08:55:20', '2026-10-19 08:55:20');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_deals`
--

CREATE TABLE `purchase_deals` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `account_id` bigint(20) UNSIGNED NOT NULL,
  `contact_id` bigint(20) UNSIGNED DEFAULT NULL,
  `po_number` varchar(80) NOT NULL,
  `title` varchar(200) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'USD',
  `stage` varchar(60) NOT NULL,
  `expected_delivery_date` date DEFAULT NULL,
  `payment_terms` varchar(60) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `custom_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_data`)),
  `assigned_to` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `record_shares`
--

CREATE TABLE `record_shares` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `module` varchar(60) NOT NULL,
  `record_id` varchar(60) NOT NULL,
  `shared_with_type` varchar(30) NOT NULL,
  `shared_with_id` varchar(60) NOT NULL,
  `access_level` varchar(30) NOT NULL DEFAULT 'read',
  `source` varchar(30) NOT NULL DEFAULT 'rule',
  `rule_id` bigint(20) UNSIGNED DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `created_by` varchar(100) NOT NULL DEFAULT 'System Admin',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `record_shares`
--

INSERT INTO `record_shares` (`id`, `module`, `record_id`, `shared_with_type`, `shared_with_id`, `access_level`, `source`, `rule_id`, `expires_at`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'leads', '1', 'role', '2', 'read_write', 'rule', 4, NULL, 'System Sharing Engine', '2026-10-16 10:16:27', '2026-10-16 10:16:27'),
(2, 'leads', '2', 'role', '2', 'read_write', 'rule', 4, NULL, 'System Sharing Engine', '2026-10-16 10:16:27', '2026-10-16 10:16:27'),
(3, 'leads', '3', 'role', '2', 'read_write', 'rule', 4, NULL, 'System Sharing Engine', '2026-10-16 10:16:27', '2026-10-16 10:16:27'),
(4, 'leads', '4', 'role', '2', 'read_write', 'rule', 4, NULL, 'System Sharing Engine', '2026-10-16 10:16:27', '2026-10-16 10:16:27'),
(5, 'leads', '5', 'role', '2', 'read_write', 'rule', 4, NULL, 'System Sharing Engine', '2026-10-16 10:16:27', '2026-10-16 10:16:27'),
(6, 'leads', '6', 'role', '2', 'read_write', 'rule', 4, NULL, 'System Sharing Engine', '2026-10-16 10:16:27', '2026-10-16 10:16:27'),
(7, 'leads', '7', 'role', '2', 'read_write', 'rule', 4, NULL, 'System Sharing Engine', '2026-10-16 10:16:27', '2026-10-16 10:16:27'),
(8, 'leads', '8', 'role', '2', 'read_write', 'rule', 4, NULL, 'System Sharing Engine', '2026-10-16 10:16:27', '2026-10-16 10:16:27'),
(9, 'leads', '9', 'role', '2', 'read_write', 'rule', 4, NULL, 'System Sharing Engine', '2026-10-16 10:16:27', '2026-10-16 10:16:27'),
(10, 'leads', '10', 'role', '2', 'read_write', 'rule', 4, NULL, 'System Sharing Engine', '2026-10-16 10:16:27', '2026-10-16 10:16:27'),
(11, 'leads', '11', 'role', '2', 'read_write', 'rule', 4, NULL, 'System Sharing Engine', '2026-10-16 10:16:27', '2026-10-16 10:16:27'),
(12, 'leads', '12', 'role', '2', 'read_write', 'rule', 4, NULL, 'System Sharing Engine', '2026-10-16 10:16:27', '2026-10-16 10:16:27'),
(13, 'leads', '13', 'role', '2', 'read_write', 'rule', 4, NULL, 'System Sharing Engine', '2026-10-16 10:16:27', '2026-10-16 10:16:27'),
(14, 'leads', '14', 'role', '2', 'read_write', 'rule', 4, NULL, 'System Sharing Engine', '2026-10-16 10:16:27', '2026-10-16 10:16:27'),
(15, 'leads', '15', 'role', '2', 'read_write', 'rule', 4, NULL, 'System Sharing Engine', '2026-10-16 10:16:27', '2026-10-16 10:16:27'),
(16, 'leads', '16', 'role', '2', 'read_write', 'rule', 4, NULL, 'System Sharing Engine', '2026-10-16 10:16:27', '2026-10-16 10:16:27'),
(17, 'leads', '17', 'role', '2', 'read_write', 'rule', 4, NULL, 'System Sharing Engine', '2026-10-16 10:16:27', '2026-10-16 10:16:27'),
(18, 'leads', '18', 'role', '2', 'read_write', 'rule', 4, NULL, 'System Sharing Engine', '2026-10-16 10:16:27', '2026-10-16 10:16:27'),
(19, 'leads', '19', 'role', '2', 'read_write', 'rule', 4, NULL, 'System Sharing Engine', '2026-10-16 10:16:27', '2026-10-16 10:16:27'),
(20, 'leads', '20', 'role', '2', 'read_write', 'rule', 4, NULL, 'System Sharing Engine', '2026-10-16 10:16:27', '2026-10-16 10:16:27'),
(21, 'leads', '21', 'role', '2', 'read_write', 'rule', 4, NULL, 'System Sharing Engine', '2026-10-16 10:16:27', '2026-10-16 10:16:27'),
(22, 'leads', '22', 'role', '2', 'read_write', 'rule', 4, NULL, 'System Sharing Engine', '2026-10-16 10:16:27', '2026-10-16 10:16:27');

-- --------------------------------------------------------

--
-- Table structure for table `relationship_definitions`
--

CREATE TABLE `relationship_definitions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `parent_object` varchar(100) NOT NULL,
  `child_object` varchar(100) NOT NULL,
  `relation_name` varchar(100) NOT NULL,
  `label` varchar(150) NOT NULL,
  `foreign_key` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `reports_to_role_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`, `reports_to_role_id`, `created_at`, `updated_at`) VALUES
(1, 'CEO', 'Chief Executive Officer', NULL, '2026-10-14 09:40:19', '2026-10-14 09:40:19'),
(2, 'VP of Sales', 'Head of global sales organization', 1, '2026-10-14 09:40:19', '2026-10-14 09:40:19'),
(3, 'Sales Manager', 'Team sales lead and pipeline controller', 2, '2026-10-14 09:40:19', '2026-10-14 09:40:19'),
(4, 'Sales Representative', 'Direct customer and lead relationship executive', 3, '2026-10-14 09:40:19', '2026-10-14 09:40:19'),
(5, 'Procurement Director', 'Oversees purchase deals and supplier contracts', 1, '2026-10-14 09:40:19', '2026-10-14 09:40:19'),
(6, 'Executive / CEO', 'Chief Executive Officer and strategic executive director', NULL, '2026-10-14 12:19:05', '2026-10-14 12:19:05'),
(7, 'Sales Executive', 'Field and inside sales representative', NULL, '2026-10-14 12:19:05', '2026-10-14 12:19:05'),
(8, 'Procurement Lead', 'Vendor negotiation and supply chain lead', NULL, '2026-10-14 12:19:05', '2026-10-14 12:19:05'),
(9, 'Finance & Billing Leads', 'Invoicing, compliance, and accounts reconciliation', NULL, '2026-10-14 12:19:05', '2026-10-14 13:44:06'),
(18, 'Finance & Billing Lead', 'Invoicing, compliance, and accounts reconciliation', NULL, '2026-10-14 13:55:35', '2026-10-14 13:55:35'),
(47, 'System Administrator', 'Full administrative access', NULL, '2026-10-19 07:36:28', '2026-10-19 07:36:28');

-- --------------------------------------------------------

--
-- Table structure for table `sharing_rules`
--

CREATE TABLE `sharing_rules` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `module` varchar(60) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `rule_type` varchar(30) NOT NULL DEFAULT 'criteria_based',
  `criteria_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`criteria_json`)),
  `ownership_role_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ownership_profile_id` bigint(20) UNSIGNED DEFAULT NULL,
  `share_with_type` varchar(30) NOT NULL,
  `share_with_id` varchar(60) NOT NULL,
  `access_level` varchar(30) NOT NULL DEFAULT 'read',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` varchar(100) NOT NULL DEFAULT 'System Admin',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sharing_rules`
--

INSERT INTO `sharing_rules` (`id`, `module`, `name`, `description`, `rule_type`, `criteria_json`, `ownership_role_id`, `ownership_profile_id`, `share_with_type`, `share_with_id`, `access_level`, `is_active`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'opportunities', 'Mega Deals Procurement Sync', 'Automatically grants procurement specialists read-only visibility into pipeline opportunities exceeding $1,000,000 for early vendor alignment.', 'criteria_based', '[{\"field\":\"amount\",\"operator\":\"greater_or_equal\",\"value\":\"1000000\"}]', NULL, NULL, 'profile', '5', 'read', 1, 'System Provisioner', '2026-10-16 10:16:27', '2026-10-16 10:16:27'),
(2, 'accounts', 'Enterprise Customer Account Visibility', 'Shares top-tier enterprise customer accounts ($5M+ annual revenue) with Executive leadership for strategic relationship management.', 'criteria_based', '[{\"field\":\"type\",\"operator\":\"equals\",\"value\":\"Customer\"},{\"field\":\"annual_revenue\",\"operator\":\"greater_or_equal\",\"value\":\"5000000\"}]', NULL, NULL, 'role', '1', 'read_write', 1, 'System Provisioner', '2026-10-16 10:16:27', '2026-10-16 10:16:27'),
(3, 'purchase_deals', 'High-Value PO Spend Fiscal Audit', 'Automatically mirrors all purchase deals with committed spend of $500,000+ to corporate finance for proactive budget approval.', 'criteria_based', '[{\"field\":\"amount\",\"operator\":\"greater_or_equal\",\"value\":\"500000\"}]', NULL, NULL, 'profile', '1', 'read', 1, 'System Provisioner', '2026-10-16 10:16:27', '2026-10-16 10:16:27'),
(4, 'leads', 'Cross-Territory Sales Lead Collaboration', 'Shares high-propensity leads owned by standard sales reps with regional sales executives for joint enterprise pitching.', 'ownership_based', NULL, NULL, 4, 'role', '2', 'read_write', 1, 'System Provisioner', '2026-10-16 10:16:27', '2026-10-16 10:16:27');

-- --------------------------------------------------------

--
-- Table structure for table `shipments`
--

CREATE TABLE `shipments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `account_id` bigint(20) UNSIGNED NOT NULL,
  `opportunity_id` bigint(20) UNSIGNED DEFAULT NULL,
  `purchase_deal_id` bigint(20) UNSIGNED DEFAULT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'Outbound',
  `carrier` varchar(80) NOT NULL,
  `tracking_number` varchar(100) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Booked',
  `shipping_origin` text DEFAULT NULL,
  `destination_address` text DEFAULT NULL,
  `dispatch_date` date DEFAULT NULL,
  `estimated_delivery_date` date DEFAULT NULL,
  `actual_delivery_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `custom_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_data`)),
  `assigned_to` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `standard_field_overrides`
--

CREATE TABLE `standard_field_overrides` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `module` varchar(60) NOT NULL,
  `field_name` varchar(100) NOT NULL,
  `field_label` varchar(150) DEFAULT NULL,
  `is_required` tinyint(1) DEFAULT NULL,
  `options` text DEFAULT NULL,
  `help_text` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `standard_field_overrides`
--

INSERT INTO `standard_field_overrides` (`id`, `module`, `field_name`, `field_label`, `is_required`, `options`, `help_text`, `updated_at`) VALUES
(1, 'leads', 'status', 'Lifecycle Qualification Stage', 1, 'New, Working, Qualified, Unqualified', 'Override description for lead status', '2026-10-19 08:55:17');

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `subject` varchar(200) NOT NULL,
  `priority` varchar(30) NOT NULL DEFAULT 'Normal',
  `status` varchar(40) NOT NULL DEFAULT 'Open',
  `assigned_to` bigint(20) UNSIGNED DEFAULT NULL,
  `assigned_to_name` varchar(150) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `related_entity_type` varchar(60) DEFAULT NULL,
  `related_entity_id` bigint(20) UNSIGNED DEFAULT NULL,
  `related_entity_name` varchar(150) DEFAULT NULL,
  `reminder_set` varchar(100) NOT NULL DEFAULT 'No reminder is set',
  `is_recurring` tinyint(1) NOT NULL DEFAULT 0,
  `recurrence_pattern` varchar(30) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by_name` varchar(150) DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by_name` varchar(150) DEFAULT NULL,
  `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tenant_api_keys`
--

CREATE TABLE `tenant_api_keys` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `key_prefix` varchar(20) NOT NULL,
  `key_hash` varchar(128) NOT NULL,
  `scopes` longtext NOT NULL,
  `created_by_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by` varchar(100) NOT NULL DEFAULT 'System Admin',
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tenant_backups`
--

CREATE TABLE `tenant_backups` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_size_bytes` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `record_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `modules_json` longtext NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'completed',
  `created_by` varchar(100) NOT NULL DEFAULT 'System Admin',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tenant_currencies`
--

CREATE TABLE `tenant_currencies` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `currency_code` varchar(10) NOT NULL,
  `currency_symbol` varchar(10) NOT NULL,
  `currency_name` varchar(60) NOT NULL,
  `exchange_rate` decimal(15,6) NOT NULL DEFAULT 1.000000,
  `is_base` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tenant_currencies`
--

INSERT INTO `tenant_currencies` (`id`, `currency_code`, `currency_symbol`, `currency_name`, `exchange_rate`, `is_base`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'USD', '$', 'US Dollar', 1.000000, 1, 1, '2026-10-14 09:40:20', '2026-10-19 08:55:07'),
(2, 'EUR', '€', 'Euro', 0.920000, 0, 1, '2026-10-14 09:40:20', '2026-10-19 08:55:07'),
(3, 'GBP', '£', 'British Pound', 0.790000, 0, 1, '2026-10-14 09:40:20', '2026-10-19 08:55:07'),
(4, 'INR', '₹', 'Indian Rupee', 83.498676, 0, 1, '2026-10-14 09:40:20', '2026-10-19 08:55:07'),
(5, 'AED', 'AED', 'UAE Dirham', 3.669944, 0, 1, '2026-10-14 09:40:20', '2026-10-19 08:55:07');

-- --------------------------------------------------------

--
-- Table structure for table `tenant_invoices`
--

CREATE TABLE `tenant_invoices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `subscription_id` bigint(20) UNSIGNED NOT NULL,
  `invoice_number` varchar(80) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'USD',
  `seats_purchased` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `seat_count_after` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `description` text NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'paid',
  `payment_method_last4` varchar(10) NOT NULL DEFAULT '4242',
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tenant_invoices`
--

INSERT INTO `tenant_invoices` (`id`, `subscription_id`, `invoice_number`, `amount`, `currency`, `seats_purchased`, `seat_count_after`, `description`, `status`, `payment_method_last4`, `paid_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'INV-2026-001', 2067.00, 'USD', 53, 53, 'Initial Enterprise Tier Subscription (53 Seats)', 'paid', '4242', '2026-09-29 12:42:58', '2026-10-14 12:42:58', '2026-10-14 12:42:58'),
(2, 1, 'INV-2026-ZJBGD3', 156.00, 'USD', 4, 32, 'Capacity Add-On (+4 Seats Expansion)', 'paid', '8888', '2026-10-14 12:42:58', '2026-10-14 12:42:58', '2026-10-14 12:42:58'),
(3, 1, 'INV-2026-F0KGC1', 78.00, 'USD', 2, 34, 'Capacity Add-On (+2 Seats Expansion)', 'paid', '9999', '2026-10-14 12:42:59', '2026-10-14 12:42:59', '2026-10-14 12:42:59'),
(4, 1, 'INV-2026-GMC3FS', 156.00, 'USD', 4, 32, 'Capacity Add-On (+4 Seats Expansion)', 'paid', '8888', '2026-10-14 12:43:12', '2026-10-14 12:43:12', '2026-10-14 12:43:12'),
(5, 1, 'INV-2026-1MTSDY', 78.00, 'USD', 2, 34, 'Capacity Add-On (+2 Seats Expansion)', 'paid', '9999', '2026-10-14 12:43:12', '2026-10-14 12:43:12', '2026-10-14 12:43:12'),
(6, 1, 'INV-2026-7FIUCH', 156.00, 'USD', 4, 32, 'Capacity Add-On (+4 Seats Expansion)', 'paid', '8888', '2026-10-14 13:25:02', '2026-10-14 13:25:02', '2026-10-14 13:25:02'),
(7, 1, 'INV-2026-KHRXKN', 78.00, 'USD', 2, 34, 'Capacity Add-On (+2 Seats Expansion)', 'paid', '9999', '2026-10-14 13:25:02', '2026-10-14 13:25:02', '2026-10-14 13:25:02'),
(8, 1, 'INV-2026-WOXIPT', 156.00, 'USD', 4, 32, 'Capacity Add-On (+4 Seats Expansion)', 'paid', '8888', '2026-10-14 13:27:56', '2026-10-14 13:27:56', '2026-10-14 13:27:56'),
(9, 1, 'INV-2026-UNJPYP', 78.00, 'USD', 2, 34, 'Capacity Add-On (+2 Seats Expansion)', 'paid', '9999', '2026-10-14 13:27:56', '2026-10-14 13:27:56', '2026-10-14 13:27:56'),
(10, 1, 'INV-2026-YYZ4VM', 156.00, 'USD', 4, 32, 'Capacity Add-On (+4 Seats Expansion)', 'paid', '8888', '2026-10-14 13:29:25', '2026-10-14 13:29:25', '2026-10-14 13:29:25'),
(11, 1, 'INV-2026-0BOZPN', 78.00, 'USD', 2, 34, 'Capacity Add-On (+2 Seats Expansion)', 'paid', '9999', '2026-10-14 13:29:25', '2026-10-14 13:29:25', '2026-10-14 13:29:25'),
(12, 1, 'INV-2026-RYD6U4', 156.00, 'USD', 4, 32, 'Capacity Add-On (+4 Seats Expansion)', 'paid', '8888', '2026-10-14 13:55:26', '2026-10-14 13:55:26', '2026-10-14 13:55:26'),
(13, 1, 'INV-2026-HZCGVS', 78.00, 'USD', 2, 34, 'Capacity Add-On (+2 Seats Expansion)', 'paid', '9999', '2026-10-14 13:55:26', '2026-10-14 13:55:26', '2026-10-14 13:55:26'),
(14, 1, 'INV-2026-H4U6KR', 156.00, 'USD', 4, 32, 'Capacity Add-On (+4 Seats Expansion)', 'paid', '8888', '2026-10-14 14:19:44', '2026-10-14 14:19:44', '2026-10-14 14:19:44'),
(15, 1, 'INV-2026-GUX9IB', 78.00, 'USD', 2, 34, 'Capacity Add-On (+2 Seats Expansion)', 'paid', '9999', '2026-10-14 14:19:44', '2026-10-14 14:19:44', '2026-10-14 14:19:44'),
(16, 1, 'INV-2026-FYGLUS', 156.00, 'USD', 4, 32, 'Capacity Add-On (+4 Seats Expansion)', 'paid', '8888', '2026-10-14 14:38:43', '2026-10-14 14:38:43', '2026-10-14 14:38:43'),
(17, 1, 'INV-2026-4J1LAQ', 78.00, 'USD', 2, 34, 'Capacity Add-On (+2 Seats Expansion)', 'paid', '9999', '2026-10-14 14:38:43', '2026-10-14 14:38:43', '2026-10-14 14:38:43'),
(18, 1, 'INV-2026-QQJ8QH', 156.00, 'USD', 4, 32, 'Capacity Add-On (+4 Seats Expansion)', 'paid', '8888', '2026-10-15 13:59:17', '2026-10-15 13:59:17', '2026-10-15 13:59:17'),
(19, 1, 'INV-2026-CH2FRN', 78.00, 'USD', 2, 34, 'Capacity Add-On (+2 Seats Expansion)', 'paid', '9999', '2026-10-15 13:59:17', '2026-10-15 13:59:17', '2026-10-15 13:59:17'),
(20, 1, 'INV-2026-NG1WAV', 156.00, 'USD', 4, 32, 'Capacity Add-On (+4 Seats Expansion)', 'paid', '8888', '2026-10-15 14:09:42', '2026-10-15 14:09:42', '2026-10-15 14:09:42'),
(21, 1, 'INV-2026-QGWCXT', 78.00, 'USD', 2, 34, 'Capacity Add-On (+2 Seats Expansion)', 'paid', '9999', '2026-10-15 14:09:42', '2026-10-15 14:09:42', '2026-10-15 14:09:42'),
(22, 1, 'INV-2026-3NNRRB', 156.00, 'USD', 4, 32, 'Capacity Add-On (+4 Seats Expansion)', 'paid', '8888', '2026-10-16 07:37:58', '2026-10-16 07:37:58', '2026-10-16 07:37:58'),
(23, 1, 'INV-2026-JTN2XI', 78.00, 'USD', 2, 34, 'Capacity Add-On (+2 Seats Expansion)', 'paid', '9999', '2026-10-16 07:37:58', '2026-10-16 07:37:58', '2026-10-16 07:37:58'),
(24, 1, 'INV-2026-LWXURA', 156.00, 'USD', 4, 32, 'Capacity Add-On (+4 Seats Expansion)', 'paid', '8888', '2026-10-16 07:54:13', '2026-10-16 07:54:13', '2026-10-16 07:54:13'),
(25, 1, 'INV-2026-GPEBJY', 78.00, 'USD', 2, 34, 'Capacity Add-On (+2 Seats Expansion)', 'paid', '9999', '2026-10-16 07:54:14', '2026-10-16 07:54:14', '2026-10-16 07:54:14'),
(26, 1, 'INV-2026-SJDU9L', 156.00, 'USD', 4, 32, 'Capacity Add-On (+4 Seats Expansion)', 'paid', '8888', '2026-10-16 08:25:47', '2026-10-16 08:25:47', '2026-10-16 08:25:47'),
(27, 1, 'INV-2026-ZTDPNQ', 78.00, 'USD', 2, 34, 'Capacity Add-On (+2 Seats Expansion)', 'paid', '9999', '2026-10-16 08:25:47', '2026-10-16 08:25:47', '2026-10-16 08:25:47'),
(28, 1, 'INV-2026-NXCN7S', 156.00, 'USD', 4, 32, 'Capacity Add-On (+4 Seats Expansion)', 'paid', '8888', '2026-10-16 09:05:11', '2026-10-16 09:05:11', '2026-10-16 09:05:11'),
(29, 1, 'INV-2026-T18IK2', 78.00, 'USD', 2, 34, 'Capacity Add-On (+2 Seats Expansion)', 'paid', '9999', '2026-10-16 09:05:11', '2026-10-16 09:05:11', '2026-10-16 09:05:11'),
(30, 1, 'INV-2026-OK53VT', 156.00, 'USD', 4, 32, 'Capacity Add-On (+4 Seats Expansion)', 'paid', '8888', '2026-10-16 09:29:24', '2026-10-16 09:29:24', '2026-10-16 09:29:24'),
(31, 1, 'INV-2026-FZLRQH', 78.00, 'USD', 2, 34, 'Capacity Add-On (+2 Seats Expansion)', 'paid', '9999', '2026-10-16 09:29:24', '2026-10-16 09:29:24', '2026-10-16 09:29:24'),
(32, 1, 'INV-2026-RXZSFE', 156.00, 'USD', 4, 32, 'Capacity Add-On (+4 Seats Expansion)', 'paid', '8888', '2026-10-16 09:51:39', '2026-10-16 09:51:39', '2026-10-16 09:51:39'),
(33, 1, 'INV-2026-5XBE5C', 78.00, 'USD', 2, 34, 'Capacity Add-On (+2 Seats Expansion)', 'paid', '9999', '2026-10-16 09:51:39', '2026-10-16 09:51:39', '2026-10-16 09:51:39'),
(34, 1, 'INV-2026-IXS7YO', 156.00, 'USD', 4, 32, 'Capacity Add-On (+4 Seats Expansion)', 'paid', '8888', '2026-10-16 10:01:27', '2026-10-16 10:01:27', '2026-10-16 10:01:27'),
(35, 1, 'INV-2026-E4WFU4', 78.00, 'USD', 2, 34, 'Capacity Add-On (+2 Seats Expansion)', 'paid', '9999', '2026-10-16 10:01:27', '2026-10-16 10:01:27', '2026-10-16 10:01:27'),
(36, 1, 'INV-2026-5FYKLU', 156.00, 'USD', 4, 32, 'Capacity Add-On (+4 Seats Expansion)', 'paid', '8888', '2026-10-16 10:09:35', '2026-10-16 10:09:35', '2026-10-16 10:09:35'),
(37, 1, 'INV-2026-MTPPRC', 78.00, 'USD', 2, 34, 'Capacity Add-On (+2 Seats Expansion)', 'paid', '9999', '2026-10-16 10:09:35', '2026-10-16 10:09:35', '2026-10-16 10:09:35'),
(38, 1, 'INV-2026-FI3PIS', 156.00, 'USD', 4, 32, 'Capacity Add-On (+4 Seats Expansion)', 'paid', '8888', '2026-10-16 10:16:47', '2026-10-16 10:16:47', '2026-10-16 10:16:47'),
(39, 1, 'INV-2026-KT69M5', 78.00, 'USD', 2, 34, 'Capacity Add-On (+2 Seats Expansion)', 'paid', '9999', '2026-10-16 10:16:47', '2026-10-16 10:16:47', '2026-10-16 10:16:47'),
(40, 1, 'INV-2026-VHB2KC', 156.00, 'USD', 4, 32, 'Capacity Add-On (+4 Seats Expansion)', 'paid', '8888', '2026-10-16 11:16:28', '2026-10-16 11:16:28', '2026-10-16 11:16:28'),
(41, 1, 'INV-2026-4ATWKR', 78.00, 'USD', 2, 34, 'Capacity Add-On (+2 Seats Expansion)', 'paid', '9999', '2026-10-16 11:16:28', '2026-10-16 11:16:28', '2026-10-16 11:16:28'),
(42, 1, 'INV-2026-BVTEBD', 156.00, 'USD', 4, 32, 'Capacity Add-On (+4 Seats Expansion)', 'paid', '8888', '2026-10-16 11:18:02', '2026-10-16 11:18:02', '2026-10-16 11:18:02'),
(43, 1, 'INV-2026-EXXLSX', 78.00, 'USD', 2, 34, 'Capacity Add-On (+2 Seats Expansion)', 'paid', '9999', '2026-10-16 11:18:02', '2026-10-16 11:18:02', '2026-10-16 11:18:02'),
(44, 1, 'INV-2026-11AKO2', 156.00, 'USD', 4, 32, 'Capacity Add-On (+4 Seats Expansion)', 'paid', '8888', '2026-10-16 11:41:54', '2026-10-16 11:41:54', '2026-10-16 11:41:54'),
(45, 1, 'INV-2026-90P8Y8', 78.00, 'USD', 2, 34, 'Capacity Add-On (+2 Seats Expansion)', 'paid', '9999', '2026-10-16 11:41:54', '2026-10-16 11:41:54', '2026-10-16 11:41:54'),
(46, 1, 'INV-2026-NID3YZ', 156.00, 'USD', 4, 32, 'Capacity Add-On (+4 Seats Expansion)', 'paid', '8888', '2026-10-16 11:49:42', '2026-10-16 11:49:42', '2026-10-16 11:49:42'),
(47, 1, 'INV-2026-KSDZH1', 78.00, 'USD', 2, 34, 'Capacity Add-On (+2 Seats Expansion)', 'paid', '9999', '2026-10-16 11:49:42', '2026-10-16 11:49:42', '2026-10-16 11:49:42'),
(48, 1, 'INV-2026-AFPTGN', 156.00, 'USD', 4, 32, 'Capacity Add-On (+4 Seats Expansion)', 'paid', '8888', '2026-10-16 11:58:26', '2026-10-16 11:58:26', '2026-10-16 11:58:26'),
(49, 1, 'INV-2026-XRXQAT', 78.00, 'USD', 2, 34, 'Capacity Add-On (+2 Seats Expansion)', 'paid', '9999', '2026-10-16 11:58:26', '2026-10-16 11:58:26', '2026-10-16 11:58:26'),
(50, 1, 'INV-2026-A4DWTL', 156.00, 'USD', 4, 32, 'Capacity Add-On (+4 Seats Expansion)', 'paid', '8888', '2026-10-16 12:17:43', '2026-10-16 12:17:43', '2026-10-16 12:17:43'),
(51, 1, 'INV-2026-DJCR6C', 78.00, 'USD', 2, 34, 'Capacity Add-On (+2 Seats Expansion)', 'paid', '9999', '2026-10-16 12:17:43', '2026-10-16 12:17:43', '2026-10-16 12:17:43'),
(52, 1, 'INV-2026-SODQPR', 156.00, 'USD', 4, 32, 'Capacity Add-On (+4 Seats Expansion)', 'paid', '8888', '2026-10-16 12:34:00', '2026-10-16 12:34:00', '2026-10-16 12:34:00'),
(53, 1, 'INV-2026-KHDL8Q', 78.00, 'USD', 2, 34, 'Capacity Add-On (+2 Seats Expansion)', 'paid', '9999', '2026-10-16 12:34:00', '2026-10-16 12:34:00', '2026-10-16 12:34:00'),
(54, 1, 'INV-2026-SDVCXX', 156.00, 'USD', 4, 32, 'Capacity Add-On (+4 Seats Expansion)', 'paid', '8888', '2026-10-19 07:37:31', '2026-10-19 07:37:31', '2026-10-19 07:37:31'),
(55, 1, 'INV-2026-TKA5U1', 78.00, 'USD', 2, 34, 'Capacity Add-On (+2 Seats Expansion)', 'paid', '9999', '2026-10-19 07:37:32', '2026-10-19 07:37:32', '2026-10-19 07:37:32'),
(56, 1, 'INV-2026-PEYG5V', 156.00, 'USD', 4, 32, 'Capacity Add-On (+4 Seats Expansion)', 'paid', '8888', '2026-10-19 08:00:43', '2026-10-19 08:00:43', '2026-10-19 08:00:43'),
(57, 1, 'INV-2026-CJRPTB', 78.00, 'USD', 2, 34, 'Capacity Add-On (+2 Seats Expansion)', 'paid', '9999', '2026-10-19 08:00:43', '2026-10-19 08:00:43', '2026-10-19 08:00:43'),
(58, 1, 'INV-2026-F2GS5J', 156.00, 'USD', 4, 32, 'Capacity Add-On (+4 Seats Expansion)', 'paid', '8888', '2026-10-19 08:14:20', '2026-10-19 08:14:20', '2026-10-19 08:14:20'),
(59, 1, 'INV-2026-AJW4O2', 78.00, 'USD', 2, 34, 'Capacity Add-On (+2 Seats Expansion)', 'paid', '9999', '2026-10-19 08:14:20', '2026-10-19 08:14:20', '2026-10-19 08:14:20'),
(60, 1, 'INV-2026-QZURTX', 156.00, 'USD', 4, 32, 'Capacity Add-On (+4 Seats Expansion)', 'paid', '8888', '2026-10-19 08:31:41', '2026-10-19 08:31:41', '2026-10-19 08:31:41'),
(61, 1, 'INV-2026-TEXG9H', 78.00, 'USD', 2, 34, 'Capacity Add-On (+2 Seats Expansion)', 'paid', '9999', '2026-10-19 08:31:41', '2026-10-19 08:31:41', '2026-10-19 08:31:41'),
(62, 1, 'INV-2026-0S2ZTS', 156.00, 'USD', 4, 32, 'Capacity Add-On (+4 Seats Expansion)', 'paid', '8888', '2026-10-19 08:33:06', '2026-10-19 08:33:06', '2026-10-19 08:33:06'),
(63, 1, 'INV-2026-UXCUIB', 78.00, 'USD', 2, 34, 'Capacity Add-On (+2 Seats Expansion)', 'paid', '9999', '2026-10-19 08:33:06', '2026-10-19 08:33:06', '2026-10-19 08:33:06'),
(64, 1, 'INV-2026-IKPEMU', 156.00, 'USD', 4, 32, 'Capacity Add-On (+4 Seats Expansion)', 'paid', '8888', '2026-10-19 08:34:30', '2026-10-19 08:34:30', '2026-10-19 08:34:30'),
(65, 1, 'INV-2026-BXOQIH', 78.00, 'USD', 2, 34, 'Capacity Add-On (+2 Seats Expansion)', 'paid', '9999', '2026-10-19 08:34:30', '2026-10-19 08:34:30', '2026-10-19 08:34:30'),
(66, 1, 'INV-2026-9SN5PW', 156.00, 'USD', 4, 32, 'Capacity Add-On (+4 Seats Expansion)', 'paid', '8888', '2026-10-19 08:55:20', '2026-10-19 08:55:20', '2026-10-19 08:55:20'),
(67, 1, 'INV-2026-IOQFXD', 78.00, 'USD', 2, 34, 'Capacity Add-On (+2 Seats Expansion)', 'paid', '9999', '2026-10-19 08:55:21', '2026-10-19 08:55:21', '2026-10-19 08:55:21');

-- --------------------------------------------------------

--
-- Table structure for table `tenant_settings`
--

CREATE TABLE `tenant_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(100) NOT NULL,
  `value` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tenant_settings`
--

INSERT INTO `tenant_settings` (`id`, `key`, `value`, `created_at`, `updated_at`) VALUES
(1, 'system_preferences', '{\"default_currency\":\"USD\",\"fiscal_year_start_month\":1,\"working_days\":\"Mon,Tue,Wed,Thu,Fri\",\"working_hours_start\":\"09:00\",\"working_hours_end\":\"18:00\",\"allow_self_password_change\":true,\"session_timeout_minutes\":60,\"max_failed_login_attempts\":5}', '2026-10-14 09:40:20', '2026-10-14 09:40:20'),
(2, 'company_profile', '{\"company_name\":\"DealSync Central Demo\",\"legal_name\":\"DealSync Central Demo\",\"time_zone\":\"America\\/New_York\",\"date_format\":\"YYYY-MM-DD\",\"time_format\":\"12h\",\"branding_theme\":\"apple\",\"accent_color\":\"#4F46E5\"}', '2026-10-14 09:40:20', '2026-10-14 09:40:20'),
(3, 'lead_conversion_mappings', '[{\"source_field_name\":\"company\",\"source_field_label\":\"Company Name\",\"source_data_type\":\"Text\",\"target_module\":\"accounts\",\"target_field_name\":\"name\",\"target_field_label\":\"Account Name\",\"target_data_type\":\"Text\",\"is_active\":1},{\"source_field_name\":\"email\",\"source_field_label\":\"Email Address\",\"source_data_type\":\"Email\",\"target_module\":\"contacts\",\"target_field_name\":\"email\",\"target_field_label\":\"Email Address\",\"target_data_type\":\"Email\",\"is_active\":1}]', '2026-10-16 11:17:52', '2026-10-16 11:17:52');

-- --------------------------------------------------------

--
-- Table structure for table `tenant_subscriptions`
--

CREATE TABLE `tenant_subscriptions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `plan_tier` varchar(40) NOT NULL DEFAULT 'professional',
  `seat_count` int(10) UNSIGNED NOT NULL DEFAULT 5,
  `unit_price_monthly` decimal(10,2) NOT NULL DEFAULT 39.00,
  `billing_cycle` varchar(20) NOT NULL DEFAULT 'monthly',
  `status` varchar(30) NOT NULL DEFAULT 'active',
  `current_period_start` date NOT NULL,
  `current_period_end` date NOT NULL,
  `payment_method_brand` varchar(30) NOT NULL DEFAULT 'Visa',
  `payment_method_last4` varchar(10) NOT NULL DEFAULT '4242',
  `auto_renew` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tenant_subscriptions`
--

INSERT INTO `tenant_subscriptions` (`id`, `plan_tier`, `seat_count`, `unit_price_monthly`, `billing_cycle`, `status`, `current_period_start`, `current_period_end`, `payment_method_brand`, `payment_method_last4`, `auto_renew`, `created_at`, `updated_at`) VALUES
(1, 'enterprise', 34, 39.00, 'monthly', 'active', '2026-10-01', '2026-10-31', 'Mastercard', '9999', 1, '2026-10-14 12:42:58', '2026-10-19 08:55:20');

-- --------------------------------------------------------

--
-- Table structure for table `tenant_webhooks`
--

CREATE TABLE `tenant_webhooks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `target_url` text NOT NULL,
  `secret_key` varchar(128) NOT NULL,
  `event_types` longtext NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `failure_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` bigint(20) UNSIGNED DEFAULT NULL,
  `profile_id` bigint(20) UNSIGNED DEFAULT NULL,
  `manager_id` bigint(20) UNSIGNED DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `mobile_phone` varchar(30) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `avatar_url` text DEFAULT NULL,
  `nickname` varchar(100) DEFAULT NULL,
  `job_title` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `email_signature` text DEFAULT NULL,
  `time_zone_override` varchar(80) DEFAULT NULL,
  `notification_preferences_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`notification_preferences_json`)),
  `linkedin_url` varchar(255) DEFAULT NULL,
  `twitter_url` varchar(255) DEFAULT NULL,
  `github_url` varchar(255) DEFAULT NULL,
  `website_url` varchar(255) DEFAULT NULL,
  `theme_mode` varchar(20) NOT NULL DEFAULT 'light',
  `accent_color` varchar(20) NOT NULL DEFAULT '#4F46E5',
  `status` varchar(30) NOT NULL DEFAULT 'Active',
  `surprise_me_enabled` tinyint(1) DEFAULT NULL,
  `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields`)),
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role_id`, `profile_id`, `manager_id`, `phone`, `mobile_phone`, `avatar`, `avatar_url`, `nickname`, `job_title`, `department`, `bio`, `email_signature`, `time_zone_override`, `notification_preferences_json`, `linkedin_url`, `twitter_url`, `github_url`, `website_url`, `theme_mode`, `accent_color`, `status`, `surprise_me_enabled`, `custom_fields`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'System Administrator', 'admin@dealsync.com', '$2y$04$LZsrgb2YicGnjG66AK5y4uyKm5pIIt4sAisVH1OkQsrYYIrj3Nvgq', 47, 1, NULL, '+1 (555) 999-0011', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'light', '#4F46E5', 'Inactive', 0, NULL, NULL, '2026-10-14 09:40:20', '2026-10-19 08:58:39'),
(2, 'Alex Morgans', 'alex.morgan@dealsync.com', '$2y$04$LeI6p/vbz3j2teDCfPZwiu3pTmETf3wKivhrjAUfacvEkmlw5HS7.', 1, 1, NULL, '+1 (555) 999-0011', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'light', '#4F46E5', 'Inactive', 0, NULL, NULL, '2026-10-14 12:19:05', '2026-10-16 09:23:55'),
(3, 'Jordan Lee', 'jordan.lee@dealsync.com', '$2y$04$gohP.vBaeS9HYLDjO7yLuehvLJtkGTEAodG7GxZkb/ah0MSodUwTa', 2, 4, NULL, '+1 (555) 999-0011', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'light', '#4F46E5', 'Inactive', 0, NULL, NULL, '2026-10-14 12:19:05', '2026-10-14 12:20:33'),
(4, 'Marcus Brody', 'marcus.brody@dealsync.com', '$2y$04$hzPtiscWibj6lUKTQm47IeAy.wm7AsZZd24v/Wi4QJEe231LyQFmq', 5, 5, NULL, '+1 (555) 999-0011', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'light', '#4F46E5', 'Inactive', 0, NULL, NULL, '2026-10-14 12:19:05', '2026-10-14 12:21:27'),
(5, 'Sarah Chen', 'sarah.chen@dealsync.com', '$2y$04$7Up70B3AP7COIj6UGxymoeJDmUEYs2TZWzDhonx0P0Pd3zYObXCEO', 9, 1, NULL, '+1 (555) 999-0011', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'light', '#4F46E5', 'Inactive', 0, NULL, NULL, '2026-10-14 12:19:05', '2026-10-14 12:43:23'),
(6, 'Clara Oswald', 'clara.test.1792000146@dealsync.com', '$2y$04$I8IWKSa93N6CTPNvHNUZpuJnu8g7pUhins/5Hu5cWlpQ32EiPbd5G', 4, 4, NULL, '+1 (555) 999-0011', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'light', '#4F46E5', 'Inactive', 0, NULL, NULL, '2026-10-14 12:19:06', '2026-10-14 13:25:12'),
(7, 'Clara Oswald', 'clara.test.1792000207@dealsync.com', '$2y$04$AuUHvCXj9eLSCo5JlQGxiu6MVkuPwAPKOT4Ww3QFp/wPsoYsDaXJe', 4, 4, NULL, '+1 (555) 999-0011', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'light', '#4F46E5', 'Inactive', 0, NULL, NULL, '2026-10-14 12:20:07', '2026-10-14 13:28:05'),
(8, 'Clara Oswald', 'clara.test.1792000232@dealsync.com', '$2y$04$1BPTGADUH2W8MvxhtudW5.BwiTFmWUzCC2gkYzFHEhTDxpaR8dPmm', 4, 4, NULL, '+1 (555) 999-0011', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'light', '#4F46E5', 'Inactive', 0, NULL, NULL, '2026-10-14 12:20:32', '2026-10-14 13:29:35'),
(9, 'Clara Oswald', 'clara.test.1792000286@dealsync.com', '$2y$04$uiw9aMkWEtP8Y4gC4sj4fOGNxHGtrYTu4rBqE5Z/gR29p6H2u21xC', 4, 4, NULL, '+1 (555) 999-0011', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'light', '#4F46E5', 'Inactive', 0, NULL, NULL, '2026-10-14 12:21:26', '2026-10-14 13:55:37'),
(10, 'Clara Oswald', 'clara.test.1792001602@dealsync.com', '$2y$04$ClDvt2OOwjqC.LQNKo.3JOmwPhqqzIbmOFvumNWxoOhC/0Jo6kEfO', 4, 4, NULL, '+1 (555) 999-0011', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'light', '#4F46E5', 'Inactive', 0, NULL, NULL, '2026-10-14 12:43:22', '2026-10-14 14:19:55'),
(11, 'Clara Oswald', 'clara.test.1792004111@dealsync.com', '$2y$04$ZLa6cnoWEkNKh6nbdUFD3u7yFRzeysIwGmker.JhLMOk8O50ssW.y', 4, 4, NULL, '+1 (555) 999-0011', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'light', '#4F46E5', 'Inactive', 0, NULL, NULL, '2026-10-14 13:25:11', '2026-10-14 14:38:47'),
(12, 'Clara Oswald', 'clara.test.1792004285@dealsync.com', '$2y$04$7b0MO6ro6DYJxVQfsauqYezGpXkFK9c9EE77SXOApddDP1Fs6o5l2', 4, 4, NULL, '+1 (555) 999-0011', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'light', '#4F46E5', 'Inactive', 0, NULL, NULL, '2026-10-14 13:28:05', '2026-10-16 07:38:02'),
(13, 'Clara Oswald', 'clara.test.1792004375@dealsync.com', '$2y$04$D1XCDiPlUV3p4O8zvYDg6.wRaMvTyQLf7SZalpxgwc8BeKYx8yLeS', 4, 4, NULL, '+1 (555) 999-0011', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'light', '#4F46E5', 'Inactive', 0, NULL, NULL, '2026-10-14 13:29:35', '2026-10-16 07:54:19'),
(14, 'Clara Oswald', 'clara.test.1792005936@dealsync.com', '$2y$04$XHHGXVOVuEljW.M6hhYF1OuVcSIpqqGKsYt8.5PYqt0vexicoMFCq', 4, 4, NULL, '+1 (555) 999-0011', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'light', '#4F46E5', 'Inactive', 0, NULL, NULL, '2026-10-14 13:55:36', '2026-10-16 08:15:50'),
(15, 'Clara Oswald', 'clara.test.1792007394@dealsync.com', '$2y$04$UEkgNr735ITHdcgpGETlm.h/1tUC7OuEpAEEScrmV7w1Sbhth9yl6', 4, 4, NULL, '+1 (555) 999-0011', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'light', '#4F46E5', 'Inactive', 0, NULL, NULL, '2026-10-14 14:19:54', '2026-10-16 08:25:38'),
(16, 'Clara Oswald', 'clara.test.1792008527@dealsync.com', '$2y$04$v8mXp/bHDPJJSVtl6RYhmOWNQwWoJPGQjY8G7rsKrBEv.zdEL20Hy', 4, 4, NULL, '+1 (555) 999-0011', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'light', '#4F46E5', 'Inactive', 0, NULL, NULL, '2026-10-14 14:38:47', '2026-10-16 08:25:52'),
(17, 'Clara Oswald', 'clara.test.1792156082@dealsync.com', '$2y$04$1QwE8mIHxs1JJ0jaTeLHQedLehPwHG4kZ2701qMIuxxr.4vtULxdK', 4, 4, NULL, '+1 (555) 999-0011', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'light', '#4F46E5', 'Inactive', 0, NULL, NULL, '2026-10-16 07:38:02', '2026-10-16 09:04:59'),
(18, 'Clara Oswald', 'clara.test.1792157058@dealsync.com', '$2y$04$pFbGv2.IW20g8P4oX5uqHegNDuzIuOkPhBMrIOrKjAOOD6PhX719m', 4, 4, NULL, '+1 (555) 999-0011', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'light', '#4F46E5', 'Inactive', 0, NULL, NULL, '2026-10-16 07:54:18', '2026-10-16 09:05:16'),
(19, 'Clara Oswald', 'clara.test.1792158350@dealsync.com', '$2y$04$rsDcCAaaP2JmtjKoQ/wM/eVpgsinndR0b/VSGoNzHbBDOaf4.G3Cq', 4, 4, NULL, '+1 (555) 999-0011', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'light', '#4F46E5', 'Inactive', 0, NULL, NULL, '2026-10-16 08:15:50', '2026-10-16 09:29:28'),
(20, 'Clara Oswald', 'clara.test.1792158937@dealsync.com', '$2y$04$7adZBnzxfjr0ATg5eMu6Q.hiXDQZa4ky0KmcfeqJgXmRpwZofo72u', 4, 4, NULL, '+1 (555) 999-0011', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'light', '#4F46E5', 'Inactive', 0, NULL, NULL, '2026-10-16 08:25:37', '2026-10-16 09:51:43'),
(21, 'Clara Oswald', 'clara.test.1792158952@dealsync.com', '$2y$04$NhkhhktsXz7LB7blatKdNOGQC1GqtUQbeq.kfKr0hom3EsdIYRymS', 4, 4, NULL, '+1 (555) 999-0011', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'light', '#4F46E5', 'Inactive', 0, NULL, NULL, '2026-10-16 08:25:52', '2026-10-16 10:01:32'),
(22, 'Clara Oswald', 'clara.test.1792161299@dealsync.com', '$2y$04$inqT3/0V75XRJ8j9QOzTyeRpy8a2l5Q4ew4RrguWhOX.ER3AfR/em', 4, 4, NULL, '+1 (555) 999-0011', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'light', '#4F46E5', 'Inactive', 0, NULL, NULL, '2026-10-16 09:04:59', '2026-10-16 10:09:40'),
(23, 'Clara Oswald', 'clara.test.1792161316@dealsync.com', '$2y$04$9NUiaYHQrzy14siItmyYLO0LyNG66y/QoNTEDVhxTofwI0kHUFkuC', 4, 4, NULL, '+1 (555) 999-0011', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'light', '#4F46E5', 'Inactive', 0, NULL, NULL, '2026-10-16 09:05:16', '2026-10-16 10:16:51'),
(24, 'Clara Oswald', 'clara.test.1792162768@dealsync.com', '$2y$04$BeIIZWti9shb0dTuH.wT6uJmFDtKQhLQa3jDBN2i0LeCEtJdepgOq', 4, 4, NULL, '+1 (555) 999-0011', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'light', '#4F46E5', 'Inactive', 0, NULL, NULL, '2026-10-16 09:29:28', '2026-10-16 11:16:33'),
(25, 'Clara Oswald', 'clara.test.1792164102@dealsync.com', '$2y$04$LpGiryxEZnQWcrUV1WrrZu7buR7rbyydJWiD.5CpVoHNe0Rspv466', 4, 4, NULL, '+1 (555) 999-0011', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'light', '#4F46E5', 'Inactive', 0, NULL, NULL, '2026-10-16 09:51:42', '2026-10-16 11:18:07'),
(26, 'Clara Oswald', 'clara.test.1792164691@dealsync.com', '$2y$04$XCUaPImn4msPzwUeClWjvu7p/bZKOyXXHXRJtLmW.mXf.mJOOZ9sW', 4, 4, NULL, '+1 (555) 999-0011', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'light', '#4F46E5', 'Inactive', 0, NULL, NULL, '2026-10-16 10:01:31', '2026-10-16 11:41:59'),
(27, 'Clara Oswald', 'clara.test.1792165180@dealsync.com', '$2y$04$mDO3f3RIANYPsv6K3i3Te.KEi6u.BTOptRR39gTahMBjwwRuAV862', 4, 4, NULL, '+1 (555) 999-0011', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'light', '#4F46E5', 'Inactive', 0, NULL, NULL, '2026-10-16 10:09:40', '2026-10-16 11:49:48'),
(28, 'Clara Oswald', 'clara.test.1792165611@dealsync.com', '$2y$04$XalX4YLF7WL3AcNWshieY.cK8t7FUZmfl0VUP4VzGMyjch3vm0hJu', 4, 4, NULL, '+1 (555) 999-0011', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'light', '#4F46E5', 'Inactive', 0, NULL, NULL, '2026-10-16 10:16:51', '2026-10-16 11:58:33'),
(29, 'Clara Oswald', 'clara.test.1792169193@dealsync.com', '$2y$04$SJYCUg02.mght1dulhrtheutT5rHNvtuOEWhcH2LFRtKSJi1BeAvC', 4, 4, NULL, '+1 (555) 999-0011', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'light', '#4F46E5', 'Inactive', 0, NULL, NULL, '2026-10-16 11:16:33', '2026-10-16 12:17:48'),
(30, 'Clara Oswald', 'clara.test.1792169286@dealsync.com', '$2y$04$G.8IAzdjsa9ZMHz4eLAaTea72LxGKkhgaLNXBPlYSNhHPWIVSZfRu', 4, 4, NULL, '+1 (555) 999-0011', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'light', '#4F46E5', 'Inactive', 0, NULL, NULL, '2026-10-16 11:18:06', '2026-10-16 12:34:05'),
(31, 'Clara Oswald', 'clara.test.1792170719@dealsync.com', '$2y$04$uBoDqRk3VjJ94qQno1dCBOc1NdG0vAlYCewVGe6sG49d6RFlWZHgu', 4, 4, NULL, '+1 (555) 999-0011', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'light', '#4F46E5', 'Inactive', 0, NULL, NULL, '2026-10-16 11:41:59', '2026-10-19 08:00:28'),
(32, 'Clara Oswald', 'clara.test.1792171187@dealsync.com', '$2y$04$Y3wmNT83hXMqw6BOowg7V.AILGiACfZd0I4EOdwyob6xvez7ZuBem', 4, 4, NULL, '+1 (555) 999-0011', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'light', '#4F46E5', 'Inactive', 0, NULL, NULL, '2026-10-16 11:49:47', '2026-10-19 08:32:09'),
(33, 'Clara Oswald', 'clara.test.1792171713@dealsync.com', '$2y$04$T0DO4zbMTFqj817NZZK2n.bwjT8HAk4hWmGV7JdQNF7ktwDyZhF/6', 4, 4, NULL, '+1 (555) 999-0011', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'light', '#4F46E5', 'Inactive', 0, NULL, NULL, '2026-10-16 11:58:33', '2026-10-19 08:32:44'),
(34, 'Clara Oswald', 'clara.test.1792172868@dealsync.com', '$2y$04$u3zc4wW7EGuDhClYXyka.u6Efr0uOlT889bznxFPYaH49N6TZwfF2', 4, 4, NULL, '+1 (555) 777-8899', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'light', '#4F46E5', 'Active', 1, NULL, NULL, '2026-10-16 12:17:48', '2026-10-16 12:17:48'),
(35, 'Clara Oswald', 'clara.test.1792173845@dealsync.com', '$2y$04$jXVBG2xHd3t3TvFjBUNQi.P9Gb6j8MZ3CHuJC3M8EiVIBMpFyPEfe', 4, 4, NULL, '+1 (555) 777-8899', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'light', '#4F46E5', 'Active', 1, NULL, NULL, '2026-10-16 12:34:05', '2026-10-16 12:34:05'),
(36, 'Clara Oswald', 'clara.test.1792415265@dealsync.com', '$2y$04$T0xqYwTYyjZfvEAX0SV/kuIS0QQaRVBW0sJ3aMOAiVcU5pp.0XRy2', 4, 4, NULL, '+1 (555) 777-8899', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'light', '#4F46E5', 'Active', 1, NULL, NULL, '2026-10-19 07:37:45', '2026-10-19 07:37:45'),
(37, 'Clara Oswald', 'clara.test.1792416628@dealsync.com', '$2y$04$6RcuOS57glhMEHX2pW87O.RH9NgUj/YavMq3dAT1k.Wsq4XcXZt1a', 4, 4, NULL, '+1 (555) 777-8899', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'light', '#4F46E5', 'Active', 1, NULL, NULL, '2026-10-19 08:00:28', '2026-10-19 08:00:28'),
(38, 'Executive Manager', 'executive.manager@dealsync.com', '$2y$04$dCUN0qIvu82RsQBiHNJMDukiOKKPy/tkqVUXFKDqROv3O0kVwj8xq', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'light', '#4F46E5', 'Active', NULL, NULL, NULL, '2026-10-19 08:00:29', '2026-10-19 08:00:29'),
(39, 'Direct Subordinate', 'subordinate.6ad61b7511077@dealsync.com', '$2y$04$6HWFqbQdvs5wUDxWv.bjwei61gFTs18xKsaXcaCBEQlbuudAIYtpa', NULL, NULL, 40, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'light', '#4F46E5', 'Active', 1, NULL, NULL, '2026-10-19 08:00:29', '2026-10-19 08:00:29'),
(40, 'Secondary Manager', 'secondary.manager@dealsync.com', '$2y$04$gs6O1YDcrY9SOff8KTiAF.onwIXJOIFk9dRGM.NkDXYaZWjultKCu', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'light', '#4F46E5', 'Active', NULL, NULL, NULL, '2026-10-19 08:00:29', '2026-10-19 08:00:29'),
(41, 'Clara Oswald', 'clara.test.1792416653@dealsync.com', '$2y$04$nNzRIZm716sp5IF4n2S6CO40Qvcyr3//8X8uXsYI9ZAhWYAAeBgbW', 4, 4, NULL, '+1 (555) 777-8899', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'light', '#4F46E5', 'Active', 1, NULL, NULL, '2026-10-19 08:00:53', '2026-10-19 08:00:53'),
(42, 'Direct Subordinate', 'subordinate.6ad61b8ec9b52@dealsync.com', '$2y$04$99H8/0rfIKqiDqODuP1lEOyH5mVoCqukMTJNmegqSykAjKUGIqhw6', NULL, NULL, 40, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'light', '#4F46E5', 'Active', 1, NULL, NULL, '2026-10-19 08:00:54', '2026-10-19 08:00:54'),
(43, 'Clara Oswald', 'clara.test.1792417471@dealsync.com', '$2y$04$7C4MO.OJnqr0Pnsa3nEBN.pdasBKVycfK45TlhfiR.N.qLXuCPO5O', 4, 4, NULL, '+1 (555) 777-8899', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'light', '#4F46E5', 'Active', 1, NULL, NULL, '2026-10-19 08:14:31', '2026-10-19 08:14:31'),
(44, 'Direct Subordinate', 'subordinate.6ad61ec0a98a9@dealsync.com', '$2y$04$YWZeMQCZT/JCuqjmyE6ovOE/vZAtZI6BYS.H69G7lhOyF.k.dGudO', NULL, NULL, 40, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'light', '#4F46E5', 'Active', 1, NULL, NULL, '2026-10-19 08:14:32', '2026-10-19 08:14:32'),
(45, 'Clara Oswald', 'clara.test.1792418513@dealsync.com', '$2y$04$EOFth5qAYKtpedMxTbdvm.0ZjIgeXKQaT6Z4YSGvy3vQkeft9YjBW', 4, 4, NULL, '+1 (555) 777-8899', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'light', '#4F46E5', 'Active', 1, NULL, NULL, '2026-10-19 08:31:53', '2026-10-19 08:31:53'),
(46, 'Direct Subordinate', 'subordinate.6ad622d2c1616@dealsync.com', '$2y$04$fnD7rHJmJLyNz2Rm/E7M3.On/Gv2.Q2yPRqdDvXbgEx03FxEscd0u', NULL, NULL, 40, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'light', '#4F46E5', 'Active', 1, NULL, NULL, '2026-10-19 08:31:54', '2026-10-19 08:31:54'),
(47, 'Clara Oswald', 'clara.test.1792418529@dealsync.com', '$2y$04$VwefiIDBeyMrmPPEaCRaI.a.aBTzu1bsHH6UZHfDShRaTGWGPeOki', 4, 4, NULL, '+1 (555) 777-8899', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'light', '#4F46E5', 'Active', 1, NULL, NULL, '2026-10-19 08:32:09', '2026-10-19 08:32:09'),
(48, 'Direct Subordinate', 'subordinate.6ad622e1bd309@dealsync.com', '$2y$04$Un5eJF6QuDzv7GSTMjq0duzI3lAkMzvLyvk6Iq/PqmGHqKrX3/oJi', NULL, NULL, 40, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'light', '#4F46E5', 'Active', 1, NULL, NULL, '2026-10-19 08:32:09', '2026-10-19 08:32:09'),
(49, 'Clara Oswald', 'clara.test.1792418563@dealsync.com', '$2y$04$UMMkjSfv2WkjOEMeSVMeL.mVNnTrrVouez6sFJdu8e7DOqj73ufvS', 4, 4, NULL, '+1 (555) 777-8899', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'light', '#4F46E5', 'Active', 1, NULL, NULL, '2026-10-19 08:32:43', '2026-10-19 08:32:43'),
(50, 'Direct Subordinate', 'subordinate.6ad6230457110@dealsync.com', '$2y$04$eHx4rHQRjo1qPYYtYj2KQOd8FcuqAFhkzvdwDSfcSPt92LSIQBkgi', NULL, NULL, 40, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'light', '#4F46E5', 'Active', 1, NULL, NULL, '2026-10-19 08:32:44', '2026-10-19 08:32:44'),
(51, 'Clara Oswald', 'clara.test.1792418597@dealsync.com', '$2y$04$gr4lOkGZyO0H6orNrkb6D.yflI/hN6KaTX8fe5bVKATZ5IHfeMyua', 4, 4, NULL, '+1 (555) 777-8899', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'light', '#4F46E5', 'Active', 1, NULL, NULL, '2026-10-19 08:33:17', '2026-10-19 08:33:17'),
(52, 'Direct Subordinate', 'subordinate.6ad62325d1605@dealsync.com', '$2y$04$cwb7avOz6kOFQhM9mXDViuk0WUkCsPkyCkKAvIrO0bdEGSmgqKV..', NULL, NULL, 40, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'light', '#4F46E5', 'Active', 1, NULL, NULL, '2026-10-19 08:33:17', '2026-10-19 08:33:17'),
(53, 'Clara Oswald', 'clara.test.1792419195@dealsync.com', '$2y$04$vI7sYSNHb8KwIX2QVgMxU.ST0uoed7c6lyZH1m/o9dZCzQwwhYgTy', 4, 4, NULL, '+1 (555) 777-8899', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'light', '#4F46E5', 'Active', 1, NULL, NULL, '2026-10-19 08:43:15', '2026-10-19 08:43:15'),
(54, 'Direct Subordinate', 'subordinate.6ad6257c1327f@dealsync.com', '$2y$04$vHqsxw5bRXnU4WAKw6M51eZ0/QJnmlrS0hhz4Vg6y9qhzxJavqIqu', NULL, NULL, 40, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'light', '#4F46E5', 'Active', 1, NULL, NULL, '2026-10-19 08:43:16', '2026-10-19 08:43:16'),
(55, 'Clara Oswald', 'clara.test.1792420118@dealsync.com', '$2y$04$AUyMzUCN5lS8/dRmfuRZxOLVGA51jKIVcIiMODoWByHGHWsVmpM9.', 4, 4, NULL, '+1 (555) 777-8899', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'light', '#4F46E5', 'Active', 1, NULL, NULL, '2026-10-19 08:58:38', '2026-10-19 08:58:38'),
(56, 'Direct Subordinate', 'subordinate.6ad629179c44d@dealsync.com', '$2y$04$TJVsg/HYj4fCVunrc3Piy.Riuph1ZZErCkKqfu1GMdWqODkXrVriW', NULL, NULL, 40, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'light', '#4F46E5', 'Active', 1, NULL, NULL, '2026-10-19 08:58:39', '2026-10-19 08:58:39');

-- --------------------------------------------------------

--
-- Table structure for table `user_invitations`
--

CREATE TABLE `user_invitations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(150) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `role_id` bigint(20) UNSIGNED DEFAULT NULL,
  `profile_id` bigint(20) UNSIGNED DEFAULT NULL,
  `invited_by_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `token` varchar(128) NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `accepted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_invitations`
--

INSERT INTO `user_invitations` (`id`, `email`, `full_name`, `role_id`, `profile_id`, `invited_by_user_id`, `token`, `status`, `expires_at`, `accepted_at`, `created_at`, `updated_at`) VALUES
(1, 'samantha.vance@dealsync.com', 'Samantha Vance', 2, 4, 1, 'tok-xuJrJbfBPWKajQUiShnTrkJT', 'pending', '2026-10-26 08:58:39', NULL, '2026-10-14 12:19:05', '2026-10-19 08:58:39'),
(2, 'david.kim@dealsync.com', 'David Kim', 5, 5, 1, 'tok-dav-EUcrJ2BTiSIH', 'pending', '2026-10-19 12:19:05', NULL, '2026-10-14 12:19:05', '2026-10-14 12:19:05');

-- --------------------------------------------------------

--
-- Table structure for table `user_notifications`
--

CREATE TABLE `user_notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `channel` varchar(30) NOT NULL DEFAULT 'in_app',
  `read` tinyint(1) NOT NULL DEFAULT 0,
  `link_module` varchar(60) DEFAULT NULL,
  `link_id` varchar(60) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `view_configurations`
--

CREATE TABLE `view_configurations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `module` varchar(60) NOT NULL,
  `view_type` varchar(30) NOT NULL,
  `visible_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`visible_fields`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `webhook_delivery_logs`
--

CREATE TABLE `webhook_delivery_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `webhook_id` bigint(20) UNSIGNED NOT NULL,
  `event_type` varchar(100) NOT NULL,
  `payload_json` longtext NOT NULL,
  `status_code` smallint(5) UNSIGNED DEFAULT NULL,
  `response_body` longtext DEFAULT NULL,
  `latency_ms` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `status` varchar(30) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `weight_dims_specs`
--

CREATE TABLE `weight_dims_specs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `parent_type` varchar(60) NOT NULL,
  `parent_id` varchar(60) NOT NULL,
  `title` varchar(150) NOT NULL,
  `weight_unit` varchar(10) NOT NULL DEFAULT 'KG',
  `dim_unit` varchar(10) NOT NULL DEFAULT 'CM',
  `rows_json` longtext NOT NULL,
  `total_packages` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `total_weight` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_cbm` decimal(15,4) NOT NULL DEFAULT 0.0000,
  `notes` text DEFAULT NULL,
  `pickup_address` text DEFAULT NULL,
  `pickup_country` varchar(60) DEFAULT NULL,
  `created_by` varchar(100) NOT NULL DEFAULT 'System Admin',
  `updated_by` varchar(100) NOT NULL DEFAULT 'System Admin',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wires_received`
--

CREATE TABLE `wires_received` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `utr_reference` varchar(100) NOT NULL,
  `remitter_name` varchar(150) NOT NULL,
  `sending_bank` varchar(120) NOT NULL,
  `receiving_bank_account` varchar(80) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'USD',
  `date_received` date NOT NULL,
  `status` varchar(40) NOT NULL DEFAULT 'Pending',
  `reconciled_account_id` bigint(20) UNSIGNED DEFAULT NULL,
  `reconciled_payment_id` bigint(20) UNSIGNED DEFAULT NULL,
  `reconciled_opportunity_id` bigint(20) UNSIGNED DEFAULT NULL,
  `reconciled_by` bigint(20) UNSIGNED DEFAULT NULL,
  `reconciled_at` timestamp NULL DEFAULT NULL,
  `reconciliation_notes` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `accounts_created_by_foreign` (`created_by`),
  ADD KEY `accounts_updated_by_foreign` (`updated_by`),
  ADD KEY `accounts_name_index` (`name`),
  ADD KEY `accounts_type_index` (`type`),
  ADD KEY `accounts_industry_index` (`industry`),
  ADD KEY `accounts_owner_id_index` (`owner_id`);

--
-- Indexes for table `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `activities_performed_by_foreign` (`performed_by`),
  ADD KEY `activities_entity_type_entity_id_index` (`entity_type`,`entity_id`);

--
-- Indexes for table `approval_actions`
--
ALTER TABLE `approval_actions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `approval_actions_actor_id_foreign` (`actor_id`),
  ADD KEY `approval_actions_request_id_index` (`request_id`);

--
-- Indexes for table `approval_processes`
--
ALTER TABLE `approval_processes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `approval_processes_module_is_active_index` (`module`,`is_active`);

--
-- Indexes for table `approval_requests`
--
ALTER TABLE `approval_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `approval_requests_process_id_foreign` (`process_id`),
  ADD KEY `approval_requests_submitted_by_foreign` (`submitted_by`),
  ADD KEY `approval_requests_module_record_id_status_index` (`module`,`record_id`,`status`);

--
-- Indexes for table `approval_steps`
--
ALTER TABLE `approval_steps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `approval_steps_process_id_step_number_index` (`process_id`,`step_number`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `audit_logs_user_id_foreign` (`user_id`),
  ADD KEY `audit_logs_entity_type_entity_id_index` (`entity_type`,`entity_id`),
  ADD KEY `audit_logs_created_at_index` (`created_at`);

--
-- Indexes for table `company_profile`
--
ALTER TABLE `company_profile`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contacts_assigned_to_foreign` (`assigned_to`),
  ADD KEY `contacts_created_by_foreign` (`created_by`),
  ADD KEY `contacts_updated_by_foreign` (`updated_by`),
  ADD KEY `contacts_account_id_is_primary_index` (`account_id`,`is_primary`),
  ADD KEY `contacts_email_index` (`email`);

--
-- Indexes for table `custom_buttons`
--
ALTER TABLE `custom_buttons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `custom_buttons_entity_type_is_active_index` (`entity_type`,`is_active`);

--
-- Indexes for table `custom_fields`
--
ALTER TABLE `custom_fields`
  ADD PRIMARY KEY (`id`),
  ADD KEY `custom_fields_global_picklist_id_foreign` (`global_picklist_id`),
  ADD KEY `custom_fields_module_field_name_index` (`module`,`field_name`);

--
-- Indexes for table `custom_field_values`
--
ALTER TABLE `custom_field_values`
  ADD PRIMARY KEY (`id`),
  ADD KEY `custom_field_values_custom_field_id_foreign` (`custom_field_id`),
  ADD KEY `custom_field_values_module_record_id_index` (`module`,`record_id`);

--
-- Indexes for table `custom_objects`
--
ALTER TABLE `custom_objects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `custom_objects_api_name_unique` (`api_name`);

--
-- Indexes for table `custom_object_records`
--
ALTER TABLE `custom_object_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `custom_object_records_custom_object_id_index` (`custom_object_id`);

--
-- Indexes for table `dashboard_layouts`
--
ALTER TABLE `dashboard_layouts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dashboard_layouts_profile_id_unique` (`profile_id`);

--
-- Indexes for table `data_operation_logs`
--
ALTER TABLE `data_operation_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `data_operation_logs_performed_by_id_foreign` (`performed_by_id`),
  ADD KEY `data_operation_logs_module_created_at_index` (`module`,`created_at`);

--
-- Indexes for table `data_operation_snapshots`
--
ALTER TABLE `data_operation_snapshots`
  ADD PRIMARY KEY (`id`),
  ADD KEY `data_operation_snapshots_operation_id_module_index` (`operation_id`,`module`);

--
-- Indexes for table `document_governance_rights`
--
ALTER TABLE `document_governance_rights`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `document_governance_rights_profile_id_unique` (`profile_id`);

--
-- Indexes for table `document_line_items`
--
ALTER TABLE `document_line_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `document_line_items_document_id_sort_order_index` (`document_id`,`sort_order`);

--
-- Indexes for table `document_snapshots`
--
ALTER TABLE `document_snapshots`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `document_snapshots_document_id_unique` (`document_id`);

--
-- Indexes for table `document_templates`
--
ALTER TABLE `document_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `document_templates_approval_process_id_foreign` (`approval_process_id`),
  ADD KEY `document_templates_document_type_is_active_index` (`document_type`,`is_active`),
  ADD KEY `document_templates_document_type_is_default_index` (`document_type`,`is_default`);

--
-- Indexes for table `duplicate_exception_domains`
--
ALTER TABLE `duplicate_exception_domains`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `duplicate_exception_domains_domain_unique` (`domain`),
  ADD KEY `duplicate_exception_domains_domain_index` (`domain`);

--
-- Indexes for table `duplicate_record_sets`
--
ALTER TABLE `duplicate_record_sets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `duplicate_record_sets_rule_id_foreign` (`rule_id`),
  ADD KEY `duplicate_record_sets_status_index` (`status`),
  ADD KEY `duplicate_record_sets_source_module_primary_record_id_index` (`source_module`,`primary_record_id`);

--
-- Indexes for table `duplicate_rules`
--
ALTER TABLE `duplicate_rules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `duplicate_rules_source_module_is_active_index` (`source_module`,`is_active`);

--
-- Indexes for table `duplicate_rule_conditions`
--
ALTER TABLE `duplicate_rule_conditions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `duplicate_rule_conditions_rule_id_index` (`rule_id`);

--
-- Indexes for table `email_rules`
--
ALTER TABLE `email_rules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email_rules_email_template_id_foreign` (`email_template_id`),
  ADD KEY `email_rules_module_is_active_index` (`module`,`is_active`);

--
-- Indexes for table `email_rule_executions`
--
ALTER TABLE `email_rule_executions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email_rule_executions_rule_id_record_id_index` (`rule_id`,`record_id`);

--
-- Indexes for table `email_templates`
--
ALTER TABLE `email_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email_templates_entity_type_is_active_index` (`entity_type`,`is_active`);

--
-- Indexes for table `field_history`
--
ALTER TABLE `field_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `field_history_changed_by_id_foreign` (`changed_by_id`),
  ADD KEY `field_history_entity_type_entity_id_index` (`entity_type`,`entity_id`);

--
-- Indexes for table `field_history_configs`
--
ALTER TABLE `field_history_configs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `field_history_configs_module_unique` (`module`);

--
-- Indexes for table `field_permissions`
--
ALTER TABLE `field_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `field_permissions_profile_id_module_field_name_unique` (`profile_id`,`module`,`field_name`);

--
-- Indexes for table `files`
--
ALTER TABLE `files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `files_created_by_id_foreign` (`created_by_id`);

--
-- Indexes for table `file_links`
--
ALTER TABLE `file_links`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `file_links_file_id_entity_type_entity_id_unique` (`file_id`,`entity_type`,`entity_id`),
  ADD KEY `file_links_created_by_id_foreign` (`created_by_id`);

--
-- Indexes for table `file_versions`
--
ALTER TABLE `file_versions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `file_versions_uploaded_by_id_foreign` (`uploaded_by_id`),
  ADD KEY `file_versions_file_id_version_number_index` (`file_id`,`version_number`);

--
-- Indexes for table `generated_documents`
--
ALTER TABLE `generated_documents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `generated_documents_document_number_unique` (`document_number`),
  ADD KEY `generated_documents_template_id_foreign` (`template_id`),
  ADD KEY `generated_documents_account_id_foreign` (`account_id`),
  ADD KEY `generated_documents_contact_id_foreign` (`contact_id`),
  ADD KEY `generated_documents_file_id_foreign` (`file_id`),
  ADD KEY `generated_documents_created_by_id_foreign` (`created_by_id`),
  ADD KEY `generated_documents_document_type_status_index` (`document_type`,`status`),
  ADD KEY `generated_documents_related_entity_type_related_entity_id_index` (`related_entity_type`,`related_entity_id`);

--
-- Indexes for table `global_picklists`
--
ALTER TABLE `global_picklists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `global_picklists_api_name_unique` (`api_name`),
  ADD KEY `global_picklists_api_name_index` (`api_name`);

--
-- Indexes for table `global_search_settings`
--
ALTER TABLE `global_search_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `leads`
--
ALTER TABLE `leads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `leads_converted_account_id_foreign` (`converted_account_id`),
  ADD KEY `leads_converted_contact_id_foreign` (`converted_contact_id`),
  ADD KEY `leads_converted_opportunity_id_foreign` (`converted_opportunity_id`),
  ADD KEY `leads_assigned_to_foreign` (`assigned_to`),
  ADD KEY `leads_created_by_foreign` (`created_by`),
  ADD KEY `leads_updated_by_foreign` (`updated_by`),
  ADD KEY `leads_status_index` (`status`),
  ADD KEY `leads_status_assigned_to_index` (`status`,`assigned_to`),
  ADD KEY `leads_email_index` (`email`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migration_batches`
--
ALTER TABLE `migration_batches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `migration_batches_performed_by_id_foreign` (`performed_by_id`),
  ADD KEY `migration_batches_status_index` (`status`);

--
-- Indexes for table `migration_error_logs`
--
ALTER TABLE `migration_error_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `migration_error_logs_migration_batch_id_index` (`migration_batch_id`);

--
-- Indexes for table `migration_id_mappings`
--
ALTER TABLE `migration_id_mappings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `migration_id_mappings_migration_batch_id_legacy_id_index` (`migration_batch_id`,`legacy_id`),
  ADD KEY `migration_id_mappings_source_crm_entity_type_legacy_id_index` (`source_crm`,`entity_type`,`legacy_id`);

--
-- Indexes for table `migration_records`
--
ALTER TABLE `migration_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `migration_records_migration_batch_id_index` (`migration_batch_id`);

--
-- Indexes for table `notification_rules`
--
ALTER TABLE `notification_rules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notification_rules_operation_module_is_active_index` (`operation`,`module`,`is_active`);

--
-- Indexes for table `notification_rule_executions`
--
ALTER TABLE `notification_rule_executions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notification_rule_executions_rule_id_record_id_index` (`rule_id`,`record_id`);

--
-- Indexes for table `opportunities`
--
ALTER TABLE `opportunities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `opportunities_contact_id_foreign` (`contact_id`),
  ADD KEY `opportunities_assigned_to_foreign` (`assigned_to`),
  ADD KEY `opportunities_created_by_foreign` (`created_by`),
  ADD KEY `opportunities_updated_by_foreign` (`updated_by`),
  ADD KEY `opportunities_stage_index` (`stage`),
  ADD KEY `opportunities_account_id_stage_index` (`account_id`,`stage`),
  ADD KEY `opportunities_purchase_deal_id_index` (`purchase_deal_id`);

--
-- Indexes for table `page_layouts`
--
ALTER TABLE `page_layouts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `page_layouts_module_is_default_index` (`module`,`is_default`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payments_invoice_number_unique` (`invoice_number`),
  ADD KEY `payments_opportunity_id_foreign` (`opportunity_id`),
  ADD KEY `payments_purchase_deal_id_foreign` (`purchase_deal_id`),
  ADD KEY `payments_created_by_foreign` (`created_by`),
  ADD KEY `payments_updated_by_foreign` (`updated_by`),
  ADD KEY `payments_invoice_number_index` (`invoice_number`),
  ADD KEY `payments_account_id_status_index` (`account_id`,`status`);

--
-- Indexes for table `private_folder_shares`
--
ALTER TABLE `private_folder_shares`
  ADD PRIMARY KEY (`id`),
  ADD KEY `private_folder_shares_granted_by_id_foreign` (`granted_by_id`),
  ADD KEY `private_folder_shares_entity_type_entity_id_index` (`entity_type`,`entity_id`);

--
-- Indexes for table `process_flows`
--
ALTER TABLE `process_flows`
  ADD PRIMARY KEY (`id`),
  ADD KEY `process_flows_entity_type_status_index` (`entity_type`,`status`),
  ADD KEY `process_flows_trigger_type_status_index` (`trigger_type`,`status`);

--
-- Indexes for table `process_flow_executions`
--
ALTER TABLE `process_flow_executions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `process_flow_executions_flow_id_status_index` (`flow_id`,`status`);

--
-- Indexes for table `profiles`
--
ALTER TABLE `profiles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `profiles_name_index` (`name`);

--
-- Indexes for table `profile_layout_assignments`
--
ALTER TABLE `profile_layout_assignments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `profile_layout_assignments_profile_id_module_unique` (`profile_id`,`module`),
  ADD KEY `profile_layout_assignments_layout_id_foreign` (`layout_id`);

--
-- Indexes for table `profile_permissions`
--
ALTER TABLE `profile_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `profile_permissions_profile_id_module_unique` (`profile_id`,`module`);

--
-- Indexes for table `purchase_deals`
--
ALTER TABLE `purchase_deals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `purchase_deals_po_number_unique` (`po_number`),
  ADD KEY `purchase_deals_contact_id_foreign` (`contact_id`),
  ADD KEY `purchase_deals_assigned_to_foreign` (`assigned_to`),
  ADD KEY `purchase_deals_created_by_foreign` (`created_by`),
  ADD KEY `purchase_deals_updated_by_foreign` (`updated_by`),
  ADD KEY `purchase_deals_stage_index` (`stage`),
  ADD KEY `purchase_deals_account_id_index` (`account_id`);

--
-- Indexes for table `record_shares`
--
ALTER TABLE `record_shares`
  ADD PRIMARY KEY (`id`),
  ADD KEY `record_shares_rule_id_foreign` (`rule_id`),
  ADD KEY `record_shares_module_record_id_index` (`module`,`record_id`);

--
-- Indexes for table `relationship_definitions`
--
ALTER TABLE `relationship_definitions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `relationship_definitions_parent_object_relation_name_unique` (`parent_object`,`relation_name`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `roles_name_index` (`name`),
  ADD KEY `roles_reports_to_role_id_foreign` (`reports_to_role_id`);

--
-- Indexes for table `sharing_rules`
--
ALTER TABLE `sharing_rules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sharing_rules_ownership_role_id_foreign` (`ownership_role_id`),
  ADD KEY `sharing_rules_ownership_profile_id_foreign` (`ownership_profile_id`),
  ADD KEY `sharing_rules_module_is_active_index` (`module`,`is_active`);

--
-- Indexes for table `shipments`
--
ALTER TABLE `shipments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `shipments_opportunity_id_foreign` (`opportunity_id`),
  ADD KEY `shipments_purchase_deal_id_foreign` (`purchase_deal_id`),
  ADD KEY `shipments_assigned_to_foreign` (`assigned_to`),
  ADD KEY `shipments_created_by_foreign` (`created_by`),
  ADD KEY `shipments_updated_by_foreign` (`updated_by`),
  ADD KEY `shipments_tracking_number_index` (`tracking_number`),
  ADD KEY `shipments_status_index` (`status`),
  ADD KEY `shipments_account_id_index` (`account_id`);

--
-- Indexes for table `standard_field_overrides`
--
ALTER TABLE `standard_field_overrides`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `standard_field_overrides_module_field_name_unique` (`module`,`field_name`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tasks_assigned_to_foreign` (`assigned_to`),
  ADD KEY `tasks_created_by_foreign` (`created_by`),
  ADD KEY `tasks_updated_by_foreign` (`updated_by`),
  ADD KEY `tasks_status_index` (`status`),
  ADD KEY `tasks_due_date_index` (`due_date`),
  ADD KEY `tasks_related_entity_type_related_entity_id_index` (`related_entity_type`,`related_entity_id`);

--
-- Indexes for table `tenant_api_keys`
--
ALTER TABLE `tenant_api_keys`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tenant_api_keys_key_hash_unique` (`key_hash`),
  ADD KEY `tenant_api_keys_created_by_id_foreign` (`created_by_id`),
  ADD KEY `tenant_api_keys_is_active_index` (`is_active`);

--
-- Indexes for table `tenant_backups`
--
ALTER TABLE `tenant_backups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tenant_backups_status_index` (`status`);

--
-- Indexes for table `tenant_currencies`
--
ALTER TABLE `tenant_currencies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tenant_currencies_currency_code_unique` (`currency_code`);

--
-- Indexes for table `tenant_invoices`
--
ALTER TABLE `tenant_invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tenant_invoices_invoice_number_unique` (`invoice_number`),
  ADD KEY `tenant_invoices_subscription_id_status_index` (`subscription_id`,`status`);

--
-- Indexes for table `tenant_settings`
--
ALTER TABLE `tenant_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tenant_settings_key_unique` (`key`);

--
-- Indexes for table `tenant_subscriptions`
--
ALTER TABLE `tenant_subscriptions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tenant_webhooks`
--
ALTER TABLE `tenant_webhooks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tenant_webhooks_is_active_index` (`is_active`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_role_id_foreign` (`role_id`),
  ADD KEY `users_profile_id_foreign` (`profile_id`),
  ADD KEY `users_status_index` (`status`),
  ADD KEY `users_manager_id_foreign` (`manager_id`);

--
-- Indexes for table `user_invitations`
--
ALTER TABLE `user_invitations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_invitations_token_unique` (`token`),
  ADD KEY `user_invitations_role_id_foreign` (`role_id`),
  ADD KEY `user_invitations_profile_id_foreign` (`profile_id`),
  ADD KEY `user_invitations_invited_by_user_id_foreign` (`invited_by_user_id`),
  ADD KEY `user_invitations_email_status_index` (`email`,`status`);

--
-- Indexes for table `user_notifications`
--
ALTER TABLE `user_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_notifications_user_id_read_index` (`user_id`,`read`);

--
-- Indexes for table `view_configurations`
--
ALTER TABLE `view_configurations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `view_configurations_module_view_type_unique` (`module`,`view_type`);

--
-- Indexes for table `webhook_delivery_logs`
--
ALTER TABLE `webhook_delivery_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `webhook_delivery_logs_webhook_id_created_at_index` (`webhook_id`,`created_at`);

--
-- Indexes for table `weight_dims_specs`
--
ALTER TABLE `weight_dims_specs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `weight_dims_specs_parent_type_parent_id_index` (`parent_type`,`parent_id`);

--
-- Indexes for table `wires_received`
--
ALTER TABLE `wires_received`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `wires_received_utr_reference_unique` (`utr_reference`),
  ADD KEY `wires_received_reconciled_account_id_foreign` (`reconciled_account_id`),
  ADD KEY `wires_received_reconciled_payment_id_foreign` (`reconciled_payment_id`),
  ADD KEY `wires_received_reconciled_opportunity_id_foreign` (`reconciled_opportunity_id`),
  ADD KEY `wires_received_reconciled_by_foreign` (`reconciled_by`),
  ADD KEY `wires_received_created_by_foreign` (`created_by`),
  ADD KEY `wires_received_updated_by_foreign` (`updated_by`),
  ADD KEY `wires_received_utr_reference_index` (`utr_reference`),
  ADD KEY `wires_received_status_index` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `activities`
--
ALTER TABLE `activities`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `approval_actions`
--
ALTER TABLE `approval_actions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `approval_processes`
--
ALTER TABLE `approval_processes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `approval_requests`
--
ALTER TABLE `approval_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `approval_steps`
--
ALTER TABLE `approval_steps`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `company_profile`
--
ALTER TABLE `company_profile`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `custom_buttons`
--
ALTER TABLE `custom_buttons`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `custom_fields`
--
ALTER TABLE `custom_fields`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `custom_field_values`
--
ALTER TABLE `custom_field_values`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `custom_objects`
--
ALTER TABLE `custom_objects`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `custom_object_records`
--
ALTER TABLE `custom_object_records`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `dashboard_layouts`
--
ALTER TABLE `dashboard_layouts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `data_operation_logs`
--
ALTER TABLE `data_operation_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `data_operation_snapshots`
--
ALTER TABLE `data_operation_snapshots`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `document_governance_rights`
--
ALTER TABLE `document_governance_rights`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `document_line_items`
--
ALTER TABLE `document_line_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `document_snapshots`
--
ALTER TABLE `document_snapshots`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `document_templates`
--
ALTER TABLE `document_templates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `duplicate_exception_domains`
--
ALTER TABLE `duplicate_exception_domains`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `duplicate_record_sets`
--
ALTER TABLE `duplicate_record_sets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `duplicate_rules`
--
ALTER TABLE `duplicate_rules`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `duplicate_rule_conditions`
--
ALTER TABLE `duplicate_rule_conditions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_rules`
--
ALTER TABLE `email_rules`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_rule_executions`
--
ALTER TABLE `email_rule_executions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_templates`
--
ALTER TABLE `email_templates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `field_history`
--
ALTER TABLE `field_history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `field_history_configs`
--
ALTER TABLE `field_history_configs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `field_permissions`
--
ALTER TABLE `field_permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=376;

--
-- AUTO_INCREMENT for table `files`
--
ALTER TABLE `files`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `file_links`
--
ALTER TABLE `file_links`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `file_versions`
--
ALTER TABLE `file_versions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `generated_documents`
--
ALTER TABLE `generated_documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `global_picklists`
--
ALTER TABLE `global_picklists`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `global_search_settings`
--
ALTER TABLE `global_search_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leads`
--
ALTER TABLE `leads`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `migration_batches`
--
ALTER TABLE `migration_batches`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migration_error_logs`
--
ALTER TABLE `migration_error_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migration_id_mappings`
--
ALTER TABLE `migration_id_mappings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migration_records`
--
ALTER TABLE `migration_records`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification_rules`
--
ALTER TABLE `notification_rules`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification_rule_executions`
--
ALTER TABLE `notification_rule_executions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `opportunities`
--
ALTER TABLE `opportunities`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `page_layouts`
--
ALTER TABLE `page_layouts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `private_folder_shares`
--
ALTER TABLE `private_folder_shares`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `process_flows`
--
ALTER TABLE `process_flows`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `process_flow_executions`
--
ALTER TABLE `process_flow_executions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `profiles`
--
ALTER TABLE `profiles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `profile_layout_assignments`
--
ALTER TABLE `profile_layout_assignments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `profile_permissions`
--
ALTER TABLE `profile_permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1012;

--
-- AUTO_INCREMENT for table `purchase_deals`
--
ALTER TABLE `purchase_deals`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `record_shares`
--
ALTER TABLE `record_shares`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `relationship_definitions`
--
ALTER TABLE `relationship_definitions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `sharing_rules`
--
ALTER TABLE `sharing_rules`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `shipments`
--
ALTER TABLE `shipments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `standard_field_overrides`
--
ALTER TABLE `standard_field_overrides`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tenant_api_keys`
--
ALTER TABLE `tenant_api_keys`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tenant_backups`
--
ALTER TABLE `tenant_backups`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tenant_currencies`
--
ALTER TABLE `tenant_currencies`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `tenant_invoices`
--
ALTER TABLE `tenant_invoices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `tenant_settings`
--
ALTER TABLE `tenant_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tenant_subscriptions`
--
ALTER TABLE `tenant_subscriptions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tenant_webhooks`
--
ALTER TABLE `tenant_webhooks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `user_invitations`
--
ALTER TABLE `user_invitations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT for table `user_notifications`
--
ALTER TABLE `user_notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `view_configurations`
--
ALTER TABLE `view_configurations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `webhook_delivery_logs`
--
ALTER TABLE `webhook_delivery_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `weight_dims_specs`
--
ALTER TABLE `weight_dims_specs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wires_received`
--
ALTER TABLE `wires_received`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `accounts`
--
ALTER TABLE `accounts`
  ADD CONSTRAINT `accounts_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `accounts_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `accounts_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `activities`
--
ALTER TABLE `activities`
  ADD CONSTRAINT `activities_performed_by_foreign` FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `approval_actions`
--
ALTER TABLE `approval_actions`
  ADD CONSTRAINT `approval_actions_actor_id_foreign` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `approval_actions_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `approval_requests` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `approval_requests`
--
ALTER TABLE `approval_requests`
  ADD CONSTRAINT `approval_requests_process_id_foreign` FOREIGN KEY (`process_id`) REFERENCES `approval_processes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `approval_requests_submitted_by_foreign` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `approval_steps`
--
ALTER TABLE `approval_steps`
  ADD CONSTRAINT `approval_steps_process_id_foreign` FOREIGN KEY (`process_id`) REFERENCES `approval_processes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `contacts`
--
ALTER TABLE `contacts`
  ADD CONSTRAINT `contacts_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `contacts_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `contacts_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `contacts_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `custom_fields`
--
ALTER TABLE `custom_fields`
  ADD CONSTRAINT `custom_fields_global_picklist_id_foreign` FOREIGN KEY (`global_picklist_id`) REFERENCES `global_picklists` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `custom_field_values`
--
ALTER TABLE `custom_field_values`
  ADD CONSTRAINT `custom_field_values_custom_field_id_foreign` FOREIGN KEY (`custom_field_id`) REFERENCES `custom_fields` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `custom_object_records`
--
ALTER TABLE `custom_object_records`
  ADD CONSTRAINT `custom_object_records_custom_object_id_foreign` FOREIGN KEY (`custom_object_id`) REFERENCES `custom_objects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `dashboard_layouts`
--
ALTER TABLE `dashboard_layouts`
  ADD CONSTRAINT `dashboard_layouts_profile_id_foreign` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `data_operation_logs`
--
ALTER TABLE `data_operation_logs`
  ADD CONSTRAINT `data_operation_logs_performed_by_id_foreign` FOREIGN KEY (`performed_by_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `data_operation_snapshots`
--
ALTER TABLE `data_operation_snapshots`
  ADD CONSTRAINT `data_operation_snapshots_operation_id_foreign` FOREIGN KEY (`operation_id`) REFERENCES `data_operation_logs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `document_governance_rights`
--
ALTER TABLE `document_governance_rights`
  ADD CONSTRAINT `document_governance_rights_profile_id_foreign` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `document_line_items`
--
ALTER TABLE `document_line_items`
  ADD CONSTRAINT `document_line_items_document_id_foreign` FOREIGN KEY (`document_id`) REFERENCES `generated_documents` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `document_snapshots`
--
ALTER TABLE `document_snapshots`
  ADD CONSTRAINT `document_snapshots_document_id_foreign` FOREIGN KEY (`document_id`) REFERENCES `generated_documents` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `document_templates`
--
ALTER TABLE `document_templates`
  ADD CONSTRAINT `document_templates_approval_process_id_foreign` FOREIGN KEY (`approval_process_id`) REFERENCES `approval_processes` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `duplicate_record_sets`
--
ALTER TABLE `duplicate_record_sets`
  ADD CONSTRAINT `duplicate_record_sets_rule_id_foreign` FOREIGN KEY (`rule_id`) REFERENCES `duplicate_rules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `duplicate_rule_conditions`
--
ALTER TABLE `duplicate_rule_conditions`
  ADD CONSTRAINT `duplicate_rule_conditions_rule_id_foreign` FOREIGN KEY (`rule_id`) REFERENCES `duplicate_rules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `email_rules`
--
ALTER TABLE `email_rules`
  ADD CONSTRAINT `email_rules_email_template_id_foreign` FOREIGN KEY (`email_template_id`) REFERENCES `email_templates` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `email_rule_executions`
--
ALTER TABLE `email_rule_executions`
  ADD CONSTRAINT `email_rule_executions_rule_id_foreign` FOREIGN KEY (`rule_id`) REFERENCES `email_rules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `field_history`
--
ALTER TABLE `field_history`
  ADD CONSTRAINT `field_history_changed_by_id_foreign` FOREIGN KEY (`changed_by_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `field_permissions`
--
ALTER TABLE `field_permissions`
  ADD CONSTRAINT `field_permissions_profile_id_foreign` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `files`
--
ALTER TABLE `files`
  ADD CONSTRAINT `files_created_by_id_foreign` FOREIGN KEY (`created_by_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `file_links`
--
ALTER TABLE `file_links`
  ADD CONSTRAINT `file_links_created_by_id_foreign` FOREIGN KEY (`created_by_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `file_links_file_id_foreign` FOREIGN KEY (`file_id`) REFERENCES `files` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `file_versions`
--
ALTER TABLE `file_versions`
  ADD CONSTRAINT `file_versions_file_id_foreign` FOREIGN KEY (`file_id`) REFERENCES `files` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `file_versions_uploaded_by_id_foreign` FOREIGN KEY (`uploaded_by_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `generated_documents`
--
ALTER TABLE `generated_documents`
  ADD CONSTRAINT `generated_documents_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `generated_documents_contact_id_foreign` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `generated_documents_created_by_id_foreign` FOREIGN KEY (`created_by_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `generated_documents_file_id_foreign` FOREIGN KEY (`file_id`) REFERENCES `files` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `generated_documents_template_id_foreign` FOREIGN KEY (`template_id`) REFERENCES `document_templates` (`id`);

--
-- Constraints for table `leads`
--
ALTER TABLE `leads`
  ADD CONSTRAINT `leads_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `leads_converted_account_id_foreign` FOREIGN KEY (`converted_account_id`) REFERENCES `accounts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `leads_converted_contact_id_foreign` FOREIGN KEY (`converted_contact_id`) REFERENCES `contacts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `leads_converted_opportunity_id_foreign` FOREIGN KEY (`converted_opportunity_id`) REFERENCES `opportunities` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `leads_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `leads_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `migration_batches`
--
ALTER TABLE `migration_batches`
  ADD CONSTRAINT `migration_batches_performed_by_id_foreign` FOREIGN KEY (`performed_by_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `migration_error_logs`
--
ALTER TABLE `migration_error_logs`
  ADD CONSTRAINT `migration_error_logs_migration_batch_id_foreign` FOREIGN KEY (`migration_batch_id`) REFERENCES `migration_batches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `migration_id_mappings`
--
ALTER TABLE `migration_id_mappings`
  ADD CONSTRAINT `migration_id_mappings_migration_batch_id_foreign` FOREIGN KEY (`migration_batch_id`) REFERENCES `migration_batches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `migration_records`
--
ALTER TABLE `migration_records`
  ADD CONSTRAINT `migration_records_migration_batch_id_foreign` FOREIGN KEY (`migration_batch_id`) REFERENCES `migration_batches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notification_rule_executions`
--
ALTER TABLE `notification_rule_executions`
  ADD CONSTRAINT `notification_rule_executions_rule_id_foreign` FOREIGN KEY (`rule_id`) REFERENCES `notification_rules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `opportunities`
--
ALTER TABLE `opportunities`
  ADD CONSTRAINT `opportunities_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `opportunities_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `opportunities_contact_id_foreign` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `opportunities_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `opportunities_purchase_deal_id_foreign` FOREIGN KEY (`purchase_deal_id`) REFERENCES `purchase_deals` (`id`),
  ADD CONSTRAINT `opportunities_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `payments_opportunity_id_foreign` FOREIGN KEY (`opportunity_id`) REFERENCES `opportunities` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `payments_purchase_deal_id_foreign` FOREIGN KEY (`purchase_deal_id`) REFERENCES `purchase_deals` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `payments_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `private_folder_shares`
--
ALTER TABLE `private_folder_shares`
  ADD CONSTRAINT `private_folder_shares_granted_by_id_foreign` FOREIGN KEY (`granted_by_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `process_flow_executions`
--
ALTER TABLE `process_flow_executions`
  ADD CONSTRAINT `process_flow_executions_flow_id_foreign` FOREIGN KEY (`flow_id`) REFERENCES `process_flows` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `profile_layout_assignments`
--
ALTER TABLE `profile_layout_assignments`
  ADD CONSTRAINT `profile_layout_assignments_layout_id_foreign` FOREIGN KEY (`layout_id`) REFERENCES `page_layouts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `profile_layout_assignments_profile_id_foreign` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `profile_permissions`
--
ALTER TABLE `profile_permissions`
  ADD CONSTRAINT `profile_permissions_profile_id_foreign` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `purchase_deals`
--
ALTER TABLE `purchase_deals`
  ADD CONSTRAINT `purchase_deals_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchase_deals_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `purchase_deals_contact_id_foreign` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `purchase_deals_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `purchase_deals_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `record_shares`
--
ALTER TABLE `record_shares`
  ADD CONSTRAINT `record_shares_rule_id_foreign` FOREIGN KEY (`rule_id`) REFERENCES `sharing_rules` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `roles`
--
ALTER TABLE `roles`
  ADD CONSTRAINT `roles_reports_to_role_id_foreign` FOREIGN KEY (`reports_to_role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `sharing_rules`
--
ALTER TABLE `sharing_rules`
  ADD CONSTRAINT `sharing_rules_ownership_profile_id_foreign` FOREIGN KEY (`ownership_profile_id`) REFERENCES `profiles` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sharing_rules_ownership_role_id_foreign` FOREIGN KEY (`ownership_role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `shipments`
--
ALTER TABLE `shipments`
  ADD CONSTRAINT `shipments_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `shipments_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `shipments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `shipments_opportunity_id_foreign` FOREIGN KEY (`opportunity_id`) REFERENCES `opportunities` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `shipments_purchase_deal_id_foreign` FOREIGN KEY (`purchase_deal_id`) REFERENCES `purchase_deals` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `shipments_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tasks_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tasks_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `tenant_api_keys`
--
ALTER TABLE `tenant_api_keys`
  ADD CONSTRAINT `tenant_api_keys_created_by_id_foreign` FOREIGN KEY (`created_by_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `tenant_invoices`
--
ALTER TABLE `tenant_invoices`
  ADD CONSTRAINT `tenant_invoices_subscription_id_foreign` FOREIGN KEY (`subscription_id`) REFERENCES `tenant_subscriptions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_manager_id_foreign` FOREIGN KEY (`manager_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_profile_id_foreign` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_invitations`
--
ALTER TABLE `user_invitations`
  ADD CONSTRAINT `user_invitations_invited_by_user_id_foreign` FOREIGN KEY (`invited_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `user_invitations_profile_id_foreign` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `user_invitations_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_notifications`
--
ALTER TABLE `user_notifications`
  ADD CONSTRAINT `user_notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `webhook_delivery_logs`
--
ALTER TABLE `webhook_delivery_logs`
  ADD CONSTRAINT `webhook_delivery_logs_webhook_id_foreign` FOREIGN KEY (`webhook_id`) REFERENCES `tenant_webhooks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wires_received`
--
ALTER TABLE `wires_received`
  ADD CONSTRAINT `wires_received_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `wires_received_reconciled_account_id_foreign` FOREIGN KEY (`reconciled_account_id`) REFERENCES `accounts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `wires_received_reconciled_by_foreign` FOREIGN KEY (`reconciled_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `wires_received_reconciled_opportunity_id_foreign` FOREIGN KEY (`reconciled_opportunity_id`) REFERENCES `opportunities` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `wires_received_reconciled_payment_id_foreign` FOREIGN KEY (`reconciled_payment_id`) REFERENCES `payments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `wires_received_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
