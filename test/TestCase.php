<?php

namespace Test;

use Src\{Redis, Mysql};
use Fuxuqiang\Framework\{Container, JWT};

class TestCase extends \Src\TestCase
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
            self::$tokens[$table][$id] = Container::get(JWT::class)->encode(
                $id,
                $table . Mysql::table($table)->where('id', $id)->val('password')
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
