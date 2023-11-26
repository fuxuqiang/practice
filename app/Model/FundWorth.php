<?php

namespace App\Model;

use Fuxuqiang\Framework\{Model\Model, Model\ModelQuery, ResponseCode, ResponseException};
use Src\Mysql;

/**
 * @method static ModelQuery fundId(int $id)
 */
class FundWorth extends Model
{
    const FUND_ID = 'fund_id',
        DATE = 'date',
        VALUE = 'value';

    public int $fundId, $value;

    public string $date;

    /**
     * 买入
     */
    public function buy(int $amount, string $confirmedAt): FundAmount
    {
        Mysql::begin();
        $transaction = $this->createTransaction($amount, Fund::getPortion($amount, $this->value), $confirmedAt);
        $fundAmount = FundAmount::newData($this, $transaction->portion);
        Mysql::commit();
        return $fundAmount;
    }

    /**
     * 卖出
     */
    public function sell(array $transactions, string $confirmAt): FundAmount
    {
        $portion = 0;
        Mysql::begin();
        foreach ($transactions as $transaction) {
            $transaction->isSold = 1;
            $transaction->save();
            $portion += $transaction->portion;
        }
        $this->createTransaction(-Fund::getAmount($portion, $this->value), -$portion, $confirmAt);
        $fundAmount = FundAmount::newData($this, -$portion);
        Mysql::commit();
        return $fundAmount;
    }

    private function createTransaction(int $amount, int $portion, string $confirmAt): FundTransaction
    {
        $transaction = new FundTransaction;
        $transaction->fundId = $this->fundId;
        $transaction->amount = $amount;
        $transaction->portion = $portion;
        $transaction->boughtAt = $this->date;
        $transaction->confirmAt = $confirmAt;
        $transaction->isSold = $amount > 0 ? 0 : null;
        $transaction->save();
        return $transaction;
    }

    /**
     * @throws ResponseException
     * @return self[]
     */
    public static function get3Worth(int $id, string $date): array
    {
        $worth = FundWorth::fundId($id)
            ->where(FundWorth::DATE, '>=', $date)
            ->limit(3)
            ->all();
        if (count($worth) != 3) {
            throw new ResponseException('没有该日期的数据', ResponseCode::BadRequest);
        }
        return $worth;
    }

    public function scopeFundId(ModelQuery $query, int $id): ModelQuery
    {
        return $query->where(self::FUND_ID, $id);
    }
}