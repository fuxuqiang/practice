<?php

namespace App\Model;

use Fuxuqiang\Framework\Model\{ModelQuery, Model};

/**
 * @method static ModelQuery canSold(int $id, string $date)
 * @method static ModelQuery fundId(int $fundId)
 * @method static ModelQuery unSold(int $fundId)
 */
class FundTransaction extends Model
{
    const ID = 'id',
        FUND_ID = 'fund_id',
        BOUGHT_AT = 'bought_at',
        CONFIRM_AT = 'confirm_at',
        AMOUNT = 'amount',
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

    public function scopeFundId(ModelQuery $query, int $fundId): ModelQuery
    {
        return $query->where(self::FUND_ID, $fundId);
    }

    public function scopeUnsold(ModelQuery $query, int $fundId): ModelQuery
    {
        return $this->scopeFundId($query, $fundId)->where(self::IS_SOLD, 0);
    }
}