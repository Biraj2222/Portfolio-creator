-- PORTFOLIO FORGE DATABASE SCHEMA

CREATE DATABASE IF NOT EXISTS `portfolio_forge` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `portfolio_forge`;

-- 1. USERS TABLE
CREATE TABLE IF NOT EXISTS `users` (
    `user_id` INT AUTO_INCREMENT PRIMARY KEY,
    `full_name` VARCHAR(100) NOT NULL,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `profile_image` VARCHAR(255) DEFAULT NULL,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. ADMINS TABLE
CREATE TABLE IF NOT EXISTS `admins` (
    `admin_id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. TEMPLATES TABLE
CREATE TABLE IF NOT EXISTS `templates` (
    `template_id` INT AUTO_INCREMENT PRIMARY KEY,
    `template_name` VARCHAR(50) NOT NULL,
    `slug` VARCHAR(50) NOT NULL UNIQUE,
    `description` TEXT,
    `preview_image` VARCHAR(255) DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. PORTFOLIOS TABLE
CREATE TABLE IF NOT EXISTS `portfolios` (
    `portfolio_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL UNIQUE,
    `template_id` INT NOT NULL DEFAULT 1,
    `title` VARCHAR(150) NOT NULL,
    `portfolio_slug` VARCHAR(100) NOT NULL UNIQUE,
    `status` ENUM('draft', 'published', 'unpublished') NOT NULL DEFAULT 'draft',
    `accent_color` VARCHAR(20) DEFAULT '#2563eb',
    `font_family` VARCHAR(50) DEFAULT 'Inter, sans-serif',
    `show_profile_image` TINYINT(1) DEFAULT 1,
    `show_email` TINYINT(1) DEFAULT 1,
    `show_phone` TINYINT(1) DEFAULT 1,
    `show_location` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
    FOREIGN KEY (`template_id`) REFERENCES `templates`(`template_id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. PORTFOLIO_SECTIONS TABLE
CREATE TABLE IF NOT EXISTS `portfolio_sections` (
    `section_id` INT AUTO_INCREMENT PRIMARY KEY,
    `portfolio_id` INT NOT NULL,
    `section_type` VARCHAR(50) NOT NULL,
    `title` VARCHAR(100) DEFAULT NULL,
    `content` LONGTEXT DEFAULT NULL,
    `display_order` INT NOT NULL DEFAULT 0,
    `is_visible` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`portfolio_id`) REFERENCES `portfolios`(`portfolio_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. RESUME TABLE
CREATE TABLE IF NOT EXISTS `resume` (
    `resume_id` INT AUTO_INCREMENT PRIMARY KEY,
    `portfolio_id` INT NOT NULL UNIQUE,
    `file_name` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(255) NOT NULL,
    `public_download_enabled` TINYINT(1) NOT NULL DEFAULT 0,
    `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`portfolio_id`) REFERENCES `portfolios`(`portfolio_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. PORTFOLIO_VISITS TABLE
CREATE TABLE IF NOT EXISTS `portfolio_visits` (
    `visit_id` INT AUTO_INCREMENT PRIMARY KEY,
    `portfolio_id` INT NOT NULL,
    `visited_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`portfolio_id`) REFERENCES `portfolios`(`portfolio_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SEED DATA: INITIAL TEMPLATES
INSERT INTO `templates` (`template_id`, `template_name`, `slug`, `description`, `preview_image`, `is_active`) VALUES
(1, 'Modern', 'modern', 'Contemporary layout with strong visual hierarchy, bold headings, modern project cards, and balanced spacing.', 'modern-preview.png', 1),
(2, 'Minimal', 'minimal', 'Clean, refined layout with wide whitespace, elegant typography, simple navigation, and minimalist visual accents.', 'minimal-preview.png', 1),
(3, 'Professional', 'professional', 'Structured formal layout highlighting experience, education, key competencies, and clear structural hierarchy.', 'professional-preview.png', 1),
(4, 'Creative', 'creative', 'Expressive and vibrant visual design emphasizing personal branding, dynamic card arrangements, and visual flair.', 'creative-preview.png', 1),
(5, 'Classic', 'classic', 'Traditional resume-inspired template with clean chronological flow, conservative styling, and straightforward layout.', 'classic-preview.png', 1)
ON DUPLICATE KEY UPDATE `template_name` = VALUES(`template_name`), `description` = VALUES(`description`);

-- SEED DATA: DEFAULT ADMIN
INSERT INTO `admins` (`admin_id`, `username`, `email`, `password`) VALUES
(1, 'admin', 'admin@portfolioforge.com', '$2y$10$OEt8Af.LSXexhElCkuMPU.0LcTgizdbKVoK5T7A6rDQ5MPh.MthQ6')
ON DUPLICATE KEY UPDATE `username` = VALUES(`username`);

