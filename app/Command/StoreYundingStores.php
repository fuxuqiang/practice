<?php

namespace App\Command;

use App\Model\{Yunding, Region};

class StoreYundingStores
{
    public function handle(Yunding $yunding)
    {
        foreach (json_decode(file_get_contents($yunding->storesFile))->content as $store) {
            $address = $region = '';
            if ($store->address && $region = Region::getCode($store->address, 'object')) {
                $address = mb_substr(
                    $store->address,
                    mb_strrpos($store->address, $region->name) + mb_strlen($region->name)
                );
            }
            \Src\Mysql::table('yunding_store')->replace([
                'name' => $store->name,
                'store_id' => $store->id,
                'address' => $address,
                'region_code' => $region->code ?? 0,
                'status' => $store->status,
            ]);
        }
    }
}
