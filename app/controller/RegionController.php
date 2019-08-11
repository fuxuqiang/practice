<?php
namespace app\controller;

class RegionController
{
    public function index(int $p_code = 0)
    {
        $factor = $p_code > 99999 ? 1000 : 100;
        return [
            'data' => mysql()->query(
                    'SELECT * FROM `region` WHERE `code` BETWEEN ? AND ?',
                    [$p_code * $factor, ($p_code + 1) * $factor]
                )->fetch_all(MYSQLI_ASSOC)
        ];
    }
}
