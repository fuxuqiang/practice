<?php

namespace App\Controller;

use App\Model\{FundTransaction, FundWorth, FundAmount};
use Fuxuqiang\Framework\{ResponseException, Route\Route};

class FundController
{
    #[Route('getData')]
    public function next(int $id, string $date = null): array
    {
        $fundWorthQuery = FundWorth::where(FundWorth::FUND_ID, $id);
        if ($date) {
            $fundWorth = $fundWorthQuery->where('date', '>', $date)->first();
            $fundAmount = FundAmount::update($fundWorth, 0, 0);
        } else {
            $fundWorth = $fundWorthQuery->first();
            $fundAmount = null;
        }

        return $this->getData($fundWorth, $fundAmount);
    }

    /**
     * @throws ResponseException
     */
    #[Route('buy', 'POST')]
    public function buy(int $id, int $amount, string $date): array
    {
        $worth = FundWorth::where(FundWorth::DATE, '>=', $date)
            ->where(FundWorth::FUND_ID, $id)
            ->limit(2)
            ->all();
        if (count($worth) != 2) {
            throw new ResponseException('没有该日期的数据', ResponseException::BAD_REQUEST);
        }

        return $this->getData($worth[1], $worth[0]->buy($amount, $worth[1]->date));
    }

    private function getData(FundWorth $worth, ?FundAmount $amount): array
    {
        if ($amount) {
            $data = [
                'amount' => $amount->amount,
                'profit' => $amount->profit,
                'portion' => $amount->portion
            ];
        } else {
            $data = ['amount' => 0, 'profit' => 0, 'portion' => 0];
        }
        return [
            'date' => $worth->date,
            'worth' => $worth->value,
            'can_sold' => FundTransaction::canSold($worth->fund_id, $worth->date)->all(),
        ] + $data;
    }
}