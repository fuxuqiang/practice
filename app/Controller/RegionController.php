<?php

namespace App\Controller;

use App\Model\{Region, Address};
use Fuxuqiang\Framework\Route\Route;

class RegionController
{
    /**
     * 查询下级区域
     */
    #[Route('regions')]
    public function list($code)
    {
        return Region::child($code)->all();
    }

    /**
     * 根据地址获取区域代码
     */
    #[Route('parseAddress')]
    public function parseAddress($address)
    {
        return (new Address($address))->getParsedAddress();
    }
}
