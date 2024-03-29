<?php

namespace Src;

use Fuxuqiang\Framework\{Model\Connector, Mysql as Query};
use mysqli;

/**
 * @method static Query table(string $name)
 * @method static bool begin()
 * @method static bool commit()
 * @method static bool rollback()
 */
class Mysql implements Connector
{
    /**
     * @var ?mysqli
     */
    private static ?mysqli $mysqli = null;

    /**
     * @var ?self
     */
    private static ?self $instance = null;

    /**
     * 动态调用\Fuxuqiang\Framework\Mysql的方法
     */
    public static function __callStatic($name, $args)
    {
        return self::getInstance()->connect()->$name(...$args);
    }

    /**
     * 获取自身实例
     */
    public static function getInstance(): self
    {
        return self::$instance ?: self::$instance = new self;
    }
    
    /**
     * 获取数据库查询实例
     */
    public function connect(): Query
    {
        if (!self::$mysqli) {
            $config = env('mysql');
            self::$mysqli = new mysqli($config['host'], $config['user'], $config['pwd'] ?? null, $config['db']);
            self::$mysqli->set_charset('utf8mb4');
        }
        return new Query(self::$mysqli);
    }

    /**
     * 关闭连接
     */
    public function __destruct()
    {
        self::$mysqli && self::$mysqli->close();
    }
}