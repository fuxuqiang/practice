<?php
namespace app\auth;

class Admin implements \src\Auth
{
    public static function handle($token)
    {
        if ($admin = mysql()->query(
                'SELECT `id`,`role_id` FROM `admin` WHERE `api_token`=? AND `token_expires`>NOW()',
                [$token]
            )->fetch_object(\src\Model::class, ['admin'])) {
            if (($route = mysql('route')->cols('id')->where([
                    ['method', '=', $_SERVER['REQUEST_METHOD']],
                    ['uri', '=', ltrim($_SERVER['PATH_INFO'], '/')]
                ])->get()->fetch_row()) && ! mysql('role_route')->where('role_id', $admin->role_id)
                    ->exists('route_id', $route[0])) {
                return false;
            }
            return $admin;
        }
        return false;
    }
}
