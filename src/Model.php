<?php
namespace src;

class Model
{
    /**
     * @var callable
     */
    private static $connector;

    /**
     * @var string
     */
    private $table,
    
    /**
     * @var array
     */
    $data;

    /**
     * 设置获取数据库操作类的方法
     */
    public static function setConnector(callable $connector)
    {
        self::$connector = $connector;
    }

    /**
     * @param string $table
     */
    public function __construct($table = null)
    {
        $this->table = $table ?: strtolower(basename(str_replace('\\', '/', static::class)));
    }

    /**
     * 获取当前表名
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * 设置模型字段
     */
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * 获取模型字段
     */
    public function __get($name)
    {
        return $this->data[$name] ?? null;
    }

    /**
     * 调用数据库操作类的方法
     */
    public function __call($name, $args)
    {
        return call_user_func(self::$connector, $this)->$name(...$args);
    }
}
