CREATE DATABASE IF NOT EXISTS `personal` CHARACTER SET `utf8mb4`;
USE `personal`;

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
    `id` SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `phone` BIGINT NOT NULL UNIQUE,
    `name` VARCHAR(255) NOT NULL DEFAULT '',
    `password` VARCHAR(255) NOT NULL DEFAULT '',
    `capital` MEDIUMINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `is_forbidden` TINYINT NOT NULL DEFAULT 0
);

DROP TABLE IF EXISTS `trade`;
CREATE TABLE `trade` (
    `id` TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` TINYINT UNSIGNED NOT NULL,
    `code` VARCHAR(255) NOT NULL,
    `price` SMALLINT UNSIGNED NULL NULL,
    `num` TINYINT NOT NULL,
    `date` DATE NOT NULL,
    `note` VARCHAR(255) NOT NULL
);

DROP TABLE IF EXISTS `position`;
CREATE TABLE `position` (
    `code` VARCHAR(255) NOT NULL,
    `user_id` TINYINT UNSIGNED NOT NULL,
    `num` TINYINT UNSIGNED NOT NULL,
    PRIMARY KEY (`code`,`user_id`)
);

DROP TABLE IF EXISTS `role`;
CREATE TABLE `role` (
    `id` TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL UNIQUE,
    `pid` TINYINT NOT NULL
);

DROP TABLE IF EXISTS `admin`;
CREATE TABLE `admin` (
    `id` TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `phone` BIGINT NOT NULL UNIQUE,
    `name` VARCHAR(255) NOT NULL DEFAULT '',
    `password` VARCHAR(255) NOT NULL DEFAULT '',
    `role_id` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `joined_at` DATE NOT NULL,
    `quitted_at` DATE
);

DROP TABLE IF EXISTS `route`;
CREATE TABLE `route` (
    `id` TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `method` VARCHAR(255) NOT NULL,
    `uri` VARCHAR(255) NOT NULL,
    `resource` VARCHAR(255) NOT NULL,
    `action` VARCHAR(255) NOT NULL,
    UNIQUE(`method`,`uri`)
);

DROP TABLE IF EXISTS `role_route`;
CREATE TABLE `role_route` (
    `role_id` TINYINT UNSIGNED,
    `route_id` TINYINT UNSIGNED,
    PRIMARY KEY (`role_id`,`route_id`)
);

DROP TABLE IF EXISTS `region`;
CREATE TABLE `region` (
    `code` BIGINT UNSIGNED PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL
);

DROP TABLE IF EXISTS `address`;
CREATE TABLE `address` (
    `id` SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` SMALLINT UNSIGNED NOT NULL,
    `code` BIGINT UNSIGNED NOT NULL,
    `address` VARCHAR(255) NOT NULL
);

DROP TABLE IF EXISTS `sku`;
CREATE TABLE `sku` (
    `id` TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `price` SMALLINT UNSIGNED NOT NULL,
    `num` TINYINT UNSIGNED NOT NULL
);

DROP TABLE IF EXISTS `order`;
CREATE TABLE `order` (
    `id` TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` SMALLINT UNSIGNED NOT NULL,
    `region_code` BIGINT UNSIGNED NOT NULL,
    `address` VARCHAR(255) NOT NULL,
    `status` TINYINT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

DROP TABLE IF EXISTS `order_sku`;
CREATE TABLE `order_sku` (
    `order_id` TINYINT UNSIGNED NOT NULL,
    `sku_id` TINYINT UNSIGNED NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `price` SMALLINT UNSIGNED NOT NULL,
    `num` TINYINT UNSIGNED NOT NULL,
    UNIQUE(`order_id`,`sku_id`)
);

DROP TABLE IF EXISTS `request_log`;
CREATE TABLE `request_log` (
    `key` VARCHAR(255) PRIMARY KEY,
    `method` VARCHAR(255) NOT NULL,
    `uri` VARCHAR(255) NOT NULL,
    `ip` VARCHAR(255) NOT NULL,
    `input` TINYTEXT,
    `token` VARCHAR(255) NOT NULL DEFAULT '',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

INSERT `role` (`name`,`pid`) VALUE ('超级管理员',0);

INSERT `admin` (`phone`,`name`,`role_id`,`joined_at`)
VALUE (18005661486,'',1,CURRENT_DATE);

INSERT `route` VALUES
(NULL,'GET','admins','管理员','列表'),
(NULL,'POST','create','管理员','创建'),
(NULL,'DELETE','admin','管理员','删除'),
(NULL,'PUT','admin_role','管理员','修改角色'),
(NULL,'POST','role','角色','创建'),
(NULL,'PUT','role','角色','修改'),
(NULL,'DELETE','role','角色','删除'),
(NULL,'POST','save_access','角色','设置权限');

INSERT `role_route` VALUES
(1,1),(1,2),(1,3),(1,4),(1,5),(1,6),(1,7),(1,8);