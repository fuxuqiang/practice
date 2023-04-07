<?php

namespace App\Model;

use Fuxuqiang\Framework\{Model\ModelQuery, Mysql, Model\Model};

/**
 * @method static ModelQuery canSold(int $id, string $date)
 */
class FundTransaction extends Model
{
    const ID = 'id',
        FUND_ID = 'fund_id',
        BOUGHT_AT = 'bought_at',
        CONFIRM_AT = 'confirm_at',
        AMOUNT = 'amount',
        PORTION = 'portion',
        PER_WORTH = 'per_worth',
        IS_SOLD = 'is_sold';

    public int $id, $fundId, $amount, $portion;

    public ?int $isSold;

    public string $boughtAt, $confirmAt;

    public function scopeCanSold(ModelQuery $query, int $fundId, string $date): ModelQuery
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