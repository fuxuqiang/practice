<?php

/**
 * 响应状态码并结束执行
 */
function response($code, $msg = null)
{
    http_response_code($code);
    exit($msg ?: '');
}

/**
 * 记录日志
 */
function logError($content, $exit = true)
{
    file_put_contents(
        __DIR__.'/log/error.log', '['.timestamp()."]\n".$content."\n", FILE_APPEND | LOCK_EX
    );
    $exit && response(500);
}

/**
 * 获取配置
 */
function config($name)
{
    static $config;
    $config || $config = require __DIR__.'/app/config.php';
    return $config[$name] ?? null;
}

/**
 * 获取Mysql实例
 */
function mysql($table = null)
{
    static $mysqli;
    if (!$mysqli) {
        $config = config('mysql');
        $mysqli = new mysqli($config['host'], $config['user'], $config['pwd'], $config['name']);
    }
    $mysql = new \src\Mysql($mysqli);
    return $table ? $mysql->from($table) : $mysql;
}

/**
 * 获取时间
 */
function timestamp($time = null)
{
    return date('Y-m-d H:i:s', $time ? strtotime($time) : time());
}
