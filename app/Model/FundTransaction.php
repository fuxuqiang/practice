<?php

namespace App\Model;

use Fuxuqiang\Framework\{Mysql, Model\Model};

class FundTransaction extends Model
{
    const BOUGHT_AT = 'bought_at',
        CONFIRM_AT = 'confirm_at',
        AMOUNT = 'amount',
        PORTION = 'portion',
        PER_WORTH = 'per_worth',
        IS_SOLD = 'is_sold';

    public $id, $portion;

    public function scopeCanSold(Mysql $query)
    {
        return $query->where(self::IS_SOLD, 0);
    }
}