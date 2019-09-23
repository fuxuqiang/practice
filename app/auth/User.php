<?php
namespace app\auth;

class User implements \src\jwt\Auth
{
    public static function handle($token, $jwt)
    {
        if ($id = $jwt->decode($token)) {
            return mysql('user')->cols('id')->where('id', $id)->get('src\Model', ['user']);
        }
    }
}
