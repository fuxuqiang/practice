<?php

/**
 * 记录日志
 */
function logError($content)
{
    file_put_contents(
        __DIR__ . '/runtime/error.log',
        '[' . timestamp() . "]\n" . $content . "\n",
        FILE_APPEND | LOCK_EX
    );
}

/**
 * 获取Mysql实例
 */
function mysql($table = null)
{
    static $mysqli;
    if (!$mysqli) {
        $config = config('mysql');
        $mysqli = new mysqli($config['host'], $config['user'], $config['pwd'], $config['db']);
    }
    $mysql = new \vendor\Mysql($mysqli);
    return $table ? $mysql->from($table) : $mysql;
}

/**
 * 获取时间
 */
function timestamp($time = null)
{
    return date('Y-m-d H:i:s', $time ? strtotime($time) : time());
}

/**
 * 启动会话
 */
function sessionStart()
{
    static $started;
    return $started || $started = session_start();
}
