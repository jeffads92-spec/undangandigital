-- WEDDING DIGITAL PREMIUM DATABASE STRUCTURE
-- Version: 2.0.0
-- Created: <?= date('Y-m-d H:i:s') ?>

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+07:00";

-- --------------------------------------------------------
-- Database: wedding_digital
-- --------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `wedding_digital` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `wedding_digital`;

-- --------------------------------------------------------
-- Table structure for table `admin_users`
-- --------------------------------------------------------
CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` enum('superadmin','admin','editor') DEFAULT 'admin',
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `login_ip` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default admin user (password: Admin123!)
INSERT INTO `admin_users` (`username`, `password_hash`, `full_name`, `role`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'superadmin');

-- --------------------------------------------------------
-- Table structure for table `guests`
-- --------------------------------------------------------
CREATE TABLE `guests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `attendance` enum('pending','hadir','tidak') DEFAULT 'pending',
  `people` int(11) DEFAULT 1,
  `message` text DEFAULT NULL,
  `invitation_sent` tinyint(1) DEFAULT 0,
  `sent_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `attendance` (`attendance`),
  KEY `phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `messages`
-- --------------------------------------------------------
CREATE TABLE `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `guest_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `is_approved` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `guest_id` (`guest_id`),
  KEY `is_approved` (`is_approved`),
  CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`guest_id`) REFERENCES `guests` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `wedding_songs`
-- --------------------------------------------------------
CREATE TABLE `wedding_songs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `artist` varchar(100) DEFAULT 'Unknown',
  `filename` varchar(255) NOT NULL,
  `duration` int(11) DEFAULT 180,
  `filesize` int(11) DEFAULT 0,
  `filetype` varchar(50) DEFAULT 'audio/mpeg',
  `is_active` tinyint(1) DEFAULT 1,
  `is_default` tinyint(1) DEFAULT 0,
  `play_order` int(11) DEFAULT 0,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `is_active` (`is_active`),
  KEY `is_default` (`is_default`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default wedding songs
INSERT INTO `wedding_songs` (`title`, `artist`, `filename`, `is_default`) VALUES
('Wedding March', 'Mendelssohn', 'default.mp3', 1),
('Canon in D', 'Pachelbel', 'canon.mp3', 0),
('A Thousand Years', 'Christina Perri', 'thousand_years.mp3', 0);

-- --------------------------------------------------------
-- Table structure for table `gallery`
-- --------------------------------------------------------
CREATE TABLE `gallery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL,
  `title` varchar(200) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `category` enum('prewedding','event','family','guests') DEFAULT 'prewedding',
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `category` (`category`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `payments`
-- --------------------------------------------------------
CREATE TABLE `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_id` varchar(50) NOT NULL,
  `guest_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `method` enum('qris','transfer','cash') DEFAULT 'qris',
  `status` enum('pending','pending_verify','verified','rejected') DEFAULT 'pending',
  `proof_image` varchar(255) DEFAULT NULL,
  `verified_by` int(11) DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `transaction_id` (`transaction_id`),
  KEY `guest_id` (`guest_id`),
  KEY `status` (`status`),
  KEY `verified_by` (`verified_by`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`guest_id`) REFERENCES `guests` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`verified_by`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `templates`
-- --------------------------------------------------------
CREATE TABLE `templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `folder_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 0,
  `activated_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `folder_name` (`folder_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default templates
INSERT INTO `templates` (`name`, `folder_name`, `description`, `is_active`) VALUES
('Royal Elegance', 'royal-elegance', 'Template premium dengan sentuhan kerajaan yang elegan', 1),
('Classic Romance', 'classic-romance', 'Template klasik dengan nuansa romantis dan timeless', 0),
('Garden Bliss', 'garden-bliss', 'Template taman dengan nuansa alam dan bunga-bunga', 0);

-- --------------------------------------------------------
-- Table structure for table `settings`
-- --------------------------------------------------------
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key_name` varchar(100) NOT NULL,
  `key_value` text DEFAULT NULL,
  `category` varchar(50) DEFAULT 'general',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `key_name` (`key_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default settings
INSERT INTO `settings` (`key_name`, `key_value`, `category`) VALUES
('site_name', 'Undangan Digital Premium', 'general'),
('site_url', 'http://localhost/wedding/', 'general'),
('wedding_title', 'Pernikahan Kita', 'wedding'),
('groom_name', 'Mempelai Pria', 'wedding'),
('bride_name', 'Mempelai Wanita', 'wedding'),
('wedding_date', '2025-12-25', 'wedding'),
('wedding_time', '14:00', 'wedding'),
('location', 'Jakarta Convention Center', 'wedding'),
('google_maps', 'https://maps.app.goo.gl/xxxx', 'wedding'),
('whatsapp_number', '6281234567890', 'contact'),
('bank_name', 'BCA', 'payment'),
('bank_account', '1234567890', 'payment'),
('account_name', 'Nama Pemilik Rekening', 'payment'),
('meta_description', 'Undangan pernikahan digital dengan fitur lengkap', 'seo'),
('meta_keywords', 'undangan digital, pernikahan, wedding, undangan online', 'seo'),
('theme_color', '#8B4513', 'design'),
('secondary_color', '#DAA520', 'design'),
('font_family', 'Poppins, sans-serif', 'design'),
('music_autoplay', '1', 'features'),
('show_countdown', '1', 'features'),
('show_gallery', '1', 'features'),
('show_gifts', '1', 'features'),
('allow_messages', '1', 'features');

-- --------------------------------------------------------
-- Table structure for table `activities`
-- --------------------------------------------------------
CREATE TABLE `activities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`),
  CONSTRAINT `activities_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `backup_logs`
-- --------------------------------------------------------
CREATE TABLE `backup_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL,
  `type` enum('full','database','files','config','auto') DEFAULT 'full',
  `description` text DEFAULT NULL,
  `size` int(11) DEFAULT 0,
  `status` enum('success','failed','deleted') DEFAULT 'success',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `login_attempts`
-- --------------------------------------------------------
CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `attempt_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `username` (`username`),
  KEY `attempt_time` (`attempt_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `remember_tokens`
-- --------------------------------------------------------
CREATE TABLE `remember_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `expires_at` (`expires_at`),
  CONSTRAINT `remember_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `password_resets`
-- --------------------------------------------------------
CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `expires_at` (`expires_at`),
  CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

COMMIT;
