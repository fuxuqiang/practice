<?php
namespace app\controller;

class RegionController
{
    public function list(int $p_code = 0)
    {
        $factor = $p_code > 99999 ? 1000 : (in_array($p_code, [4419, 4420]) ? 100000 : 100);
        return [
            'data' => mysql()->query(
                    'SELECT * FROM `region` WHERE `code` BETWEEN ? AND ?',
                    [$p_code * $factor, ($p_code + 1) * $factor]
                )->fetch_all(MYSQLI_ASSOC)
        ];
    }
}
