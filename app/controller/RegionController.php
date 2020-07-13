<?php

namespace app\controller;

use app\model\Region;

class RegionController
{
    public function list($p_code = 0)
    {
        $factor = $p_code > 99999 ? 1000 : (in_array($p_code, [4419, 4420]) ? 100000 : 100);
        return [
            'data' => \src\Mysql::table('region')
                ->whereBetween('code', [$p_code * $factor, ($p_code + 1) * $factor])->all()
        ];
    }

    public function getCode($address)
    {
        $getRegionName = function ($offset) use ($address) {
            return mb_substr($address, $offset, 2);
        };
        if ($province = Region::find($getRegionName(0))) {
            $provinceNameLen = mb_strlen($province->name);
            if (
                ($cityName = $getRegionName($provinceNameLen))
                && ($city = Region::find($cityName))
                && $districtName = $getRegionName($provinceNameLen + mb_strlen($city->name))
            ) {
                $district = Region::find($districtName);
            }
            return [$province, $city ?? null, $district ?? null];
        } else {
            return error('未查询到');
        }
    }
}
