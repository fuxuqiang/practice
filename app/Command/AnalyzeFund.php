<?php

namespace App\Command;

use App\Model\{FundAmount, FundTransaction, FundWorth};

class AnalyzeFund
{
    private array $amountList = [1, 1, 2, 6, 10, 20, 60, 100, 200, 600, 1000, 2000, 6000];

    private int $firstAmount = 20000;

    private float $scope = 0.003;

    public function __construct()
    {
        array_walk($this->amountList, fn(&$item) => $item *= 1000);
    }

    public function handle(int $fundId): void
    {
        $lastTransaction = FundTransaction::fundId($fundId)->orderByDesc(FundTransaction::BOUGHT_AT)->first();
        if ($lastTransaction) {
            $fundWorth = FundWorth::fundId($fundId)->where(FundWorth::DATE, '>=', $lastTransaction->boughtAt)->all();
            $currWorthValue = array_shift($fundWorth)->value;
            $this->updateAmountList();
        } else {
            $fundWorth = FundWorth::fundId($fundId)->all();
            $currWorthValue = array_shift($fundWorth)->value;
            foreach ($fundWorth as $key => $worth) {
                // 满足起始金额
                if (current($this->amountList) == $this->firstAmount) {
                    // 建仓
                    $worth->buy($this->updateAmountList(), $fundWorth[$key+1]->date);
                    $currWorthValue = $worth->value;
                    $fundWorth = array_slice($fundWorth, $key + 1);
                    break;
                }
                // 切换起始金额
                $rate = ($worth->value - $currWorthValue) / $currWorthValue;
                if ($rate < -$this->scope) {
                    next($this->amountList);
                    $currWorthValue = $worth->value;
                } elseif ($rate > $this->scope) {
                    prev($this->amountList);
                    $currWorthValue = $worth->value;
                }
            }
        }

        foreach ($fundWorth as $key => $item) {
            if (isset($fundWorth[$key+1])) {
                $rate = ($item->value - $currWorthValue) / $currWorthValue;
                // 买入
                if ($rate < -$this->scope) {
                    $amountList = array_diff(
                        $this->amountList,
                        FundTransaction::fundId($fundId)->where(FundTransaction::IS_SOLD, 0)->column(FundTransaction::AMOUNT)
                    );
                    if ($amountList) {
                        $item->buy(min($amountList), $fundWorth[$key+1]->date);
                    }
                    $currWorthValue = $item->value;
                // 卖出
                } elseif (
                    $rate > $this->scope &&
                    $canSold = FundTransaction::canSold($fundId, $fundWorth[$key+1]->date)->orderBy(FundTransaction::AMOUNT)->first()
                ) {
                    $item->sell([$canSold], $fundWorth[$key+1]->date);
                    $currWorthValue = $item->value;
                } else {
                    FundAmount::newData($item, 0);
                }
            }
        }
    }

    private function updateAmountList(): int
    {
        $key = array_search($this->firstAmount, $this->amountList) + 1;
        $firstAmount = array_sum(array_slice($this->amountList, 0, $key));
        $this->amountList = array_merge([$firstAmount], array_slice($this->amountList, $key));
        return $firstAmount;
    }
}