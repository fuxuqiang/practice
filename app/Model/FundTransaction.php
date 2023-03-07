<?php

namespace App\Model;

use Fuxuqiang\Framework\{Mysql, Model\Model};

class FundTransaction extends Model
{
    const FUND_ID = 'fund_id',
        BOUGHT_AT = 'bought_at',
        CONFIRM_AT = 'confirm_at',
        AMOUNT = 'amount',
        PORTION = 'portion',
        PER_WORTH = 'per_worth',
        IS_SOLD = 'is_sold';

    public int $id, $portion;

    public function scopeCanSold(Mysql $query, $fundId): Mysql
    {
        return $query->where(self::IS_SOLD, 0)->where(self::FUND_ID, $fundId);
    }
}