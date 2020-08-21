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

    public static function getCode($address, $returnType = 'array')
    {
        $getRegionName = function ($offset) use ($address) {
            return mb_substr($address, $offset, 2);
        };
        if ($province = self::find($getRegionName(0))) {
            $provinceNameLen = mb_strlen($province->name);
            if (
                ($cityName = $getRegionName($provinceNameLen))
                && ($city = self::find($cityName))
                && $districtName = $getRegionName($provinceNameLen + mb_strlen($city->name))
            ) {
                $district = self::find($districtName);
            }
            return $returnType == 'array' ? 
                [$province, $city ?? null, $district ?? null] :
                $district ?? $city ?? $province;
        } else {
            return false;
        }
    }
}
