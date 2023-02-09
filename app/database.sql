CREATE DATABASE IF NOT EXISTS `personal` CHARACTER SET `utf8mb4`;
USE `personal`;

DROP TABLE IF EXISTS `region`;
CREATE TABLE `region` (
    `code` BIGINT UNSIGNED PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `en_name` VARCHAR(255) NOT NULL DEFAULT '',
    `short_en_name` VARCHAR(255) NOT NULL DEFAULT ''
);

DROP TABLE IF EXISTS `fund_worth`;
CREATE TABLE `fund_worth` (
    `date` DATE NOT NULL,
    `value` MEDIUMINT UNSIGNED NOT NULL,
    `rate` MEDIUMINT NOT NULL
);

DROP TABLE IF EXISTS `fund_transaction`;
CREATE TABLE `fund_transaction` (
    `id` SMALLINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    `user_id` TINYINT UNSIGNED NOT NULL,
    `date` DATE NOT NULL,
    `amount` INT NOT NULL,
    `portion` INT NOT NULL,
    `is_followed` TINYINT NOT NULL DEFAULT 0
);

DROP TABLE IF EXISTS `request_log`;
CREATE TABLE `request_log` (
    `key` VARCHAR(255) PRIMARY KEY,
    `method` VARCHAR(255) NOT NULL,
    `uri` VARCHAR(255) NOT NULL,
    `ip` VARCHAR(255) NOT NULL,
    `input` TINYTEXT,
    `token` VARCHAR(255) NOT NULL DEFAULT '',
    `created_at` INT NOT NULL
);
