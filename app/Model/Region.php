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

    /**
     * 根据地址搜索区域
     */
    public static function getCode($address, $returnType = 'array')
    {
        $getRegionName = function ($offset) use ($address) {
            return mb_substr($address, $offset, 3);
        };
        if ($province = self::search($getRegionName(0))) {
            $provinceNameLen = mb_strlen($province->name);
            if (
                ($cityName = $getRegionName($provinceNameLen))
                && ($city = self::search($cityName))
                && $districtName = $getRegionName($provinceNameLen + mb_strlen($city->name))
            ) {
                $district = self::search($districtName);
            } else {
                return self::find(self::getAllCode($province->code));
            }
            return $returnType == 'array' ? 
                [$province, $city ?? null, $district ?? null] :
                $district ?? $city ?? $province;
        } else {
            return false;
        }
    }
}
