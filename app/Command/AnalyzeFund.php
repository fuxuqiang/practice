<?php

namespace App\Command;

use App\Model\{FundAmount, FundTransaction, FundWorth};
use Src\Mysql;

class AnalyzeFund
{
    private $fundWorth,

        $currWorthValue = 1000,

        $factor = 10000,

        $list = [4, 6, 10, 20, 60, 100, 200, 600],
        
        $fields = [
            FundTransaction::FUND_ID,
            FundTransaction::AMOUNT,
            FundTransaction::PORTION,
            FundTransaction::BOUGHT_AT,
            FundTransaction::CONFIRM_AT,
            FundTransaction::PER_WORTH,
            FundTransaction::IS_SOLD,
        ];
    private int $fundId = 2;

    public function __construct()
    {
        array_walk($this->list, fn(&$item) => $item *= 10000);
        $this->fundWorth = FundWorth::where(FundWorth::FUND_ID, $this->fundId)->all();
    }

    public function handle(): void
    {
        $this->findBuyingPoint();

        $totalProfit = $portion = 0;
        foreach ($this->fundWorth as $key => $item) {
            // 相对于最后一次操作时净值的涨跌幅
            $rate = ($item->value - $this->currWorthValue) / $this->currWorthValue;
            // 盈亏
            $profit = $key ? $this->getAmount($portion, $item->value - $this->fundWorth[$key-1]->value) : 0;

            if ($nextWorth = $this->fundWorth[$key+1] ?? false) {
                // 买入
                if ($rate < -0.003) {
                    $portion += $this->buy($item, $nextWorth->date);
                // 卖出
                } elseif ($rate > 0.003) {
                    $currentAmount = FundTransaction::canSold($this->fundId)->sum(FundTransaction::AMOUNT);
                    if ($this->getAmount($portion, $item->value) > $currentAmount) {
                        $portion -= $this->sell($item, $nextWorth->date);
                    }
                }
            }
            // 记录持仓及盈亏
            FundAmount::fields([
                    FundAmount::FUND_ID,
                    FundAmount::DATE,
                    FundAmount::PORTION,
                    FundAmount::AMOUNT,
                    FundAmount::PROFIT,
                    FundAmount::TOTAL_PROFIT
                ])
                ->insert([[
                    $this->fundId,
                    $item->date,
                    $portion,
                    $this->getAmount($portion, $item->value),
                    $profit,
                    $totalProfit += $profit
                ]]);
        }
    }

    /**
     * 寻找买入点
     */
    private function findBuyingPoint()
    {
        $max = 0;
        foreach ($this->fundWorth as $item) {
            if ($item->value > $max) {
                $max = $item->value;
            } elseif (($item->value - $max) / $max < -0.06) {
                return;
            }
            $this->currWorthValue = array_shift($this->fundWorth)->value;
        }
    }

    /**
     * 买入
     */
    private function buy(FundWorth $item, $confirmAt)
    {
        $portion = 0;
        $list = $this->list;
        foreach (FundTransaction::canSold($this->fundId)->column(FundTransaction::AMOUNT) as $amount) {
            if (($key = array_search($amount, $list)) !== false) {
                unset($list[$key]);
            }
        }
        if ($list) {
            $amount = min($list);
            $this->currWorthValue = $item->value;
            $portion = round($amount * $this->factor / $this->currWorthValue);
            FundTransaction::fields($this->fields)
                ->insert([
                    [$this->fundId, $amount, $portion, $item->date, $confirmAt, $this->currWorthValue, 0]
                ]);
        }
        return $portion;
    }

    /**
     * 卖出
     */
    private function sell(FundWorth $item, $confirmAt)
    {
        // 查询可以卖出的份额
        $canSolds = FundTransaction::canSold($this->fundId)
            ->where(FundTransaction::CONFIRM_AT, '<=', date('Y-m-d', strtotime($confirmAt) - 7*24*3600))
            ->all();

        $portion = 0;
        if ($canSolds) {
            Mysql::begin();
            foreach ($canSolds as $canSold) {
                $portion += $canSold->portion;
                $canSold->update([FundTransaction::IS_SOLD => 1]);
            }
            // 卖出操作
            if ($portion) {
                $this->currWorthValue = $item->value;
                FundTransaction::fields(array_slice($this->fields, 0, -1))
                    ->insert([[
                        $this->fundId,
                        -$this->getAmount($portion, $this->currWorthValue),
                        -$portion,
                        $item->date,
                        $confirmAt,
                        $item->value
                    ]]);
            }
            Mysql::commit();
        }
        return $portion;
    }

    /**
     * 通过净值和份额计算金额
     */
    private function getAmount($portion, $worth)
    {
        return round($portion * $worth / $this->factor);
    }
}