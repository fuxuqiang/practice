<?php

namespace App\Model;

use App\Model\Region;

class Address
{
    public function __construct(private string $address) {}

    /**
     * 获取地址的详细行政区
     */
    public function parseRegion()
    {
        if ($province = Region::like($this->getRegionName(0))->get()) {
            if ($city = $this->getRegion($province->code, $regionLen = mb_strlen($province->name))) {
                if ($district = $this->getRegion($city->code, $regionLen += mb_strlen($city->name))) {
                    if ($town = $this->getRegion($district->code, $regionLen += mb_strlen($district->name))) {
                        $regionLen += mb_strlen($town->name);
                    }
                }
                return [
                    'region' => [$province, $city, $district, $town ?? null],
                    'address' => mb_substr($this->address, $regionLen),
                ];
            } else {
                return Region::find(Region::getAllCode($province->code));
            }
        }
    }

    /**
     * 获取行政区model
     */
    private function getRegion($code, $offset)
    {
        $regionName = $this->getRegionName($offset);
        if (strlen($regionName) < 2) {
            return null;
        }
        return Region::like($regionName)->child($code)->get();
    }

    /**
     * 获取行政区名称
     */
    private function getRegionName($offset)
    {
        return mb_substr($this->address, $offset, 3);
    }
}