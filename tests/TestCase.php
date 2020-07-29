<?php

namespace tests;

use src\{Redis, Mysql};

class TestCase extends \src\TestCase
{
    protected static $tokens;

    protected function getCode($mobile)
    {
        $this->post('send_code', ['mobile' => $mobile]);
        return Redis::get($mobile);
    }
    
    protected function getToken($id, $table)
    {
        return self::$tokens[$table][$id] ??
            self::$tokens[$table][$id] = \vendor\Container::get('vendor\JWT')->encode(
                $id,
                Mysql::table($table)->where('id', $id)->val('password')
            );
    }

    protected function user($id)
    {
        return $this->setToken($id, 'user');
    }

    protected function admin($id)
    {
        return $this->setToken($id, 'admin');
    }

    private function setToken($id, $table)
    {
        $this->token = $this->getToken($id, $table);
        return $this;
    }
}
