<?php

namespace App\Model;

use Fuxuqiang\Framework\Model\{Model, ModelQuery};

/**
 * @method static ModelQuery firstPriority()
 */
class Fund extends Model
{
    const ID = 'id',
        PRIORITY = 'priority';

    public int $id;

    public string $code, $industry;

    private static int $factor = 10000;

    public static function getAmount(int $portion, int $worth): float
    {
        return round($portion * $worth / self::$factor);
    }

    public static function getPortion(int $amount, int $worth): float
    {
        return round($amount * self::$factor / $worth);
    }

    public function scopeFirstPriority(ModelQuery $query): ModelQuery
    {
        return $query->where(self::PRIORITY, 0);
    }

    public function worth(): ModelQuery
    {
        return FundWorth::fundId($this->id);
    }
}