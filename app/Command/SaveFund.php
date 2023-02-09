<?php

namespace App\Command;

use App\Model\FundWorth;

class SaveFund
{
    public function handle()
    {
        foreach (json_decode(file_get_contents(runtimePath('008591.json'))) as $item) {
            $data[] = [
                date('Y-m-d', $item->x / 1000),
                $item->y * 10000,
                $item->equityReturn * 10000
            ];
        }

        FundWorth::fields([FundWorth::DATE, FundWorth::VALUE, FundWorth::RATE])->insert($data);
    }
}