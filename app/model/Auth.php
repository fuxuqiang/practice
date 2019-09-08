<?php
namespace app\model;

class Auth 
{
    public static function getToken($table, $id)
    {
        $token = uniqid();
        mysql($table)->where('id', $id)
            ->update(['api_token' => $token, 'token_expires' => timestamp('2 hour')]);
        return $token;
    }
}
