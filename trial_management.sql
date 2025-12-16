-- Trial Management System - Database Schema
-- Note: Adjust database name if needed before importing.

CREATE DATABASE IF NOT EXISTS `trial_management`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `trial_management`;

-- ---------------------------------------------
-- Table: trials
-- ---------------------------------------------

DROP TABLE IF EXISTS `trials`;

CREATE TABLE `trials` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `city` VARCHAR(100) NOT NULL,
  `state` VARCHAR(100) NOT NULL,
  `venue` VARCHAR(255) NOT NULL,
  `trial_date` DATE NOT NULL,
  `reporting_time` VARCHAR(50) DEFAULT NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'inactive',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_trials_status` (`status`),
  KEY `idx_trials_trial_date` (`trial_date`),
  KEY `idx_trials_city` (`city`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------
-- Table: players
-- ---------------------------------------------

DROP TABLE IF EXISTS `players`;

CREATE TABLE `players` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `registration_id` VARCHAR(30) NOT NULL,
  `full_name` VARCHAR(150) NOT NULL,
  `age` INT UNSIGNED NOT NULL,
  `mobile` VARCHAR(15) NOT NULL,
  `player_type` ENUM('batsman', 'bowler', 'all_rounder', 'wicket_keeper') NOT NULL,
  `player_state` VARCHAR(100) DEFAULT NULL,
  `player_city` VARCHAR(100) DEFAULT NULL,
  `trial_id` INT UNSIGNED DEFAULT NULL,
  `total_fee` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `paid_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `due_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `payment_status` ENUM('unpaid', 'partially_paid', 'paid', 'fully_paid') NOT NULL DEFAULT 'unpaid',
  `status_updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_players_registration_id` (`registration_id`),
  UNIQUE KEY `uq_players_mobile` (`mobile`),
  KEY `idx_players_mobile` (`mobile`),
  KEY `idx_players_trial_id` (`trial_id`),
  KEY `idx_players_created_at` (`created_at`),
  KEY `idx_players_payment_status` (`payment_status`),
  CONSTRAINT `fk_players_trial` FOREIGN KEY (`trial_id`)
    REFERENCES `trials` (`id`)
    ON UPDATE CASCADE
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------
-- Table: trial_attendance
-- ---------------------------------------------

DROP TABLE IF EXISTS `trial_attendance`;

CREATE TABLE `trial_attendance` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `trial_id` INT UNSIGNED NOT NULL,
  `player_id` INT UNSIGNED NOT NULL,
  `is_present` TINYINT(1) NOT NULL DEFAULT 0,
  `remarks` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_trial_player` (`trial_id`, `player_id`),
  KEY `idx_trial_attendance_trial_id` (`trial_id`),
  KEY `idx_trial_attendance_player_id` (`player_id`),
  CONSTRAINT `fk_attendance_trial` FOREIGN KEY (`trial_id`)
    REFERENCES `trials` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  CONSTRAINT `fk_attendance_player` FOREIGN KEY (`player_id`)
    REFERENCES `players` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------
-- Table: payments (per-player, per-trial collections)
-- ---------------------------------------------

DROP TABLE IF EXISTS `payments`;

CREATE TABLE `payments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `player_id` INT UNSIGNED NOT NULL,
  `trial_id` INT UNSIGNED NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `source` ENUM('registration','on_spot','attendance','adjustment') NOT NULL DEFAULT 'registration',
  `paid_on` DATE NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_payments_player_id` (`player_id`),
  KEY `idx_payments_trial_id` (`trial_id`),
  KEY `idx_payments_paid_on` (`paid_on`),
  CONSTRAINT `fk_payments_player` FOREIGN KEY (`player_id`)
    REFERENCES `players` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  CONSTRAINT `fk_payments_trial` FOREIGN KEY (`trial_id`)
    REFERENCES `trials` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



