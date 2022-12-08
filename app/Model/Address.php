<?php

namespace App\Model;

use App\Model\Region;

class Address
{
    private $regionLen, $regions;

    public function __construct(private string $address)
    {
        if ($province = Region::search($this->getRegionName(0))->first()) {
            $this->regions[] = $province;
            if (!$this->findChild($province)) {
                $this->regions = Region::find($province->getAllCode());
            }
        }
    }

    /**
     * 获取地址的详细行政区
     */
    public function getParsedAddress()
    {
        return [
            'region' => $this->regions,
            'address' => mb_substr($this->address, $this->regionLen),
        ];
    }

    /**
     * 获取行政区model
     */
    private function findChild(Region $region)
    {
        $regionName = $this->getRegionName($this->regionLen += mb_strlen($region->name));
        if (
            strlen($regionName) > 1
            && ($child = Region::search($regionName)->child($region->code)->first())
            && $region->code < 999999
        ) {
            $this->regions[] = $child;
            $this->findChild($child);
            return true;
        }
        return false;
    }

    /**
     * 获取行政区名称
     */
    private function getRegionName($offset)
    {
        return mb_substr($this->address, $offset, 2);
    }
}