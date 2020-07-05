<?php

namespace tests;

class TestCase extends \src\TestCase
{
    protected static $tokens;

    protected function getCode($phone)
    {
        $this->post('sendCode', ['phone' => $phone]);
        return $_SESSION['code_' . $phone];
    }
    
    protected function getToken($id, $table)
    {
        return self::$tokens[$table][$id] ??
            self::$tokens[$table][$id] = \vendor\Container::get('vendor\JWT')->encode(
                $id,
                \src\Mysql::table($table)->where('id', $id)->val('password')
            );
    }
}
