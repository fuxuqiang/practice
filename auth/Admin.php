<?php
namespace auth;

class Admin implements \src\Auth
{
    public static function handle($token)
    {
        return $token && ($model = mysql()->query(
                'SELECT `id`,`role_id` FROM `admin` WHERE `api_token`=? AND `token_expires`>NOW()',
                's',
                [$token]
            )->fetch_object(\model\Admin::class)) ?
            $model : false;
    }
}
