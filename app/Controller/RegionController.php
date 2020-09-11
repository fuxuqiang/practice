<?php

namespace App\Controller;

class RegionController
{
    public function list($p_code = 0)
    {
        $factor = $p_code > 99999 ? 1000 : (in_array($p_code, [4419, 4420]) ? 100000 : 100);
        return [
            'data' => \Src\Mysql::table('region')
                ->whereBetween('code', [$p_code * $factor, ($p_code + 1) * $factor])->all()
        ];
    }

    public function getCode($address)
    {
        if ($regions = \App\Model\Region::getCode($address)) {
            return $regions;
        } else {
            return error('未查询到');
        }
    }
}
