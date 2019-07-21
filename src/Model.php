<?php
namespace src;

class Model
{
    public $table;

    public static $connector;

    private $data;

    public function __construct($table = null)
    {
        $this->table = $table ?? strtolower(basename(__CLASS__));
    }

    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function __get($name)
    {
        return $this->data[$name] ?? null;
    }

    public function __call($name, $args)
    {
        return call_user_func(self::$connector, $this)->$name(...$args);
    }
}
