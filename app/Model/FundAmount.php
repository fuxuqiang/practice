<?php

namespace App\Model;

class FundAmount extends \Fuxuqiang\Framework\Model\Model
{
    const FUND_ID = 'fund_id',
        DATE = 'date',
        AMOUNT = 'amount',
        PORTION = 'portion',
        PROFIT = 'profit',
        TOTAL_PROFIT = 'total_profit';

    public int $fundId, $amount, $portion, $profit, $totalProfit;

    public string $date;
    
    public static function newData(FundWorth $worth, int $portion): self
    {
        $lastAmount = self::where(self::DATE, '<', $worth->date)
            ->orderByDesc(self::DATE)
            ->fields([self::PORTION, self::PROFIT, self::AMOUNT, self::FUND_ID])
            ->first();
        $profit = $totalProfit = 0;
        if ($lastAmount) {
            $portion += $lastAmount->portion;
            $profit = Fund::getAmount($lastAmount->portion, $worth->value) - $lastAmount->amount;
            $totalProfit = $lastAmount->totalProfit + $profit;
        }
        $fundAmount = new self;
        $fundAmount->fundId = $worth->fundId;
        $fundAmount->date = $worth->date;
        $fundAmount->portion = $portion;
        $fundAmount->amount = Fund::getAmount($portion, $worth->value);
        $fundAmount->profit = $profit;
        $fundAmount->totalProfit = $totalProfit;
        $fundAmount->save();

        return $fundAmount;
    }
}