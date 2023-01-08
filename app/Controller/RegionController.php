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
    public function list(int $code)
    {
        return Region::child($code)->all();
    }

    /**
     * 根据地址获取区域代码
     */
    #[Route('parseAddress')]
    public function parseAddress(string $address)
    {
        return (new Address($address))->getParsedAddress();
    }

    /**
     * 搜索城市
     */
    #[Route('searchCity')]
    public function search(string $name)
    {
        return Region::whereLike(
                [Region::NAME, Region::EN_NAME, Region::SHORT_EN_NAME],
                '%'.$name.'%'
            )
            ->limit(5)
            ->all();
    }
}
