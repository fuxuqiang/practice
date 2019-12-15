<?php

namespace src;

class Mysql
{
    public static $mysqli;

    public static function __callStatic($name, $args)
    {
        if (!self::$mysqli) {
            $config = config('mysql');
            $mysqli = new \mysqli($config['host'], $config['user'], $config['pwd'], $config['db']);
        }
        $mysql = new \vendor\Mysql($mysqli);
        return $mysql->$name(...$args);
    }
}