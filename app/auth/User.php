<?php
namespace app\auth;

class User implements \src\Auth
{
    public static function handle($token)
    {
        return ($user = mysql()->query(
                'SELECT `id` FROM `user` WHERE `api_token`=? AND `token_expires`>NOW()',
                's',
                [$token]
            )->fetch_object(\src\Model::class, ['user'])) ?
            $user : false;
    }
}
