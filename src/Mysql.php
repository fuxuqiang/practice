<?php

namespace src;

class Mysql implements \vendor\Connector
{
    /**
     * @var \mysqli
     */
    private static $mysqli;

    /**
     * @var self
     */
    private static $instance;

    /**
     * 动态调用\vendor\Mysql的方法
     */
    public static function __callStatic($name, $args)
    {
        return self::getInstance()->connect()->$name(...$args);
    }

    /**
     * 获取自身实例
     */
    public static function getInstance()
    {
        return self::$instance ?: self::$instance = new self;
    }
    
    /**
     * 获取数据库查询实例
     */
    public function connect(): \vendor\Mysql
    {
        if (!self::$mysqli) {
            $config = env('mysql');
            self::$mysqli = new \mysqli($config['host'], $config['user'], $config['pwd'] ?? null, $config['db']);
            self::$mysqli->set_charset('utf8');
        }
        return new \vendor\Mysql(self::$mysqli);
    }

    /**
     * 获取mysqli实例
     */
    public static function getMysqli()
    {
        return self::$mysqli;
    }
}