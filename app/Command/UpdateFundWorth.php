<?php

namespace App\Command;

use App\Model\{Fund, FundWorth};
use Fuxuqiang\Framework\Http\HttpClient;

class UpdateFundWorth
{
    const PATH = 'https://fund.eastmoney.com/pingzhongdata/';

    public function handle(): void
    {
        $http = new HttpClient();
        foreach (Fund::all() as $fund) {
            $http->addHandle(self::PATH . $fund->code . '.js', ['id' => $fund->id]);
        }
        FundWorth::truncate();
        foreach ($http->multiRequest() as $item) {
            preg_match('/ACWorthTrend = (.+?);/', $item->getContent(), $matches);
            foreach (json_decode($matches[1]) as $value) {
                $data[] = [
                    $item->params['id'],
                    date('Y-m-d', $value[0] / 1000),
                    round($value[1] * 10000)
                ];
            }
            FundWorth::fields([FundWorth::FUND_ID, FundWorth::DATE, FundWorth::VALUE])->insert($data);
        }
    }
}