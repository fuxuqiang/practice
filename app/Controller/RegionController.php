<?php

namespace App\Controller;

use App\Model\Region;
use Fuxuqiang\Framework\Route\Route;

class RegionController
{
    /**
     * 查询下级区域
     */
    #[Route('regions')]
    public function list($pCode = 0)
    {
        $factor = $pCode > 99999 ? 1000 : (in_array($pCode, [4419, 4420]) ? 100000 : 100);
        return data(Region::whereBetween('code', [$pCode * $factor, ($pCode + 1) * $factor])->all());
    }

    /**
     * 根据地址获取区域代码
     */
    #[Route('getRegionCode')]
    public function getCode($address)
    {
        if ($regions = Region::getCode($address)) {
            return $regions;
        } else {
            return error('未查询到');
        }
    }
}
