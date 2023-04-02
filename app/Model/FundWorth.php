<?php

namespace App\Model;

use Src\Mysql;

class FundWorth extends \Fuxuqiang\Framework\Model\Model
{
    const FUND_ID = 'fund_id',
        DATE = 'date',
        VALUE = 'value';

    public int $fund_id, $value;

    public string $date;

    public function buy(int $amount, string $confirmedAt): FundAmount
    {
        Mysql::begin();

        $transaction = new FundTransaction;
        $transaction->fund_id = $this->fund_id;
        $transaction->amount = $amount;
        $transaction->portion = Fund::getPortion($amount, $this->value);
        $transaction->bought_at = $this->date;
        $transaction->confirm_at = $confirmedAt;
        $transaction->save();

        $fundAmount = FundAmount::update($this, $transaction->portion);

        Mysql::commit();
        return $fundAmount;
    }
}