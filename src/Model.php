<?php
namespace src;

class Model
{
    private static $connector;

    private $table, $data;

    public static function setConnector(callable $connector)
    {
        self::$connector = $connector;
    }

    public function __construct($table = null)
    {
        $this->table = $table ?: strtolower(basename(str_replace('\\', '/', static::class)));
    }

    public function getTable()
    {
        return $this->table;
    }

    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function __get($name)
    {
        return $this->data[$name] ?? null;
    }

    public function update(array $data)
    {
        return call_user_func(self::$connector, $this)->update($data);
    }
}
