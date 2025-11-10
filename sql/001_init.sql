-- Schema initialization for CORE Newspaper Redirector
CREATE DATABASE IF NOT EXISTS `core_newspaper` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `core_newspaper`;

CREATE TABLE IF NOT EXISTS `providers` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `slug` VARCHAR(64) NOT NULL UNIQUE,
    `name` VARCHAR(128) NOT NULL,
    `pattern_type` ENUM('date','sequence','monthly') NOT NULL,
    `pattern_template` VARCHAR(255) NOT NULL,
    `current_issue` VARCHAR(64) NULL,
    `last_issue_url` VARCHAR(512) NULL,
    `last_updated_at` DATETIME NULL,
    `cron_last_run_at` DATETIME NULL,
    `cron_status` ENUM('success','fail','pending') NOT NULL DEFAULT 'pending',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `notes` TEXT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `cron_runs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `started_at` DATETIME NOT NULL,
    `ended_at` DATETIME NULL,
    `status` ENUM('success','partial','fail') NOT NULL,
    `providers_checked` INT NOT NULL DEFAULT 0,
    `providers_updated` INT NOT NULL DEFAULT 0,
    `message` TEXT NULL,
    `duration_ms` INT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `admin_users` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(160) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('admin','viewer') NOT NULL DEFAULT 'viewer',
    `created_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `provider_changes` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `provider_id` INT UNSIGNED NOT NULL,
    `changed_by` INT UNSIGNED NOT NULL,
    `old_issue` VARCHAR(64) NULL,
    `new_issue` VARCHAR(64) NULL,
    `old_url` VARCHAR(512) NULL,
    `new_url` VARCHAR(512) NULL,
    `changed_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_changes_provider` FOREIGN KEY (`provider_id`) REFERENCES `providers`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_changes_user` FOREIGN KEY (`changed_by`) REFERENCES `admin_users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE INDEX `idx_provider_active` ON `providers`(`is_active`);
CREATE INDEX `idx_provider_slug` ON `providers`(`slug`);
CREATE INDEX `idx_cron_status` ON `cron_runs`(`status`);
