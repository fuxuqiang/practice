<?php
namespace app\model;

class Region
{
    public static function getAllCode($code)
    {
        return [
            substr($code, 0, 2),
            substr($code, 0, 4),
            substr($code, 0, 6),
            substr($code, 0, 9),
            $code
        ];
    }
}
