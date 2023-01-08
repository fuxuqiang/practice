<?php

namespace App\Command;

use App\Model\Region;
use Overtrue\Pinyin\Pinyin;

class RegionToPinyin
{
    public function handle()
    {
        $regions = Region::whereRaw(
            '(`code` IN (?,?,?,?) OR `code` BETWEEN ? AND ?) AND `name` NOT IN (?,?,?,?)',
            [11, 12, 31, 50, 1000, 999999, '县', '自治区直辖县级行政区划', '省直辖县级行政区划', '市辖区']
        )
        ->all();
        foreach ($regions as $region) {
            $region->update([
                'en_name' => Pinyin::permalink($region->name, ''),
                'short_en_name' => Pinyin::abbr($region->name)->join(''),
            ]);
        }
    }
}