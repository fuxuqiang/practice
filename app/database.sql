CREATE DATABASE IF NOT EXISTS `personal` CHARACTER SET `utf8mb4`;
USE `personal`;

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
    `id` TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `phone` BIGINT NOT NULL UNIQUE,
    `name` VARCHAR(255) NOT NULL DEFAULT '',
    `password` VARCHAR(255) NOT NULL DEFAULT '',
    `capital` mediumint UNSIGNED NOT NULL DEFAULT 0,
    `api_token` VARCHAR(255) NOT NULL,
    `token_expires` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

DROP TABLE IF EXISTS `trade`;
CREATE TABLE `trade` (
    `id` TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `type` TINYINT UNSIGNED NOT NULL,
    `user_id` TINYINT UNSIGNED NOT NULL,
    `code` VARCHAR(255) NOT NULL,
    `price` SMALLINT UNSIGNED NULL NULL,
    `num` TINYINT UNSIGNED NOT NULL,
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
    `name` VARCHAR(255) NOT NULL,
    `pid` TINYINT NOT NULL
);

DROP TABLE IF EXISTS `admin`;
CREATE TABLE `admin` (
    `id` TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `phone` BIGINT NOT NULL UNIQUE,
    `name` VARCHAR(255) NOT NULL DEFAULT '',
    `password` VARCHAR(255) NOT NULL DEFAULT '',
    `role_id` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `api_token` VARCHAR(255) NOT NULL,
    `token_expires` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP
);

INSERT `role` (`name`,`pid`) VALUE ('超级管理员',0);
INSERT `admin` (`phone`,`name`,`role_id`,`api_token`,`token_expires`) VALUE (18005661486,'',1,'',NOW())

DROP TABLE IF EXISTS `route`;
CREATE TABLE `route` (
    `id` TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `method` VARCHAR(255) NOT NULL,
    `uri` VARCHAR(255) NOT NULL,
    `resource` VARCHAR(255) NOT NULL,
    `action` VARCHAR(255) NOT NULL,
    UNIQUE (`method`,`uri`)
);

INSERT `route` VALUES
(NULL,'GET','admin/admins','管理员','列表'),
(NULL,'POST','admin/create','管理员','创建'),
(NULL,'DELETE','admin/admin','管理员','删除'),
(NULL,'PUT','admin/setRole','管理员','修改角色'),
(NULL,'POST','admin/role','角色','创建'),
(NULL,'PUT','admin/role','角色','修改'),
(NULL,'DELETE','admin/role','角色','删除');

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
    `code` BIGINT UNSIGNED NOT NULL,
    `address` VARCHAR(255) NOT NULL
)