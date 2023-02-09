<?php

namespace App\Command;

use App\Model\{FundTransaction, FundWorth};

class AnalyzeFund
{
    public function handle()
    {
        // 设定买入数额的第一位组成的序列
        $list = [1, 2, 6];
        // 建仓的金额及单位
        $amount = $unit = 1000;
        // 金额计算份额系数
        $factor = 10000;

        // 获取基金净值数据
        $worth = FundWorth::where(FundWorth::DATE, '>=', '2019-12-30')->all();

        $boughtWorth = array_shift($worth);
        $lastDealtWorth = $boughtWorth->value; // 最后成交净值
        $fields = [FundTransaction::USER_ID, FundTransaction::DATE, FundTransaction::AMOUNT, FundTransaction::PORTION];

        // Mysql::begin();

        // 建仓
        FundTransaction::fields($fields)->insert([
            [1, $boughtWorth->date, $amount, round($amount * $factor / $lastDealtWorth)]
        ]);

        foreach ($worth as $item) {
            // 相对于最后一次操作时净值的涨跌幅
            $rate = ($item->value - $lastDealtWorth) / $lastDealtWorth;
            // 买入
            if ($rate < -0.003) {
                // 买入金额
                $lastSold = FundTransaction::where(FundTransaction::IS_FOLLOWED, 0)
                    ->where(FundTransaction::AMOUNT, '<', 0)
                    ->orderByDesc(FundTransaction::AMOUNT)
                    ->first();
                if ($lastSold) {
                    $lastSold->update([FundTransaction::IS_FOLLOWED => 1]);
                    $amount = -$lastSold->amount;
                } else {
                    if (($amount = current($list) * $unit) == 10000000) {
                        continue;
                    }
                    if (!next($list)) {
                        reset($list);
                        $unit *= 10;
                    }
                }
                // 买入净值
                $lastDealtWorth = $item->value;
                // 记录买入数据
                FundTransaction::fields($fields)
                    ->insert([[1, $item->date, $amount, round($amount * $factor / $lastDealtWorth)]]);

            // 卖出
            } elseif ($rate > 0.003) {
                // 查询可以卖出的份额
                $canSold = FundTransaction::where([
                        FundTransaction::IS_FOLLOWED => 0,
                        [FundTransaction::PORTION, '>', 0],
                        [FundTransaction::DATE, '<=', date('Y-m-d', strtotime($item->date) - 7*24*3600)]
                    ])
                    ->first();

                if ($canSold) {
                    $canSold->update([FundTransaction::IS_FOLLOWED => 1]);
                    $lastDealtWorth = $item->value;
                    FundTransaction::fields($fields)
                        ->insert([
                            [1, $item->date, -round($canSold->portion * $lastDealtWorth / $factor), -$canSold->portion]
                        ]);
                }
            }
        }

        // Mysql::commit();
    }
}