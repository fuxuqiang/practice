<?php

namespace App\Model;

class Fund extends \Fuxuqiang\Framework\Model\Model
{
    public int $id;

    public string $code;

    private static int $factor = 10000;

    public static function getAmount(int $portion, int $worth): float
    {
        return round($portion * $worth / self::$factor);
    }

    public static function getPortion(int $amount, int $worth): float
    {
        return round($amount * self::$factor / $worth);
    }
}