<?php

namespace Src;

/**
 * @method static bool|\Redis setex(string $key, int $expire, $value)
 * @method static get(string $key)
 */
class Redis
{
    private static ?\Redis $redis = null;

    /**
     * @throws \RedisException
     */
    public static function __callStatic($name, $args)
    {
        if (!self::$redis) {
            self::$redis = new \Redis;
            self::$redis->connect('127.0.0.1');
        }
        return self::$redis->$name(...$args);
    }
}
