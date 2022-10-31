<?php

namespace App\Model;

class Region extends \Fuxuqiang\Framework\Model\Model
{
    const CODE = 'code', NAME = 'name';

    protected $primaryKey = self::CODE;

    /**
     * 获取所有级联区域代码
     */
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

    /**
     * 获取上级区域代码
     */
    private static function getParentCode($code, $len)
    {
        return strlen($code) >= $len ? (int) substr($code, 0, $len) : 0;
    }

    /**
     * 根据名称搜索区域
     */
    public static function search($name)
    {
        return self::where('name', 'LIKE', $name . '%')->get();
    }
}
