<?php

namespace App\Controller;

use App\Model\{FundTransaction, FundWorth, FundAmount};
use Fuxuqiang\Framework\{ResponseCode, ResponseException, Route\Route};

#[Route('fund')]
class FundController
{
    #[Route('getData')]
    public function index(int $id): array
    {
        $amount = FundAmount::orderByDesc(FundAmount::DATE)->first();
        if ($amount) {
            $worth3 = FundWorth::get3Worth($id, $amount->date);
            $worthList = array_merge(
                FundWorth::fundId($id)->where(FundWorth::DATE, '<', $amount->date)->all(),
                array_slice($worth3, 0, 2)
            );
            $confirmAt = $worth3[2]->date;
        } else {
            $worthList = [FundWorth::fundId($id)->firstOrFail()];
            $confirmAt = null;
        }
        return $this->getData($id, $amount, $confirmAt) + ['worth' => $worthList];
    }

    #[Route('next')]
    public function next(int $id, string $date): array
    {
        $worth = FundWorth::get3Worth($id, $date);
        $amount = null;
        if (FundAmount::exists(FundWorth::DATE, '<', $date)) {
            $amount = FundAmount::newData($worth[0], 0);
        }

        return $this->getNextData($worth[1], $amount, $worth[2]->date);
    }

    /**
     * @throws ResponseException
     */
    #[Route('buy', 'POST')]
    public function buy(int $id, int $amount, string $date): array
    {
        $worth = FundWorth::get3Worth($id, $date);

        return $this->getNextData($worth[1], $worth[0]->buy($amount, $worth[1]->date), $worth[2]->date);
    }

    /**
     * @throws ResponseException
     */
    #[Route('sell', 'POST')]
    public function sell(array $transactionIds, int $id, string $date): array
    {
        $worth = FundWorth::get3Worth($id, $date);
        $transactions = FundTransaction::canSold($id, $worth[1]->date)
            ->whereIn(FundTransaction::ID, $transactionIds)
            ->all();
        if (count($transactions) != count($transactionIds)) {
            throw new ResponseException('存在不可卖出的份额', ResponseCode::BadRequest);
        }

        return $this->getNextData($worth[1], $worth[0]->sell($transactions, $worth[1]->date), $worth[2]->date);
    }

    /**
     * 获取图表数据
     */
    private function getData(int $id, ?FundAmount $amount, ?string $confirmAt): array
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
        return $data + ['canSold' => $confirmAt ? FundTransaction::canSold($id, $confirmAt)->all() : []];
    }

    private function getNextData(FundWorth $worth, ?FundAmount $amount, string $confirmAt): array
    {
        return $this->getData($worth->fundId, $amount, $confirmAt) + [
            'date' => $worth->date,
            'value' => $worth->value
        ];
    }
}