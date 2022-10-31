<?php

namespace App\Model;

use App\Model\Region;

class Address
{
    public function __construct(private string $address) {}

    /**
     * 获取行政区model
     */
    private function getRegion($offset)
    {
        $regionName = mb_substr($this->address, $offset, 3);
        if (strlen($regionName) < 2) {
            return null;
        }
        return Region::search($regionName);
    }

    /**
     * 获取地址的详细行政区
     */
    public function getCode()
    {
        if ($province = $this->getRegion(0)) {
            if ($city = $this->getRegion($foundRegionLen = mb_strlen($province->name))) {
                if ($district = $this->getRegion($foundRegionLen += mb_strlen($city->name))) {
                    $town = $this->getRegion($foundRegionLen += mb_strlen($district->name));
                }
                return [$province, $city, $district, $town ?? null];
            } else {
                return Region::find(Region::getAllCode($province->code));
            }
        } else {
            return null;
        }
    }
}