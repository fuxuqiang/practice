<?php

namespace App\Model;

class FundAmount extends \Fuxuqiang\Framework\Model\Model
{
    const FUND_ID = 'fund_id',
        DATE = 'date',
        TOTAL_PROFIT = 'total_profit';

    public int $fundId, $amount, $portion, $profit, $totalProfit;

    public string $date;
    
    public static function newData(FundWorth $worth, int $portion): self
    {
        $lastAmount = self::where(self::DATE, '<', $worth->date)
            ->where(self::FUND_ID, $worth->fundId)
            ->orderByDesc(self::DATE)
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