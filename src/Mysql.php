<?php

namespace src;

class Mysql
{
    /**
     * @var \mysqli
     */
    private static $mysqli;

    /**
     * 动态调用\vendor\Mysql的方法
     */
    public static function __callStatic($name, $args)
    {
        if (!self::$mysqli) {
            $config = env('mysql');
            self::$mysqli = new \mysqli($config['host'], $config['user'], $config['pwd'] ?? null, $config['db']);
            self::$mysqli->set_charset('utf8');
        }
        $mysql = new \vendor\Mysql(self::$mysqli);
        return $mysql->$name(...$args);
    }

    /**
     * 获取mysqli实例
     */
    public static function getMysqli()
    {
        return self::$mysqli;
    }
}