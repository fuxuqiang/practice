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
        return Region::getChild($code);
    }

    /**
     * 根据地址获取区域代码
     */
    #[Route('parseRegion')]
    public function parseRegion($address)
    {
        return (new Address($address))->parseRegion();
    }
}
