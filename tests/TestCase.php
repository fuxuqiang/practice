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
    
    protected function admin($id)
    {
        return self::$tokens[$id] ??
            self::$tokens[$id] = \vendor\Container::get('vendor\JWT')->encode(
                $id,
                \src\Mysql::table('admin')->where('id', $id)->val('password')
            );
    }
}
