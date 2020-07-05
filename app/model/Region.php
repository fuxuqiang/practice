<?php
namespace app\model;

class Region
{
    public static function getAllCode($code)
    {
        return [
            (int) substr($code, 0, 2),
            self::getParentCode($code, 4),
            self::getParentCode($code, 6),
            self::getParentCode($code, 9),
            strlen($code) == 12 ? $code : 0
        ];
    }

    private static function getParentCode($code, $len)
    {
        return strlen($code) >= $len ? (int) substr($code, 0, $len) : 0;
    }

    public static function find($name)
    {
        return \src\Mysql::table('region')->where('name', 'LIKE', $name . '%')->get();
    }
}
