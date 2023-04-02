<?php

namespace App\Model;

use Fuxuqiang\Framework\{Mysql, Model\Model};

/**
 * @method static Mysql canSold(int $id, string $date)
 */
class FundTransaction extends Model
{
    const FUND_ID = 'fund_id',
        BOUGHT_AT = 'bought_at',
        CONFIRM_AT = 'confirm_at',
        AMOUNT = 'amount',
        PORTION = 'portion',
        PER_WORTH = 'per_worth',
        IS_SOLD = 'is_sold';

    public int $id, $fund_id, $amount, $portion;

    public string $bought_at, $confirm_at;

    public function scopeCanSold(Mysql $query, int $fundId, string $date): Mysql
    {
        return $query->where(self::IS_SOLD, 0)
            ->where(self::FUND_ID, $fundId)
            ->where(
                FundTransaction::CONFIRM_AT,
                '<=', date('Y-m-d',
                strtotime($date) - 7*24*3600)
            );
    }
}