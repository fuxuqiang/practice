<?php

namespace App\Model;

class FundTransaction extends \Fuxuqiang\Framework\Model\Model
{
    const USER_ID = 'user_id',
        DATE = 'date',
        AMOUNT = 'amount',
        PORTION = 'portion',
        IS_FOLLOWED = 'is_followed';

    public $id,
        $user_id,
        $date,
        $amount,
        $portion;
}