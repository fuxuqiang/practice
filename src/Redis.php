<?php

namespace Src;

class Redis
{
    private static $redis;

    public static function __callStatic($name, $args)
    {
        if (!self::$redis) {
            self::$redis = new \Redis;
            self::$redis->connect('127.0.0.1');
        }
        return self::$redis->$name(...$args);
    }
}
