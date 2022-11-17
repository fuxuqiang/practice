<?php

namespace App\Model;

use Fuxuqiang\Framework\{Mysql, Model\Model};

class Region extends Model
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
    public function scopeLike(Mysql $query, $name)
    {
        return $query->where('name', 'LIKE', $name . '%');
    }

    /**
     * 获取下级区域
     */
    public function scopeChild(Mysql $query, $code)
    {
        $factor = $code > 99999 ? 1000 : (in_array($code, [4419, 4420]) ? 100000 : 100);
        return $query->whereBetween('code', [$code * $factor, ($code + 1) * $factor]);
    }
}
