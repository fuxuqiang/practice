CREATE DATABASE IF NOT EXISTS `personal`;
USE `personal`;

DROP TABLE IF EXISTS `region`;
CREATE TABLE `region` (
    `code` BIGINT UNSIGNED PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `en_name` VARCHAR(255),
    `short_en_name` VARCHAR(255)
);

DROP TABLE IF EXISTS `fund`;
CREATE TABLE `fund` (
    `id` TINYINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    `code` VARCHAR(255) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `industry` VARCHAR(255) NOT NULL,
    `sell_fee` TINYINT NOT NULL DEFAULT 0,
    `priority` TINYINT NOT NULL DEFAULT 0
);

DROP TABLE IF EXISTS `fund_worth`;
CREATE TABLE `fund_worth` (
    `fund_id` TINYINT UNSIGNED,
    `date` DATE NOT NULL,
    `value` MEDIUMINT UNSIGNED NOT NULL
);

DROP TABLE IF EXISTS `fund_transaction`;
CREATE TABLE `fund_transaction` (
    `id` SMALLINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    `fund_id` TINYINT UNSIGNED,
    `bought_at` DATE NOT NULL,
    `confirm_at` DATE NOT NULL,
    `amount` INT NOT NULL,
    `portion` INT NOT NULL,
    `is_sold` TINYINT
);

DROP TABLE IF EXISTS `fund_amount`;
CREATE TABLE `fund_amount` (
    `fund_id` TINYINT UNSIGNED,
    `date` DATE NOT NULL,
    `portion` INT UNSIGNED NOT NULL,
    `amount` INT UNSIGNED NOT NULL,
    `profit` MEDIUMINT NOT NULL,
    `total_profit` MEDIUMINT NOT NULL
);

DROP TABLE IF EXISTS `request_log`;
CREATE TABLE `request_log` (
    `key` VARCHAR(255) PRIMARY KEY,
    `method` VARCHAR(255) NOT NULL,
    `uri` VARCHAR(255) NOT NULL,
    `ip` VARCHAR(255) NOT NULL,
    `input` TINYTEXT,
    `token` VARCHAR(255),
    `created_at` INT NOT NULL
);

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
    `id` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    `mobile` CHAR(11) NOT NULL UNIQUE,
    `name` VARCHAR(255),
    `created_at` INT NOT NULL
);
