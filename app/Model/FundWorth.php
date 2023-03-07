<?php

namespace App\Model;

class FundWorth extends \Fuxuqiang\Framework\Model\Model
{
    const FUND_ID = 'fund_id',
        DATE = 'date',
        VALUE = 'value';

    public string $date;

    public int $value;
}