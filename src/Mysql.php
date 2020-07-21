<?php

namespace src;

class Mysql
{
    /**
     * @var \mysqli
     */
    private static $mysqli;

    public static function __callStatic($name, $args)
    {
        if (!self::$mysqli) {
            $config = config('mysql');
            self::$mysqli = new \mysqli($config['host'], $config['user'], $config['pwd'] ?? null, $config['db']);
            self::$mysqli->set_charset('utf8');
        }
        $mysql = new \vendor\Mysql(self::$mysqli);
        return $mysql->$name(...$args);
    }

    public static function getMysqli()
    {
        return self::$mysqli;
    }
}