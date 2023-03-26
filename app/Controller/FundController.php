<?php

namespace App\Controller;

use App\Model\FundWorth;
use Fuxuqiang\Framework\Route\Route;

class FundController
{
    #[Route('getWorth')]
    public function getWorth(int $id, string $date = null): FundWorth
    {
        $query = FundWorth::where(FundWorth::FUND_ID, $id);
        if ($date) {
            $query->where('date', '>', $date)->first();
        }
        return $query->first();
    }
}